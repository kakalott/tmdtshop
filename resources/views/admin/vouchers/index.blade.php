@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">Quan Ly Voucher</h2>
        <a href="{{ route('admin.vouchers.create') }}" class="btn btn-primary fw-bold">+ Them voucher</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Ma</th>
                        <th>Ten</th>
                        <th>Gia tri</th>
                        <th>Dieu kien</th>
                        <th>Luot dung</th>
                        <th>Thoi gian</th>
                        <th>Trang thai</th>
                        <th class="text-end">Thao tac</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                        <tr>
                            <td class="fw-bold text-primary">{{ $voucher->code }}</td>
                            <td>{{ $voucher->name }}</td>
                            <td>
                                @if($voucher->type === 'percent')
                                    {{ rtrim(rtrim(number_format($voucher->value, 2), '0'), '.') }}%
                                    @if($voucher->max_discount_amount)
                                        <br><small class="text-muted">Toi da {{ number_format($voucher->max_discount_amount, 0, ',', '.') }}d</small>
                                    @endif
                                @else
                                    {{ number_format($voucher->value, 0, ',', '.') }}d
                                @endif
                            </td>
                            <td>Don tu {{ number_format($voucher->min_order_amount, 0, ',', '.') }}d</td>
                            <td>
                                {{ $voucher->used_count }}{{ $voucher->usage_limit ? '/' . $voucher->usage_limit : '' }}
                                @if($voucher->usage_limit_per_user)
                                    <br><small class="text-muted">{{ $voucher->usage_limit_per_user }} lan/user</small>
                                @endif
                            </td>
                            <td>
                                <small>
                                    {{ optional($voucher->starts_at)->format('d/m/Y H:i') ?? 'Khong gioi han' }}<br>
                                    {{ optional($voucher->ends_at)->format('d/m/Y H:i') ?? 'Khong gioi han' }}
                                </small>
                            </td>
                            <td>
                                <span class="badge {{ $voucher->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $voucher->is_active ? 'Dang bat' : 'Dang tat' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.vouchers.edit', $voucher->id) }}" class="btn btn-sm btn-warning">Sua</a>
                                <form action="{{ route('admin.vouchers.destroy', $voucher->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Xoa voucher nay?')">Xoa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Chua co voucher nao.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
