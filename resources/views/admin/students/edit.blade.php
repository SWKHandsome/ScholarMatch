@extends('layouts.admin', ['pageTitle' => 'Edit Student Account'])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-2">Edit Student Account</h1>
    <p class="text-on-surface-variant mb-6">Update account details. Profile and academic information are read-only.</p>
    <form method="POST" action="{{ route('admin.students.update', $student) }}" class="card p-6">
        @csrf
        @method('PATCH')
        @include('admin.students._form')
    </form>
</div>
@endsection
