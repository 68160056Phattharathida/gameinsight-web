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
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; padding-top: 40px; margin: 0; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"], .form-group input[type="email"], .form-group input[type="password"] {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #bdbdbd;
            color: #666;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: not-allowed;
            transition: background-color 0.2s;
        }

        .btn:enabled {
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }

        .btn:enabled:hover {
            background-color: #218838;
        }

        .btn-login {
            display: inline-block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            font-size: 15px;
            font-weight: bold;
            box-sizing: border-box;
            margin-top: 10px;
        }

        .btn-login:hover {
            background-color: #0056b3;
        }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        .login-link { text-align: center; margin-top: 15px; font-size: 14px; }
        .login-link a { color: #007bff; text-decoration: none; font-weight: bold; }

        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 999; }
        .modal-content { background: white; padding: 25px; border-radius: 8px; width: 85%; max-width: 650px; max-height: 80vh; overflow-y: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .close-btn { float: right; cursor: pointer; font-weight: bold; font-size: 24px; color: #64748b; line-height: 1; }
        .close-btn:hover { color: #000; }
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
                <label style="font-weight: normal; cursor: pointer;">
                    <input type="checkbox" name="agreed_terms" value="1" required>
                    ฉันยอมรับ <a href="#" id="openModal" style="color: #007bff;">ข้อกำหนดการให้บริการและนโยบายความเป็นส่วนตัว</a>
                </label>
            </div>

            <button type="submit" class="btn" id="registerBtn" disabled>
                สมัครสมาชิก
            </button>
        </form>

        <div class="login-link">
            มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a>
        </div>
    <?php endif; ?>
</div>

<div id="tosModal" class="modal">
    <div class="modal-content" style="text-align: left; line-height: 1.6; color: #1e293b;">
        <h3 style="margin-top: 0; color: #0f172a; font-size: 1.25rem;">ข้อกำหนดการให้บริการและนโยบายความเป็นส่วนตัว</h3>
        <p style="color: #64748b; margin-top: -10px; font-size: 0.95rem; font-weight: bold;">(Terms of Service & Disclaimer)</p>
        
        <p>ยินดีต้อนรับสู่ <strong>GameInsight</strong> แพลตฟอร์มสำหรับแบ่งปันประสบการณ์และรีวิวเกมจากผู้เล่นจริง เพื่อความเข้าใจและความปลอดภัยร่วมกัน กรุณาอ่านเงื่อนไขต่อไปนี้ก่อนสมัครใช้งาน:</p>
        
        <div style="background-color: #f0fdf4; border-left: 4px solid #22c55e; padding: 12px; margin: 15px 0; font-size: 14px; border-radius: 4px;">
            <strong>วัตถุประสงค์ของเว็บไซต์:</strong> แพลตฟอร์มนี้จัดทำขึ้นเพื่อการแลกเปลี่ยนความคิดเห็น คำแนะนำ และบทวิเคราะห์เกี่ยวกับเกม ไม่มีจุดประสงค์ในการจำหน่าย แจกจ่าย หรือส่งเสริมการละเมิดลิขสิทธิ์ซอฟต์แวร์ทุกประเภท
        </div>

        <h4 style="color: #0f172a; margin: 15px 0 5px 0;">1. เงื่อนไขการใช้งานของผู้ใช้</h4>
        <ul style="padding-left: 20px; margin: 0;">
            <li>ผู้ใช้งานต้องกรอกข้อมูลจริงในการสมัครสมาชิก และรับผิดชอบความปลอดภัยของบัญชีตนเอง</li>
            <li>การเผยแพร่เนื้อหาและรีวิวต้องเป็นความคิดเห็นส่วนบุคคลจากประสบการณ์ตรง ไม่คัดลอกผลงานของผู้อื่นโดยไม่ได้รับอนุญาต</li>
            <li>ห้ามโพสต์ข้อความที่เป็นการหมิ่นประมาท หยาบคาย สแปม หรือเนื้อหาที่ขัดต่อกฎหมายและศีลธรรมอันดี</li>
            <li>ห้ามแจกจ่ายลิงก์สำหรับดาวน์โหลดเกมเถื่อน แค็ก หรือไฟล์ละเมิดลิขสิทธิ์ใดๆ บนเว็บไซต์เด็ดขาด</li>
        </ul>

        <h4 style="color: #0f172a; margin: 15px 0 5px 0;">2. ข้อจำกัดความรับผิดชอบ (Disclaimer)</h4>
        <ul style="padding-left: 20px; margin: 0;">
            <li>เนื้อหารีวิวและคะแนนที่ปรากฏบนเว็บไซต์เป็นมุมมองของผู้เขียนแต่ละบุคคล ทาง GameInsight ไม่มีส่วนรับผิดชอบต่อความเสียหายที่อาจเกิดขึ้นจากการตัดสินใจซื้อหรือใช้งานเกมตามรีวิวนั้นๆ</li>
            <li>ลิงก์ไปยังร้านค้าทางการ (เช่น Steam, Epic Games Store ฯลฯ) จัดทำขึ้นเพื่ออำนวยความสะดวกเท่านั้น GameInsight ไม่มีส่วนเกี่ยวข้องกับธุรกรรมการเงินระหว่างผู้ใช้กับผู้ให้บริการภายนอก</li>
        </ul>

        <h4 style="color: #0f172a; margin: 15px 0 5px 0;">3. สิทธิ์การจัดการเนื้อหา (Content Moderation)</h4>
        <ul style="padding-left: 20px; margin: 0;">
            <li>ผู้ดูแลระบบ (Admin) ขอสงวนสิทธิ์ในการแก้ไข ระงับ หรือลบรีวิวและคอมเมนต์ที่มีพฤติกรรมละเมิดข้อกำหนดโดยไม่ต้องแจ้งให้ทราบล่วงหน้า</li>
        </ul>

        <!-- ปุ่มย้อนกลับด้านล่าง -->
        <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
            <a href="#" id="backBtnModal" style="color: #2563eb; text-decoration: none; font-weight: bold; font-size: 15px;">← ย้อนกลับ</a>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('tosModal');
    const openBtn = document.getElementById('openModal');
    const backBtn = document.getElementById('backBtnModal');

    if (openBtn && modal) {
        openBtn.onclick = (e) => {
            e.preventDefault();
            modal.style.display = 'flex';
        };
    }

    // ปิดหน้าต่างด้วยปุ่ม ← ย้อนกลับ ด้านล่าง
    if (backBtn && modal) {
        backBtn.onclick = (e) => {
            e.preventDefault();
            modal.style.display = 'none';
        };
    }

    // คลิกพื้นที่สีเทารอบนอกเพื่อปิด
    window.onclick = (e) => {
        if (modal && e.target === modal) {
            modal.style.display = 'none';
        }
    };

    // ตรวจสอบข้อมูลก่อนเปิดใช้งานปุ่มสมัครสมาชิก
    const form = document.querySelector('.card form');
    const registerBtn = document.getElementById('registerBtn');

    if (form && registerBtn) {
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const agreedTerms = form.querySelector('input[name="agreed_terms"]');

        function checkForm() {
            const allFilled =
                username.value.trim() !== '' &&
                email.value.trim() !== '' &&
                password.value !== '' &&
                confirmPassword.value !== '';

            const validEmail = email.validity.valid;
            const validPassword = password.value.length >= 6;
            const passwordsMatch = password.value === confirmPassword.value;
            const termsAccepted = agreedTerms.checked;

            registerBtn.disabled = !(
                allFilled &&
                validEmail &&
                validPassword &&
                passwordsMatch &&
                termsAccepted
            );
        }

        form.addEventListener('input', checkForm);
        form.addEventListener('change', checkForm);

        checkForm();
    }
</script>

</body>
</html>