<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../config/response.php';
require_once __DIR__.'/../../config/auth.php';

requireAdmin();
$db = (new Database())->getConnection();

$status   = $_GET['status'] ?? null;
$kategori = intval($_GET['kategori'] ?? 0);
$q        = trim($_GET['q'] ?? '');

$sql = "SELECT a.id_aduan, a.tanggal_waktu, a.deskripsi, a.foto, a.status,
               k.golongan AS kategori, u.nama AS nama_pengadu,
               (SELECT COUNT(*) FROM balasan b WHERE b.id_aduan=a.id_aduan) AS jumlah_balasan
        FROM aduan a
        JOIN kategori k ON k.id_kategori = a.id_kategori
        JOIN users u    ON u.id_user = a.id_user
        WHERE 1=1";
$params = [];

if ($status && in_array($status,['menunggu','dibalas'])) {
    $sql .= " AND a.status = :st"; $params[':st'] = $status;
}
if ($kategori > 0) {
    $sql .= " AND a.id_kategori = :kat"; $params[':kat'] = $kategori;
}
if ($q !== '') {
    $sql .= " AND a.deskripsi LIKE :q"; $params[':q'] = "%$q%";
}
$sql .= " ORDER BY a.tanggal_waktu DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);

jsonResponse(["success"=>true,"data"=>$stmt->fetchAll()]);