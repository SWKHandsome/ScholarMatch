<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IncomeCategory;
use App\Models\StudyField;
use App\Services\ScholarshipRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeCategoryController extends Controller
{
    public function index()
    {
        $categories = IncomeCategory::configuredCategories();
        $studyFields = StudyField::orderBy('name')->get();

        return view('admin.income-categories.index', compact('categories', 'studyFields'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:income_categories,name'],
            'min_income' => ['required', ...IncomeCategory::amountRules()],
            'max_income' => ['nullable', ...IncomeCategory::amountRules(), 'gte:min_income'],
        ]);

        DB::transaction(function () use ($validated) {
            IncomeCategory::create($validated);
            IncomeCategory::synchronizeStudentProfiles();
        });
        ScholarshipRecommendationService::invalidateCatalogCache();

        return back()->with('success', 'Income category created successfully.');
    }

    public function update(Request $request, IncomeCategory $incomeCategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:income_categories,name,'.$incomeCategory->id],
            'min_income' => ['required', ...IncomeCategory::amountRules()],
            'max_income' => ['nullable', ...IncomeCategory::amountRules(), 'gte:min_income'],
        ]);

        DB::transaction(function () use ($incomeCategory, $validated) {
            $incomeCategory->update($validated);
            IncomeCategory::synchronizeStudentProfiles();
        });
        ScholarshipRecommendationService::invalidateCatalogCache();

        return back()->with('success', 'Income category updated successfully.');
    }

    public function destroy(IncomeCategory $incomeCategory)
    {
        DB::transaction(function () use ($incomeCategory) {
            $incomeCategory->delete();
            IncomeCategory::synchronizeStudentProfiles();
        });
        ScholarshipRecommendationService::invalidateCatalogCache();

        return back()->with('success', 'Income category deleted successfully.');
    }
}
