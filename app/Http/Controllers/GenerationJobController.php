<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GenerationJobController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\GenerationJob::with(['user:id,name,email', 'aiModel:id,name,slug']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $jobs = $query->latest()->paginate(20)->withQueryString();
        
        return view('generation_jobs.index', compact('jobs'));
    }

    public function create() { return redirect()->route('generation-jobs.index'); }
    public function store(Request $request) { return redirect()->route('generation-jobs.index'); }
    
    public function show(string $id)
    {
        $job = \App\Models\GenerationJob::with(['user', 'aiModel'])->findOrFail($id);
        return view('generation_jobs.show', compact('job'));
    }

    public function edit(string $id) { return redirect()->route('generation-jobs.index'); }
    public function update(Request $request, string $id) { return redirect()->route('generation-jobs.index'); }

    public function destroy(string $id)
    {
        \App\Models\GenerationJob::findOrFail($id)->delete();
        return redirect()->route('generation-jobs.index')->with('success', 'Job deleted');
    }
}
