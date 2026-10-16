@extends('Layouts.admin')
@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Role Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Role Management</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="row" id="table-hover-row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-header">
                        <div class="col-xl-12">
                            <form action="{{route('roles.index')}}" method="get">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                        <div class="input-group mb-6">
                                            <input type="text" class="form-control" name="searchText" placeholder="Search Role" value="{{$searchText}}" aria-label="Recipient's username" aria-describedby="button-addon2">
                                            <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Search</button>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                        <div class="input-group mb-6">
                                            <a href="{{route('roles.create')}}" class="btn btn-success">New</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="card-body"></div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $role)
                                    <tr>
                                        <td>{{$role->id}}</td>
                                        <td>{{$role->role_description}}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary"><i class="fas fa-pencil-alt"></i> Editar</a>
                                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-delete-{{ $role->id }}"><i class="fas fa-trash-alt"></i> Eliminar</button>
                                            </div>
                                        </td>
                                    </tr>
                                    @include('stock.role.modal')
                                    @endforeach
                                </tbody>
                            </table>
                            {{$roles->links()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection