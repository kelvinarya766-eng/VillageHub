<?php
session_start();
require_once '../config/database.php';

// Panggil PHPMailer
require_once __DIR__.'/../vendor/src/Exception.php';
require_once __DIR__.'/../vendor/src/PHPMailer.php';
require_once __DIR__.'/../vendor/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

$stmtUser = $db->prepare("SELECT * FROM users WHERE id_user = :id");
$stmtUser->execute([':id' => $user_id]);
$userData = $stmtUser->fetch();

// ==========================================
// 2. PROSES KIRIM / EDIT BALASAN & NOTIFIKASI EMAIL
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_balasan'])) {
    $balasan_teks = trim($_POST['balasan'] ?? '');
    $id_balas = intval($_POST['id_aduan'] ?? 0);
    
    if (!empty($balasan_teks) && $id_balas > 0) {
        $db->beginTransaction();
        try {
            // Simpan / Update Balasan
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

            // Update Status Aduan
            $db->prepare("UPDATE aduan SET status='dibalas' WHERE id_aduan=:id")->execute([':id' => $id_balas]);
            
            // Ambil data user untuk keperluan kirim email
            $stmtData = $db->prepare("SELECT u.email, u.nama, k.golongan AS kategori FROM aduan a JOIN users u ON u.id_user = a.id_user JOIN kategori k ON k.id_kategori = a.id_kategori WHERE a.id_aduan = :id");
            $stmtData->execute([':id' => $id_balas]);
            $dataAduan = $stmtData->fetch();

            // Kirim Notifikasi Email
            if ($dataAduan) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    
                    // ==========================================
                    // PEMBENARAN KONFIGURASI EMAIL
                    // ==========================================
                    $mail->Host       = 'smtp-relay.brevo.com'; 
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'c17bbb001@smtp-brevo.com'; 
                    $mail->Password   = 'bskQDiSqYDpApOX';          
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587; 
                    // ==========================================

                    $mail->setFrom('kelvinarya766@gmail.com', 'VillageHub');
                    $mail->addAddress($dataAduan['email'], $dataAduan['nama']);
                    $mail->isHTML(true);
                    $mail->Subject = 'Aduan Anda Telah Dibalas - VillageHub';
                    $mail->Body    = "
                        <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                            <h2 style='color: #1b7a2b; text-align: center;'>VillageHub</h2>
                            <p>Halo <b>{$dataAduan['nama']}</b>,</p>
                            <p>Aduan Anda dengan kategori <b>{$dataAduan['kategori']}</b> telah dibalas oleh Admin desa.</p>
                            <p>Silakan login ke website VillageHub dan buka menu <b>Balasan Admin</b> untuk melihat detail balasannya.</p>
                        </div>
                    ";
                    $mail->send();
                } catch (Exception $e) {
                    $email_error = "Balasan tersimpan, TAPI gagal kirim email: " . $mail->ErrorInfo;
                }
            }

            $db->commit();
            
            if (isset($email_error)) {
                $_SESSION['flash_msg'] = ['type' => 'warning', 'text' => $email_error];
            } else {
                $_SESSION['flash_msg'] = ['type' => 'success', 'text' => 'Balasan berhasil disimpan & Notifikasi Email terkirim!'];
            }
            
            header("Location: dashboard.php");
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_msg'] = ['type' => 'danger', 'text' => "Gagal Database: " . $e->getMessage()];
            header("Location: dashboard.php");
            exit;
        }
    }
}

$msg = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']);

