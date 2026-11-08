<?php
session_start();
require_once __DIR__ . '/config/db.php';

// รับ game_id จาก URL
$game_id = isset($_GET['game_id']) ? (int) $_GET['game_id'] : 1;

// ดึงข้อมูลเกมและหมวดหมู่
$stmt = $pdo->prepare("
    SELECT g.*, c.category_name
    FROM gi_games g
    LEFT JOIN gi_categories c
        ON g.category_id = c.category_id
    WHERE g.game_id = ?
");
$stmt->execute([$game_id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    http_response_code(404);
    die("ไม่พบข้อมูลเกมนี้ในระบบ ViewG");
}

// ดึงรีวิวพร้อมชื่อผู้ใช้
$review_stmt = $pdo->prepare("
    SELECT r.*, u.username
    FROM gi_reviews r
    JOIN gi_users u ON r.user_id = u.user_id
    WHERE r.game_id = ?
    ORDER BY r.created_at DESC
");
$review_stmt->execute([$game_id]);
$reviews = $review_stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงความคิดเห็นทั้งหมดของรีวิว (ทั้งข้อความหลักและตอบกลับ)
$comment_stmt = $pdo->prepare("
    SELECT c.*, u.username
    FROM gi_comments c
    JOIN gi_users u ON c.user_id = u.user_id
    WHERE c.review_id = ?
    ORDER BY c.created_at ASC
");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ViewG - <?= htmlspecialchars($game['title']) ?> [8-BIT ARCADE]</title>
    <!-- Pixel Font สำหรับหัวข้อ/ปุ่ม และ Prompt สำหรับภาษาไทย -->
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
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 20px;
            color: var(--neon-cyan);
            text-decoration: none;
            text-shadow: 2px 2px 0px #000;
        }

        .nav-links { display: flex; align-items: center; gap: 14px; }
        .nav-links a { color: #94a3b8; text-decoration: none; font-size: 14px; }
        .nav-links a:hover { color: var(--neon-cyan); }

        /* Pixel Buttons */
        .pixel-btn {
            display: inline-block;
            font-family: 'Press Start 2P', monospace;
            font-size: 10px;
            padding: 8px 12px;
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
        .pixel-btn-pink:hover { background-color: #ff3344; color: #fff; }

        .pixel-btn-yellow { background-color: var(--neon-yellow); color: #000; }
        .pixel-btn-yellow:hover { background-color: #ffd700; color: #000; }

        /* Main Container & Pixel Panels */
        main { max-width: 900px; margin: 30px auto; padding: 0 16px; }

        .pixel-card {
            background: var(--panel-bg);
            border: 3px solid #fff;
            box-shadow: 6px 6px 0px #000;
            padding: 24px;
            margin-bottom: 24px;
        }

        /* Hero Info */
        .game-hero {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        @media (min-width: 768px) {
            .game-hero { flex-direction: row; }
        }

        .game-cover {
            width: 100%;
            max-width: 280px;
            height: 180px;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 4px 4px 0px #000;
        }

        .category-tag {
            background: #000;
            color: var(--neon-cyan);
            border: 2px solid var(--neon-cyan);
            padding: 3px 8px;
            font-size: 9px;
            font-family: 'Press Start 2P', monospace;
            display: inline-block;
        }

        /* Pros / Cons */
        .grid-pros-cons {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            margin: 16px 0;
        }
        @media (min-width: 640px) {
            .grid-pros-cons { grid-template-columns: 1fr 1fr; }
        }

        .box-pros {
            background: #062312;
            border: 2px solid var(--neon-green);
            box-shadow: 4px 4px 0px #000;
            padding: 14px;
        }

        .box-cons {
            background: #2b0813;
            border: 2px solid var(--neon-pink);
            box-shadow: 4px 4px 0px #000;
            padding: 14px;
        }

        /* Comments Layout */
        .comment-item {
            background: #0b0e17;
            border: 2px solid #334155;
            padding: 14px;
            margin-bottom: 12px;
        }

        .comment-reply {
            margin-left: 28px;
            border-left: 3px solid var(--neon-cyan);
            background: #111524;
            padding: 10px 14px;
            margin-top: 8px;
            margin-bottom: 8px;
        }

        textarea {
            width: 100%;
            background: #080a0f;
            border: 2px solid #fff;
            color: #fff;
            padding: 10px;
            font-family: 'Prompt', sans-serif;
            outline: none;
            resize: vertical;
        }
        textarea:focus { border-color: var(--neon-cyan); }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav>
        <div class="nav-container">
            <a href="games.php" class="logo pixel-text">🎮 ViewG</a>
            <div class="nav-links">
                <a href="games.php">หน้ารายการเกม</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span style="color: var(--neon-cyan);">👤 <?= htmlspecialchars($_SESSION['username'] ?? 'ผู้ใช้') ?></span>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <span class="pixel-text" style="background: var(--neon-pink); color: #fff; padding: 2px 6px; font-size: 8px;">ADMIN</span>
                        <a href="admin_dashboard.php" style="color: var(--neon-yellow); font-size: 13px;">จัดการหลังบ้าน</a>
                    <?php endif; ?>
                    <a href="logout.php" class="pixel-btn pixel-btn-pink" style="padding: 4px 8px;">ออก</a>
                <?php else: ?>
                    <a href="login.php" class="pixel-btn">เข้าสู่ระบบ</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <!-- Game Hero Card -->
        <div class="pixel-card game-hero">
            <img src="<?= htmlspecialchars($game['cover_image'] ?: 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=600&auto=format&fit=crop&q=80') ?>" 
                 alt="Cover" class="game-cover">
            <div style="flex: 1;">
                <span class="category-tag"><?= htmlspecialchars($game['category_name'] ?? 'ETC') ?></span>
                <h1 style="margin: 8px 0; font-size: 26px; color: #fff;"><?= htmlspecialchars($game['title']) ?></h1>
                <p style="color: #94a3b8; font-size: 14px; margin: 0 0 16px 0;">Dev: <?= htmlspecialchars($game['developer'] ?? 'ไม่ระบุ') ?></p>

                <?php if (!empty($game['store_url'])): ?>
                    <a href="<?= htmlspecialchars($game['store_url']) ?>" target="_blank" class="pixel-btn" style="background: var(--neon-cyan);">
                        STORE PAGE ↗
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Review Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 class="pixel-text" style="font-size: 14px; color: var(--neon-cyan); margin: 0;">
                REVIEWS (<?= count($reviews) ?>)
            </h2>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="add_review.php?game_id=<?= $game_id ?>" class="pixel-btn" style="background: var(--neon-yellow);">
                    ✍️ เขียนรีวิวเกมนี้
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($reviews)): ?>
            <div class="pixel-card" style="text-align: center; border-style: dashed;">
                <p class="pixel-text" style="color: var(--neon-pink); font-size: 11px;">[ NO REVIEWS YET ]</p>
                <p style="color: #94a3b8; font-size: 14px;">มาร่วมเป็นคนแรกที่รีวิวเกมนี้ใน ViewG กันเถอะ!</p>
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="pixel-card">
                    <!-- Review Top Bar -->
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px dashed #334155; padding-bottom: 12px; margin-bottom: 14px;">
                        <div>
                            <strong style="color: var(--neon-cyan); font-size: 16px;"><?= htmlspecialchars($review['username']) ?></strong>
                            <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($review['created_at']) ?></div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="pixel-text" style="color: var(--neon-yellow); font-size: 11px;">
                                ★ <?= number_format((float) $review['rating'], 1) ?>/5.0
                            </span>

                            <!-- ปุ่มรายงานรีวิว (แสดงเฉพาะเมื่อเข้าสู่ระบบแล้ว) -->
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="report_review.php?review_id=<?= $review['review_id'] ?>" 
                                   class="pixel-btn pixel-btn-yellow" style="padding: 4px 6px; font-size: 9px;"
                                   onclick="return confirm('ต้องการรายงานรีวิวนี้ว่าไม่เหมาะสมใช่หรือไม่?');">
                                    🚨 REPORT
                                </a>
                            <?php endif; ?>

                            <!-- ปุ่มลบรีวิว (สำหรับเจ้าของโพสต์หรือ Admin) -->
                            <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $review['user_id'] || ($_SESSION['role'] ?? '') === 'admin')): ?>
                                <a href="delete_review.php?id=<?= $review['review_id'] ?>" 
                                   onclick="return confirm('ยืนยันลบรีวิวนี้หรือไม่?');" 
                                   class="pixel-btn pixel-btn-pink" style="padding: 4px 6px; font-size: 9px;">DEL</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #fff;"><?= htmlspecialchars($review['title']) ?></h3>

                    <?php if (!empty($review['review_image'])): ?>
                        <img src="<?= htmlspecialchars($review['review_image']) ?>" alt="Review Image" 
                             style="max-width: 100%; max-height: 320px; object-fit: contain; border: 2px solid #fff; margin-bottom: 14px;">
                    <?php endif; ?>

                    <!-- Pros / Cons -->
                    <div class="grid-pros-cons">
                        <div class="box-pros">
                            <h5 style="margin: 0 0 6px 0; color: var(--neon-green); font-size: 13px;">✅ ข้อดี (PROS)</h5>
                            <p style="margin: 0; font-size: 13px; color: #a7f3d0; whitespace-pre-line;"><?= htmlspecialchars($review['content_pros'] ?? '-') ?></p>
                        </div>
                        <div class="box-cons">
                            <h5 style="margin: 0 0 6px 0; color: var(--neon-pink); font-size: 13px;">❌ ข้อเสีย (CONS)</h5>
                            <p style="margin: 0; font-size: 13px; color: #fecdd3; whitespace-pre-line;"><?= htmlspecialchars($review['content_cons'] ?? '-') ?></p>
                        </div>
                    </div>

                    <!-- Q&A Discussions -->
                    <div style="margin-top: 24px; border-top: 2px solid #334155; padding-top: 16px;">
                        <h4 class="pixel-text" style="font-size: 11px; color: var(--neon-cyan); margin: 0 0 14px 0;">
                            💬 Q&A DISCUSSIONS
                        </h4>

                        <!-- ฟอร์มตั้งคำถามใหม่ (parent_id ว่าง) -->
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form action="submit_comment.php" method="POST" style="margin-bottom: 16px;">
                                <input type="hidden" name="review_id" value="<?= (int) $review['review_id'] ?>">
                                <input type="hidden" name="game_id" value="<?= (int) $game_id ?>">
                                <input type="hidden" name="parent_id" value="">
                                <textarea name="comment" rows="2" required placeholder="สอบถามผู้รีวิวเกี่ยวกับเกมนี้..."></textarea>
                                <div style="text-align: right; margin-top: 6px;">
                                    <button type="submit" class="pixel-btn">SEND COMMENT</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
                                <a href="login.php" style="color: var(--neon-cyan);">เข้าสู่ระบบ</a> เพื่อส่งคำถามหรือแสดงความคิดเห็น
                            </p>
                        <?php endif; ?>

                        <!-- จัดกลุ่มและแยกแสดงผล: คอมเมนต์หลัก vs คอมเมนต์ตอบกลับ -->
                        <?php
                        $comment_stmt->execute([(int) $review['review_id']]);
                        $all_comments = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);

                        $parent_comments = [];
                        $replies = [];
                        foreach ($all_comments as $c) {
                            if (!empty($c['parent_id'])) {
                                $replies[$c['parent_id']][] = $c;
                            } else {
                                $parent_comments[] = $c;
                            }
                        }
                        ?>

                        <?php if (empty($parent_comments)): ?>
                            <p style="font-size: 12px; color: #64748b; text-align: center;">ยังไม่มีคำถามหรือความคิดเห็นใต้รีวิวนี้</p>
                        <?php else: ?>
                            <?php foreach ($parent_comments as $comm): ?>
                                <div class="comment-item">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                        <strong style="color: #fff; font-size: 13px;"><?= htmlspecialchars($comm['username']) ?></strong>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <span style="font-size: 10px; color: #64748b;"><?= htmlspecialchars($comm['created_at']) ?></span>
                                            
                                            <!-- ปุ่ม Reply เปิดฟอร์มพิมพ์ตอบกลับ -->
                                            <?php if (isset($_SESSION['user_id'])): ?>
                                                <button type="button" class="pixel-btn" style="padding: 2px 6px; font-size: 8px;"
                                                        onclick="document.getElementById('reply-form-<?= $comm['comment_id'] ?>').style.display = 'block';">
                                                    REPLY
                                                </button>
                                            <?php endif; ?>

                                            <!-- ปุ่มลบคอมเมนต์ -->
                                            <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $comm['user_id'] || ($_SESSION['role'] ?? '') === 'admin')): ?>
                                                <a href="delete_comment.php?id=<?= $comm['comment_id'] ?>&game_id=<?= $game_id ?>" 
                                                   onclick="return confirm('ยืนยันลบคอมเมนต์?');" 
                                                   style="color: var(--neon-pink); font-size: 11px; text-decoration: none;">ลบ</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p style="margin: 0; font-size: 13px; color: #cbd5e1; whitespace-pre-line;"><?= htmlspecialchars($comm['comment_text']) ?></p>

                                    <!-- ฟอร์มสำหรับพิมพ์ตอบกลับ (ซ่อนอยู่จนกว่าจะกดปุ่ม REPLY) -->
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <form id="reply-form-<?= $comm['comment_id'] ?>" action="submit_comment.php" method="POST" style="display: none; margin-top: 10px;">
                                            <input type="hidden" name="review_id" value="<?= (int) $review['review_id'] ?>">
                                            <input type="hidden" name="game_id" value="<?= (int) $game_id ?>">
                                            <input type="hidden" name="parent_id" value="<?= (int) $comm['comment_id'] ?>">
                                            <textarea name="comment" rows="2" required placeholder="ตอบกลับคุณ <?= htmlspecialchars($comm['username']) ?>..."></textarea>
                                            <div style="text-align: right; margin-top: 4px; display: flex; justify-content: flex-end; gap: 6px;">
                                                <button type="button" class="pixel-btn" style="background: #334155; color: #fff;" onclick="this.parentElement.parentElement.style.display = 'none';">CANCEL</button>
                                                <button type="submit" class="pixel-btn">SEND REPLY</button>
                                            </div>
                                        </form>
                                    <?php endif; ?>

                                    <!-- แสดงรายการคำตอบกลับย่อย (Replies) -->
                                    <?php if (!empty($replies[$comm['comment_id']])): ?>
                                        <?php foreach ($replies[$comm['comment_id']] as $rep): ?>
                                            <div class="comment-reply">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                                    <span style="color: var(--neon-cyan); font-size: 12px; font-weight: 600;">↳ <?= htmlspecialchars($rep['username']) ?></span>
                                                    <div style="display: flex; gap: 6px; align-items: center;">
                                                        <span style="font-size: 9px; color: #64748b;"><?= htmlspecialchars($rep['created_at']) ?></span>
                                                        <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $rep['user_id'] || ($_SESSION['role'] ?? '') === 'admin')): ?>
                                                            <a href="delete_comment.php?id=<?= $rep['comment_id'] ?>&game_id=<?= $game_id ?>" 
                                                               onclick="return confirm('ยืนยันลบคอมเมนต์ตอบกลับ?');" 
                                                               style="color: var(--neon-pink); font-size: 10px; text-decoration: none;">ลบ</a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <p style="margin: 0; font-size: 12px; color: #e2e8f0; whitespace-pre-line;"><?= htmlspecialchars($rep['comment_text']) ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <footer style="text-align: center; padding: 24px; color: #64748b; font-size: 11px;" class="pixel-text">
        &copy; 2026 ViewG. All rights reserved.
    </footer>

</body>
</html>
