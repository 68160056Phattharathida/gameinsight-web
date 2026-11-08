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
    <title>คลังเกมและหมวดหมู่ - ViewG [8-BIT ARCADE]</title>
    <!-- Pixel Font (Press Start 2P) และ Prompt คมชัด -->
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Prompt:wght@400;600;700&display=swap" rel="stylesheet">
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
            /* ลายตารางจำลองหน้าจอเกม CRT */
            background-image: 
                linear-gradient(rgba(0, 240, 255, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 240, 255, 0.04) 1px, transparent 1px);
            background-size: 20px 20px;
            color: var(--text-main);
            font-family: 'Prompt', sans-serif;
        }

        .pixel-font {
            font-family: 'Press Start 2P', monospace;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px 16px;
        }

        /* Navbar */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: var(--panel-bg);
            border: 4px solid var(--neon-cyan);
            box-shadow: 6px 6px 0px #000;
            margin-bottom: 24px;
        }

        .logo {
            font-size: 20px;
            color: var(--neon-cyan);
            text-shadow: 3px 3px 0px #000;
            text-decoration: none;
        }

        .header-links {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .header-links a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
        }

        .header-links a:hover {
            color: var(--neon-cyan);
        }

        /* 8-bit Buttons */
        .pixel-btn {
            display: inline-block;
            font-family: 'Press Start 2P', monospace;
            font-size: 10px;
            padding: 9px 14px;
            color: #000;
            background-color: var(--neon-green);
            border: 3px solid #ffffff;
            box-shadow: 4px 4px 0px #000;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.08s, box-shadow 0.08s;
            text-align: center;
        }

        .pixel-btn:hover {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0px #000;
            background-color: var(--neon-yellow);
        }

        .pixel-btn-danger {
            background-color: var(--neon-pink);
            color: #fff;
        }

        .pixel-btn-danger:hover {
            background-color: #ff3344;
            color: #fff;
        }

        .badge-admin {
            background: var(--neon-pink);
            color: #fff;
            padding: 3px 6px;
            font-size: 10px;
            border: 2px solid #fff;
            font-weight: bold;
        }

        /* Filter Panel */
        .filter-section {
            background: var(--panel-bg);
            border: 3px solid #fff;
            box-shadow: 5px 5px 0px #000;
            padding: 16px;
            margin-bottom: 28px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        input[type="text"], select {
            background: #080a0f;
            border: 3px solid #fff;
            color: #fff;
            padding: 9px 12px;
            font-family: 'Prompt', sans-serif;
            font-size: 14px;
            outline: none;
        }

        input[type="text"]:focus, select:focus {
            border-color: var(--neon-cyan);
        }

        /* Game Grid */
        .game-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 22px;
        }

        .game-card {
            background: var(--panel-bg);
            border: 3px solid #fff;
            box-shadow: 6px 6px 0px #000;
            display: flex;
            flex-direction: column;
            transition: transform 0.12s, box-shadow 0.12s, border-color 0.12s;
        }

        .game-card:hover {
            transform: translate(-3px, -3px);
            box-shadow: 9px 9px 0px var(--neon-cyan);
            border-color: var(--neon-cyan);
        }

        .cover-wrapper {
            position: relative;
            width: 100%;
            height: 175px;
            background: #000;
            border-bottom: 3px solid #fff;
            overflow: hidden;
        }

        .cover-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .badge-cat {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #000;
            color: var(--neon-cyan);
            border: 2px solid var(--neon-cyan);
            padding: 3px 6px;
            font-size: 9px;
            font-family: 'Press Start 2P', monospace;
        }

        .card-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .game-title {
            margin: 0 0 6px 0;
            font-size: 17px;
            color: #fff;
        }

        .developer {
            color: #94a3b8;
            font-size: 13px;
            margin-bottom: 14px;
        }

        .card-actions {
            margin-top: auto;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .no-data {
            grid-column: 1 / -1;
            text-align: center;
            padding: 48px;
            background: var(--panel-bg);
            border: 3px dashed #334155;
        }
    </style>
</head>
<body>

<header>
    <a href="games.php" class="logo pixel-font">🎮 ViewG</a>
    <div class="header-links">
        <a href="games.php">หน้ารายการเกม</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span style="color: var(--neon-cyan); font-weight: 600;">👤 <?= htmlspecialchars($_SESSION['username'] ?? 'สมาชิก') ?></span>

            <!-- แสดงป้ายแอดมินสำหรับบทบาท admin -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <span class="badge-admin">ADMIN</span>
            <?php endif; ?>

            <a href="logout.php" class="pixel-btn pixel-btn-danger" style="padding: 6px 10px;">ออก</a>
        <?php else: ?>
            <a href="login.php" class="pixel-btn" style="background: var(--neon-green);">เข้าสู่ระบบ</a>
            <a href="register.php" class="pixel-btn" style="background: var(--neon-cyan);">สมัครสมาชิก</a>
        <?php endif; ?>
        <a href="terms.php" style="color: #64748b; font-size: 13px;">ข้อกำหนด</a>
    </div>
</header>

<div class="container">
    <div class="filter-section">
        <form method="GET" action="games.php" style="display: flex; gap: 10px; flex: 1; flex-wrap: wrap; align-items: center;">
            <input type="text" name="search" placeholder="ค้นหาชื่อเกม หรือผู้พัฒนา..." value="<?= htmlspecialchars($search) ?>" style="flex: 2; min-width: 200px;">
            
            <select name="category" style="flex: 1; min-width: 170px;">
                <option value="">🕹️ ทุกหมวดหมู่</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= $selected_category == $cat['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="pixel-btn">SEARCH</button>
            <?php if (!empty($search) || !empty($selected_category)): ?>
                <a href="games.php" class="pixel-btn" style="background: #334155; color: #fff;">RESET</a>
            <?php endif; ?>
        </form>

        <!-- ปุ่มเพิ่มเกมใหม่สำหรับผู้ใช้ที่เข้าสู่ระบบแล้ว -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="add_game.php" class="pixel-btn" style="background: var(--neon-yellow); color: #000;">
                ➕ เพิ่มเกม/รีวิว
            </a>
        <?php endif; ?>
    </div>

    <div class="game-grid">
        <?php if (!empty($games)): ?>
            <?php foreach ($games as $game): ?>
                <div class="game-card">
                    <div class="cover-wrapper">
                        <img src="<?= htmlspecialchars($game['cover_image'] ?: 'https://via.placeholder.com/300x180?text=No+Cover') ?>" alt="<?= htmlspecialchars($game['title']) ?>">
                        <span class="badge-cat"><?= htmlspecialchars($game['category_name'] ?: 'ETC') ?></span>
                    </div>
                    <div class="card-body">
                        <h3 class="game-title"><?= htmlspecialchars($game['title']) ?></h3>
                        <div class="developer">Dev: <?= htmlspecialchars($game['developer'] ?: 'ไม่ระบุ') ?></div>
                        
                        <div class="card-actions">
                            <a href="game_detail.php?game_id=<?= $game['game_id'] ?>" class="pixel-btn" style="background: var(--neon-cyan); flex: 1;">รีวิว</a>
                            
                            <?php if (!empty($game['store_url'])): ?>
                                <a href="<?= htmlspecialchars($game['store_url']) ?>" target="_blank" class="pixel-btn" style="background: #27272a; color: #fff; flex: 1;">ร้านค้า ↗</a>
                            <?php endif; ?>

                            <!-- ปุ่มลบเกม แสดงเฉพาะแอดมิน -->
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <a href="delete_game.php?game_id=<?= $game['game_id'] ?>" 
                                   onclick="return confirm('คุณเป็นแอดมิน ยืนยันที่จะลบเกมนี้ออกจาก ViewG หรือไม่?')" 
                                   class="pixel-btn pixel-btn-danger">
                                   ลบ
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-data">
                <p class="pixel-font" style="color: var(--neon-pink); font-size: 13px;">[ NO GAMES FOUND ]</p>
                <p>ยังไม่มีข้อมูลเกมในระบบ หรือไม่พบเกมที่ค้นหา</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>