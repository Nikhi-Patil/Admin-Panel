<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "admin";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Connection Failed: " . $conn->connect_error);
}

if (!defined('BASE_URL')) {
    define("BASE_URL", "/admin/");
}
?>