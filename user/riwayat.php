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
    <title>Riwayat Aduan – VillageHub</title>
    
    <!-- BOOTSTRAP 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- GOOGLE FONTS: NUNITO -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --brand-green: #1b7a2b; 
            --brand-yellow: #ffc107; 
        }
        
        body {
            font-family: 'Nunito', 'Segoe UI', sans-serif;
            background-color: #f5f2eb; 
            color: #333;
            min-height: 100vh; 
            display: flex; 
            flex-direction: column;
        }
        a { text-decoration: none; color: inherit; }

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

        /* ====== KONTEN UTAMA ====== */
        .main-content { max-width: 900px; width: 100%; margin: 40px auto; padding: 0 20px; flex: 1; }

        .riwayat-card { background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e2dcd0; width: 100%; }
        .section-title { font-size: 24px; font-weight: 800; color: #1a1a2e; text-align: center; margin-bottom: 25px; }

        .table-custom th { background: var(--brand-green); color: #fff; text-align: center; }
        .table-custom td { vertical-align: middle; }

        .chip-kategori { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; border: 2px solid; text-align: center; }
        .chip-kebersihan { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
        .chip-infrastruktur { background: #fff3e0; border-color: #ff9800; color: #e65100; }
        .chip-kesehatan { background: #e3f2fd; border-color: #2196f3; color: #1565c0; }
        .chip-keamanan { background: #fce4ec; border-color: #e91e63; color: #c2185b; }
        .chip-pendidikan { background: #f3e5f5; border-color: #9c27b0; color: #7b1fa2; }
        .chip-sosial { background: #e0f2f1; border-color: #009688; color: #00695c; }
        .chip-lingkungan { background: #f1f8e9; border-color: #8bc34a; color: #558b2f; }
        .chip-administrasi { background: #ede7f6; border-color: #673ab7; color: #4527a0; }

        .tag-status { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; border: 2px solid; }
        .status-dibalas { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
        .status-menunggu { background: #fff3e0; border-color: #ff9800; color: #e65100; }

        .btn-hapus { display: inline-block; padding: 6px 14px; background: #ffebee; border: 2px solid #c62828; border-radius: 8px; font-size: 12px; font-weight: 700; color: #c62828; cursor: pointer; transition: all 0.25s; }
        .btn-hapus:hover { background: #c62828; color: #fff; } 

        @media (max-width: 768px) {
            .riwayat-card { padding: 15px; }
        }

        .footer { background: #1a1a2e; color: #a0a0b0; padding: 24px 5%; font-size: 13px; text-align: center; }
        .footer .brand-village { color: #fff; }
        .footer .brand-hub { color: var(--brand-yellow); }
    </style>
</head>
<body>

    <!-- NAVBAR BOOTSTRAP (SAMA SEPERTI BERANDA & PROFIL) -->
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
                    <li class="nav-item"><a class="nav-link" href="beranda.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="buat-aduan.php">Form Aduan</a></li>
                    <li class="nav-item"><a class="nav-link" href="balasan-admin.php">Balasan Admin</a></li>
                    <li class="nav-item"><a class="nav-link active" href="riwayat.php">Riwayat</a></li>
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
        <div class="riwayat-card">
            <h2 class="section-title">Riwayat Aduan</h2>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-custom">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Tanggal diajukan</th>
                            <th>Deskripsi Aduan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr><td colspan="5" class="text-center py-4 text-muted">Memuat riwayat aduan...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
     <footer class="footer">
        &copy; 2026 <span class="brand-name"><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></span>. Semua hak cipta dilindungi.
    </footer>

    <!-- BOOTSTRAP JS & SCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/api.js?v=9"></script>
    <script>
        const iconMap = {
            'Kebersihan': '🧹', 'Infrastruktur': '🏗️', 'Kesehatan': '🏥',
            'Keamanan dan Ketertiban': '🛡️', 'Keamanan': '🛡️', 'Pendidikan': '📚',
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

        async function loadRiwayat() {
            const res = await getAduanSaya();
            const tbody = document.getElementById('tableBody');

            if (!res.success || !res.data || res.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat aduan. <a href="buat-aduan.php" class="text-success fw-bold">Buat aduan sekarang →</a></td></tr>`;
                return;
            }

            const list = res.data.slice(0, 10);

            tbody.innerHTML = list.map(a => {
                const statusClass = a.status === 'dibalas' ? 'status-dibalas' : 'status-menunggu';
                const statusLabel = a.status === 'dibalas' ? 'Dibalas' : 'Menunggu';
                
                const namaKategori = a.kategori || 'Kebersihan';
                const catClass = chipColorMap[namaKategori] || 'chip-kebersihan';
                const iconKategori = iconMap[namaKategori] || '📌';

                const tgl = new Date(a.tanggal_waktu);
                const tanggal = tgl.toLocaleDateString('id-ID', {
                    day: '2-digit', month: '2-digit', year: 'numeric'
                }).replace(/\//g, ' - ');

                return `
                    <tr>
                        <td class="text-center"><span class="chip-kategori ${catClass}">${iconKategori} ${namaKategori}</span></td>
                        <td class="text-center fw-bold">${tanggal}</td>
                        <td>${(a.deskripsi || '').substring(0, 100)}${a.deskripsi.length > 100 ? '...' : ''}</td>
                        <td class="text-center"><span class="tag-status ${statusClass}">${statusLabel}</span></td>
                        <td class="text-center"><button class="btn-hapus" onclick="konfirmasiHapus(${a.id_aduan})">Hapus</button></td>
                    </tr>
                `;
            }).join('');
        }

        async function konfirmasiHapus(id) {
            if (!confirm('Yakin ingin menghapus aduan ini?')) return;
            const res = await hapusAduan(id);
            if (res.success) {
                alert('Aduan berhasil dihapus!');
                loadRiwayat();
            } else {
                alert('Gagal menghapus: ' + res.message);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadRiwayat();
        });
    </script>
</body>
</html>