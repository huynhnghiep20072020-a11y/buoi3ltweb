<?php
require __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/products.php');
}

$id = (int) ($_POST['id'] ?? 0);

try {
    // Lấy thông tin ảnh trước khi xóa
    $stmtImg = $pdo->prepare('SELECT image FROM products WHERE id = ?');
    $stmtImg->execute([$id]);
    $oldImg = $stmtImg->fetchColumn();

    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);

    if ($oldImg && str_starts_with($oldImg, 'uploads/') && file_exists(__DIR__ . '/../' . $oldImg)) {
        @unlink(__DIR__ . '/../' . $oldImg);
    }

    set_flash('success', 'Đã xóa sản phẩm.');
} catch (PDOException $e) {
    // Sản phẩm đã nằm trong đơn hàng: khóa ngoại không cho xóa
    set_flash('error', 'Không thể xóa: sản phẩm đã có trong đơn hàng. Hãy đặt tồn kho về 0 để ngừng bán.');
}

redirect('admin/products.php');
