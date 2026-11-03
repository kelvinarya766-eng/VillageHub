<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../config/response.php';
require_once __DIR__.'/../../config/auth.php';

$user = requireLogin();
$db = (new Database())->getConnection();

$kategori = intval($_GET['kategori'] ?? 0);

$sql = "SELECT a.id_aduan, a.tanggal_waktu, a.deskripsi, a.foto, a.status,
               k.golongan AS kategori,
               (SELECT COUNT(*) FROM balasan b WHERE b.id_aduan=a.id_aduan) AS jumlah_balasan
        FROM aduan a
        JOIN kategori k ON k.id_kategori = a.id_kategori
        WHERE a.id_user = :u";
$params = [':u'=>$user['id_user']];

if ($kategori > 0) {
    $sql .= " AND a.id_kategori = :kat";
    $params[':kat'] = $kategori;
}
$sql .= " ORDER BY a.tanggal_waktu DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);

jsonResponse(["success"=>true,"data"=>$stmt->fetchAll()]);