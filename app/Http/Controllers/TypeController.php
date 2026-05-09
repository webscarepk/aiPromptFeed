<?php

namespace App\Http\Controllers;

use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Services\ImageService;

class TypeController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    public function index(Request $request)
    {
        $query = Type::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $types = $query->latest()->paginate(10)->withQueryString();
        return view('types.index', compact('types'));
    }

    public function create() { return redirect()->route('types.index'); }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:types,name',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $data = ['name' => $request->name, 'slug' => Str::slug($request->name), 'description' => $request->description];
        if ($request->hasFile('image')) {
            $paths = $this->imageService->compressAndStore($request->file('image'), 'types');
            $data['image'] = $paths['original'];
            $data['compressed_image'] = $paths['compressed'];
        }
        Type::create($data);
        return redirect()->route('types.index')->with('success', 'Type created successfully.');
    }

    public function edit(Type $type) { return redirect()->route('types.index'); }

    public function update(Request $request, Type $type)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:types,name,' . $type->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $data = ['name' => $request->name, 'slug' => Str::slug($request->name), 'description' => $request->description];
        if ($request->hasFile('image')) {
            $this->imageService->deleteImages($type->image, $type->compressed_image);
            $paths = $this->imageService->compressAndStore($request->file('image'), 'types');
            $data['image'] = $paths['original'];
            $data['compressed_image'] = $paths['compressed'];
        }
        $type->update($data);
        return redirect()->route('types.index')->with('success', 'Type updated successfully.');
    }

    public function destroy(Type $type)
    {
        $this->imageService->deleteImages($type->image, $type->compressed_image);
        $type->delete();
        return redirect()->route('types.index')->with('success', 'Type deleted.');
    }
}
