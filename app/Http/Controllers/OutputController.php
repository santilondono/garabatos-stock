<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Product;
use App\Models\Output;
use App\Models\OutputDetail;
use Illuminate\Support\Facades\Auth;

class OutputController extends Controller
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
        $query = trim($request->get('searchText'));

        if($query){
            $outputs = DB::table('outputs as o')
            ->join('users as u', 'o.user_id', '=', 'u.id')
            ->select('o.output_id', 'o.output_date', 'u.name','o.reason')
            ->where('o.output_id', 'LIKE', '%' . $query . '%')
            ->orWhere('u.name', 'LIKE', '%' . $query . '%')
            ->groupBy('o.output_id', 'o.output_date', 'u.name', 'o.reason')
            ->orderBy('o.output_date', 'desc')
            ->paginate(5);
        }else{
            $outputs = DB::table('outputs as o')
            ->join('users as u', 'o.user_id', '=', 'u.id')
            ->select('o.output_id', 'o.output_date', 'u.name','o.reason')
            ->groupBy('o.output_id', 'o.output_date', 'u.name', 'o.reason')
            ->orderBy('o.output_date', 'desc')
            ->paginate(5);
        }

        return view('stock.output.index', ['outputs' => $outputs, 'searchText' => $query]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $output = Output::all();
        $products = DB::table('products as p')
        ->select(DB::raw('CONCAT(p.product_reference, " ", p.product_description) AS product'), 'p.product_id','p.quantity')
        ->where('p.stock', '>', '0')
        ->where('p.active', true)
        ->get();

        return view('stock.output.create', ['products' => $products]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            DB::beginTransaction();
            $output = new Output();
            $output->output_date = Carbon::now();
            $output->user_id = Auth::user()->id;
            $output->reason = $request->get('reason');
            $output->save();

            $product_id = $request->get('product_id');
            $quantity = $request->get('quantity_taken_out');

            $cont = 0;

            while($cont < count($product_id)){
                $output_detail = new OutputDetail();
                $output_detail->output_id = $output->output_id;
                $output_detail->product_id = $product_id[$cont];
                $output_detail->quantity_taken_out = $quantity[$cont];
                $output_detail->save();

                $product = Product::find($product_id[$cont]);
                $product->stock = $product->stock - $quantity[$cont];
                $product->gross_revenue = $product->stock * $product->purchase_price * $product->quantity;
                $product->update();

                $cont++;
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
        }

        return redirect('stock/outputs');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $output = DB::table('outputs as o')
        ->join('users as u', 'o.user_id', '=', 'u.id')
        ->join('outputs_detail as od', 'o.output_id', '=', 'od.output_id')
        ->join('products as p', 'od.product_id', '=', 'p.product_id')
        ->select('o.output_id', 'o.output_date', 'u.name', 'o.reason')
        ->where('o.output_id', '=', $id)
        ->first();

        $output_details = DB::table('outputs_detail as od')
        ->join('products as p', 'od.product_id', '=', 'p.product_id')
        ->select('p.product_reference', 'p.product_description','p.quantity', 'od.quantity_taken_out')
        ->where('od.output_id', '=', $id)
        ->get();

        return view('stock.output.show', ['output' => $output, 'outputs_detail' => $output_details]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
