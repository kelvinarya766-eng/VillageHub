<?php
require_once __DIR__ . '/config/auth.php';

$isLoggedIn = !empty($_SESSION['user']);
$userData   = $_SESSION['user'] ?? null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VillageHub — Rukun Warganya, Maju Desanya</title>
    
    <!-- BOOTSTRAP 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root { --brand-green: #1b7a2b; --brand-yellow: #ffc107; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', 'Segoe UI', sans-serif; background-color: #f0f2f5; color: #333; line-height: 1.6; overflow-x: hidden; }
        a { text-decoration: none; color: inherit; }

        .navbar {
            background: linear-gradient(135deg, var(--brand-green) 0%, #28a745 50%, #2d8f3e 100%);
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 5%; box-shadow: 0 3px 16px rgba(0,0,0,0.15);
            position: sticky; top: 0; z-index: 1000;
        }
        .navbar-logo { display: flex; align-items: center; gap: 10px; }
        .navbar-logo img { width: 54px; height: 54px; object-fit: contain; }
        .brand-name { font-weight: 800; line-height: 1.1; white-space: nowrap; }
        .navbar-logo .brand-name { font-size: 22px; letter-spacing: 0.5px; }
        .brand-village { color: #fff; }
        .brand-hub { color: var(--brand-yellow); }
        .navbar-actions { display: flex; align-items: center; gap: 10px; }

        .btn-masuk {
            padding: 10px 28px; border: 2px solid var(--brand-yellow); border-radius: 8px;
            background: var(--brand-yellow); color: #1a1a2e; font-size: 14px; font-weight: 600;
            font-family: 'Poppins', sans-serif; cursor: pointer; transition: all 0.3s ease;
        }
        .btn-masuk:hover { background: #e0a800; transform: translateY(-2px); }

        .btn-daftar {
            padding: 10px 28px; border: 2px solid #ffffff; border-radius: 8px;
            background: #ffffff; color: #1a1a2e; font-size: 14px; font-weight: 600;
            font-family: 'Poppins', sans-serif; cursor: pointer; transition: all 0.3s ease;
        }
        .btn-daftar:hover { background: #f0f0f0; transform: translateY(-2px); }

        .hero-banner { position: relative; width: 100%; height: 320px; overflow: hidden; }
        .hero-banner > img { width: 100%; height: 100%; object-fit: cover; object-position: center 40%; display: block; }
        .hero-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(0,80,0,0.65) 0%, rgba(0,60,0,0.45) 40%, rgba(0,40,0,0.25) 100%);
            display: flex; flex-direction: column; justify-content: center; padding: 0 6%;
        }
        .hero-brand { display: flex; align-items: center; gap: 18px; margin-bottom: 14px; }
        .hero-brand-logo { width: 100px; height: 100px; object-fit: contain; filter: drop-shadow(0 3px 8px rgba(0,0,0,0.3)); }
        .hero-brand-name { font-size: 46px; letter-spacing: 1px; text-shadow: 2px 3px 6px rgba(0,0,0,0.3); }
        .hero-overlay p { font-size: 15px; color: rgba(255,255,255,0.9); max-width: 520px; text-shadow: 1px 1px 4px rgba(0,0,0,0.25); font-style: italic; }

        .main-content { max-width: 1200px; margin: 0 auto; padding: 30px 5%; }

        .category-section { margin-bottom: 35px; }
        
        .category-filters { 
            display: flex; 
            gap: 10px; 
            flex-wrap: wrap; 
            padding: 5px 0; 
            justify-content: center; 
        }
        .category-chip {
            padding: 8px 15px; border-radius: 20px; font-size: 13px; font-weight: 600;
            font-family: 'Poppins', sans-serif; cursor: pointer; transition: all 0.3s ease;
            white-space: nowrap; border: 2px solid;
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            flex: 1 1 120px; 
            max-width: 200px; 
        }
        .category-chip:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
        .category-chip.active { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

        .chip-kebersihan { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
        .chip-kebersihan:hover, .chip-kebersihan.active { background: #4caf50; color: #fff; }
        .chip-infrastruktur { background: #fff3e0; border-color: #ff9800; color: #e65100; }
        .chip-infrastruktur:hover, .chip-infrastruktur.active { background: #ff9800; color: #fff; }
        .chip-kesehatan { background: #e3f2fd; border-color: #2196f3; color: #1565c0; }
        .chip-kesehatan:hover, .chip-kesehatan.active { background: #2196f3; color: #fff; }
        .chip-keamanan { background: #fce4ec; border-color: #e91e63; color: #c2185b; }
        .chip-keamanan:hover, .chip-keamanan.active { background: #e91e63; color: #fff; }
        .chip-pendidikan { background: #f3e5f5; border-color: #9c27b0; color: #7b1fa2; }
        .chip-pendidikan:hover, .chip-pendidikan.active { background: #9c27b0; color: #fff; }
        .chip-sosial { background: #e0f2f1; border-color: #009688; color: #00695c; }
        .chip-sosial:hover, .chip-sosial.active { background: #009688; color: #fff; }
        .chip-lingkungan { background: #f1f8e9; border-color: #8bc34a; color: #558b2f; }
        .chip-lingkungan:hover, .chip-lingkungan.active { background: #8bc34a; color: #fff; }
        .chip-administrasi { background: #ede7f6; border-color: #673ab7; color: #4527a0; }
        .chip-administrasi:hover, .chip-administrasi.active { background: #673ab7; color: #fff; }

        .section-title { font-size: 24px; font-weight: 700; color: #1a1a2e; margin-bottom: 20px; position: relative; padding-left: 14px; }
        .section-title::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 5px; height: 28px; background: linear-gradient(180deg, #28a745, var(--brand-yellow)); border-radius: 3px; }

        .aduan-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; }

        .aduan-card {
            background: #fff; border: 2px solid #e0e6ed; border-radius: 14px;
            overflow: hidden; transition: all 0.3s ease;
            display: flex; flex-direction: column;
        }
        .aduan-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.1); border-color: #28a745; }
        .aduan-card-image { width: 100%; height: 180px; overflow: hidden; background: #f0f0f0; cursor: pointer; }
        .aduan-card-image img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.3s; }
        .aduan-card-image:hover img { transform: scale(1.05); }
        
        .aduan-card-body { padding: 16px 18px; flex: 1; display: flex; flex-direction: column; }
        .aduan-card-tags { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; gap: 8px; }
        .tag-kategori { padding: 4px 14px; border-radius: 16px; font-size: 12px; font-weight: 600; color: #fff; }
        .tag-infrastruktur { background: #ff9800; }
        .tag-keamanan { background: #e91e63; }
        .tag-kesehatan { background: #4caf50; }
        .tag-kebersihan { background: #2196f3; }
        .tag-pendidikan { background: #9c27b0; }
        .tag-sosial { background: #009688; }
        .tag-lingkungan { background: #8bc34a; }
        .tag-administrasi { background: #673ab7; }
        .tag-status { padding: 4px 12px; border-radius: 16px; font-size: 11px; font-weight: 600; border: 2px solid; }
        .status-dibalas { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
        .status-menunggu { background: #fce4ec; border-color: #e91e63; color: #c2185b; }
        .aduan-card-desc { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 14px; line-height: 1.5; }
        .aduan-card-footer { display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding-top: 12px; border-top: 1px solid #f0f0f0; }
        .aduan-author { display: flex; align-items: center; gap: 8px; }
        .author-avatar { width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #28a745, #20c997); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 12px; font-weight: 700; }
        .author-name { font-size: 12px; font-weight: 600; color: #555; }
        .aduan-date { font-size: 11px; color: #999; }
        .link-balasan { font-size: 12px; color: #2196f3; font-weight: 600; text-decoration: underline; cursor: pointer; }
        .link-balasan:hover { color: #1565c0; }

        /* ===== CSS UNTUK KOTAK BALASAN ADMIN ===== */
        .admin-reply-box {
            background-color: #f8f9fa;
            border-left: 4px solid var(--brand-green);
            padding: 10px 15px;
            border-radius: 4px;
            margin-top: 12px;
            font-size: 13px;
            color: #333;
        }
        .admin-reply-header {
            font-weight: 700;
            color: var(--brand-green);
            margin-bottom: 4px;
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .navbar-logo .brand-name { font-size: 18px; }
            .navbar-logo img { width: 36px; height: 36px; }
            .hero-banner { height: 220px; }
            .hero-brand { gap: 12px; margin-bottom: 10px; }
            .hero-brand-logo { width: 68px; height: 68px; }
            .hero-brand-name { font-size: clamp(24px, 6vw, 34px); }
            .hero-overlay p { font-size: 12px; }
            .aduan-grid { grid-template-columns: 1fr; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .aduan-card { animation: fadeInUp 0.5s ease forwards; }

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
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <img src="asset/Logo_village_Hub-removebg-preview.png" alt="Logo" style="width: 50px; height: 50px; object-fit: contain;">
                <span class="brand-name"><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPublic">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navPublic">
                <div class="navbar-nav ms-auto gap-2 text-center mt-3 mt-lg-0">
                    <a href="login.php" class="btn btn-masuk">Masuk</a>
                    <a href="register.php" class="btn btn-daftar">Daftar</a>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero-banner">
        <img src="asset/Gambar pedesaan.avif" alt="Pemandangan Desa">
        <div class="hero-overlay">
            <div class="hero-brand">
                <img class="hero-brand-logo" src="asset/Logo_village_Hub-removebg-preview.png" alt="Logo VillageHub">
                <h1 class="brand-name hero-brand-name"><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></h1>
            </div>
            <p>Sampaikan aduanmu dan pantau balasan dari pihak desa.</p>
        </div>
    </section>

    <main class="main-content">
        <div class="category-section">
            <div class="category-filters" id="category-filters">
                <button class="category-chip chip-semua active" data-kategori="0" style="background:#333;color:#fff;border-color:#333;">Semua</button>
            </div>
        </div>

        <h2 class="section-title">Aduan Terbaru</h2>
        <div class="aduan-grid" id="aduan-grid">
            <p style="text-align: center; color: #999; grid-column: 1/-1;">Memuat aduan...</p>
        </div>
    </main>

    <!-- FOOTER BARU (4 KOLOM) -->
    <footer class="footer-mega">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-3 mb-4">
                    <div class="footer-brand">
                        <img src="asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
                        <span><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></span>
                    </div>
                    <p><strong>Sistem Pengaduan Desa</strong></p>
                    <p>Jawa Tengah 57143</p>
                    <p>Telp. +62 889-85493525</p>
                </div>

                <div class="col-12 col-md-3 mb-4">
                    <h5>Program & Layanan</h5>
                    <a href="login.php">Pengaduan Warga</a>
                    <a href="login.php">Buat Aduan Baru</a>
                    <a href="login.php">Riwayat Aduan</a>
                    <a href="login.php">Pengaturan Profil</a>
                    <a href="https://wa.me/6288985493525">Bantuan</a>
                </div>

                <div class="col-12 col-md-3 mb-4">
                    <h5>Informasi</h5>
                    <p><strong>VillageHub Support</strong></p>
                    <a href="https://wa.me/6288985493525">+62 889-8549-3525</a>
                </div>

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

    <!-- BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="assets/api.js?v=13"></script>
    <script>
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

        const iconMap = {
            'Kebersihan': '🧹', 'Infrastruktur': '🏗️', 'Kesehatan': '🏥',
            'Keamanan': '🛡️', 'Keamanan dan Ketertiban': '🛡️', 'Pendidikan': '📚',
            'Sosial': '🤝', 'Lingkungan': '🌿', 'Lingkungan Hidup': '🌿',
            'Administrasi': '📋', 'Administrasi dan Pelayanan Publik': '📋'
        };

        let allAduan = []; 

        // ==========================================
        // FUNGSI MEMBUKA MODAL FOTO
        // ==========================================
        function bukaModalFoto(urlGambar) {
            document.getElementById('imgModalFoto').src = urlGambar;
            var myModal = new bootstrap.Modal(document.getElementById('modalLihatFoto'));
            myModal.show();
        }

        async function loadKategori() {
            const res = await getKategori();
            const container = document.getElementById('category-filters');

            if (res.success) {
                res.data.forEach(k => {
                    const cls   = chipColorMap[k.golongan] || 'chip-kebersihan';
                    const icon  = iconMap[k.golongan] || '📌';
                    const chip  = document.createElement('button');
                    chip.className = `category-chip ${cls}`;
                    chip.setAttribute('data-kategori', k.id_kategori);
                    chip.textContent = `${icon} ${k.golongan}`;
                    chip.addEventListener('click', () => filterByKategori(k.id_kategori, chip));
                    container.appendChild(chip);
                });
            }
        }

        async function loadAduanTerbaru() {
            const res = await apiFetch('/public/aduan_terbaru.php', { method: 'GET' });
            const grid = document.getElementById('aduan-grid');

            if (!res.success) {
                grid.innerHTML = '<p style="text-align:center;color:#999;grid-column:1/-1;">Belum ada aduan. <a href="login.php" style="color:#28a745;">Login</a> untuk mulai.</p>';
                return;
            }

            allAduan = res.data || [];
            renderAduan(allAduan);
        }

        function renderAduan(list) {
            const grid = document.getElementById('aduan-grid');

            if (!list || list.length === 0) {
                grid.innerHTML = '<p style="text-align:center;color:#999;grid-column:1/-1;">Belum ada aduan.</p>';
                return;
            }

            grid.innerHTML = list.map((a, i) => {
                const statusClass = a.status === 'dibalas' ? 'status-dibalas' : 'status-menunggu';
                const statusLabel = a.status === 'dibalas' ? 'Dibalas' : 'Menunggu';
                const tagClass    = 'tag-' + (a.kategori || 'kebersihan').toLowerCase().split(' ')[0];
                
                // LOGIKA FOTO PROFIL
                const inisial     = (a.nama_pengadu || 'A').charAt(0).toUpperCase();
                const fotoPengadu = a.foto_pengadu ? `uploads/${a.foto_pengadu}` : null;
                
                const avatarHtml = fotoPengadu 
                    ? `<img src="${fotoPengadu}" alt="Foto" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">` 
                    : `<div class="author-avatar">${inisial}</div>`;

                const tanggal     = new Date(a.tanggal_waktu).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                const foto        = a.foto ? `uploads/${a.foto}` : 'asset/Gambar pedesaan.avif';

                // LOGIKA MENAMPILKAN BALASAN ADMIN
                let balasanHtml = '';
                if (a.status === 'dibalas' && a.isi_balasan) {
                    balasanHtml = `
                        <div class="admin-reply-box">
                            <div class="admin-reply-header">
                                💬 Balasan Admin (${a.nama_admin || 'Admin'})
                            </div>
                            "${a.isi_balasan}"
                        </div>
                    `;
                }

                return `
                    <div class="aduan-card" data-kategori="${a.id_kategori}" style="animation-delay:${i*0.1}s;">
                        <!-- TAMBAHKAN ONCLICK UNTUK MEMBUKA MODAL -->
                        <div class="aduan-card-image" onclick="bukaModalFoto('${foto}')">
                            <img src="${foto}" alt="Aduan ${a.kategori}">
                        </div>
                        <div class="aduan-card-body">
                            <div class="aduan-card-tags">
                                <span class="tag-kategori ${tagClass}">${a.kategori}</span>
                                <span class="tag-status ${statusClass}">${statusLabel}</span>
                            </div>
                            <p class="aduan-card-desc">${(a.deskripsi || '').substring(0, 80)}...</p>
                            
                            <!-- TAMPILKAN BALASAN DI SINI -->
                            ${balasanHtml} 

                            <div class="aduan-card-footer">
                                <div class="aduan-author">
                                    ${avatarHtml}
                                    <span class="author-name">${a.nama_pengadu || 'Anonim'}</span>
                                </div>
                                <span class="aduan-date ms-auto">${tanggal}</span>                              
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function filterByKategori(id, chipEl) {
            document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
            chipEl.classList.add('active');

            if (id === 0) {
                renderAduan(allAduan);
            } else {
                const filtered = allAduan.filter(a => a.id_kategori == id);
                renderAduan(filtered);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadKategori();
            loadAduanTerbaru(); 

            document.querySelector('.chip-semua').addEventListener('click', function() {
                filterByKategori(0, this);
            });
        });
    </script>

</body>
</html>