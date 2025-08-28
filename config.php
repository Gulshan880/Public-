<?php
$host = "localhost";
$dbname = "apna_db";
$username = "apna_user";
$password = "apna_password";
$base_url = "https://apnidomain.com/";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

session_start();
?>