<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$user = $_SESSION['user'];

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
    <title>Riwayat Aduan – Admin VillageHub</title>
    
    <!-- BOOTSTRAP 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- GOOGLE FONTS: NUNITO -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --green-dark: #0d5c2c;
            --green-main: #178a40;
            --yellow: #ffc107;
        }

        body {
            font-family: 'Nunito', 'Segoe UI', sans-serif;
            background: url('../assets/bg-desa.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar-custom { background: linear-gradient(135deg, var(--green-main) 0%, #28a745 50%, #2d8f3e 100%); box-shadow: 0 3px 16px rgba(0,0,0,0.15); }
        .navbar-brand img { width: 50px !important; height: 50px !important; object-fit: contain; margin-right: 10px; }
        .brand-text { font-weight: 800; font-size: 22px; letter-spacing: 0.5px; }
        .text-village { color: #fff; }
        .text-hub { color: var(--yellow); }
        
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
            color: var(--yellow); 
            border-bottom-color: var(--yellow); 
        }

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

        /* Card & Container */
        .main-card { background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }

        /* Statistik */
        .stat-box { border: 2px solid; border-radius: 10px; text-align: center; padding: 15px 10px; background: #fff; }
        .stat-box.blue { border-color: #29b6f6; }
        .stat-box.orange { border-color: #ff9800; }
        .stat-box.green { border-color: #4caf50; }
        .stat-title { font-size: 11px; font-weight: 800; text-transform: uppercase; margin-bottom: 5px; }
        .stat-value { font-size: 28px; font-weight: 800; color: #333; }

        /* Chip Kategori */
        .chip { display: inline-block; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; border: 2px solid; text-align: center; }
        .chip-Keamanan { background: #fff9c4; border-color: #fbc02d; color: #f57f17; }
        .chip-Infrastruktur { background: #e3f2fd; border-color: #2196f3; color: #1565c0; }
        .chip-Kebersihan { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
        .chip-Kesehatan { background: #ffebee; border-color: #f44336; color: #c62828; }
        .chip-Sosial { background: #e0f7fa; border-color: #00bcd4; color: #00838f; }
        .chip-default { background: #f5f5f5; border-color: #9e9e9e; color: #616161; }

        .badge-Dibalas { background: #4caf50; color: #fff; padding: 6px 10px; border-radius: 6px; display: inline-block; }
        .badge-Menunggu { background: #ff9800; color: #fff; padding: 6px 10px; border-radius: 6px; display: inline-block; }

        /* ====== TABEL DESKTOP ====== */
        .table-custom th { background: var(--green-main); color: #fff; font-weight: 600; text-align: center; }
        .table-custom td { vertical-align: middle; text-align: center; }

        /* ====== KARTU MOBILE (Disembunyikan di Desktop) ====== */
        .mobile-card-container { display: none; }

        .footer { background: #1a1a2e; color: #a0a0b0; padding: 24px 5%; margin-top: auto; font-size: 13px; text-align: center; }
        .footer .text-village { color: #fff; }
        .footer .text-hub { color: var(--yellow); }

        /* ====== MEDIA QUERY UNTUK HP ====== */
        @media (max-width: 768px) {
            /* Sembunyikan Tabel */
            .desktop-table { display: none; }
            
            /* Tampilkan Kartu Mobile */
            .mobile-card-container { display: block; }

            .mobile-card {
                border: 1px solid #e0e6ed;
                border-radius: 12px;
                padding: 15px;
                margin-bottom: 15px;
                background: #fff;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            }

            .mobile-card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }

            .mobile-card-body {
                font-size: 14px;
                color: #333;
                line-height: 1.5;
            }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container">
            <!-- KIRI: Logo -->
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="../asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
                <span class="brand-text"><span class="text-village">VILLAGE</span> <span class="text-hub">HUB</span></span>
            </a>
            
            <!-- KANAN: Tombol Logout Desktop & Tombol Titik Tiga -->
            <div class="d-flex align-items-center order-lg-3 gap-3">
                <!-- Tombol Logout untuk Desktop (Dipindah ke sini) -->
                <button onclick="logoutDanRedirect()" class="btn-logout d-none d-lg-block order-3 order-lg-3">Logout</button>

                <!-- Tombol Hamburger -->
                <button class="navbar-toggler border-0 order-2 order-lg-4" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <!-- MENU DROPDOWN -->
            <div class="collapse navbar-collapse order-lg-2" id="navbarNav">
                <ul class="navbar-nav mx-auto mb-3 mb-lg-0 text-center align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="riwayat.php">Riwayat</a>
                    </li>
                    
                    <!-- Tombol Logout untuk HP (Tetap di dalam dropdown) -->
                    <li class="nav-item d-lg-none mt-3 w-100">
                        <button onclick="logoutDanRedirect()" class="btn-logout w-100 d-block">Logout</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <div class="container my-4 flex-grow-1">
        <div class="main-card p-3 p-md-5">
            
            <!-- Statistik -->
            <div class="row g-2 mb-4 text-center">
                <div class="col-4">
                    <div class="stat-box blue">
                        <div class="stat-title">Total</div>
                        <div class="stat-value" id="statTotal">0</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-box orange">
                        <div class="stat-title">Menunggu</div>
                        <div class="stat-value" id="statMenunggu">0</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-box green">
                        <div class="stat-title">Dibalas</div>
                        <div class="stat-value" id="statDibalas">0</div>
                    </div>
                </div>
            </div>

            <h2 class="text-center fw-bold mb-4">Riwayat Aduan</h2>

            <!-- ====== TAMPILAN TABEL DESKTOP ====== -->
            <div class="table-responsive desktop-table">
                <table class="table table-bordered table-hover table-custom">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Tanggal Aduan</th>
                            <th>Deskripsi Aduan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBodyDesktop">
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ====== TAMPILAN KARTU MOBILE ====== -->
            <div class="mobile-card-container" id="mobileCardContainer">
                <div class="text-center py-4 text-muted">Memuat data...</div>
            </div>

        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
        &copy; 2026 <span class="text-village fw-bold">VILLAGE</span> <span class="text-hub fw-bold">HUB</span>. Semua hak cipta dilindungi.
    </footer>

    <!-- BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/api.js?v=12"></script>
    <script>
        function getCatClass(kategori) {
            const cat = kategori || 'default';
            if (cat.toLowerCase().includes('keamanan')) return 'chip-Keamanan';
            if (cat.toLowerCase().includes('infrastruktur')) return 'chip-Infrastruktur';
            if (cat.toLowerCase().includes('kebersihan')) return 'chip-Kebersihan';
            if (cat.toLowerCase().includes('kesehatan')) return 'chip-Kesehatan';
            if (cat.toLowerCase().includes('sosial')) return 'chip-Sosial';
            return 'chip-default';
        }

        async function loadData() {
            // Ambil data statistik
            const resDash = await getDashboardAdmin();
            if (resDash.success && resDash.statistik) {
                document.getElementById('statTotal').textContent = resDash.statistik.total_aduan;
                document.getElementById('statMenunggu').textContent = resDash.statistik.menunggu;
                document.getElementById('statDibalas').textContent = resDash.statistik.dibalas;
            }

            // Ambil data riwayat
            const res = await getRiwayatAdmin(1, 50);
            const tbodyDesktop = document.getElementById('tableBodyDesktop');
            const mobileContainer = document.getElementById('mobileCardContainer');

            if (!res.success || !res.data || res.data.length === 0) {
                tbodyDesktop.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat aduan.</td></tr>`;
                mobileContainer.innerHTML = `<div class="text-center py-4 text-muted">Belum ada riwayat aduan.</div>`;
                return;
            }

            let htmlDesktop = '';
            let htmlMobile = '';

            res.data.forEach(a => {
                const catClass = getCatClass(a.kategori);
                const statusClass = a.status === 'dibalas' ? 'badge-Dibalas' : 'badge-Menunggu';
                const statusLabel = a.status === 'dibalas' ? 'Dibalas' : 'Menunggu';
                
                const tgl = new Date(a.tanggal_waktu).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, ' - ');
                const deskripsi = a.deskripsi.length > 50 ? a.deskripsi.substring(0, 50) + '...' : a.deskripsi;

                // 1. HTML untuk Tabel Desktop
                htmlDesktop += `
                    <tr>
                        <td><div class="chip ${catClass}">${a.kategori}</div></td>
                        <td class="fw-bold">${tgl}</td>
                        <td class="text-start">${deskripsi}</td>
                        <td><span class="${statusClass}">${statusLabel}</span></td>
                        <td>
                            <a href="dashboard.php" class="btn btn-sm btn-outline-success fw-bold">Edit ></a>
                        </td>
                    </tr>
                `;

                // 2. HTML untuk Kartu Mobile
                htmlMobile += `
                    <div class="mobile-card">
                        <div class="mobile-card-header">
                            <div class="chip ${catClass}">${a.kategori}</div>
                            <span class="${statusClass}">${statusLabel}</span>
                        </div>
                        <div class="mobile-card-body">
                            <p class="fw-bold mb-1">${tgl}</p>
                            <p class="mb-2">${a.deskripsi}</p>
                            <a href="dashboard.php" class="btn btn-sm btn-outline-success fw-bold w-100">Edit ></a>
                        </div>
                    </div>
                `;
            });

            tbodyDesktop.innerHTML = htmlDesktop;
            mobileContainer.innerHTML = htmlMobile;
        }

        document.addEventListener('DOMContentLoaded', loadData);
    </script>
</body>
</html>