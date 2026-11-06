<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Access denied.");
}

require_once "../config/database.php";

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
    exit("Invalid customer ID.");
}

// Delete only customers with no reservations
$stmt = $pdo->prepare(
    "DELETE FROM users
     WHERE id = ?
       AND role = 'customer'
       AND NOT EXISTS (
           SELECT 1 FROM reservations WHERE user_id = ?
       )"
);

$stmt->execute([$id, $id]);

header("Location: customers.php");
exit;
