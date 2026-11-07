@extends('layouts.admin', ['pageTitle' => 'Add Scholarship Rules'])
@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Add Rules for: {{ $scholarship->name }}</h1>
    <form method="POST" action="{{ route('admin.scholarships.rules.store', $scholarship) }}" class="card p-6 space-y-6">
        @csrf
        @include('admin.rules._form')
        <div class="flex justify-end gap-3">
            <a class="btn btn-outline" href="{{ route('admin.scholarships.rules.index', $scholarship) }}">Cancel</a>
            <button class="btn btn-primary" type="submit">Save Rules</button>
        </div>
    </form>
</div>
@endsection
