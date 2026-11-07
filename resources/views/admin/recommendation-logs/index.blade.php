@extends('layouts.admin', ['pageTitle' => 'Recommendation Logs'])
@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div><h1 class="text-2xl font-bold">Recommendation Logs</h1><p class="text-on-surface-variant mt-1">Latest eligibility decisions and requirement explanations for each student and scholarship.</p></div>
    <form method="GET" action="{{ route('admin.recommendation-logs.index') }}" class="card p-5 space-y-4">
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="label" for="status">Status</label>
                <select class="input" name="status" id="status">
                    @foreach(['' => 'All Statuses', 'Eligible' => 'Eligible', 'eligible_verification' => 'Eligible !', 'Not Eligible' => 'Not Eligible', 'legacy' => 'Previous Evaluations'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="scholarship_id">Scholarship</label>
                <select class="input" id="scholarship_id" name="scholarship_id">
                    <option value="">All Scholarships</option>
                    @foreach($scholarships as $scholarship)
                        <option value="{{ $scholarship->id }}" @selected(request('scholarship_id') == $scholarship->id)>{{ $scholarship->name }}</option>
                    @endforeach
                </select>
            </div>
            @foreach(['date_from' => 'From Date', 'date_to' => 'To Date'] as $key => $label)
                <div><label class="label" for="{{ $key }}">{{ $label }}</label><input class="input" type="date" id="{{ $key }}" name="{{ $key }}" value="{{ request($key) }}"></div>
            @endforeach
        </div>
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1"><label class="label" for="search">Search</label><input class="input" type="search" name="search" id="search" value="{{ request('search') }}" placeholder="Student name, email or scholarship"></div>
            <button class="btn btn-primary" type="submit">Search</button><a class="btn btn-outline" href="{{ route('admin.recommendation-logs.index') }}">Clear Filters</a>
        </div>
    </form>
    <div class="card overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-surface-container-low"><tr>@foreach(['Student', 'Scholarship', 'Decision', 'Failed Requirements', 'Last Evaluated', 'Actions'] as $label)<th class="p-4">{{ $label }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-outline-variant">
                @forelse($logs as $log)
                    <tr>
                        <td class="p-4">{{ $log->user->name }}<span class="block text-on-surface-variant">{{ $log->user->email }}</span></td>
                        <td class="p-4">{{ $log->scholarship->name }}<span class="block text-on-surface-variant">{{ $log->scholarship->provider }}</span></td>
                        <td class="p-4">
                            @if($log->isCurrentEvaluation())
                                @include('components.decision-badge', ['status' => $log->status, 'requiresVerification' => $log->requires_verification, 'specialRequirements' => $log->special_requirements])
                            @else
                                <span class="badge badge-info">Previous Evaluation</span>
                            @endif
                        </td>
                        <td class="p-4">{{ $log->isCurrentEvaluation() ? (collect($log->requirement_results)->where('outcome', 'not_met')->pluck('label')->join(', ') ?: 'None') : 'Re-evaluation required' }}</td>
                        <td class="p-4">{{ $log->updated_at->format('M d, Y H:i') }}</td>
                        <td class="p-4"><a class="text-primary hover:underline" href="{{ route('admin.recommendation-logs.show', $log) }}">View Details</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center">No Recommendation Logs</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $logs->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
