<?php
require_once __DIR__ . '/../config/auth.php';

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
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
    <title>Riwayat Aduan – Admin VillageHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
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
            min-height: 100vh; display: flex; flex-direction: column;
        }

        /* ====== NAVBAR ====== */
        .navbar {
            background: linear-gradient(135deg, var(--green-main) 0%, #28a745 50%, #2d8f3e 100%);
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 5%; box-shadow: 0 3px 16px rgba(0,0,0,0.15);
            position: sticky; top: 0; z-index: 1000;
            width: 100%;
        }
        
        .navbar-left { display: flex; align-items: center; gap: 20px; }
        .navbar-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .navbar-logo img { width: 54px; height: 54px; object-fit: contain; }
        .brand-name { font-weight: 800; line-height: 1.1; white-space: nowrap; font-size: 22px; letter-spacing: 0.5px; }
        .brand-village { color: #fff; }
        .brand-hub { color: var(--yellow); }

        .navbar-menu { 
            display: flex; align-items: center; gap: 20px; list-style: none; 
            position: absolute; left: 50%; transform: translateX(-50%);
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

        .navbar-actions { display: flex; align-items: center; gap: 15px; }
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

        /* ====== MAIN CONTENT ====== */
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; flex: 1; width: 100%; }

        .card-box {
            background: #fff; border-radius: 16px; padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        /* ====== WIDGET STATISTIK ====== */
        .stats-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 30px;
        }
        .stat-item {
            border: 2px solid; border-radius: 10px; text-align: center; padding: 20px 10px;
            background: #fff;
        }
        .stat-item.border-blue { border-color: #29b6f6; }
        .stat-item.border-orange { border-color: #ff9800; }
        .stat-item.border-green { border-color: #4caf50; }

        .stat-title { font-size: 12px; font-weight: 800; text-transform: uppercase; margin-bottom: 5px; }
        .stat-item.border-blue .stat-title { color: #0288d1; }
        .stat-item.border-orange .stat-title { color: #e65100; }
        .stat-item.border-green .stat-title { color: #2e7d32; }

        .stat-value { font-size: 32px; font-weight: 800; color: #333; }

        /* ====== TABEL RIWAYAT ====== */
        .section-title { text-align: center; font-size: 22px; font-weight: 800; margin-bottom: 20px; }

        .table-header {
            display: grid; grid-template-columns: 1.2fr 1.2fr 3fr 1fr 0.8fr;
            gap: 10px; margin-bottom: 15px;
        }
        .th { text-align: center; padding: 8px 10px; border-radius: 8px; color: #fff; font-weight: 700; font-size: 13px; }
        .th-kategori { background: #2ea3a1; }
        .th-tanggal { background: #e39f3b; }
        .th-deskripsi { background: #1e6b8c; }
        .th-status { background: #5cb85c; }
        .th-aksi { background: #d9534f; }

        .table-body {
            display: flex; flex-direction: column; gap: 10px;
            max-height: 400px; overflow-y: auto; padding-right: 5px;
        }
        .table-body::-webkit-scrollbar { width: 6px; }
        .table-body::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }

        .row {
            display: grid; grid-template-columns: 1.2fr 1.2fr 3fr 1fr 0.8fr;
            gap: 10px; align-items: center; border: 1px solid #e0e6ed; padding: 10px;
            border-radius: 10px; transition: 0.3s; background: #fff;
        }
        .row:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-color: #178a40; }

        .chip { display: inline-block; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; border: 2px solid; text-align: center; width: 100%; }
        
        .chip-Keamanan { background: #fff9c4; border-color: #fbc02d; color: #f57f17; }
        .chip-Infrastruktur { background: #e3f2fd; border-color: #2196f3; color: #1565c0; }
        .chip-Kebersihan { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
        .chip-Kesehatan { background: #ffebee; border-color: #f44336; color: #c62828; }
        .chip-Sosial { background: #e0f7fa; border-color: #00bcd4; color: #00838f; }
        .chip-default { background: #f5f5f5; border-color: #9e9e9e; color: #616161; }

        .cell-center { text-align: center; font-size: 13px; font-weight: 600; }
        .cell-desc { font-size: 12px; font-weight: 500; line-height: 1.4; }

        .badge { padding: 6px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; color: #fff; border: none; display: inline-block; width: 100%; cursor: pointer; text-align: center; }
        .badge-Dibalas { background: #4caf50; }
        .badge-Menunggu { background: #ff9800; }
        
        .btn-edit {
            background: #f8f9fa; border: 1px solid #ccc; padding: 6px 12px; border-radius: 6px;
            font-size: 12px; font-weight: 600; cursor: pointer; color: #333; transition: 0.3s; text-decoration: none;
            display: inline-block; width: 100%; text-align: center;
        }
        .btn-edit:hover { background: #178a40; color: #fff; border-color: #178a40; }

        @media (max-width: 1000px) {
            .navbar { flex-direction: column; gap: 10px; padding: 10px; }
            .navbar-menu { position: static; transform: none; margin-top: 10px; }
            .role-text { display: none; }
        }
        @media (max-width: 800px) {
            .table-header { display: none; }
            .row { grid-template-columns: 1fr; gap: 8px; text-align: center; }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-left">
            <a href="dashboard.php" class="navbar-logo">
                <img src="../asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
                <div class="brand-name"><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></div>
            </a>
        </div>

        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="riwayat.php" class="active">Riwayat</a></li>
        </ul>

        <div class="navbar-actions">
            <span class="role-text">Admin</span>
            <!-- TOMBOL LOGOUT DENGAN FUNGSI JS -->
            <button onclick="logoutDanRedirect()" class="btn-header btn-red" style="border: none; cursor: pointer;">Logout</button>
        </div>
    </nav>

    <!-- KONTEN -->
    <div class="container">
        <div class="card-box">
            
            <!-- Statistik -->
            <div class="stats-grid">
                <div class="stat-item border-blue">
                    <div class="stat-title">Total Aduan</div>
                    <div class="stat-value" id="statTotal">0</div>
                </div>
                <div class="stat-item border-orange">
                    <div class="stat-title">Menunggu</div>
                    <div class="stat-value" id="statMenunggu">0</div>
                </div>
                <div class="stat-item border-green">
                    <div class="stat-title">Dibalas</div>
                    <div class="stat-value" id="statDibalas">0</div>
                </div>
            </div>

            <h2 class="section-title">Riwayat Aduan</h2>

            <!-- Header Tabel -->
            <div class="table-header">
                <div class="th th-kategori">Kategori</div>
                <div class="th th-tanggal">Tanggal Aduan</div>
                <div class="th th-deskripsi">Deskripsi Aduan</div>
                <div class="th th-status">Status</div>
                <div class="th th-aksi">Aksi</div>
            </div>

            <!-- Body Tabel -->
            <div class="table-body" id="tableBody">
                <div style="text-align:center; padding: 20px; color: #888;">Memuat data...</div>
            </div>

        </div>
    </div>

    <!-- WAJIB: Panggil api.js agar fungsi logoutDanRedirect() jalan -->
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
            // Ambil data statistik (Hanya menampilkan angka)
            const resDash = await getDashboardAdmin();
            if (resDash.success && resDash.statistik) {
                document.getElementById('statTotal').textContent = resDash.statistik.total_aduan;
                document.getElementById('statMenunggu').textContent = resDash.statistik.menunggu;
                document.getElementById('statDibalas').textContent = resDash.statistik.dibalas;
            }

            // Ambil data riwayat aduan
            const res = await getRiwayatAdmin(1, 50);
            const tbody = document.getElementById('tableBody');

            if (!res.success || !res.data || res.data.length === 0) {
                tbody.innerHTML = `<div style="text-align:center; padding: 20px; color: #888;">Belum ada riwayat aduan.</div>`;
                return;
            }

            let html = '';
            res.data.forEach(a => {
                const catClass = getCatClass(a.kategori);
                const statusClass = a.status === 'dibalas' ? 'badge-Dibalas' : 'badge-Menunggu';
                const statusLabel = a.status === 'dibalas' ? 'Dibalas' : 'Menunggu';
                
                const tgl = new Date(a.tanggal_waktu).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, ' - ');
                const deskripsi = a.deskripsi.length > 50 ? a.deskripsi.substring(0, 50) + '...' : a.deskripsi;

                html += `
                    <div class="row">
                        <div><div class="chip ${catClass}">${a.kategori}</div></div>
                        <div class="cell-center">${tgl}</div>
                        <div class="cell-desc">${deskripsi}</div>
                        <div class="cell-center"><span class="badge ${statusClass}">${statusLabel}</span></div>
                        <div class="cell-center">
                            <a href="dashboard.php" class="btn-edit">Edit ></a>
                        </div>
                    </div>
                `;
            });

            tbody.innerHTML = html;
        }

        document.addEventListener('DOMContentLoaded', loadData);
    </script>
</body>
</html>