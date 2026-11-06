<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/database.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$search = $_GET["room"] ?? "";

// INTENTIONALLY VULNERABLE: SQL Injection
$sql = "SELECT id, room_number, room_type, price
        FROM rooms
        WHERE room_number = '$search'";

try {
    $stmt = $pdo->query($sql);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $error = "";
} catch (PDOException $exception) {
    $rooms = [];
    $error = "Database query failed.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SQL Injection Lab</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/sidebar.php"; ?>

<div class="dashboard-content">
    <h1>SQL Injection Training Lab</h1>

    <p>Educational demonstration — intentionally vulnerable code.</p>

    <form method="GET" class="room-form">
        <label>Room Number</label>
        <input type="text" name="room"
               value="<?= e($search) ?>"
               placeholder="Example: 101">

        <button type="submit" class="btn">Search</button>
    </form>

    <?php if ($error): ?>
        <p><?= e($error) ?></p>
    <?php endif; ?>

    <table class="data-table">
        <tr>
            <th>ID</th>
            <th>Room</th>
            <th>Type</th>
            <th>Price</th>
        </tr>

        <?php foreach ($rooms as $room): ?>
            <tr>
                <td><?= e($room["id"]) ?></td>
                <td><?= e($room["room_number"]) ?></td>
                <td><?= e($room["room_type"]) ?></td>
                <td><?= e($room["price"]) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
