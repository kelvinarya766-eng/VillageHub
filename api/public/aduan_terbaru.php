<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../config/response.php';

// ============================================
// ENDPOINT PUBLIK — TANPA LOGIN
// Berfungsi untuk beranda publik
// Menampilkan aduan terbaru dari semua warga
// ============================================

$db = (new Database())->getConnection();

// Parameter opsional dari URL
$kategori = intval($_GET['kategori'] ?? 0);  // filter by kategori
$limit    = min(20, intval($_GET['limit'] ?? 10)); // max 20 aduan

// Query dasar
$sql = "SELECT a.id_aduan, a.tanggal_waktu, a.deskripsi, a.foto, a.status,
               a.id_kategori, 
               k.golongan AS kategori, 
               u.nama AS nama_pengadu
        FROM aduan a
        JOIN kategori k ON k.id_kategori = a.id_kategori
        JOIN users u    ON u.id_user = a.id_user
        WHERE 1=1";

$params = [];

// Filter kategori (kalau ada)
if ($kategori > 0) {
    $sql .= " AND a.id_kategori = :kat";
    $params[':kat'] = $kategori;
}

// Urutkan terbaru & limit
$sql .= " ORDER BY a.tanggal_waktu DESC LIMIT $limit";

// Eksekusi
$stmt = $db->prepare($sql);
$stmt->execute($params);

// Response
jsonResponse([
    "success" => true,
    "total"   => $stmt->rowCount(),
    "data"    => $stmt->fetchAll()
]);