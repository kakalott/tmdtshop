@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="fw-bold text-primary mb-4">Sua Voucher {{ $voucher->code }}</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('admin.vouchers.update', $voucher->id) }}" method="POST">
                @method('PUT')
                @include('admin.vouchers._form')
            </form>
        </div>
    </div>
</div>
@endsection
