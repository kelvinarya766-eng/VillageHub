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
    <title>Balasan Admin – VillageHub</title>
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
            display: inline-block; white-space: nowrap;
        }
        .btn-red { background: #c62828; border-color: #c62828; color: #fff; }
        .btn-red:hover { background: #a31515; transform: translateY(-2px); }

        /* ====== MAIN CONTENT ====== */
        .main-content { 
            max-width: 1300px; width: 100%;
            margin: 30px auto; padding: 0 5%; flex: 1; 
        }

        /* Layout 2 Kolom */
        .dashboard-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            align-items: start;
        }

        /* Header Halaman */
        .page-title {
            font-size: 28px; font-weight: 800; color: #1a1a2e; text-transform: uppercase; margin-bottom: 5px;
        }
        .page-subtitle {
            font-size: 14px; color: #555; margin-bottom: 20px; font-weight: 500;
        }
        .filter-bar {
            display: flex; gap: 10px; margin-bottom: 20px;
        }
        .filter-select {
            padding: 8px 12px; border: 2px solid #ccc; border-radius: 8px;
            font-family: 'Poppins', sans-serif; font-size: 13px; background: #fff;
        }

        /* KARTU ADUAN (KIRI) */
        .aduan-card-list {
            display: flex; flex-direction: column; gap: 20px;
        }

        .aduan-card-detail {
            background: #fff; border: 2px solid #e0e6ed; border-radius: 14px;
            overflow: hidden; display: flex; box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        /* Sisi Kiri Kartu (Gambar & Info) */
        .card-left {
            width: 200px; padding: 20px; border-right: 2px solid #eee;
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .card-left .tag-kategori { margin-bottom: 10px; }
        .img-thumb {
            width: 100%; height: 100px; border-radius: 8px; object-fit: cover;
            border: 1px dashed #ccc; background: #fafafa; margin-bottom: 10px;
        }
        .card-left-date { font-size: 12px; color: #888; font-weight: 600; }

        /* Sisi Kanan Kartu (Detail Balasan) */
        .card-right {
            flex: 1; background: var(--brand-green); padding: 20px; color: #fff;
            display: flex; flex-direction: column; gap: 15px;
        }
        .card-right-title { font-size: 20px; font-weight: 800; text-align: center; margin-bottom: 5px; letter-spacing: 1px; }
        
        .balasan-box {
            background: #fff; color: #333; border-radius: 8px; padding: 15px;
            border-left: 6px solid var(--brand-yellow);
        }
        .balasan-header {
            font-size: 12px; font-weight: 700; color: #1b7a2b; margin-bottom: 8px;
        }
        .balasan-text {
            font-size: 13px; border-bottom: 1px solid #ccc; padding-bottom: 20px; margin-bottom: 10px;
            font-style: italic;
        }
        .link-detail {
            font-size: 12px; color: #28a745; font-weight: 700; text-decoration: underline;
        }

        /* SIDEBAR (KANAN) */
        .sidebar { display: flex; flex-direction: column; gap: 20px; }
        
        .panel-card {
            background: #fff; border: 2px solid #e0e6ed; border-radius: 14px; padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .panel-title { font-size: 18px; font-weight: 800; text-align: center; text-transform: uppercase; margin-bottom: 15px; }

        /* Panel Ringkasan */
        .summary-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
        }
        .summary-box {
            text-align: center; border-radius: 8px; padding: 15px 10px; color: #fff;
        }
        .summary-box.bg-green { background: #1b7a2b; border: 3px solid #28a745; }
        .summary-box.bg-yellow { background: #ffc107; border: 3px solid #e0a800; color: #333; }
        .summary-label { font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; }
        .summary-value { font-size: 28px; font-weight: 900; }

        /* Panel Statistik Kategori */
        .cat-stats-list { display: flex; flex-direction: column; gap: 10px; }
        .cat-stat-item {
            display: flex; justify-content: space-between; align-items: center;
        }
        .cat-badge {
            padding: 6px 16px; border-radius: 8px; font-size: 13px; font-weight: 700;
            width: 60%; text-align: center;
        }
        .cat-count { font-size: 24px; font-weight: 900; }

        /* Panel Buat Aduan */
        .cta-card {
            background: #ffc107; border: 3px solid #e0a800; border-radius: 14px;
            padding: 20px; text-align: left;
        }
        .cta-title { font-size: 16px; font-weight: 800; margin-bottom: 8px; color: #1a1a2e; }
        .cta-desc { font-size: 13px; color: #333; margin-bottom: 15px; font-weight: 600; }
        .btn-cta {
            display: block; width: 100%; padding: 12px; background: #1b7a2b; color: #fff;
            text-align: center; border-radius: 20px; font-weight: 800; font-size: 14px;
            text-transform: uppercase;
        }
        .btn-cta:hover { background: #146c24; }

        /* Warna Kategori (Sesuai Lampiran) */
        .cat-Infrastruktur { background: #29b6f6; border: 2px solid #0288d1; color: #fff; }
        .cat-Kesehatan { background: #e53935; border: 2px solid #b71c1c; color: #fff; }
        .cat-Kebersihan { background: #8bc34a; border: 2px solid #558b2f; color: #fff; }
        .cat-Sosial { background: #00acc1; border: 2px solid #00838f; color: #fff; }
        .cat-Keamanan { background: #ffca28; border: 2px solid #f57f17; color: #333; }
        .cat-Lingkungan { background: #7cb342; border: 2px solid #558b2f; color: #fff; }
        .cat-Pendidikan { background: #ab47bc; border: 2px solid #7b1fa2; color: #fff; }
        .cat-Administrasi { background: #673ab7; border: 2px solid #4527a0; color: #fff; }

        /* Responsive */
        @media (max-width: 1000px) {
            .dashboard-layout { grid-template-columns: 1fr; }
            .navbar-menu { display: none; }
        }
        @media (max-width: 768px) {
            .aduan-card-detail { flex-direction: column; }
            .card-left { width: 100%; border-right: none; border-bottom: 2px solid #eee; }
            .nav-hello { display: none; }
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
                <li><a href="beranda.php">Beranda</a></li>
                <li><a href="buat-aduan.php">Form Aduan</a></li>
                <li><a href="balasan-admin.php" class="active">Balasan Admin</a></li>
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
            <img src="<?= $fotoProfil ?>" alt="Foto Profil" class="nav-avatar">
        </div>
    </nav>

    <!-- ====== KONTEN ====== -->
    <main class="main-content">
        <div class="page-title">ADUAN DIBALAS</div>
        <div class="page-subtitle">Seluruh aduan yang telah dibalas admin akan ditampilkan disini</div>
        
        <div class="filter-bar">
            <select class="filter-select" id="filterKategori">
                <option value="0">Semua Kategori</option>
                <!-- Opsi Kategori diisi via JS -->
            </select>
            <select class="filter-select">
                <option>Terbaru Dibalas</option>
            </select>
        </div>

        <div class="dashboard-layout">
            
            <!-- KOLOM KIRI: DAFTAR ADUAN -->
            <div class="aduan-card-list" id="aduanList">
                <div style="text-align:center; padding:20px;">Memuat data...</div>
            </div>

            <!-- KOLOM KANAN: SIDEBAR -->
            <div class="sidebar">
                
                <!-- Panel Ringkasan -->
                <div class="panel-card">
                    <div class="panel-title">RINGKASAN</div>
                    <div class="summary-grid">
                        <div class="summary-box bg-green">
                            <div class="summary-label">TOTAL DIBALAS</div>
                            <div class="summary-value" id="totalDibalas">0</div>
                        </div>
                        <div class="summary-box bg-yellow">
                            <div class="summary-label">MENUNGGU BALASAN</div>
                            <div class="summary-value" id="menungguBalasan">0</div>
                        </div>
                    </div>
                </div>

                <!-- Panel Statistik Kategori -->
                <div class="panel-card">
                    <div class="cat-stats-list" id="statKategori">
                        <!-- Diisi via JS -->
                    </div>
                </div>

                <!-- Panel CTA -->
                <div class="cta-card">
                    <div class="cta-title">Punya masalah di desa?</div>
                    <div class="cta-desc">Sampaikan masalahmu disini, admin desa akan membalasnya diwebsite ini.</div>
                    <a href="buat-aduan.php" class="btn-cta">BUAT ADUAN</a>
                </div>

            </div>
        </div>
    </main>

    <script src="../assets/api.js?v=12"></script>
    <script>
        // ===== MAPPING ICON & WARNA =====
        const iconMap = {
            'Kebersihan': '🧹', 'Infrastruktur': '🏗️', 'Kesehatan': '🏥',
            'Keamanan': '🛡️', 'Keamanan dan Ketertiban': '🛡️', 'Pendidikan': '📚',
            'Sosial': '🤝', 'Lingkungan': '🌿', 'Lingkungan Hidup': '🌿',
            'Administrasi': '📋', 'Administrasi dan Pelayanan Publik': '📋'
        };

        // =============================================
        // 1. LOAD DATA
        // =============================================
        async function loadData() {
            const res = await getAduanSaya();
            const listContainer = document.getElementById('aduanList');
            const statContainer = document.getElementById('statKategori');
            
            if (!res.success || !res.data || res.data.length === 0) {
                listContainer.innerHTML = `<div class="empty-row">Belum ada aduan.</div>`;
                return;
            }

            // Hitung Statistik
            const total = res.data.length;
            const dibalas = res.data.filter(a => a.status === 'dibalas').length;
            const menunggu = res.data.filter(a => a.status === 'menunggu').length;

            document.getElementById('totalDibalas').textContent = dibalas;
            document.getElementById('menungguBalasan').textContent = menunggu;

            // Hitung Statistik Per Kategori
            const catCount = {};
            res.data.forEach(a => {
                catCount[a.kategori] = (catCount[a.kategori] || 0) + 1;
            });

            // Render Statistik Kategori
            let statHtml = '';
            const topCats = Object.entries(catCount).sort((a,b) => b[1] - a[1]).slice(0, 3); // Ambil 3 teratas
            topCats.forEach(([nama, jumlah]) => {
                const slug = nama.toLowerCase().split(' ')[0];
                const catClass = 'cat-' + slug;
                statHtml += `
                    <div class="cat-stat-item">
                        <div class="cat-badge ${catClass}">${nama}</div>
                        <div class="cat-count">${jumlah}</div>
                    </div>
                `;
            });
            statContainer.innerHTML = statHtml;

            // Filter hanya yang dibalas untuk ditampilkan di kiri
            const listDibalas = res.data.filter(a => a.status === 'dibalas');
            
            if (listDibalas.length === 0) {
                listContainer.innerHTML = `<div style="text-align:center; padding:20px; background:#fff; border-radius:10px;">Belum ada aduan yang dibalas.</div>`;
                return;
            }

            // Tampilkan List Card
            let cardHtml = '';
            // Looping ambil detail balasan
            for (const [index, a] of listDibalas.entries()) {
                const detailRes = await getAduanDetail(a.id_aduan);
                let teksBalasan = "Balasan belum tersedia.";
                if (detailRes.success && detailRes.data.balasan && detailRes.data.balasan.length > 0) {
                    teksBalasan = detailRes.data.balasan[0].balasan;
                }

                const slug = (a.kategori || 'kebersihan').toLowerCase().split(' ')[0];
                const catClass = 'cat-' + slug;
                const iconKategori = iconMap[a.kategori] || '📌';
                const tgl = new Date(a.tanggal_waktu).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-');
                const foto = a.foto ? `../uploads/${a.foto}` : '../asset/Gambar pedesaan.avif';
                const noAduan = String(index + 1).padStart(3, '0');

                cardHtml += `
                    <div class="aduan-card-detail">
                        <!-- Sisi Kiri -->
                        <div class="card-left">
                            <div>
                                <div class="tag-kategori ${catClass}" style="text-align:center; font-weight:700; font-size:12px; padding:4px 10px; border-radius:6px;">
                                    ${iconKategori} ${a.kategori}
                                </div>
                                <img src="${foto}" class="img-thumb" alt="Foto">
                            </div>
                            <div class="card-left-date">Adit S &nbsp;&nbsp; ${tgl}</div>
                        </div>
                        
                        <!-- Sisi Kanan -->
                        <div class="card-right">
                            <div class="card-right-title">ADUAN ${noAduan}</div>
                            <div class="balasan-box">
                                <div class="balasan-header">Balasan Admin &nbsp;&nbsp; [nama admin] &nbsp;&nbsp; [tanggal dibalas]</div>
                                <div class="balasan-text">"${teksBalasan}"</div>
                                <a href="riwayat.php" class="link-detail">Lihat detail lengkap</a>
                            </div>
                        </div>
                    </div>
                `;
            }
            listContainer.innerHTML = cardHtml;
        }

        // INIT
        document.addEventListener('DOMContentLoaded', () => {
            loadData();
        });
    </script>
</body>
</html>