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

    public function create() { return redirect()->route('ai-prompts.index'); }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'type_id'     => 'required|exists:types,id',
            'prompt'      => 'required|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
        ]);

        $data = $request->only(['category_id', 'type_id', 'prompt', 'description']);
        $data['slug'] = Str::slug(Str::limit($request->prompt, 50)) . '-' . rand(1000, 9999);

        if ($request->hasFile('image')) {
            $paths = $this->imageService->compressAndStore($request->file('image'), 'prompts');
            $data['image'] = $paths['original'];
            $data['compressed_image'] = $paths['compressed'];
        }

        AiPrompt::create($data);
        return redirect()->route('ai-prompts.index')->with('success', 'AI Prompt created successfully.');
    }

    public function edit(AiPrompt $aiPrompt) { return redirect()->route('ai-prompts.index'); }

    public function update(Request $request, AiPrompt $aiPrompt)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'type_id'     => 'required|exists:types,id',
            'prompt'      => 'required|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
        ]);

        $data = $request->only(['category_id', 'type_id', 'prompt', 'description']);

        if ($request->prompt !== $aiPrompt->prompt) {
            $data['slug'] = Str::slug(Str::limit($request->prompt, 50)) . '-' . rand(1000, 9999);
        }
        if ($request->hasFile('image')) {
            $this->imageService->deleteImages($aiPrompt->image, $aiPrompt->compressed_image);
            $paths = $this->imageService->compressAndStore($request->file('image'), 'prompts');
            $data['image'] = $paths['original'];
            $data['compressed_image'] = $paths['compressed'];
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
