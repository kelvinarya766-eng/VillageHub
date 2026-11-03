<?php
require_once __DIR__.'/../../config/response.php';
require_once __DIR__.'/../../config/auth.php';

session_unset();
session_destroy();

jsonResponse([
    "success"=>true,
    "message"=>"Anda telah logout"
]);