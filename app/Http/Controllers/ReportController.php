<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\SaleFormRequest; //Importar request
use Illuminate\Support\Facades\DB; //Importar conexion a BD
use Illuminate\Support\Carbon; //Importar Carbon
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SaleNotificatiosMailable;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function stock_summary()
    {
        $stock_summary = DB::table('products')
            ->select('product_reference', 'product_description', 'purchase_price', 'sale_price', 'stock', 'comming')
            ->where('active', 1)
            ->get();

        return view('stock.report.stock_summary', ['stock_summary' => $stock_summary]);
    }

    public function sales_summary($startDate = null, $endDate = null)
    {
        $start_date = $startDate ? Carbon::createFromFormat('Y-m-d', $startDate) : Carbon::create(2024, 1, 1);
        $end_date = $endDate ? Carbon::createFromFormat('Y-m-d', $endDate) : now();

        $start_date = $start_date->format('Y-m-d');
        $end_date = $end_date->format('Y-m-d');

        $sales_summary = DB::table('sales')
            ->select(
                'sales.sale_id',
                'sale_date',
                'users.name as user_name',
                'clients.client_name',
                DB::raw('SUM(quantity_sold * quantity * sales_detail.sale_price) as gross_profit'),
                DB::raw('SUM(quantity_sold * quantity * (sales_detail.sale_price - products.purchase_price)) as net_profit')
            )
            ->join('sales_detail', 'sales.sale_id', '=', 'sales_detail.sale_id')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->join('clients', 'sales.client_id', '=', 'clients.client_id')
            ->where('is_cancelled', false)
            ->join('products', 'sales_detail.product_id', '=', 'products.product_id')
            ->whereBetween('sale_date', [$start_date, $end_date])
            ->groupBy('sales.sale_id', 'sale_date', 'users.name', 'clients.client_name', 'is_cancelled')
            ->orderByDesc('sales.sale_id')
            ->get();

        $total_summary = DB::table('sales')
            ->select(
                DB::raw('SUM(quantity_sold * quantity * sales_detail.sale_price) as gross_profit'),
                DB::raw('SUM(quantity_sold * quantity * (sales_detail.sale_price - products.purchase_price)) as net_profit')
            )
            ->join('sales_detail', 'sales.sale_id', '=', 'sales_detail.sale_id')
            ->where('is_cancelled', false)
            ->join('products', 'sales_detail.product_id', '=', 'products.product_id')
            ->whereBetween('sale_date', [$start_date, $end_date])
            ->first();

        return view('stock.report.sales_summary', [
            'sales_summary' => $sales_summary,
            'total_summary' => $total_summary,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);
    }

    public function cards(Request $request)
    {
        $start_date = $request->input('start_date') ? Carbon::createFromFormat('Y-m-d', $request->input('start_date')) : Carbon::create(2024, 1, 1);
        $end_date = $request->input('end_date') ? Carbon::createFromFormat('Y-m-d', $request->input('end_date')) : now();

        $start_date = $start_date->format('Y-m-d');
        $end_date = $end_date->format('Y-m-d');

        // Consulta: 5 productos más vendidos
        $top_selling_products = DB::table('sales')
            ->select(
                'products.product_reference',
                'products.product_description',
                DB::raw('SUM(quantity_sold) as quantity_sold'),
                DB::raw('SUM(quantity_sold * quantity * sales_detail.sale_price) as gross_profit'),
                DB::raw('SUM(quantity_sold * quantity * (sales_detail.sale_price - products.purchase_price)) as net_profit')
            )
            ->join('sales_detail', 'sales.sale_id', '=', 'sales_detail.sale_id')
            ->join('products', 'sales_detail.product_id', '=', 'products.product_id')
            ->where('is_cancelled', false)
            ->whereBetween('sale_date', [$start_date, $end_date])
            ->groupBy('products.product_reference', 'products.product_description')
            ->orderByDesc('quantity_sold')
            ->limit(5)
            ->get();

        // Consulta: Ganancias brutas y netas
        $profits = DB::table('sales')
            ->select(
                DB::raw('SUM(quantity_sold * quantity * sales_detail.sale_price) as gross_profit'),
                DB::raw('SUM(quantity_sold * quantity * (sales_detail.sale_price - products.purchase_price)) as net_profit')
            )
            ->join('sales_detail', 'sales.sale_id', '=', 'sales_detail.sale_id')
            ->join('products', 'sales_detail.product_id', '=', 'products.product_id')
            ->where('is_cancelled', false)
            ->whereBetween('sale_date', [$start_date, $end_date])
            ->first();
            
        // Consulta: Dinero en bodega
        $stock_value = DB::table('products')
            ->select(
                DB::raw('SUM(stock * quantity * purchase_price) as stock_value')
            )
            ->first();

        return view('stock.report.cards', [
            'top_selling_products' => $top_selling_products,
            'profits' => $profits,
            'stock_value' => $stock_value,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);
    }
}
