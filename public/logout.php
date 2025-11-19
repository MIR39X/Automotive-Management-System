<?php
session_start();
$_SESSION = [];
session_destroy();

$base = '/ams_project';
header("Location: {$base}/public/login.php");
exit;
