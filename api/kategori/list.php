<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../config/response.php';

$db = (new Database())->getConnection();
$data = $db->query("SELECT id_kategori, golongan FROM kategori ORDER BY id_kategori")->fetchAll();

jsonResponse(["success"=>true,"total"=>count($data),"data"=>$data]);