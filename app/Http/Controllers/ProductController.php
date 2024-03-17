<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\ProductFormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        
        $query = $request->input('searchText');

        if ($query) {
            $products = DB::table('products')
                ->where('product_description', 'LIKE', '%' . $query . '%')
                ->orWhere('product_reference', 'LIKE', '%' . $query . '%')
                ->orderBy('product_id', 'asc')
                ->paginate(5);
        } else {
            // Si no hay texto de búsqueda, obtén todos los productos
            $products = DB::table('products')->orderBy('product_id', 'asc')->paginate(5);
        }

        return view('stock.product.index', ['products' => $products, 'searchText' => $query]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('stock.product.create');
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(ProductFormRequest $request)
    {
        
        $product = new Product();
        $product->product_reference = $request->get('product_reference');
        $product->list_description = $request->get('list_description');
        $product->product_description = $request->get('product_description');
        $product->purchase_price = $request->get('purchase_price');
        $product->sale_price = $request->get('sale_price');
        $product->weight = $request->get('weight');
        $product->length = $request->get('length');
        $product->width = $request->get('width');
        $product->height = $request->get('height');
        $product->cubic_meter = ($request->get('length') * $request->get('width') * $request->get('height')) / 1000000;
        $product->quantity = $request->get('quantity');
        $product->stock = 0;
        $product->gross_revenue = 0;

        if($request->hasFile('image')){
            $image = $request->file('image');
            $name = Str::slug($request->get('product_reference')) . '-' . time() . '.' . $image->guessExtension(); 
            $destinationPath = public_path('/dist/img/');

            copy($image->getRealPath(), $destinationPath.$name);

            $product->image = $name;
        }

        $product->save();

        return Redirect::to('stock/products');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('stock.product.show', ['product' => Product::findOrFail($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('stock.product.edit', ['product' => Product::findOrFail($id)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductFormRequest $request, string $id)
    {
        $product = Product::findOrFail($id);
        $product->product_reference = $request->get('product_reference');
        $product->list_description = $request->get('list_description');
        $product->product_description = $request->get('product_description');
        $product->purchase_price = $request->get('purchase_price');
        $product->sale_price = $request->get('sale_price');
        $product->weight = $request->get('weight');
        $product->length = $request->get('length');
        $product->width = $request->get('width');
        $product->height = $request->get('height');
        $product->cubic_meter = ($request->get('length') * $request->get('width') * $request->get('height')) / 1000000;
        $product->quantity = $request->get('quantity');
        $product->gross_revenue = $product->stock * $product->purchase_price * $product->quantity;

        if($request->hasFile('image')){
            $image = $request->file('image');
            $name = Str::slug($request->get('product_reference')) . '-' . time() . '.' . $image->guessExtension(); 
            $destinationPath = public_path('/dist/img/');

            copy($image->getRealPath(), $destinationPath.$name);

            $product->image = $name;

        }

        $product->update();

        return Redirect::to('stock/products');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return Redirect::to('stock/products');
    }
}
