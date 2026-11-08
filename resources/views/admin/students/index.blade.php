@extends('layouts.admin', ['pageTitle' => 'Manage Students'])

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Manage Students</h1>
            <p class="text-on-surface-variant mt-1">Manage student accounts and view profile completion.</p>
        </div>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary">+ Add Student</a>
    </div>
    <form action="{{ route('admin.students.index') }}" method="GET" class="card p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <label for="student-search" class="sr-only">Search students by name or email</label>
            <input id="student-search" type="search" name="search" value="{{ $search }}" maxlength="255"
                   placeholder="Search by name or email..." class="input flex-1">
            <button type="submit" class="btn btn-primary">Search</button>
            @if($search !== '')<a href="{{ route('admin.students.index') }}" class="btn btn-outline">Clear</a>@endif
        </div>
        <x-input-error :messages="$errors->get('search')" class="mt-2" />
    </form>
    @if($students->isEmpty())
        <div class="card p-10 text-center">
            <h2 class="text-lg font-semibold">{{ $search !== '' ? 'No matching students' : 'No students yet' }}</h2>
            <p class="mt-2 text-on-surface-variant">{{ $search !== '' ? 'Try a different name or email.' : 'Add a student account to get started.' }}</p>
        </div>
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px]">
                    <thead class="bg-surface-container-low">
                        <tr>
                            @foreach(['Name', 'Email', 'Student Profile', 'Academic Information', 'Actions'] as $heading)
                                <th class="px-4 py-3 text-left text-sm font-semibold">{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/50">
                        @foreach($students as $student)
                            <tr class="hover:bg-surface-container-low">
                                <td class="px-4 py-4 font-medium break-words">{{ $student->name }}</td>
                                <td class="px-4 py-4 break-all">{{ $student->email }}</td>
                                <td class="px-4 py-4"><span class="badge {{ $student->studentProfile?->isComplete() ? 'badge-eligible' : 'badge-partial' }}">{{ $student->studentProfile?->isComplete() ? 'Complete' : 'Incomplete' }}</span></td>
                                <td class="px-4 py-4"><span class="badge {{ $student->academicResult?->isReadyForRecommendations() ? 'badge-eligible' : 'badge-partial' }}">{{ $student->academicResult?->isReadyForRecommendations() ? 'Complete' : 'Incomplete' }}</span></td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline text-sm px-3 py-1.5">View</a>
                                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline text-sm px-3 py-1.5">Edit</a>
                                        @include('admin.students._delete')
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $students->links() }}</div>
    @endif
</div>
@endsection
