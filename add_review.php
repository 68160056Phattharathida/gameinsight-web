<?php
session_start();
require_once 'config/db.php';
require_once 'auth_check.php';

// บังคับล็อกอินก่อนเขียนรีวิว
requireLogin();

$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;

// ตรวจสอบว่ามีเกมนี้อยู่จริงหรือไม่
$stmt = $pdo->prepare("SELECT game_id, title FROM gi_games WHERE game_id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch();

if (!$game) {
    die("ไม่พบข้อมูลเกมที่ต้องการรีวิว");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content_pros = trim($_POST['content_pros'] ?? '');
    $content_cons = trim($_POST['content_cons'] ?? '');
    $rating = (float)($_POST['rating'] ?? 5.0);
    $user_id = $_SESSION['user_id'];

    if (empty($title) || empty($content_pros) || empty($content_cons)) {
        $error = "กรุณากรอกข้อมูลรีวิวให้ครบถ้วน";
    } elseif ($rating < 1 || $rating > 5) {
        $error = "คะแนนต้องอยู่ระหว่าง 1 ถึง 5";
    } else {
        try {
            $insert_sql = "INSERT INTO gi_reviews (game_id, user_id, title, content_pros, content_cons, rating, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $ins_stmt = $pdo->prepare($insert_sql);
            $ins_stmt->execute([$game_id, $user_id, $title, $content_pros, $content_cons, $rating]);

            header("Location: game_detail.php?game_id=" . $game_id);
            exit();
        } catch (PDOException $e) {
            $error = "เกิดข้อผิดพลาดในการบันทึก: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เขียนรีวิวเกม: <?= htmlspecialchars($game['title']) ?> - GameInsight</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; margin: 0; padding: 40px 15px; color: #1e293b; }
        .card { background: white; max-width: 600px; margin: 0 auto; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        h2 { margin-top: 0; color: #0f172a; }
        .game-tag { color: #0284c7; font-weight: bold; margin-bottom: 20px; font-size: 1.1rem; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.95rem; }
        input[type="text"], input[type="number"], textarea, select {
            width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 0.95rem;
        }
        textarea { resize: vertical; min-height: 80px; }
        .btn-submit { width: 100%; padding: 12px; background-color: #10b981; color: white; border: none; border-radius: 6px; font-size: 1rem; font-weight: bold; cursor: pointer; }
        .btn-submit:hover { background-color: #059669; }
        .back-link { display: inline-block; margin-top: 15px; color: #64748b; text-decoration: none; font-size: 0.9rem; }
        .back-link:hover { text-decoration: underline; }
        .alert-error { background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="card">
    <h2>✍️ เขียนรีวิวเกม</h2>
    <div class="game-tag">🎮 <?= htmlspecialchars($game['title']) ?></div>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="title">หัวข้อรีวิว</label>
            <input type="text" id="title" name="title" placeholder="เช่น สุดยอดเกมแนว Souls ที่ดีที่สุดแห่งปี" required>
        </div>

        <div class="form-group">
            <label for="rating">ให้คะแนนเกม (1 - 5 ดาว)</label>
            <select id="rating" name="rating" required>
                <option value="5">⭐⭐⭐⭐⭐ (5/5 ยอดเยี่ยม)</option>
                <option value="4">⭐⭐⭐⭐ (4/5 ดีมาก)</option>
                <option value="3">⭐⭐⭐ (3/5 พอใช้)</option>
                <option value="2">⭐⭐ (2/5 ควรปรับปรุง)</option>
                <option value="1">⭐ (1/5 ไม่แนะนำ)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="content_pros">✅ ข้อดี (Pros)</label>
            <textarea id="content_pros" name="content_pros" placeholder="ระบุสิ่งที่ชอบ เช่น ภาพสวย ระบบต่อสู้ลื่นไหล..." required></textarea>
        </div>

        <div class="form-group">
            <label for="content_cons">❌ ข้อเสีย (Cons)</label>
            <textarea id="content_cons" name="content_cons" placeholder="ระบุสิ่งที่ไม่ชอบ เช่น ปัญหา Optimization, บั๊ก..." required></textarea>
        </div>

        <button type="submit" class="btn-submit">เผยแพร่รีวิว</button>
    </form>

    <a href="game_detail.php?game_id=<?= $game_id ?>" class="back-link">← ยกเลิกและกลับไปหน้ารายละเอียดเกม</a>
</div>

</body>
</html>