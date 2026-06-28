<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IncomeCategory;
use Illuminate\Http\Request;

class IncomeCategoryController extends Controller
{
    public function index()
    {
        $categories = IncomeCategory::orderBy('min_income')->get();
        return view('admin.income-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:income_categories,name'],
            'min_income' => ['required', 'numeric', 'min:0'],
            'max_income' => ['nullable', 'numeric', 'min:0'],
        ]);

        IncomeCategory::create($validated);

        return back()->with('success', 'Income category created successfully.');
    }

    public function update(Request $request, IncomeCategory $incomeCategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:income_categories,name,' . $incomeCategory->id],
            'min_income' => ['required', 'numeric', 'min:0'],
            'max_income' => ['nullable', 'numeric', 'min:0'],
        ]);

        $incomeCategory->update($validated);

        return back()->with('success', 'Income category updated successfully.');
    }

    public function destroy(IncomeCategory $incomeCategory)
    {
        $incomeCategory->delete();
        return back()->with('success', 'Income category deleted successfully.');
    }
}