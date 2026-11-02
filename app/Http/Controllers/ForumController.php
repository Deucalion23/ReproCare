<?php

namespace App\Http\Controllers;

use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumLike;
use App\Models\Woman;
use App\Models\Midwife;
use App\Models\Bhw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ForumController extends Controller
{
    private const FORUM_IMAGE_DIRECTORIES = [
        'uploads/forum/',
        'forum/',
    ];

    private const FEED_PAGE_SIZE = 10;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get current authenticated user
     */
    private function getCurrentUser()
    {
        return auth()->user();
    }

    /**
     * Get current user type for view routing
     */
    private function getUserType()
    {
        $user = auth()->user();
        return match($user->role) {
            'midwife' => 'midwife',
            'bhw' => 'bhw',
            'bhw_president' => 'bhw-president',
            'user' => 'user',
            default => null,
        };
    }

    // Index - Show all posts
    public function index()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) return redirect()->route('login');

        $posts = $this->feedQuery()->cursorPaginate(self::FEED_PAGE_SIZE, ['created_at', 'id'], 'cursor');
        $likedIds = $this->likedPostIds($currentUser, $posts->items());

        $filter = request('filter');
        $search = request('search');

        // One shared community design for every role.
        return view('forum.index', compact('posts', 'filter', 'search', 'likedIds'));
    }

    /**
     * Next page of the feed as a bare fragment, for the infinite scroll.
     */
    public function feed()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) return response()->json(['html' => '', 'hasMore' => false], 401);

        $posts = $this->feedQuery()->cursorPaginate(self::FEED_PAGE_SIZE, ['created_at', 'id'], 'cursor');
        $likedIds = $this->likedPostIds($currentUser, $posts->items());
        $isLimited = (bool) $currentUser->hasLimitedAccess();

        $html = collect($posts->items())
            ->map(fn ($post) => view('forum.post-card', [
                'post' => $post,
                'isLimited' => $isLimited,
                'likedIds' => $likedIds,
            ])->render())
            ->implode("\n");

        $next = $posts->nextCursor();

        return response()->json([
            'html' => $html,
            'cursor' => $next ? $next->encode() : null,
            'hasMore' => $posts->hasMorePages(),
        ]);
    }

    /**
     * The feed, ordered for a stable cursor. Shared by the page and the
     * infinite-scroll endpoint so both agree on what "next" means.
     */
    private function feedQuery()
    {
        $currentUser = $this->getCurrentUser();

        $query = ForumPost::active()
            ->with([
                'user',
                'comments' => function ($query) {
                    $query->with('user');
                    $query->latest()->take(3);
                },
            ])
            ->withCount(['likes as likes_count', 'comments as comments_count']);

        $search = request('search');
        if ($search) {
            $query->where('content', 'like', '%' . $search . '%');
        }

        if (request('filter') === 'my-posts') {
            $query->where('user_id', $currentUser->id);
        }

        // created_at is not unique, so two posts sharing a second would sit on
        // either side of a cursor and one would be handed out twice. id ties it.
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    /**
     * Which of these posts the viewer already liked, in one query — the card
     * used to ask per post, which the scroll multiplied every page.
     */
    private function likedPostIds($currentUser, array $posts): array
    {
        if (!$currentUser || $posts === []) {
            return [];
        }

        return ForumLike::where('user_id', $currentUser->id)
            ->whereIn('post_id', array_map(fn ($post) => $post->id, $posts))
            ->pluck('post_id')
            ->all();
    }

    // Show single post
    public function show($id)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) return redirect()->route('login');

        $post = ForumPost::with([
                'user',
                'comments.user',
                'likes.user',
            ])
            ->withCount(['likes as likes_count', 'comments as comments_count'])
            ->where(function ($q) use ($currentUser) {
                $q->where('status', 'active')->orWhere('user_id', $currentUser->id);
            })
            ->findOrFail($id);

        // One shared community design for every role.
        return view('forum.show', compact('post'));
    }

    // Create post form
    public function create()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) return redirect()->route('login');

        // One shared community design for every role.
        return view('forum.create');
    }

    // Store new post
    public function store(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) return redirect()->route('login');

        $request->validate([
            'content' => 'required|string|max:2000',
            'post_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $postData = [
            'content' => $request->content,
            'user_id' => $currentUser->id,
        ];

        // Handle image upload
        if ($request->hasFile('post_image')) {
            $image = $request->file('post_image');
            $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('uploads/forum', $filename, 'public');
            $postData['post_image'] = $filename;
            $postData['post_image_data'] = ForumPost::makePostImageDataUrl($image);
        }

        ForumPost::create($postData);

        return redirect()->route('forum.index')
            ->with('success', 'Post created successfully');
    }

    // Edit post form
    public function edit($id)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) return redirect()->route('login');

        $post = ForumPost::findOrFail($id);

        // Only allow users to edit their own posts
        if ($post->user_id !== $currentUser->id) {
            abort(403);
        }

        // One shared community design for every role.
        return view('forum.edit', compact('post'));
    }

    // Update post
    public function update(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) return redirect()->route('login');

        $post = ForumPost::findOrFail($id);

        // Only allow users to edit their own posts
        if ($post->user_id !== $currentUser->id) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string|max:2000',
            'post_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'remove_post_image' => 'nullable|boolean',
        ]);

        $updateData = ['content' => $request->content];
        $removePostImage = $request->boolean('remove_post_image');

        if ($removePostImage) {
            $this->deletePostImage($post->post_image);
            $updateData['post_image'] = null;
            $updateData['post_image_data'] = null;
        }

        // Handle image upload
        if ($request->hasFile('post_image')) {
            // Delete old image if exists
            $this->deletePostImage($post->post_image);

            $image = $request->file('post_image');
            $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('uploads/forum', $filename, 'public');
            $updateData['post_image'] = $filename;
            $updateData['post_image_data'] = ForumPost::makePostImageDataUrl($image);
        }

        $post->update($updateData);

        return redirect()->route('forum.show', $id)
            ->with('success', 'Post updated successfully');
    }

    // Delete post (soft delete for midwives, hard delete for own posts)
    public function destroy($id)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) return redirect()->route('login');

        $post = ForumPost::findOrFail($id);
        $userType = $this->getUserType();

        // Delete post image if exists
        $this->deletePostImage($post->post_image);

        // Midwives can soft delete any post
        if ($userType === 'midwife') {
            $post->softDelete();
            return redirect()->route('forum.index')
                ->with('success', 'Post deleted successfully');
        }

        // Users can only delete their own posts
        if ($post->user_id === $currentUser->id) {
            $post->delete();
            return redirect()->route('forum.index')
                ->with('success', 'Post deleted successfully');
        }

        abort(403);
    }

    // Add comment
    public function comment(Request $request, $postId)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) return redirect()->route('login');

        $post = ForumPost::where('status', 'active')->findOrFail($postId);
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $commentData = [
            'post_id' => $post->id,
            'content' => $request->content,
            'user_id' => $currentUser->id,
        ];

        ForumComment::create($commentData);

        return redirect()->route('forum.show', $postId)
            ->with('success', 'Comment added successfully');
    }

    // Like/Unlike post
    public function like(Request $request, $postId)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) return redirect()->route('login');

        $post = ForumPost::where('status', 'active')->findOrFail($postId);

        // Toggle via unique key so races cannot duplicate.
        $existingLike = ForumLike::where('post_id', $post->id)
            ->where('user_id', $currentUser->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            try {
                ForumLike::create([
                    'post_id' => $post->id,
                    'user_id' => $currentUser->id,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Already liked concurrently — treat as liked.
            }
        }

        // The feed is infinite, so a like must not reload the page and throw
        // away every page the reader already scrolled in.
        if ($request->expectsJson()) {
            return response()->json([
                'liked' => !$existingLike,
                'likes_count' => ForumLike::where('post_id', $post->id)->count(),
            ]);
        }

        return redirect()->back();
    }

    private function deletePostImage(?string $filename): bool
    {
        if (!$filename) {
            return false;
        }

        $publicDisk = Storage::disk('public');
        $normalizedFilename = basename($filename);

        if ($publicDisk->exists($filename)) {
            $publicDisk->delete($filename);
            return true;
        }

        foreach (self::FORUM_IMAGE_DIRECTORIES as $directory) {
            $path = $directory . $normalizedFilename;

            if ($publicDisk->exists($path)) {
                $publicDisk->delete($path);
                return true;
            }
        }

        foreach (self::FORUM_IMAGE_DIRECTORIES as $directory) {
            $legacyPublicPath = public_path($directory . $normalizedFilename);

            if (file_exists($legacyPublicPath)) {
                @unlink($legacyPublicPath);
                return true;
            }
        }

        return false;
    }
}
