@extends('Layouts.admin')
@section('content')

<div class="col-md-12" style="padding-top: 20px">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Entry Detail</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label for="entry_date">Entry Date</label>
                        <p>{{$entry->entry_date}}</p>
                    </div>
                </div>
                <div class="col-8">
                    <div class="form-group">
                        <label for="entry_date">User</label>
                        <p>{{$entry->name}}</p>
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
                                <th>Entry</th>
                                <th>Purchase price</th>
                                <th>Sub total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entries_detail as $detail)
                            <tr>
                                <td>{{$detail->product_reference}} {{$detail->product_description}}</td>
                                <td>{{$detail->quantity}}</td>
                                <td>{{$detail->quantity_entered}}</td>
                                <td>¥{{$detail->purchase_price}}</td>
                                <td>¥{{$detail->subtotal}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4">Total</th>
                                <th id="total">
                                    <h4>¥{{$entry->total}}</h4>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('entries.index') }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection