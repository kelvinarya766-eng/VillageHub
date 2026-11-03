<?php
require_once __DIR__ . '/../config/auth.php';

// Wajib login sebagai warga
if (empty($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['user']['role'] !== 'warga') {
    header('Location: ../admin/dashboard.php');
    exit;
}

$user = $_SESSION['user'];

// Ambil data lengkap user (termasuk foto) dari database
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT * FROM users WHERE id_user = :id");
$stmt->execute([':id' => $user['id_user']]);
$userData = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Warga – VillageHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* ====== ROOT & RESET ====== */
        :root { --brand-green: #1b7a2b; --brand-yellow: #ffc107; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background-color: #f0f2f5; color: #333;
            line-height: 1.6; overflow-x: hidden;
            min-height: 100vh; display: flex; flex-direction: column;
        }
        a { text-decoration: none; color: inherit; }

        /* ====== NAVBAR ====== */
        .navbar {
            background: linear-gradient(135deg, var(--brand-green) 0%, #28a745 50%, #2d8f3e 100%);
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 5%; box-shadow: 0 3px 16px rgba(0,0,0,0.15);
            position: sticky; top: 0; z-index: 1000;
        }
        .navbar-left { display: flex; align-items: center; gap: 20px; }
        .navbar-logo { display: flex; align-items: center; gap: 10px; }
        .navbar-logo img { width: 54px; height: 54px; object-fit: contain; }
        .brand-name { font-weight: 800; line-height: 1.1; white-space: nowrap; }
        .navbar-logo .brand-name { font-size: 22px; letter-spacing: 0.5px; }
        .brand-village { color: #fff; }
        .brand-hub { color: var(--brand-yellow); }

        /* Menu Horizontal */
        .navbar-menu {
            display: flex; align-items: center; gap: 4px;
            list-style: none; margin-left: 10px;
        }
        .navbar-menu a {
            padding: 8px 14px; border-radius: 8px;
            color: #fff; font-size: 13px; font-weight: 600;
            transition: all 0.25s; white-space: nowrap;
            border-bottom: 2px solid transparent;
        }
        .navbar-menu a:hover { background: rgba(255,255,255,0.15); }
        .navbar-menu a.active {
            color: var(--brand-yellow);
            border-bottom-color: var(--brand-yellow);
            border-radius: 0; padding-bottom: 6px;
            background: transparent;
        }

        .navbar-actions { display: flex; align-items: center; gap: 10px; }
        
        /* CSS TEKS SAPAAN */
        .nav-hello {
            color: #fff; font-weight: 600; font-size: 14px;
            margin-right: 6px; white-space: nowrap; line-height: 1.2;
        }
        .nav-hello small { display: block; font-weight: 400; font-size: 11px; opacity: 0.85; }

        /* CSS FOTO PROFIL (NAV AVATAR) */
        .nav-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            object-fit: cover; border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2); margin-left: 5px;
        }

        .btn-header {
            padding: 9px 22px; border: 2px solid; border-radius: 8px;
            font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 13px;
            cursor: pointer; transition: all 0.3s ease;
            display: inline-block; white-space: nowrap;
        }
        .btn-red { background: #c62828; border-color: #c62828; color: #fff; }
        .btn-red:hover { background: #a31515; transform: translateY(-2px); }

        /* ====== HERO BANNER ====== */
        .hero {
            position: relative; width: 100%; height: 220px;
            overflow: hidden; margin-bottom: 30px;
        }
        .hero > img {
            width: 100%; height: 100%; object-fit: cover;
            object-position: center 40%; display: block;
        }
        .hero-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(0,80,0,0.75) 0%, rgba(0,60,0,0.55) 50%, rgba(0,40,0,0.35) 100%);
            display: flex; align-items: center; padding: 0 6%; gap: 20px;
        }
        .hero-overlay img {
            width: 90px; height: 90px; object-fit: contain;
            filter: drop-shadow(0 3px 8px rgba(0,0,0,0.3));
        }
        .hero-text h1 {
            font-size: 28px; font-weight: 800; color: #fff;
            text-shadow: 2px 3px 6px rgba(0,0,0,0.3);
            line-height: 1.2; margin-bottom: 4px;
        }
        .hero-text p {
            font-size: 14px; color: rgba(255,255,255,0.9);
            text-shadow: 1px 1px 4px rgba(0,0,0,0.3);
        }

        /* ====== CONTAINER ====== */
        .main-content { 
            max-width: 1200px; width: 100%;
            margin: 0 auto; padding: 0 5% 40px; flex: 1; 
        }

        .section-title {
            font-size: 22px; font-weight: 700; color: #1a1a2e;
            margin-bottom: 20px; position: relative; padding-left: 14px;
        }
        .section-title::before {
            content: ''; position: absolute; left: 0; top: 50%;
            transform: translateY(-50%); width: 5px; height: 26px;
            background: linear-gradient(180deg, #28a745, var(--brand-yellow));
            border-radius: 3px;
        }

        /* ====== 3 KARTU STATISTIK ====== */
        .stat-grid {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 20px; margin-bottom: 40px;
        }

        .stat-card {
            border-radius: 14px; overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            transition: all 0.3s ease; border: 3px solid;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 28px rgba(0,0,0,0.15); }

        .stat-card.blue { border-color: #2196f3; }
        .stat-card.blue .stat-header { background: #2196f3; color: #fff; }
        .stat-card.blue .stat-value { color: #1565c0; }
        .stat-card.blue .stat-link { color: #2196f3; }

        .stat-card.orange { border-color: #ff9800; }
        .stat-card.orange .stat-header { background: #ff9800; color: #fff; }
        .stat-card.orange .stat-value { color: #e65100; }
        .stat-card.orange .stat-link { color: #ff9800; }

        .stat-card.green { border-color: #28a745; }
        .stat-card.green .stat-header { background: #28a745; color: #fff; }
        .stat-card.green .stat-value { color: #1b7a2b; }
        .stat-card.green .stat-link { color: #28a745; }

        .stat-header { padding: 12px 20px; font-weight: 700; font-size: 14px; letter-spacing: 0.3px; }
        .stat-body { background: #fff; padding: 24px 20px; text-align: center; }
        .stat-value { font-size: 42px; font-weight: 800; line-height: 1; margin-bottom: 4px; }
        .stat-label { font-size: 13px; color: #888; font-weight: 600; letter-spacing: 1px; }
        .stat-link {
            display: block; padding: 12px; background: #f8f9fa; text-align: center;
            font-size: 13px; font-weight: 700; border-top: 1px solid #eee; transition: all 0.3s;
        }
        .stat-link:hover { background: #e9ecef; }

        /* ====== TABEL RIWAYAT ====== */
        .table-wrapper {
            background: #fff; border-radius: 14px; overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .table-header {
            background: linear-gradient(135deg, var(--brand-green) 0%, #28a745 100%);
            padding: 14px 20px; display: grid;
            grid-template-columns: 1.2fr 1.2fr 3fr 1fr 0.8fr;
            gap: 12px; color: #fff; font-weight: 700; font-size: 13px;
        }

        .table-header > div {
            padding: 6px 12px; border-radius: 8px;
            text-align: center; border: 2px solid rgba(255,255,255,0.3);
        }
        .th-kategori    { background: #2ea3a1; }
        .th-tanggal     { background: #e39f3b; }
        .th-deskripsi   { background: #1e6b8c; }
        .th-status      { background: #5cb85c; }
        .th-aksi        { background: #d9534f; }

        .table-body { padding: 8px 0; }

        .table-row {
            display: grid; grid-template-columns: 1.2fr 1.2fr 3fr 1fr 0.8fr;
            gap: 12px; padding: 14px 20px; align-items: center;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
        }
        .table-row:last-child { border-bottom: none; }
        .table-row:hover { background: #f9fdfb; }

        /* ====== CHIP KATEGORI (PASTEL - SESUAI LAMPIRAN 2) ====== */
        .chip-kategori {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 700;
            border: 2px solid; width: max-content; min-width: 100%;
            white-space: nowrap;
        }

        .chip-kebersihan { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
        .chip-infrastruktur { background: #fff3e0; border-color: #ff9800; color: #e65100; }
        .chip-kesehatan { background: #e3f2fd; border-color: #2196f3; color: #1565c0; }
        .chip-keamanan { background: #fce4ec; border-color: #e91e63; color: #c2185b; }
        .chip-pendidikan { background: #f3e5f5; border-color: #9c27b0; color: #7b1fa2; }
        .chip-sosial { background: #e0f2f1; border-color: #009688; color: #00695c; }
        .chip-lingkungan { background: #f1f8e9; border-color: #8bc34a; color: #558b2f; }
        .chip-administrasi { background: #ede7f6; border-color: #673ab7; color: #4527a0; }

        .cell-tanggal { font-size: 13px; font-weight: 600; color: #333; text-align: center; }
        .cell-deskripsi { font-size: 13px; color: #555; }
        .cell-status { text-align: center; }
        .cell-aksi { text-align: center; }

        .tag-status {
            display: inline-block; padding: 5px 14px;
            border-radius: 16px; font-size: 11px; font-weight: 700;
            border: 2px solid;
        }
        .status-dibalas { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
        .status-menunggu { background: #fce4ec; border-color: #e91e63; color: #c2185b; }

        .btn-edit {
            display: inline-block; padding: 6px 14px;
            background: transparent; border: 2px solid #999;
            border-radius: 8px; font-size: 12px; font-weight: 700;
            color: #666; cursor: pointer; transition: all 0.25s;
        }
        .btn-edit:hover { background: var(--brand-green); border-color: var(--brand-green); color: #fff; }

        .empty-row, .loading-row {
            text-align: center; padding: 40px; color: #999; font-size: 14px;
        }

        .lihat-semua {
            display: block; text-align: right; padding: 8px 20px;
            color: var(--brand-green); font-weight: 700; font-size: 13px;
        }
        .lihat-semua:hover { text-decoration: underline; }

        /* ====== CARD GRID ====== */
        .aduan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
            margin-top: 40px;
        }

        .aduan-card {
            background: #fff; border: 2px solid #e0e6ed; border-radius: 14px;
            overflow: hidden; transition: all 0.3s ease;
            display: flex; flex-direction: column;
        }
        .aduan-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.1); border-color: #28a745; }
        
        .aduan-card-image { width: 100%; height: 180px; overflow: hidden; background: #f0f0f0; }
        .aduan-card-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
        
        .aduan-card-body { padding: 16px 18px; flex: 1; display: flex; flex-direction: column; }
        .aduan-card-tags { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; gap: 8px; }
        
        /* CHIP KATEGORI DI CARD */
        .tag-kategori-card { 
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; border: 2px solid;
        }

        .aduan-card-desc { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 14px; line-height: 1.5; }
        
        .aduan-card-footer { display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding-top: 12px; border-top: 1px solid #f0f0f0; }
        .aduan-author { display: flex; align-items: center; gap: 8px; }
        .author-avatar { width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #28a745, #20c997); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 12px; font-weight: 700; }
        .author-name { font-size: 12px; font-weight: 600; color: #555; }
        .aduan-date { font-size: 11px; color: #999; }
        .link-balasan { font-size: 12px; color: #2196f3; font-weight: 600; text-decoration: underline; cursor: pointer; }
        .link-balasan:hover { color: #1565c0; }

        /* ====== FOOTER ====== */
        .footer { 
            background: #1a1a2e; color: #a0a0b0; text-align: center; 
            padding: 24px 5%; margin-top: auto; font-size: 13px; width: 100%;
        }
        .footer .brand-village { color: #fff; }
        .footer .brand-hub { color: var(--brand-yellow); }

        /* ====== RESPONSIVE ====== */
        @media (max-width: 1100px) {
            .navbar-menu a { padding: 8px 10px; font-size: 12px; }
        }
        @media (max-width: 1000px) {
            .navbar-menu { display: none; }
        }
        @media (max-width: 900px) {
            .table-header, .table-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            .table-header > div { text-align: left; }
            .chip-kategori { width: max-content; }
        }
        @media (max-width: 768px) {
            .navbar-logo .brand-name { font-size: 18px; }
            .navbar-logo img { width: 40px; height: 40px; }
            .btn-header { padding: 7px 14px; font-size: 12px; }
            .stat-grid { grid-template-columns: 1fr; }
            .hero { height: 160px; }
            .hero-overlay img { width: 60px; height: 60px; }
            .hero-text h1 { font-size: 20px; }
            .hero-text p { font-size: 12px; }
            .stat-value { font-size: 34px; }
            .nav-hello { display: none; }
            .aduan-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- ====== NAVBAR ====== -->
    <nav class="navbar">
        <div class="navbar-left">
            <a href="../index.php" class="navbar-logo">
                <img src="../asset/Logo_village_Hub-removebg-preview.png" alt="VillageHub Logo">
                <div class="brand-name"><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></div>
            </a>

            <ul class="navbar-menu">
                <li><a href="beranda.php" class="active">Beranda</a></li>
                <li><a href="buat-aduan.php">Form Aduan</a></li>
                <li><a href="balasan-admin.php">Balasan Admin</a></li>
                <li><a href="riwayat.php">Riwayat</a></li>
                <li><a href="profil.php">Kelola Profil</a></li>
            </ul>
        </div>

        <div class="navbar-actions">
            <div class="nav-hello">
                Halo, <?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?>
                <small><?= ucfirst($user['role']) ?></small>
            </div>
            <button onclick="logoutDanRedirect()" class="btn-header btn-red">Logout</button>
            <?php 
                $fotoProfil = !empty($userData['foto']) ? '../uploads/' . $userData['foto'] : '../asset/default-avatar.png';
            ?>
            <img src="<?= $fotoProfil ?>" alt="Foto Profil" class="nav-avatar" title="<?= htmlspecialchars($user['nama']) ?>">
        </div>
    </nav>

    <!-- ====== HERO ====== -->
    <section class="hero">
        <img src="../asset/Gambar pedesaan.avif" alt="Pemandangan Desa">
        <div class="hero-overlay">
            <img src="../asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
            <div class="hero-text">
                <h1>Selamat Datang, <?= htmlspecialchars($user['nama']) ?>! 👋</h1>
                <p>Pantau aduanmu dan lihat balasan dari pihak desa di sini.</p>
            </div>
        </div>
    </section>

    <main class="main-content">

        <!-- ====== 3 KARTU STATISTIK ====== -->
        <h2 class="section-title">Ringkasan Aduan</h2>
        <div class="stat-grid">
            <div class="stat-card blue">
                <div class="stat-header">Total Aduan</div>
                <div class="stat-body">
                    <div class="stat-value" id="totalAduan">–</div>
                    <div class="stat-label">ADUAN</div>
                </div>
                <a href="riwayat.php" class="stat-link">Lihat Riwayat →</a>
            </div>

            <div class="stat-card orange">
                <div class="stat-header">Menunggu Balasan</div>
                <div class="stat-body">
                    <div class="stat-value" id="menunggu">–</div>
                    <div class="stat-label">ADUAN</div>
                </div>
                <a href="riwayat.php?status=menunggu" class="stat-link">Lihat Riwayat →</a>
            </div>

            <div class="stat-card green">
                <div class="stat-header">Dibalas</div>
                <div class="stat-body">
                    <div class="stat-value" id="dibalas">–</div>
                    <div class="stat-label">ADUAN</div>
                </div>
                <a href="riwayat.php?status=dibalas" class="stat-link">Lihat Riwayat →</a>
            </div>
        </div>

        <!-- ====== CUPLIKAN RIWAYAT (TABEL) ====== -->
        <h2 class="section-title"> 5 Aduan Terbaru</h2>

        <div class="table-wrapper">
            <div class="table-header">
                <div class="th-kategori">Kategori</div>
                <div class="th-tanggal">Tanggal diajukan</div>
                <div class="th-deskripsi">Deskripsi Aduan</div>
                <div class="th-status">Status</div>
                <div class="th-aksi">Aksi</div>
            </div>
            <div class="table-body" id="tableBody">
                <div class="loading-row">Memuat riwayat aduan...</div>
            </div>
        </div>

        <a href="riwayat.php" class="lihat-semua">Lihat Semua Riwayat →</a>

        <!-- ====== TAMBAHAN: CARD GRID ====== -->
        <h2 class="section-title" style="margin-top: 50px;">Aduan Terbaru (Card)</h2>
        <div class="aduan-grid" id="aduanGrid">
            <div class="loading-row">Memuat aduan...</div>
        </div>

    </main>

    <!-- ====== FOOTER ====== -->
    <footer class="footer">
        &copy; 2026 <span class="brand-name footer-brand"><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></span>. Semua hak cipta dilindungi.
    </footer>

    <script src="../assets/api.js?v=14"></script>
    <script>
        // ===== MAPPING ICON & WARNA =====
        const iconMap = {
            'Kebersihan': '🧹', 'Infrastruktur': '🏗️', 'Kesehatan': '🏥',
            'Keamanan': '🛡️', 'Keamanan dan Ketertiban': '🛡️', 'Pendidikan': '📚',
            'Sosial': '🤝', 'Lingkungan': '🌿', 'Lingkungan Hidup': '🌿',
            'Administrasi': '📋', 'Administrasi dan Pelayanan Publik': '📋'
        };

        const chipColorMap = {
            'Kebersihan': 'chip-kebersihan',
            'Infrastruktur': 'chip-infrastruktur',
            'Kesehatan': 'chip-kesehatan',
            'Keamanan dan Ketertiban': 'chip-keamanan',
            'Keamanan': 'chip-keamanan',
            'Pendidikan': 'chip-pendidikan',
            'Sosial': 'chip-sosial',
            'Lingkungan': 'chip-lingkungan',
            'Lingkungan Hidup': 'chip-lingkungan',
            'Administrasi': 'chip-administrasi',
            'Administrasi dan Pelayanan Publik': 'chip-administrasi'
        };

        // =============================================
        // 1. LOAD STATISTIK
        // =============================================
        async function loadStatistik() {
            const res = await getDashboardUser();
            if (res.success) {
                document.getElementById('totalAduan').textContent = res.statistik.total_aduan;
                document.getElementById('menunggu').textContent   = res.statistik.menunggu;
                document.getElementById('dibalas').textContent    = res.statistik.dibalas;
            }
        }

        // =============================================
        // 2. LOAD CUPLIKAN RIWAYAT (TABEL)
        // =============================================
        async function loadCuplikanRiwayat() {
            const res = await getAduanSaya();
            const tbody = document.getElementById('tableBody');

            if (!res.success || !res.data || res.data.length === 0) {
                tbody.innerHTML = `<div class="empty-row">Belum ada aduan. <a href="buat-aduan.php" style="color:#28a745;font-weight:700;">Buat aduan pertama →</a></div>`;
                return;
            }

            const list = res.data.slice(0, 5);

            tbody.innerHTML = list.map(a => {
                const statusClass = a.status === 'dibalas' ? 'status-dibalas' : 'status-menunggu';
                const statusLabel = a.status === 'dibalas' ? 'Dibalas' : 'Menunggu';
                
                // Gunakan Chip Warna
                const namaKategori = a.kategori || 'Kebersihan';
                const catClass = chipColorMap[namaKategori] || 'chip-kebersihan';
                const iconKategori = iconMap[namaKategori] || '📌';

                const tanggal = new Date(a.tanggal_waktu).toLocaleDateString('id-ID', {
                    day: '2-digit', month: '2-digit', year: 'numeric'
                }).replace(/\//g, ' - ');

                return `
                    <div class="table-row">
                        <div><span class="chip-kategori ${catClass}">${iconKategori} ${namaKategori}</span></div>
                        <div class="cell-tanggal">${tanggal}</div>
                        <div class="cell-deskripsi">${(a.deskripsi || '').substring(0, 100)}${a.deskripsi.length > 100 ? '...' : ''}</div>
                        <div class="cell-status"><span class="tag-status ${statusClass}">${statusLabel}</span></div>
                        <div class="cell-aksi">
                            <a href="riwayat.php?id=${a.id_aduan}" class="btn-edit">Lihat ></a>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // =============================================
        // 3. LOAD CARD GRID
        // =============================================
        const currentUserName = "<?= htmlspecialchars($user['nama']) ?>"; 

        async function loadCardGrid() {
            const res = await getAduanSaya();
            const grid = document.getElementById('aduanGrid');

            if (!res.success || !res.data || res.data.length === 0) {
                grid.innerHTML = `<div class="empty-row">Belum ada aduan untuk ditampilkan.</div>`;
                return;
            }

            const list = res.data.slice(0, 5);

            grid.innerHTML = list.map(a => {
                const statusClass = a.status === 'dibalas' ? 'status-dibalas' : 'status-menunggu';
                const statusLabel = a.status === 'dibalas' ? 'Dibalas' : 'Menunggu';
                
                // Gunakan Chip Warna
                const namaKategori = a.kategori || 'Kebersihan';
                const catClass = chipColorMap[namaKategori] || 'chip-kebersihan';
                const iconKategori = iconMap[namaKategori] || '📌';

                const inisial     = currentUserName.charAt(0).toUpperCase();
                const tanggal     = new Date(a.tanggal_waktu).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                const foto        = a.foto ? `../uploads/${a.foto}` : '../asset/Gambar pedesaan.avif';

                return `
                    <div class="aduan-card">
                        <div class="aduan-card-image">
                            <img src="${foto}" alt="Aduan ${namaKategori}">
                        </div>
                        <div class="aduan-card-body">
                            <div class="aduan-card-tags">
                                <span class="tag-kategori-card ${catClass}">${iconKategori} ${namaKategori}</span>
                                <span class="tag-status ${statusClass}">${statusLabel}</span>
                            </div>
                            <p class="aduan-card-desc">${(a.deskripsi || '').substring(0, 80)}...</p>
                            <div class="aduan-card-footer">
                                <div class="aduan-author">
                                    <div class="author-avatar">${inisial}</div>
                                    <span class="author-name">${currentUserName}</span>
                                </div>
                                <span class="aduan-date">${tanggal}</span>
                                <a href="riwayat.php" class="link-balasan">Lihat balasan</a>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // =============================================
        // 4. AUTO-REFRESH
        // =============================================
        async function refreshAll() {
            await Promise.all([loadStatistik(), loadCuplikanRiwayat(), loadCardGrid()]);
        }

        document.addEventListener('DOMContentLoaded', () => {
            refreshAll();
            setInterval(refreshAll, 8000);
        });
    </script>

</body>
</html>