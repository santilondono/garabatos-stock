@extends('Layouts.admin')
@section('content')

<div class="col-md-12" style="padding-top: 20px">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">New Output</h3>
        </div>
        @error('output_date')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        @error('user_id')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        @error('product_id')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        @error('quantity_taken_out')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <form method="POST" action="{{ route('outputs.store') }}" class="form">
            @csrf
            <div class="card-body">
            <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="reason">Reason</label>
                            <input type="text" name="reason" id="reason" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="form-group">
                            <label for="product_id">Product</label>
                            <select name="select_product_id" id="select_product_id" class="form-control selectpicker" data-live-search="true">
                                <option value="" disabled selected>Select a product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->product_id }}_{{ $product->quantity }}" >{{ $product->product}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label for="quantity_entered">Output</label>
                            <input type="number" name="pquantity_taken_out" id="pquantity_taken_out" class="form-control">
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
                        <table id="output_details" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Actions</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Output</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary" id="save">Save</button>
                        <a href="{{ route('outputs.index') }}" class="btn btn-danger">Cancel</a>
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
    var rows = 0;

    function reset() {
        $('#select_product_id').selectpicker('val', '');
        $('#pquantity_taken_out').val('');
    }

    function validate() {
        if (rows > 0) {
            $('#save').show();
        } else {
            $('#save').hide();
        }
    }

    function remove(index) {
        $('#row' + index).remove();
        rows--;
        validate();
    }

    function add() {
        data = document.getElementById('select_product_id').value.split('_');

        product_id = data[0];
        product = $('#select_product_id option:selected').text();
        qty = data[1];
        quantity = $('#pquantity_taken_out').val();

        if (product_id != '' && quantity != '' && quantity > 0 ) {

            var row = '<tr class="selected" id="row' + cont + '">\n\
                <td><button type="button" class="btn btn-warning" onclick="remove(' + cont + ');">Remove</button></td>\n\
                <td><input type="hidden" name="product_id[]" value="' + product_id + '">' + product + '</td>\n\
                <td><input type="hidden" name="qty" value="' + qty + '">' + qty + '</td>\n\
                <td><input type="hidden" name="quantity_taken_out[]" value="' + quantity + '">' + quantity + '</td>\n\
                </tr>';

            cont++;
            rows++;
            reset();
            validate();
            $('#output_details').append(row);

        } else {
            alert('Error: Check the entered data');
        }
    }

    $('#save').hide();
</script>
@endpush
@endsection