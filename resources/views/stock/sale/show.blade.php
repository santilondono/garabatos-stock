@extends('Layouts.admin')
@section('content')

<div class="col-md-12" style="padding-top: 20px">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Sale Detail</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-2">
                    <div class="form-group">
                        <label for="sale_id">Sale ID</label>
                        <p>{{$sale->sale_id}}</p>
                    </div>
                </div>
                <div class="col-7">
                    <div class="form-group">
                        <label for="sale_date">Client</label>
                        <p>{{$sale->client_name}}</p>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <h3>
                            @if($sale->is_cancelled)
                            <span class="badge badge-danger">Cancelled</span>
                            @else
                            <span class="badge badge-success">Active</span>
                            @endif
                        </h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label for="entry_date">Sale Date</label>
                        <p>{{$sale->sale_date}}</p>
                    </div>
                </div>
                <div class="col-8">
                    <div class="form-group">
                        <label for="entry_date">User</label>
                        <p>{{$sale->name}}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <table id="entry_details" class="table table-bordered table-striped ">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Sale price</th>
                                <th>Sale</th>
                                <th>Sub total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales_detail as $detail)
                            <tr>
                                <td>{{$detail->product_reference}} {{$detail->product_description}}</td>
                                <td>{{$detail->quantity}}</td>
                                <td>¥{{$detail->sale_price}}</td>
                                <td>{{$detail->quantity_sold}}</td>
                                <td>¥{{$detail->subtotal}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4">Total</th>
                                <th id="total">
                                    <h4>¥{{$sale->total}}</h4>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    @if(Auth::check() && Auth::user()->role_id == 1)
                        <a href="{{ route('sales.index') }}" class="btn btn-primary">Back</a>
                    @endif
                    @if(Auth::check() && Auth::user()->role_id == 2)
                        <a href="{{ route('home') }}" class="btn btn-primary">Back</a>
                    @endif
                    @if(Auth::check() && Auth::user()->role_id == 1)
                        @if(!$sale->is_cancelled)
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-delete-{{ $sale->sale_id }}">Cancel Sale</button>
                        @endif
                    @endif
                    <button class="btn btn-dark" onclick="printSaleWindow()">Print</button>
                </div>
                @include('stock.sale.modal')
            </div>
        </div>
    </div>
</div>
<script>
    function printSaleWindow() {
        var tools = "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=800,height=600";
        var ventana = window.open("{{ route('print-sale', ['sale_id' => $sale->sale_id]) }}", '_blank', tools);

        ventana.onload = function() {
            ventana.print();
        }
    }
</script>
@endsection