<?php
// Các hàm dùng chung của MiniShop.

// ===== 1. Hiển thị =====

// Mã hóa ký tự đặc biệt trước khi in ra HTML (chống XSS)
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Định dạng tiền: 250000 -> "250.000 đ"
function format_price($amount)
{
    return number_format((int) $amount, 0, ',', '.') . ' đ';
}

// Tạo đường dẫn tuyệt đối trong website: url('cart.php') -> "/minishop/cart.php"
function url($path = '')
{
    return BASE_URL . ltrim($path, '/');
}