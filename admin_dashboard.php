<?php
session_start();
require_once 'config/db.php';

// ตรวจสอบสิทธิ์: ต้องล็อกอินและมีบทบาทเป็น admin เท่านั้น
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: games.php");
    exit();
}

// ระบบจัดการแอ็กชันของรายงาน (ถ้ามีการกดเคลียร์รายงาน)
if (isset($_GET['dismiss_report'])) {
    $dismiss_id = (int)$_GET['dismiss_report'];
    $stmt = $pdo->prepare("UPDATE gi_reports SET status = 'resolved' WHERE report_id = ?");
    $stmt->execute([$dismiss_id]);
    header("Location: admin_dashboard.php");
    exit();
}

// 1. ดึงสถิติภาพรวม
$stat_games = $pdo->query("SELECT COUNT(*) FROM gi_games")->fetchColumn();
$stat_users = $pdo->query("SELECT COUNT(*) FROM gi_users")->fetchColumn();
$stat_reviews = $pdo->query("SELECT COUNT(*) FROM gi_reviews")->fetchColumn();
$stat_comments = $pdo->query("SELECT COUNT(*) FROM gi_comments")->fetchColumn();
$stat_reports = $pdo->query("SELECT COUNT(*) FROM gi_reports WHERE status = 'pending'")->fetchColumn();

