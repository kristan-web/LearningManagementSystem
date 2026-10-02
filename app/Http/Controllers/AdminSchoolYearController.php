<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSchoolYearController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SchoolYear::class);

        $schoolYears = SchoolYear::orderByDesc('created_at')->get();

        return view('admin.school-year.index', compact('schoolYears'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', SchoolYear::class);

        return view('admin.school-year.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SchoolYear::class);

        $validated = $request->validate([
            'year' => 'required|string|max:9|unique:school_years,year',
            'status' => 'required|in:active,closed',
        ]);

        SchoolYear::create($validated);

        return redirect()->route('admin.school-years.index')
            ->with('success', 'School year created successfully.');
    }

    public function edit(Request $request, SchoolYear $schoolYear): View
    {
        $this->authorize('update', $schoolYear);

        return view('admin.school-year.edit', compact('schoolYear'));
    }

    public function update(Request $request, SchoolYear $schoolYear): RedirectResponse
    {
        $this->authorize('update', $schoolYear);

        $validated = $request->validate([
            'year' => 'required|string|max:9|unique:school_years,year,' . $schoolYear->school_year_id,
            'status' => 'required|in:active,closed',
        ]);

        $schoolYear->update($validated);

        return redirect()->route('admin.school-years.index')
            ->with('success', 'School year updated successfully.');
    }

    public function destroy(Request $request, SchoolYear $schoolYear): RedirectResponse
    {
        $this->authorize('delete', $schoolYear);

        $schoolYear->delete();

        return redirect()->route('admin.school-years.index')
            ->with('success', 'School year deleted successfully.');
    }
    public function rollover(Request $request): RedirectResponse
    {
        $this->authorize('create', SchoolYear::class);

        $validated = $request->validate([
            'new_year' => 'required|string|max:9',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
            // Close the currently active school year(s)
            SchoolYear::where('status', 'active')->update(['status' => 'closed']);

            // Create new active school year
            SchoolYear::create([
                'year' => $validated['new_year'],
                'status' => 'active',
            ]);
        });

        return redirect()->route('admin.school-years.index')
            ->with('success', 'School year rollover completed successfully.');
    }


}