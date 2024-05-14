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
                    <h3 class="card-title">Sales report</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <label for="report_date">Report's date</label>
                                <p>{{ \Carbon\Carbon::now()->toDateString() }}</p>
                            </div>
                        </div>
                        <!-- date start date end -->
                        <div class="col-4">
                            <div class="form-group">
                                <label for="init_date">Date's range</label>
                                <p>{{ $start_date }} - {{ $end_date }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <table id="entry_details" class="table table-bordered table-striped ">
                                <thead>
                                    <tr>
                                        <th>Sale</th>
                                        <th>Sale date</th>
                                        <th>User</th>
                                        <th>Client</th>
                                        <th>Gross profit</th>
                                        <th>Net profit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sales_summary as $detail)
                                    <tr>
                                        <td>{{$detail->sale_id}}</td>
                                        <td>{{$detail->sale_date}}</td>
                                        <td>{{$detail->user_name}}</td>
                                        <td>{{$detail->client_name}}</td>
                                        <td>¥{{$detail->gross_profit}}</td>
                                        <td>¥{{$detail->net_profit}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>

                                <tr>
                                    <td colspan="4"></td>
                                    <td><strong>Total gross profit</strong></td>
                                    <td><strong>¥{{$total_summary->gross_profit}}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td><strong>Total net profit</strong></td>
                                    <td><strong>¥{{$total_summary->net_profit}}</strong></td>
                                </tr>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>