// ==========================================
// 3. AMBIL DATA ADUAN
// ==========================================
$filter_status = $_GET['status'] ?? '';
$sql = "SELECT a.*, k.golongan AS kategori, u.nama AS nama_pengadu, (SELECT balasan FROM balasan b WHERE b.id_aduan = a.id_aduan ORDER BY b.id_balasan ASC LIMIT 1) AS isi_balasan FROM aduan a JOIN kategori k ON k.id_kategori = a.id_kategori JOIN users u ON u.id_user = a.id_user";
if ($filter_status == 'menunggu' || $filter_status == 'dibalas') { $sql .= " WHERE a.status = :status"; }
$sql .= " ORDER BY a.tanggal_waktu DESC";
$stmt = $db->prepare($sql);
if ($filter_status == 'menunggu' || $filter_status == 'dibalas') { $stmt->execute([':status' => $filter_status]); } else { $stmt->execute(); }
$aduan_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin – VillageHub</title>
    
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

        .navbar-brand img {
            width: 50px !important;
            height: 50px !important;
            object-fit: contain;
            margin-right: 10px;
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--green-main) 0%, #28a745 50%, #2d8f3e 100%);
            box-shadow: 0 3px 16px rgba(0,0,0,0.15);
        }
        
        .brand-text { font-weight: 800; font-size: 22px; letter-spacing: 0.5px; }
        .text-village { color: #fff; }
        .text-hub { color: var(--yellow); }

        /* ====== STYLING MENU NAVBAR ====== */
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

        .main-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .badge-kategori { padding: 8px 12px; border-radius: 8px; font-weight: 800; font-size: 13px; border: 1px solid #ccc; display: block; text-align: center; }
        .badge-kategori.hijau { background: #a5d6a7; color: #1b5e20; }
        .badge-kategori.kuning { background: var(--yellow); color: #000; }
        .badge-kategori.biru { background: #81d4fa; color: #01579b; }
        
        .badge-status { padding: 8px 15px; border-radius: 20px; font-weight: 800; font-size: 12px; display: block; text-align: center; }
        .badge-status.menunggu { background: var(--yellow); color: #000; }
        .badge-status.dibalas { background: var(--green-dark); color: #fff; }

        .btn-aksi { background: #fff; border: 2px solid #999; border-radius: 8px; font-weight: 700; color: #333; transition: 0.3s; }
        .btn-aksi:hover { background: var(--green-main); color: white; border-color: var(--green-main); }
        .btn-aksi.edit { border-color: #e0a800; color: #e0a800; }
        .btn-aksi.edit:hover { background: var(--yellow); color: #000; border-color: var(--yellow); }

        .footer { background: #1a1a2e; color: #a0a0b0; padding: 24px 5%; margin-top: auto; font-size: 13px; text-align: center; }
        .footer .text-village { color: #fff; }
        .footer .text-hub { color: var(--yellow); }
    </style>
</head>
<body>

    <!-- NAVBAR BOOTSTRAP -->
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
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="riwayat.php">Riwayat</a>
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
            <h2 class="text-center fw-bold mb-4">Daftar Aduan</h2>

            <!-- Alert Bootstrap -->
            <?php if ($msg): ?>
                <div class="alert alert-<?= $msg['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($msg['text']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filter -->
            <div class="d-flex flex-column flex-md-row justify-content-center gap-2 mb-4">
                <a href="dashboard.php?status=menunggu" class="btn btn-warning fw-bold px-4 py-2">Menunggu</a>
                <a href="dashboard.php?status=dibalas" class="btn btn-success fw-bold px-4 py-2" style="background-color: var(--green-dark); border-color: var(--green-dark);">Dibalas</a>
                <a href="dashboard.php" class="btn btn-info fw-bold px-4 py-2 text-white">Semua</a>
            </div>

            <!-- List Aduan -->
            <div class="row g-3">
                <?php if (empty($aduan_list)): ?>
                    <div class="col-12 text-center py-4 text-muted fw-bold">Belum ada aduan.</div>
                <?php else: ?>
                    <?php foreach ($aduan_list as $aduan): ?>
                        <div class="col-12">
                            <div class="d-flex flex-column flex-lg-row align-items-lg-center bg-light p-3 rounded-3 border gap-3">
                                
                                <div class="fw-bold text-dark" style="min-width: 100px;"><?= date('d/m/Y', strtotime($aduan['tanggal_waktu'])) ?></div>
                                <div class="fw-bold text-dark text-truncate" style="min-width: 120px; max-width: 150px;"><?= htmlspecialchars($aduan['nama_pengadu']) ?></div>
                                
                                <div style="min-width: 120px;">
                                    <div class="badge-kategori <?= strtolower($aduan['kategori']) == 'kebersihan' ? 'hijau' : (strtolower($aduan['kategori']) == 'infrastruktur' ? 'biru' : 'kuning') ?>">
                                        <?= htmlspecialchars($aduan['kategori']) ?>
                                    </div>
                                </div>
                                
                                <div style="min-width: 110px;">
                                    <span class="badge-status <?= $aduan['status'] ?>"><?= strtoupper($aduan['status']) ?></span>
                                </div>
                                
                                <div class="ms-lg-auto w-100" style="max-width: 150px;">
                                    <?php if($aduan['status'] == 'dibalas'): ?>
                                        <button class="btn-aksi edit w-100 py-2" onclick="bukaModal(<?= $aduan['id_aduan'] ?>, '<?= htmlspecialchars(addslashes($aduan['deskripsi'])) ?>', '<?= htmlspecialchars(addslashes($aduan['nama_pengadu'])) ?>', '<?= htmlspecialchars(addslashes($aduan['isi_balasan'] ?? '')) ?>')">
                                            ✏️ Edit
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-aksi w-100 py-2" onclick="bukaModal(<?= $aduan['id_aduan'] ?>, '<?= htmlspecialchars(addslashes($aduan['deskripsi'])) ?>', '<?= htmlspecialchars(addslashes($aduan['nama_pengadu'])) ?>', '')">
                                            Balas
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MODAL BOOTSTRAP -->
    <div class="modal fade" id="modalBalas" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-success" id="modalTitle">Balas Aduan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <p class="mb-2">Aduan dari: <strong id="modal-nama"></strong></p>
                        <div class="bg-light p-3 rounded mb-3 border-start border-4 border-success" id="modal-deskripsi" style="max-height: 150px; overflow-y: auto;"></div>
                        
                        <input type="hidden" name="id_aduan" id="modal-id-aduan">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Balasan Anda</label>
                            <textarea name="balasan" id="modal-isi-balasan" class="form-control" rows="4" placeholder="Ketik balasan..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="kirim_balasan" class="btn btn-success fw-bold">Simpan & Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
        &copy; 2026 <span class="text-village fw-bold">VILLAGE</span> <span class="text-hub fw-bold">HUB</span>. Semua hak cipta dilindungi.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
            
            var myModal = new bootstrap.Modal(document.getElementById('modalBalas'));
            myModal.show();
        }
    </script>
</body>
</html>