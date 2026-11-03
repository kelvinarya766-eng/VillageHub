<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../config/response.php';
require_once __DIR__.'/../../config/auth.php';

$user = requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(["success"=>false,"message"=>"Method harus POST"],405);

$id_kategori = intval($_POST['id_kategori'] ?? 0);
$deskripsi   = trim($_POST['deskripsi'] ?? '');

if ($id_kategori <= 0)   jsonResponse(["success"=>false,"message"=>"Kategori wajib dipilih"],422);
if (strlen($deskripsi)<10) jsonResponse(["success"=>false,"message"=>"Deskripsi minimal 10 karakter"],422);

$db = (new Database())->getConnection();

$namaFoto = null;
if (!empty($_FILES['foto']['name'])) {
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];
    if (!in_array($ext, $allowed)) jsonResponse(["success"=>false,"message"=>"Format foto harus JPG/PNG/WEBP"],415);
    if ($_FILES['foto']['size'] > 2*1024*1024) jsonResponse(["success"=>false,"message"=>"Ukuran max 2MB"],413);

    $dir = __DIR__.'/../../uploads/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $namaFoto = 'aduan_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
    move_uploaded_file($_FILES['foto']['tmp_name'], $dir.$namaFoto);
}

$stmt = $db->prepare("INSERT INTO aduan (id_user,id_kategori,deskripsi,foto,status) 
                      VALUES (:u,:k,:d,:f,'menunggu')");
$stmt->execute([
    ':u'=>$user['id_user'], ':k'=>$id_kategori,
    ':d'=>$deskripsi, ':f'=>$namaFoto
]);

jsonResponse([
    "success"=>true,
    "message"=>"Berhasil mengirim aduan",
    "id_aduan"=>$db->lastInsertId()
], 201);