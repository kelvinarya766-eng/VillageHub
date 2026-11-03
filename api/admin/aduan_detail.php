<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../config/response.php';
require_once __DIR__.'/../../config/auth.php';

requireAdmin();
$id = intval($_GET['id'] ?? 0);
if ($id<=0) jsonResponse(["success"=>false,"message"=>"id wajib diisi"],422);

$db = (new Database())->getConnection();

$stmt = $db->prepare("
    SELECT a.*, k.golongan AS kategori, u.nama AS nama_pengadu
    FROM aduan a
    JOIN kategori k ON k.id_kategori = a.id_kategori
    JOIN users u    ON u.id_user = a.id_user
    WHERE a.id_aduan = :id
");
$stmt->execute([':id'=>$id]);
$aduan = $stmt->fetch();

if (!$aduan) jsonResponse(["success"=>false,"message"=>"Aduan tidak ditemukan"],404);

$stmt = $db->prepare("
    SELECT b.id_balasan, b.tanggal_waktu, b.balasan, u.nama AS admin_nama
    FROM balasan b
    JOIN users u ON u.id_user = b.id_user
    WHERE b.id_aduan = :id
    ORDER BY b.tanggal_waktu ASC
");
$stmt->execute([':id'=>$id]);
$aduan['balasan'] = $stmt->fetchAll();

jsonResponse(["success"=>true,"data"=>$aduan]);