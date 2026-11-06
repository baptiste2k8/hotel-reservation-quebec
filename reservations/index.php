<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$isAdmin = ($_SESSION["role"] ?? "") === "admin";
$userId = (int)$_SESSION["user_id"];

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if ($isAdmin) {
    $stmt = $pdo->query(
        "SELECT r.*, u.full_name, rm.room_number
         FROM reservations r
         JOIN users u ON r.user_id = u.id
         JOIN rooms rm ON r.room_id = rm.id
         ORDER BY r.id DESC"
    );
} else {
    $stmt = $pdo->prepare(
        "SELECT r.*, u.full_name, rm.room_number
         FROM reservations r
         JOIN users u ON r.user_id = u.id
         JOIN rooms rm ON r.room_id = rm.id
         WHERE r.user_id = ?
         ORDER BY r.id DESC"
    );
    $stmt->execute([$userId]);
}

$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reservations - Hotel Quebec</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/sidebar.php"; ?>

<div class="dashboard-content">
    <h1>Reservations</h1>

    <a href="add.php" class="btn">New Reservation</a>

    <table class="data-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Room</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Total (CAD)</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($reservations as $r): ?>
            <tr>
                <td><?= e($r["id"]) ?></td>
                <td><?= e($r["full_name"]) ?></td>
                <td><?= e($r["room_number"]) ?></td>
                <td><?= e($r["check_in"]) ?></td>
                <td><?= e($r["check_out"]) ?></td>
                <td><?= e($r["total_price"]) ?></td>
                <td><?= e($r["status"]) ?></td>
                <td>
                    <?php if ($r["status"] !== "cancelled"): ?>
                        <form method="POST" action="cancel.php">
                            <input type="hidden" name="id"
                                   value="<?= (int)$r["id"] ?>">
                            <input type="hidden" name="csrf_token"
                                   value="<?= e($_SESSION["csrf_token"]) ?>">
                            <button type="submit">Cancel</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($isAdmin): ?>
                        <a href="status.php?id=<?= (int)$r["id"] ?>">
                            Update Status
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
