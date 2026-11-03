<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/auth.php';

$user = requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(["success"=>false,"message"=>"Method harus POST"],405);
}

if (empty($_FILES['foto']['name'])) {
    jsonResponse(["success"=>false,"message"=>"File foto wajib diunggah"],422);
}

$ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
$allowed = ['jpg','jpeg','png','webp'];
if (!in_array($ext, $allowed)) jsonResponse(["success"=>false,"message"=>"Format foto harus JPG/PNG/WEBP"],415);
if ($_FILES['foto']['size'] > 2*1024*1024) jsonResponse(["success"=>false,"message"=>"Ukuran max 2MB"],413);

$dir = __DIR__.'/../../uploads/';
if (!is_dir($dir)) mkdir($dir, 0777, true);
$namaFoto = 'user_'.$user['id_user'].'_'.time().'.'.$ext;

if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir.$namaFoto)) {
    $db = (new Database())->getConnection();
    
    // Ambil foto lama untuk dihapus (opsional)
    $stmt = $db->prepare("SELECT foto FROM users WHERE id_user=:id");
    $stmt->execute([':id'=>$user['id_user']]);
    $oldFoto = $stmt->fetchColumn();

    // Update database
    $stmt = $db->prepare("UPDATE users SET foto=:f WHERE id_user=:id");
    $stmt->execute([':f'=>$namaFoto, ':id'=>$user['id_user']]);

    // Hapus foto lama jika ada
    if ($oldFoto && file_exists($dir.$oldFoto)) {
        unlink($dir.$oldFoto);
    }

    // Simpan ke session
    $_SESSION['user']['foto'] = $namaFoto;

    jsonResponse(["success"=>true, "message"=>"Foto profil berhasil diperbarui", "foto"=>$namaFoto]);
} else {
    jsonResponse(["success"=>false,"message"=>"Gagal mengunggah foto"],500);
}