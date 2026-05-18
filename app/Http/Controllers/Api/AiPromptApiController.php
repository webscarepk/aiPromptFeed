<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiPrompt;
use Illuminate\Http\Request;

class AiPromptApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = AiPrompt::with(['category', 'type']);

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('type_id')) {
                $query->where('type_id', $request->type_id);
            }

            if ($request->filled('search')) {
                $query->where('prompt', 'like', '%' . $request->search . '%');
            }

            $prompts = $query->latest()->paginate(20);

            $prompts->getCollection()->transform(function ($prompt) {
                return [
                    'id' => $prompt->id,
                    'prompt' => $prompt->prompt,
                    'description' => $prompt->description,
                    'image' => $prompt->image ? asset('storage/' . $prompt->image) : null,
                    'compressed_image' => $prompt->compressed_image ? asset('storage/' . $prompt->compressed_image) : null,
                    'slug' => $prompt->slug,
                    'category' => $prompt->category ? [
                        'id' => $prompt->category->id,
                        'name' => $prompt->category->name,
                        'slug' => $prompt->category->slug,
                        'image' => $prompt->category->image ? asset('storage/' . $prompt->category->image) : null,
                        'compressed_image' => $prompt->category->compressed_image ? asset('storage/' . $prompt->category->compressed_image) : null,
                    ] : null,
                    'type' => $prompt->type ? [
                        'id' => $prompt->type->id,
                        'name' => $prompt->type->name,
                        'slug' => $prompt->type->slug,
                        'image' => $prompt->type->image ? asset('storage/' . $prompt->type->image) : null,
                        'compressed_image' => $prompt->type->compressed_image ? asset('storage/' . $prompt->type->compressed_image) : null,
                    ] : null,
                    'created_at' => $prompt->created_at->diffForHumans(),
                ];
            });

            return response()->json(['success' => true, 'data' => $prompts], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch AI prompts'], 500);
        }
    }

    public function show($id)
    {
        try {
            $prompt = AiPrompt::with(['category', 'type'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $prompt->id,
                    'prompt' => $prompt->prompt,
                    'description' => $prompt->description,
                    'image' => $prompt->image ? asset('storage/' . $prompt->image) : null,
                    'compressed_image' => $prompt->compressed_image ? asset('storage/' . $prompt->compressed_image) : null,
                    'slug' => $prompt->slug,
                    'category' => $prompt->category ? [
                        'id' => $prompt->category->id,
                        'name' => $prompt->category->name,
                        'slug' => $prompt->category->slug,
                        'image' => $prompt->category->image ? asset('storage/' . $prompt->category->image) : null,
                        'compressed_image' => $prompt->category->compressed_image ? asset('storage/' . $prompt->category->compressed_image) : null,
                    ] : null,
                    'type' => $prompt->type ? [
                        'id' => $prompt->type->id,
                        'name' => $prompt->type->name,
                        'slug' => $prompt->type->slug,
                        'image' => $prompt->type->image ? asset('storage/' . $prompt->type->image) : null,
                        'compressed_image' => $prompt->type->compressed_image ? asset('storage/' . $prompt->type->compressed_image) : null,
                    ] : null,
                    'created_at' => $prompt->created_at->toDateTimeString(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'AI Prompt not found'], 404);
        }
    }

    public function similar($id)
    {
        try {
            $prompt = AiPrompt::findOrFail($id);
            
            $similarPrompts = AiPrompt::with(['category', 'type'])
                ->where('id', '!=', $prompt->id)
                ->where(function ($query) use ($prompt) {
                    if ($prompt->category_id) {
                        $query->where('category_id', $prompt->category_id);
                    }
                    if ($prompt->type_id) {
                        $query->orWhere('type_id', $prompt->type_id);
                    }
                })
                ->latest()
                ->take(6)
                ->get();
                
            $formatted = $similarPrompts->map(function ($p) {
                return [
                    'id' => $p->id,
                    'prompt' => $p->prompt,
                    'description' => $p->description,
                    'image' => $p->image ? asset('storage/' . $p->image) : null,
                    'compressed_image' => $p->compressed_image ? asset('storage/' . $p->compressed_image) : null,
                    'slug' => $p->slug,
                    'category' => $p->category ? [
                        'id' => $p->category->id,
                        'name' => $p->category->name,
                        'slug' => $p->category->slug,
                        'image' => $p->category->image ? asset('storage/' . $p->category->image) : null,
                        'compressed_image' => $p->category->compressed_image ? asset('storage/' . $p->category->compressed_image) : null,
                    ] : null,
                    'type' => $p->type ? [
                        'id' => $p->type->id,
                        'name' => $p->type->name,
                        'slug' => $p->type->slug,
                        'image' => $p->type->image ? asset('storage/' . $p->type->image) : null,
                        'compressed_image' => $p->type->compressed_image ? asset('storage/' . $p->type->compressed_image) : null,
                    ] : null,
                    'created_at' => $p->created_at->diffForHumans(),
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $formatted
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch similar prompts'], 500);
        }
    }
}
