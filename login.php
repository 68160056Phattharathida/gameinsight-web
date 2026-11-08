<?php
session_start();
require_once __DIR__ . '/config/db.php';

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
        // ค้นหาผู้ใช้จาก username หรือ email ในตาราง gi_users
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
    <title>เข้าสู่ระบบ - ViewG [8-BIT ARCADE]</title>
    <!-- Pixel Font สำหรับหัวข้อ/ปุ่ม และ Prompt สำหรับภาษาไทย -->
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0a0c13;
            --panel-bg: #131722;
            --neon-cyan: #00f0ff;
            --neon-pink: #ff0055;
            --neon-green: #00ff66;
            --neon-yellow: #ffe600;
            --text-main: #f1f5f9;
        }

        * { 
            box-sizing: border-box; 
            image-rendering: pixelated; 
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--bg-dark);
            background-image: 
                linear-gradient(rgba(0, 240, 255, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 240, 255, 0.04) 1px, transparent 1px);
            background-size: 20px 20px;
            color: var(--text-main);
            font-family: 'Prompt', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .pixel-text { 
            font-family: 'Press Start 2P', monospace; 
        }

        /* Navbar Header */
        nav {
            background: var(--panel-bg);
            border-bottom: 4px solid var(--neon-cyan);
            box-shadow: 0 4px 0px #000;
            padding: 16px 24px;
        }

        .nav-container {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 20px;
            color: var(--neon-cyan);
            text-decoration: none;
            text-shadow: 2px 2px 0px #000;
        }

        .nav-links { 
            display: flex; 
            align-items: center; 
            gap: 14px; 
        }

        .nav-links a { 
            color: #94a3b8; 
            text-decoration: none; 
            font-size: 14px; 
        }

        .nav-links a:hover { 
            color: var(--neon-cyan); 
        }

        /* Pixel Buttons */
        .pixel-btn {
            display: inline-block;
            font-family: 'Press Start 2P', monospace;
            font-size: 10px;
            padding: 8px 12px;
            color: #000000 !important;
            background-color: var(--neon-pink);
            border: 2px solid #fff;
            box-shadow: 3px 3px 0px #000;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.08s, box-shadow 0.08s, background-color 0.08s;
            text-align: center;
            font-weight: bold;
        }

        .pixel-btn:hover {
            transform: translate(2px, 2px);
            box-shadow: 1px 1px 0px #000;
            background-color: var(--neon-yellow);
            color: #000000 !important;
        }

        .pixel-btn-cyan {
            background-color: var(--neon-cyan);
            color: #000000 !important;
        }

        .pixel-btn-cyan:hover {
            background-color: var(--neon-yellow);
            color: #000000 !important;
        }

        /* Container Layout */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        .login-card {
            background: var(--panel-bg);
            border: 3px solid var(--neon-cyan);
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.2), 6px 6px 0px #000;
            padding: 32px;
            width: 100%;
            max-width: 420px;
            border-radius: 12px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .login-header h2 {
            margin: 0 0 8px 0;
            font-size: 22px;
            color: #fff;
        }

        .login-header .sub-title {
            font-size: 9px;
            color: var(--neon-cyan);
            letter-spacing: 1px;
        }

        .alert-danger {
            background: #2b0813;
            border: 2px solid var(--neon-pink);
            color: #fecdd3;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 3px 3px 0px #000;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 11px;
            color: #94a3b8;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            background: #080a0f;
            border: 2px solid #334155;
            color: #fff;
            font-family: 'Prompt', sans-serif;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            border-color: var(--neon-cyan);
            box-shadow: 0 0 8px rgba(0, 240, 255, 0.3);
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            font-size: 11px;
            margin-top: 10px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px dashed #334155;
            font-size: 13px;
            color: #94a3b8;
        }

        .register-link a {
            color: var(--neon-cyan);
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
            color: var(--neon-yellow);
        }
    </style>
</head>
<body>

    <!-- Navbar Header -->
    <nav>
        <div class="nav-container">
            <a href="games.php" class="logo pixel-text">🎮 ViewG</a>
            <div class="nav-links">
                <a href="games.php">หน้ารายการเกม</a>
                <a href="register.php" class="pixel-btn pixel-btn-cyan">สมัครสมาชิก</a>
            </div>
        </div>
    </nav>

    <!-- Main Content Card -->
    <main>
        <div class="login-card">
            <div class="login-header">
                <h2>🎮 เข้าสู่ระบบ</h2>
                <div class="pixel-text sub-title">SYSTEM LOGIN ACCESS</div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username_email" class="pixel-text">USERNAME / EMAIL</label>
                    <input type="text" id="username_email" name="username_email" required 
                           placeholder="กรอกชื่อผู้ใช้ หรือ อีเมล"
                           value="<?= isset($_POST['username_email']) ? htmlspecialchars($_POST['username_email']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="password" class="pixel-text">PASSWORD</label>
                    <input type="password" id="password" name="password" required placeholder="กรอกรหัสผ่าน">
                </div>

                <button type="submit" class="pixel-btn submit-btn">LOG IN</button>
            </form>

            <div class="register-link">
                ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิกที่นี่</a>
            </div>
        </div>
    </main>

</body>
</html>