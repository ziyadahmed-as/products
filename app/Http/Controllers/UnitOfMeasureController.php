<?php

namespace App\Http\Controllers;

use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;

class UnitOfMeasureController extends Controller
{
    public function index(Request $request)
    {
        $units = UnitOfMeasure::withCount('products')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80|unique:unit_of_measures,name',
            'code' => 'required|string|max:20|unique:unit_of_measures,code',
        ]);

        $unit = UnitOfMeasure::create($data);

        return redirect()->route('units.index')->with('success', "Unit «{$unit->name}» created.");
    }

    public function show(UnitOfMeasure $unit)
    {
        $unit->loadCount('products');
        return view('units.show', compact('unit'));
    }

    public function edit(UnitOfMeasure $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, UnitOfMeasure $unit)
    {
        $data = $request->validate([
            'name' => "required|string|max:80|unique:unit_of_measures,name,{$unit->id}",
            'code' => "required|string|max:20|unique:unit_of_measures,code,{$unit->id}",
        ]);

        $unit->update($data);

        return redirect()->route('units.index')->with('success', "Unit «{$unit->name}» updated.");
    }

    public function destroy(UnitOfMeasure $unit)
    {
        if ($unit->products()->count() > 0) {
            return back()->with('error', 'Cannot delete unit that is used by products.');
        }

        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Unit removed.');
    }
}
