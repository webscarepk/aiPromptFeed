<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Http\Request;

class TypeApiController extends Controller
{
    public function index()
    {
        try {
            $types = Type::all()->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                    'image' => $type->image ? asset('storage/' . $type->image) : null,
                    'compressed_image' => $type->compressed_image ? asset('storage/' . $type->compressed_image) : null,
                    'description' => $type->description,
                ];
            });
            return response()->json(['success' => true, 'data' => $types], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch types'], 500);
        }
    }

    public function show($id)
    {
        try {
            $type = Type::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                    'image' => $type->image ? asset('storage/' . $type->image) : null,
                    'compressed_image' => $type->compressed_image ? asset('storage/' . $type->compressed_image) : null,
                    'description' => $type->description,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Type not found'], 404);
        }
    }
}
