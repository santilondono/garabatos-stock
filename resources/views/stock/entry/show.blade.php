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
                            @php
                                $total_entry = 0; // Variable para calcular total de "Entry"
                            @endphp
                            @foreach($entries_detail as $detail)
                            @php
                                $total_entry += $detail->quantity_entered; // Sumar al total
                            @endphp
                            <tr>
                                <td>{{$detail->product_reference}} {{$detail->product_description}}</td>
                                <td>{{$detail->quantity}}</td>
                                <td>{{$detail->quantity_entered}}</td>
                                <td>¥{{ number_format($detail->purchase_price, 2) }}</td>
                                <td>¥{{ number_format($detail->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2">Total</th>
                                <th id="total_entry">
                                    <h4>{{$total_entry}}</h4>
                                </th>
                                <th></th>
                                <th id="total">
                                    <h4>¥{{ number_format($entry->total, 2) }}</h4>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
