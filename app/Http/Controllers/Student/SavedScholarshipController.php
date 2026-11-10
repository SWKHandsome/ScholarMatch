<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SavedScholarship;
use App\Models\Scholarship;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SavedScholarshipController extends Controller
{
    public function index(ScholarshipRecommendationService $recommendationService)
    {
        $user = Auth::user();
        $savedScholarships = SavedScholarship::with('scholarship')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        $evaluation = $savedScholarships->isEmpty()
            ? ['recommendations' => [], 'error' => null]
            : $recommendationService->getRecommendations($user);
        $recommendations = collect($evaluation['recommendations'])->keyBy('scholarship_id');
        $evaluationError = $evaluation['error'];

        return view('student.saved-scholarships.index', compact('savedScholarships', 'recommendations', 'evaluationError'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'scholarship_id' => [
                'required',
                'exists:scholarships,id',
                Rule::unique('saved_scholarships')->where(fn ($query) => $query->where('user_id', Auth::id())),
            ],
        ]);

        $user = Auth::user();

        SavedScholarship::create([
            'user_id' => $user->id,
            'scholarship_id' => $request->scholarship_id,
        ]);

        return redirect()->route('student.saved-scholarships.index')
            ->with('success', 'Scholarship saved successfully.');
    }

    public function destroy(Scholarship $scholarship)
    {
        $user = Auth::user();

        SavedScholarship::where('user_id', $user->id)
            ->where('scholarship_id', $scholarship->id)
            ->delete();

        return redirect()->route('student.saved-scholarships.index')
            ->with('success', 'Scholarship removed from saved list.');
    }
}
