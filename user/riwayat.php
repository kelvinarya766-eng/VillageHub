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
    <title>Riwayat Aduan – VillageHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* ====== ROOT & RESET ====== */
        :root { --brand-green: #1b7a2b; --brand-yellow: #ffc107; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
              body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background-color: #f5f2eb; /* Warna cream seperti profil */
            color: #333;
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
            width: 100%;
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
            background: #c62828; border-color: #c62828; color: #fff;
        }
        .btn-header:hover { background: #a31515; transform: translateY(-2px); }

        /* ====== CONTAINER ====== */
               .main-content { 
            max-width: 900px; /* Lebar disamakan dengan profil */
            width: 100%;
            margin: 40px auto; 
            padding: 0 20px; 
            flex: 1; 
        }

        /* Card Putih Besar */
        .riwayat-card {
            background: #fff; border-radius: 16px; padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2dcd0;
            width: 100%;
        }

        .section-title {
            font-size: 24px; font-weight: 800; color: #1a1a2e; text-align: center; margin-bottom: 25px;
        }

        /* Tabel Header (Warna-warni) */
        .table-header {
            background: linear-gradient(135deg, var(--brand-green) 0%, #28a745 100%);
            padding: 14px 20px; display: grid;
            grid-template-columns: 1fr 1.2fr 2.5fr 1fr 0.8fr;
            gap: 12px; color: #fff; font-weight: 700; font-size: 13px;
            border-radius: 10px; margin-bottom: 20px;
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

        /* List Data (Bisa discroll) */
        .table-body { 
            display: flex; flex-direction: column; gap: 12px;
            max-height: 450px;
            overflow-y: auto; 
            padding-right: 10px;
        }

        /* Scrollbar Kustom */
        .table-body::-webkit-scrollbar { width: 8px; }
        .table-body::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .table-body::-webkit-scrollbar-thumb { background: #a8a8a8; border-radius: 10px; }
        .table-body::-webkit-scrollbar-thumb:hover { background: #888; }

        .table-row {
            display: grid; grid-template-columns: 1fr 1.2fr 2.5fr 1fr 0.8fr;
            gap: 12px; padding: 14px 20px; align-items: center;
            border: 1px solid #e2dcd0; border-radius: 10px;
            transition: all 0.2s; background: #fff;
        }
        .table-row:hover { background: #f9fdfb; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

        /* ====== STYLING BARU UNTUK KATEGORI (SESUAI LAMPIRAN 3) ====== */
        .cell-kategori {
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
            gap: 6px; 
            padding: 6px 14px;
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: 700;
            text-align: center; 
            border: 2px solid;
            width: max-content; /* Menyesuaikan panjang teks */
            min-width: 100%;
        }

        /* Warna Background & Teks (Disamakan dengan Beranda Publik) */
        .chip-kebersihan { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
        .chip-infrastruktur { background: #fff3e0; border-color: #ff9800; color: #e65100; }
        .chip-kesehatan { background: #e3f2fd; border-color: #2196f3; color: #1565c0; }
        .chip-keamanan { background: #fce4ec; border-color: #e91e63; color: #c2185b; }
        .chip-pendidikan { background: #f3e5f5; border-color: #9c27b0; color: #7b1fa2; }
        .chip-sosial { background: #e0f2f1; border-color: #009688; color: #00695c; }
        .chip-lingkungan { background: #f1f8e9; border-color: #8bc34a; color: #558b2f; }
        .chip-administrasi { background: #ede7f6; border-color: #673ab7; color: #4527a0; }

        .cell-tanggal { font-size: 13px; font-weight: 700; color: #333; text-align: center; }
        .cell-deskripsi { font-size: 13px; color: #333; font-weight: 600; line-height: 1.4; }
        .cell-status { text-align: center; }
        .cell-aksi { text-align: center; }

        .tag-status {
            display: inline-block; padding: 6px 14px;
            border-radius: 20px; font-size: 11px; font-weight: 700;
            border: 2px solid; width: 100%;
        }
        .status-dibalas { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
        .status-menunggu { background: #fff3e0; border-color: #ff9800; color: #e65100; }

        /* Tombol Hapus */
        .btn-hapus {
            display: inline-block; padding: 6px 14px;
            background: #ffebee; border: 2px solid #c62828;
            border-radius: 8px; font-size: 12px; font-weight: 700;
            color: #c62828; cursor: pointer; transition: all 0.25s;
        }
        .btn-hapus:hover { background: #c62828; color: #fff; }

        .empty-row, .loading-row {
            text-align: center; padding: 40px; color: #999; font-size: 14px;
        }

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
            .cell-kategori { width: max-content; }
        }
        @media (max-width: 768px) {
            .navbar-logo .brand-name { font-size: 18px; }
            .navbar-logo img { width: 40px; height: 40px; }
            .btn-header { padding: 7px 14px; font-size: 12px; }
            .nav-hello { display: none; }
            .riwayat-card { padding: 15px; }
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
                <li><a href="balasan-admin.php">Balasan Admin</a></li>
                <li><a href="riwayat.php" class="active">Riwayat</a></li>
                <li><a href="profil.php">Kelola Profil</a></li>
            </ul>
        </div>
        <div class="navbar-actions">
            <div class="nav-hello">
                Halo, <?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?>
                <small><?= ucfirst($user['role']) ?></small>
            </div>
            <button onclick="logoutDanRedirect()" class="btn-header">Logout</button>
            <?php 
                $fotoProfil = !empty($userData['foto']) ? '../uploads/' . $userData['foto'] : '../asset/default-avatar.png';
            ?>
            <img src="<?= $fotoProfil ?>" alt="Foto Profil" class="nav-avatar">
        </div>
    </nav>

    <!-- ====== KONTEN ====== -->
    <main class="main-content">
        <div class="riwayat-card">
            <h2 class="section-title">Riwayat Aduan</h2>

            <!-- Header Tabel -->
            <div class="table-header">
                <div class="th-kategori">Kategori</div>
                <div class="th-tanggal">Tanggal diajukan</div>
                <div class="th-deskripsi">Deskripsi Aduan</div>
                <div class="th-status">Status</div>
                <div class="th-aksi">Aksi</div>
            </div>

            <!-- Body Tabel (Bisa Discroll) -->
            <div class="table-body" id="tableBody">
                <div class="loading-row">Memuat riwayat aduan...</div>
            </div>
        </div>
    </main>

    <!-- ====== FOOTER ====== -->
    <footer class="footer">
        &copy; 2026 <span class="brand-name footer-brand"><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></span>. Semua hak cipta dilindungi.
    </footer>

    <script src="../assets/api.js?v=9"></script>
    <script>
        // ===== MAPPING ICON KATEGORI (SAMA PERSIS BERANDA PUBLIK) =====
        const iconMap = {
            'Kebersihan': '🧹',
            'Infrastruktur': '🏗️',
            'Kesehatan': '🏥',
            'Keamanan dan Ketertiban': '🛡️',
            'Keamanan': '🛡️',
            'Pendidikan': '📚',
            'Sosial': '🤝',
            'Lingkungan': '🌿',
            'Lingkungan Hidup': '🌿',
            'Administrasi': '📋',
            'Administrasi dan Pelayanan Publik': '📋'
        };

        // ===== MAPPING WARNA CHIP (SAMA PERSIS BERANDA PUBLIK) =====
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
        // 1. LOAD DATA RIWAYAT (MAKSIMAL 10 DATA)
        // =============================================
        async function loadRiwayat() {
            const res = await getAduanSaya();
            const tbody = document.getElementById('tableBody');

            if (!res.success || !res.data || res.data.length === 0) {
                tbody.innerHTML = `
                    <div class="empty-row">
                        Belum ada riwayat aduan. 
                        <a href="buat-aduan.php" style="color:#28a745;font-weight:700;">Buat aduan sekarang →</a>
                    </div>`;
                return;
            }

            // BATASI HANYA 10 DATA TERBARU
            const list = res.data.slice(0, 10);

            tbody.innerHTML = list.map(a => {
                const statusClass = a.status === 'dibalas' ? 'status-dibalas' : 'status-menunggu';
                const statusLabel = a.status === 'dibalas' ? 'Dibalas' : 'Menunggu';
                
                // LOGIKA WARNA & ICON
                const namaKategori = a.kategori || 'Kebersihan';
                
                // Ambil Class Warna dari Mapping
                const catClass = chipColorMap[namaKategori] || 'chip-kebersihan';
                
                // Ambil Icon dari mapping
                const iconKategori = iconMap[namaKategori] || '📌';

                const tgl = new Date(a.tanggal_waktu);
                const tanggal = tgl.toLocaleDateString('id-ID', {
                    day: '2-digit', month: '2-digit', year: 'numeric'
                }).replace(/\//g, ' - ');

                return `
                    <div class="table-row">
                        <!-- TAMPILKAN ICON DAN WARNA CHIP -->
                        <div><span class="cell-kategori ${catClass}">${iconKategori} ${namaKategori}</span></div>
                        <div class="cell-tanggal">${tanggal}</div>
                        <div class="cell-deskripsi">${(a.deskripsi || '').substring(0, 100)}${a.deskripsi.length > 100 ? '...' : ''}</div>
                        <div class="cell-status"><span class="tag-status ${statusClass}">${statusLabel}</span></div>
                        <div class="cell-aksi">
                            <button class="btn-hapus" onclick="konfirmasiHapus(${a.id_aduan})">Hapus</button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // =============================================
        // 2. FUNGSI KONFIRMASI & HAPUS ADUAN
        // =============================================
        async function konfirmasiHapus(id) {
            if (!confirm('Yakin ingin menghapus aduan ini?')) return;

            // Panggil fungsi hapusAduan dari api.js
            const res = await hapusAduan(id);

            if (res.success) {
                alert('Aduan berhasil dihapus!');
                loadRiwayat(); // Refresh tabel
            } else {
                alert('Gagal menghapus: ' + res.message);
            }
        }

        // INIT
        document.addEventListener('DOMContentLoaded', () => {
            loadRiwayat();
        });
    </script>

</body>
</html>