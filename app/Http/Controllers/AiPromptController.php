<?php

namespace App\Http\Controllers;

use App\Models\AiPrompt;
use App\Models\Category;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Services\ImageService;

class AiPromptController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    public function index(Request $request)
    {
        $query = AiPrompt::with(['category', 'type'])->latest();

        if ($request->filled('search')) {
            $query->where('prompt', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }

        $aiPrompts  = $query->paginate(12)->withQueryString();
        $categories = Category::all();
        $types      = Type::all();

        return view('ai_prompts.index', compact('aiPrompts', 'categories', 'types'));
    }

    public function create()
    {
        $categories = Category::all();
        $types = Type::all();
        return view('ai_prompts.create', compact('categories', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'type_id'     => 'required|exists:types,id',
            'prompt'      => 'required|string',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'nullable|image',
            'description' => 'nullable|string',
        ]);

        $data = $request->only(['category_id', 'type_id', 'prompt', 'description']);
        $data['slug'] = Str::slug(Str::limit($request->prompt, 50)) . '-' . rand(1000, 9999);

        // Process ALL uploaded images and store paths
        $imagesData = [];
        $uploadedFiles = $request->hasFile('images')
            ? (array) $request->file('images')
            : ($request->hasFile('image') ? [$request->file('image')] : []);

        foreach ($uploadedFiles as $file) {
            if ($file && $file->isValid()) {
                $paths = $this->imageService->compressAndStore($file, 'prompts');
                $imagesData[] = [
                    'original'   => $paths['original'],
                    'compressed' => $paths['compressed'],
                ];
            }
        }

        if (!empty($imagesData)) {
            // First image → legacy single columns (for card grid display)
            $data['image']            = $imagesData[0]['original'];
            $data['compressed_image'] = $imagesData[0]['compressed'];
            // All images → JSON array column
            $data['images_data'] = $imagesData;
        }

        AiPrompt::create($data);
        return redirect()->route('ai-prompts.index')->with('success', 'AI Prompt created successfully.');
    }

    public function edit(AiPrompt $aiPrompt)
    {
        $categories = Category::all();
        $types = Type::all();
        return view('ai_prompts.edit', compact('aiPrompt', 'categories', 'types'));
    }

    public function update(Request $request, AiPrompt $aiPrompt)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'type_id'     => 'required|exists:types,id',
            'prompt'      => 'required|string',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'nullable|image|max:102400',
            'description' => 'nullable|string',
        ]);

        $data = $request->only(['category_id', 'type_id', 'prompt', 'description']);

        if ($request->prompt !== $aiPrompt->prompt) {
            $data['slug'] = Str::slug(Str::limit($request->prompt, 50)) . '-' . rand(1000, 9999);
        }

        // Process ALL uploaded images
        $uploadedFiles = $request->hasFile('images')
            ? (array) $request->file('images')
            : ($request->hasFile('image') ? [$request->file('image')] : []);

        if (!empty($uploadedFiles)) {
            // Delete old images
            $this->imageService->deleteImages($aiPrompt->image, $aiPrompt->compressed_image);
            if (!empty($aiPrompt->images_data)) {
                foreach ($aiPrompt->images_data as $oldImg) {
                    $this->imageService->deleteImages(
                        $oldImg['original'] ?? null,
                        $oldImg['compressed'] ?? null
                    );
                }
            }

            $imagesData = [];
            foreach ($uploadedFiles as $file) {
                if ($file && $file->isValid()) {
                    $paths = $this->imageService->compressAndStore($file, 'prompts');
                    $imagesData[] = [
                        'original'   => $paths['original'],
                        'compressed' => $paths['compressed'],
                    ];
                }
            }

            if (!empty($imagesData)) {
                $data['image']            = $imagesData[0]['original'];
                $data['compressed_image'] = $imagesData[0]['compressed'];
                $data['images_data']      = $imagesData;
            }
        }

        $aiPrompt->update($data);
        return redirect()->route('ai-prompts.index')->with('success', 'AI Prompt updated successfully.');
    }

    public function destroy(AiPrompt $aiPrompt)
    {
        $this->imageService->deleteImages($aiPrompt->image, $aiPrompt->compressed_image);
        $aiPrompt->delete();
        return redirect()->route('ai-prompts.index')->with('success', 'AI Prompt deleted.');
    }
}
