<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ewarung_podomoro";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die(json_encode(["status" => false, "message" => "Koneksi database gagal: " . mysqli_connect_error()]));
}
?>