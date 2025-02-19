@extends('Layouts.admin')
@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Entry Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Entry Management</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="row" id="table-hover-row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-header">
                        <div class="col-xl-12">
                            <form action="{{route('entries.index')}}" method="get">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                        <div class="input-group mb-6">
                                            <input type="text" class="form-control" name="searchText" placeholder="Search entry" value="{{$searchText}}" aria-label="Recipient's username" aria-describedby="button-addon2">
                                            <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Search</button>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                        <div class="input-group mb-6">
                                            <a href="{{route('entries.create')}}" class="btn btn-success">New</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="card-body"></div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Date</th>
                                        <th>User</th>
                                        <th>Is coming</th>
                                        <th>Total purchase</th>
                                        <th>Confirm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($entries as $entry)
                                    <tr data-toggle="tooltip" title="{{ $entry->product_details }}">
                                        <td>{{ $entry->entry_id }}</td>
                                        <td>{{ $entry->entry_date }}</td>
                                        <td>{{ $entry->name }}</td>
                                        <td>
                                            @if ($entry->is_comming)
                                            <span class="badge badge-success">Yes</span>
                                            @else
                                            <span class="badge badge-danger">No</span>
                                            @endif
                                        </td>
                                        <td>¥{{ $entry->total }}</td>
                                        <td>
                                            @if ($entry->is_comming)
                                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-delete-{{ $entry->entry_id }}"><i class="fas fa-check"></i></button>
                                            @endif
                                            <a href="{{ route('entries.show', $entry->entry_id) }}" class="btn btn-primary"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                    @include('stock.entry.modal')
                                    @endforeach
                                </tbody>
                            </table>
                            {{$entries->links()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>