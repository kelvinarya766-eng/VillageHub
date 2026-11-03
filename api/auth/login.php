<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(["success"=>false,"message"=>"Method harus POST"],405);

$in = body();
$email    = trim($in['email'] ?? '');
$password = $in['password'] ?? '';

if ($email === '' || $password === '') {
    jsonResponse(["success"=>false,"message"=>"Email & password wajib diisi"], 422);
}

try {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("SELECT id_user,nama,email,password,role FROM users WHERE email = :e");
    $stmt->execute([':e'=>$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        jsonResponse(["success"=>false,"message"=>"Email atau password salah"], 401);
    }

    $_SESSION['user'] = [
        "id_user" => $user['id_user'],
        "nama"    => $user['nama'],
        "email"   => $user['email'],
        "role"    => $user['role']
    ];

    jsonResponse([
        "success"=>true,
        "message"=>"Login berhasil",
        "user"=>$_SESSION['user']
    ]);
} catch (Exception $e) {
    jsonResponse(["success"=>false, "message"=>"Sistem Error: " . $e->getMessage()], 500);
}