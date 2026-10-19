<div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
        <thead class="border-b border-outline-variant">
            <tr><th class="p-3">Requirement</th><th class="p-3">Required</th><th class="p-3">Your Information</th><th class="p-3">Result</th></tr>
        </thead>
        <tbody class="divide-y divide-outline-variant">
            @foreach($checks as $check)
                <tr data-explanation-status="{{ $check['outcome'] === 'not_met' ? 'failed' : ($check['outcome'] === 'met' ? 'passed' : 'not-applicable') }}">
                    <th class="p-3 font-medium">{{ $check['label'] }}</th>
                    <td class="p-3">{{ is_array($check['required']) ? (implode(', ', $check['required']) ?: 'Not specified') : ($check['required'] ?? 'Not specified') }}</td>
                    <td class="p-3">{{ is_array($check['actual']) ? implode(', ', $check['actual']) : ($check['actual'] ?? '—') }}</td>
                    <td class="p-3 {{ $check['outcome'] === 'not_met' ? 'text-error' : ($check['outcome'] === 'met' ? 'text-success' : 'text-on-surface-variant') }}">
                        {{ ['met' => 'Met', 'not_met' => 'Not Met', 'not_applicable' => 'Not Applicable'][$check['outcome']] }}
                    </td>
                </tr>
                @if(!empty($check['routes']))
                    <tr><td colspan="4" class="p-3">
                        <details>
                            <summary class="cursor-pointer font-medium">View all qualification routes — pass any one</summary>
                            <div class="mt-3 space-y-4">
                                @foreach($check['routes'] as $route)
                                    <div class="rounded-lg border border-outline-variant p-3">
                                        <p class="mb-2 font-medium">{{ $route['label'] }}: {{ $route['passed'] ? 'Met' : 'Not Met' }}</p>
                                        @include('components.requirement-results', ['checks' => $route['checks']])
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    </td></tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
