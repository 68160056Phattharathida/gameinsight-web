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

    // 3. บันทึกลงฐานข้อมูล
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
    <title>สมัครสมาชิก - ViewG / GameInsight</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #0b0c10;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .cyber-card {
            background-color: #12141d;
            border: 2px solid #00f2fe;
            box-shadow: 0 0 20px rgba(0, 242, 254, 0.25), inset 0 0 10px rgba(0, 242, 254, 0.05);
        }

        .cyber-input {
            background-color: #0b0c10;
            border: 1px solid #2a2e3d;
            color: #ffffff;
            transition: all 0.2s ease;
        }

        .cyber-input:focus {
            border-color: #00f2fe;
            box-shadow: 0 0 8px rgba(0, 242, 254, 0.5);
            outline: none;
        }

        /* ปุ่มตอนกดได้ (Enabled) */
        .btn-cyber-enabled {
            background-color: #ff007f !important;
            color: #ffffff !important;
            border: 2px solid #ff007f !important;
            box-shadow: 0 0 12px rgba(255, 0, 127, 0.5) !important;
            cursor: pointer !important;
            transition: all 0.2s !important;
        }

        .btn-cyber-enabled:hover {
            background-color: #e0006f !important;
            box-shadow: 0 0 18px rgba(255, 0, 127, 0.8) !important;
        }

        /* ปุ่มตอนกดไม่ได้ (Disabled) */
        .btn-cyber-disabled {
            background-color: #1f2330 !important;
            color: #4b5563 !important;
            border: 1px solid #374151 !important;
            cursor: not-allowed !important;
            box-shadow: none !important;
        }

        .cyber-modal {
            background-color: #12141d;
            border: 2px solid #00f2fe;
            box-shadow: 0 0 25px rgba(0, 242, 254, 0.4);
        }
    </style>
