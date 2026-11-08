<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentAccountRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate(['search' => ['nullable', 'string', 'max:255']]);
        $search = trim($validated['search'] ?? '');
        $students = User::where('role', User::ROLE_STUDENT)
            ->with(['studentProfile', 'academicResult'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->latest('id')->paginate(10)->withQueryString();

        return view('admin.students.index', compact('students', 'search'));
    }

    public function create()
    {
        return view('admin.students.create', ['student' => new User]);
    }

    public function store(StudentAccountRequest $request)
    {
        $data = $request->validated();
        $student = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_STUDENT,
        ]);

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student account created. The student can log in and complete their profile.');
    }

    public function show(User $student)
    {
        abort_unless($student->isStudent(), 404);
        $student->load(['studentProfile', 'academicResult']);

        return view('admin.students.show', compact('student'));
    }

    public function edit(User $student)
    {
        abort_unless($student->isStudent(), 404);

        return view('admin.students.edit', compact('student'));
    }

    public function update(StudentAccountRequest $request, User $student)
    {
        abort_unless($student->isStudent(), 404);
        $data = $request->validated();
        DB::transaction(function () use ($student, $data) {
            $oldEmail = $student->email;
            $student->name = $data['name'];
            $student->email = $data['email'];
            $emailChanged = $student->isDirty('email');
            $passwordChanged = filled($data['password'] ?? null);
            if ($emailChanged) {
                $student->email_verified_at = null;
            }
            if ($passwordChanged) {
                $student->password = Hash::make($data['password']);
            }
            if ($emailChanged || $passwordChanged) {
                $student->remember_token = Str::random(60);
                DB::table('sessions')->where('user_id', $student->id)->delete();
                DB::table('password_reset_tokens')->whereIn('email', [$oldEmail, $student->email])->delete();
            }
            $student->save();
        });

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student account updated successfully.');
    }

    public function destroy(User $student)
    {
        abort_unless($student->isStudent(), 404);
        DB::transaction(function () use ($student) {
            DB::table('sessions')->where('user_id', $student->id)->delete();
            DB::table('password_reset_tokens')->where('email', $student->email)->delete();
            $student->delete();
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Student account and related student records deleted successfully.');
    }
}
