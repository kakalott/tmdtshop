# tmdtshop
# 🛒 TMDTShop — Ứng dụng Thương mại Điện tử

TMDTShop là một ứng dụng web thương mại điện tử được xây dựng bằng **Laravel**, cho phép người dùng duyệt sản phẩm, thêm vào giỏ hàng, đặt hàng, và quản lý tài khoản cá nhân. Hệ thống bao gồm cả giao diện khách hàng và trang quản trị dành cho Admin.

---

## ✨ Tính năng nổi bật

### Phía khách hàng
- Đăng ký / đăng nhập tài khoản, xác thực email, đặt lại mật khẩu
- Xem danh sách sản phẩm, lọc theo danh mục
- Xem chi tiết sản phẩm, chọn biến thể (size, màu sắc,...)
- Thêm sản phẩm vào giỏ hàng và thanh toán
- Theo dõi lịch sử đơn hàng
- Viết đánh giá sản phẩm
- Chỉnh sửa thông tin hồ sơ cá nhân

### Phía quản trị (Admin)
- Dashboard thống kê tổng quan
- Quản lý sản phẩm (thêm, sửa, xóa, phân loại danh mục)
- Quản lý đơn hàng (xem, cập nhật trạng thái)
- Quản lý banner trang chủ
- Quản lý người dùng

---

## 🛠️ Công nghệ sử dụng

| Thành phần | Công nghệ |
|---|---|
| Backend | Laravel (PHP) |
| Frontend | Blade Templates, Bootstrap, Tailwind CSS |
| Build tool | Vite |
| Database | MySQL |
| Authentication | Laravel Auth (built-in) |
| HTTP Client | Axios |
| CSS | SCSS / App CSS |

---

## 📁 Cấu trúc thư mục

```
tmdtshop/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Admin/          # Controllers cho trang quản trị
│   │       ├── Auth/           # Xác thực người dùng
│   │       ├── CartController.php
│   │       ├── CheckoutController.php
│   │       ├── OrderController.php
│   │       ├── ProductController.php
│   │       ├── ShopController.php
│   │       └── ProfileController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── ProductVariant.php
│   │   ├── Category.php
│   │   ├── Cart.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── OrderDetail.php
│   │   ├── Review.php
│   │   └── Banner.php
│   └── Providers/
├── database/
│   ├── migrations/             # Các migration tạo bảng CSDL
│   ├── seeders/                # Dữ liệu mẫu
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── admin/              # Giao diện trang quản trị
│   │   ├── auth/               # Trang đăng nhập / đăng ký
│   │   ├── cart/               # Giỏ hàng
│   │   ├── checkout/           # Thanh toán
│   │   ├── orders/             # Đơn hàng
│   │   ├── products/           # Danh sách & chi tiết sản phẩm
│   │   └── profile/            # Hồ sơ người dùng
│   ├── css/
│   ├── js/
│   └── sass/
├── routes/
│   └── web.php                 # Định nghĩa các route
├── public/
├── tests/
├── vite.config.js
└── composer.json
```

---

## ⚙️ Yêu cầu hệ thống

- PHP >= 8.1
- Composer
- Node.js >= 18 & npm
- MySQL >= 5.7 hoặc MariaDB
- Laravel CLI (tùy chọn)

---

## 🚀 Hướng dẫn cài đặt

### 1. Clone dự án

```bash
git clone <repository-url>
cd tmdtshop
```

### 2. Cài đặt dependencies PHP

```bash
composer install
```

### 3. Cài đặt dependencies Node.js

```bash
npm install
```

### 4. Cấu hình môi trường

```bash
cp .env.example .env
php artisan key:generate
```

Mở file `.env` và cập nhật thông tin kết nối database:

```env
APP_NAME=TMDTShop
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tmdtshop
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Tạo database và chạy migration

```bash
php artisan migrate
```

### 6. Chạy seeder (dữ liệu mẫu)

```bash
php artisan db:seed
```

### 7. Tạo symbolic link cho storage

```bash
php artisan storage:link
```

### 8. Build assets frontend

```bash
# Development
npm run dev

# Production
npm run build
```

### 9. Khởi chạy server

```bash
php artisan serve
```

Truy cập ứng dụng tại: [http://127.0.0.1:8000](http://127.0.0.1:8000)

### 10. Cấu hình chatbot

Để chatbot hoạt động:

- `GEMINI_API_KEY` phải được cấu hình trong `.env`.
- `APP_URL` nên đặt thành `http://127.0.0.1:8000` nếu bạn chạy app ở địa chỉ đó.
- Nếu nội dung chatbot trả về link trong ứng dụng, nó sẽ dùng đường dẫn tương đối `/...` để đảm bảo đúng host hiện tại.

Chatbot truy cập dữ liệu từ cơ sở dữ liệu nội bộ của ứng dụng, bao gồm:

- thông tin sản phẩm,
- trạng thái đơn hàng,
- thông tin khách hàng (nếu đã đăng nhập).

Chatbot không tự gọi đến cơ sở dữ liệu ngoài ứng dụng; nó đọc dữ liệu qua các model Laravel và services đã định nghĩa.

---

## 👤 Tài khoản mặc định

Sau khi chạy seeder, bạn có thể đăng nhập bằng tài khoản Admin mặc định (xem file `database/seeders/UserSeeder.php` để biết thông tin chi tiết).

---

## 🗃️ Cơ sở dữ liệu

Dự án sử dụng các bảng chính sau:

| Bảng | Mô tả |
|---|---|
| `users` | Tài khoản người dùng và Admin |
| `products` | Thông tin sản phẩm |
| `product_variants` | Biến thể sản phẩm (size, màu,...) |
| `categories` | Danh mục sản phẩm |
| `carts` | Giỏ hàng |
| `orders` | Đơn hàng |
| `order_items` | Chi tiết sản phẩm trong đơn hàng |
| `order_details` | Thông tin giao hàng |
| `reviews` | Đánh giá sản phẩm |
| `banners` | Banner hiển thị trên trang chủ |

---

## 🧪 Chạy kiểm thử

```bash
php artisan test
```

---

## 📝 License

Dự án này được phát triển phục vụ mục đích học tập và nghiên cứu.