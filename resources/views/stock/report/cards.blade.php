@extends('layouts.admin')

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Reports</h1>
            </div>
        </div>
    </div>
</div>

<section class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            <h5><i class="fas fa-calendar-alt"></i> Select Date Range</h5>
            <form action="{{ route('stock-cards') }}" method="GET">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date:</label>
                        <input type="date" class="form-control date-input" id="start_date" name="start_date" value="{{ $start_date ? $start_date : '' }}">
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">End Date:</label>
                        <input type="date" class="form-control date-input" id="end_date" name="end_date" value="{{ $end_date ? $end_date : '' }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            </form>

        </div>

        <div class="col-md-8 d-flex justify-content-end align-items-center">
            <button class="btn btn-primary" onclick="printStockSummary()"><i class="fas fa-print"></i> Stock Summary</button>
            <button class="btn btn-primary" onclick="printSalesSummary()" style="margin-left: 10px;"><i class="fas fa-print"></i> Sales Summary</button>
        </div>
    </div>
    <div class="row mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chart-line"></i> Top 5 Selling Products</h5>
                    <br>
                    <!-- Display the data of the top 5 selling products here -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product Reference</th>
                                <th>Description</th>
                                <th>Quantity Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($top_selling_products as $product)
                            <tr>
                                <td>{{ $product->product_reference }}</td>
                                <td>{{ $product->product_description }}</td>
                                <td>{{ $product->quantity_sold }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-money-bill-wave"></i> Gross and Net Profits</h5>
                            <br>
                            <!-- Display the gross and net profits here -->
                            <ul class="list-group">
                                <li class="list-group-item">Gross Profit: ¥{{ $profits->gross_profit }}</li>
                                <li class="list-group-item">Net Profit: ¥{{ $profits->net_profit }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mt-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-briefcase"></i> Stock Value</h5>
                            <br>
                            <!-- Display the total value of the warehouse here -->
                            <ul class="list-group">
                                <li class="list-group-item">Total Value: ¥{{ $stock_value->stock_value }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    function printStockSummary() {
        var tools = "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=800,height=600";
        var ventana = window.open("{{ route('print-stock-summary')}}", '_blank', tools);
    }

    function printSalesSummary() {
        var tools = "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=800,height=600";
        var ventana = window.open("{{ route('print-sales-summary', ['start_date' => $start_date, 'end_date' => $end_date]) }}", '_blank', tools);
    }
</script>

@endsection