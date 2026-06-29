@props(['type' => 'default', 'label' => ''])

@php
$classes = [
    'default' => 'bg-surface-container-highest text-on-surface-variant',
    'eligible' => 'bg-success/10 text-success',
    'partial' => 'bg-warning/10 text-warning',
    'not-suitable' => 'bg-error/10 text-error',
    'info' => 'bg-info/10 text-info',
    'success' => 'bg-success/10 text-success',
    'warning' => 'bg-warning/10 text-warning',
    'error' => 'bg-error/10 text-error',
    'pending' => 'bg-info/10 text-info',
    'expired' => 'bg-error/10 text-error',
    'active' => 'bg-success/10 text-success',
    'inactive' => 'bg-surface-container-highest text-on-surface-variant',
];
$class = $classes[$type] ?? $classes['default'];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $class }}">
    {{ $label ?: ucfirst(str_replace('-', ' ', $type)) }}
</span>