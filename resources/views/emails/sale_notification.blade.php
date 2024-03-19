<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        /* Estilos para el contenedor principal */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        /* Estilos para la imagen */
        .logo-img {
            display: block;
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
        }

        /* Estilos para los encabezados */
        h2, h3 {
            margin: 0;
            margin-bottom: 10px;
        }

        /* Estilos para la tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        /* Estilos para el botón */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <div class="container">
        <a href="#">
            <img src="https://garabatos-stock.com/dist/img/garabatos_logo.png" alt="Garabatos_logo" class="logo-img">
        </a>

        <h2>New sale</h2>
        <h3>#{{$sale->sale_id}}</h3>
        <h3>Date: {{$sale->sale_date}}</h3>
        <h3>Client: {{$sale->client_name}} - {{$sale->country}}</h3>
        <h3>User: {{$sale->name}}</h3>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity sold</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saleDetails as $detail)
                <tr>
                    <td>{{$detail->product_reference}} {{$detail->product_description}}</td>
                    <td>¥{{$detail->sale_price}}</td>
                    <td>{{$detail->quantity_sold}}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="2">Total</td>
                    <td><h3>¥{{$sale->total}}</h3></td>
                </tr>
            </tbody>
        </table>

        <a href="{{ route('sales.show',$sale->sale_id) }}" class="btn">See sale</a>
    </div>

</body>

</html>
