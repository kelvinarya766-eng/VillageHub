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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- GOOGLE FONTS: NUNITO -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root { --brand-green: #1b7a2b; --brand-yellow: #ffc107; }
        body {
            font-family: 'Nunito', 'Segoe UI', sans-serif;
            background-color: #f0f2f5; color: #333;
            min-height: 100vh; display: flex; flex-direction: column;
        }
        a { text-decoration: none; color: inherit; }

        .navbar-custom {
            background: linear-gradient(135deg, var(--brand-green) 0%, #28a745 50%, #2d8f3e 100%);
            box-shadow: 0 3px 16px rgba(0,0,0,0.15);
        }
        .navbar-brand img { width: 50px !important; height: 50px !important; object-fit: contain; margin-right: 10px; }
        .brand-name { font-weight: 800; font-size: 22px; letter-spacing: 0.5px; }
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
        .navbar-nav .nav-link.active { 
            background-color: rgba(255, 255, 255, 0.2); 
            font-weight: 600;
            color: #fff; 
        }
        .navbar-nav .nav-link:hover { 
            color: var(--brand-yellow); 
            border-bottom-color: var(--brand-yellow); 
        }

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

        .main-content { max-width: 1300px; width: 100%; margin: 30px auto; padding: 0 5%; flex: 1; }
        .dashboard-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start; }
        .page-title { font-size: 28px; font-weight: 800; color: #1a1a2e; text-transform: uppercase; margin-bottom: 5px; }
        .page-subtitle { font-size: 14px; color: #555; margin-bottom: 20px; font-weight: 500; }
        .filter-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-select { padding: 8px 12px; border: 2px solid #ccc; border-radius: 8px; font-family: 'Nunito', sans-serif; font-size: 13px; background: #fff; width: 100%; max-width: 250px; }
        .aduan-card-list { display: flex; flex-direction: column; gap: 20px; }
        .aduan-card-detail { background: #fff; border: 2px solid #e0e6ed; border-radius: 14px; overflow: hidden; display: flex; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .card-left { width: 200px; padding: 20px; border-right: 2px solid #eee; display: flex; flex-direction: column; justify-content: space-between; }
        .card-left .tag-kategori { margin-bottom: 10px; }
        .img-thumb { width: 100%; height: 100px; border-radius: 8px; object-fit: cover; border: 1px dashed #ccc; background: #fafafa; margin-bottom: 10px; }
        .card-left-date { font-size: 12px; color: #888; font-weight: 600; }
        .card-right { flex: 1; background: var(--brand-green); padding: 20px; color: #fff; display: flex; flex-direction: column; gap: 15px; }
        .card-right-title { font-size: 20px; font-weight: 800; text-align: center; margin-bottom: 5px; letter-spacing: 1px; }
        .balasan-box { background: #fff; color: #333; border-radius: 8px; padding: 15px; border-left: 6px solid var(--brand-yellow); }
        .balasan-header { font-size: 12px; font-weight: 700; color: #1b7a2b; margin-bottom: 8px; }
        .balasan-text { font-size: 13px; border-bottom: 1px solid #ccc; padding-bottom: 20px; margin-bottom: 10px; font-style: italic; }
        .link-detail { font-size: 12px; color: #28a745; font-weight: 700; text-decoration: underline; }
        .sidebar { display: flex; flex-direction: column; gap: 20px; }
        .panel-card { background: #fff; border: 2px solid #e0e6ed; border-radius: 14px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .panel-title { font-size: 18px; font-weight: 800; text-align: center; text-transform: uppercase; margin-bottom: 15px; }
        .summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .summary-box { text-align: center; border-radius: 8px; padding: 15px 10px; color: #fff; }
        .summary-box.bg-green { background: #1b7a2b; border: 3px solid #28a745; }
        .summary-box.bg-yellow { background: #ffc107; border: 3px solid #e0a800; color: #333; }
        .summary-label { font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; }
        .summary-value { font-size: 28px; font-weight: 900; }
        .cat-stats-list { display: flex; flex-direction: column; gap: 10px; }
        .cat-stat-item { display: flex; justify-content: space-between; align-items: center; }
        .cat-badge { padding: 6px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; width: 60%; text-align: center; }
        .cat-count { font-size: 24px; font-weight: 900; }
        .cta-card { background: #ffc107; border: 3px solid #e0a800; border-radius: 14px; padding: 20px; text-align: left; }
        .cta-title { font-size: 16px; font-weight: 800; margin-bottom: 8px; color: #1a1a2e; }
        .cta-desc { font-size: 13px; color: #333; margin-bottom: 15px; font-weight: 600; }
        .btn-cta { display: block; width: 100%; padding: 12px; background: #1b7a2b; color: #fff; text-align: center; border-radius: 20px; font-weight: 800; font-size: 14px; text-transform: uppercase; }
        .btn-cta:hover { background: #146c24; }

        .cat-Infrastruktur { background: #29b6f6; border: 2px solid #0288d1; color: #fff; }
        .cat-Kesehatan { background: #e53935; border: 2px solid #b71c1c; color: #fff; }
        .cat-Kebersihan { background: #8bc34a; border: 2px solid #558b2f; color: #fff; }
        .cat-Sosial { background: #00acc1; border: 2px solid #00838f; color: #fff; }
        .cat-Keamanan { background: #ffca28; border: 2px solid #f57f17; color: #333; }
        .cat-Lingkungan { background: #7cb342; border: 2px solid #558b2f; color: #fff; }
        .cat-Pendidikan { background: #ab47bc; border: 2px solid #7b1fa2; color: #fff; }
        .cat-Administrasi { background: #673ab7; border: 2px solid #4527a0; color: #fff; }

        @media (max-width: 1000px) {
            .dashboard-layout { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .aduan-card-detail { flex-direction: column; }
            .card-left { width: 100%; border-right: none; border-bottom: 2px solid #eee; }
            .filter-select { max-width: 100%; }
        }

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
     <!-- BOOTSTRAP 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- TAMBAHKAN BARIS INI UNTUK MEMUNCULKAN IKON -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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

                <!-- Tombol Logout untuk Desktop (Dipindah ke kanan profil) -->
                <a href="../logout.php" class="btn-logout d-none d-lg-block order-3 order-lg-3" onclick="return confirm('Apakah anda yakin ingin keluar?');">Logout</a>

                <!-- Tombol Titik Tiga (Hamburger) -->
                <button class="navbar-toggler border-0 order-2 order-lg-4" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <!-- MENU DROPDOWN -->
            <div class="collapse navbar-collapse order-lg-2" id="navbarNav">
                <ul class="navbar-nav mx-auto mb-3 mb-lg-0 text-center align-items-center">
                    <li class="nav-item"><a class="nav-link" href="beranda.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="buat-aduan.php">Form Aduan</a></li>
                    <li class="nav-item"><a class="nav-link active" href="balasan-admin.php">Balasan Admin</a></li>
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

    <main class="main-content">
        <div class="page-title">ADUAN DIBALAS</div>
        <div class="page-subtitle">Seluruh aduan yang telah dibalas admin akan ditampilkan disini</div>
        
        <div class="filter-bar">
            <select class="filter-select" id="filterKategori">
                <option value="0">Semua Kategori</option>
            </select>
            <select class="filter-select">
                <option>Terbaru Dibalas</option>
            </select>
        </div>

        <div class="dashboard-layout">
            <div class="aduan-card-list" id="aduanList">
                <div style="text-align:center; padding:20px;">Memuat data...</div>
            </div>

            <div class="sidebar">
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

                <div class="panel-card">
                    <div class="cat-stats-list" id="statKategori"></div>
                </div>

                <div class="cta-card">
                    <div class="cta-title">Punya masalah di desa?</div>
                    <div class="cta-desc">Sampaikan masalahmu disini, admin desa akan membalasnya diwebsite ini.</div>
                    <a href="buat-aduan.php" class="btn-cta">BUAT ADUAN</a>
                </div>
            </div>
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
                    <h5>Info Pengembamg</h5>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/api.js?v=12"></script>
    <script>
        const iconMap = {
            'Kebersihan': '🧹', 'Infrastruktur': '🏗️', 'Kesehatan': '🏥',
            'Keamanan': '🛡️', 'Keamanan dan Ketertiban': '🛡️', 'Pendidikan': '📚',
            'Sosial': '🤝', 'Lingkungan': '🌿', 'Lingkungan Hidup': '🌿',
            'Administrasi': '📋', 'Administrasi dan Pelayanan Publik': '📋'
        };

        async function loadData() {
            const res = await getAduanSaya();
            const listContainer = document.getElementById('aduanList');
            const statContainer = document.getElementById('statKategori');
            
            if (!res.success || !res.data || res.data.length === 0) {
                listContainer.innerHTML = `<div class="empty-row text-center p-4 bg-white rounded">Belum ada aduan.</div>`;
                return;
            }

            const dibalas = res.data.filter(a => a.status === 'dibalas').length;
            const menunggu = res.data.filter(a => a.status === 'menunggu').length;

            document.getElementById('totalDibalas').textContent = dibalas;
            document.getElementById('menungguBalasan').textContent = menunggu;

            const catCount = {};
            res.data.forEach(a => {
                catCount[a.kategori] = (catCount[a.kategori] || 0) + 1;
            });

            let statHtml = '';
            const topCats = Object.entries(catCount).sort((a,b) => b[1] - a[1]).slice(0, 3);
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

            const listDibalas = res.data.filter(a => a.status === 'dibalas');
            
            if (listDibalas.length === 0) {
                listContainer.innerHTML = `<div class="text-center p-4 bg-white rounded">Belum ada aduan yang dibalas.</div>`;
                return;
            }

            let cardHtml = '';
            const currentUserName = "<?= htmlspecialchars($user['nama']) ?>";

            for (const [index, a] of listDibalas.entries()) {
                const detailRes = await getAduanDetail(a.id_aduan);
                
                let teksBalasan = "Balasan belum tersedia.";
                let namaAdmin = "-";
                let tglBalas = "-";
                
                if (detailRes.success && detailRes.data.balasan && detailRes.data.balasan.length > 0) {
                    const b = detailRes.data.balasan[0];
                    teksBalasan = b.balasan;
                    namaAdmin = b.admin_nama;
                    tglBalas = new Date(b.tanggal_waktu).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-');
                }

                const slug = (a.kategori || 'kebersihan').toLowerCase().split(' ')[0];
                const catClass = 'cat-' + slug;
                const iconKategori = iconMap[a.kategori] || '📌';
                const tgl = new Date(a.tanggal_waktu).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-');
                const foto = a.foto ? `../uploads/${a.foto}` : '../asset/Gambar pedesaan.avif';
                const noAduan = String(index + 1).padStart(3, '0');

                cardHtml += `
                    <div class="aduan-card-detail">
                        <div class="card-left">
                            <div>
                                <div class="tag-kategori ${catClass}" style="text-align:center; font-weight:700; font-size:12px; padding:4px 10px; border-radius:6px;">
                                    ${iconKategori} ${a.kategori}
                                </div>
                                <img src="${foto}" class="img-thumb" alt="Foto">
                            </div>
                            <div class="card-left-date">${currentUserName} &nbsp;&nbsp; ${tgl}</div>
                        </div>
                        
                        <div class="card-right">
                            <div class="card-right-title">ADUAN ${noAduan}</div>
                            <div class="balasan-box">
                                <div class="balasan-header">Balasan Admin: ${namaAdmin} &nbsp;&nbsp; (${tglBalas})</div>
                                <div class="balasan-text">"${teksBalasan}"</div>
                                <a href="riwayat.php" class="link-detail">Lihat detail lengkap</a>
                            </div>
                        </div>
                    </div>
                `;
            }
            listContainer.innerHTML = cardHtml;
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadData();
        });
    </script>
</body>
</html>