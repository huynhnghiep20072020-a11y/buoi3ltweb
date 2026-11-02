<?php
require __DIR__ . '/../includes/init.php';
require_admin();

// Thống kê tổng quan cho quản trị viên
$totalOrders = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$pendingOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$shippingOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'shipping'")->fetchColumn();
$completedOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn();
$cancelledOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetchColumn();

// Doanh thu các đơn hoàn tất
$totalRevenue = (int) $pdo->query("SELECT SUM(total) FROM orders WHERE status = 'completed'")->fetchColumn();

// 5 sản phẩm sắp hết hàng (tồn kho thấp nhất)
$lowStockProducts = $pdo->query('SELECT id, name, stock, price FROM products ORDER BY stock ASC, id ASC LIMIT 5')->fetchAll();

$pageTitle = 'Tổng quan quản trị';
require __DIR__ . '/../includes/header.php';
?>
<h2>Tổng quan quản trị</h2>

<p class="cart-actions">
    <a href="<?= url('admin/products.php') ?>" class="btn btn-accent">Quản lý sản phẩm</a>
    <a href="<?= url('admin/orders.php') ?>" class="btn">Quản lý đơn hàng</a>
    <a href="<?= url('admin/categories.php') ?>" class="btn">Quản lý danh mục</a>
</p>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin: 20px 0;">
    <div style="background: var(--white); padding: 16px; border-radius: 8px; border: 1px solid var(--border);">
        <h3 style="margin-top: 0;">Tổng đơn hàng</h3>
        <p style="font-size: 1.8rem; font-weight: bold; margin: 0; color: var(--primary);"><?= $totalOrders ?></p>
        <small>Chờ xử lý: <?= $pendingOrders ?> | Đang giao: <?= $shippingOrders ?></small>
    </div>
    <div style="background: var(--white); padding: 16px; border-radius: 8px; border: 1px solid var(--border);">
        <h3 style="margin-top: 0;">Doanh thu hoàn tất</h3>
        <p style="font-size: 1.8rem; font-weight: bold; margin: 0; color: #2e7d32;"><?= format_price($totalRevenue) ?></p>
        <small>Đơn hoàn tất: <?= $completedOrders ?> | Đã hủy: <?= $cancelledOrders ?></small>
    </div>
</div>

<h3>Sản phẩm sắp hết hàng</h3>
<table>
    <thead>
        <tr>
            <th>Mã</th>
            <th>Tên sản phẩm</th>
            <th>Giá</th>
            <th>Tồn kho</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lowStockProducts as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= e($p['name']) ?></td>
            <td><?= format_price($p['price']) ?></td>
            <td style="color: <?= $p['stock'] < 5 ? 'var(--danger)' : 'inherit' ?>; font-weight: bold;"><?= $p['stock'] ?></td>
            <td><a href="<?= url('admin/product-form.php?id=' . $p['id']) ?>" class="btn">Cập nhật</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
