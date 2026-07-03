@extends('layouts.admin', ['pageTitle' => 'Income Categories'])

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Income Categories</h1>
            <p class="text-on-surface-variant mt-1">Manage B40/M40/T20 household income thresholds for scholarship matching</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openCreateModal()">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Category
        </button>
    </div>

    @if($categories->isEmpty())
        <div class="card p-10 text-center">
            <svg class="w-16 h-16 mx-auto text-on-surface-variant/50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0l1-1m-1 1l-1-1"></path></svg>
            <h3 class="text-lg font-medium text-on-surface mb-1">No Income Categories</h3>
            <p class="text-on-surface-variant mb-6">Create your first income category to get started.</p>
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">Add Category</button>
        </div>
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-container-low">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Min Income (RM)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Max Income (RM)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Range</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-on-surface-variant uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/50">
                        @foreach($categories as $category)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-4 py-4">
                                    <div class="font-medium text-on-surface">{{ $category->name }}</div>
                                </td>
                                <td class="px-4 py-4 text-on-surface">{{ number_format($category->min_income, 2) }}</td>
                                <td class="px-4 py-4 text-on-surface">
                                    @if($category->max_income !== null)
                                        {{ number_format($category->max_income, 2) }}
                                    @else
                                        <span class="text-on-surface-variant">No upper limit</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if($category->max_income !== null)
                                        <span class="text-sm text-on-surface-variant">RM {{ number_format($category->min_income, 2) }} – {{ number_format($category->max_income, 2) }}</span>
                                    @else
                                        <span class="text-sm text-on-surface-variant">≥ RM {{ number_format($category->min_income, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                            class="btn btn-outline text-sm px-3 py-1.5"
                                            onclick="openEditModal({{ $category->id }}, '{{ $category->name }}', {{ $category->min_income }}, {{ $category->max_income ?? 'null' }})">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.income-categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Delete {{ $category->name }} category? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-ghost text-sm px-3 py-1.5 text-error hover:bg-error/10">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

                    </div>
    @endif
</div>

<!-- Create Modal -->
<div id="create-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-5 border-b border-outline-variant flex items-center justify-between">
            <h3 class="text-lg font-semibold text-on-surface">Add Income Category</h3>
            <button type="button" class="p-2 rounded-md hover:bg-surface-container-low" onclick="closeCreateModal()" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.income-categories.store') }}" id="create-form" class="p-5 space-y-4">
            @csrf

            <div>
                <label for="create_name" class="label">Category Name <span class="text-error">*</span></label>
                <input type="text" id="create_name" name="name" class="input @error('name') input-error @enderror"
                    required maxlength="255" placeholder="e.g., B40, M40, T20, Custom">
                @error('name')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="create_min_income" class="label">Minimum Income (RM) <span class="text-error">*</span></label>
                <input type="number" id="create_min_income" name="min_income" step="0.01" min="0" class="input @error('min_income') input-error @enderror"
                    required placeholder="0.00">
                @error('min_income')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="create_max_income" class="label">Maximum Income (RM)</label>
                <input type="number" id="create_max_income" name="max_income" step="0.01" min="0" class="input @error('max_income') input-error @enderror"
                    placeholder="Optional - leave empty for no upper limit">
                @error('max_income')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-on-surface-variant">Leave empty for categories like T20 with no upper limit.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant">
                <button type="button" class="btn btn-outline" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-5 border-b border-outline-variant flex items-center justify-between">
            <h3 class="text-lg font-semibold text-on-surface">Edit Income Category</h3>
            <button type="button" class="p-2 rounded-md hover:bg-surface-container-low" onclick="closeEditModal()" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form method="POST" action="" id="edit-form" class="p-5 space-y-4">
            @csrf
            @method('PATCH')

            <input type="hidden" id="edit_id" name="id">

            <div>
                <label for="edit_name" class="label">Category Name <span class="text-error">*</span></label>
                <input type="text" id="edit_name" name="name" class="input @error('name') input-error @enderror"
                    required maxlength="255">
                @error('name')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="edit_min_income" class="label">Minimum Income (RM) <span class="text-error">*</span></label>
                <input type="number" id="edit_min_income" name="min_income" step="0.01" min="0" class="input @error('min_income') input-error @enderror"
                    required>
                @error('min_income')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="edit_max_income" class="label">Maximum Income (RM)</label>
                <input type="number" id="edit_max_income" name="max_income" step="0.01" min="0" class="input @error('max_income') input-error @enderror"
                    placeholder="Optional - leave empty for no upper limit">
                @error('max_income')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-on-surface-variant">Leave empty for categories like T20 with no upper limit.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant">
                <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Category</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('create-modal').classList.remove('hidden');
    document.getElementById('create-modal').classList.add('flex');
    document.getElementById('create_name').focus();
}

function closeCreateModal() {
    document.getElementById('create-modal').classList.add('hidden');
    document.getElementById('create-modal').classList.remove('flex');
    document.getElementById('create-form').reset();
}

function openEditModal(id, name, minIncome, maxIncome) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_min_income').value = minIncome;
    document.getElementById('edit_max_income').value = maxIncome === null ? '' : maxIncome;
    document.getElementById('edit-form').action = '{{ route('admin.income-categories.update', ':id') }}'.replace(':id', id);
    document.getElementById('edit-modal').classList.remove('hidden');
    document.getElementById('edit-modal').classList.add('flex');
    document.getElementById('edit_name').focus();
}

function closeEditModal() {
    document.getElementById('edit-modal').classList.add('hidden');
    document.getElementById('edit-modal').classList.remove('flex');
    document.getElementById('edit-form').reset();
}

// Close modals on backdrop click
document.getElementById('create-modal')?.addEventListener('click', (e) => {
    if (e.target === document.getElementById('create-modal')) closeCreateModal();
});
document.getElementById('edit-modal')?.addEventListener('click', (e) => {
    if (e.target === document.getElementById('edit-modal')) closeEditModal();
});

// Close modals on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeCreateModal();
        closeEditModal();
    }
});
</script>
@endsection