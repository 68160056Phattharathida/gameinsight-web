<?php
session_start();
require_once 'config/db.php';

// 1. รับค่า game_id จาก URL
$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;

if ($game_id <= 0) {
    header("Location: games.php");
    exit();
}

try {
    // 2. ดึงรายละเอียดข้อมูลเกม
    $game_stmt = $pdo->prepare("
        SELECT g.*, c.category_name 
        FROM gi_games g 
        LEFT JOIN gi_categories c ON g.category_id = c.category_id 
        WHERE g.game_id = ?
    ");
    $game_stmt->execute([$game_id]);
    $game = $game_stmt->fetch();

    if (!$game) {
        die("ไม่พบข้อมูลเกมนี้ในระบบ");
    }

    // 3. ดึงรายการรีวิวของเกมนี้พร้อมชื่อผู้ใช้
    $rev_stmt = $pdo->prepare("
        SELECT r.*, u.username 
        FROM gi_reviews r 
        LEFT JOIN gi_users u ON r.user_id = u.user_id 
        WHERE r.game_id = ? 
        ORDER BY r.created_at DESC
    ");
    $rev_stmt->execute([$game_id]);
    $reviews = $rev_stmt->fetchAll();

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($game['title']) ?> - รายละเอียดรีวิวเกม | GameInsight</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; }
    </style>
</head>
<body class="bg-pink-50 text-gray-800 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-pink-100">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="games.php" class="text-2xl font-bold text-pink-600">🎮 GameInsight</a>
            <div class="space-x-4">
                <a href="games.php" class="text-gray-600 hover:text-pink-600">หน้ารายการเกม</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-pink-600 font-medium">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <a href="logout.php" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-full shadow-sm hover:bg-gray-200">ออกจากระบบ</a>
                <?php else: ?>
                    <a href="login.php" class="bg-pink-500 text-white px-4 py-2 rounded-full shadow hover:bg-pink-600">เข้าสู่ระบบ</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">
        
        <div class="mb-4">
            <a href="games.php" class="text-pink-600 hover:underline text-sm font-medium">&larr; กลับไปหน้ารายการเกม</a>
        </div>

        <!-- Game Header Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-pink-100 p-6 mb-8 flex flex-col md:flex-row gap-6">
            <img src="<?= htmlspecialchars($game['cover_image'] ?: 'https://via.placeholder.com/600x400?text=No+Cover') ?>" alt="<?= htmlspecialchars($game['title']) ?>" class="w-full md:w-1/3 rounded-xl object-cover h-56">
            <div class="flex-1">
                <span class="bg-pink-100 text-pink-700 text-xs px-3 py-1 rounded-full font-medium"><?= htmlspecialchars($game['category_name'] ?: 'ทั่วไป') ?></span>
                <h1 class="text-3xl font-bold text-gray-900 mt-2"><?= htmlspecialchars($game['title']) ?></h1>
                <p class="text-gray-500 text-sm mt-1">พัฒนาโดย: <?= htmlspecialchars($game['developer'] ?: 'ไม่ระบุ') ?></p>
                
                <?php if (!empty($game['store_url'])): ?>
                    <div class="mt-4">
                        <a href="<?= htmlspecialchars($game['store_url']) ?>" target="_blank" class="inline-block bg-gray-900 text-white text-xs px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                            หน้าร้านค้าทางการ &rarr;
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reviews List -->
        <h2 class="text-2xl font-bold text-gray-900 mb-4">รีวิวจากผู้เล่น (<?= count($reviews) ?>)</h2>

        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $rev): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-pink-100 p-6 mb-8">
                    <!-- Review Header -->
                    <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-pink-200 rounded-full flex items-center justify-center font-bold text-pink-700 uppercase">
                                <?= mb_substr($rev['username'] ?: 'U', 0, 1, 'UTF-8') ?>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($rev['username'] ?: 'ผู้ใช้ทั่วไป') ?></h3>
                                <p class="text-xs text-gray-400">รีวิวเมื่อ: <?= date('d M Y', strtotime($rev['created_at'])) ?></p>
                            </div>
                        </div>
                        <div class="bg-amber-100 text-amber-800 px-4 py-1.5 rounded-full font-bold text-lg flex items-center gap-1">
                            ⭐ <?= number_format($rev['rating'], 1) ?> / 5.0
                        </div>
                    </div>

                    <!-- Review Title & Body -->
                    <div class="mb-6">
                        <h4 class="font-bold text-gray-900 text-lg mb-2"><?= htmlspecialchars($rev['title']) ?></h4>
                        <?php if (!empty($rev['review_image'])): ?>
                            <img src="<?= htmlspecialchars($rev['review_image']) ?>" alt="ภาพประกอบรีวิว" class="rounded-xl mb-4 max-h-80 object-cover w-full">
                        <?php endif; ?>
                    </div>

                    <!-- Pros & Cons Grid -->
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                        <!-- Pros -->
                        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                            <h5 class="font-semibold text-emerald-800 mb-2 flex items-center gap-1.5">
                                ✅ ข้อดี (Pros)
                            </h5>
                            <p class="text-sm text-emerald-700 whitespace-pre-line leading-relaxed">
                                <?= !empty($rev['content_pros']) ? htmlspecialchars($rev['content_pros']) : 'ไม่มีการระบุข้อดี' ?>
                            </p>
                        </div>
                        <!-- Cons -->
                        <div class="bg-rose-50 border border-rose-100 rounded-xl p-4">
                            <h5 class="font-semibold text-rose-800 mb-2 flex items-center gap-1.5">
                                ❌ ข้อเสีย (Cons)
                            </h5>
                            <p class="text-sm text-rose-700 whitespace-pre-line leading-relaxed">
                                <?= !empty($rev['content_cons']) ? htmlspecialchars($rev['content_cons']) : 'ไม่มีการระบุข้อเสีย' ?>
                            </p>
                        </div>
                    </div>

                    <!-- Comments / Q&A Section -->
                    <div class="border-t border-gray-100 pt-4 mt-4">
                        <h5 class="text-md font-bold text-gray-800 mb-3">💬 ความคิดเห็นและคำถามใต้รีวิว</h5>
                        
                        <?php
                        // ดึงคอมเมนต์ของรีวิวนี้
                        $comm_stmt = $pdo->prepare("
                            SELECT c.*, u.username 
                            FROM gi_comments c 
                            LEFT JOIN gi_users u ON c.user_id = u.user_id 
                            WHERE c.review_id = ? 
                            ORDER BY c.created_at ASC
                        ");
                        $comm_stmt->execute([$rev['review_id']]);
                        $comments = $comm_stmt->fetchAll();
                        ?>

                        <div class="space-y-3 mb-4">
                            <?php if (!empty($comments)): ?>
                                <?php foreach ($comments as $comment): ?>
                                    <div class="bg-pink-50/50 rounded-xl p-3">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="font-semibold text-xs text-gray-900"><?= htmlspecialchars($comment['username'] ?: 'สมาชิก') ?></span>
                                            <span class="text-[10px] text-gray-400"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></span>
                                        </div>
                                        <p class="text-xs text-gray-600 leading-relaxed"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-xs text-gray-400 italic">ยังไม่มีคำถามหรือความคิดเห็นในรีวิวนี้</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-sm border border-pink-100 p-8 text-center text-gray-500 mb-8">
                <p>ยังไม่มีบทวิจารณ์สำหรับเกมนี้ในระบบ</p>
            </div>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="text-center py-6 text-gray-400 text-xs">
        &copy; 2026 GameInsight. All rights reserved.
    </footer>

</body>
</html>