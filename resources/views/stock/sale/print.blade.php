<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Detail</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div id="print-content"> <!-- Contenedor para el contenido que se imprimirá -->

        <div class="col-md-12" style="padding-top: 20px">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <img src="{{asset('dist\img\garabatos_logo.png')}}" alt="Logo" style="width: 30%">
                    </h3>
                </div>
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
                                    <span class="card-title">Cancelled</span>
                                    @else
                                    <span class="card-title">Active</span>
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
                </div>
            </div>
        </div>
    </div>
</body>

</html>
