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
    die("ไม่พบข้อมูลเกมนี้ในระบบ");
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

// ดึงความคิดเห็นของรีวิวแต่ละรายการ
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
    <title>GameInsight - <?= htmlspecialchars($game['title']) ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Prompt', sans-serif;
        }
    </style>
</head>

<body class="bg-pink-50 text-gray-800 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-pink-100">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="games.php" class="text-2xl font-bold text-pink-600">
                🎮 GameInsight
            </a>

            <div class="space-x-4 flex items-center">
                <a href="games.php" class="text-gray-600 hover:text-pink-600">
                    หน้าแรก
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-gray-700 font-medium">
                        👤 <?= htmlspecialchars($_SESSION['username'] ?? 'ผู้ใช้') ?>
                    </span>

                    <!-- ส่วนแยกสิทธิ์สำหรับ Admin -->
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded font-bold">แอดมิน</span>
                        <a href="admin_dashboard.php" class="text-amber-600 hover:text-amber-700 font-medium text-sm">
                            ⚙️ จัดการหลังบ้าน
                        </a>
                    <?php endif; ?>

                    <a href="logout.php" class="text-red-500 hover:text-red-600 text-sm">
                        ออกจากระบบ
                    </a>
                <?php else: ?>
                    <a href="login.php"
                       class="bg-pink-500 text-white px-4 py-2 rounded-full shadow hover:bg-pink-600">
                        เข้าสู่ระบบ
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">

        <!-- Game Information -->
        <div class="bg-white rounded-2xl shadow-sm border border-pink-100 p-6 mb-8 flex flex-col md:flex-row gap-6">

            <img
                src="<?= htmlspecialchars(
                    $game['cover_image']
                    ?: 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80'
                ) ?>"
                alt="Game Cover"
                class="w-full md:w-1/3 rounded-xl object-cover h-56"
            >

            <div class="flex-1">
                <span class="bg-pink-100 text-pink-700 text-xs px-3 py-1 rounded-full font-medium">
                    <?= htmlspecialchars($game['category_name'] ?? 'ไม่ระบุหมวดหมู่') ?>
                </span>

                <h1 class="text-3xl font-bold text-gray-900 mt-2">
                    <?= htmlspecialchars($game['title']) ?>
                </h1>

                <p class="text-gray-500 text-sm mt-1">
                    พัฒนาโดย: <?= htmlspecialchars($game['developer'] ?? 'ไม่ระบุ') ?>
                </p>

                <div class="mt-4">
                    <?php if (!empty($game['store_url'])): ?>
                        <a
                            href="<?= htmlspecialchars($game['store_url']) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="bg-gray-900 text-white text-xs px-4 py-2 rounded-xl hover:bg-gray-800 font-medium inline-block"
                        >
                            หน้าร้านค้าทางการ →
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Review Header -->
        <div class="flex justify-between items-center mb-6 gap-4">
            <h2 class="text-xl font-bold text-gray-900">
                รีวิวจากผู้เล่น (<?= count($reviews) ?>)
            </h2>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a
                    href="add_review.php?game_id=<?= $game_id ?>"
                    class="bg-pink-500 text-white text-sm px-4 py-2 rounded-xl shadow hover:bg-pink-600 font-medium"
                >
                    ✍️ เขียนรีวิวเกมนี้
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($reviews)): ?>

            <div class="bg-white rounded-2xl shadow-sm border border-pink-100 p-8 text-center text-gray-400 mb-8">
                <p>ยังไม่มีบทวิจารณ์สำหรับเกมนี้ในระบบ</p>
                <p class="text-xs mt-1">
                    มาเป็นคนแรกที่แชร์ประสบการณ์การเล่นเกมนี้กันเถอะ!
                </p>
            </div>

        <?php else: ?>

            <?php foreach ($reviews as $review): ?>

                <!-- Review Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-pink-100 p-6 mb-6">

                    <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4 gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-pink-200 rounded-full flex items-center justify-center font-bold text-pink-700">
                                <?= htmlspecialchars(mb_substr($review['username'], 0, 1)) ?>
                            </div>

                            <div>
                                <h3 class="font-semibold text-gray-900">
                                    <?= htmlspecialchars($review['username']) ?>
                                </h3>

                                <p class="text-xs text-gray-400">
                                    รีวิวเมื่อ: <?= htmlspecialchars($review['created_at']) ?>
                                </p>
                            </div>
                        </div>

                        <div class="bg-amber-100 text-amber-800 px-4 py-1.5 rounded-full font-bold text-lg flex items-center gap-1">
                            ⭐ <?= number_format((float) $review['rating'], 1) ?> / 5.0
                        </div>
                    </div>

                    <!-- Review Title and Content -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-800 mb-2">
                            <?= htmlspecialchars($review['title']) ?>
                        </h4>

                        <?php if (!empty($review['review_image'])): ?>
                            <img
                                src="<?= htmlspecialchars($review['review_image']) ?>"
                                alt="รูปประกอบรีวิว"
                                class="max-h-80 rounded-xl object-contain mb-4"
                            >
                        <?php endif; ?>
                    </div>

                    <!-- Pros and Cons -->
                    <div class="grid md:grid-cols-2 gap-4 mb-6">

                        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                            <h5 class="font-semibold text-emerald-800 mb-2">
                                ✅ ข้อดี (Pros)
                            </h5>

                            <p class="text-sm text-emerald-700 whitespace-pre-line"><?= htmlspecialchars($review['content_pros'] ?? '') ?></p>
                        </div>

                        <div class="bg-rose-50 border border-rose-100 rounded-xl p-4">
                            <h5 class="font-semibold text-rose-800 mb-2">
                                ❌ ข้อเสีย (Cons)
                            </h5>

                            <p class="text-sm text-rose-700 whitespace-pre-line"><?= htmlspecialchars($review['content_cons'] ?? '') ?></p>
                        </div>

                    </div>
                </div>

                <!-- Comments / Q&A -->
                <div class="bg-white rounded-2xl shadow-sm border border-pink-100 p-6 mb-8">

                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        💬 สอบถามข้อมูลเชิงลึก / ความคิดเห็น
                    </h3>

                    <!-- Comment Form -->
                    <?php if (isset($_SESSION['user_id'])): ?>

                        <form action="submit_comment.php" method="POST" class="mb-6">
                            <input
                                type="hidden"
                                name="review_id"
                                value="<?= (int) $review['review_id'] ?>"
                            >

                            <textarea
                                name="comment"
                                rows="3"
                                required
                                placeholder="สอบถามผู้รีวิว เช่น เกมนี้เล่นบนจอยสติ๊กสะดวกไหม หรือมีภาษาไทยไหม..."
                                class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:outline-none focus:border-pink-500"
                            ></textarea>

                            <div class="flex justify-end mt-2">
                                <button
                                    type="submit"
                                    class="bg-pink-500 text-white text-sm px-5 py-2 rounded-xl shadow hover:bg-pink-600 font-medium cursor-pointer"
                                >
                                    ส่งคำถาม
                                </button>
                            </div>
                        </form>

                    <?php else: ?>

                        <p class="text-sm text-gray-500 mb-6">
                            <a href="login.php" class="text-pink-600 font-semibold hover:underline">
                                เข้าสู่ระบบ
                            </a>
                            เพื่อส่งคำถามหรือแสดงความคิดเห็น
                        </p>

                    <?php endif; ?>

                    <!-- Comment List -->
                    <div class="space-y-4 border-t border-gray-100 pt-4">

                        <?php
                        $comment_stmt->execute([(int) $review['review_id']]);
                        $comments = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <?php if (empty($comments)): ?>

                            <p class="text-xs text-gray-400 text-center py-2">
                                ยังไม่มีคำถามหรือความคิดเห็นใต้รีวิวนี้
                            </p>

                        <?php else: ?>

                            <?php foreach ($comments as $comm): ?>

                                <div class="bg-pink-50/50 rounded-xl p-4">
                                    <div class="flex justify-between items-center mb-1 gap-4">
                                        <span class="font-semibold text-sm text-gray-900">
                                            <?= htmlspecialchars($comm['username']) ?>
                                        </span>

                                        <span class="text-xs text-gray-400">
                                            <?= htmlspecialchars($comm['created_at']) ?>
                                        </span>
                                    </div>

                                    <p class="text-sm text-gray-600 whitespace-pre-line"><?= htmlspecialchars($comm['comment_text']) ?></p>
                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>
                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="text-center py-6 text-gray-400 text-xs">
        &copy; 2026 GameInsight. All rights reserved.
    </footer>

</body>
</html>