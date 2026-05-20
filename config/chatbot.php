<?php

return [

    'shop_name' => env('CHATBOT_SHOP_NAME', 'Cửa Hàng Đồ Nhựa'),

    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),

    'fallback_models' => [
        'gemini-2.5-flash-lite',
        'gemini-flash-latest',
        'gemini-2.0-flash-lite',
    ],

    'max_products_in_context' => (int) env('CHATBOT_MAX_PRODUCTS', 10),

    'max_history' => (int) env('CHATBOT_MAX_HISTORY', 12),

    'max_orders_in_context' => (int) env('CHATBOT_MAX_ORDERS', 5),

    'policies' => [
        'Thanh toán: tiền mặt (COD), chuyển khoản khi đặt online.',
        'Đơn web: trạng thái pending (chờ xử lý), processing (đang giao), completed (hoàn thành), cancelled (đã hủy).',
        'Khách có thể hủy đơn tại trang lịch sử đơn hàng khi đơn còn pending hoặc unpaid (nếu có).',
        'Giỏ hàng, thanh toán, đơn hàng: dẫn khách bằng link [tại đây] (URL trong dữ liệu).',
        'Mua sỉ: từ 10 sản phẩm trở lên có thể áp dụng giá wholesale_price nếu shop đã cấu hình.',
    ],

    'order_status_labels' => [
        'pending' => 'Chờ xử lý',
        'unpaid' => 'Chưa thanh toán',
        'processing' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ],

];
