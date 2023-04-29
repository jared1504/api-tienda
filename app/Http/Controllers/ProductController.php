<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();
        
        return $products;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'name' => 'required|string|max:255',
            'price_sale' => 'required|numeric',
            'price_order' => 'required|numeric',
            'stock' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $pro = Product::find($request->id);

        if ($pro === null) {
            $product = Product::create([
                'id' => $request->id,
                'name' => $request->name,
                'price_sale' => $request->price_sale,
                'price_order' => $request->price_order,
                'stock' => $request->stock
            ]);
            return response()->json([
                "success" => true,
                "message" => "Product created successfully.",
                "data" => $product
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => "El id del producto no esta disponible",

            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        $product = Product::find($product->id);
        if ($product===null) {
            return response()->json([
                "success" => false,
                "message" => "El producto no existe"
            ]);
        }else{
            return response()->json([
            "success" => true,
            "message" => "Product retrieved successfully.",
            "data" => $product
        ]);
        }
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price_sale' => 'required|numeric',
            'price_order' => 'required|numeric',
            'stock' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $product = Product::find($product->id);

        $product->name = $request['name'];
        $product->price_order = $request['price_order'];
        $product->price_sale = $request['price_sale'];
        $product->stock = $request['stock'];
        $product->save();

        return response()->json([
            "success" => true,
            "message" => "Product updated successfully.",
            "data" => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json([
            "success" => true,
            "message" => "Product deleted successfully.",
            "data" => $product
        ]);
    }
}
