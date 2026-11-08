@extends('layouts.admin', ['pageTitle' => 'Add Student'])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-2">Add Student</h1>
    <p class="text-on-surface-variant mb-6">Create a student account.</p>
    <form method="POST" action="{{ route('admin.students.store') }}" class="card p-6">
        @csrf
        @include('admin.students._form')
    </form>
</div>
@endsection
