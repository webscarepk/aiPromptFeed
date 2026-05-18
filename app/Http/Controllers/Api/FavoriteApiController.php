<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiPrompt;
use Illuminate\Http\Request;

class FavoriteApiController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()->favoritePrompts()->paginate(15);
        return response()->json($favorites);
    }

    public function toggle(Request $request, AiPrompt $aiPrompt)
    {
        $user = $request->user();

        $user->favoritePrompts()->toggle($aiPrompt->id);

        $isFavorited = $user->favoritePrompts()->where('ai_prompt_id', $aiPrompt->id)->exists();

        return response()->json([
            'message' => $isFavorited ? 'Prompt added to favorites.' : 'Prompt removed from favorites.',
            'is_favorited' => $isFavorited
        ]);
    }
}
