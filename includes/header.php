<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>MiniShop - Phụ kiện công nghệ</title>
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
    <script src="<?= url('js/main.js') ?>" defer></script>
</head>
<body>
    <!-- ĐẦU TRANG -->
    <header class="site-header">
        <h1>MiniShop</h1>
        <nav class="main-nav">
            <a href="<?= url('index.php') ?>">Trang chủ</a>
            <a href="<?= url('about.php') ?>">Giới thiệu</a>
            <a href="<?= url('contact.php') ?>">Liên hệ</a>
            <a href="<?= url('cart.php') ?>">Giỏ hàng (<span id="cart-count">0</span>)</a>
            <a href="<?= url('login.php') ?>">Đăng nhập</a>
            <a href="<?= url('register.php') ?>">Đăng ký</a>
        </nav>
    </header>

    <!-- NỘI DUNG CHÍNH -->
    <main>