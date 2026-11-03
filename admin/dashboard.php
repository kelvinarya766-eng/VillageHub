<?php
session_start();
require_once '../config/database.php';

// ==========================================
// 1. CEK LOGIN ADMIN
// ==========================================
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db = (new Database())->getConnection();
$admin_name = $_SESSION['user']['nama'] ?? 'Admin Desa';
$user_id = $_SESSION['user']['id_user'];

// Ambil data admin untuk foto profil
$stmtUser = $db->prepare("SELECT * FROM users WHERE id_user = :id");
$stmtUser->execute([':id' => $user_id]);
$userData = $stmtUser->fetch();

// ==========================================
// 2. PROSES KIRIM BALASAN
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_balasan'])) {
    $balasan_teks = trim($_POST['balasan'] ?? '');
    $id_balas = intval($_POST['id_aduan'] ?? 0);
    
    if (!empty($balasan_teks) && $id_balas > 0) {
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO balasan (id_aduan, id_user, balasan) VALUES (:a, :u, :b)");
            $stmt->execute([
                ':a' => $id_balas, 
                ':u' => $user_id, 
                ':b' => $balasan_teks
            ]);
            $db->prepare("UPDATE aduan SET status='dibalas' WHERE id_aduan=:id")->execute([':id' => $id_balas]);
            $db->commit();
            header("Location: dashboard.php?msg=success");
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $error_msg = "Gagal: " . $e->getMessage();
        }
    }
}

// ==========================================
// 3. AMBIL DATA ADUAN
// ==========================================
$filter_status = $_GET['status'] ?? '';
$sql = "SELECT a.*, k.golongan AS kategori, u.nama AS nama_pengadu 
        FROM aduan a 
        JOIN kategori k ON k.id_kategori = a.id_kategori 
        JOIN users u ON u.id_user = a.id_user";
if ($filter_status == 'menunggu' || $filter_status == 'dibalas') {
    $sql .= " WHERE a.status = :status";
}
$sql .= " ORDER BY a.tanggal_waktu DESC";

