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

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    exit("Invalid customer ID.");
}

$stmt = $pdo->prepare(
    "SELECT id, full_name, email, created_at
     FROM users
     WHERE id = ? AND role = 'customer'"
);
$stmt->execute([$id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    http_response_code(404);
    exit("Customer not found.");
}

$stmt = $pdo->prepare(
    "SELECT r.id, rm.room_number, r.check_in,
            r.check_out, r.total_price, r.status
     FROM reservations r
     JOIN rooms rm ON r.room_id = rm.id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC"
);
$stmt->execute([$id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Details - Hotel Quebec</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/sidebar.php"; ?>

<div class="dashboard-content">

    <h1>Customer Details</h1>

    <p><strong>Name:</strong> <?= e($customer["full_name"]) ?></p>
    <p><strong>Email:</strong> <?= e($customer["email"]) ?></p>
    <p><strong>Registered:</strong> <?= e($customer["created_at"]) ?></p>

    <h2>Reservation History</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>Reservation ID</th>
                <th>Room</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Total (CAD)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($reservations as $r): ?>
            <tr>
                <td><?= e($r["id"]) ?></td>
                <td><?= e($r["room_number"]) ?></td>
                <td><?= e($r["check_in"]) ?></td>
                <td><?= e($r["check_out"]) ?></td>
                <td><?= e($r["total_price"]) ?></td>
                <td><?= e($r["status"]) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <p>
        <a href="customers.php" class="btn">Back to Customers</a>
    </p>

</div>
</body>
</html>
