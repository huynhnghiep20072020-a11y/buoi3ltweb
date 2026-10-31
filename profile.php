<?php
require __DIR__ . '/includes/init.php';
require_login();

$userId = current_user()['id'];
$stmt = $pdo->prepare('SELECT id, email, fullname, phone, address FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    redirect('logout.php');
}

$errors = [];
$fullname = $user['fullname'] ?? '';
$phone = $user['phone'] ?? '';
$address = $user['address'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (mb_strlen($fullname) < 2 || mb_strlen($fullname) > 100) {
        $errors['fullname'] = 'Họ tên phải có từ 2 đến 100 ký tự.';
    }
    if ($phone !== '' && !preg_match('/^0[0-9]{9}$/', $phone)) {
        $errors['phone'] = 'Số điện thoại gồm 10 chữ số, bắt đầu bằng số 0.';
    }
    if ($address !== '' && (mb_strlen($address) < 5 || mb_strlen($address) > 255)) {
        $errors['address'] = 'Địa chỉ phải có từ 5 đến 255 ký tự.';
    }

    if (!$errors) {
        $updateStmt = $pdo->prepare('UPDATE users SET fullname = ?, phone = ?, address = ? WHERE id = ?');
        $updateStmt->execute([$fullname, $phone, $address, $userId]);

        // Cập nhật lại session
        $_SESSION['user']['fullname'] = $fullname;

        set_flash('success', 'Cập nhật thông tin tài khoản thành công.');
        redirect('profile.php');
    }
}

$pageTitle = 'Thông tin tài khoản';
require __DIR__ . '/includes/header.php';
?>
<h2>Thông tin tài khoản</h2>

<form action="<?= url('profile.php') ?>" method="post" class="form">
    <p>
        <label>Email đăng nhập</label><br>
        <input type="email" value="<?= e($user['email']) ?>" disabled style="background-color: #eee;">
    </p>
    <p>
        <label for="fullname">Họ và tên</label><br>
        <input type="text" id="fullname" name="fullname" value="<?= e($fullname) ?>" required>
        <span class="error"><?= e($errors['fullname'] ?? '') ?></span>
    </p>
    <p>
        <label for="phone">Số điện thoại</label><br>
        <input type="tel" id="phone" name="phone" value="<?= e($phone) ?>" placeholder="Ví dụ: 0912345678">
        <span class="error"><?= e($errors['phone'] ?? '') ?></span>
    </p>
    <p>
        <label for="address">Địa chỉ</label><br>
        <textarea id="address" name="address" rows="3" placeholder="Địa chỉ giao hàng"><?= e($address) ?></textarea>
        <span class="error"><?= e($errors['address'] ?? '') ?></span>
    </p>
    <p>
        <button type="submit" class="btn btn-accent">Lưu thay đổi</button>
        <a href="<?= url('orders.php') ?>">Xem đơn hàng của bạn</a>
    </p>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
