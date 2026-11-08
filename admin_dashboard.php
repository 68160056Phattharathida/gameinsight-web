<?php
session_start();
require_once 'config/db.php';

// ตรวจสอบสิทธิ์: ต้องล็อกอินและมีบทบาทเป็น admin เท่านั้น
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: games.php");
    exit();
}

// 1. ดึงสถิติภาพรวม
$stat_games = $pdo->query("SELECT COUNT(*) FROM gi_games")->fetchColumn();
$stat_users = $pdo->query("SELECT COUNT(*) FROM gi_users")->fetchColumn();
$stat_reviews = $pdo->query("SELECT COUNT(*) FROM gi_reviews")->fetchColumn();
$stat_comments = $pdo->query("SELECT COUNT(*) FROM gi_comments")->fetchColumn();

// 2. ดึงรายการเกมล่าสุด 10 รายการ
$games_stmt = $pdo->query("
    SELECT g.game_id, g.title, g.developer, c.category_name, g.created_at 
    FROM gi_games g 
    LEFT JOIN gi_categories c ON g.category_id = c.category_id 
    ORDER BY g.created_at DESC LIMIT 10
");
$recent_games = $games_stmt->fetchAll();

// 3. ดึงรายชื่อสมาชิกล่าสุด 10 คน
$users_stmt = $pdo->query("SELECT user_id, username, email, role, created_at FROM gi_users ORDER BY created_at DESC LIMIT 10");
$recent_users = $users_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการหลังบ้าน - ViewG [ADMIN CONSOLE]</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Prompt:wght@300;400;600;700&display=swap" rel="stylesheet">
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
            border-bottom: 4px solid var(--neon-pink);
            box-shadow: 0 4px 0px #000;
            padding: 16px 24px;
        }

        .nav-container {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 18px;
            color: var(--neon-pink);
            text-decoration: none;
            text-shadow: 2px 2px 0px #000;
        }

        .pixel-btn {
            display: inline-block;
            font-family: 'Press Start 2P', monospace;
            font-size: 9px;
            padding: 8px 12px;
            color: #000;
            background-color: var(--neon-green);
            border: 2px solid #fff;
            box-shadow: 3px 3px 0px #000;
            text-decoration: none;
            cursor: pointer;
        }

        .pixel-btn:hover {
            transform: translate(2px, 2px);
            box-shadow: 1px 1px 0px #000;
            background-color: var(--neon-yellow);
        }

        .pixel-btn-pink { background-color: var(--neon-pink); color: #fff; }
        .pixel-btn-cyan { background-color: var(--neon-cyan); color: #000; }

        .container { max-width: 1100px; margin: 30px auto; padding: 0 16px; }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--panel-bg);
            border: 3px solid #fff;
            box-shadow: 5px 5px 0px #000;
            padding: 20px;
            text-align: center;
        }

        .stat-num {
            font-size: 26px;
            font-weight: bold;
            color: var(--neon-cyan);
            margin: 10px 0 0 0;
        }

        /* Panel & Tables */
        .admin-panel {
            background: var(--panel-bg);
            border: 3px solid #fff;
            box-shadow: 6px 6px 0px #000;
            padding: 20px;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-top: 14px;
        }

        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 2px solid #222b40;
        }

        th {
            background: #0b0e17;
            color: var(--neon-cyan);
            font-family: 'Press Start 2P', monospace;
            font-size: 9px;
        }

        tr:hover { background: rgba(0, 240, 255, 0.03); }

        .role-badge {
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            border: 1px solid #fff;
        }
    </style>
</head>
<body>

    <nav>
        <div class="nav-container">
            <a href="admin_dashboard.php" class="logo pixel-text">🛠️ ViewG [ADMIN CONSOLE]</a>
            <div style="display: flex; gap: 12px; align-items: center;">
                <a href="games.php" class="pixel-btn pixel-btn-cyan">← หน้ารายการเกม</a>
                <a href="logout.php" class="pixel-btn pixel-btn-pink">ออก</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Dashboard Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="pixel-text" style="font-size: 10px; color: #94a3b8;">TOTAL GAMES</span>
                <div class="stat-num pixel-text"><?= (int)$stat_games ?></div>
            </div>
            <div class="stat-card">
                <span class="pixel-text" style="font-size: 10px; color: #94a3b8;">TOTAL USERS</span>
                <div class="stat-num pixel-text" style="color: var(--neon-yellow);"><?= (int)$stat_users ?></div>
            </div>
            <div class="stat-card">
                <span class="pixel-text" style="font-size: 10px; color: #94a3b8;">TOTAL REVIEWS</span>
                <div class="stat-num pixel-text" style="color: var(--neon-green);"><?= (int)$stat_reviews ?></div>
            </div>
            <div class="stat-card">
                <span class="pixel-text" style="font-size: 10px; color: #94a3b8;">COMMENTS</span>
                <div class="stat-num pixel-text" style="color: var(--neon-pink);"><?= (int)$stat_comments ?></div>
            </div>
        </div>

        <!-- Manage Recent Games -->
        <div class="admin-panel">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="pixel-text" style="font-size: 12px; color: var(--neon-cyan); margin: 0;">🎮 รายการเกมในระบบ (ล่าสุด)</h3>
                <a href="add_game.php" class="pixel-btn" style="background: var(--neon-yellow);">+ เพิ่มเกมใหม่</a>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ชื่อเกม</th>
                            <th>ผู้พัฒนา</th>
                            <th>หมวดหมู่</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_games as $g): ?>
                            <tr>
                                <td><?= $g['game_id'] ?></td>
                                <td><strong><?= htmlspecialchars($g['title']) ?></strong></td>
                                <td><?= htmlspecialchars($g['developer'] ?: '-') ?></td>
                                <td><span style="color: var(--neon-cyan);"><?= htmlspecialchars($g['category_name'] ?: 'ETC') ?></span></td>
                                <td>
                                    <a href="game_detail.php?game_id=<?= $g['game_id'] ?>" style="color: var(--neon-green); text-decoration: none; margin-right: 8px;">ดู</a>
                                    <a href="delete_game.php?game_id=<?= $g['game_id'] ?>" 
                                       onclick="return confirm('ยืนยันลบเกมนี้ออกจากฐานข้อมูล?')" 
                                       style="color: var(--neon-pink); text-decoration: none;">ลบ</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Manage Recent Users -->
        <div class="admin-panel">
            <h3 class="pixel-text" style="font-size: 12px; color: var(--neon-yellow); margin: 0;">👤 รายชื่อสมาชิกในระบบ</h3>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>USER ID</th>
                            <th>ชื่อผู้ใช้</th>
                            <th>อีเมล</th>
                            <th>สถานะบทบาท</th>
                            <th>วันที่สมัคร</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_users as $u): ?>
                            <tr>
                                <td><?= $u['user_id'] ?></td>
                                <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                                <td><?= htmlspecialchars($u['email'] ?: '-') ?></td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="role-badge" style="background: var(--neon-pink); color: #fff;">ADMIN</span>
                                    <?php else: ?>
                                        <span class="role-badge" style="background: #334155; color: #fff;">MEMBER</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: #64748b;"><?= htmlspecialchars($u['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>