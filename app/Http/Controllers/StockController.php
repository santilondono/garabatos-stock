<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
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

        $stockAlerts = DB::table('products')
            ->where('stock', '>', 0)
            ->where('stock', '<', 5)
            ->get();

        return view('stock.index', ['products' => $products, 'searchText' => $query, 'stockAlerts' => $stockAlerts]);
    }
}
