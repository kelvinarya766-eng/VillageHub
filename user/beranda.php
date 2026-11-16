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
    
    <!-- BOOTSTRAP 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- GOOGLE FONTS: NUNITO -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --brand-green: #1b7a2b;
            --brand-yellow: #ffc107;
        }

        /* Menerapkan Font Nunito ke seluruh halaman */
        body {
            font-family: 'Nunito', 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ====== NAVBAR BOOTSTRAP ====== */
        .navbar-custom {
            background: linear-gradient(135deg, var(--brand-green) 0%, #28a745 50%, #2d8f3e 100%);
            box-shadow: 0 3px 16px rgba(0,0,0,0.15);
        }
        .navbar-brand img { width: 50px !important; height: 50px !important; object-fit: contain; margin-right: 10px; }
        .brand-name { font-weight: 800; font-size: 22px; letter-spacing: 0.5px; font-family: 'Nunito', sans-serif; }
        .brand-village { color: #fff; }
        .brand-hub { color: var(--brand-yellow); }

        /* Styling Menu Navbar */
        .navbar-nav .nav-link { 
            color: #fff; 
            padding: 8px 16px;
            border-radius: 8px; 
            margin: 0 3px; 
            font-size: 14px; 
            font-weight: 500; 
            transition: all 0.3s ease; 
            border-bottom: 3px solid transparent; 
        }
        
        /* Menu saat aktif */
        .navbar-nav .nav-link.active { 
            background-color: rgba(255, 255, 255, 0.2); 
            font-weight: 600; 
            color: #fff; 
        }
        
        /* Efek saat kursor diarahkan */
        .navbar-nav .nav-link:hover { 
            color: var(--brand-yellow); 
            border-bottom-color: var(--brand-yellow); 
        }

        /* Profil & Logout */
        .profile-link { display: flex; align-items: center; text-decoration: none; }
        .nav-hello { color: #fff; font-weight: 600; font-size: 14px; margin-right: 10px; line-height: 1.2; text-align: right; }
        .nav-hello small { display: block; font-weight: 400; font-size: 11px; opacity: 0.85; }
        .nav-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; transition: transform 0.2s ease; }
        .profile-link:hover .nav-avatar { transform: scale(1.1); border-color: var(--brand-yellow); }
        
        .btn-logout { 
            background: #c62828; 
            color: #ffffff !important; 
            border: none !important; 
            font-weight: 600; 
            border-radius: 8px; 
            padding: 8px 20px; 
            text-decoration: none !important;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover { 
            background: #a31515; 
            color: #ffffff !important; 
            text-decoration: none !important;
            border-bottom: none !important;
        }

        /* ====== HERO BANNER ====== */
        .hero { position: relative; width: 100%; height: 220px; overflow: hidden; }
        .hero > img { width: 100%; height: 100%; object-fit: cover; object-position: center 40%; }
        .hero-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(0,80,0,0.75) 0%, rgba(0,60,0,0.55) 50%, rgba(0,40,0,0.35) 100%);
            display: flex; align-items: center; padding: 0 5%; gap: 20px;
        }
        .hero-overlay img { width: 90px; height: 90px; object-fit: contain; }
        .hero-text h1 { font-size: 28px; font-weight: 800; color: #fff; text-shadow: 2px 3px 6px rgba(0,0,0,0.3); margin-bottom: 4px; }
        .hero-text p { font-size: 14px; color: rgba(255,255,255,0.9); text-shadow: 1px 1px 4px rgba(0,0,0,0.3); }

        /* ====== KONTEN UTAMA ====== */
        .main-content { max-width: 1200px; margin: 0 auto; padding: 30px 5%; flex: 1; width: 100%; }

        .section-title {
            font-size: 22px; font-weight: 800; color: #1a1a2e;
            margin-bottom: 20px; position: relative; padding-left: 14px;
        }
        .section-title::before {
            content: ''; position: absolute; left: 0; top: 50%;
            transform: translateY(-50%); width: 5px; height: 26px;
            background: linear-gradient(180deg, #28a745, var(--brand-yellow));
            border-radius: 3px;
        }

        /* ====== STATISTIK CARD ====== */
        .stat-card {
            border-radius: 14px; overflow: hidden; border: 3px solid;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08); transition: all 0.3s ease;
            background: #fff;
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

        .stat-header { padding: 12px 20px; font-weight: 700; font-size: 14px; }
        .stat-body { padding: 24px 20px; text-align: center; }
        .stat-value { font-size: 42px; font-weight: 800; line-height: 1; margin-bottom: 4px; }
        .stat-label { font-size: 13px; color: #888; font-weight: 600; }
        .stat-link { display: block; padding: 12px; background: #f8f9fa; text-align: center; font-size: 13px; font-weight: 700; border-top: 1px solid #eee; text-decoration: none; }
        .stat-link:hover { background: #e9ecef; }

        /* ====== TABEL & CARD ADUAN ====== */
        .table-custom th { background: var(--brand-green); color: #fff; text-align: center; }
        .table-custom td { vertical-align: middle; }

        .chip-kategori {
            display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; border: 2px solid;
        }
        .chip-kebersihan { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
        .chip-infrastruktur { background: #fff3e0; border-color: #ff9800; color: #e65100; }
        .chip-kesehatan { background: #e3f2fd; border-color: #2196f3; color: #1565c0; }
        .chip-keamanan { background: #fce4ec; border-color: #e91e63; color: #c2185b; }
        .chip-pendidikan { background: #f3e5f5; border-color: #9c27b0; color: #7b1fa2; }
        .chip-sosial { background: #e0f2f1; border-color: #009688; color: #00695c; }
        .chip-lingkungan { background: #f1f8e9; border-color: #8bc34a; color: #558b2f; }
        .chip-administrasi { background: #ede7f6; border-color: #673ab7; color: #4527a0; }

        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; border: 2px solid; display: inline-block; }
        .status-dibalas { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
        .status-menunggu { background: #fff3e0; border-color: #ff9800; color: #e65100; }

        .aduan-card {
            border: 2px solid #e0e6ed; border-radius: 14px; overflow: hidden;
            transition: all 0.3s ease; display: flex; flex-direction: column; height: 100%; background: #fff;
        }
        .aduan-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.1); border-color: #28a745; }
        .aduan-card-image { height: 180px; overflow: hidden; cursor: pointer; }
        .aduan-card-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
        .aduan-card-image:hover img { transform: scale(1.05); }

        /* ====== FOOTER BARU (MEGA FOOTER) ====== */
        .footer-mega {
            background-color: #1a1a2e; 
            color: #a0a0b0;
            padding: 50px 5% 20px 5%;
            font-family: 'Nunito', sans-serif;
            margin-top: auto;
        }
        .footer-mega h5 { font-weight: 800; font-size: 18px; margin-bottom: 20px; color: #fff; }
        .footer-mega p, .footer-mega a { font-size: 14px; color: #a0a0b0; text-decoration: none; line-height: 1.8; display: block; margin-bottom: 8px; }
        .footer-mega a:hover { color: var(--brand-yellow); text-decoration: underline; }
        .footer-mega .footer-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; font-size: 18px; font-weight: 700; color: #fff; }
        .footer-mega .footer-brand img { width: 40px; height: 40px; object-fit: contain; }
        .footer-mega .social-media a { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }

        .footer-copyright {
            background-color: #141424; color: #a0a0b0; text-align: center;
            padding: 15px 0; font-size: 13px; font-weight: 600;
        }
        .brand-village { color: #ffffff; font-weight: 800; }
        .brand-hub { color: var(--brand-yellow); font-weight: 800; }
        .hub { color: var(--brand-yellow); font-weight: 800; }
        .bi-instagram { color: #E1306C; }
    </style>
</head>
<body>

    <!-- NAVBAR BOOTSTRAP -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container">
            
            <!-- KIRI: Logo -->
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <img src="../asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
                <span class="brand-name"><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></span>
            </a>
            
            <!-- KANAN: Profil, Logout Desktop, & Tombol Titik Tiga -->
            <div class="d-flex align-items-center order-lg-3 gap-3">
                
                <!-- Foto Profil -->
                <a href="profil.php" class="profile-link order-1 order-lg-2" title="Kelola Profil">
                    <div class="nav-hello d-none d-lg-block">
                        Halo, <?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?>
                        <small><?= ucfirst($user['role']) ?></small>
                    </div>
                    <?php 
                        $fotoProfil = !empty($userData['foto']) ? '../uploads/' . $userData['foto'] : '../asset/default-avatar.png';
                    ?>
                    <img src="<?= $fotoProfil ?>" alt="Foto" class="nav-avatar">
                </a>

                <!-- Tombol Logout untuk Desktop -->
                <a href="../logout.php" class="btn-logout d-none d-lg-block order-3 order-lg-3" onclick="return confirm('Apakah anda yakin ingin keluar?');">Logout</a>

                <!-- Tombol Titik Tiga (Hamburger) -->
                <button class="navbar-toggler border-0 order-2 order-lg-4" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <!-- MENU DROPDOWN -->
            <div class="collapse navbar-collapse order-lg-2" id="navbarNav">
                
                <!-- Menu Navigasi (Tengah) -->
                <ul class="navbar-nav mx-auto mb-3 mb-lg-0 text-center align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="beranda.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="buat-aduan.php">Form Aduan</a></li>
                    <li class="nav-item"><a class="nav-link" href="balasan-admin.php">Balasan Admin</a></li>
                    <li class="nav-item"><a class="nav-link" href="riwayat.php">Riwayat</a></li>
                    <li class="nav-item"><a class="nav-link" href="profil.php">Kelola Profil</a></li>
                    
                    <!-- Tombol Logout untuk HP (Tetap di dalam dropdown) -->
                    <li class="nav-item d-lg-none mt-3 w-100">
                        <a href="../logout.php" class="btn-logout w-100 d-block" onclick="return confirm('Apakah anda yakin ingin keluar?');">Logout</a>
                    </li>
                </ul>

            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero">
        <img src="../asset/Gambar pedesaan.avif" alt="Pemandangan Desa">
        <div class="hero-overlay">
            <img src="../asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
            <div class="hero-text">
                <h1>Selamat Datang, <?= htmlspecialchars($user['nama']) ?></h1>
                <p>Pantau aduanmu dan lihat balasan dari pihak desa di sini.</p>
            </div>
        </div>
    </section>

    <main class="main-content">

        <!-- 3 KARTU STATISTIK -->
        <h2 class="section-title">Ringkasan Aduan</h2>
        <div class="row g-3 mb-5">
            <div class="col-12 col-md-4">
                <div class="stat-card blue">
                    <div class="stat-header">Total Aduan</div>
                    <div class="stat-body">
                        <div class="stat-value" id="totalAduan">–</div>
                        <div class="stat-label">ADUAN</div>
                    </div>
                    <a href="riwayat.php" class="stat-link">Lihat Riwayat →</a>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card orange">
                    <div class="stat-header">Menunggu Balasan</div>
                    <div class="stat-body">
                        <div class="stat-value" id="menunggu">–</div>
                        <div class="stat-label">ADUAN</div>
                    </div>
                    <a href="riwayat.php?status=menunggu" class="stat-link">Lihat Riwayat →</a>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card green">
                    <div class="stat-header">Dibalas</div>
                    <div class="stat-body">
                        <div class="stat-value" id="dibalas">–</div>
                        <div class="stat-label">ADUAN</div>
                    </div>
                    <a href="riwayat.php?status=dibalas" class="stat-link">Lihat Riwayat →</a>
                </div>
            </div>
        </div>

        <!-- CUPLIKAN RIWAYAT (TABEL RESPONSIVE) -->
        <h2 class="section-title">5 Aduan Terbaru</h2>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-hover table-custom">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr><td colspan="5" class="text-center py-4 text-muted">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="text-end mb-5">
            <a href="riwayat.php" class="fw-bold text-success text-decoration-none">Lihat Semua Riwayat →</a>
        </div>

        <!-- GRID CARD ADUAN -->
        <h2 class="section-title">Aduan Terbaru</h2>
        <div class="row g-4" id="aduanGrid">
            <div class="col-12 text-center py-4 text-muted">Memuat aduan...</div>
        </div>

    </main>

     <!-- FOOTER BARU (4 KOLOM) -->
    <footer class="footer-mega">
        <div class="container-fluid">
            <div class="row">
                
                <!-- Kolom 1: Info Brand -->
                <div class="col-12 col-md-3 mb-4">
                    <div class="footer-brand">
                        <img src="../asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
                       <span><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></span>
                    </div>
                    <p><strong>Sistem Pengaduan Desa</strong></p>
                    <p>Jawa Tengah 57143</p>
                    <p>Telp. +62 889-85493525</p>
                </div>

                <!-- Kolom 2: Menu Cepat -->
                <div class="col-12 col-md-3 mb-4">
                    <h5>Program & Layanan</h5>
                    <a href="beranda.php">Pengaduan Warga</a>
                    <a href="buat-aduan.php">Buat Aduan Baru</a>
                    <a href="riwayat.php">Riwayat Aduan</a>
                    <a href="profil.php">Pengaturan Profil</a>
                    <a href="https://wa.me/6288985493525">Bantuan</a>
                </div>

                <!-- Kolom 3: Informasi -->
                <div class="col-12 col-md-3 mb-4">
                    <h5>Informasi</h5>
                      <p><strong>VillageHub Support</strong></p>
                    <p>+62 889-8549-3525</p>
                </div>

                <!-- Kolom 4: Sosial Media -->
                <div class="col-12 col-md-3 mb-4">
                    <h5>Info Pengembang</h5>
                    <div class="social-media">
                          <h6>Instagram</h6>
                 	    <a href="https://www.instagram.com/klvinarp_?cplk=MTRvOHE4bTFsNTkxdg%3D%3D"><i class="bi bi-instagram"></i>@Klvinarp_</a>
                    </div>
                </div>

            </div>
        </div>
    </footer>
     <!-- BARIS COPYRIGHT -->
    <div class="footer-copyright">
        &copy; 2026 <span class="footer-brand"><span style="color:#fff;">VILLAGE</span> <span class="hub">HUB</span></span>. Semua hak cipta dilindungi.
    </div>

    <!-- ========================================== -->
    <!-- MODAL LIHAT FOTO (DENGAN BINGKAI) -->
    <!-- ========================================== -->
    <div class="modal fade" id="modalLihatFoto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">Foto Aduan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- BODY DENGAN LATAR ABU-ABU -->
                <div class="modal-body text-center" style="background-color: #f0f2f5; padding: 30px; border-radius: 0 0 8px 8px;">
                    <!-- GAMBAR DENGAN BINGKAI PUTIH -->
                    <img id="imgModalFoto" src="" class="rounded shadow-sm" style="max-height: 70vh; max-width: 100%; object-fit: contain; padding: 10px; background-color: #ffffff; border: 1px solid #ddd;" alt="Foto Besar">
                </div>
            </div>
        </div>
    </div>

    <!-- BOOTSTRAP JS & SCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/api.js?v=14"></script>
    <script>
        const iconMap = {
            'Kebersihan': '🧹', 'Infrastruktur': '🏗️', 'Kesehatan': '🏥',
            'Keamanan': '🛡️', 'Keamanan dan Ketertiban': '🛡️', 'Pendidikan': '📚',
            'Sosial': '🤝', 'Lingkungan': '🌿', 'Lingkungan Hidup': '🌿',
            'Administrasi': '📋', 'Administrasi dan Pelayanan Publik': '📋'
        };

        const chipColorMap = {
            'Kebersihan': 'chip-kebersihan', 'Infrastruktur': 'chip-infrastruktur',
            'Kesehatan': 'chip-kesehatan', 'Keamanan dan Ketertiban': 'chip-keamanan',
            'Keamanan': 'chip-keamanan', 'Pendidikan': 'chip-pendidikan',
            'Sosial': 'chip-sosial', 'Lingkungan': 'chip-lingkungan',
            'Lingkungan Hidup': 'chip-lingkungan', 'Administrasi': 'chip-administrasi',
            'Administrasi dan Pelayanan Publik': 'chip-administrasi'
        };

        // ==========================================
        // FUNGSI MEMBUKA MODAL FOTO
        // ==========================================
        function bukaModalFoto(urlGambar) {
            document.getElementById('imgModalFoto').src = urlGambar;
            var myModal = new bootstrap.Modal(document.getElementById('modalLihatFoto'));
            myModal.show();
        }

        async function loadStatistik() {
            const res = await getDashboardUser();
            if (res.success) {
                document.getElementById('totalAduan').textContent = res.statistik.total_aduan;
                document.getElementById('menunggu').textContent   = res.statistik.menunggu;
                document.getElementById('dibalas').textContent    = res.statistik.dibalas;
            }
        }

        async function loadCuplikanRiwayat() {
            const res = await getAduanSaya();
            const tbody = document.getElementById('tableBody');

            if (!res.success || !res.data || res.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada aduan. <a href="buat-aduan.php" class="text-success fw-bold">Buat aduan pertama →</a></td></tr>`;
                return;
            }

            const list = res.data.slice(0, 5);
            tbody.innerHTML = list.map(a => {
                const statusClass = a.status === 'dibalas' ? 'status-dibalas' : 'status-menunggu';
                const statusLabel = a.status === 'dibalas' ? 'Dibalas' : 'Menunggu';
                const namaKategori = a.kategori || 'Kebersihan';
                const catClass = chipColorMap[namaKategori] || 'chip-kebersihan';
                const iconKategori = iconMap[namaKategori] || '📌';
                const tanggal = new Date(a.tanggal_waktu).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, ' - ');

                return `
                    <tr>
                        <td class="text-center"><span class="chip-kategori ${catClass}">${iconKategori} ${namaKategori}</span></td>
                        <td class="text-center fw-bold">${tanggal}</td>
                        <td>${(a.deskripsi || '').substring(0, 100)}${a.deskripsi.length > 100 ? '...' : ''}</td>
                        <td class="text-center"><span class="status-badge ${statusClass}">${statusLabel}</span></td>
                        <td class="text-center"><a href="riwayat.php?id=${a.id_aduan}" class="btn btn-sm btn-outline-success fw-bold">Lihat ></a></td>
                    </tr>
                `;
            }).join('');
        }

        const currentUserName = "<?= htmlspecialchars($user['nama']) ?>"; 

        async function loadCardGrid() {
            const res = await getAduanSaya();
            const grid = document.getElementById('aduanGrid');

            if (!res.success || !res.data || res.data.length === 0) {
                grid.innerHTML = `<div class="col-12 text-center py-4 text-muted">Belum ada aduan untuk ditampilkan.</div>`;
                return;
            }

            const list = res.data.slice(0, 5);
            grid.innerHTML = list.map(a => {
                const statusClass = a.status === 'dibalas' ? 'status-dibalas' : 'status-menunggu';
                const statusLabel = a.status === 'dibalas' ? 'Dibalas' : 'Menunggu';
                const namaKategori = a.kategori || 'Kebersihan';
                const catClass = chipColorMap[namaKategori] || 'chip-kebersihan';
                const iconKategori = iconMap[namaKategori] || '📌';
                
                // LOGIKA FOTO PROFIL
                const inisial = currentUserName.charAt(0).toUpperCase();
                const fotoPengadu = a.foto_pengadu ? `../uploads/${a.foto_pengadu}` : null;
                const avatarHtml = fotoPengadu 
                    ? `<img src="${fotoPengadu}" alt="Foto" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">` 
                    : `<div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:28px; height:28px; font-size:12px;">${inisial}</div>`;

                const tanggal = new Date(a.tanggal_waktu).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                const foto = a.foto ? `../uploads/${a.foto}` : '../asset/Gambar pedesaan.avif';

                return `
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="aduan-card">
                            <!-- TAMBAHKAN ONCLICK UNTUK MEMBUKA MODAL -->
                            <div class="aduan-card-image" onclick="bukaModalFoto('${foto}')">
                                <img src="${foto}" alt="Aduan">
                            </div>
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="chip-kategori ${catClass}">${iconKategori} ${namaKategori}</span>
                                    <span class="status-badge ${statusClass}">${statusLabel}</span>
                                </div>
                                <p class="text-dark fw-bold mb-3">${(a.deskripsi || '').substring(0, 80)}...</p>
                                <div class="d-flex align-items-center justify-content-between mt-auto pt-3 border-top">
                                    <div class="d-flex align-items-center gap-2">
                                        ${avatarHtml}
                                        <span class="fw-semibold text-muted" style="font-size: 12px;">${currentUserName}</span>
                                    </div>
                                    <span class="text-muted" style="font-size: 11px;">${tanggal}</span>
                                    <a href="riwayat.php" class="text-primary fw-bold" style="font-size: 12px;">Lihat balasan</a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function refreshAll() {
            await Promise.all([loadStatistik(), loadCuplikanRiwayat(), loadCardGrid()]);
        }

        document.addEventListener('DOMContentLoaded', () => {
            refreshAll();
            setInterval(refreshAll, 60000);
        });
    </script>

</body>
</html>