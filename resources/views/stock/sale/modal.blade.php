<div class="modal fade" id="modal-delete-{{ $sale->sale_id }}">
    <div class="modal-dialog">
        <form action="{{ route('sales.destroy',$sale->sale_id)}}" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-content bg-danger">
                <div class="modal-header">
                    <h4 class="modal-title">Cancel sale</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel the sale number {{ $sale->sale_id}} ?</p>
                    <p>Client: {{ $sale->client_name}}</p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">No</button>
                    <button type="submit" class="btn btn-outline-light">Yes</button>
                </div>
            </div>
        </form>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>