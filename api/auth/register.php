<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../config/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(["success"=>false,"message"=>"Method harus POST"],405);

$in = body();
$nama     = trim($in['nama'] ?? '');
$email    = trim($in['email'] ?? '');
$telepon  = trim($in['nomor_telepon'] ?? '');
$alamat   = trim($in['alamat'] ?? '');
$password = $in['password'] ?? '';

$errors = [];
if ($nama === '')                               $errors['nama'] = "Nama wajib diisi";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Email tidak valid";
if (strlen($password) < 6)                      $errors['password'] = "Password minimal 6 karakter";

if ($errors) jsonResponse(["success"=>false,"errors"=>$errors], 422);

$db = (new Database())->getConnection();

$cek = $db->prepare("SELECT id_user FROM users WHERE email = :e");
$cek->execute([':e'=>$email]);
if ($cek->fetch()) {
    jsonResponse(["success"=>false,"message"=>"Email sudah terdaftar"], 409);
}

$stmt = $db->prepare("INSERT INTO users (nama,email,nomor_telepon,alamat,password,role) 
                      VALUES (:n,:e,:t,:a,:p,'warga')");
$stmt->execute([
    ':n'=>$nama, ':e'=>$email, ':t'=>$telepon,
    ':a'=>$alamat, ':p'=>password_hash($password, PASSWORD_DEFAULT)
]);

jsonResponse([
    "success"=>true,
    "message"=>"Registrasi berhasil, silakan login",
    "id_user"=>$db->lastInsertId()
], 201);