@extends('layouts.student', ['pageTitle' => 'Saved Scholarships'])

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Saved Scholarships</h1>
            <p class="text-on-surface-variant mt-1">Scholarships you've bookmarked for later review.</p>
        </div>
        <a href="{{ route('student.recommendations') }}" class="btn btn-primary">Find More Scholarships</a>
    </div>

    @if($savedScholarships->isEmpty())
        <div class="card p-10 text-center">
            <svg class="w-16 h-16 mx-auto text-on-surface-variant/50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
            <h3 class="text-lg font-medium text-on-surface mb-1">No Saved Scholarships</h3>
            <p class="text-on-surface-variant mb-6">Start exploring recommendations and save scholarships you're interested in.</p>
            <a href="{{ route('student.recommendations') }}" class="btn btn-primary">View Recommendations</a>
        </div>
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-container-low">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Scholarship</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Provider</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Award Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Deadline</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/50">
                        @foreach($savedScholarships as $saved)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-4 py-4">
                                    <div class="font-medium text-on-surface">{{ $saved->scholarship->name }}</div>
                                    <div class="text-sm text-on-surface-variant line-clamp-1">{{ $saved->scholarship->description }}</div>
                                </td>
                                <td class="px-4 py-4 text-on-surface">{{ $saved->scholarship->provider }}</td>
                                <td class="px-4 py-4">
                                    <span class="badge badge-info">{{ $saved->scholarship->award_type }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="{{ \Carbon\Carbon::parse($saved->scholarship->deadline)->isPast() ? 'text-error' : 'text-on-surface' }}">
                                        {{ \Carbon\Carbon::parse($saved->scholarship->deadline)->format('M d, Y') }}
                                    </span>
                                    @if(\Carbon\Carbon::parse($saved->scholarship->deadline)->isPast())
                                        <span class="ml-2 badge badge-error">Expired</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($saved->scholarship->application_link)
                                            <a href="{{ $saved->scholarship->application_link }}" target="_blank" rel="noopener noreferrer"
                                                class="btn btn-primary text-sm px-3 py-1.5">
                                                Apply
                                            </a>
                                        @endif
                                        <form action="{{ route('student.saved-scholarships.destroy', $saved->scholarship) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-ghost text-sm px-3 py-1.5 text-error hover:bg-error/10"
                                                onclick="return confirm('Remove this scholarship from your saved list?')">
                                                Remove
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
            @if($savedScholarships->hasPages())
                <div class="px-4 py-3 border-t border-outline-variant">
                    {{ $savedScholarships->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection