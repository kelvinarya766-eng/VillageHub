<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/auth.php';

$user = requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(["success"=>false,"message"=>"Method harus POST"],405);

$in = body();
$id_aduan = intval($in['id_aduan'] ?? 0);

if ($id_aduan <= 0) jsonResponse(["success"=>false,"message"=>"ID Aduan tidak valid"],422);

$db = (new Database())->getConnection();

// Pastikan aduan ini milik user yang sedang login
$cek = $db->prepare("SELECT foto FROM aduan WHERE id_aduan = :id AND id_user = :u");
$cek->execute([':id'=>$id_aduan, ':u'=>$user['id_user']]);
$aduan = $cek->fetch();

if (!$aduan) {
    jsonResponse(["success"=>false,"message"=>"Aduan tidak ditemukan atau bukan milik Anda"], 404);
}

// Hapus file foto jika ada
if (!empty($aduan['foto'])) {
    $fileFoto = __DIR__ . '/../../uploads/' . $aduan['foto'];
    if (file_exists($fileFoto)) {
        unlink($fileFoto); // Hapus file fisik
    }
}

// Hapus dari database
$stmt = $db->prepare("DELETE FROM aduan WHERE id_aduan = :id");
$stmt->execute([':id'=>$id_aduan]);

jsonResponse(["success"=>true, "message"=>"Aduan berhasil dihapus"]);