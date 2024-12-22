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
        $query = $request->input('searchText', '');
        $page = $request->input('page', 1); // Número de página solicitada

        // Construcción de la consulta
        $productsQuery = DB::table('products')
            ->where('active', true)
            ->orderBy('product_id', 'asc');

        if ($query) {
            $productsQuery->where(function ($q) use ($query) {
                $q->where('product_description', 'LIKE', '%' . $query . '%')
                    ->orWhere('product_reference', 'LIKE', '%' . $query . '%');
            });
        }

        // Paginación
        $products = $productsQuery->paginate(20);

        // Validar el número de página
        $page = max(1, min($page, $products->lastPage()));

        // Redirigir si la página no es válida
        if ($request->has('page') && $page != $request->input('page')) {
            return redirect()->route('stock-now.index', [
                'searchText' => $query,
                'page' => $page,
            ]);
        }

        // Alertas de stock
        $stockAlerts = DB::table('products')
            ->where('stock', '>', 0)
            ->where('stock', '<', 5)
            ->get();

        return view('stock.index', [
            'products' => $products,
            'searchText' => $query,
            'stockAlerts' => $stockAlerts,
        ]);
    }
}
