<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SavedScholarship;
use App\Models\Scholarship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedScholarshipController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $savedScholarships = SavedScholarship::with('scholarship')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('student.saved-scholarships.index', compact('savedScholarships'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'scholarship_id' => ['required', 'exists:scholarships,id'],
        ]);

        $user = Auth::user();

        SavedScholarship::firstOrCreate([
            'user_id' => $user->id,
            'scholarship_id' => $request->scholarship_id,
        ]);

        return back()->with('success', 'Scholarship saved successfully.');
    }

    public function destroy(Scholarship $scholarship)
    {
        $user = Auth::user();

        SavedScholarship::where('user_id', $user->id)
            ->where('scholarship_id', $scholarship->id)
            ->delete();

        return back()->with('success', 'Scholarship removed from saved list.');
    }
}