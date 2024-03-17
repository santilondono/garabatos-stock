@extends('Layouts.admin')
@section('content')

<div class="col-md-12" style="padding-top: 20px">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Output Detail</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label for="output_date">Output Date</label>
                        <p>{{$output->output_date}}</p>
                    </div>
                </div>
                <div class="col-8">
                    <div class="form-group">
                        <label for="output_date">User</label>
                        <p>{{$output->name}}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <p>{{$output->reason}}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <table id="output_details" class="table table-bordered table-striped ">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Output</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($outputs_detail as $detail)
                            <tr>
                                <td>{{$detail->product_reference}} {{$detail->product_description}}</td>
                                <td>{{$detail->quantity}}</td>
                                <td>{{$detail->quantity_taken_out}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('outputs.index') }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection