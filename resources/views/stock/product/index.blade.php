@extends('Layouts.admin')
@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Products Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Products Management</li>
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
                            <form action="{{route('products.index')}}" method="get">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                        <div class="input-group mb-6">
                                            <input type="text" class="form-control" name="searchText" placeholder="Search Product" value="{{$searchText}}" aria-label="Recipient's username" aria-describedby="button-addon2">
                                            <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Search</button>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                        <div class="input-group mb-6">
                                            <a href="{{route('products.create')}}" class="btn btn-success">New</a>
                                            <button class="btn btn-info" style="margin-left: 10px;"><span class="fas fa-redo-alt"></span></button>
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
                                        <th>Product reference</th>
                                        <th>Image</th>
                                        <th>List description</th>
                                        <th>Product description</th>
                                        <th>Purchase price</th>
                                        <th>Sale price</th>
                                        <th>Weight</th>
                                        <th>Length</th>
                                        <th>Width</th>
                                        <th>Height</th>
                                        <th>Cubic meter</th>
                                        <th>Quantity</th>
                                        <th>Stock</th>
                                        <th>Gross revenue</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                    <tr>
                                        <td>{{$product->product_id}}</td>
                                        <td>{{$product->product_reference}}</td>
                                        <td><img src="{{ asset('dist/img/' . $product->image) }}" alt="" width="100"></td>
                                        <td>{{$product->list_description}}</td>
                                        <td>{{$product->product_description}}</td>
                                        <td>{{"¥".$product->purchase_price}}</td>
                                        <td>{{"¥".$product->sale_price}}</td>
                                        <td>{{$product->weight}}</td>
                                        <td>{{$product->length}}</td>
                                        <td>{{$product->width}}</td>
                                        <td>{{$product->height}}</td>
                                        <td>{{$product->cubic_meter}}</td>
                                        <td>{{$product->quantity}}</td>
                                        <td>{{$product->stock}}</td>
                                        <td>{{"¥".$product->gross_revenue}}</td>
                                        <td>
                                            <a href="{{ route('products.edit',$product->product_id) }}" class="btn btn-primary"><i class="fas fa-pencil-alt"></i></a>
                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-delete-{{ $product->product_id }}"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                    @include('stock.product.modal')
                                    @endforeach
                                </tbody>
                            </table>
                            {{$products->links()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection