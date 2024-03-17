@extends('Layouts.admin')
@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit Product</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products Management</a></li>
                    <li class="breadcrumb-item active">Edit Product</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container">
    @error('product_reference')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    @error('cubic_meter')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    @error('list_description')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    @error('product_description')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    @error('purchase_price')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    @error('sale_price')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    @error('weight')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    @error('length')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    @error('width')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    @error('height')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    @error('quantity')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    @error('image')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    @error('stock')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    <form action="{{route('products.update',$product->product_id)}}" method="post" class="form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-6">
                <div class="form-group" style="max-width:50%">
                    <label for="product_reference">Product reference</label>
                    <input type="text" class="form-control" id="product_reference" name="product_reference" value="{{ $product->product_reference }}" required>
                </div>
                <div class="form-group" style="max-width:60%">
                    <label for="image">Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="list_description">List description</label>
                    <input type="text" class="form-control" id="list_description" name="list_description" value="{{ $product->list_description }}" required>
                </div>
                <div class="form-group">
                    <label for="product_description">Product description</label>
                    <input type="text" class="form-control" id="product_description" name="product_description" value="{{ $product->product_description }}" required>
                </div>
                <div class="form-group" style="max-width:50%">
                    <label for="purchase_price">Purchase price</label>
                    <input type="number" step="0.0001" class="form-control" id="purchase_price" name="purchase_price" value="{{ $product->purchase_price}}" required>
                </div>
                <div class="form-group" style="max-width:50%">
                    <label for="sale_price">Sale price</label>
                    <input type="number" step="0.0001" class="form-control" id="sale_price" name="sale_price" value="{{ $product->sale_price }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group" style="max-width:50%">
                    <label for="weight">Weight</label>
                    <input type="number" step="0.0001" class="form-control" id="weight" name="weight" value="{{ $product->weight }}" required>
                </div>
                <div class="form-group" style="max-width:50%">
                    <label for="length">Length</label>
                    <input type="number" step="0.0001" class="form-control" id="length" name="length" value="{{ $product->length }}" required>
                </div>
                <div class="form-group" style="max-width:50%">
                    <label for="width">Width</label>
                    <input type="number" step="0.0001" class="form-control" id="width" name="width" value="{{ $product->width }}" required>
                </div>
                <div class="form-group" style="max-width:50%">
                    <label for="height">Height</label>
                    <input type="number" step="0.0001" class="form-control" id="height" name="height" value="{{ $product->height }}" required>
                </div>
                <div class="form-group" style="max-width:50%">
                    <label for="quantity">Quantity</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" value="{{ $product->quantity }}" required>
                </div>
            </div>
        </div>

        <button type="button" onclick=window.history.back() class="btn btn-danger">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection