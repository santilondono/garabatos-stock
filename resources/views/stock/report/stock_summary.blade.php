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
                    <h3 class="card-title">Stock summary</h3>
                </div>
                <div class="card-body">
        
                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <label for="report_date">Report's date</label>
                                <p>{{ \Carbon\Carbon::now()->toDateString() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <table id="entry_details" class="table table-bordered table-striped ">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Purchase price</th>
                                        <th>Sale price</th>
                                        <th>Stock</th>
                                        <th>Comming</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stock_summary as $detail)
                                    <tr>
                                        <td>{{$detail->product_reference}} {{$detail->product_description}}</td>
                                        <td>¥{{$detail->purchase_price}}</td>
                                        <td>¥{{$detail->sale_price}}</td>
                                        <td>{{$detail->stock}}</td>
                                        <td>{{$detail->comming}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
