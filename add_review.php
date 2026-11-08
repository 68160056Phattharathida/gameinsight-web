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
    <title>เขียนรีวิวเกม: <?= htmlspecialchars($game['title']) ?> - ViewG [8-BIT ARCADE]</title>
    <!-- Google Fonts 8-BIT & Prompt -->
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

        * { box-sizing: border-box; image-rendering: pixelated; }

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
        }

        .pixel-text { font-family: 'Press Start 2P', monospace; }

        /* Navbar Header */
        nav {
            background: var(--panel-bg);
            border-bottom: 4px solid var(--neon-cyan);
            box-shadow: 0 4px 0px #000;
            padding: 16px 24px;
        }

        .nav-container {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo { font-size: 20px; color: var(--neon-cyan); text-decoration: none; text-shadow: 2px 2px 0px #000; }

        .pixel-btn {
            display: inline-block;
            font-family: 'Press Start 2P', monospace;
            font-size: 10px;
            padding: 8px 14px;
            color: #000;
            background-color: var(--neon-green);
            border: 2px solid #fff;
            box-shadow: 3px 3px 0px #000;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.08s, box-shadow 0.08s;
        }

        .pixel-btn:hover {
            transform: translate(2px, 2px);
            box-shadow: 1px 1px 0px #000;
            background-color: var(--neon-yellow);
        }

        .pixel-btn-cancel { background: #334155; color: #fff; }

        main { max-width: 650px; margin: 40px auto; padding: 0 16px; }

        .pixel-card {
            background: var(--panel-bg);
            border: 3px solid var(--neon-cyan);
            box-shadow: 6px 6px 0px #000;
            padding: 32px;
            border-radius: 8px;
        }

        .game-subtitle {
            text-align: center;
            color: var(--neon-cyan);
            font-size: 15px;
            margin-bottom: 24px;
            font-weight: 600;
            word-break: break-word;
        }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 11px; color: #94a3b8; }
        
        .form-control {
            width: 100%;
            padding: 11px;
            background: #080a0f;
            border: 2px solid #334155;
            color: #fff;
            font-family: 'Prompt', sans-serif;
            font-size: 14px;
            outline: none;
        }

        .form-control:focus { border-color: var(--neon-cyan); box-shadow: 0 0 8px rgba(0,240,255,0.2); }

        textarea.form-control { resize: vertical; min-height: 90px; }

        .submit-btn {
            width: 100%;
            padding: 12px;
            font-size: 11px;
            margin-top: 10px;
        }

        .alert-danger {
            background: #2b0813;
            border: 2px solid var(--neon-pink);
            color: #fecdd3;
            padding: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 3px 3px 0px #000;
        }
    </style>
</head>
<body>

    <nav>
        <div class="nav-container">
            <a href="games.php" class="logo pixel-text">🎮 ViewG</a>
            <a href="game_detail.php?game_id=<?= $game_id ?>" class="pixel-btn pixel-btn-cancel">CANCEL</a>
        </div>
    </nav>

    <main>
        <div class="pixel-card">
            <h2 class="pixel-text" style="font-size: 16px; color: var(--neon-yellow); margin-top: 0; text-align: center;">
                ✍️ เขียนรีวิวเกม
            </h2>
            <div class="game-subtitle">
                🎮 <?= htmlspecialchars($game['title']) ?>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="title" class="pixel-text">REVIEW TITLE *</label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="เช่น สุดยอดเกมแนว Souls ที่ดีที่สุดแห่งปี" required>
                </div>

                <div class="form-group">
                    <label for="rating" class="pixel-text">RATING (1 - 5 STARS) *</label>
                    <select id="rating" name="rating" class="form-control" required>
                        <option value="5">⭐⭐⭐⭐⭐ (5/5 ยอดเยี่ยม)</option>
                        <option value="4">⭐⭐⭐⭐ (4/5 ดีมาก)</option>
                        <option value="3">⭐⭐⭐ (3/5 พอใช้)</option>
                        <option value="2">⭐⭐ (2/5 ควรปรับปรุง)</option>
                        <option value="1">⭐ (1/5 ไม่แนะนำ)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content_pros" class="pixel-text" style="color: var(--neon-green);">✅ PROS (ข้อดี) *</label>
                    <textarea id="content_pros" name="content_pros" class="form-control" placeholder="ระบุสิ่งที่ชอบ เช่น ภาพสวย ระบบต่อสู้ลื่นไหล..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="content_cons" class="pixel-text" style="color: var(--neon-pink);">❌ CONS (ข้อเสีย) *</label>
                    <textarea id="content_cons" name="content_cons" class="form-control" placeholder="ระบุสิ่งที่ไม่ชอบ เช่น ปัญหา Optimization, บั๊ก..." required></textarea>
                </div>

                <button type="submit" class="pixel-btn submit-btn">POST REVIEW</button>
            </form>
        </div>
    </main>

</body>
</html>