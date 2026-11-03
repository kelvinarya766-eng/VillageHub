<?php
// File: api/user/update_profil.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/auth.php';

// Wajib login
$user = requireLogin();

// Wajib method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(["success" => false, "message" => "Method harus POST"], 405);
}

// Ambil input JSON
$in = body();
$nama     = trim($in['nama'] ?? '');
$telepon  = trim($in['nomor_telepon'] ?? '');
$alamat   = trim($in['alamat'] ?? '');
$password = $in['password'] ?? '';

if ($nama === '') {
    jsonResponse(["success" => false, "message" => "Nama wajib diisi"], 422);
}

try {
    $db = (new Database())->getConnection();

    if (!empty($password)) {
        if (strlen($password) < 6) {
            jsonResponse(["success" => false, "message" => "Password minimal 6 karakter"], 422);
        }
        $stmt = $db->prepare("UPDATE users SET nama=:n, nomor_telepon=:t, alamat=:a, password=:p WHERE id_user=:id");
        $stmt->execute([
            ':n' => $nama, 
            ':t' => $telepon, 
            ':a' => $alamat, 
            ':p' => password_hash($password, PASSWORD_DEFAULT), 
            ':id' => $user['id_user']
        ]);
    } else {
        $stmt = $db->prepare("UPDATE users SET nama=:n, nomor_telepon=:t, alamat=:a WHERE id_user=:id");
        $stmt->execute([
            ':n' => $nama, 
            ':t' => $telepon, 
            ':a' => $alamat, 
            ':id' => $user['id_user']
        ]);
    }

    // Update session nama agar navbar langsung berubah
    $_SESSION['user']['nama'] = $nama;

    jsonResponse(["success" => true, "message" => "Data sudah Terupdate"]);

} catch (Exception $e) {
    // Tangkap error database agar tidak merusak format JSON
    jsonResponse(["success" => false, "message" => "Database Error: " . $e->getMessage()], 500);
}