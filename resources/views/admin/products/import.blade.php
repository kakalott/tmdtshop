@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Import Products from Excel</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="file">Excel file (.xlsx, .xls, .csv)</label>
            <input type="file" name="file" id="file" class="form-control" required>
        </div>
        <button class="btn btn-primary mt-2">Upload & Import</button>
    </form>

    <hr />
    <h4>Template</h4>
    <p>Header columns: <code>name, sku, price, stock_quantity, category, description</code></p>
</div>
@endsection