$stmt = $db->prepare($sql);
if ($filter_status == 'menunggu' || $filter_status == 'dibalas') { 
    $stmt->execute([':status' => $filter_status]); 
} else { 
    $stmt->execute(); 
}
$aduan_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin – VillageHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --green-dark: #0d5c2c;
            --green-main: #178a40;
            --green-light: #e8f5e9;
            --yellow: #ffc107;
            --bg-card: #ffffff; 
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }

        /* BACKGROUND GAMBAR DESA */
        body {
            background: url('../assets/bg-desa.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ====== NAVBAR ATAS ====== */
        .navbar {
            background: linear-gradient(135deg, var(--green-main) 0%, #28a745 50%, #2d8f3e 100%);
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 5%; box-shadow: 0 3px 16px rgba(0,0,0,0.15);
            position: sticky; top: 0; z-index: 1000;
            position: relative; /* Penting untuk posisi menu tengah */
        }
        
        .navbar-left { display: flex; align-items: center; gap: 20px; z-index: 2; }
        .navbar-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .navbar-logo img { width: 54px; height: 54px; object-fit: contain; }
        .brand-name { font-weight: 800; line-height: 1.1; white-space: nowrap; font-size: 22px; letter-spacing: 0.5px; }
        .brand-village { color: #fff; }
        .brand-hub { color: var(--yellow); }

        /* CSS AGAR MENU BERADA DI TENGAH */
        .navbar-menu { 
            display: flex; 
            align-items: center; 
            gap: 20px; 
            list-style: none; 
            
            /* Trik Absolute Center */
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
        }

        .navbar-menu a {
            padding: 8px 14px; border-radius: 8px;
            color: #fff; font-size: 14px; font-weight: 600;
            text-decoration: none; transition: all 0.25s; white-space: nowrap;
            border-bottom: 3px solid transparent;
        }
        .navbar-menu a:hover { background: rgba(255,255,255,0.15); }
        .navbar-menu a.active {
            color: var(--yellow);
            border-bottom-color: var(--yellow);
            border-radius: 0; padding-bottom: 6px;
            background: transparent;
        }

        .navbar-actions { display: flex; align-items: center; gap: 10px; z-index: 2; }
        .nav-hello {
            color: #fff; font-weight: 600; font-size: 14px;
            margin-right: 6px; white-space: nowrap; line-height: 1.2;
        }
        .nav-hello small { display: block; font-weight: 400; font-size: 11px; opacity: 0.85; }
        .nav-avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.2); margin-left: 5px; }

        .btn-header {
            padding: 9px 22px; border: 2px solid; border-radius: 8px;
            font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 13px;
            cursor: pointer; transition: all 0.3s ease;
            display: inline-block; white-space: nowrap; text-align: center;
            text-decoration: none;
        }
        .btn-red { background: #c62828; border-color: #c62828; color: #fff; }
        .btn-red:hover { background: #a31515; transform: translateY(-2px); }

        /* LAYOUT UTAMA */
        .container {
            display: flex;
            flex: 1;
            padding: 20px 30px;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        /* SIDEBAR KIRI */
        .sidebar {
            width: 200px;
            background: var(--bg-card);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            height: fit-content;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .sidebar a {
            text-decoration: none;
            color: #333;
            font-weight: 700;
            font-size: 15px;
            padding: 8px 10px;
            border-radius: 6px;
            transition: 0.3s;
        }
        .sidebar a:hover { background: var(--green-light); color: var(--green-dark); padding-left: 15px; }
        .sidebar a.active { background: var(--green-light); color: var(--green-dark); }

        /* KONTEN TENGAH */
        .main-list {
            flex: 1;
            background: var(--bg-card);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .main-title { text-align: center; font-size: 24px; font-weight: 800; color: #111; margin-bottom: 20px; }
        
        .filter-bar { display: flex; gap: 10px; margin-bottom: 20px; justify-content: center; }
        .filter-btn {
            padding: 8px 20px; border-radius: 8px; border: 2px solid transparent;
            font-weight: 800; cursor: pointer; transition: 0.3s; text-decoration: none;
        }
        .filter-btn.menunggu { background: var(--yellow); color: #000; }
        .filter-btn.dibalas { background: var(--green-dark); color: white; }
        .filter-btn.semua { background: #00bcd4; color: white; }
        .filter-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }

        .list-container { display: flex; flex-direction: column; gap: 10px; }
        
        /* ITEM ADUAN */
        .aduan-item {
            display: flex; align-items: center; justify-content: space-between;
            background: #f9fafb; padding: 12px 15px; border-radius: 10px;
            border: 1px solid #e5e7eb; transition: 0.3s;
        }
        .aduan-item:hover { background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); transform: translateX(5px); border-color: var(--green-main); }
        .aduan-info { display: flex; align-items: center; gap: 15px; flex: 1; }
        .aduan-info span { font-size: 13px; font-weight: 700; color: #333; }
        
        .kategori-badge {
            padding: 6px 15px; border-radius: 6px; font-weight: 800; font-size: 13px;
            border: 1px solid #ccc; text-align: center; min-width: 100px;
        }
        .kategori-badge.hijau { background: #a5d6a7; color: #1b5e20; }
        .kategori-badge.kuning { background: var(--yellow); color: #000; }
        .kategori-badge.biru { background: #81d4fa; color: #01579b; }
        
        .status-badge {
            padding: 6px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;
        }
        .status-badge.menunggu { background: var(--yellow); color: #000; }
        .status-badge.dibalas { background: var(--green-dark); color: white; }

        .btn-balas-trigger {
            background: #fff; border: 1px solid #999; border-radius: 6px;
            padding: 6px 15px; font-weight: 700; cursor: pointer; transition: 0.3s;
            color: #333;
        }
        .btn-balas-trigger:hover { background: var(--green-main); color: white; border-color: var(--green-main); }

        /* MODAL (EFEK BLUR) */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: none; justify-content: center; align-items: center; z-index: 1000;
        }
        .modal-content {
            background: #fff; width: 90%; max-width: 500px;
            border-radius: 16px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            animation: slideDown 0.3s ease-out;
        }
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .modal-header h3 { font-size: 20px; color: var(--green-dark); font-weight: 800; }
        .close-btn { background: none; border: none; font-size: 24px; cursor: pointer; color: #888; transition: 0.3s; }
        .close-btn:hover { color: #ff0000; transform: rotate(90deg); }
        
        .modal-desc { background: #f9fafb; padding: 15px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; max-height: 150px; overflow-y: auto; color: #333; border-left: 4px solid var(--green-main);}
        .modal-content textarea {
            width: 100%; height: 120px; padding: 10px; border-radius: 8px; border: 1px solid #ccc;
            font-family: 'Poppins'; resize: vertical; margin-bottom: 15px;
        }
        .modal-content .btn-submit {
            background: var(--green-main); color: white; border: none; width: 100%;
            padding: 12px; border-radius: 8px; font-weight: 800; cursor: pointer; transition: 0.3s;
        }
        .modal-content .btn-submit:hover { background: var(--green-dark); }

        .alert-success { background: #d1fae5; color: #065f46; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-weight: bold; text-align: center; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-weight: bold; text-align: center; }
        
        /* Responsive */
        @media (max-width: 1000px) {
            .navbar { flex-direction: column; gap: 10px; padding: 10px; }
            .navbar-menu { position: static; transform: none; margin-top: 10px; }
            .container { flex-direction: column; }
            .sidebar { width: 100%; flex-direction: row; overflow-x: auto; }
            .sidebar a { white-space: nowrap; }
        }
        @media (max-width: 768px) {
            .nav-hello { display: none; }
            .aduan-info { flex-wrap: wrap; gap: 5px; }
        }
    </style>
</head>
<body>

    <!-- NAVBAR ATAS -->
    <nav class="navbar">
        <div class="navbar-left">
            <a href="dashboard.php" class="navbar-logo">
                <img src="../asset/Logo_village_Hub-removebg-preview.png" alt="VillageHub Logo">
                <div class="brand-name"><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></div>
            </a>
        </div>

        <!-- MENU BERADA DI TENGAH -->
        <ul class="navbar-menu">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="#">Riwayat</a></li>
        </ul>

        <div class="navbar-actions">
            <div class="nav-hello">
                Halo, <?= htmlspecialchars(explode(' ', $admin_name)[0]) ?>
                <small>Admin</small>
            </div>
            
            <a href="../logout.php" class="btn-header btn-red" onclick="return confirm('Yakin ingin logout?')">Logout</a>
            
            <?php 
                $fotoProfil = !empty($userData['foto']) ? '../uploads/' . $userData['foto'] : '../asset/default-avatar.png';
            ?>
            <img src="<?= $fotoProfil ?>" alt="Foto Admin" class="nav-avatar" title="<?= htmlspecialchars($admin_name) ?>">
        </div>
    </nav>

    <!-- LAYOUT UTAMA -->
    <div class="container">
        
        <!-- SIDEBAR KIRI (Menu Admin) -->
        <div class="sidebar">
            <a href="dashboard.php" class="active">Main Admin</a>
            <a href="dashboard.php">Daftar Aduan</a>
            <a href="#">Riwayat</a>
            <a href="#">Kategori</a>
        </div>

        <!-- KONTEN TENGAH -->
        <div class="main-list">
            <h2 class="main-title">Daftar Aduan</h2>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
                <div class="alert-success">Balasan berhasil dikirim!</div>
            <?php endif; ?>
            
            <?php if (isset($error_msg)): ?>
                <div class="alert-error"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <!-- Filter Tombol -->
            <div class="filter-bar">
                <a href="dashboard.php?status=menunggu" class="filter-btn menunggu">Menunggu</a>
                <a href="dashboard.php?status=dibalas" class="filter-btn dibalas">Dibalas</a>
                <a href="dashboard.php" class="filter-btn semua">Semua</a>
            </div>

            <!-- List Aduan -->
            <div class="list-container">
                <?php if (empty($aduan_list)): ?>
                    <p style="text-align:center; color:#666; font-weight:bold;">Belum ada aduan.</p>
                <?php else: ?>
                    <?php foreach ($aduan_list as $aduan): ?>
                        <div class="aduan-item">
                            <div class="aduan-info">
                                <span><?= date('d/m/Y', strtotime($aduan['tanggal_waktu'])) ?></span>
                                <span><?= htmlspecialchars($aduan['nama_pengadu']) ?></span>
                                
                                <div class="kategori-badge <?= strtolower($aduan['kategori']) == 'kebersihan' ? 'hijau' : (strtolower($aduan['kategori']) == 'infrastruktur' ? 'biru' : 'kuning') ?>">
                                    <?= htmlspecialchars($aduan['kategori']) ?>
                                </div>
                                
                                <span class="status-badge <?= $aduan['status'] ?>"><?= strtoupper($aduan['status']) ?></span>
                            </div>
                            <button class="btn-balas-trigger" onclick="bukaModal(<?= $aduan['id_aduan'] ?>, '<?= htmlspecialchars(addslashes($aduan['deskripsi'])) ?>', '<?= htmlspecialchars(addslashes($aduan['nama_pengadu'])) ?>')">
                                Balas
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- MODAL POPUP -->
    <div class="modal-overlay" id="modalBalas">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Balas Aduan</h3>
                <button class="close-btn" onclick="tutupModal()">&times;</button>
            </div>
            
            <p style="font-size: 14px; margin-bottom: 10px;">Balasan untuk: <strong id="modal-nama"></strong></p>
            <div class="modal-desc" id="modal-deskripsi"></div>
            
            <form method="POST" action="">
                <input type="hidden" name="id_aduan" id="modal-id-aduan">
                <textarea name="balasan" placeholder="Ketik balasan atau tanggapan Anda untuk warga di sini..." required></textarea>
                <button type="submit" name="kirim_balasan" class="btn-submit">Kirim Balasan</button>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT UNTUK MODAL -->
    <script>
        function bukaModal(id, deskripsi, nama) {
            document.getElementById('modal-id-aduan').value = id;
            document.getElementById('modal-deskripsi').innerText = deskripsi;
            document.getElementById('modal-nama').innerText = nama;
            document.getElementById('modalBalas').style.display = 'flex';
        }

        function tutupModal() {
            document.getElementById('modalBalas').style.display = 'none';
        }

        document.getElementById('modalBalas').addEventListener('click', function(e) {
            if (e.target === this) {
                tutupModal();
            }
        });
    </script>
</body>
</html>