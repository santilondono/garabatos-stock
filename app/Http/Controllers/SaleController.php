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

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function saleNotification($sale_id)
    {
        $sale = DB::table('sales as s')
            ->join('clients as c', 's.client_id', '=', 'c.client_id')
            ->join('sales_detail as sd', 's.sale_id', '=', 'sd.sale_id')
            ->join('products as p', 'sd.product_id', '=', 'p.product_id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->select('s.sale_id', 's.sale_date', 'c.client_name', 'u.name', 'c.country', 'c.shipping_mark', DB::raw("sum(sd.quantity_sold * sd.sale_price * p.quantity) AS total"), 's.is_cancelled')
            ->where('s.sale_id', '=', $sale_id)
            ->groupBy('s.sale_id', 's.sale_date', 'c.client_name', 'c.country', 'c.shipping_mark', 's.is_cancelled', 'u.name')
            ->orderBy('s.sale_date', 'desc')
            ->first();

        $sale_details = DB::table('sales_detail as sd')
            ->join('products as p', 'sd.product_id', '=', 'p.product_id')
            ->select('p.product_reference', 'p.product_description', 'sd.quantity_sold', 'sd.sale_price', 'p.quantity', DB::raw('sd.quantity_sold * sd.sale_price * p.quantity AS subtotal'))
            ->where('sd.sale_id', '=', $sale_id)
            ->get();

        $admins = [
            'flyon-importexport@outlook.es',
            '2997221689@qq.com'
        ];

        // $admins = [
        //     'santiloo2002@gmail.com'
        // ];

        Mail::to($admins)->send(new SaleNotificatiosMailable($sale, $sale_details));
    }

    public function printSale($sale_id)
    {
        $sale = DB::table('sales as s')
            ->join('clients as c', 's.client_id', '=', 'c.client_id')
            ->join('sales_detail as sd', 's.sale_id', '=', 'sd.sale_id')
            ->join('products as p', 'sd.product_id', '=', 'p.product_id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->select('s.sale_id', 's.sale_date', 'c.client_name', 'u.name', 'c.country', 'c.shipping_mark', DB::raw("sum(sd.quantity_sold * sd.sale_price * p.quantity) AS total"), 's.is_cancelled')
            ->where('s.sale_id', '=', $sale_id)
            ->groupBy('s.sale_id', 's.sale_date', 'c.client_name', 'c.country', 'c.shipping_mark', 's.is_cancelled', 'u.name')
            ->orderBy('s.sale_date', 'desc')
            ->first();

        $sale_details = DB::table('sales_detail as sd')
            ->join('products as p', 'sd.product_id', '=', 'p.product_id')
            ->select('p.product_reference', 'p.product_description', 'sd.quantity_sold', 'sd.sale_price', 'p.quantity', DB::raw('sd.quantity_sold * sd.sale_price * p.quantity AS subtotal'))
            ->where('sd.sale_id', '=', $sale_id)
            ->get();

        return view('stock.sale.print', ['sale' => $sale, 'sales_detail' => $sale_details]);
    }




    public function index(Request $request)
    {
        $query = $request->input('searchText');

        if ($query) {
            $sales = DB::table('sales')
                ->join('clients', 'sales.client_id', '=', 'clients.client_id')
                ->join('sales_detail as sd', 'sales.sale_id', '=', 'sd.sale_id')
                ->select('sales.sale_id', 'sales.sale_date', 'clients.country', 'clients.client_name', DB::raw('ROUND(sum(sd.quantity_sold * sd.sale_price * p.quantity),2) as total'), 'sales.is_cancelled')
                ->where('clients.client_name', 'LIKE', "%$query%")
                ->orWhere('sales.sale_id', 'LIKE', "%$query%")
                ->groupBy('sales.sale_id', 'sales.sale_date', 'clients.country', 'clients.client_name', 'sales.is_cancelled')
                ->orderBy('sales.sale_date', 'desc')
                ->paginate(5);
        } else {
            $sales = DB::table('sales')
                ->join('clients', 'sales.client_id', '=', 'clients.client_id')
                ->join('sales_detail as sd', 'sales.sale_id', '=', 'sd.sale_id')
                ->join('products as p', 'sd.product_id', '=', 'p.product_id')
                ->select('sales.sale_id', 'sales.sale_date', 'clients.country', 'clients.client_name', DB::raw('ROUND(sum(sd.quantity_sold * sd.sale_price * p.quantity),2) as total'), 'sales.is_cancelled')
                ->groupBy('sales.sale_id', 'sales.sale_date', 'clients.country', 'clients.client_name', 'sales.is_cancelled')
                ->orderBy('sales.sale_date', 'desc')
                ->paginate(5);
        }

        return view('stock.sale.index', ['sales' => $sales, 'searchText' => $query]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sale = Sale::all();

        $products = DB::table('products')
            ->select(DB::raw('CONCAT(product_reference, " ", product_description) AS product'), 'product_id', 'sale_price', 'quantity', 'stock')
            ->where('stock', '>', '0')
            ->where('active', true)
            ->get();

        $clients = DB::table('clients')
            ->select(DB::raw('CONCAT(client_name, " - ", country) AS client'), 'client_id', 'shipping_mark')
            ->get();

        return view('stock.sale.create', ['sale' => $sale, 'products' => $products, 'clients' => $clients]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaleFormRequest $request)
    {
        try {
            DB::beginTransaction();
            $sale = new Sale();
            $sale->sale_date = Carbon::now();
            $sale->user_id = Auth::user()->id;
            $sale->client_id = $request->get('client_id');
            $sale->is_cancelled = false;
            $sale->save();


            $product_id = $request->get('product_id');
            $quantity = $request->get('quantity_sold');
            $sale_price = $request->get('sale_price');

            $cont = 0;

            while ($cont < count($product_id)) {
                $sale_detail = new SaleDetail();
                $sale_detail->sale_id = $sale->sale_id;
                $sale_detail->product_id = $product_id[$cont];
                $sale_detail->quantity_sold = $quantity[$cont];
                $sale_detail->sale_price = $sale_price[$cont];
                $sale_detail->save();

                $product = Product::find($product_id[$cont]);
                $product->stock = $product->stock - $quantity[$cont];
                $product->gross_revenue = $product->stock * $product->purchase_price * $product->quantity;
                $product->update();

                $cont++;
            }

            DB::commit();

            $this->saleNotification($sale->sale_id);
        } catch (\Exception $e) {
            DB::rollback();
        }

        return Redirect::to(route('sales.show', $sale->sale_id));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sale = DB::table('sales as s')
            ->join('clients as c', 's.client_id', '=', 'c.client_id')
            ->join('sales_detail as sd', 's.sale_id', '=', 'sd.sale_id')
            ->join('products as p', 'sd.product_id', '=', 'p.product_id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->select('s.sale_id', 's.sale_date', 'c.client_name', 'u.name', 'c.country', 'c.shipping_mark', DB::raw("sum(sd.quantity_sold * sd.sale_price * p.quantity) AS total"), 's.is_cancelled')
            ->where('s.sale_id', '=', $id)
            ->groupBy('s.sale_id', 's.sale_date', 'c.client_name', 'c.country', 'c.shipping_mark', 's.is_cancelled', 'u.name')
            ->orderBy('s.sale_date', 'desc')
            ->first();

        $sale_details = DB::table('sales_detail as sd')
            ->join('products as p', 'sd.product_id', '=', 'p.product_id')
            ->select('p.product_reference', 'p.product_description', 'sd.quantity_sold', 'sd.sale_price', 'p.quantity', DB::raw('sd.quantity_sold * sd.sale_price * p.quantity AS subtotal'))
            ->where('sd.sale_id', '=', $id)
            ->get();

        return view('stock.sale.show', ['sale' => $sale, 'sales_detail' => $sale_details]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            // Obtener la venta y detalles asociados
            $sale = Sale::findOrFail($id);
            $saleDetails = SaleDetail::where('sale_id', $id)->get();

            // Anular la venta
            $sale->is_cancelled = true;
            $sale->update();

            // Revertir unidades vendidas y actualizar ganancia bruta para cada producto vendido
            foreach ($saleDetails as $saleDetail) {
                $product = Product::find($saleDetail->product_id);

                // Aumentar el stock
                $product->stock += $saleDetail->quantity_sold;

                // Restar la ganancia bruta
                $product->gross_revenue = $product->stock * $product->purchase_price * $product->quantity;

                // Actualizar el producto en la base de datos
                $product->update();
            }

            DB::commit();

            return Redirect::to('stock/sales');
        } catch (\Exception $e) {
            DB::rollback();
            // Manejar la excepción según tus necesidades
        }
    }
}
