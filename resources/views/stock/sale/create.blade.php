@extends('Layouts.admin')
@section('content')

<div class="col-md-12" style="padding-top: 20px">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">New Sale</h3>
        </div>
        <form method="POST" action="{{ route('sales.store') }}" class="form">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-8">
                        <div class="form-group">
                            <label for="client_id">Client</label>
                            <select name="client_id" id="client_id" class="form-control selectpicker" data-live-search="true" required>
                                <option value="" disabled selected>Select a client</option>
                                @foreach($clients as $client)
                                <option value="{{ $client->client_id }}">{{ $client->client }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="product_id">Product</label>
                            <select name="select_product_id" id="select_product_id" class="form-control selectpicker" data-live-search="true">
                                <option value="" disabled selected>Select a product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->product_id }}_{{ $product->quantity }}_{{ $product->sale_price }}_{{$product->stock}}">{{ $product->product}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-1">
                        <div class="form-group">
                            <label for="quantity_entered">Sale</label>
                            <input type="number" name="pquantity_sold" id="pquantity_sold" class="form-control">
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label for="purchase_price">Sale price</label>
                            <input type="number" name="psale_price" id="psale_price" class="form-control" step="0.01">
                        </div>
                    </div>
                    <div class="col-1">
                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input type="number" name="stock" id="stock" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <button type="button" id="add" class="btn btn-primary" style="margin-top: 32px;">Add</button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <table id="sale_details" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Actions</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Sale</th>
                                    <th>Sale price</th>
                                    <th>Sub total</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5">Total</th>
                                    <th id="total">
                                        <h4>¥0.00</h4>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary" id="save">Save</button>
                        @if(Auth::check() && Auth::user()->role_id == 2)
                        <a href="/stock-now" class="btn btn-danger">Cancel</a>
                        @endif
                        @if(Auth::check() && Auth::user()->role_id == 1)
                        <a href="{{ route('sales.index') }}" class="btn btn-danger">Cancel</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
    $(document).ready(function() {
        $('#add').click(function() {
            add();
        });

    });

    var cont = 0;
    var subtotal = [];
    total = 0;

    function reset() {
        $('#select_product_id').selectpicker('val', '');
        $('#pquantity_sold').val('');
        $('#psale_price').val('');
        $('#stock').val('');
    }

    function validate() {
        if (total > 0) {
            $('#save').show();
        } else {
            $('#save').hide();
        }
    }

    function showProductData() {
        data = document.getElementById('select_product_id').value.split('_');
        console.log(data);
        $('#psale_price').val(data[2]);
        $('#stock').val(data[3]);
    }

    function remove(index) {
        total = total - subtotal[index];
        $('#total').html('<h4>¥' + total + '</h4>');
        $('#row' + index).remove();
        validate();
    }

    function add() {
        data = document.getElementById('select_product_id').value.split('_');

        product_id = data[0];
        product = $('#select_product_id option:selected').text();
        stock = data[3];
        qty = data[1];
        quantity = $('#pquantity_sold').val();
        sale_price = $('#psale_price').val();

        if (product_id != '' && quantity != '' && quantity > 0 && sale_price != ''  && parseInt(stock) >= parseInt(quantity)) {
            subtotal[cont] = (quantity * sale_price * qty);
            total = total + subtotal[cont];
            total = parseFloat(total.toFixed(2));

            var row = '<tr class="selected" id="row' + cont + '">\n\
                <td><button type="button" class="btn btn-warning" onclick="remove(' + cont + ');">Remove</button></td>\n\
                <td><input type="hidden" name="product_id[]" value="' + product_id + '">' + product + '</td>\n\
                <td><input type="hidden" name="qty" value="' + qty + '">' + qty + '</td>\n\
                <td><input type="hidden" name="quantity_sold[]" value="' + quantity + '">' + quantity + '</td>\n\
                <td><input type="hidden" name="sale_price[]" value="' + sale_price + '">¥' + sale_price + '</td>\n\
                <td>¥' + subtotal[cont].toFixed(2) + '</td></tr>';

            cont++;
            reset();
            $('#total').html('<h4>¥' + total + '</h4>');
            validate();
            $('#sale_details').append(row);
        } else {
            alert('Error: Check the entered data');
        }
    }

    $('#save').hide();
    $('#select_product_id').change(showProductData);
</script>
@endpush
@endsection