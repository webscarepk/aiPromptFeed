<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Services\ImageService;

class CategoryController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    public function index(Request $request)
    {
        $query = Category::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $categories = $query->latest()->paginate(10)->withQueryString();
        return view('categories.index', compact('categories'));
    }

    public function create() { return redirect()->route('categories.index'); }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:200048'
        ]);

        $data = ['name' => $request->name, 'slug' => Str::slug($request->name), 'description' => $request->description];
        if ($request->hasFile('image')) {
            $paths = $this->imageService->compressAndStore($request->file('image'), 'categories');
            $data['image'] = $paths['original'];
            $data['compressed_image'] = $paths['compressed'];
        }
        $category = Category::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'data' => $category
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category) { return redirect()->route('categories.index'); }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:200048'
        ]);

        $data = ['name' => $request->name, 'slug' => Str::slug($request->name), 'description' => $request->description];
        if ($request->hasFile('image')) {
            $this->imageService->deleteImages($category->image, $category->compressed_image);
            $paths = $this->imageService->compressAndStore($request->file('image'), 'categories');
            $data['image'] = $paths['original'];
            $data['compressed_image'] = $paths['compressed'];
        }
        $category->update($data);
        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $this->imageService->deleteImages($category->image, $category->compressed_image);
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }
}
