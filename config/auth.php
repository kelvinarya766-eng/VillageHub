<?php
session_start();

function requireLogin() {
    if (empty($_SESSION['user'])) {
        jsonResponse(["success"=>false,"message"=>"Anda belum login"], 401);
    }
    return $_SESSION['user'];
}

function requireAdmin() {
    $u = requireLogin();
    if ($u['role'] !== 'admin') {
        jsonResponse(["success"=>false,"message"=>"Akses ditolak"], 403);
    }
    return $u;
}