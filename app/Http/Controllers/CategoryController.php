<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
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
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string', 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);
        $data = ['name' => $request->name, 'slug' => Str::slug($request->name), 'description' => $request->description];
        if ($request->hasFile('image')) $data['image'] = $request->file('image')->store('categories', 'public');
        Category::create($data);
        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category) { return redirect()->route('categories.index'); }

    public function update(Request $request, Category $category)
    {
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string', 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);
        $data = ['name' => $request->name, 'slug' => Str::slug($request->name), 'description' => $request->description];
        if ($request->hasFile('image')) {
            if ($category->image) Storage::disk('public')->delete($category->image);
            $data['image'] = $request->file('image')->store('categories', 'public');
        }
        $category->update($data);
        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->image) Storage::disk('public')->delete($category->image);
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }
}
