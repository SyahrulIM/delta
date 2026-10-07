<?php

namespace App\Http\Controllers;

use App\Exports\ProjectExport;
use App\Exports\ProjectReportExport;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ProjectController extends Controller
{
    public function index()
    {
        return view('projects.index'); // blade yang kita buat di bawah
    }

    // Data untuk DataTable (JSON)
    public function data()
    {
        $projects = Project::orderBy('id', 'desc')->get();

        $rows = $projects->map(function ($p) {
            return [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'customer_name' => $p->customer_name,
                'location' => $p->location,
                'contact_name' => $p->contact_name,
                'contact_phone' => $p->contact_phone,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:projects,code',
            'name' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:100',
            'job' => 'nullable|string|max:100',
            'npwp' => 'nullable|string|max:100',
            'contract_no' => 'nullable|string|max:100',
        ]);

        $project = Project::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Project created',
            'data' => $project,
        ]);
    }

    public function show(Project $project)
    {
        return response()->json($project);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'code' => ['required','string','max:100', Rule::unique('projects','code')->ignore($project->id)],
            'name' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:100',
            'job' => 'nullable|string|max:100',
            'npwp' => 'nullable|string|max:100',
            'contract_no' => 'nullable|string|max:100',
        ]);

        $project->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Project updated',
            'data' => $project,
        ]);
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted'
        ]);
    }

    public function export()
    {
        return Excel::download(new ProjectExport, 'projects.xlsx');
    }
}
