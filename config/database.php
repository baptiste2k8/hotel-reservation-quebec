<?php
$host = "localhost";
$dbname = "hotel_quebec";
$username = "hotel_user";
$password = "admin!2026!";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Database connection failed.");
}
