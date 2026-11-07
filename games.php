<?php
session_start();
require_once 'config/db.php';

// ดึงรายการหมวดหมู่ทั้งหมดสำหรับทำแถบ Filter
try {
    $cat_stmt = $pdo->query("SELECT category_id, category_name FROM gi_categories ORDER BY category_name ASC");
    $categories = $cat_stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// รับค่าการค้นหาและการกรองหมวดหมู่
$search = trim($_GET['search'] ?? '');
$selected_category = $_GET['category'] ?? '';

// สร้างคำสั่ง SQL ดึงรายชื่อเกม
$sql = "SELECT g.game_id, g.title, g.developer, g.cover_image, g.store_url, c.category_name 
        FROM gi_games g 
        LEFT JOIN gi_categories c ON g.category_id = c.category_id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (g.title LIKE ? OR g.developer LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($selected_category)) {
    $sql .= " AND g.category_id = ?";
    $params[] = $selected_category;
}

$sql .= " ORDER BY g.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $games = $stmt->fetchAll();
} catch (PDOException $e) {
    $games = [];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คลังเกมและหมวดหมู่ - GameInsight</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #0f172a;
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 { margin: 0; font-size: 1.5rem; }
        .header-links {
            display: flex;
            align-items: center;
        }
        .header-links a {
            color: #94a3b8;
            text-decoration: none;
            margin-left: 1rem;
        }
        .header-links a:hover { color: white; }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-section input[type="text"], .filter-section select {
            padding: 0.6rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.95rem;
            flex: 1;
            min-width: 200px;
        }
        .btn-search {
            padding: 0.6rem 1.5rem;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.95rem;
        }
        .btn-search:hover { background-color: #1d4ed8; }
        .btn-reset {
            padding: 0.6rem 1rem;
            background-color: #e2e8f0;
            color: #475569;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }
        .btn-add-game {
            background-color: #10b981;
            color: white;
            padding: 0.6rem 1.25rem;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
            transition: background-color 0.2s;
        }
        .btn-add-game:hover {
            background-color: #059669;
        }
        .game-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.5rem;
        }
        .game-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        .game-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background-color: #cbd5e1;
        }
        .card-body {
            padding: 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .badge {
            display: inline-block;
            background-color: #e0f2fe;
            color: #0369a1;
            font-size: 0.8rem;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            align-self: flex-start;
            margin-bottom: 0.5rem;
        }
        .game-title {
            margin: 0 0 0.5rem 0;
            font-size: 1.15rem;
            color: #0f172a;
        }
        .developer {
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }
        .card-actions {
            margin-top: auto;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .btn-review {
            flex: 1;
            text-align: center;
            padding: 0.5rem;
            background-color: #0ea5e9;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        .btn-store {
            flex: 1;
            text-align: center;
            padding: 0.5rem;
            background-color: #334155;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        .btn-delete {
            background-color: #ef4444;
            color: white;
            text-align: center;
            padding: 0.5rem 0.8rem;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .btn-delete:hover {
            background-color: #dc2626;
        }
        .no-data {
            text-align: center;
            color: #64748b;
            grid-column: 1 / -1;
            padding: 3rem;
            background: white;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<header>
    <h1>GameInsight</h1>
    <div class="header-links">
        <a href="games.php">หน้ารายการเกม</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span style="color: #38bdf8; margin-left: 1rem;">👤 <?= htmlspecialchars($_SESSION['username'] ?? 'สมาชิก') ?></span>

            <!-- แสดงป้ายแอดมินสำหรับบทบาท admin -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <span style="background-color: #ef4444; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; margin-left: 6px;">แอดมิน</span>
            <?php endif; ?>

            <a href="logout.php" style="color: #f87171;">ออกจากระบบ</a>
        <?php else: ?>
            <a href="login.php">เข้าสู่ระบบ</a>
            <a href="register.php">สมัครสมาชิก</a>
        <?php endif; ?>
        <a href="terms.php">ข้อกำหนด</a>
    </div>
</header>

<div class="container">
    <div class="filter-section">
        <form method="GET" action="games.php" style="display: flex; gap: 1rem; flex: 1; flex-wrap: wrap; align-items: center;">
            <input type="text" name="search" placeholder="ค้นหาชื่อเกม หรือผู้พัฒนา..." value="<?= htmlspecialchars($search) ?>">
            
            <select name="category">
                <option value="">-- ทุกหมวดหมู่ --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= $selected_category == $cat['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-search">ค้นหา</button>
            <?php if (!empty($search) || !empty($selected_category)): ?>
                <a href="games.php" class="btn-reset">ล้างค่า</a>
            <?php endif; ?>
        </form>

        <!-- ปุ่มเพิ่มเกมใหม่สำหรับผู้ใช้ที่เข้าสู่ระบบแล้ว -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="add_game.php" class="btn-add-game">
                ➕ เพิ่มเกมและโพสต์รีวิว
            </a>
        <?php endif; ?>
    </div>

    <div class="game-grid">
        <?php if (!empty($games)): ?>
            <?php foreach ($games as $game): ?>
                <div class="game-card">
                    <img src="<?= htmlspecialchars($game['cover_image'] ?: 'https://via.placeholder.com/300x180?text=No+Cover') ?>" alt="<?= htmlspecialchars($game['title']) ?>">
                    <div class="card-body">
                        <span class="badge"><?= htmlspecialchars($game['category_name'] ?: 'ทั่วไป') ?></span>
                        <h3 class="game-title"><?= htmlspecialchars($game['title']) ?></h3>
                        <div class="developer">ผู้พัฒนา: <?= htmlspecialchars($game['developer'] ?: 'ไม่ระบุ') ?></div>
                        
                        <div class="card-actions">
                            <a href="game_detail.php?game_id=<?= $game['game_id'] ?>" class="btn-review">ดูรีวิวเกมนี้</a>
                            <?php if (!empty($game['store_url'])): ?>
                                <a href="<?= htmlspecialchars($game['store_url']) ?>" target="_blank" class="btn-store">ร้านค้า</a>
                            <?php endif; ?>

                            <!-- ปุ่มลบเกม แสดงเฉพาะแอดมิน -->
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <a href="delete_game.php?game_id=<?= $game['game_id'] ?>" 
                                   onclick="return confirm('คุณเป็นแอดมิน ยืนยันที่จะลบเกมนี้ออกจากระบบหรือไม่?')" 
                                   class="btn-delete">
                                   ลบโพสต์
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-data">
                <p>ยังไม่มีข้อมูลเกมในระบบ หรือไม่พบเกมที่ค้นหา</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>