<?php
session_start();
require_once __DIR__ . '/config/db.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงหมวดหมู่ที่มีอยู่ทั้งหมด
try {
    $cat_stmt = $pdo->query("SELECT * FROM gi_categories ORDER BY category_name ASC");
    $categories = $cat_stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $developer = trim($_POST['developer'] ?? '');
    $store_url = trim($_POST['store_url'] ?? '');
    
    $category_id = $_POST['category_id'] ?? '';
    $new_category = trim($_POST['new_category'] ?? '');

    $cover_image = trim($_POST['cover_url'] ?? '');

    $review_title = trim($_POST['review_title'] ?? '');
    $rating = (float)($_POST['rating'] ?? 5.0);
    $content_pros = trim($_POST['content_pros'] ?? '');
    $content_cons = trim($_POST['content_cons'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Validation
    if (empty($title) || empty($review_title) || empty($content_pros) || empty($content_cons)) {
        $error = "กรุณากรอกข้อมูลที่จำเป็น (*) ให้ครบถ้วน";
    } else {
        try {
            $pdo->beginTransaction();

            // จัดการหมวดหมู่ใหม่ (ถ้ามีการพิมพ์ชื่อใหม่)
            if (!empty($new_category)) {
                $check_cat = $pdo->prepare("SELECT category_id FROM gi_categories WHERE category_name = ?");
                $check_cat->execute([$new_category]);
                $existing_cat = $check_cat->fetch();

                if ($existing_cat) {
                    $category_id = $existing_cat['category_id'];
                } else {
                    $ins_cat = $pdo->prepare("INSERT INTO gi_categories (category_name) VALUES (?)");
                    $ins_cat->execute([$new_category]);
                    $category_id = $pdo->lastInsertId();
                }
            }

            // เพิ่มเกมใหม่ลงใน gi_games
            $game_stmt = $pdo->prepare("INSERT INTO gi_games (title, developer, cover_image, store_url, category_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $game_stmt->execute([
                $title,
                $developer ?: 'ไม่ระบุ',
                $cover_image ?: 'https://via.placeholder.com/600x350?text=' . urlencode($title),
                $store_url,
                $category_id ?: null
            ]);
            $new_game_id = $pdo->lastInsertId();

            // บันทึกรีวิวลงใน gi_reviews
            $rev_stmt = $pdo->prepare("INSERT INTO gi_reviews (game_id, user_id, title, content_pros, content_cons, rating, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $rev_stmt->execute([$new_game_id, $user_id, $review_title, $content_pros, $content_cons, $rating]);

            $pdo->commit();

            // ส่งกลับไปหน้ารายการเกม
            header("Location: games.php");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มเกมและโพสต์รีวิว - ViewG [8-BIT ARCADE]</title>
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
            background-color: var(--neon-yellow);
            border: 2px solid #fff;
            box-shadow: 3px 3px 0px #000;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.08s, box-shadow 0.08s;
        }

        .pixel-btn:hover {
            transform: translate(2px, 2px);
            box-shadow: 1px 1px 0px #000;
        }

        .pixel-btn-cancel { background: #334155; color: #fff; }

        main { max-width: 700px; margin: 40px auto; padding: 0 16px; }

        .pixel-card {
            background: var(--panel-bg);
            border: 3px solid var(--neon-cyan);
            box-shadow: 6px 6px 0px #000;
            padding: 32px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .section-header {
            border-bottom: 2px dashed #334155;
            padding-bottom: 8px;
            margin-bottom: 20px;
            margin-top: 24px;
            color: var(--neon-cyan);
            font-size: 12px;
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

        textarea.form-control { resize: vertical; min-height: 80px; }

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
            <a href="games.php" class="pixel-btn pixel-btn-cancel">CANCEL</a>
        </div>
    </nav>

    <main>
        <div class="pixel-card">
            <h2 class="pixel-text" style="font-size: 16px; color: var(--neon-yellow); margin-top: 0; text-align: center;">
                🎮 เพิ่มเกมและโพสต์รีวิว
            </h2>

            <?php if (!empty($error)): ?>
                <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="section-header pixel-text">1. ข้อมูลตัวเกม (GAME INFO)</div>

                <div class="form-group">
                    <label for="title" class="pixel-text">ชื่อเกม *</label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="เช่น Black Myth: Wukong" required>
                </div>

                <div class="form-group">
                    <label for="developer" class="pixel-text">ผู้พัฒนา</label>
                    <input type="text" id="developer" name="developer" class="form-control" placeholder="เช่น Game Science">
                </div>

                <div class="form-group">
                    <label for="category_id" class="pixel-text">เลือกหมวดหมู่เกม</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">-- เลือกจากหมวดหมู่ที่มีอยู่ --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="new_category" class="pixel-text">หรือ เพิ่มหมวดหมู่ใหม่</label>
                    <input type="text" id="new_category" name="new_category" class="form-control" placeholder="เช่น Soulslike, Simulator (ถ้าไม่มีในรายการด้านบน)">
                </div>

                <div class="form-group">
                    <label for="cover_url" class="pixel-text">COVER IMAGE URL</label>
                    <input type="url" id="cover_url" name="cover_url" class="form-control" placeholder="https://example.com/cover.jpg">
                </div>

                <div class="form-group">
                    <label for="store_url" class="pixel-text">STORE URL (Steam / Epic Games)</label>
                    <input type="url" id="store_url" name="store_url" class="form-control" placeholder="https://store.steampowered.com/app/...">
                </div>

                <div class="section-header pixel-text" style="color: var(--neon-green);">2. เนื้อหารีวิวของคุณ (REVIEW INFO)</div>

                <div class="form-group">
                    <label for="review_title" class="pixel-text">หัวข้อรีวิว *</label>
                    <input type="text" id="review_title" name="review_title" class="form-control" placeholder="เช่น เกมแอ็กชันยอดเยี่ยมแห่งปี เล่นเพลินไม่มีเบื่อ" required>
                </div>

                <div class="form-group">
                    <label for="rating" class="pixel-text">คะแนนความประทับใจ *</label>
                    <select id="rating" name="rating" class="form-control" required>
                        <option value="5">★ 5.0 - ดีเยี่ยม</option>
                        <option value="4">★ 4.0 - ดี</option>
                        <option value="3">★ 3.0 - ปานกลาง</option>
                        <option value="2">★ 2.0 - พอใช้</option>
                        <option value="1">★ 1.0 - ต้องปรับปรุง</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content_pros" class="pixel-text" style="color: var(--neon-green);">✅ ข้อดี (PROS) *</label>
                    <textarea id="content_pros" name="content_pros" class="form-control" placeholder="ระบุสิ่งที่ประทับใจ จุดเด่นของเกม..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="content_cons" class="pixel-text" style="color: var(--neon-pink);">❌ ข้อเสีย (CONS) *</label>
                    <textarea id="content_cons" name="content_cons" class="form-control" placeholder="ระบุสิ่งที่ไม่ชอบ หรือปัญหาที่พบ..." required></textarea>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px;">
                    <a href="games.php" class="pixel-btn pixel-btn-cancel">CANCEL</a>
                    <button type="submit" class="pixel-btn">POST GAME & REVIEW</button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>