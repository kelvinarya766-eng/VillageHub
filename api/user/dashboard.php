<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../config/response.php';
require_once __DIR__.'/../../config/auth.php';

$user = requireLogin();
$db = (new Database())->getConnection();

$total   = $db->prepare("SELECT COUNT(*) FROM aduan WHERE id_user=:u");
$total->execute([':u'=>$user['id_user']]);

$menunggu = $db->prepare("SELECT COUNT(*) FROM aduan WHERE id_user=:u AND status='menunggu'");
$menunggu->execute([':u'=>$user['id_user']]);

$dibalas = $db->prepare("SELECT COUNT(*) FROM aduan WHERE id_user=:u AND status='dibalas'");
$dibalas->execute([':u'=>$user['id_user']]);

$balasan = $db->prepare("SELECT COUNT(*) FROM balasan WHERE id_aduan IN 
                         (SELECT id_aduan FROM aduan WHERE id_user=:u)");
$balasan->execute([':u'=>$user['id_user']]);

jsonResponse([
    "success"=>true,
    "statistik"=>[
        "total_aduan"   => (int)$total->fetchColumn(),
        "menunggu"      => (int)$menunggu->fetchColumn(),
        "dibalas"       => (int)$dibalas->fetchColumn(),
        "total_balasan" => (int)$balasan->fetchColumn()
    ]
]);