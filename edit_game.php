<?php
session_start();
require_once __DIR__ . '/config/db.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;
$error = '';

// ดึงข้อมูลเกมเดิม
$stmt = $pdo->prepare("SELECT * FROM gi_games WHERE game_id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch();

if (!$game) {
    die("ไม่พบข้อมูลเกมนี้ในระบบ");
}

// ดึงหมวดหมู่เกมทั้งหมด
$cat_stmt = $pdo->query("SELECT * FROM gi_categories ORDER BY category_name ASC");
$categories = $cat_stmt->fetchAll();

// บันทึกการแก้ไข
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $developer = trim($_POST['developer'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $store_url = trim($_POST['store_url'] ?? '');
    $cover_image = trim($_POST['cover_image'] ?? ''); // URL เดิม/ใส่เพิ่ม

    // การจัดการอัปโหลดไฟล์รูปภาพ
    if (isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['cover_file']['tmp_name'];
        $fileName = $_FILES['cover_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // นามสกุลไฟล์ที่อนุญาต
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadFileDir = 'uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            // ตั้งชื่อไฟล์ใหม่กันชื่อซ้ำ
            $newFileName = time() . '_' . md5(uniqid()) . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $cover_image = $dest_path; // ใช้พาธไฟล์รูปที่อัปโหลด
            } else {
                $error = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์รูปภาพ";
            }
        } else {
            $error = "อนุญาตเฉพาะไฟล์รูปภาพประเภท JPG, PNG, GIF, WEBP เท่านั้น";
        }
    }

    if (empty($title)) {
        $error = "กรุณากรอกชื่อเกม";
    } elseif (empty($error)) {
        $update_stmt = $pdo->prepare("
            UPDATE gi_games 
            SET title = ?, developer = ?, category_id = ?, store_url = ?, cover_image = ?
            WHERE game_id = ?
        ");
        $result = $update_stmt->execute([$title, $developer, $category_id, $store_url, $cover_image, $game_id]);

        if ($result) {
            header("Location: game_detail.php?game_id=" . $game_id);
            exit();
        } else {
            $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลเกม - ViewG</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0a0c13;
            --panel-bg: #131722;
            --neon-cyan: #00f0ff;
            --neon-pink: #ff0055;
            --neon-yellow: #ffe600;
            --text-main: #f1f5f9;
        }

        * { box-sizing: border-box; image-rendering: pixelated; }

        body {
            margin: 0;
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

        main { max-width: 600px; margin: 40px auto; padding: 0 16px; }

        .pixel-card {
            background: var(--panel-bg);
            border: 3px solid var(--neon-cyan);
            box-shadow: 6px 6px 0px #000;
            padding: 28px;
            border-radius: 8px;
        }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 11px; color: #94a3b8; }
        
        .form-control {
            width: 100%;
            padding: 10px;
            background: #080a0f;
            border: 2px solid #334155;
            color: #fff;
            font-family: 'Prompt', sans-serif;
            font-size: 14px;
            outline: none;
        }

        .form-control:focus { border-color: var(--neon-cyan); }

        .pixel-btn {
            display: inline-block;
            font-family: 'Press Start 2P', monospace;
            font-size: 10px;
            padding: 10px 16px;
            color: #000;
            background-color: var(--neon-yellow);
            border: 2px solid #fff;
            box-shadow: 3px 3px 0px #000;
            text-decoration: none;
            cursor: pointer;
        }

        .pixel-btn:hover { transform: translate(2px, 2px); box-shadow: 1px 1px 0px #000; }
        .pixel-btn-cancel { background: #334155; color: #fff; }
    </style>
</head>
<body>

    <nav>
        <div class="nav-container">
            <a href="games.php" class="logo pixel-text">🎮 ViewG</a>
            <a href="game_detail.php?game_id=<?= $game_id ?>" class="pixel-btn pixel-btn-cancel">BACK</a>
        </div>
    </nav>

    <main>
        <div class="pixel-card">
            <h2 class="pixel-text" style="font-size: 14px; color: var(--neon-yellow); margin-top: 0;">✏️ EDIT GAME</h2>

            <?php if ($error): ?>
                <p style="color: var(--neon-pink); font-size: 13px;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <!-- เพิ่ม enctype="multipart/form-data" เพื่อรองรับการแนบไฟล์ -->
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="pixel-text">GAME TITLE</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($game['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="pixel-text">DEVELOPER</label>
                    <input type="text" name="developer" class="form-control" value="<?= htmlspecialchars($game['developer'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="pixel-text">CATEGORY</label>
                    <select name="category_id" class="form-control">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>" <?= $cat['category_id'] == $game['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="pixel-text">STORE URL</label>
                    <input type="url" name="store_url" class="form-control" value="<?= htmlspecialchars($game['store_url'] ?? '') ?>">
                </div>

                <!-- ช่องแนบไฟล์รูปภาพจากเครื่อง -->
                <div class="form-group">
                    <label class="pixel-text">UPLOAD COVER IMAGE (FILE)</label>
                    <input type="file" name="cover_file" class="form-control" accept="image/*">
                </div>

                <!-- ช่องระบุ URL รูปภาพ (สำรองไว้กรณีไม่มีไฟล์) -->
                <div class="form-group">
                    <label class="pixel-text">OR COVER IMAGE URL</label>
                    <input type="text" name="cover_image" class="form-control" value="<?= htmlspecialchars($game['cover_image'] ?? '') ?>" placeholder="https://...">
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <a href="game_detail.php?game_id=<?= $game_id ?>" class="pixel-btn pixel-btn-cancel">CANCEL</a>
                    <button type="submit" class="pixel-btn">SAVE CHANGES</button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>