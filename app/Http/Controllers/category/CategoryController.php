<?php

namespace App\Http\Controllers\category;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{

    // Get all
    public function index()
    {
        return response()->json(Category::all());
    }

    // Store
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048', // accept images
        ]);

        $category = new Category();
        $category->name = $request->name;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/categories', $imageName); // stores in storage/app/public/categories
            $category->image = $imageName;
        }

        $category->save();

        return response()->json($category);
    }



    // Update
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $category->update($request->only(['name', 'image']));

        return response()->json(['category' => $category]);
    }

    // Delete
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }
}
