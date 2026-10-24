<?php
require __DIR__ . '/includes/init.php';

$pageTitle = 'Đăng ký';
require __DIR__ . '/includes/header.php';
?>
        <h2>Đăng ký tài khoản</h2>

        <form action="register.php" method="get" class="form" id="register-form">
            <p>
                <label for="fullname">Họ và tên</label><br>
                <input type="text" id="fullname" name="fullname"
                       required minlength="2" maxlength="100" placeholder="Nguyễn Văn An">
            </p>
            <p>
                <label for="email">Email</label><br>
                <input type="email" id="email" name="email"
                       required placeholder="ten@example.com">
            </p>
            <p>
                <label for="password">Mật khẩu</label><br>
                <input type="password" id="password" name="password"
                       required minlength="6">
            </p>
            <p>
                <label for="password2">Nhập lại mật khẩu</label><br>
                <input type="password" id="password2" name="password2"
                       required minlength="6">
                <span class="error" id="password-error"></span>
            </p>
            <p>
                <label for="phone">Số điện thoại</label><br>
                <input type="tel" id="phone" name="phone"
                       required pattern="0[0-9]{9}" placeholder="0900000000"
                       title="Số điện thoại gồm 10 chữ số, bắt đầu bằng số 0">
            </p>
            <p>
                Giới tính<br>
                <input type="radio" id="male" name="gender" value="male">
                <label for="male">Nam</label>
                <input type="radio" id="female" name="gender" value="female">
                <label for="female">Nữ</label>
            </p>
            <p>
                <label for="province">Tỉnh / Thành phố</label><br>
                <select id="province" name="province">
                    <option value="">-- Chọn --</option>
                    <option value="dong-nai">Đồng Nai</option>
                    <option value="hcm">TP. Hồ Chí Minh</option>
                    <option value="ha-noi">Hà Nội</option>
                    <option value="da-nang">Đà Nẵng</option>
                </select>
            </p>
            <p>
                <label for="address">Địa chỉ nhận hàng</label><br>
                <textarea id="address" name="address" rows="3" cols="40"></textarea>
            </p>
            <p>
                <input type="checkbox" id="agree" name="agree" value="1" required>
                <label for="agree">Tôi đồng ý với điều khoản sử dụng</label>
            </p>
            <p>
                <button type="submit" class="btn">Đăng ký</button>
            </p>
        </form>
    <?php require __DIR__ . '/includes/footer.php'; ?>