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

        $query = ForumPost::active()
            ->with([
                'user',
                'comments' => function($query) {
                    $query->with('user');
                    $query->latest()->take(3);
                },
            ])
            ->withCount(['likes as likes_count', 'comments as comments_count']);
        
        // Filter by search query
        $search = request('search');
        if ($search) {
            $query->where('content', 'like', '%' . $search . '%');
        }

        // Filter by user's own posts if requested
        $filter = request('filter');
        if ($filter === 'my-posts') {
            $query->where('user_id', $currentUser->id);
        }

        $posts = $query->latest()->paginate(10)->withQueryString();
        $userType = $this->getUserType();

        // Use different views based on user role
        if ($userType === 'midwife') {
            return view('midwife.forum.index', compact('posts', 'filter', 'search'));
        } elseif ($userType === 'bhw' || $userType === 'bhw-president') {
            return view('bhw.forum.index', compact('posts', 'filter', 'search'));
        }

        return view('forum.index', compact('posts', 'filter', 'search'));
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

        $userType = $this->getUserType();
        
        // Use different views based on user role
        if ($userType === 'midwife') {
            return view('midwife.forum.show', compact('post'));
        } elseif ($userType === 'bhw' || $userType === 'bhw-president') {
            return view('bhw.forum.show', compact('post'));
        }

        return view('forum.show', compact('post'));
    }

    // Create post form
    public function create()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) return redirect()->route('login');

        $userType = $this->getUserType();

        // Use different views based on user role
        if ($userType === 'midwife') {
            return view('midwife.forum.create');
        } elseif ($userType === 'bhw' || $userType === 'bhw-president') {
            return view('bhw.forum.create');
        }

        return view('forum.create');
    }

    // Store new post
    public function store(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) return redirect()->route('login');

        $request->validate([
            'content' => 'required|string|max:2000',
            'post_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
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

        $userType = $this->getUserType();

        // Use different views based on user role
        if ($userType === 'midwife') {
            return view('midwife.forum.edit', compact('post'));
        } elseif ($userType === 'bhw' || $userType === 'bhw-president') {
            return view('bhw.forum.edit', compact('post'));
        }

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
            'post_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'remove_post_image' => 'nullable|boolean',
        ]);

        $updateData = ['content' => $request->content];
        $removePostImage = $request->boolean('remove_post_image');

        if ($removePostImage) {
            $this->deletePostImage($post->post_image);
            $updateData['post_image'] = null;
        }

        // Handle image upload
        if ($request->hasFile('post_image')) {
            // Delete old image if exists
            $this->deletePostImage($post->post_image);

            $image = $request->file('post_image');
            $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('uploads/forum', $filename, 'public');
            $updateData['post_image'] = $filename;
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
    public function like($postId)
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

        return redirect()->back();
    }

    // Admin functions for midwives
    public function adminIndex()
    {
        if (!auth()->check() || auth()->user()->role !== 'midwife') {
            abort(403);
        }

        $posts = ForumPost::with(['user', 'comments', 'likes'])
            ->active()
            ->latest()
            ->paginate(20);

        return view('forum.admin.index', compact('posts'));
    }

    // Admin create method
    public function adminCreate()
    {
        if (!auth()->check() || auth()->user()->role !== 'midwife') {
            abort(403);
        }

        return view('forum.admin.create');
    }

    // Admin store method
    public function adminStore(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'midwife') {
            abort(403);
        }

        $currentUser = auth()->user();

        $request->validate([
            'content' => 'required|string|max:1000',
            'post_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $data = [
            'content' => $request->content,
            'status' => 'active',
            'user_id' => $currentUser->id,
        ];

        // Handle image upload
        if ($request->hasFile('post_image')) {
            $image = $request->file('post_image');
            $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('uploads/forum', $filename, 'public');
            $data['post_image'] = $filename;
        }

        $post = ForumPost::create($data);

        // Broadcast notification to all users about the new forum post.
        // Idempotent per post so retries/double-clicks cannot spam inboxes.
        $allUsers = \App\Models\User::where('role', 'user')->pluck('id');
        foreach ($allUsers as $userId) {
            \App\Models\Notification::firstOrCreate(
                ['user_id' => $userId, 'event_key' => 'forum:'.$post->id],
                [
                    'title' => 'New Forum Post - Maternal Care',
                    'message' => 'New maternal care education post available in the forum.',
                    'type' => 'info',
                    'category' => 'forum',
                    'action_url' => route('forum.show', $post->id),
                    'is_read' => false,
                ]
            );
        }

        return redirect()->route('midwife.forum.admin.index')
            ->with('success', 'Post created and broadcasted to all users successfully');
    }

    public function restorePost($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'midwife') {
            abort(403);
        }

        $post = ForumPost::findOrFail($id);
        $post->status = 'active';
        $post->save();

        return redirect()->back()
            ->with('success', 'Post restored successfully');
    }

    // Admin show method
    public function adminShow($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'midwife') {
            abort(403);
        }

        $post = ForumPost::with([
            'user',
            'comments.user',
            'likes.user',
        ])->findOrFail($id);
        return view('forum.admin.show', compact('post'));
    }

    // Admin edit method
    public function adminEdit($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'midwife') {
            abort(403);
        }

        $currentUser = auth()->user();
        $post = ForumPost::findOrFail($id);

        return view('forum.admin.edit', compact('post'));
    }

    // Admin update method
    public function adminUpdate(Request $request, $id)
    {
        if (!auth()->check() || auth()->user()->role !== 'midwife') {
            abort(403);
        }

        $currentUser = auth()->user();

        $request->validate([
            'content' => 'required|string|max:1000',
            'post_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'remove_post_image' => 'nullable|boolean',
        ]);

        $post = ForumPost::findOrFail($id);

        $updateData = ['content' => $request->content];

        if ($request->boolean('remove_post_image')) {
            $this->deletePostImage($post->post_image);
            $updateData['post_image'] = null;
        }

        if ($request->hasFile('post_image')) {
            $this->deletePostImage($post->post_image);

            $image = $request->file('post_image');
            $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('uploads/forum', $filename, 'public');
            $updateData['post_image'] = $filename;
        }

        $post->update($updateData);

        return redirect()->route('midwife.forum.admin.index')
            ->with('success', 'Post updated successfully');
    }

    // Admin destroy method
    public function adminDestroy($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'midwife') {
            abort(403);
        }

        $currentUser = auth()->user();
        $post = ForumPost::findOrFail($id);

        $this->deletePostImage($post->post_image);

        // Midwives can soft delete any post
        $post->softDelete();
        return redirect()->route('midwife.forum.admin.index')
            ->with('success', 'Post deleted successfully');
    }

    public function bulkDelete(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'midwife') {
            abort(403);
        }

        $request->validate([
            'posts' => 'required|array',
            'posts.*' => 'integer|exists:forum_posts,id',
        ]);

        $posts = ForumPost::whereIn('id', $request->posts)->get();

        foreach ($posts as $post) {
            $this->deletePostImage($post->post_image);
            $post->softDelete();
        }

        return redirect()->route('midwife.forum.admin.index')
            ->with('success', $posts->count() . ' post(s) deleted successfully');
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
