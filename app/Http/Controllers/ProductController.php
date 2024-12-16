<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $search = $request->input('search');
    $products = Product::when($search, function($query) use ($search) {
        return $query->where('name', 'like', '%' . $search . '%')
                     ->orWhere('description', 'like', '%' . $search . '%');
    })->cursorPaginate(5);

    return view('products.index', compact('products', 'search'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $validatedData = $request->only(['name', 'price', 'quantity','description']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/products', 'public');
            $validatedData['image'] = $path;
        }
        Product::create($validatedData);
    
        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }
    

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        $validatedData = $request->only(['name', 'price', 'quantity']);
    
        if ($request->hasFile('image')) {
            
            $path = $request->file('image')->store('images/products', 'public');
            $validatedData['image'] = $path;
    
        
             Storage::delete($product->image);
        }
    
        // Update the product with the validated data
        $product->update($validatedData);
    
        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
