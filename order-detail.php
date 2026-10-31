<?php
require __DIR__ . '/includes/init.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch();

// Chỉ chủ đơn hàng hoặc quản trị viên được xem.
// Đơn không tồn tại và đơn của người khác đều trả về 404 như nhau.
if (!$order || ($order['user_id'] !== current_user()['id'] && !is_admin())) {
    http_response_code(404);
    $pageTitle = 'Không tìm thấy đơn hàng';
    require __DIR__ . '/includes/header.php';
    echo '<h2>Không tìm thấy đơn hàng</h2>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Bài tập về nhà 1: Xử lý Hủy đơn khi trạng thái là pending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    if ($order['status'] !== 'pending') {
        set_flash('error', 'Đơn hàng này không thể hủy vì không ở trạng thái chờ xử lý.');
        redirect('order-detail.php?id=' . $id);
    }

    try {
        $pdo->beginTransaction();

        // 1. Cập nhật trạng thái đơn thành cancelled
        $updateOrder = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND status = 'pending'");
        $updateOrder->execute([$id]);

        if ($updateOrder->rowCount() === 0) {
            throw new RuntimeException('Đơn hàng không thể hủy hoặc đã được cập nhật trước đó.');
        }

        // 2. Lấy danh sách sản phẩm và cộng trả tồn kho
        $itemsStmt = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
        $itemsStmt->execute([$id]);
        $orderItems = $itemsStmt->fetchAll();

        $refundStock = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
        foreach ($orderItems as $item) {
            $refundStock->execute([$item['quantity'], $item['product_id']]);
        }

        $pdo->commit();
        set_flash('success', 'Đã hủy đơn hàng #' . $id . ' và hoàn trả tồn kho thành công.');
        redirect('order-detail.php?id=' . $id);
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash('error', 'Lỗi khi hủy đơn: ' . $e->getMessage());
        redirect('order-detail.php?id=' . $id);
    }
}

$stmt = $pdo->prepare(
    'SELECT product_name, price, quantity FROM order_items WHERE order_id = ? ORDER BY id'
);
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$pageTitle = 'Đơn hàng #' . $order['id'];
require __DIR__ . '/includes/header.php';
?>
<h2>Đơn hàng #<?= $order['id'] ?></h2>

<table>
    <tbody>
        <tr>
            <th>Ngày đặt</th>
            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
        </tr>
        <tr>
            <th>Trạng thái</th>
            <td><?= e(order_status_label($order['status'])) ?></td>
        </tr>
        <tr>
            <th>Người nhận</th>
            <td><?= e($order['fullname']) ?> - <?= e($order['phone']) ?></td>
        </tr>
        <tr>
            <th>Địa chỉ</th>
            <td><?= e($order['address']) ?></td>
        </tr>
        <tr>
            <th>Ghi chú</th>
            <td><?= nl2br(e($order['note'])) ?></td>
        </tr>
    </tbody>
</table>

<h3>Sản phẩm</h3>
<table>
    <thead>
        <tr>
            <th>Sản phẩm</th>
            <th>Đơn giá</th>
            <th>Số lượng</th>
            <th>Thành tiền</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= e($item['product_name']) ?></td>
                <td><?= format_price($item['price']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= format_price($item['price'] * $item['quantity']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3">Tổng cộng</th>
            <th><?= format_price($order['total']) ?></th>
        </tr>
    </tfoot>
</table>

<?php if ($order['status'] === 'pending'): ?>
    <form action="<?= url('order-detail.php?id=' . $order['id']) ?>" method="post"
          onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');" style="margin: 16px 0;">
        <input type="hidden" name="action" value="cancel">
        <button type="submit" class="btn btn-danger">Hủy đơn</button>
    </form>
<?php endif; ?>

<p><a href="<?= url(is_admin() ? 'admin/orders.php' : 'orders.php') ?>">&laquo; Danh sách đơn hàng</a></p>

<?php require __DIR__ . '/includes/footer.php'; ?>
