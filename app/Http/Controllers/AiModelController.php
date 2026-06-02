<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AiModelController extends Controller
{
    public function index(Request $request)
    {
        $query = AiModel::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $aiModels = $query->latest()->paginate(10)->withQueryString();
        return view('ai_models.index', compact('aiModels'));
    }

    public function create() { return redirect()->route('ai-models.index'); }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'api_key' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:200048',
            'api_endpoint' => 'nullable|string|url',
            'webhook_secret' => 'nullable|string',
            'cost_per_use' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $data = $request->only(['name', 'description', 'api_key', 'api_endpoint', 'webhook_secret', 'cost_per_use', 'is_active']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->has('is_active') ? $request->is_active : false;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('ai_models', 'public');
        }

        AiModel::create($data);
        return redirect()->route('ai-models.index')->with('success', 'AI Model created successfully.');
    }

    public function edit(AiModel $aiModel) { return redirect()->route('ai-models.index'); }

    public function update(Request $request, AiModel $aiModel)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'api_key' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:200048',
            'api_endpoint' => 'nullable|string|url',
            'webhook_secret' => 'nullable|string',
            'cost_per_use' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $data = $request->only(['name', 'description', 'api_key', 'api_endpoint', 'webhook_secret', 'cost_per_use', 'is_active']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->has('is_active') ? $request->is_active : false;

        if ($request->hasFile('image')) {
            if ($aiModel->image) {
                Storage::disk('public')->delete($aiModel->image);
            }
            $data['image'] = $request->file('image')->store('ai_models', 'public');
        }

        $aiModel->update($data);
        return redirect()->route('ai-models.index')->with('success', 'AI Model updated successfully.');
    }

    public function destroy(AiModel $aiModel)
    {
        if ($aiModel->image) {
            Storage::disk('public')->delete($aiModel->image);
        }
        $aiModel->delete();
        return redirect()->route('ai-models.index')->with('success', 'AI Model deleted.');
    }
}
