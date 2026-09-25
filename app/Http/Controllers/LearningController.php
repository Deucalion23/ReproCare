<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\LearningMaterial;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class LearningController extends Controller
{
    private function getCurrentUser()
    {
        return auth()->user();
    }

    public function __construct()
    {
        $this->middleware('auth');
    }

    private function storePublicUpload(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    private function deletePublicUpload(?string $path): void
    {
        if ($path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }
    }

    // Index - View all materials (for users, midwives, BHWs)
    public function index(Request $request)
    {
        $query = LearningMaterial::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        
        // Filter by type
        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'video') {
                $query->where(function ($q) {
                    $q->where('material_type', 'video')
                      ->orWhereNotNull('video_url');
                });
            } else {
                $query->where('material_type', $type);
            }
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        $materials = $query->latest()->paginate(12)->withQueryString();
        return view('learning.index', compact('materials'));
    }

    // Show single material with embedded playable media
    public function show($id)
    {
        $material = LearningMaterial::findOrFail($id);
        $relatedMaterials = LearningMaterial::where('id', '!=', $id)
            ->where(function($q) use ($material) {
                $q->where('category', $material->category)
                  ->orWhere('material_type', $material->material_type);
            })
            ->limit(4)
            ->get();

        return view('learning.show', compact('material', 'relatedMaterials'));
    }

    // Admin functions for midwives & CHO
    public function adminIndex(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user || (!$user->isMidwife() && !$user->isCho())) {
            abort(403, 'Unauthorized access.');
        }

        $query = LearningMaterial::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('material_type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $materials = $query->latest()->paginate(10)->withQueryString();
        return view('midwife.learning.index', compact('materials'));
    }

    public function create()
    {
        $user = $this->getCurrentUser();
        if (!$user || (!$user->isMidwife() && !$user->isCho())) {
            abort(403, 'Unauthorized access.');
        }

        return view('midwife.learning.create');
    }

    public function store(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user || (!$user->isMidwife() && !$user->isCho())) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'title'         => 'required|string|max:255',
            'content'       => 'required|string',
            'material_type' => 'required|in:article,link,file,video,quiz',
            'link_url'      => 'nullable|url|required_if:material_type,link',
            'video_url'     => [
                'nullable',
                'url',
                'required_if:material_type,video',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->material_type === 'video' && $value && !LearningMaterial::extractYoutubeId($value)) {
                        $fail('The YouTube Video Link must be a valid YouTube watch, Shorts, share, or embed URL.');
                    }
                },
            ],
            'category'      => 'nullable|string|max:100',
            'week_number'   => 'nullable|integer|min:1|max:42',
            'quiz_data'     => 'nullable|json',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'file'          => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp3,wav,pdf,doc,docx,xls,xlsx,ppt,pptx,txt|max:102400', // 100MB max (YouTube embeds replace raw MP4 uploads)
        ]);

        $data = $request->except('image', 'file', 'quiz_data');
        $data['category']   = $request->category ?? 'general';
        $data['quiz_data']  = $request->quiz_data ? json_decode($request->quiz_data, true) : null;
        if ($request->has('video_url')) {
            $data['youtube_id'] = LearningMaterial::extractYoutubeId($request->video_url);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $this->storePublicUpload($request->file('image'), 'learning_materials');
        }

        if ($request->hasFile('file')) {
            $data['file'] = $this->storePublicUpload($request->file('file'), 'learning_files');
        }

        $material = LearningMaterial::create($data);
        ActivityLog::log('create', "Created learning material/video: {$material->title}");

        return redirect()->route('midwife.learning.index')
            ->with('success', 'Learning material / playable video published successfully');
    }

    public function edit($id)
    {
        $user = $this->getCurrentUser();
        if (!$user || (!$user->isMidwife() && !$user->isCho())) {
            abort(403, 'Unauthorized access.');
        }

        $material = LearningMaterial::findOrFail($id);
        return view('midwife.learning.edit', compact('material'));
    }

    public function update(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user || (!$user->isMidwife() && !$user->isCho())) {
            abort(403, 'Unauthorized access.');
        }

        $material = LearningMaterial::findOrFail($id);

        $request->validate([
            'title'         => 'required|string|max:255',
            'content'       => 'required|string',
            'material_type' => 'required|in:article,link,file,video,quiz',
            'link_url'      => 'nullable|url|required_if:material_type,link',
            'video_url'     => [
                'nullable',
                'url',
                'required_if:material_type,video',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->material_type === 'video' && $value && !LearningMaterial::extractYoutubeId($value)) {
                        $fail('The YouTube Video Link must be a valid YouTube watch, Shorts, share, or embed URL.');
                    }
                },
            ],
            'category'      => 'nullable|string|max:100',
            'week_number'   => 'nullable|integer|min:1|max:42',
            'quiz_data'     => 'nullable|json',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'file'          => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp3,wav,pdf,doc,docx,xls,xlsx,ppt,pptx,txt|max:102400', // 100MB max (YouTube embeds replace raw MP4 uploads)
        ]);

        $data = $request->except('image', 'file', 'quiz_data');
        $data['category']   = $request->category ?? $material->category;
        $data['quiz_data']  = $request->filled('quiz_data') ? json_decode($request->quiz_data, true) : null;
        if ($request->has('video_url')) {
            $data['youtube_id'] = LearningMaterial::extractYoutubeId($request->video_url);
        }

        if ($request->hasFile('image')) {
            $this->deletePublicUpload($material->image);
            $data['image'] = $this->storePublicUpload($request->file('image'), 'learning_materials');
        }

        if ($request->hasFile('file')) {
            $this->deletePublicUpload($material->file);
            $data['file'] = $this->storePublicUpload($request->file('file'), 'learning_files');
        }

        $material->update($data);
        ActivityLog::log('update', "Updated learning material/video: {$material->title}");

        return redirect()->route('midwife.learning.index')
            ->with('success', 'Learning material updated successfully');
    }

    // Soft delete (archive) - NO HARD DELETES
    public function destroy($id)
    {
        $user = $this->getCurrentUser();
        if (!$user || (!$user->isMidwife() && !$user->isCho())) {
            abort(403, 'Unauthorized access.');
        }

        $material = LearningMaterial::findOrFail($id);
        $title = $material->title;
        $reason = trim((string) request()->input('reason', ''));
        if ($reason === '') {
            $reason = 'Learning material archived via console';
        }

        try {
            app(\App\Services\ArchiveService::class)->archiveRecord($material, $reason, $user);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['reason' => $e->getMessage()])->withInput();
        }

        return redirect()->route('midwife.learning.index')
            ->with('success', "Learning material '{$title}' moved to archives.");
    }

    // Filter by type
    public function articles()
    {
        $materials = LearningMaterial::articles()->latest()->paginate(12);
        return view('learning.index', compact('materials'));
    }

    public function videos()
    {
        $materials = LearningMaterial::videos()->latest()->paginate(12);
        return view('learning.index', compact('materials'));
    }

    public function links()
    {
        $materials = LearningMaterial::links()->latest()->paginate(12);
        return view('learning.index', compact('materials'));
    }

    public function files()
    {
        $materials = LearningMaterial::files()->latest()->paginate(12);
        return view('learning.index', compact('materials'));
    }

    public function hcwTraining()
    {
        $materials = LearningMaterial::hcwTraining()->latest()->paginate(12);
        return view('learning.index', compact('materials'));
    }

    // Quiz submission
    public function submitQuiz(Request $request, $id)
    {
        $material = LearningMaterial::whereNotNull('quiz_data')->findOrFail($id);
        $questions = $material->quiz_data ?? [];
        $answers   = $request->input('answers', []);
        $score = 0;
        $total = count($questions);
        $results = [];

        foreach ($questions as $i => $q) {
            $correct = $q['answer'] ?? null;
            $given   = $answers[$i] ?? null;
            $isRight = ($given === $correct);
            if ($isRight) $score++;
            $results[] = [
                'question' => $q['question'],
                'correct'  => $correct,
                'given'    => $given,
                'is_right' => $isRight,
            ];
        }

        return view('learning.quiz-result', compact('material', 'score', 'total', 'results'));
    }

    // Pregnancy week-by-week guide
    public function weekGuide($week)
    {
        $week      = (int)$week;
        $materials = LearningMaterial::weekGuide($week)->latest()->get();
        return view('learning.week-guide', compact('materials', 'week'));
    }

    // BHW Learning Materials
    public function bhwIndex(Request $request)
    {
        if (!$this->getCurrentUser()?->isBhw()) {
            abort(403);
        }

        $query = LearningMaterial::query();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        if ($request->filled('type')) {
            $query->where('material_type', $request->type);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $materials = $query->latest()->paginate(12)->withQueryString();
        return view('learning.index', compact('materials'));
    }

    public function bhwShow($id)
    {
        if (!$this->getCurrentUser()?->isBhw()) {
            abort(403);
        }
        $material = LearningMaterial::findOrFail($id);
        return view('learning.show', compact('material'));
    }
}
