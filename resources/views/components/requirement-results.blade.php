<div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
        <thead class="border-b border-outline-variant bg-surface-container-low">
            <tr><th class="p-3">Requirement</th><th class="p-3">Required</th><th class="p-3">Your Information</th><th class="p-3">Result</th></tr>
        </thead>
        <tbody class="divide-y divide-outline-variant">
            @foreach($checks as $check)
                <tr class="{{ $check['outcome'] === 'not_met' ? 'bg-rose-50' : ($check['outcome'] === 'met' ? 'bg-emerald-50' : 'text-on-surface-variant') }}" data-explanation-status="{{ $check['outcome'] === 'not_met' ? 'failed' : ($check['outcome'] === 'met' ? 'passed' : 'not-applicable') }}">
                    <th scope="row" class="p-3 font-semibold">{{ $check['label'] }}</th>
                    <td class="p-3 {{ $check['outcome'] !== 'not_applicable' ? 'font-semibold' : '' }}">{{ is_array($check['required']) ? (implode(', ', $check['required']) ?: 'Not specified') : ($check['required'] ?? 'Not specified') }}</td>
                    <td class="p-3 {{ $check['outcome'] === 'not_met' ? 'font-semibold text-rose-800' : ($check['outcome'] === 'met' ? 'font-semibold text-emerald-800' : '') }}">{{ is_array($check['actual']) ? implode(', ', $check['actual']) : ($check['actual'] ?? '—') }}</td>
                    <td class="p-3">
                        @include('components.requirement-status', ['outcome' => $check['outcome']])
                    </td>
                </tr>
                @if(!empty($check['routes']))
                    <tr><td colspan="4" class="p-3">
                        <details>
                            <summary class="cursor-pointer font-medium">View all qualification routes — pass any one</summary>
                            <div class="mt-3 space-y-4">
                                @foreach($check['routes'] as $route)
                                    <div class="rounded-lg border p-3 {{ $route['passed'] ? 'border-emerald-200 bg-emerald-50/50' : 'border-rose-200 bg-rose-50/50' }}">
                                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                            <p class="font-semibold">{{ $route['label'] }}</p>
                                            @include('components.requirement-status', ['outcome' => $route['passed'] ? 'met' : 'not_met'])
                                        </div>
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
