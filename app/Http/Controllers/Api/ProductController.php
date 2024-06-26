<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_product' => 'required|string',
            'desc' => 'required|string',
            'brand' => 'required|string',
            'image' => 'required|file|image|max:3048',
            'image2' => 'nullable|file|image|max:3048',
            'image3' => 'nullable|file|image|max:3048',
            'size' => 'required|integer',
            'price' => 'required|integer',
            'discount' => 'required|numeric', // Use numeric instead of float
            'status' => 'required|in:available,sold out'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        // Handle the file upload
        $image = $request->file('image');
        $imageContent = file_get_contents($image->getRealPath());
        $imageBase64 = base64_encode($imageContent);

        $image2 = $request->hasFile('image2') ? base64_encode(file_get_contents($request->file('image2')->getRealPath())) : null;
        $image3 = $request->hasFile('image3') ? base64_encode(file_get_contents($request->file('image3')->getRealPath())) : null;

        $product = Product::create([
            'name_product' => $request->name_product,
            'desc' => $request->desc,
            'brand' => $request->brand,
            'image' => $imageBase64,
            'image2' => $image2,
            'image3' => $image3,
            'size' => $request->size,
            'price' => $request->price,
            'discount' => $request->discount,
            'status' => $request->status
        ]);

        $product = $this->imageService->convertImage($product);

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Product created successfully!',
            'data' => $product
        ]);
    }

    public function getProducts(Request $request)
    {
        $brand = $request->query('brand');
        $id = $request->query('id');
        $searchTerm = $request->input('search');

        $query = Product::query();

        if ($brand) {
            $query->where('brand', $brand);
        }

        if ($id) {
            $query->where('id_product', $id);
        }

        if ($searchTerm) {
            $query->where('name_product', 'like', '%' . $searchTerm . '%');
        }

        $products = $query->paginate(6);

        if ($products->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'No products found!'
            ], 404);
        }

        $products->getCollection( )->transform(function ($product) {
            return $this->imageService->convertImage($product);
        });

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Products retrieved successfully!',
            'data' => $products
        ]);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Product not found!'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Product deleted successfully!'
        ]);
    }

    public function getProductById($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Product not found!'
            ], 404);
        }

        $product = $this->imageService->convertImage($product);

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Product retrieved successfully!',
            'data' => $product
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Product not found!'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name_product' => 'sometimes|string',
            'desc' => 'sometimes|string',
            'brand' => 'sometimes|string',
            'image' => 'sometimes|file|image|max:3048',
            'image2' => 'sometimes|file|image|max:3048',
            'image3' => 'sometimes|file|image|max:3048',
            'size' => 'sometimes|integer',
            'price' => 'sometimes|integer',
            'discount' => 'sometimes|numeric',
            'status' => 'sometimes|in:available,sold out'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $updateData = $request->only(['name_product', 'desc', 'brand', 'size', 'price', 'discount', 'status']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageContent = file_get_contents($image->getRealPath());
            $updateData['image'] = base64_encode($imageContent);
        }

        if ($request->hasFile('image2')) {
            $image2 = $request->file('image2');
            $image2Content = file_get_contents($image2->getRealPath());
            $updateData['image2'] = base64_encode($image2Content);
        }

        if ($request->hasFile('image3')) {
            $image3 = $request->file('image3');
            $image3Content = file_get_contents($image3->getRealPath());
            $updateData['image3'] = base64_encode($image3Content);
        }

        $product->update($updateData);

        $product = $this->imageService->convertImage($product);

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Product updated successfully!',
            'data' => $product
        ], 200);
    }

    
    public function getBrandProductCounts()
    {
        $brandCount = Product::distinct('brand')->count();

        if ($brandCount == 0) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'No brands found!',
            ], 404);
        }

        $brands = Product::select('brand', DB::raw('count(*) as total'))
            ->groupBy('brand')
            ->get();

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Brand product counts retrieved successfully!',
            'data' => $brands
        ]);
    }
}
