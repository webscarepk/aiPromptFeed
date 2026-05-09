<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExploreFeedItem;
use App\Models\AiPrompt;
use Illuminate\Http\Request;

class ExploreApiController extends Controller
{
    public function index()
    {
        $feed = ExploreFeedItem::with('aiModel:id,name,slug')
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->latest()
            ->paginate(20);

        return response()->json($feed);
    }

    public function prompts()
    {
        $prompts = AiPrompt::latest()->paginate(20);
        return response()->json($prompts);
    }
}
