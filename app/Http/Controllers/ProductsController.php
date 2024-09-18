<?php

namespace App\Http\Controllers;

use App\Models\ProductsModel;

use Illuminate\{
    Http\Request,
    Support\Facades\Validator
};
use App\Services\ProductService;

class ProductsController extends Controller
{

    private String $apiSecret;
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
        $this->apiSecret = env('API_SECRET_KEY');
    }


    public function getById(Request $request, $id)
    {

        if ($request->header('X-API-SECRET') !== $this->apiSecret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!isset($id)) {
            return response()->json(['status' => 'error', 'message' => 'Both ID parameters are required.'], 400);
        }

        $result = $this->productService->find('id', $id);
        if (!$result) {
            return response()->json(['status' => 'error', 'message' => 'non-existent product'], 404);
        }

        return response()->json(['status' => 'success', 'message' => '', 'data' => $result], 200);
    }

    public function getByImage(Request $request, $image)
    {

        if ($request->header('X-API-SECRET') !== $this->apiSecret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        if (!isset($image)) {
            return response()->json(['status' => 'error', 'message' => 'Both ID parameters are required.'], 400);
        }

        if ($image == "empty") {
            $image = '';
        }

        $result = $this->productService->all()->where('image_url', $image);
        if (!$result) {
            return response()->json(['status' => 'error', 'message' => 'non-existent product'], 404);
        }

        return response()->json(['status' => 'success', 'message' => '', 'data' => $result], 200);
    }

    public function getByNameAndCategory(Request $request)
    {

        if ($request->header('X-API-SECRET') !== $this->apiSecret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $name = $request->query('name');
        $category = $request->query('category');

        if (!isset($name) || !isset($category)) {
            return response()->json(['status' => 'error', 'message' => 'Both NAME and CATEGORY parameters are required.'], 400);
        }

        $result = $this->productService->find('category', $category);
        if (!$result) {
            return response()->json(['status' => 'error', 'message' => 'non-existent product'], 404);
        }

        $product = $result->where('name', 'like', "%$name%")->get();

        return response()->json(['status' => 'success', 'message' => '', 'data' => $product], 200);
    }

    public function getByCategory(Request $request, $category)
    {

        if ($request->header('X-API-SECRET') !== $this->apiSecret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!isset($category)) {
            return response()->json(['status' => 'error', 'message' => 'CATEGORY parameters are required.'], 400);
        }

        $result = $this->productService->all()->where('category', $category);
        if (!$result) {
            return response()->json(['status' => 'error', 'message' => 'non-existent product'], 404);
        }

        return response()->json(['status' => 'success', 'message' => '', 'data' => $result], 200);
    }

    /**
     * Display a listing of the resource.
     */
    public function post(Request $request)
    {
        if ($request->header('X-API-SECRET') !== $this->apiSecret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'category' => 'required|string',
            'image' => 'required|url'
        ], [
            'name.required' => 'The NAME field is required',
            'name.string' => 'The NAME field must be a string',
            'price.required' => 'The PRICE field is required',
            'price.numeric' => 'The PRICE field must be a numeric',
            'description.required' => 'The DESCRIPTION field is required',
            'description.string' => 'The DESCRIPTION field must be a string',
            'category.required' => 'The CATEGORY field is required',
            'category.string' => 'The CATEGORY field must be a string',
            'image.required' => 'The IMAGE field is required',
            'image.url' => 'The IMAGE field must be a string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        $productSameName = $this->productService->find('name', $validatedData['name']);
        if ($productSameName) {
            return response()->json(['status' => 'error', 'message' => 'There is already a product with that name'], 409);
        }

        $product = [
            'name' => $validatedData['name'],
            'price' => $validatedData['price'],
            'description' => $validatedData['description'],
            'category' => $validatedData['category'],
            'image_url' => $validatedData['image'],
        ];

        $save = $this->productService->create($product);
        if ($save) {
            return response()->json(['status' => 'success', 'message' => 'product saved successfully', 'data' => json_encode($save)], 200);
        }

        return response()->json(['status' => 'error', 'message' => 'Failed to save the product'], 500);
    }

    /**
     * Update the specified resource in storage.
     */
    public function put(Request $request, $id)
    {
        if ($request->header('X-API-SECRET') !== $this->apiSecret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|url'
        ], [
            'name.string' => 'The NAME field must be a string.',
            'name.max' => 'The NAME field must not exceed 255 characters.',
            'price.numeric' => 'The PRICE field must be numeric.',
            'price.min' => 'The PRICE field must be at least 0.',
            'description.string' => 'The DESCRIPTION field must be a string.',
            'category.string' => 'The CATEGORY field must be a string.',
            'image.url' => 'The IMAGE field must be a valid URL.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = $this->productService->find('id', $id);
        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'non-existent product'], 404);
        }

        $validatedData = $validator->validate();

        $product = [
            'name' => isset($validatedData['name']) && $validatedData['name'] !== '' ? $validatedData['name'] : $product->name,
            'price' => isset($validatedData['price']) && $validatedData['price'] !== '' ? $validatedData['price'] : $product->price,
            'description' => isset($validatedData['description']) && $validatedData['description'] !== '' ? $validatedData['description'] : $product->description,
            'image_url' => isset($validatedData['image']) && $validatedData['image'] !== '' ? $validatedData['image'] : $product->image_url,
            'category' => isset($validatedData['category']) && $validatedData['category'] !== '' ? $validatedData['category'] : $product->category,
        ];


        $result = $this->productService->update($product, $id);
        if ($result) {
            return response()->json(['status' => 'success', 'message' => 'Product updated successfully.'], 200);
        }

        return response()->json(['status' => 'error', 'message' => 'Failed to update the product. It may not exist or the data provided is invalid.'], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request, $id)
    {
        if ($request->header('X-API-SECRET') !== $this->apiSecret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $product = $this->productService->find('id', $id);
        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'non-existent product'], 404);
        }

        $this->productService->delete($id);
        return response()->json(['status' => 'success', 'message' => 'Product deleted successfully.'], 200);
    }
}
