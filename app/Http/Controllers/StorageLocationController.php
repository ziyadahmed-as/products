<?php

namespace App\Http\Controllers;

use App\Models\StorageLocation;
use App\Models\Branch;
use Illuminate\Http\Request;

class StorageLocationController extends Controller
{
    public function index(Request $request)
    {
        $locations = StorageLocation::with('branch')
            ->withCount('inventoryBalances')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"))
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->filled('status'), fn($q) => $q->where('is_active', $request->status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('storage_locations.index', compact('locations', 'branches'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('storage_locations.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:100',
            'code'      => 'required|string|max:30|unique:storage_locations,code',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $location = StorageLocation::create($data);

        return redirect()->route('storage-locations.index')->with('success', "Storage location «{$location->name}» created.");
    }

    public function edit(StorageLocation $storageLocation)
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('storage_locations.edit', compact('storageLocation', 'branches'));
    }

    public function update(Request $request, StorageLocation $storageLocation)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:100',
            'code'      => "required|string|max:30|unique:storage_locations,code,{$storageLocation->id}",
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $storageLocation->update($data);

        return redirect()->route('storage-locations.index')->with('success', "Storage location «{$storageLocation->name}» updated.");
    }

    public function destroy(StorageLocation $storageLocation)
    {
        if ($storageLocation->inventoryBalances()->where('quantity', '>', 0)->count() > 0) {
            return back()->with('error', 'Cannot delete storage location that contains inventory items.');
        }

        $storageLocation->delete();
        return redirect()->route('storage-locations.index')->with('success', 'Storage location deleted.');
    }
}
