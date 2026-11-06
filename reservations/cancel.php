<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

if (
    !isset($_POST["csrf_token"], $_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    http_response_code(403);
    exit("Invalid CSRF token.");
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    exit("Invalid reservation.");
}

if (($_SESSION["role"] ?? "") === "admin") {
    $stmt = $pdo->prepare(
        "UPDATE reservations SET status = 'cancelled'
         WHERE id = ?"
    );
    $stmt->execute([$id]);
} else {
    $stmt = $pdo->prepare(
        "UPDATE reservations SET status = 'cancelled'
         WHERE id = ? AND user_id = ?"
    );
    $stmt->execute([$id, $_SESSION["user_id"]]);
}

header("Location: index.php");
exit;
