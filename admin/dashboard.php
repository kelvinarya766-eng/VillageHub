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

// Ambil data admin
$stmtUser = $db->prepare("SELECT * FROM users WHERE id_user = :id");
$stmtUser->execute([':id' => $user_id]);
$userData = $stmtUser->fetch();

// ==========================================
// 2. PROSES KIRIM / EDIT BALASAN
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_balasan'])) {
    $balasan_teks = trim($_POST['balasan'] ?? '');
    $id_balas = intval($_POST['id_aduan'] ?? 0);
    
    if (!empty($balasan_teks) && $id_balas > 0) {
        $db->beginTransaction();
        try {
            $cekBalasan = $db->prepare("SELECT id_balasan FROM balasan WHERE id_aduan = :id LIMIT 1");
            $cekBalasan->execute([':id' => $id_balas]);
            $balasanLama = $cekBalasan->fetch();

            if ($balasanLama) {
                $stmt = $db->prepare("UPDATE balasan SET balasan = :b, id_user = :u WHERE id_balasan = :id_b");
                $stmt->execute([':b' => $balasan_teks, ':u' => $user_id, ':id_b' => $balasanLama['id_balasan']]);
            } else {
                $stmt = $db->prepare("INSERT INTO balasan (id_aduan, id_user, balasan) VALUES (:a, :u, :b)");
                $stmt->execute([':a' => $id_balas, ':u' => $user_id, ':b' => $balasan_teks]);
            }

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
// 3. AMBIL DATA ADUAN & BALASAN
// ==========================================
$filter_status = $_GET['status'] ?? '';
$sql = "SELECT a.*, k.golongan AS kategori, u.nama AS nama_pengadu, 
        (SELECT balasan FROM balasan b WHERE b.id_aduan = a.id_aduan ORDER BY b.id_balasan ASC LIMIT 1) AS isi_balasan
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
            position: relative;
        }
        
        .navbar-left { display: flex; align-items: center; gap: 20px; z-index: 2; }
        .navbar-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .navbar-logo img { width: 54px; height: 54px; object-fit: contain; }
        .brand-name { font-weight: 800; line-height: 1.1; white-space: nowrap; font-size: 22px; letter-spacing: 0.5px; }
        .brand-village { color: #fff; }
        .brand-hub { color: var(--yellow); }

        .navbar-menu { 
            display: flex; align-items: center; gap: 20px; list-style: none; 
            position: absolute; left: 50%; transform: translateX(-50%); z-index: 2;
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

        .navbar-actions { display: flex; align-items: center; gap: 15px; z-index: 2; }
        .role-text { color: #fff; font-weight: 600; font-size: 14px; letter-spacing: 0.5px; }

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
            flex: 1;
            padding: 30px 5%;
            max-width: 1000px;
            margin: 0 auto;
            width: 100%;
        }

        /* KONTEN TENGAH */
        .main-list {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .main-title { text-align: center; font-size: 28px; font-weight: 800; color: #111; margin-bottom: 25px; }
        
        .filter-bar { display: flex; gap: 15px; margin-bottom: 30px; justify-content: center; }
        .filter-btn {
            padding: 10px 24px; border-radius: 8px; border: 2px solid transparent;
            font-weight: 800; cursor: pointer; transition: 0.3s; text-decoration: none;
        }
        .filter-btn.menunggu { background: var(--yellow); color: #000; }
        .filter-btn.dibalas { background: var(--green-dark); color: white; }
        .filter-btn.semua { background: #00bcd4; color: white; }
        .filter-btn:hover { transform: translateY(-3px); box-shadow: 0 6px 12px rgba(0,0,0,0.2); }

        .list-container { display: flex; flex-direction: column; gap: 15px; }
        
        /* ITEM ADUAN - MENGGUNAKAN GRID AGAR LURUS */
        .aduan-item {
            display: grid;
            grid-template-columns: 100px 150px 120px 110px 1fr;
            align-items: center;
            gap: 15px;
            background: #f9fafb; padding: 15px 20px; border-radius: 12px;
            border: 1px solid #e5e7eb; transition: 0.3s;
        }
        .aduan-item:hover { background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-color: var(--green-main); }
        
        .cell-text { font-size: 14px; font-weight: 700; color: #333; }
        .cell-center { text-align: center; }
        .cell-right { text-align: right; }
        
        .kategori-badge {
            padding: 8px 12px; border-radius: 8px; font-weight: 800; font-size: 13px;
            border: 1px solid #ccc; text-align: center; width: 100%;
        }
        .kategori-badge.hijau { background: #a5d6a7; color: #1b5e20; }
        .kategori-badge.kuning { background: var(--yellow); color: #000; }
        .kategori-badge.biru { background: #81d4fa; color: #01579b; }
        
        .status-badge {
            padding: 8px 15px; border-radius: 20px; font-weight: 800; font-size: 12px;
            width: 100%; display: inline-block;
        }
        .status-badge.menunggu { background: var(--yellow); color: #000; }
        .status-badge.dibalas { background: var(--green-dark); color: white; }

        /* TOMBOL AKSI (BALAS / EDIT) */
        .btn-aksi {
            background: #fff; border: 2px solid #999; border-radius: 8px;
            padding: 8px 15px; font-weight: 700; cursor: pointer; transition: 0.3s;
            color: #333; font-size: 13px; text-align: center; min-width: 80px;
            display: inline-flex; justify-content: center; align-items: center; gap: 5px;
        }
        .btn-aksi:hover { background: var(--green-main); color: white; border-color: var(--green-main); }
        
        .btn-aksi.edit { border-color: #e0a800; color: #e0a800; }
        .btn-aksi.edit:hover { background: var(--yellow); color: #000; border-color: var(--yellow); }

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

        .alert-success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; }
        
        @media (max-width: 1000px) {
            .navbar { flex-direction: column; gap: 10px; padding: 10px; }
            .navbar-menu { position: static; transform: none; margin-top: 10px; }
        }
        @media (max-width: 800px) {
            .aduan-item { grid-template-columns: 1fr; text-align: center; gap: 10px; }
            .cell-right { text-align: center; }
            .role-text { display: none; }
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

        <ul class="navbar-menu">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="riwayat.php">Riwayat</a></li>
        </ul>

        <div class="navbar-actions">
            <span class="role-text">Admin</span>
            <!-- TOMBOL LOGOUT DENGAN FUNGSI JS -->
            <button onclick="logoutDanRedirect()" class="btn-header btn-red" style="border: none; cursor: pointer;">Logout</button>
        </div>
    </nav>

    <!-- LAYOUT UTAMA -->
    <div class="container">
        
        <div class="main-list">
            <h2 class="main-title">Daftar Aduan</h2>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
                <div class="alert-success">Balasan berhasil disimpan!</div>
            <?php endif; ?>
            
            <?php if (isset($error_msg)): ?>
                <div class="alert-error"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <div class="filter-bar">
                <a href="dashboard.php?status=menunggu" class="filter-btn menunggu">Menunggu</a>
                <a href="dashboard.php?status=dibalas" class="filter-btn dibalas">Dibalas</a>
                <a href="dashboard.php" class="filter-btn semua">Semua</a>
            </div>

            <div class="list-container">
                <?php if (empty($aduan_list)): ?>
                    <p style="text-align:center; color:#666; font-weight:bold; padding: 20px;">Belum ada aduan.</p>
                <?php else: ?>
                    <?php foreach ($aduan_list as $aduan): ?>
                        <div class="aduan-item">
                            <div class="cell-text"><?= date('d/m/Y', strtotime($aduan['tanggal_waktu'])) ?></div>
                            <div class="cell-text" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= htmlspecialchars($aduan['nama_pengadu']) ?>
                            </div>
                            <div class="cell-center">
                                <div class="kategori-badge <?= strtolower($aduan['kategori']) == 'kebersihan' ? 'hijau' : (strtolower($aduan['kategori']) == 'infrastruktur' ? 'biru' : 'kuning') ?>">
                                    <?= htmlspecialchars($aduan['kategori']) ?>
                                </div>
                            </div>
                            <div class="cell-center">
                                <span class="status-badge <?= $aduan['status'] ?>"><?= strtoupper($aduan['status']) ?></span>
                            </div>
                            <div class="cell-right">
                                <?php if($aduan['status'] == 'dibalas'): ?>
                                    <button class="btn-aksi edit" onclick="bukaModal(<?= $aduan['id_aduan'] ?>, '<?= htmlspecialchars(addslashes($aduan['deskripsi'])) ?>', '<?= htmlspecialchars(addslashes($aduan['nama_pengadu'])) ?>', '<?= htmlspecialchars(addslashes($aduan['isi_balasan'] ?? '')) ?>')">
                                        ✏️ Edit
                                    </button>
                                <?php else: ?>
                                    <button class="btn-aksi" onclick="bukaModal(<?= $aduan['id_aduan'] ?>, '<?= htmlspecialchars(addslashes($aduan['deskripsi'])) ?>', '<?= htmlspecialchars(addslashes($aduan['nama_pengadu'])) ?>', '')">
                                        Balas
                                    </button>
                                <?php endif; ?>
                            </div>
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
                <h3 id="modalTitle">Balas Aduan</h3>
                <button class="close-btn" onclick="tutupModal()">&times;</button>
            </div>
            
            <p style="font-size: 14px; margin-bottom: 10px;">Aduan dari: <strong id="modal-nama"></strong></p>
            <div class="modal-desc" id="modal-deskripsi"></div>
            
            <form method="POST" action="">
                <input type="hidden" name="id_aduan" id="modal-id-aduan">
                <textarea name="balasan" id="modal-isi-balasan" placeholder="Ketik balasan atau tanggapan Anda untuk warga di sini..." required></textarea>
                <button type="submit" name="kirim_balasan" class="btn-submit">Simpan Balasan</button>
            </form>
        </div>
    </div>

    <!-- WAJIB: Panggil api.js agar fungsi logoutDanRedirect() jalan -->
    <script src="../assets/api.js?v=12"></script>
    <script>
        function bukaModal(id, deskripsi, nama, balasanLama) {
            document.getElementById('modal-id-aduan').value = id;
            document.getElementById('modal-deskripsi').innerText = deskripsi;
            document.getElementById('modal-nama').innerText = nama;
            document.getElementById('modal-isi-balasan').value = balasanLama;
            
            if (balasanLama !== '') {
                document.getElementById('modalTitle').innerText = 'Edit Balasan Aduan';
            } else {
                document.getElementById('modalTitle').innerText = 'Balas Aduan';
            }

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