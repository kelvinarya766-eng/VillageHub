<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../config/response.php';
require_once __DIR__.'/../../config/auth.php';

$admin = requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(["success"=>false,"message"=>"Method harus POST"],405);

$in = body();
$id_aduan = intval($in['id_aduan'] ?? 0);
$balasan  = trim($in['balasan'] ?? '');

if ($id_aduan<=0 || $balasan==='') {
    jsonResponse(["success"=>false,"message"=>"id_aduan & balasan wajib diisi"],422);
}

$db = (new Database())->getConnection();

$cek = $db->prepare("SELECT id_aduan FROM aduan WHERE id_aduan=:id");
$cek->execute([':id'=>$id_aduan]);
if (!$cek->fetch()) jsonResponse(["success"=>false,"message"=>"Aduan tidak ditemukan"],404);

$db->beginTransaction();
try {
    $stmt = $db->prepare("INSERT INTO balasan (id_aduan,id_user,balasan) VALUES (:a,:u,:b)");
    $stmt->execute([':a'=>$id_aduan, ':u'=>$admin['id_user'], ':b'=>$balasan]);

    $db->prepare("UPDATE aduan SET status='dibalas' WHERE id_aduan=:id")
       ->execute([':id'=>$id_aduan]);

    $db->commit();
    jsonResponse(["success"=>true,"message"=>"Balasan berhasil dikirim"]);
} catch (Exception $e) {
    $db->rollBack();
    jsonResponse(["success"=>false,"message"=>"Gagal: ".$e->getMessage()],500);
}