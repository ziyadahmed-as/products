<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeMaterial;
use App\Models\Product;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $recipes = Recipe::with(['product', 'materials.rawMaterial'])
            ->withCount('materials')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$request->search}%")))
            ->when($request->filled('status'), fn($q) => $q->where('is_active', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('recipes.index', compact('recipes'));
    }

    public function create()
    {
        $products  = Product::where('is_active', true)->whereIn('type', ['manufactured_product'])->orderBy('name')->get();
        $materials = Product::where('is_active', true)->whereIn('type', ['raw_material'])->orderBy('name')->get();
        return view('recipes.create', compact('products', 'materials'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'              => 'required|exists:products,id',
            'name'                    => 'required|string|max:150',
            'description'             => 'nullable|string|max:1000',
            'is_active'               => 'boolean',
            'materials'               => 'nullable|array',
            'materials.*.material_id' => 'required|exists:products,id',
            'materials.*.quantity'    => 'required|numeric|min:0.001',
        ]);

        $recipe = Recipe::create([
            'product_id'  => $data['product_id'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        foreach ($data['materials'] ?? [] as $row) {
            $recipe->materials()->create([
                'raw_material_id' => $row['material_id'],
                'quantity'        => $row['quantity'],
            ]);
        }

        return redirect()->route('recipes.index')->with('success', "Recipe «{$recipe->name}» created.");
    }

    public function show(Recipe $recipe)
    {
        $recipe->load(['product', 'materials.rawMaterial.unit']);
        return view('recipes.show', compact('recipe'));
    }

    public function edit(Recipe $recipe)
    {
        $recipe->load('materials.rawMaterial');
        $products  = Product::where('is_active', true)->whereIn('type', ['manufactured_product'])->orderBy('name')->get();
        $materials = Product::where('is_active', true)->whereIn('type', ['raw_material'])->orderBy('name')->get();
        return view('recipes.edit', compact('recipe', 'products', 'materials'));
    }

    public function update(Request $request, Recipe $recipe)
    {
        $data = $request->validate([
            'product_id'              => 'required|exists:products,id',
            'name'                    => 'required|string|max:150',
            'description'             => 'nullable|string|max:1000',
            'is_active'               => 'boolean',
            'materials'               => 'nullable|array',
            'materials.*.material_id' => 'required|exists:products,id',
            'materials.*.quantity'    => 'required|numeric|min:0.001',
        ]);

        $recipe->update([
            'product_id'  => $data['product_id'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        // Replace all BOM lines
        $recipe->materials()->delete();
        foreach ($data['materials'] ?? [] as $row) {
            $recipe->materials()->create([
                'raw_material_id' => $row['material_id'],
                'quantity'        => $row['quantity'],
            ]);
        }

        return redirect()->route('recipes.index')->with('success', "Recipe «{$recipe->name}» updated.");
    }

    public function destroy(Recipe $recipe)
    {
        $recipe->materials()->delete();
        $recipe->delete();
        return redirect()->route('recipes.index')->with('success', 'Recipe removed.');
    }
}
