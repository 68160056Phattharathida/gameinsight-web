<?php
session_start();
require_once 'config/db.php';

// สร้าง CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. ตรวจสอบ CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF Token Validation Failed");
    }

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agreed_terms = isset($_POST['agreed_terms']) ? 1 : 0;

    // 2. Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "รูปแบบอีเมลไม่ถูกต้อง";
    }
    if ($password !== $confirm_password) {
        $errors[] = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    }
    if (!$agreed_terms) {
        $errors[] = "คุณต้องยอมรับข้อกำหนดการให้บริการก่อนสมัครสมาชิก";
    }

    // 3. บันทึกลงฐานข้อมูล (ใช้ตาราง users ตาม Schema ใหม่)
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO gi_users (username, email, password_hash, agreed_terms, agreed_at) VALUES (?, ?, ?, ?, NOW())");

        try {
            $stmt->execute([$username, $email, $hashed_password, $agreed_terms]);
            $success = "สมัครสมาชิกสำเร็จเรียบร้อยแล้ว!";
            unset($_SESSION['csrf_token']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "Username หรือ Email นี้ถูกใช้งานแล้ว";
            } else {
                $errors[] = "เกิดข้อผิดพลาด: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - GameInsight</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; padding-top: 40px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"], .form-group input[type="email"], .form-group input[type="password"] {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        .btn { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .btn:hover { background-color: #218838; }
        .btn-login { display: inline-block; width: 100%; padding: 10px; background-color: #007bff; color: white; text-align: center; text-decoration: none; border-radius: 4px; font-size: 15px; font-weight: bold; box-sizing: border-box; margin-top: 10px; }
        .btn-login:hover { background-color: #0056b3; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        .login-link { text-align: center; margin-top: 15px; font-size: 14px; }
        .login-link a { color: #007bff; text-decoration: none; font-weight: bold; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 20px; border-radius: 8px; width: 80%; max-width: 500px; max-height: 70vh; overflow-y: auto; }
        .close-btn { float: right; cursor: pointer; font-weight: bold; font-size: 20px; }
    </style>
</head>
<body>

<div class="card">
    <h2>สมัครสมาชิก</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <?php foreach ($errors as $error) echo "<p style='margin:0;'>• $error</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert-success">
            <p style="margin: 0 0 10px 0; font-weight: bold;"><?= $success ?></p>
            <a href="login.php" class="btn-login">เข้าสู่ระบบทันที</a>
        </div>
    <?php else: ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="agreed_terms" value="1" required>
                    ฉันยอมรับ <a href="#" id="openModal">ข้อกำหนดการให้บริการและนโยบายความเป็นส่วนตัว</a>
                </label>
            </div>

            <button type="submit" class="btn">สมัครสมาชิก</button>
        </form>

        <div class="login-link">
            มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a>
        </div>
    <?php endif; ?>
</div>

<div id="tosModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" id="closeModal">&times;</span>
        <h3>ข้อกำหนดการให้บริการ (Terms of Service)</h3>
        <p>1. ผู้ใช้ต้องกรอกข้อมูลที่เป็นความจริงในการสมัครสมาชิก</p>
        <p>2. ห้ามใช้ระบบนี้ในการส่งข้อมูลที่เป็นเท็จหรือละเมิดกฎหมาย</p>
        <p>3. ระบบจะทำการเก็บรักษาข้อมูลของคุณอย่างปลอดภัยตามนโยบายความเป็นส่วนตัว</p>
    </div>
</div>

<script>
    const modal = document.getElementById('tosModal');
    const openBtn = document.getElementById('openModal');
    const closeBtn = document.getElementById('closeModal');

    if (openBtn) {
        openBtn.onclick = (e) => { e.preventDefault(); modal.style.display = 'flex'; };
    }
    if (closeBtn) {
        closeBtn.onclick = () => { modal.style.display = 'none'; };
    }
    window.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };
</script>

</body>
</html>