@extends('Layouts.admin')
@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit Role</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Role Management</a></li>
                    <li class="breadcrumb-item active">Edit Role</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container">
    @error('role_description')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    <form action="{{ route('roles.update', $role->id) }}" method="post" class="form">
        @csrf
        @method('PUT')
        <div class="form-group" style="max-width:50%">
            <label for="role_description">Description</label>
            <input type="text" class="form-control" id="role_description" name="role_description" value="{{ old('role_description', $role->role_description) }}" maxlength="30" required>
        </div>
        <button type="button" onclick=window.history.back() class="btn btn-danger">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
