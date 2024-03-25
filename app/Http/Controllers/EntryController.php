<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\EntryFormRequest;
use App\Models\Entry;
use App\Models\EntryDetail;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EntryController extends Controller
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
            $entries = DB::table('entries as e')
            ->join('users as u', 'e.user_id', '=', 'u.id')
            ->join('entries_detail as ed', 'e.entry_id', '=', 'ed.entry_id')
            ->join('products as p', 'ed.product_id', '=', 'p.product_id')
            ->select('e.entry_id', 'e.entry_date', 'u.name', 'e.is_comming',DB::raw('ROUND(sum(ed.quantity_entered * ed.purchase_price * p.quantity),2) as total'))
            ->where('e.entry_id', 'LIKE', '%' . $query . '%')
            ->orWhere('u.name', 'LIKE', '%' . $query . '%')
            ->groupBy('e.entry_id', 'e.entry_date', 'u.name', 'e.is_comming')
            ->orderBy('e.entry_date', 'desc')
            ->paginate(5);
        }else{
            $entries = DB::table('entries as e')
            ->join('users as u', 'e.user_id', '=', 'u.id')
            ->join('entries_detail as ed', 'e.entry_id', '=', 'ed.entry_id')
            ->join('products as p', 'ed.product_id', '=', 'p.product_id')
            ->select('e.entry_id', 'e.entry_date', 'u.name', 'e.is_comming', DB::raw('ROUND(sum(ed.quantity_entered * ed.purchase_price * p.quantity),2) as total'))
            ->groupBy('e.entry_id', 'e.entry_date', 'u.name', 'e.is_comming')
            ->orderBy('e.entry_date', 'desc')
            ->paginate(5);
        }

        return view('stock.entry.index', ['entries' => $entries, 'searchText' => $query]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $entry = Entry::all();
        $products = DB::table('products as p')
        ->select(DB::raw('CONCAT(p.product_reference, " ", p.product_description) AS product'), 'p.product_id','p.purchase_price','p.quantity','p.active')
        ->where('p.active', true)
        ->get();

        return view('stock.entry.create', ['entry' => $entry, 'products' => $products]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EntryFormRequest $request)
    {  
        try{
            DB::beginTransaction();
            $entry = new Entry();
            $entry->entry_date = Carbon::now();           
            $entry->user_id = Auth::user()->id;
            if($request->get('is_comming') == 'on'){
                $entry->is_comming = true;
            }else{
                $entry->is_comming = false;
            }
            $entry->save();

            $product_id = $request->get('product_id');
            $quantity = $request->get('quantity_entered');
            $purchase_price = $request->get('purchase_price');

            $cont = 0;

            while($cont < count($product_id)){
                $entry_detail = new EntryDetail();
                $entry_detail->entry_id = $entry->entry_id;
                $entry_detail->product_id = $product_id[$cont];
                $entry_detail->quantity_entered = $quantity[$cont];
                $entry_detail->purchase_price = $purchase_price[$cont];
                $entry_detail->save();

                $product = Product::find($product_id[$cont]);
                $product->stock = $product->stock + $quantity[$cont];
                $product->purchase_price = $purchase_price[$cont];
                $product->gross_revenue = $product->stock * $product->purchase_price * $product->quantity;
                if($entry->is_comming){
                    $product->comming += $quantity[$cont];
                }
                $product->update();

                $cont++;

            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
        }

        return Redirect::to('stock/entries');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $entry = DB::table('entries as e')
        ->join('users as u', 'e.user_id', '=', 'u.id')
        ->join('entries_detail as ed', 'e.entry_id', '=', 'ed.entry_id')
        ->join('products as p', 'ed.product_id', '=', 'p.product_id')
        ->select('e.entry_id', 'e.entry_date', 'u.name', DB::raw('sum(ed.quantity_entered * ed.purchase_price * p.quantity) as total'))
        ->where('e.entry_id', '=', $id)
        ->groupBy('e.entry_id', 'e.entry_date', 'u.name')
        ->orderBy('e.entry_id', 'desc')
        ->first();

        $entry_details = DB::table('entries_detail as ed')
        ->join('products as p', 'ed.product_id', '=', 'p.product_id')
        ->select('p.product_reference', 'p.product_description','p.quantity', 'ed.quantity_entered', 'ed.purchase_price', DB::raw('ed.quantity_entered * ed.purchase_price * p.quantity as subtotal'))
        ->where('ed.entry_id', '=', $id)
        ->get();

        return view('stock.entry.show', ['entry' => $entry, 'entries_detail' => $entry_details]);
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
        try{
            DB::beginTransaction();
            $entry = Entry::findOrFail($id);
            $entry->is_comming = false;
            $entry->update();

            $entry_details = EntryDetail::where('entry_id', $id)->get();

            foreach($entry_details as $detail){
                $product = Product::find($detail->product_id);
                $product->comming -= $detail->quantity_entered;
                $product->update();
            }

            DB::commit();

            return Redirect::to('stock/entries');
        }catch(\Exception $e){

            DB::rollback();
        }
    }
}
