<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../config/response.php';

// ==========================================
// MEMANGGIL PHPMailer SECARA MANUAL (TANPA COMPOSER)
// ==========================================
require_once __DIR__.'/../../vendor/src/Exception.php';
require_once __DIR__.'/../../vendor/src/PHPMailer.php';
require_once __DIR__.'/../../vendor/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(["success"=>false,"message"=>"Method harus POST"], 405);
}

$in = body();
$action = $in['action'] ?? 'send_otp';

// ==========================================
// AKSI 1: VALIDASI DATA & KIRIM OTP
// ==========================================
if ($action === 'send_otp') {
    $nama     = trim($in['nama'] ?? '');
    $email    = trim($in['email'] ?? '');
    $telepon  = trim($in['nomor_telepon'] ?? '');
    $alamat   = trim($in['alamat'] ?? '');
    $password = $in['password'] ?? '';

    // Validasi Input
    $errors = [];
    if ($nama === '') $errors['nama'] = "Nama wajib diisi";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Email tidak valid";
    if (strlen($password) < 6) $errors['password'] = "Password minimal 6 karakter";
    
    // Validasi Nomor Telepon 12 Angka
    if ($telepon === '') {
        $errors['nomor_telepon'] = "Nomor telepon wajib diisi";
    } elseif (!preg_match('/^\d+$/', $telepon)) {
        $errors['nomor_telepon'] = "Nomor telepon hanya boleh berisi angka";
    } elseif (strlen($telepon) !== 12) {
        $errors['nomor_telepon'] = "Nomor telepon harus tepat 12 angka";
    }

    if ($errors) {
        jsonResponse(["success"=>false,"errors"=>$errors], 422);
    }

    $db = (new Database())->getConnection();
    
    // Cek email sudah terdaftar atau belum
    $cek = $db->prepare("SELECT id_user FROM users WHERE email = :e");
    $cek->execute([':e'=>$email]);
    if ($cek->fetch()) {
        jsonResponse(["success"=>false,"message"=>"Email sudah terdaftar"], 409);
    }

    // Generate OTP 6 Digit
    $otp = random_int(100000, 999999);
    
    // Simpan data sementara + OTP di session
    $_SESSION['otp_data'] = [
        'nama' => $nama, 
        'email' => $email, 
        'telepon' => $telepon,
        'alamat' => $alamat, 
        'password' => $password,
        'kode' => (string)$otp, 
        'expiry' => time() + 300 // Berlaku 5 menit
    ];

    // Kirim Email via PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com'; 
        $mail->SMTPAuth   = true;
        
        // ==========================================
        // GANTI BAGIAN INI DENGAN DATA DARI BREVO KAMU
        // ==========================================
        $mail->Username   = 'c17bbb001@smtp-brevo.com';  // Login dari Brevo
        $mail->Password   = 'bskQDiSqYDpApOX'; // SMTP Key dari Brevo
        // ==========================================
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587; // Port SMTP Brevo

        // Pengirim (Harus email yang sudah diverifikasi di Brevo)
        $mail->setFrom('kelvinarya766@gmail.com', 'VillageHub');
        
        // Tujuan
        $mail->addAddress($email, $nama);
        
        // Konten Email
        $mail->isHTML(true);
        $mail->Subject = 'Kode OTP Registrasi VillageHub';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                <h2 style='color: #1b7a2b; text-align: center;'>VillageHub</h2>
                <p>Halo <b>$nama</b>,</p>
                <p>Terima kasih telah mendaftar. Berikut adalah kode OTP untuk menyelesaikan registrasi akun Anda:</p>
                <h1 style='text-align: center; letter-spacing: 5px; color: #333; background: #f4f4f4; padding: 15px; border-radius: 5px;'>$otp</h1>
                <p style='color: #888; font-size: 12px;'>Kode ini hanya berlaku selama 5 menit. Jangan bagikan kode ini kepada siapa pun.</p>
            </div>
        ";
        
        $mail->send();
        jsonResponse(["success"=>true, "message"=>"OTP telah berhasil dikirim ke email"]);
        
    } catch (Exception $e) {
        // Hapus session jika gagal kirim email agar user bisa coba lagi
        unset($_SESSION['otp_data']); 
        jsonResponse(["success"=>false, "message"=>"Gagal mengirim email: {$mail->ErrorInfo}"], 500);
    }
}

// ==========================================
// AKSI 2: VERIFIKASI OTP & SIMPAN USER KE DB
// ==========================================
if ($action === 'verify_otp') {
    $otpInput = trim($in['otp'] ?? '');

    if (empty($_SESSION['otp_data'])) {
        jsonResponse(["success"=>false, "message"=>"Sesi habis, silakan daftar ulang dari awal"], 400);
    }

    $data = $_SESSION['otp_data'];

    // Cek Kedaluwarsa (5 Menit)
    if (time() > $data['expiry']) {
        unset($_SESSION['otp_data']);
        jsonResponse(["success"=>false, "message"=>"Kode OTP sudah kedaluwarsa, silakan minta ulang"], 400);
    }

    // Cocokkan Kode
    if ($otpInput !== $data['kode']) {
        jsonResponse(["success"=>false, "message"=>"Kode OTP salah, silakan periksa kembali"], 401);
    }

    // Jika Benar -> Simpan ke Database
    try {
        $db = (new Database())->getConnection();
        $stmt = $db->prepare("INSERT INTO users (nama,email,nomor_telepon,alamat,password,role) VALUES (:n,:e,:t,:a,:p,'warga')");
        $stmt->execute([
            ':n'=>$data['nama'], 
            ':e'=>$data['email'], 
            ':t'=>$data['telepon'],
            ':a'=>$data['alamat'], 
            ':p'=>password_hash($data['password'], PASSWORD_DEFAULT)
        ]);

        // Hapus session OTP setelah berhasil
        unset($_SESSION['otp_data']); 
        
        jsonResponse(["success"=>true, "message"=>"Registrasi berhasil, silakan login"]);
    } catch (Exception $e) {
        jsonResponse(["success"=>false, "message"=>"Database Error: " . $e->getMessage()], 500);
    }
}