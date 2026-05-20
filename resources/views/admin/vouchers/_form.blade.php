@csrf

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Ma voucher</label>
        <input type="text" name="code" class="form-control text-uppercase" value="{{ old('code', $voucher->code) }}" required>
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label fw-bold">Ten chuong trinh</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $voucher->name) }}">
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Loai giam</label>
        <select name="type" class="form-select" required>
            <option value="fixed" {{ old('type', $voucher->type ?: 'fixed') === 'fixed' ? 'selected' : '' }}>Giam tien co dinh</option>
            <option value="percent" {{ old('type', $voucher->type) === 'percent' ? 'selected' : '' }}>Giam theo phan tram</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Gia tri</label>
        <input type="number" name="value" class="form-control" min="0" step="0.01" value="{{ old('value', $voucher->value) }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Giam toi da</label>
        <input type="number" name="max_discount_amount" class="form-control" min="0" value="{{ old('max_discount_amount', $voucher->max_discount_amount) }}" placeholder="Cho voucher %">
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Don toi thieu</label>
        <input type="number" name="min_order_amount" class="form-control" min="0" value="{{ old('min_order_amount', $voucher->min_order_amount ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Tong luot dung</label>
        <input type="number" name="usage_limit" class="form-control" min="1" value="{{ old('usage_limit', $voucher->usage_limit) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Luot dung / user</label>
        <input type="number" name="usage_limit_per_user" class="form-control" min="1" value="{{ old('usage_limit_per_user', $voucher->usage_limit_per_user) }}">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Bat dau</label>
        <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', optional($voucher->starts_at)->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Ket thuc</label>
        <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', optional($voucher->ends_at)->format('Y-m-d\TH:i')) }}">
    </div>
</div>

<div class="form-check form-switch mb-4">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ old('is_active', $voucher->exists ? $voucher->is_active : true) ? 'checked' : '' }}>
    <label class="form-check-label fw-bold" for="is_active">Dang kich hoat</label>
</div>

<button type="submit" class="btn btn-primary fw-bold px-4">Luu voucher</button>
<a href="{{ route('admin.vouchers.index') }}" class="btn btn-outline-secondary px-4">Huy</a>
