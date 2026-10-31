<?php
require __DIR__ . '/includes/init.php';

// Đảm bảo bảng contacts tồn tại (Bài tập về nhà #3)
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fullname VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            subject VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (Exception $e) {
    // Bỏ qua nếu bảng đã tồn tại hoặc CSDL bận
}

$errors = [];
$fullname = '';
$email = '';
$subject = '';
$message = '';

if (is_logged_in()) {
    $u = current_user();
    $fullname = $u['fullname'] ?? '';
    $uStmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
    $uStmt->execute([$u['id']]);
    $uData = $uStmt->fetch();
    $email = $uData['email'] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (mb_strlen($fullname) < 2 || mb_strlen($fullname) > 100) {
        $errors['fullname'] = 'Họ tên phải có từ 2 đến 100 ký tự.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không hợp lệ.';
    }
    if (mb_strlen($subject) < 2 || mb_strlen($subject) > 200) {
        $errors['subject'] = 'Tiêu đề phải có từ 2 đến 200 ký tự.';
    }
    if (mb_strlen($message) < 5) {
        $errors['message'] = 'Nội dung liên hệ phải có ít nhất 5 ký tự.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO contacts (fullname, email, subject, message) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$fullname, $email, $subject, $message]);

        set_flash('success', 'Cảm ơn bạn đã gửi liên hệ. Chúng tôi sẽ phản hồi sớm nhất!');
        redirect('contact.php');
    }
}

$pageTitle = 'Liên hệ';
require __DIR__ . '/includes/header.php';
?>
<h2>Liên hệ</h2>

<form action="<?= url('contact.php') ?>" method="post" class="form">
    <p>
        <label for="fullname">Họ và tên</label><br>
        <input type="text" id="fullname" name="fullname" value="<?= e($fullname) ?>" required>
        <span class="error"><?= e($errors['fullname'] ?? '') ?></span>
    </p>
    <p>
        <label for="email">Email</label><br>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
        <span class="error"><?= e($errors['email'] ?? '') ?></span>
    </p>
    <p>
        <label for="subject">Tiêu đề</label><br>
        <input type="text" id="subject" name="subject" value="<?= e($subject) ?>" required>
        <span class="error"><?= e($errors['subject'] ?? '') ?></span>
    </p>
    <p>
        <label for="message">Nội dung</label><br>
        <textarea id="message" name="message" rows="5" cols="40" required><?= e($message) ?></textarea>
        <span class="error"><?= e($errors['message'] ?? '') ?></span>
    </p>
    <p>
        <button type="submit" class="btn">Gửi liên hệ</button>
    </p>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
