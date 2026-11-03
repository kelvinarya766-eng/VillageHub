<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../config/response.php';
require_once __DIR__.'/../../config/auth.php';

requireAdmin();
$db = (new Database())->getConnection();

$total    = (int)$db->query("SELECT COUNT(*) FROM aduan")->fetchColumn();
$menunggu = (int)$db->query("SELECT COUNT(*) FROM aduan WHERE status='menunggu'")->fetchColumn();
$dibalas  = (int)$db->query("SELECT COUNT(*) FROM aduan WHERE status='dibalas'")->fetchColumn();

$kategori = $db->query("
    SELECT k.golongan, COUNT(a.id_aduan) AS jumlah
    FROM kategori k
    LEFT JOIN aduan a ON a.id_kategori = k.id_kategori
    GROUP BY k.id_kategori
    ORDER BY jumlah DESC
")->fetchAll();

jsonResponse([
    "success"=>true,
    "statistik"=>[
        "total_aduan" => $total,
        "menunggu"    => $menunggu,
        "dibalas"     => $dibalas
    ],
    "per_kategori" => $kategori
]);