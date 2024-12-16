<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->input('search');
        $products = Product::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', '%' . $search . '%')
                         ->orWhere('description', 'like', '%' . $search . '%');
        })->cursorPaginate(5);

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Debugging: Log the incoming request data
        \Log::info('Request Data:', $request->all());
    
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'required',
                'description' => 'nullable|string',
                'price' => 'required|numeric',
                'quantity' => 'required|integer',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
    
            \Log::info('Validation passed:', $validatedData);
    
            // Check if an image file is uploaded
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                
                // Debugging: Log image details
                \Log::info('Image Details:', [
                    'is_valid' => $file->isValid(),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
    
                // Store the image and log the path
                $path = $file->store('images/products', 'public');
                \Log::info('Image stored at path:', ['path' => $path]);
    
                $validatedData['image'] = $path;
            }
    
            // Create the product and log the data
            $product = Product::create($validatedData);
            \Log::info('Product created:', $product->toArray());
    
            return response()->json(['message' => 'Product created successfully.', 'product' => $product], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Debugging: Log validation errors
            \Log::error('Validation failed:', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Debugging: Log unexpected errors
            \Log::error('Unexpected error occurred:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }
    

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        // Debugging: Log the incoming request data
        \Log::info('Update Request Data:', $request->all());
    
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'required',
                'description' => 'nullable|string',
                'price' => 'required|numeric',
                'quantity' => 'required|integer',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
    
            \Log::info('Validation passed:', $validatedData);
    
            // Check if an image file is uploaded
            if ($request->hasFile('image')) {
                $file = $request->file('image');
    
                // Debugging: Log image details
                \Log::info('Image Details:', [
                    'is_valid' => $file->isValid(),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
    
                // Store the image and log the path
                $path = $file->store('images/products', 'public');
                \Log::info('Image stored at path:', ['path' => $path]);
    
                // Add the path to validated data
                $validatedData['image'] = $path;
    
                // Optionally delete the old image if needed
                if ($product->image) {
                    \Log::info('Deleting old image:', ['path' => $product->image]);
                    \Storage::disk('public')->delete($product->image);
                }
            }
    
            // Update the product and log the updated data
            $product->update($validatedData);
            \Log::info('Product updated successfully:', $product->toArray());
    
            return response()->json(['message' => 'Product updated successfully.', 'product' => $product]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Debugging: Log validation errors
            \Log::error('Validation failed:', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Debugging: Log unexpected errors
            \Log::error('Unexpected error occurred:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
