<?php
require __DIR__ . '/includes/init.php';

// Lab 3.5: Đọc tham số lọc danh mục và từ khóa tìm kiếm trên URL
$categoryId = (int) ($_GET['category'] ?? 0);
$keyword = trim($_GET['q'] ?? '');

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY id')->fetchAll();

// Ghép câu truy vấn theo điều kiện lọc; giá trị luôn đi qua tham số ?
$sql = 'SELECT id, name, price, image FROM products';
$where = [];
$params = [];

if ($categoryId > 0) {
    $where[] = 'category_id = ?';
    $params[] = $categoryId;
}

if ($keyword !== '') {
    $where[] = 'name LIKE ?';
    $params[] = '%' . $keyword . '%';
}

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

// Bài tập về nhà 1: Sắp xếp theo giá. Dùng if chọn chuỗi ORDER BY viết sẵn,
// không bao giờ ghép trực tiếp giá trị $_GET['sort'] vào câu SQL.
$sort = $_GET['sort'] ?? '';
$orderBy = 'ORDER BY id ASC';
if ($sort === 'price_asc') {
    $orderBy = 'ORDER BY price ASC';
} elseif ($sort === 'price_desc') {
    $orderBy = 'ORDER BY price DESC';
}

$sql .= ' ' . $orderBy;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = 'Trang chủ';
require __DIR__ . '/includes/header.php';
?>
<section>
    <h2>Sản phẩm</h2>

    <!-- Form tìm kiếm (Lab 3.5) -->
    <form action="<?= url('index.php') ?>" method="get" class="search-form">
        <input type="search" name="q" value="<?= e($keyword) ?>" placeholder="Tìm sản phẩm...">
        <?php if ($categoryId > 0): ?>
            <input type="hidden" name="category" value="<?= $categoryId ?>">
        <?php endif; ?>
        <?php if ($sort !== ''): ?>
            <input type="hidden" name="sort" value="<?= e($sort) ?>">
        <?php endif; ?>
        <button type="submit" class="btn">Tìm</button>
    </form>

    <!-- Thanh danh mục (Lab 3.5) -->
    <p class="category-bar">
        <a href="<?= url('index.php' . ($sort ? '?sort=' . urlencode($sort) : '')) ?>"
           class="<?= $categoryId === 0 ? 'active' : '' ?>">Tất cả</a>
        <?php foreach ($categories as $category): ?>
            <a href="<?= url('index.php?category=' . $category['id'] . ($sort ? '&sort=' . urlencode($sort) : '')) ?>"
               class="<?= $categoryId === $category['id'] ? 'active' : '' ?>"><?= e($category['name']) ?></a>
        <?php endforeach; ?>
    </p>

    <!-- Thanh sắp xếp và đếm kết quả (Bài tập về nhà 1 & 2) -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin: 15px 0 20px; flex-wrap: wrap; gap: 10px;">
        <!-- Bài tập 2: Hiển thị dòng "Tìm thấy N sản phẩm" phía trên lưới -->
        <p style="margin: 0; font-weight: bold; color: var(--muted);">
            Tìm thấy <?= count($products) ?> sản phẩm
        </p>

        <!-- Bài tập 1: Sắp xếp theo giá -->
        <div>
            <label for="sort-select"><strong>Sắp xếp theo giá:</strong></label>
            <select id="sort-select" onchange="location.href = this.value;" style="padding: 6px 12px; border: 1px solid var(--border); border-radius: 4px; font-size: 0.95rem;">
                <?php
                $buildSortUrl = function($sortVal) use ($categoryId, $keyword) {
                    $queryParams = [];
                    if ($categoryId > 0) $queryParams['category'] = $categoryId;
                    if ($keyword !== '') $queryParams['q'] = $keyword;
                    if ($sortVal !== '') $queryParams['sort'] = $sortVal;
                    $qs = http_build_query($queryParams);
                    return url('index.php' . ($qs ? '?' . $qs : ''));
                };
                ?>
                <option value="<?= $buildSortUrl('') ?>" <?= $sort === '' ? 'selected' : '' ?>>Mặc định</option>
                <option value="<?= $buildSortUrl('price_asc') ?>" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                <option value="<?= $buildSortUrl('price_desc') ?>" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
            </select>
        </div>
    </div>

    <?php if (!$products): ?>
        <p>Không tìm thấy sản phẩm nào.</p>
    <?php endif; ?>

    <div class="product-list">
        <?php foreach ($products as $product): ?>
        <article class="product-card">
            <img src="<?= url($product['image']) ?>" alt="<?= e($product['name']) ?>">
            <h3><?= e($product['name']) ?></h3>
            <p class="price"><?= format_price($product['price']) ?></p>
            <a href="<?= url('product.php?id=' . $product['id']) ?>" class="btn">Xem chi tiết</a>
            <button type="button" class="btn btn-accent add-to-cart"
                    data-id="<?= $product['id'] ?>">Thêm vào giỏ</button>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>