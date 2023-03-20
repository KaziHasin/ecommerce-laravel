<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index($id = null) {

     $id ? $data = Product::find($id): $data = Product::all();
        
         if(!$data) {
            return response()->json(['message' => 'Product not found'], 404);
        }
         return $data;

    }

    public function store(Request $req) {
       
        $req->validate([
            'product_name' => 'required',
            'product_description' => 'required|min:6|max:2500',
            'product_category'=>'required',
            'product_image' =>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'product_price' => 'required',
            'product_quantity' => 'required',

        ]);
           
           $input = $req->all();

           $input['product_image'] = $req->file('product_image')->store('public/product_images');
         

         $result = Product::create($input);

       if($result) {
        return response()->json(["message" => "Data saved successfully"], 201);
    }else {
        return response()->json(["message" => "Data not saved"], 204);
    }

    }

    public function update(Request $request, $id) {

         $product = Product::find($id);
         
         $input = $request->all();
        
        if($request->hasFile('product_image')) {
         Storage::delete($product->product_image);
         $input['product_image'] = $request->file('product_image')->store('public/product_images');

        }


            $product->update($input);

       return response()->json($product);

    }

    public function destroy($id = null) {


      
       $product = Product::find($id);
       
       if(!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        Storage::delete($product->product_image);
        $product->delete();
        return response()->json(['message' => 'Product deleted'], 200);

    }


}
