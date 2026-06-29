@extends('layouts.admin', ['pageTitle' => 'Scholarships'])

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Scholarships</h1>
            <p class="text-on-surface-variant mt-1">Manage scholarship listings and eligibility rules</p>
        </div>
        <a href="{{ route('admin.scholarships.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Scholarship
        </a>
    </div>

    @if($scholarships->isEmpty())
        <div class="card p-10 text-center">
            <svg class="w-16 h-16 mx-auto text-on-surface-variant/50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <h3 class="text-lg font-medium text-on-surface mb-1">No Scholarships Yet</h3>
            <p class="text-on-surface-variant mb-6">Create your first scholarship to get started.</p>
            <a href="{{ route('admin.scholarships.create') }}" class="btn btn-primary">Add Scholarship</a>
        </div>
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-container-low">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Provider</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Award Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Deadline</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Rules</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-on-surface-variant uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/50">
                        @foreach($scholarships as $scholarship)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-4 py-4">
                                    <div class="font-medium text-on-surface">{{ $scholarship->name }}</div>
                                    <div class="text-sm text-on-surface-variant line-clamp-1">{{ $scholarship->description }}</div>
                                </td>
                                <td class="px-4 py-4 text-on-surface">{{ $scholarship->provider }}</td>
                                <td class="px-4 py-4">
                                    <span class="badge badge-info">{{ $scholarship->award_type }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="{{ \Carbon\Carbon::parse($scholarship->deadline)->isPast() ? 'text-error' : 'text-on-surface' }}">
                                        {{ \Carbon\Carbon::parse($scholarship->deadline)->format('M d, Y') }}
                                    </span>
                                    @if(\Carbon\Carbon::parse($scholarship->deadline)->isPast())
                                        <span class="ml-1 badge badge-error">Expired</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if($scholarship->is_active)
                                        <span class="badge badge-active">Active</span>
                                    @else
                                        <span class="badge badge-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if($scholarship->rule)
                                        <span class="badge badge-success">Configured</span>
                                    @else
                                        <span class="badge badge-warning">Not Set</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.scholarships.rules.index', $scholarship) }}"
                                            class="btn btn-outline text-sm px-3 py-1.5">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            Rules
                                        </a>
                                        <a href="{{ route('admin.scholarships.edit', $scholarship) }}"
                                            class="btn btn-ghost text-sm px-3 py-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('admin.scholarships.destroy', $scholarship) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-ghost text-sm px-3 py-1.5 text-error hover:bg-error/10"
                                                onclick="return confirm('Delete this scholarship? This action cannot be undone.')">
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

            <!-- Pagination -->
            @if($scholarships->hasPages())
                <div class="px-4 py-3 border-t border-outline-variant">
                    {{ $scholarships->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection