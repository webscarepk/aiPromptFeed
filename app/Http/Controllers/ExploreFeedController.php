<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExploreFeedController extends Controller
{
    public function index()
    {
        $items = \App\Models\ExploreFeedItem::with(['generationJob.user', 'aiModel'])
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->latest()
            ->paginate(15);
        return view('explore_feed.index', compact('items'));
    }

    public function create() { return redirect()->route('explore-feed.index'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'generation_job_id' => 'required|exists:generation_jobs,id',
            'is_featured' => 'boolean',
            'display_order' => 'integer'
        ]);

        $job = \App\Models\GenerationJob::findOrFail($data['generation_job_id']);

        if ($job->status !== 'completed' || !$job->result_image_url) {
            return redirect()->back()->with('error', 'Only completed jobs with results can be added to the explore feed.');
        }

        \App\Models\ExploreFeedItem::create([
            'generation_job_id' => $job->id,
            'model_id' => $job->model_id,
            'prompt' => $job->prompt,
            'image_url' => $job->result_image_url,
            'is_featured' => $request->has('is_featured'),
            'display_order' => $request->input('display_order', 0),
        ]);

        return redirect()->route('explore-feed.index')->with('success', 'Item added to Explore Feed');
    }

    public function show(string $id) { return redirect()->route('explore-feed.index'); }
    public function edit(string $id) { return redirect()->route('explore-feed.index'); }

    public function update(Request $request, string $id)
    {
        $item = \App\Models\ExploreFeedItem::findOrFail($id);
        $data = $request->validate([
            'is_featured' => 'boolean',
            'display_order' => 'integer'
        ]);

        $item->update([
            'is_featured' => $request->has('is_featured'),
            'display_order' => $request->input('display_order', 0),
        ]);

        return redirect()->route('explore-feed.index')->with('success', 'Explore Feed item updated');
    }

    public function destroy(string $id)
    {
        \App\Models\ExploreFeedItem::findOrFail($id)->delete();
        return redirect()->route('explore-feed.index')->with('success', 'Item removed from Explore Feed');
    }
}
