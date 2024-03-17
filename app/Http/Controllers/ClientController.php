<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\ClientFormRequest;//Importar request
use Illuminate\Support\Facades\DB; //Importar conexion a BD


class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request){    
        
        $query = $request->input('searchText');

        if($query){
            $clients = DB::table('clients')
                ->where('client_name','LIKE','%'.$query.'%')
                ->orderBy('client_id','desc')
                ->paginate(5);
        }else{
            $clients = DB::table('clients')
                ->orderBy('client_id','desc')
                ->paginate(5);
        }

        return view('stock.client.index', ['clients' => $clients, 'searchText' => $query]);
    }    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('stock.client.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClientFormRequest $request)
    {
        
        $client = new Client();
        $client->client_name = $request->get('client_name');
        $client->shipping_mark = $request->get('shipping_mark');
        $client->country = $request->get('country');
        $client->save();
        return Redirect::to('stock/clients');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('stock.client.show', ['client' => Client::findOrFail($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('stock.client.edit', ['client'=> Client::findOrFail($id)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClientFormRequest $request, string $id)
    {
        $client = Client::findOrFail($id);
        $client->client_name = $request->get('client_name');
        $client->shipping_mark = $request->get('shipping_mark');
        $client->country = $request->get('country');
        $client->update();
        return Redirect::to('stock/clients');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $client = Client::findOrFail($id);
        $client->delete();
        return Redirect::to('stock/clients');
    }
}
