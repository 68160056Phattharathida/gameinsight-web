<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth_check.php';

// บังคับล็อกอินก่อนทำการแก้ไข
requireLogin();

$review_id = isset($_GET['review_id']) ? (int)$_GET['review_id'] : 0;
$user_id = $_SESSION['user_id'];

// ดึงข้อมูลรีวิวเฉพาะของเจ้าของ หรือ Admin
$stmt = $pdo->prepare("
    SELECT r.*, g.title AS game_title 
    FROM gi_reviews r
    JOIN gi_games g ON r.game_id = g.game_id
    WHERE r.review_id = ?
");
$stmt->execute([$review_id]);
$review = $stmt->fetch(PDO::FETCH_ASSOC);

// ตรวจสอบสิทธิ์ (ต้องเป็นเจ้าของรีวิว หรือ Admin เท่านั้น)
if (!$review || ($review['user_id'] != $user_id && ($_SESSION['role'] ?? '') !== 'admin')) {
    die("คุณไม่มีสิทธิ์ในการแก้ไขรีวิวนี้");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content_pros = trim($_POST['content_pros'] ?? '');
    $content_cons = trim($_POST['content_cons'] ?? '');
    $rating = (float)($_POST['rating'] ?? 5.0);

    if (empty($title) || empty($content_pros) || empty($content_cons)) {
        $error = "กรุณากรอกข้อมูลรีวิวให้ครบถ้วน";
    } elseif ($rating < 1 || $rating > 5) {
        $error = "คะแนนต้องอยู่ระหว่าง 1 ถึง 5";
    } else {
        try {
            $update_sql = "UPDATE gi_reviews 
                           SET title = ?, content_pros = ?, content_cons = ?, rating = ? 
                           WHERE review_id = ?";
            $up_stmt = $pdo->prepare($update_sql);
            $up_stmt->execute([$title, $content_pros, $content_cons, $rating, $review_id]);

            header("Location: game_detail.php?game_id=" . $review['game_id']);
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
    <title>แก้ไขรีวิวเกม: <?= htmlspecialchars($review['game_title']) ?> - ViewG [8-BIT ARCADE]</title>
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
            color: #000 !important;
            background-color: var(--neon-yellow);
            border: 2px solid #fff;
            box-shadow: 3px 3px 0px #000;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.08s, box-shadow 0.08s;
            font-weight: bold;
        }

        .pixel-btn:hover {
            transform: translate(2px, 2px);
            box-shadow: 1px 1px 0px #000;
            background-color: var(--neon-green);
        }

        .pixel-btn-cancel { background: #334155; color: #fff !important; }

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
            <a href="game_detail.php?game_id=<?= $review['game_id'] ?>" class="pixel-btn pixel-btn-cancel">CANCEL</a>
        </div>
    </nav>

    <main>
        <div class="pixel-card">
            <h2 class="pixel-text" style="font-size: 16px; color: var(--neon-yellow); margin-top: 0; text-align: center;">
                ✏️ แก้ไขรีวิวเกม
            </h2>
            <div class="game-subtitle">
                🎮 <?= htmlspecialchars($review['game_title']) ?>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="title" class="pixel-text">REVIEW TITLE *</label>
                    <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($review['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="rating" class="pixel-text">RATING (1 - 5 STARS) *</label>
                    <select id="rating" name="rating" class="form-control" required>
                        <option value="5" <?= (float)$review['rating'] == 5 ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ (5/5 ยอดเยี่ยม)</option>
                        <option value="4" <?= (float)$review['rating'] == 4 ? 'selected' : '' ?>>⭐⭐⭐⭐ (4/5 ดีมาก)</option>
                        <option value="3" <?= (float)$review['rating'] == 3 ? 'selected' : '' ?>>⭐⭐⭐ (3/5 พอใช้)</option>
                        <option value="2" <?= (float)$review['rating'] == 2 ? 'selected' : '' ?>>⭐⭐ (2/5 ควรปรับปรุง)</option>
                        <option value="1" <?= (float)$review['rating'] == 1 ? 'selected' : '' ?>>⭐ (1/5 ไม่แนะนำ)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content_pros" class="pixel-text" style="color: var(--neon-green);">✅ PROS (ข้อดี) *</label>
                    <textarea id="content_pros" name="content_pros" class="form-control" required><?= htmlspecialchars($review['content_pros']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="content_cons" class="pixel-text" style="color: var(--neon-pink);">❌ CONS (ข้อเสีย) *</label>
                    <textarea id="content_cons" name="content_cons" class="form-control" required><?= htmlspecialchars($review['content_cons']) ?></textarea>
                </div>

                <button type="submit" class="pixel-btn submit-btn">UPDATE REVIEW</button>
            </form>
        </div>
    </main>

</body>
</html>