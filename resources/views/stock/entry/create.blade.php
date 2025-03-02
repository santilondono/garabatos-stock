@extends('Layouts.admin')
@section('content')

<div class="col-md-12" style="padding-top: 20px">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">New Entry</h3>
        </div>
        @error('entry_date')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        @error('user_id')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        @error('product_id')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        @error('quantity_entered')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        @error('purchase_price')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <form method="POST" action="{{ route('entries.store') }}" class="form">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="product_id">Product</label>
                            <select name="select_product_id" id="select_product_id" class="form-control selectpicker" data-live-search="true">
                                <option value="" disabled selected>Select a product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->product_id }}_{{ $product->quantity }}_{{ $product->purchase_price }}">{{ $product->product}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label for="quantity_entered">Entry</label>
                            <input type="number" name="pquantity_entered" id="pquantity_entered" class="form-control">
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label for="purchase_price">Purchase price</label>
                            <input type="number" name="ppurchase_price" id="ppurchase_price" class="form-control" step="0.01">
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <button type="button" id="add" class="btn btn-primary" style="margin-top: 32px;">Add</button>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin: 10px">
                    <div class="col-12">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" id="is_comming" name="is_comming">Is coming
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <table id="entry_details" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Actions</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Entry</th>
                                    <th>Purchase price</th>
                                    <th>Sub total</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Total</th>
                                    <th id="total_entry"><h4>0</h4></th> <!-- Total de "Entry" -->
                                    <th></th>
                                    <th id="total"><h4>¥0.00</h4></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary" id="save">Save</button>
                        <a href="{{ route('entries.index') }}" class="btn btn-danger">Cancel</a>
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
    var total = 0;
    var total_entry = 0; // Total de la columna "Entry"

    function reset() {
        $('#select_product_id').selectpicker('val', '');
        $('#pquantity_entered').val('');
        $('#ppurchase_price').val('');
    }

    function validate() {
        if (total > 0) {
            $('#save').show();
        } else {
            $('#save').hide();
        }
    }

    function showProductData() {
        let data = document.getElementById('select_product_id').value.split('_');
        $('#ppurchase_price').val(data[2]);
    }

    function remove(index) {
        total -= parseFloat(subtotal[index]);
        total = parseFloat(total).toFixed(2); // Redondear total después de la resta
        total_entry -= parseInt($('#row' + index + ' td:nth-child(4)').text());

        $('#total').html('<h4>¥' + total + '</h4>');
        $('#total_entry').html('<h4>' + total_entry + '</h4>'); // Actualizar el total de "Entry"
        $('#row' + index).remove();
        validate();
    }

    function add() {
        let data = document.getElementById('select_product_id').value.split('_');

        let product_id = data[0];
        let product = $('#select_product_id option:selected').text();
        let qty = data[1];
        let quantity = $('#pquantity_entered').val();
        let purchase_price = $('#ppurchase_price').val();

        if (product_id !== '' && quantity !== '' && quantity > 0 && purchase_price !== '') {
            subtotal[cont] = parseFloat(quantity * purchase_price * qty).toFixed(2); // Redondear a 2 decimales
            total += parseFloat(subtotal[cont]);
            total = parseFloat(total).toFixed(2);

            total_entry += parseInt(quantity); // Sumar la cantidad ingresada al total de "Entry"

            let row = '<tr class="selected" id="row' + cont + '">\n\
                <td><button type="button" class="btn btn-warning" onclick="remove(' + cont + ');">Remove</button></td>\n\
                <td><input type="hidden" name="product_id[]" value="' + product_id + '">' + product + '</td>\n\
                <td><input type="hidden" name="qty" value="' + qty + '">' + qty + '</td>\n\
                <td><input type="hidden" name="quantity_entered[]" value="' + quantity + '">' + quantity + '</td>\n\
                <td><input type="hidden" name="purchase_price[]" value="' + purchase_price + '">¥' + parseFloat(purchase_price).toFixed(2) + '</td>\n\
                <td>¥' + subtotal[cont] + '</td></tr>';

            cont++;
            reset();
            $('#total').html('<h4>¥' + total + '</h4>');
            $('#total_entry').html('<h4>' + total_entry + '</h4>'); // Actualizar el total de "Entry"
            validate();
            $('#entry_details').append(row);
        } else {
            alert('Error: Check the entered data');
        }
    }

    $('#save').hide();
    $('#select_product_id').change(showProductData);
</script>
@endpush
@endsection
