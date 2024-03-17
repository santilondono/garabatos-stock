<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\RoleFormRequest;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
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
            $roles = DB::table('roles')
                ->where('role_description', 'LIKE', '%' . $query . '%')
                ->orderBy('role_id', 'asc')
                ->paginate(5);
        } else {
            // Si no hay texto de búsqueda, obtén todos los roles
            $roles = DB::table('roles')->orderBy('role_id', 'asc')->paginate(5);
        }
    
        return view('stock.role.index', ['roles' => $roles, 'searchText' => $query]);
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('stock.role.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleFormRequest $request)
    {
        $role = new Role();
        $role->role_description = $request->get('role_description');
        $role->save();
        return Redirect::to('stock/roles');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('stock.roles.show', ['role' => Role::findOrFail($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('stock.roles.edit', ['role' => Role::findOrFail($id)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleFormRequest $request, string $id)
    {
        $role = Role::findOrFail($id);
        $role->role_description = $request->get('role_description');
        $role->update();
        return Redirect::to('stock/roles');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return Redirect::to('stock/roles');
    }
}