</head>
<body class="text-gray-100 min-h-screen flex flex-col justify-between">

    <!-- Header / Navbar -->
    <nav class="border-b-2 border-cyan-400 bg-gray-900/80 backdrop-blur-md px-6 py-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <a href="games.php" class="text-2xl font-bold tracking-wider text-cyan-400 flex items-center gap-2">
                🎮 ViewG
            </a>
            <div class="space-x-4 text-sm font-medium">
                <a href="games.php" class="text-gray-300 hover:text-cyan-400 transition-colors">หน้ารายการเกม</a>
                <a href="login.php" class="bg-cyan-400 text-gray-950 px-3 py-1.5 rounded font-bold hover:bg-cyan-300 transition-all">เข้าสู่ระบบ</a>
            </div>
        </div>
    </nav>

    <!-- Main Content Card -->
    <main class="flex-grow flex items-center justify-center px-4 py-8">
        <div class="cyber-card rounded-xl p-8 max-w-md w-full relative">
            
            <h2 class="text-2xl font-bold text-center text-white mb-1 tracking-wide">
                📝 สมัครสมาชิก
            </h2>
            <p class="text-xs text-center text-cyan-400 mb-6 font-mono">CREATE YOUR CYBER ACCOUNT</p>

            <!-- Alerts -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-950/80 border border-red-500 text-red-200 text-xs p-3 rounded mb-4 font-mono space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <p>• <?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-emerald-950/80 border border-emerald-400 text-emerald-200 text-sm p-4 rounded mb-4 text-center font-mono">
                    <p class="font-bold mb-3">✅ <?= htmlspecialchars($success) ?></p>
                    <a href="login.php" class="inline-block w-full py-2 bg-cyan-400 text-gray-950 font-bold rounded hover:bg-cyan-300 transition-colors">
                        เข้าสู่ระบบทันที →
                    </a>
                </div>
            <?php else: ?>

                <form method="POST" action="" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div>
                        <label for="username" class="block text-xs font-semibold text-gray-300 uppercase mb-1">Username</label>
                        <input type="text" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               class="w-full text-sm p-3 rounded cyber-input" placeholder="เช่น gamer_pro">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-300 uppercase mb-1">Email</label>
                        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               class="w-full text-sm p-3 rounded cyber-input" placeholder="yourname@email.com">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-gray-300 uppercase mb-1">Password</label>
                        <input type="password" id="password" name="password" required minlength="6"
                               class="w-full text-sm p-3 rounded cyber-input" placeholder="อย่างน้อย 6 ตัวอักษร">
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-xs font-semibold text-gray-300 uppercase mb-1">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                               class="w-full text-sm p-3 rounded cyber-input" placeholder="กรอกรหัสผ่านซ้ำอีกครั้ง">
                    </div>

                    <div class="flex items-start gap-2 pt-1">
                        <input type="checkbox" id="agreed_terms" name="agreed_terms" value="1" required class="mt-1 accent-cyan-400 cursor-pointer">
                        <label for="agreed_terms" class="text-xs text-gray-400 leading-relaxed">
                            ฉันยอมรับ 
                            <a href="#" id="openModal" class="text-cyan-400 underline hover:text-cyan-300 font-medium">
                                ข้อกำหนดการให้บริการและนโยบายความเป็นส่วนตัว
                            </a>
                        </label>
                    </div>

                    <button type="submit" id="registerBtn" disabled
                            class="w-full py-3 rounded font-bold tracking-wider text-sm transition-all btn-cyber-disabled">
                        สมัครสมาชิก
                    </button>
                </form>

                <div class="border-t border-gray-800 mt-6 pt-4 text-center">
                    <p class="text-xs text-gray-400">
                        มีบัญชีอยู่แล้ว? 
                        <a href="login.php" class="text-cyan-400 font-semibold hover:underline">
                            เข้าสู่ระบบ
                        </a>
                    </p>
                </div>

            <?php endif; ?>
        </div>
    </main>

    <!-- Modal ข้อกำหนด -->
    <div id="tosModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden justify-center items-center p-4 z-50">
        <div class="cyber-modal rounded-xl p-6 max-w-lg w-full max-h-[75vh] overflow-y-auto relative text-gray-300">
            <span class="close-btn text-gray-400 hover:text-cyan-400 float-right text-2xl font-bold cursor-pointer" id="closeModal">&times;</span>
            <h3 class="text-lg font-bold text-cyan-400 mb-4 border-b border-gray-800 pb-2">📜 ข้อกำหนดการให้บริการ (Terms of Service)</h3>
            <div class="space-y-3 text-xs leading-relaxed">
                <p>1. ผู้ใช้ต้องกรอกข้อมูลที่เป็นความจริงในการสมัครสมาชิก</p>
                <p>2. ห้ามใช้ระบบนี้ในการส่งข้อมูลที่เป็นเท็จหรือละเมิดกฎหมาย</p>
                <p>3. ระบบจะทำการเก็บรักษาข้อมูลของคุณอย่างปลอดภัยตามนโยบายความเป็นส่วนตัว</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 text-xs text-gray-500 border-t border-gray-900">
        &copy; 2026 ViewG. All rights reserved.
    </footer>

<script>
    // Modal Script
    const modal = document.getElementById('tosModal');
    const openBtn = document.getElementById('openModal');
    const closeBtn = document.getElementById('closeModal');

    if (openBtn && modal) {
        openBtn.onclick = (e) => {
            e.preventDefault();
            modal.style.display = 'flex';
        };
    }

    if (closeBtn && modal) {
        closeBtn.onclick = () => {
            modal.style.display = 'none';
        };
    }

    window.onclick = (e) => {
        if (modal && e.target === modal) {
            modal.style.display = 'none';
        }
    };

    // Form Validation Script
    const form = document.querySelector('.cyber-card form');
    const registerBtn = document.getElementById('registerBtn');

    if (form && registerBtn) {
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const agreedTerms = document.getElementById('agreed_terms');

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

            const isValid = allFilled && validEmail && validPassword && passwordsMatch && termsAccepted;

            registerBtn.disabled = !isValid;

            if (isValid) {
                registerBtn.classList.remove('btn-cyber-disabled');
                registerBtn.classList.add('btn-cyber-enabled');
            } else {
                registerBtn.classList.remove('btn-cyber-enabled');
                registerBtn.classList.add('btn-cyber-disabled');
            }
        }

        form.addEventListener('input', checkForm);
        form.addEventListener('change', checkForm);

        checkForm();
    }
</script>
</body>
</html>