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
        let row = $('#row' + index);
        let entryValue = parseInt(row.find('td:nth-child(4)').text()); // Obtener el Entry antes de eliminar
        let subTotalValue = parseFloat(row.find('td:nth-child(6)').text().replace('¥', '')); // Obtener el subtotal antes de eliminar

        total -= subTotalValue;
        total_entry -= entryValue;

        $('#total').html('<h4>¥' + total.toFixed(2) + '</h4>');
        $('#total_entry').html('<h4>' + total_entry + '</h4>');

        row.remove();
        validate();
    }

    function add() {
        let data = document.getElementById('select_product_id').value.split('_');

        let product_id = data[0];
        let product = $('#select_product_id option:selected').text();
        let qty = data[1];  // Cantidad en stock (no se usa en el subtotal)
        let quantity = $('#pquantity_entered').val();
        let purchase_price = $('#ppurchase_price').val();

        if (product_id !== '' && quantity !== '' && quantity > 0 && purchase_price !== '') {
            let index = $('tbody tr').length; // Usar cantidad de filas en la tabla
            let subTotalValue = parseFloat(quantity * purchase_price * qty);

            subtotal[index] = subTotalValue;
            total += subTotalValue;
            total_entry += parseInt(quantity);

            let row = '<tr class="selected" id="row' + index + '">\n\
                <td><button type="button" class="btn btn-warning" onclick="remove(' + index + ');">Remove</button></td>\n\
                <td><input type="hidden" name="product_id[]" value="' + product_id + '">' + product + '</td>\n\
                <td><input type="hidden" name="qty" value="' + qty + '">' + qty + '</td>\n\
                <td><input type="hidden" name="quantity_entered[]" value="' + quantity + '">' + quantity + '</td>\n\
                <td><input type="hidden" name="purchase_price[]" value="' + purchase_price + '">¥' + parseFloat(purchase_price).toFixed(2) + '</td>\n\
                <td>¥' + subTotalValue.toFixed(2) + '</td></tr>';

            reset();
            $('#total').html('<h4>¥' + total.toFixed(2) + '</h4>');
            $('#total_entry').html('<h4>' + total_entry + '</h4>');
            validate();
            $('#entry_details tbody').append(row);
        } else {
            alert('Error: Check the entered data');
        }
    }

    $('#save').hide();
    $('#select_product_id').change(showProductData);
</script>
@endpush
@endsection
