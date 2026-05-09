<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiModelApiController extends Controller
{
    public function index()
    {
        try {
            $aiModels = AiModel::all()->map(function ($aiModel) {
                return [
                    'id' => $aiModel->id,
                    'name' => $aiModel->name,
                    'slug' => $aiModel->slug,
                    'image' => $aiModel->image,
                    'description' => $aiModel->description,
                    'api_key_masked' => $aiModel->api_key ? Str::mask($aiModel->api_key, '*', 4) : null,
                ];
            });
            return response()->json(['success' => true, 'data' => $aiModels], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch AI models'], 500);
        }
    }

    public function show($id)
    {
        try {
            $aiModel = AiModel::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $aiModel->id,
                    'name' => $aiModel->name,
                    'slug' => $aiModel->slug,
                    'image' => $aiModel->image ? asset('storage/' . $aiModel->image) : null,
                    'description' => $aiModel->description,
                    'api_key_masked' => $aiModel->api_key ? Str::mask($aiModel->api_key, '*', 4) : null,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'AI Model not found'], 404);
        }
    }
}
