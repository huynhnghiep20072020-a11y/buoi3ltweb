<?php
require __DIR__ . '/../includes/init.php';
require_admin();

$errors = [];
$name = '';
$editId = (int) ($_GET['edit'] ?? 0);

// Xử lý thêm hoặc sửa danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        // Kiểm tra xem có sản phẩm thuộc danh mục này không
        $check = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            set_flash('error', 'Không thể xóa danh mục đang có sản phẩm. Hãy chuyển sản phẩm sang danh mục khác trước.');
        } else {
            $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
            $stmt->execute([$id]);
            set_flash('success', 'Đã xóa danh mục.');
        }
        redirect('admin/categories.php');
    }

    $name = trim($_POST['name'] ?? '');
    $catId = (int) ($_POST['cat_id'] ?? 0);

    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors['name'] = 'Tên danh mục phải có từ 2 đến 100 ký tự.';
    }

    if (!$errors) {
        if ($catId > 0) {
            $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
            $stmt->execute([$name, $catId]);
            set_flash('success', 'Đã cập nhật danh mục.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
            $stmt->execute([$name]);
            set_flash('success', 'Đã thêm danh mục mới.');
        }
        redirect('admin/categories.php');
    }
}

// Nếu đang ở chế độ sửa
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$editId]);
    $catToEdit = $stmt->fetch();
    if ($catToEdit) {
        $name = $catToEdit['name'];
    }
}

// Lấy danh sách danh mục kèm số lượng sản phẩm
$categories = $pdo->query('
    SELECT c.id, c.name, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id, c.name
    ORDER BY c.id ASC
')->fetchAll();

$pageTitle = 'Quản lý danh mục';
require __DIR__ . '/../includes/header.php';
?>
<h2>Quản lý danh mục</h2>

<p class="cart-actions">
    <a href="<?= url('admin/index.php') ?>" class="btn">Tổng quan</a>
    <a href="<?= url('admin/product-form.php') ?>" class="btn btn-accent">Thêm sản phẩm</a>
    <a href="<?= url('admin/products.php') ?>" class="btn">Quản lý sản phẩm</a>
    <a href="<?= url('admin/orders.php') ?>" class="btn">Quản lý đơn hàng</a>
</p>

<form action="<?= url('admin/categories.php') ?>" method="post" class="form" style="margin-bottom: 24px;">
    <?= csrf_field() ?>
    <input type="hidden" name="cat_id" value="<?= $editId ?>">
    <h3><?= $editId > 0 ? 'Sửa danh mục' : 'Thêm danh mục mới' ?></h3>
    <p>
        <label for="name">Tên danh mục</label><br>
        <input type="text" id="name" name="name" value="<?= e($name) ?>" required>
        <span class="error"><?= e($errors['name'] ?? '') ?></span>
    </p>
    <p>
        <button type="submit" class="btn btn-accent"><?= $editId > 0 ? 'Lưu thay đổi' : 'Thêm danh mục' ?></button>
        <?php if ($editId > 0): ?>
            <a href="<?= url('admin/categories.php') ?>" class="btn">Hủy</a>
        <?php endif; ?>
    </p>
</form>

<table>
    <thead>
        <tr>
            <th>Mã</th>
            <th>Tên danh mục</th>
            <th>Số sản phẩm</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $cat): ?>
        <tr>
            <td><?= $cat['id'] ?></td>
            <td><?= e($cat['name']) ?></td>
            <td><?= $cat['product_count'] ?></td>
            <td>
                <a href="<?= url('admin/categories.php?edit=' . $cat['id']) ?>" class="btn">Sửa</a>
                <form action="<?= url('admin/categories.php') ?>" method="post" class="inline-form"
                      onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
