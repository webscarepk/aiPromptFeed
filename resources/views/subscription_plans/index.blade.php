@extends('layouts.app')
@section('page-title', 'Subscription Plans')

@section('content')
<div x-data="planModal()">
    <div class="card p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-white">Subscription Plans</h2>
            <button @click="openCreate()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                + Add Plan
            </button>
        </div>
        
        <table class="w-full text-left text-sm text-gray-400">
            <thead class="bg-gray-800 text-gray-300">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Price (Cents)</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Allocations</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plans as $plan)
                <tr class="border-b border-gray-700 hover:bg-gray-800/50">
                    <td class="px-4 py-3 text-white font-medium">{{ $plan->name }}</td>
                    <td class="px-4 py-3">{{ $plan->price_usd_cents }}</td>
                    <td class="px-4 py-3">
                        @if($plan->is_active)
                            <span class="px-2 py-1 text-xs bg-green-900/50 text-green-400 rounded border border-green-800">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs bg-red-900/50 text-red-400 rounded border border-red-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs">
                        @foreach($plan->creditAllocations as $alloc)
                            <div class="mb-1">{{ $alloc->aiModel->name ?? 'Unknown' }}: <span class="text-white">{{ $alloc->credits_granted }}</span> cr</div>
                        @endforeach
                    </td>
                    <td class="px-4 py-3 text-right">
                        @php
                            $allocs = $plan->creditAllocations->pluck('credits_granted', 'model_id')->toJson();
                        @endphp
                        <button @click="openEdit({{ $plan->id }}, '{{ addslashes($plan->name) }}', {{ $plan->price_usd_cents }}, {{ $plan->is_active ? 'true' : 'false' }}, {{ $allocs }})" class="text-blue-400 hover:text-blue-300 mr-3">Edit</button>
                        <form action="{{ route('subscription-plans.destroy', $plan) }}" method="POST" class="inline" onsubmit="return confirm('Delete this plan?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $plans->links() }}</div>
    </div>

    <!-- Modal -->
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4" style="display:none;" @keydown.escape.window="open = false">
        <div class="w-full max-w-lg bg-gray-900 border border-gray-700 rounded-xl max-h-[90vh] overflow-y-auto">
            <div class="p-5 border-b border-gray-800 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white" x-text="isEdit ? 'Edit Plan' : 'New Plan'"></h3>
                <button @click="open = false" class="text-gray-400 hover:text-white">&times;</button>
            </div>
            
            <form :action="isEdit ? '/subscription-plans/' + editId : '{{ route('subscription-plans.store') }}'" method="POST" class="p-5 space-y-4">
                @csrf
                <input type="hidden" name="_method" :value="isEdit ? 'PUT' : 'POST'">
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Plan Name</label>
                    <input type="text" name="name" x-model="form.name" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-400 mb-1">Price (Cents)</label>
                    <input type="number" name="price_usd_cents" x-model="form.price_usd_cents" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white" required>
                </div>

                <div class="pt-2 border-t border-gray-800">
                    <h4 class="text-sm font-semibold text-white mb-2">Model Credit Allocations</h4>
                    <p class="text-xs text-gray-500 mb-3">How many credits does this pack grant?</p>
                    
                    @foreach($aiModels as $model)
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm text-gray-300">{{ $model->name }}</label>
                        <input type="number" name="allocations[{{ $model->id }}]" x-model="form.allocations[{{ $model->id }}]" class="w-24 bg-gray-800 border border-gray-700 rounded-lg px-2 py-1 text-white text-sm" placeholder="0" min="0">
                    </div>
                    @endforeach
                </div>
                
                <div class="flex items-center pt-2">
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                        <span class="ml-3 text-sm font-medium text-gray-300">Active</span>
                    </label>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-800">
                    <button type="button" @click="open = false" class="px-4 py-2 text-gray-400 hover:text-white mr-3">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Save Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function planModal() {
        return {
            open: false, isEdit: false, editId: null,
            form: { name: '', price_usd_cents: 0, is_active: true, allocations: {} },
            
            openCreate() {
                this.isEdit = false; this.editId = null;
                this.form = { name: '', price_usd_cents: 0, is_active: true, allocations: {} };
                this.open = true;
            },
            openEdit(id, name, price, active, allocs) {
                this.isEdit = true; this.editId = id;
                this.form = { name: name, price_usd_cents: price, is_active: active, allocations: allocs || {} };
                this.open = true;
            }
        }
    }
</script>
@endsection