// 2. ดึงรายการแจ้งรายงานที่รอดำเนินการ (Pending Reports)
$reports_stmt = $pdo->query("
    SELECT rep.report_id, rep.target_id, rep.reason, rep.created_at, 
           u.username AS reporter_name,
           r.title AS review_title, r.content_pros, r.content_cons,
           g.title AS game_title
    FROM gi_reports rep
    LEFT JOIN gi_users u ON rep.user_id = u.user_id
    LEFT JOIN gi_reviews r ON rep.target_id = r.review_id
    LEFT JOIN gi_games g ON r.game_id = g.game_id
    WHERE rep.status = 'pending'
    ORDER BY rep.created_at DESC
");
$pending_reports = $reports_stmt->fetchAll();

// 3. ดึงรายการเกมล่าสุด 10 รายการ
$games_stmt = $pdo->query("
    SELECT g.game_id, g.title, g.developer, c.category_name, g.created_at 
    FROM gi_games g 
    LEFT JOIN gi_categories c ON g.category_id = c.category_id 
    ORDER BY g.created_at DESC LIMIT 10
");
$recent_games = $games_stmt->fetchAll();

// 4. ดึงรายชื่อสมาชิกล่าสุด 10 คน
$users_stmt = $pdo->query("SELECT user_id, username, email, role, created_at FROM gi_users ORDER BY created_at DESC LIMIT 10");
$recent_users = $users_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการหลังบ้าน - ViewG [ADMIN CONSOLE]</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
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

        /* ปรับปุ่มให้ขนาดตัวอักษรอ่านสบายตา สมส่วนกับปุ่ม */
        .pixel-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: 'Prompt', sans-serif;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 16px;
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

        .pixel-btn-pink { background-color: var(--neon-pink); color: #fff; }
        .pixel-btn-pink:hover { color: #fff; }
        .pixel-btn-cyan { background-color: var(--neon-cyan); color: #000; }
        .pixel-btn-yellow { background-color: var(--neon-yellow); color: #000; }

        .container { max-width: 1100px; margin: 30px auto; padding: 0 16px; }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--panel-bg);
            border: 3px solid #fff;
            box-shadow: 5px 5px 0px #000;
            padding: 18px;
            text-align: center;
        }

        .stat-num {
            font-size: 26px;
            font-weight: bold;
            color: var(--neon-cyan);
            margin: 8px 0 0 0;
        }

        /* Panel & Tables */
        .admin-panel {
            background: var(--panel-bg);
            border: 3px solid #fff;
            box-shadow: 6px 6px 0px #000;
            padding: 22px;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 14px;
        }

        th, td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 2px solid #222b40;
        }

        /* หัวตารางปรับฟอนต์ Prompt ชัดเจน ขนาดพอดี */
        th {
            background: #0b0e17;
            color: var(--neon-cyan);
            font-family: 'Prompt', sans-serif;
            font-size: 13px;
            font-weight: 600;
        }

        tr:hover { background: rgba(0, 240, 255, 0.03); }

        .role-badge {
            padding: 3px 8px;
            font-size: 11px;
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
                <span class="pixel-text" style="font-size: 9px; color: #94a3b8;">TOTAL GAMES</span>
                <div class="stat-num pixel-text"><?= (int)$stat_games ?></div>
            </div>
            <div class="stat-card">
                <span class="pixel-text" style="font-size: 9px; color: #94a3b8;">TOTAL USERS</span>
                <div class="stat-num pixel-text" style="color: var(--neon-yellow);"><?= (int)$stat_users ?></div>
            </div>
            <div class="stat-card">
                <span class="pixel-text" style="font-size: 9px; color: #94a3b8;">TOTAL REVIEWS</span>
                <div class="stat-num pixel-text" style="color: var(--neon-green);"><?= (int)$stat_reviews ?></div>
            </div>
            <div class="stat-card">
                <span class="pixel-text" style="font-size: 9px; color: #94a3b8;">COMMENTS</span>
                <div class="stat-num pixel-text" style="color: var(--neon-pink);"><?= (int)$stat_comments ?></div>
            </div>
            <div class="stat-card" style="border-color: var(--neon-pink);">
                <span class="pixel-text" style="font-size: 9px; color: var(--neon-pink);">REPORTS</span>
                <div class="stat-num pixel-text" style="color: #ff3344;"><?= (int)$stat_reports ?></div>
            </div>
        </div>

        <!-- 📌 1. ตารางจัดการรายงานความไม่เหมาะสม (REPORTS QUEUE) -->
        <div class="admin-panel" style="border-color: var(--neon-pink);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 16px; color: var(--neon-pink); margin: 0; font-weight: 700;">
                    🚨 รายการแจ้งรายงานความไม่เหมาะสม (รอตรวจสอบ <?= count($pending_reports) ?> รายการ)
                </h3>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>เกมที่ถูกร้องเรียน</th>
                            <th>หัวข้อรีวิว</th>
                            <th>เหตุผลที่แจ้ง</th>
                            <th>ผู้แจ้ง</th>
                            <th>วันเวลา</th>
                            <th>การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pending_reports)): ?>
                            <?php foreach ($pending_reports as $rep): ?>
                                <tr>
                                    <td><strong style="color: var(--neon-cyan);"><?= htmlspecialchars($rep['game_title'] ?: 'รีวิวถูกลบไปแล้ว') ?></strong></td>
                                    <td><?= htmlspecialchars($rep['review_title'] ?: '-') ?></td>
                                    <td><span style="color: #fca5a5;"><?= htmlspecialchars($rep['reason']) ?></span></td>
                                    <td><?= htmlspecialchars($rep['reporter_name'] ?: 'ไม่ระบุ') ?></td>
                                    <td style="color: #64748b; font-size: 12px;"><?= htmlspecialchars($rep['created_at']) ?></td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <?php if (!empty($rep['target_id'])): ?>
                                                <a href="delete_review.php?id=<?= $rep['target_id'] ?>" 
                                                   onclick="return confirm('ยืนยันลบรีวิวนี้ออกจากระบบถาวรหรือไม่?')" 
                                                   class="pixel-btn pixel-btn-pink" style="padding: 4px 8px; font-size: 11px;">
                                                   ลบรีวิวทิ้ง
                                                </a>
                                            <?php endif; ?>
                                            <a href="admin_dashboard.php?dismiss_report=<?= $rep['report_id'] ?>" 
                                               class="pixel-btn" style="background: #334155; color: #fff; padding: 4px 8px; font-size: 11px;">
                                               เพิกเฉย
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #64748b; padding: 24px;">
                                    ✨ เยี่ยมมาก! ยังไม่มีรายงานความไม่เหมาะสมค้างในระบบ
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. ตารางจัดการเกม -->
        <div class="admin-panel">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 16px; color: var(--neon-cyan); margin: 0; font-weight: 700;">🎮 รายการเกมในระบบ (ล่าสุด)</h3>
                <a href="add_game.php" class="pixel-btn pixel-btn-yellow">+ เพิ่มเกมใหม่</a>
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
                                    <a href="game_detail.php?game_id=<?= $g['game_id'] ?>" style="color: var(--neon-green); text-decoration: none; margin-right: 12px; font-weight: 600;">ดู</a>
                                    <a href="delete_game.php?game_id=<?= $g['game_id'] ?>" 
                                       onclick="return confirm('ยืนยันลบเกมนี้ออกจากฐานข้อมูล?')" 
                                       style="color: var(--neon-pink); text-decoration: none; font-weight: 600;">ลบ</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. ตารางจัดการสมาชิก -->
        <div class="admin-panel">
            <h3 style="font-size: 16px; color: var(--neon-yellow); margin: 0; font-weight: 700;">👤 รายชื่อสมาชิกในระบบ</h3>
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