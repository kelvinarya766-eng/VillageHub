<?php
// Menampilkan error jika ada masalah (untuk debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/response.php';

// Panggil PHPMailer manual
require_once __DIR__ . '/../../vendor/src/Exception.php';
require_once __DIR__ . '/../../vendor/src/PHPMailer.php';
require_once __DIR__ . '/../../vendor/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(["success"=>false,"message"=>"Method harus POST"], 405);
}

$in = body();
$action = $in['action'] ?? '';

// ==========================================
// AKSI 1: KIRIM OTP LUPA SANDI
// ==========================================
if ($action === 'send_otp') {
    $email = trim($in['email'] ?? '');
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(["success"=>false,"message"=>"Format email tidak valid"], 422);
    }

    $db = (new Database())->getConnection();
    $cek = $db->prepare("SELECT id_user, nama FROM users WHERE email = :e");
    $cek->execute([':e'=>$email]);
    $user = $cek->fetch();

    if (!$user) {
        jsonResponse(["success"=>false,"message"=>"Email tidak ditemukan dalam sistem"], 404);
    }

    // Generate OTP
    $otp = random_int(100000, 999999);
    
    // Simpan di session
    $_SESSION['reset_otp'] = [
        'email' => $email,
        'kode' => (string)$otp,
        'expiry' => time() + 300
    ];

    // Kirim Email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'c17bbb001@smtp-brevo.com'; // Login Brevo kamu
        $mail->Password   = 'bskQDiSqYDpApOX';          // SMTP Key kamu
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 2525;

        $mail->setFrom('kelvinarya766@gmail.com', 'VillageHub');
        $mail->addAddress($email, $user['nama']);
        
        $mail->isHTML(true);
        $mail->Subject = 'Kode OTP Reset Sandi VillageHub';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                <h2 style='color: #1b7a2b; text-align: center;'>VillageHub</h2>
                <p>Halo <b>{$user['nama']}</b>,</p>
                <p>Kami menerima permintaan untuk mereset kata sandi akun Anda. Berikut adalah kode OTP Anda:</p>
                <h1 style='text-align: center; letter-spacing: 5px; color: #333; background: #f4f4f4; padding: 15px; border-radius: 5px;'>$otp</h1>
                <p style='color: #888; font-size: 12px;'>Kode ini berlaku 5 menit. Jika Anda tidak meminta reset sandi, abaikan email ini.</p>
            </div>
        ";
        
        $mail->send();
        jsonResponse(["success"=>true, "message"=>"OTP berhasil dikirim"]);
    } catch (Exception $e) {
        unset($_SESSION['reset_otp']);
        jsonResponse(["success"=>false, "message"=>"Gagal mengirim email: {$mail->ErrorInfo}"], 500);
    }
}

// ==========================================
// AKSI 2: VERIFIKASI OTP
// ==========================================
if ($action === 'verify_otp') {
    $email = trim($in['email'] ?? '');
    $otp = trim($in['otp'] ?? '');

    if (empty($_SESSION['reset_otp'])) {
        jsonResponse(["success"=>false, "message"=>"Sesi habis, silakan ulangi"], 400);
    }

    $data = $_SESSION['reset_otp'];

    if (time() > $data['expiry'] || $data['email'] !== $email) {
        unset($_SESSION['reset_otp']);
        jsonResponse(["success"=>false, "message"=>"Kode OTP kedaluwarsa"], 400);
    }

    if ($otp !== $data['kode']) {
        jsonResponse(["success"=>false, "message"=>"Kode OTP salah"], 401);
    }

    jsonResponse(["success"=>true, "message"=>"OTP Valid"]);
}

// ==========================================
// AKSI 3: SIMPAN SANDI BARU
// ==========================================
if ($action === 'reset_password') {
    $email = trim($in['email'] ?? '');
    $password = $in['password'] ?? '';

    if (strlen($password) < 6) {
        jsonResponse(["success"=>false, "message"=>"Sandi minimal 6 karakter"], 422);
    }

    // Pastikan session verifikasi masih ada
    if (empty($_SESSION['reset_otp']) || $_SESSION['reset_otp']['email'] !== $email) {
        jsonResponse(["success"=>false, "message"=>"Akses ilegal. Silakan ulangi proses."], 403);
    }

    try {
        $db = (new Database())->getConnection();
        $stmt = $db->prepare("UPDATE users SET password=:p WHERE email=:e");
        $stmt->execute([
            ':p'=>password_hash($password, PASSWORD_DEFAULT),
            ':e'=>$email
        ]);

        unset($_SESSION['reset_otp']); // Hapus session jika sukses
        
        jsonResponse(["success"=>true, "message"=>"Sandi berhasil diubah"]);
    } catch (Exception $e) {
        jsonResponse(["success"=>false, "message"=>"Database Error: " . $e->getMessage()], 500);
    }
}