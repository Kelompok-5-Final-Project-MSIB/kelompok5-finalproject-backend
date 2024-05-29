<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\ImageService;

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
            'image' => 'required|file|image|max:3048', // Ensure the image is a file and limit its size
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

        $product = Product::create([
            'name_product' => $request->name_product,
            'desc' => $request->desc,
            'brand' => $request->brand,
            'image' => $imageBase64,
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

    public function getProducts()
    {
        $products = Product::paginate(5);

        $products->getCollection()->transform(function ($product) {
            return $this->imageService->convertImage($product);
        });

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Products retrieved successfully!',
            'data' => $products
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
            'name_product' => 'sometimes|required|string',
            'desc' => 'sometimes|required|string',
            'brand' => 'sometimes|required|string',
            'image' => 'sometimes|file|image|max:2048',
            'price' => 'sometimes|required|integer',
            'discount' => 'sometimes|required|numeric',
            'status' => 'sometimes|required|in:available,sold out'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $updateData = $request->only(['name_product', 'desc', 'brand', 'price', 'discount', 'status']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageContent = file_get_contents($image->getRealPath());
            $updateData['image'] = base64_encode($imageContent);
        }

        $product->update($updateData);

        $product = $this->imageService->convertImage($product);

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Product updated successfully!',
            'data' => $product
        ]);
    }

    public function getProductByBrand($brand)
    {
        $products = Product::where('brand', $brand)->get();

        if ($products->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'No products found for this brand!'
            ], 404);
        }

        $products->each(function ($product) {
            $product = $this->imageService->convertImage($product);
        });

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Products retrieved successfully!',
            'data' => $products
        ]);
    }

    public function searchProducts(Request $request)
    {
        $searchTerm = $request->input('search');

        $products = Product::where('name_product', 'like', '%' . $searchTerm . '%')->paginate(5);

        if ($products->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'No products found!'
            ], 404);
        }

        $products->getCollection()->transform(function ($product) {
            return $this->imageService->convertImage($product);
        });

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Products retrieved successfully!',
            'data' => $products
        ]);
    }
}
