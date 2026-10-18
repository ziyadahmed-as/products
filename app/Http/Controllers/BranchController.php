<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\StorageLocation;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::with(['parent', 'storageLocations'])
            ->withCount(['users', 'storageLocations'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"))
            ->when($request->filled('status'), fn($q) => $q->where('is_active', $request->status))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        $parents = Branch::where('is_active', true)->orderBy('name')->get();
        return view('branches.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:120|unique:branches,name',
            'code'             => 'required|string|max:20|unique:branches,code',
            'type'             => 'required|in:headquarters,branch,warehouse,distribution_center',
            'parent_branch_id' => 'nullable|exists:branches,id',
            'is_active'        => 'boolean',
        ]);

        $branch = Branch::create([...$data, 'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('branches.index')->with('success', "Branch «{$branch->name}» created.");
    }

    public function show(Branch $branch)
    {
        $branch->load(['parent', 'children', 'storageLocations', 'users.roles']);
        return view('branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        $parents = Branch::where('is_active', true)->where('id', '!=', $branch->id)->orderBy('name')->get();
        return view('branches.edit', compact('branch', 'parents'));
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name'             => "required|string|max:120|unique:branches,name,{$branch->id}",
            'code'             => "required|string|max:20|unique:branches,code,{$branch->id}",
            'type'             => 'required|in:headquarters,branch,warehouse,distribution_center',
            'parent_branch_id' => 'nullable|exists:branches,id',
            'is_active'        => 'boolean',
        ]);

        $branch->update([...$data, 'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('branches.index')->with('success', "Branch «{$branch->name}» updated.");
    }

    public function destroy(Branch $branch)
    {
        if ($branch->users()->count() > 0) {
            return back()->with('error', 'Cannot delete branch with assigned users.');
        }

        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Branch removed.');
    }
}
