<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Hệ Thống Quản Trị</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1e1e2f; /* Màu nền tối ngầu ngầu */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background-color: #27293d;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            border-top: 5px solid #e14eca; /* Viền tím chói lọi phân biệt với trang khách */
        }
        .form-control {
            background-color: #1e1e2f;
            border: 1px solid #2b3553;
            color: white;
        }
        .form-control:focus {
            background-color: #1e1e2f;
            color: white;
            border-color: #e14eca;
            box-shadow: none;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h3 class="text-center text-white fw-bold mb-4">Đăng Nhập Hệ Thống QUẢN TRỊ</h3>
        
        @if($errors->any())
            <div class="alert alert-danger fw-bold text-center p-2">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/admin/login" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label text-light fw-bold">Email quản trị</label>
                <input type="email" name="email" class="form-control form-control-lg" required placeholder="admin@domain.com">
            </div>
            
            <div class="mb-4">
                <label class="form-label text-light fw-bold">Mật khẩu</label>
                <input type="password" name="password" class="form-control form-control-lg" required placeholder="••••••••">
            </div>
            
            <button type="submit" class="btn btn-primary w-100 fw-bold fs-5 py-2" style="background-color: #e14eca; border: none;">
                ĐĂNG NHẬP HỆ THỐNG
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="/" class="text-muted text-decoration-none"> Quay lại trang mua hàng</a>
        </div>
    </div>

</body>
</html>