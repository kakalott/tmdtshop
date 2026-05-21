@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            @if($vietqrUrl)
                <!-- Giao diện thanh toán VietQR cao cấp -->
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-gradient-danger-orange text-white text-center py-4">
                        <h3 class="fw-bold mb-1">THANH TOÁN CHUYỂN KHOẢN VIETQR</h3>
                        <p class="mb-0 opacity-75">Tự động nhận diện giao dịch sau khi chuyển khoản thành công</p>
                    </div>
                    <div class="card-body p-4 bg-light">
                        <div class="row align-items-center">
                            <!-- Cột trái: Mã QR và Trạng thái -->
                            <div class="col-md-5 text-center mb-4 mb-md-0 border-end border-2 border-white">
                                <div class="qr-container bg-white p-3 rounded-4 shadow-sm border border-light d-inline-block position-relative mb-3">
                                    <img src="{{ $vietqrUrl }}" alt="VietQR Payment Code" class="img-fluid rounded-3" style="max-height: 280px; object-fit: contain;">
                                    <div class="qr-pulse-indicator"></div>
                                </div>

                                <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                                    <span class="pulse-dot"></span>
                                    <span class="fw-bold text-muted small">Đang chờ quét mã thanh toán...</span>
                                </div>

                                <div class="timer-box bg-white border rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-2 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="text-danger" viewBox="0 0 16 16">
                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                    </svg>
                                    <span id="countdown-timer" class="fw-bold text-dark font-monospace fs-6">10:00</span>
                                </div>
                            </div>

                            <!-- Cột phải: Thông tin tài khoản -->
                            <div class="col-md-7 px-md-4">
                                <div class="alert bg-white border rounded-3 p-3 mb-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                        <span class="text-muted fw-semibold">Tổng tiền thanh toán</span>
                                        <span class="fs-4 fw-bold text-danger">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted fw-semibold">Mã đơn hàng</span>
                                        <span class="fw-bold text-dark">#{{ $order->id }}</span>
                                    </div>
                                </div>

                                <h5 class="fw-bold text-dark mb-3">Thông Tin Chuyển Khoản</h5>
                                <div class="d-flex flex-column gap-2.5">
                                    <!-- Ngân hàng -->
                                    <div class="info-row d-flex justify-content-between align-items-center bg-white p-2.5 rounded-3 border">
                                        <div>
                                            <span class="text-muted small d-block">Ngân hàng</span>
                                            <strong class="text-dark">{{ strtoupper(config('vietqr.bank_bin')) }}</strong>
                                        </div>
                                    </div>

                                    <!-- Số tài khoản -->
                                    <div class="info-row d-flex justify-content-between align-items-center bg-white p-2.5 rounded-3 border">
                                        <div>
                                            <span class="text-muted small d-block">Số tài khoản</span>
                                            <strong class="text-dark fs-5 font-monospace" id="acc-num">{{ config('vietqr.account_number') }}</strong>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary copy-btn" onclick="copyText('acc-num', this)">
                                            Sao chép
                                        </button>
                                    </div>

                                    <!-- Chủ tài khoản -->
                                    <div class="info-row d-flex justify-content-between align-items-center bg-white p-2.5 rounded-3 border">
                                        <div>
                                            <span class="text-muted small d-block">Tên chủ tài khoản</span>
                                            <strong class="text-dark">{{ strtoupper(config('vietqr.account_name')) }}</strong>
                                        </div>
                                    </div>

                                    <!-- Nội dung chuyển khoản -->
                                    <div class="info-row d-flex justify-content-between align-items-center bg-white p-2.5 rounded-3 border border-warning-subtle" style="background-color: #fffdf5 !important;">
                                        <div>
                                            <span class="text-warning-emphasis small d-block fw-bold">Nội dung chuyển khoản (Ghi chính xác)</span>
                                            <strong class="text-danger fs-5 font-monospace" id="transfer-memo">DH{{ $order->id }}</strong>
                                        </div>
                                        <button class="btn btn-sm btn-warning copy-btn fw-bold" onclick="copyText('transfer-memo', this)">
                                            Sao chép
                                        </button>
                                    </div>
                                </div>

                                <div class="alert alert-warning py-2.5 px-3 rounded-3 mt-3 mb-4 small text-dark d-flex gap-2 align-items-center border-warning">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="text-warning flex-shrink-0" viewBox="0 0 16 16">
                                        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                                    </svg>
                                    <span>Vui lòng chuyển khoản đúng số tiền <strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong> và nội dung <strong>DH{{ $order->id }}</strong> để hệ thống tự động ghi nhận chính xác.</span>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 border-2 opacity-10">

                        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center align-items-center">
                            <button id="check-payment-btn" class="btn btn-success px-5 py-3 fw-bold shadow-sm rounded-pill fs-5 order-md-2 w-100 w-md-auto">
                                Tôi đã chuyển khoản thành công
                            </button>
                            <a href="/profile/orders" class="btn btn-outline-secondary px-5 py-3 fw-bold rounded-pill order-md-1 w-100 w-md-auto">
                                Hủy giao dịch
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Giao diện thanh toán VNPay nguyên bản -->
                <div class="card shadow border-primary border-2 text-center">
                    <div class="card-header bg-primary text-white fw-bold fs-4 py-3">
                        THANH TOÁN QUÉT MÃ QR VNPAY
                    </div>
                    <div class="card-body mt-3 mb-3">
                        <h5 class="fw-bold text-dark">Mã Đơn Hàng: #{{ $order->id }}</h5>
                        <p class="fs-5 mb-4">Tổng số tiền cần thanh toán: <br><span class="text-danger fw-bold fs-2">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span></p>

                        <div class="alert alert-info mb-4">
                            <strong>VNPay</strong><br>
                            Đơn hàng sẽ tự chuyển sang hoàn thành khi VNPay xác nhận giao dịch thành công.
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if($payUrl)
                            <div class="mb-4">
                                <p class="fw-bold">Quét mã QR bên dưới để thanh toán qua VNPay:</p>
                                <img src="https://api.qrserver.com/v1/create-qr-code?size=300x300&data={{ urlencode($payUrl) }}" alt="QR Payment" class="img-fluid border rounded shadow-sm mb-3">
                                <p class="small text-muted">Hoặc mở liên kết này nếu không quét được QR:</p>
                                <a href="{{ $payUrl }}" target="_blank" class="d-inline-block text-primary text-decoration-underline mb-3">{{ $payUrl }}</a>
                            </div>
                        @endif

                        @if($order->status === 'unpaid' && $order->payment_method === 'ONLINE')
                            <form action="/checkout/payment/{{ $order->id }}/start" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success fw-bold px-4 mt-2">Thanh toán bằng VNPay</button>
                            </form>
                        @elseif($order->status === 'completed')
                            <div class="alert alert-success fw-bold">Đơn hàng đã được thanh toán và hoàn thành.</div>
                        @else
                            <div class="alert alert-secondary fw-bold">Đơn hàng không thể thanh toán online ở trạng thái hiện tại.</div>
                        @endif

                        <a href="/" class="btn btn-outline-secondary fw-bold px-4 mt-3 d-inline-block">
                             Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Gradient VietQR Header */
    .bg-gradient-danger-orange {
        background: linear-gradient(135deg, #e52d27 0%, #b31217 100%);
    }

    /* Pulse Green Dot Indicator */
    .pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #198754;
        border-radius: 50%;
        display: inline-block;
        animation: pulseIndicator 1.8s infinite ease-in-out;
    }
    @keyframes pulseIndicator {
        0% { transform: scale(0.9); opacity: 0.5; }
        50% { transform: scale(1.3); opacity: 1; box-shadow: 0 0 0 6px rgba(25, 135, 84, 0.2); }
        100% { transform: scale(0.9); opacity: 0.5; }
    }

    /* Container for QR */
    .qr-container {
        border-color: #eee !important;
        position: relative;
    }
    .qr-pulse-indicator {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        border: 2px dashed #dc3545;
        border-radius: 12px;
        animation: borderPulse 3s infinite linear;
        pointer-events: none;
    }
    @keyframes borderPulse {
        0% { transform: scale(1.00); opacity: 0.3; }
        50% { transform: scale(1.02); opacity: 0.8; }
        100% { transform: scale(1.00); opacity: 0.3; }
    }

    /* Info Row styling */
    .info-row {
        border-color: #eaedf1 !important;
        transition: all 0.2s ease;
    }
    .info-row:hover {
        border-color: #cdd4dc !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }
    .w-md-auto {
        @media (min-width: 768px) {
            width: auto !important;
        }
    }
</style>

<script>
    // Copy Helper function
    function copyText(elementId, button) {
        const text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(() => {
            const originalText = button.innerText;
            button.innerText = 'Đã chép!';
            button.classList.remove('btn-outline-secondary', 'btn-warning');
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerText = originalText;
                button.classList.remove('btn-success');
                if (elementId === 'transfer-memo') {
                    button.classList.add('btn-warning');
                } else {
                    button.classList.add('btn-outline-secondary');
                }
            }, 1500);
        }).catch(err => {
            console.error('Lỗi khi sao chép:', err);
        });
    }

    // Countdown Timer for VietQR
    @if($vietqrUrl)
        document.addEventListener('DOMContentLoaded', function () {
            let timeLeft = 600; // 10 mins
            const timerEl = document.getElementById('countdown-timer');
            const timerInterval = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    if (timerEl) {
                        timerEl.innerText = "Hết hạn";
                        timerEl.classList.remove('text-dark');
                        timerEl.classList.add('text-danger');
                    }
                    return;
                }
                timeLeft--;
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                if (timerEl) {
                    timerEl.innerText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }, 1000);

            // AJAX Checking Status Action
            document.getElementById('check-payment-btn')?.addEventListener('click', function () {
                const btn = this;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Đang xác thực giao dịch...`;

                fetch(`/checkout/payment/{{ $order->id }}/vietqr-check`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Lỗi phản hồi mạng');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành Công!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = '/profile/orders';
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Chưa Nhận Được Tiền',
                            text: data.message || 'Hệ thống chưa tìm thấy giao dịch chuyển khoản phù hợp. Vui lòng thử lại sau vài giây.',
                            confirmButtonText: 'Đồng ý',
                            confirmButtonColor: '#ffc107'
                        });
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    console.error('AJAX Error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi Hệ Thống',
                        text: 'Không thể kết nối đến máy chủ. Vui lòng kiểm tra lại kết nối mạng.',
                        confirmButtonText: 'Đóng',
                        confirmButtonColor: '#dc3545'
                    });
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            });
        });
    @endif
</script>
@endsection
