<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Str;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // ✅ 1. Get all products with category + images
    public function index()
    {
        $products = Product::with(['category', 'images'])->get();

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found'], 200);
        }

        return response()->json([
            'message' => 'Products fetched successfully',
            'products' => $products
        ], 200);
    }

    // ✅ 2. Create new product with multiple images
    // public function store(Request $req)
    // {
    //     $validated = $req->validate([
    //         'name' => 'required|string|max:255',
    //         'category_id' => 'required|exists:categories,id',
    //         'price' => 'required|numeric|min:0',
    //         'rating' => 'nullable|numeric|min:1|max:5',
    //         'description' => 'nullable|string',
    //         'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    //     ]);

    //     // ✅ Double-check category exists
    //     $category = Category::find($req->category_id);
    //     if (!$category) {
    //         return response()->json(['message' => 'Category not found'], 404);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         // ✅ Create product
    //         $product = Product::create([
    //             'name' => $req->name,
    //             'category_id' => $req->category_id,
    //             'price' => $req->price,
    //             'rating' => $req->rating,
    //             'description' => $req->description,
    //         ]);

    //         // ✅ Upload and save images (if provided)
    //         if ($req->hasFile('images')) {
    //             foreach ($req->file('images') as $image) {
    //                 $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
    //                 $path = 'uploads/products/';
    //                 $image->move(public_path($path), $filename);

    //                 ProductImage::create([
    //                     'product_id' => $product->id,
    //                     'image_path' => $path . $filename,
    //                 ]);
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Product created successfully',
    //             'product' => $product->load(['images', 'category'])
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['message' => 'Error creating product', 'error' => $e->getMessage()], 500);
    //     }
    // }

    // public function store(Request $req)
    // {
    //     $validated = $req->validate([
    //         'name' => 'required|string|max:255',
    //         'category_id' => 'required|exists:categories,id',
    //         'price' => 'required|numeric|min:0',
    //         'rating' => 'nullable|numeric|min:1|max:5',
    //         'description' => 'nullable|string',
    //         'main_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // 👈 main image
    //         'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'    // 👈 multiple images
    //     ]);

    //     // ✅ Double-check category exists
    //     $category = Category::find($req->category_id);
    //     if (!$category) {
    //         return response()->json(['message' => 'Category not found'], 404);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         $mainImageName = null;

    //         // ✅ Handle main image
    //         if ($req->hasFile('main_image')) {
    //             $mainImage = $req->file('main_image');
    //             $mainImageName = time() . '_' . uniqid() . '.' . $mainImage->getClientOriginalExtension();
    //             $mainImage->move(public_path('uploads/products/'), $mainImageName);
    //         }

    //         // ✅ Create product (with main image)
    //         $product = Product::create([
    //             'name' => $req->name,
    //             'category_id' => $req->category_id,
    //             'price' => $req->price,
    //             'rating' => $req->rating,
    //             'description' => $req->description,
    //             'image' => $mainImageName, // 👈 store main image filename
    //         ]);

    //         // ✅ Upload and save multiple images (if provided)
    //         if ($req->hasFile('images')) {
    //             foreach ($req->file('images') as $image) {
    //                 $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
    //                 $path = 'uploads/products/';
    //                 $image->move(public_path($path), $filename);

    //                 ProductImage::create([
    //                     'product_id' => $product->id,
    //                     'image_path' => $path . $filename,
    //                 ]);
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Product created successfully',
    //             'product' => $product->load(['images', 'category'])
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Error creating product',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // public function store(Request $req)
    // {
    //     // dd($req->all());
    //     // ✅ Validate input
    //     $validated = $req->validate([
    //         'name' => 'required|string|max:255',
    //         'category_id' => 'required|exists:categories,id',
    //         'price' => 'required|numeric|min:0',
    //         'rating' => 'nullable|numeric|min:1|max:5',
    //         'description' => 'nullable|string',
    //         'main_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    //         'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    //     ]);

    //     // ✅ Check if category exists
    //     $category = Category::find($req->category_id);
    //     if (!$category) {
    //         return response()->json(['message' => 'Category not found'], 404);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         $mainImageName = null;

    //         // ✅ Handle main image
    //         if ($req->hasFile('main_image')) {
    //             $mainImage = $req->file('main_image');
    //             if ($mainImage->isValid()) {
    //                 $mainImageName = time() . '_' . uniqid() . '.' . $mainImage->getClientOriginalExtension();
    //                 $mainImage->move(public_path('uploads/products/'), $mainImageName);
    //             } else {
    //                 return response()->json(['message' => 'Main image is not valid'], 400);
    //             }
    //         }

    //         // ✅ Create product with main image
    //         $product = Product::create([
    //             'name' => $req->name,
    //             'category_id' => $req->category_id,
    //             'price' => $req->price,
    //             'rating' => $req->rating,
    //             'description' => $req->description,
    //             'image' => $mainImageName, // store main image filename
    //         ]);

    //         // ✅ Handle multiple images
    //         if ($req->hasFile('images')) {
    //             foreach ($req->file('images') as $image) {
    //                 if ($image->isValid()) {
    //                     $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
    //                     $path = 'uploads/products/';
    //                     $image->move(public_path($path), $filename);

    //                     ProductImage::create([
    //                         'product_id' => $product->id,
    //                         'image_path' => $path . $filename,
    //                     ]);
    //                 }
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Product created successfully',
    //             'product' => $product->load(['images', 'category']),
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Error creating product',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function store(Request $req)
    {
        // ✅ Validate input
        $validated = $req->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'rating' => 'nullable|numeric|min:1|max:5',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $imageName = null;
            if ($req->hasFile('image')) {
                $image = $req->file('image');

                if ($image->isValid()) {
                    $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->storeAs(('public/products/'), $imageName);
                } else {
                    return response()->json(['message' => 'Invalid image file'], 400);
                }
            }

            // ✅ Create product with UUID
            $product = Product::create([
                'uuid' => (string) Str::uuid(),
                'name' => $validated['name'],
                'category_id' => $validated['category_id'],
                'price' => $validated['price'],
                'rating' => $validated['rating'] ?? null,
                'description' => $validated['description'] ?? null,
                'image' => $imageName,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product->load('category'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // ✅ 3. Show single product
    public function show($id)
    {
        $product = Product::with(['category', 'images'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json(['product' => $product], 200);
    }

    // ✅ 4. Update product
    public function update(Request $req, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validated = $req->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id',
            'price' => 'sometimes|required|numeric|min:0',
            'rating' => 'nullable|numeric|min:1|max:5',
            'description' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        DB::beginTransaction();

        try {
            // ✅ Update product info
            $product->update($req->only(['name', 'category_id', 'price', 'rating', 'description']));

            // ✅ If new images uploaded
            if ($req->hasFile('images')) {
                // Optional: delete old images first if needed
                // ProductImage::where('product_id', $product->id)->delete();

                foreach ($req->file('images') as $image) {
                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $path = 'uploads/products/';
                    $image->move(public_path($path), $filename);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path . $filename,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product->load(['images', 'category'])
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating product', 'error' => $e->getMessage()], 500);
        }
    }

    // ✅ 5. Delete product
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        ProductImage::where('product_id', $product->id)->delete();
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
