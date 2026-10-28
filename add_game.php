<?php
session_start();
require_once 'config/db.php';

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
    $cover_image = trim($_POST['cover_image'] ?? '');
    $store_url = trim($_POST['store_url'] ?? '');
    
    $category_id = $_POST['category_id'] ?? '';
    $new_category = trim($_POST['new_category'] ?? '');

    $review_title = trim($_POST['review_title'] ?? '');
    $rating = (float)($_POST['rating'] ?? 5.0);
    $content_pros = trim($_POST['content_pros'] ?? '');
    $content_cons = trim($_POST['content_cons'] ?? '');
    $user_id = $_SESSION['user_id'];

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

            // บันทึกเสร็จแล้ว ส่งกลับไปหน้ารายการเกม
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
    <title>เพิ่มเกมและโพสต์รีวิว - GameInsight</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f1f5f9; margin: 0; padding: 40px 15px; color: #1e293b; }
        .card { background: white; max-width: 650px; margin: 0 auto; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        h2 { margin-top: 0; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; }
        h3 { color: #0284c7; margin: 20px 0 10px 0; font-size: 1.1rem; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.95rem; }
        input[type="text"], input[type="url"], select, textarea {
            width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 0.95rem; font-family: inherit;
        }
        textarea { resize: vertical; min-height: 80px; }
        .btn-submit { width: 100%; padding: 14px; background-color: #10b981; color: white; border: none; border-radius: 6px; font-size: 1.05rem; font-weight: bold; cursor: pointer; margin-top: 15px; }
        .btn-submit:hover { background-color: #059669; }
        .alert-error { background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.95rem; }
        .back-link { display: inline-block; margin-top: 15px; color: #64748b; text-decoration: none; font-size: 0.9rem; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="card">
    <h2>🎮 เพิ่มเกมและโพสต์รีวิว</h2>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <h3>1. ข้อมูลตัวเกม</h3>
        <div class="form-group">
            <label for="title">ชื่อเกม *</label>
            <input type="text" id="title" name="title" placeholder="เช่น Black Myth: Wukong" required>
        </div>

        <div class="form-group">
            <label for="developer">ผู้พัฒนา</label>
            <input type="text" id="developer" name="developer" placeholder="เช่น Game Science">
        </div>

        <div class="form-group">
            <label for="category_id">เลือกหมวดหมู่เกม</label>
            <select id="category_id" name="category_id">
                <option value="">-- เลือกจากหมวดหมู่ที่มีอยู่ --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="new_category">หรือ เพิ่มหมวดหมู่ใหม่ (ถ้าไม่มีในรายการด้านบน)</label>
            <input type="text" id="new_category" name="new_category" placeholder="เช่น Soulslike, Simulator">
        </div>

        <div class="form-group">
            <label for="cover_image">ลิงก์ URL รูปหน้าปกเกม</label>
            <input type="url" id="cover_image" name="cover_image" placeholder="https://example.com/cover.jpg">
        </div>

        <div class="form-group">
            <label for="store_url">ลิงก์หน้าร้านค้าทางการ (Steam / Epic Games)</label>
            <input type="url" id="store_url" name="store_url" placeholder="https://store.steampowered.com/app/...">
        </div>

        <h3>2. เนื้อหารีวิวของคุณ</h3>
        <div class="form-group">
            <label for="review_title">หัวข้อรีวิว *</label>
            <input type="text" id="review_title" name="review_title" placeholder="เช่น เกมแอ็กชันยอดเยี่ยมแห่งปี เล่นเพลินไม่มีเบื่อ" required>
        </div>

        <div class="form-group">
            <label for="rating">คะแนนความประทับใจ *</label>
            <select id="rating" name="rating" required>
                <option value="5">⭐⭐⭐⭐⭐ (5 ดาว - ยอดเยี่ยม)</option>
                <option value="4">⭐⭐⭐⭐ (4 ดาว - ดีมาก)</option>
                <option value="3">⭐⭐⭐ (3 ดาว - พอใช้)</option>
                <option value="2">⭐⭐ (2 ดาว - ยังต้องปรับปรุง)</option>
                <option value="1">⭐ (1 ดาว - ไม่แนะนำ)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="content_pros">✅ ข้อดี (Pros) *</label>
            <textarea id="content_pros" name="content_pros" placeholder="ระบุสิ่งที่ประทับใจ จุดเด่นของเกม..." required></textarea>
        </div>

        <div class="form-group">
            <label for="content_cons">❌ ข้อเสีย (Cons) *</label>
            <textarea id="content_cons" name="content_cons" placeholder="ระบุสิ่งที่ไม่ชอบ หรือปัญหาที่พบ..." required></textarea>
        </div>

        <button type="submit" class="btn-submit">🚀 โพสต์เกมและรีวิวทันที</button>
    </form>

    <a href="games.php" class="back-link">← ยกเลิกและกลับไปหน้ารายการเกม</a>
</div>

</body>
</html>