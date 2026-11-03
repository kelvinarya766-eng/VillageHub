<?php
require_once __DIR__.'/../../config/response.php';
require_once __DIR__.'/../../config/auth.php';

if (empty($_SESSION['user'])) {
    jsonResponse(["success"=>false,"message"=>"Belum login","logged_in"=>false], 401);
}
jsonResponse(["success"=>true,"logged_in"=>true,"user"=>$_SESSION['user']]);