<?php
session_start();
require_once 'config/db.php';

// ถ้าล็อกอินอยู่แล้ว ให้เปลี่ยนหน้าไป games.php
if (isset($_SESSION['user_id'])) {
    header("Location: games.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_email = trim($_POST['username_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username_email) || empty($password)) {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } else {
        // ค้นหาผู้ใช้จาก username หรือ email ในตาราง users
        $stmt = $pdo->prepare("SELECT * FROM gi_users WHERE username = :input OR email = :input LIMIT 1");
        $stmt->execute(['input' => $username_email]);
        $user = $stmt->fetch();

        // ตรวจสอบรหัสผ่านด้วย password_verify()
        if ($user && password_verify($password, $user['password_hash'])) {
            // ป้องกัน Session Fixation
            session_regenerate_id(true);

            // จัดเก็บข้อมูลลงใน $_SESSION
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'member';

            // ส่งไปยังหน้าหลักใหม่ games.php
            header("Location: games.php");
            exit();
        } else {
            $error = "ชื่อผู้ใช้/อีเมล หรือรหัสผ่านไม่ถูกต้อง";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - GameInsight</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #1a1a1a; color: #fff; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-card { background-color: #2a2a2a; padding: 30px; border-radius: 8px; width: 100%; max-width: 400px; box-shadow: 0 4px 10px rgba(0,0,0,0.5); }
        h2 { text-align: center; margin-bottom: 20px; color: #00d2d3; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #444; background: #333; color: #fff; box-sizing: border-box; }
        .btn { width: 100%; padding: 10px; background-color: #00d2d3; border: none; border-radius: 4px; color: #1a1a1a; font-weight: bold; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .btn:hover { background-color: #00a8a9; }
        .alert { background-color: #ff4d4d; color: white; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; text-align: center; }
        .register-link { text-align: center; margin-top: 15px; font-size: 14px; }
        .register-link a { color: #00d2d3; text-decoration: none; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>เข้าสู่ระบบ</h2>

    <?php if (!empty($error)): ?>
        <div class="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username_email">ชื่อผู้ใช้ หรือ อีเมล</label>
            <input type="text" id="username_email" name="username_email" required value="<?php echo isset($_POST['username_email']) ? htmlspecialchars($_POST['username_email']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="password">รหัสผ่าน</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn">เข้าสู่ระบบ</button>
    </form>

    <div class="register-link">
        ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a>
    </div>
</div>

</body>
</html>