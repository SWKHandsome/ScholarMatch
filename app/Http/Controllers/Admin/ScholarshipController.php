<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use Illuminate\Http\Request;

class ScholarshipController extends Controller
{
    public function index()
    {
        $scholarships = Scholarship::with('rule')->latest()->paginate(15);
        return view('admin.scholarships.index', compact('scholarships'));
    }

    public function create()
    {
        return view('admin.scholarships.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'award_type' => ['required', 'string', 'max:255'],
            'deadline' => ['required', 'date'],
            'application_link' => ['required', 'url'],
            'is_active' => ['boolean'],
        ]);

        Scholarship::create($validated);

        return redirect()->route('admin.scholarships.index')
            ->with('success', 'Scholarship created successfully.');
    }

    public function edit(Scholarship $scholarship)
    {
        return view('admin.scholarships.edit', compact('scholarship'));
    }

    public function update(Request $request, Scholarship $scholarship)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'award_type' => ['required', 'string', 'max:255'],
            'deadline' => ['required', 'date'],
            'application_link' => ['required', 'url'],
            'is_active' => ['boolean'],
        ]);

        $scholarship->update($validated);

        return redirect()->route('admin.scholarships.index')
            ->with('success', 'Scholarship updated successfully.');
    }

    public function destroy(Scholarship $scholarship)
    {
        $scholarship->delete();

        return redirect()->route('admin.scholarships.index')
            ->with('success', 'Scholarship deleted successfully.');
    }
}