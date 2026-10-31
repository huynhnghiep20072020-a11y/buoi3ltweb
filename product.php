<?php
require __DIR__ . '/includes/init.php';

// Lab 3.5: Lấy mã sản phẩm từ URL và ép kiểu nguyên
$id = (int) ($_GET['id'] ?? 0);

// Truy vấn sản phẩm kèm tên danh mục bằng JOIN
$stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.id = ?'
);
$stmt->execute([$id]);
$product = $stmt->fetch();

// Không có sản phẩm với mã này: trả về mã trạng thái 404
if (!$product) {
    http_response_code(404);
    $pageTitle = 'Không tìm thấy sản phẩm';
    require __DIR__ . '/includes/header.php';
    echo '<h2>Không tìm thấy sản phẩm</h2>';
    echo '<p><a href="' . url('index.php') . '">&laquo; Quay lại danh sách sản phẩm</a></p>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $product['name'];
require __DIR__ . '/includes/header.php';
?>
<article class="product-detail">
    <div class="product-image">
        <img src="<?= url($product['image']) ?>" alt="<?= e($product['name']) ?>">
    </div>

    <div class="product-info">
        <h2><?= e($product['name']) ?></h2>
        <p class="price">Giá: <strong><?= format_price($product['price']) ?></strong></p>
        <p><?= nl2br(e($product['description'])) ?></p>

        <h3>Thông tin sản phẩm</h3>
        <table>
            <tbody>
                <tr>
                    <th>Mã sản phẩm</th>
                    <td><?= $product['id'] ?></td>
                </tr>
                <tr>
                    <th>Danh mục</th>
                    <td><?= e($product['category_name']) ?></td>
                </tr>
                <tr>
                    <th>Tình trạng</th>
                    <td><?= $product['stock'] > 0 ? 'Còn ' . $product['stock'] . ' sản phẩm' : 'Hết hàng' ?></td>
                </tr>
            </tbody>
        </table>

        <?php if ($product['stock'] > 0): ?>
            <form action="<?= url('cart-add.php') ?>" method="post" class="cart-actions">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                <button type="submit" class="btn btn-accent">Thêm vào giỏ</button>
            </form>
        <?php endif; ?>

        <p><a href="<?= url('index.php') ?>">&laquo; Quay lại danh sách sản phẩm</a></p>
    </div>
</article>

<!-- Bài tập về nhà 3: Hiển thị tối đa 3 sản phẩm khác cùng danh mục -->
<?php
$stmtRelated = $pdo->prepare('SELECT id, name, price, image FROM products WHERE category_id = ? AND id <> ? LIMIT 3');
$stmtRelated->execute([$product['category_id'], $product['id']]);
$relatedProducts = $stmtRelated->fetchAll();
?>
<?php if (!empty($relatedProducts)): ?>
<section class="related-products" style="margin-top: 40px;">
    <h2>Sản phẩm cùng danh mục</h2>
    <div class="product-list">
        <?php foreach ($relatedProducts as $relProduct): ?>
        <article class="product-card">
            <img src="<?= url($relProduct['image']) ?>" alt="<?= e($relProduct['name']) ?>">
            <h3><?= e($relProduct['name']) ?></h3>
            <p class="price"><?= format_price($relProduct['price']) ?></p>
            <a href="<?= url('product.php?id=' . $relProduct['id']) ?>" class="btn">Xem chi tiết</a>
            <form action="<?= url('cart-add.php') ?>" method="post" class="inline-form">
                <input type="hidden" name="product_id" value="<?= $relProduct['id'] ?>">
                <button type="submit" class="btn btn-accent">Thêm vào giỏ</button>
            </form>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>