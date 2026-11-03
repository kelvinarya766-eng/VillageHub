<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../config/response.php';
require_once __DIR__.'/../../config/auth.php';

requireAdmin();
$db = (new Database())->getConnection();

$page   = max(1, intval($_GET['page'] ?? 1));
$limit  = min(50, intval($_GET['limit'] ?? 10));
$offset = ($page-1)*$limit;

$status = $_GET['status'] ?? null;

$where = "WHERE 1=1"; $params=[];
if ($status && in_array($status,['menunggu','dibalas'])) {
    $where .= " AND a.status = :s"; $params[':s']=$status;
}

$count = $db->prepare("SELECT COUNT(*) FROM aduan a $where");
$count->execute($params);
$total = (int)$count->fetchColumn();

$sql = "SELECT a.id_aduan, a.tanggal_waktu, a.deskripsi, a.status,
               k.golongan AS kategori, u.nama AS nama_pengadu
        FROM aduan a
        JOIN kategori k ON k.id_kategori = a.id_kategori
        JOIN users u    ON u.id_user = a.id_user
        $where
        ORDER BY a.tanggal_waktu DESC
        LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);

jsonResponse([
    "success"=>true,
    "page"=>$page,
    "limit"=>$limit,
    "total"=>$total,
    "total_page"=>ceil($total/$limit),
    "data"=>$stmt->fetchAll()
]);