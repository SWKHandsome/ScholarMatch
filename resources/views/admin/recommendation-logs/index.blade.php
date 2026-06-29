@extends('layouts.admin', ['pageTitle' => 'Recommendation Logs'])

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-on-surface">Recommendation Logs</h1>
        <p class="text-on-surface-variant mt-1">View student scholarship matching results and scores</p>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-6">
        <form method="GET" action="{{ route('admin.recommendation-logs.index') }}" class="grid sm:grid-cols-4 gap-4">
            <div>
                <label for="status" class="label">Status</label>
                <select id="status" name="status" class="input">
                    <option value="">All Statuses</option>
                    <option value="Eligible" {{ request('status') === 'Eligible' ? 'selected' : '' }}>Eligible</option>
                    <option value="Partially Eligible" {{ request('status') === 'Partially Eligible' ? 'selected' : '' }}>Partially Eligible</option>
                    <option value="Not Suitable" {{ request('status') === 'Not Suitable' ? 'selected' : '' }}>Not Suitable</option>
                </select>
            </div>

            <div>
                <label for="scholarship_id" class="label">Scholarship</label>
                <select id="scholarship_id" name="scholarship_id" class="input">
                    <option value="">All Scholarships</option>
                    @foreach($scholarships as $scholarship)
                        <option value="{{ $scholarship->id }}" {{ request('scholarship_id') == $scholarship->id ? 'selected' : '' }}>
                            {{ $scholarship->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date_from" class="label">From Date</label>
                <input type="date" id="date_from" name="date_from" class="input" value="{{ request('date_from') }}">
            </div>

            <div>
                <label for="date_to" class="label">To Date</label>
                <input type="date" id="date_to" name="date_to" class="input" value="{{ request('date_to') }}">
            </div>
        </form>
        <div class="mt-4 flex justify-end">
            <a href="{{ route('admin.recommendation-logs.index') }}" class="btn btn-outline">Clear Filters</a>
        </div>
    </div>

    @if($logs->isEmpty())
        <div class="card p-10 text-center">
            <svg class="w-16 h-16 mx-auto text-on-surface-variant/50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <h3 class="text-lg font-medium text-on-surface mb-1">No Recommendation Logs</h3>
            <p class="text-on-surface-variant">No matching logs found. Students will generate logs when they view recommendations.</p>
        </div>
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-container-low">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Scholarship</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Score</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Failed Hard Rules</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Preliminary</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-on-surface-variant uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/50">
                        @foreach($logs as $log)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-medium text-sm">
                                            {{ strtoupper($log->user->name[0]) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-on-surface text-sm">{{ $log->user->name }}</p>
                                            <p class="text-xs text-on-surface-variant">{{ $log->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-medium text-on-surface text-sm">{{ $log->scholarship->name }}</p>
                                    <p class="text-xs text-on-surface-variant">{{ $log->scholarship->provider }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="badge
                                        {{ $log->status === 'Eligible' ? 'badge-eligible' :
                                           ($log->status === 'Partially Eligible' ? 'badge-partial' : 'badge-not-suitable') }}">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="font-mono font-medium text-on-surface">{{ $log->score }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    @if(!empty($log->failed_hard_rules))
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($log->failed_hard_rules as $rule)
                                                <span class="badge badge-error text-xs">{{ $rule }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-on-surface-variant text-sm">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if($log->explanation && collect($log->explanation)->contains(fn($e) => str_contains($e, 'Preliminary')))
                                        <span class="badge badge-info">Yes</span>
                                    @else
                                        <span class="text-on-surface-variant text-sm">No</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-on-surface-variant">{{ $log->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-4 py-4 text-right">
                                    <button type="button"
                                        class="btn btn-ghost text-sm px-3 py-1.5"
                                        onclick="showDetail({{ $log->id }})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-4 py-3 border-t border-outline-variant">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

<!-- Detail Modal -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-5 border-b border-outline-variant flex items-center justify-between sticky top-0 bg-white z-10">
            <h3 class="text-lg font-semibold text-on-surface">Recommendation Details</h3>
            <button type="button" class="p-2 rounded-md hover:bg-surface-container-low" onclick="closeDetailModal()" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div id="detail-content" class="p-5 space-y-4">
            <!-- Loaded via JS -->
        </div>
    </div>
</div>

<script>
function showDetail(logId) {
    const modal = document.getElementById('detail-modal');
    const content = document.getElementById('detail-content');
    content.innerHTML = '<div class="flex items-center justify-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-2 border-primary border-t-transparent"></div></div>';
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    fetch(`{{ route('admin.recommendation-logs.show', ':id') }}`.replace(':id', logId))
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                content.innerHTML = `<div class="text-center py-8 text-error">${data.error}</div>`;
                return;
            }
            renderDetail(data);
        })
        .catch(() => {
            content.innerHTML = '<div class="text-center py-8 text-error">Failed to load details</div>';
        });
}

function renderDetail(log) {
    const content = document.getElementById('detail-content');
    const explanations = log.explanation || [];
    const breakdown = log.score_breakdown || {};
    const failedRules = log.failed_hard_rules || [];
    const isPreliminary = explanations.some(e => e.includes('Preliminary'));

    content.innerHTML = `
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-on-surface-variant">Student</p>
                <p class="font-medium text-on-surface">${log.user_name}</p>
            </div>
            <div>
                <p class="text-sm text-on-surface-variant">Email</p>
                <p class="font-medium text-on-surface">${log.user_email}</p>
            </div>
            <div>
                <p class="text-sm text-on-surface-variant">Scholarship</p>
                <p class="font-medium text-on-surface">${log.scholarship_name}</p>
            </div>
            <div>
                <p class="text-sm text-on-surface-variant">Provider</p>
                <p class="font-medium text-on-surface">${log.provider}</p>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <span class="badge
                ${log.status === 'Eligible' ? 'badge-eligible' :
                   (log.status === 'Partially Eligible' ? 'badge-partial' : 'badge-not-suitable')}">
                ${log.status}
            </span>
            <span class="font-mono text-lg font-medium text-on-surface">${log.score}/100</span>
            ${isPreliminary ? '<span class="badge badge-info">Preliminary</span>' : ''}
        </div>

        ${Object.keys(breakdown).length > 0 && Object.values(breakdown).some(v => v > 0) ? `
        <div class="border-t border-outline-variant pt-4">
            <h4 class="font-medium text-on-surface mb-3">Score Breakdown</h4>
            <div class="grid grid-cols-2 gap-2 text-sm">
                ${Object.entries({
                    'academic': 'Academic',
                    'field': 'Field of Study',
                    'institution': 'Institution Type',
                    'income': 'Income Priority'
                }).map(([key, label]) => `
                    <div class="flex justify-between p-2 bg-surface-container-low rounded">
                        <span class="text-on-surface-variant">${label}</span>
                        <span class="font-medium ${(breakdown[key] ?? 0) > 0 ? 'text-success' : 'text-on-surface-variant'}">${breakdown[key] ?? 0}</span>
                    </div>
                `).join('')}
            </div>
        </div>
        ` : ''}

        ${failedRules.length > 0 ? `
        <div class="border-t border-outline-variant pt-4">
            <h4 class="font-medium text-on-surface mb-3">Failed Hard Rules</h4>
            <div class="flex flex-wrap gap-1">
                ${failedRules.map(rule => `<span class="badge badge-error text-xs">${rule}</span>`).join('')}
            </div>
        </div>
        ` : ''}

        ${explanations.length > 0 ? `
        <div class="border-t border-outline-variant pt-4">
            <h4 class="font-medium text-on-surface mb-3">Explanation</h4>
            <div class="space-y-1">
                ${explanations.map(exp => `
                    <p class="text-sm text-on-surface-variant flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0
                            ${exp.includes('does not meet') || exp.includes('exceeds') || exp.includes('not match') ? 'text-error' : 'text-success'}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="${exp.includes('does not meet') || exp.includes('exceeds') || exp.includes('not match') ? 'M6 18L18 6M6 6l12 12' : 'M5 13l4 4L19 7'}"></path>
                        </svg>
                        ${exp}
                    </p>
                `).join('')}
            </div>
        </div>
        ` : ''}
    `;
}

function closeDetailModal() {
    document.getElementById('detail-modal').classList.add('hidden');
    document.getElementById('detail-modal').classList.remove('flex');
}

document.getElementById('detail-modal')?.addEventListener('click', (e) => {
    if (e.target === document.getElementById('detail-modal')) closeDetailModal();
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeDetailModal();
});
</script>
@endsection