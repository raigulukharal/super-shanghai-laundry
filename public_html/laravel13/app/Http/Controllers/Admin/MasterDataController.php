<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClothType;
use App\Models\Color;
use App\Models\Category;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    /**
     * Get all cloth types
     */
    public function getClothTypes(Request $request)
    {
        $query = ClothType::where('is_active', true);
        
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        return response()->json($query->orderBy('name')->get());
    }
    
    /**
     * Get cloth types by category (for dynamic filtering)
     */
    public function getClothTypesByCategory(Request $request)
    {
        $categoryId = $request->category_id;
        
        if (!$categoryId) {
            return response()->json([]);
        }
        
        $types = ClothType::where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return response()->json($types);
    }
    
    /**
     * Get all colors
     */
    public function getColors()
    {
        return response()->json(Color::orderBy('name')->get());
    }
    
    /**
     * Get all categories
     */
    public function getCategories()
    {
        return response()->json(Category::orderBy('name')->get());
    }
    
    /**
     * Store new cloth type
     */
    public function storeClothType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0'
        ]);
        
        $clothType = ClothType::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'base_price' => $request->base_price,
            'is_active' => true
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Cloth type added successfully',
            'cloth_type' => $clothType
        ]);
    }
}