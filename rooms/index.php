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

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

require_once "../config/database.php";

// Read all rooms
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY id DESC");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Room Management - Hotel Quebec</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/sidebar.php"; ?>

<div class="dashboard-content">

    <h1>Room Management</h1>

    <p>Manage all hotel rooms.</p>

    <a href="add.php" class="btn">Add New Room</a>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Room Number</th>
                <th>Type</th>
                <th>Price (CAD)</th>
                <th>Capacity</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rooms as $room): ?>
            <tr>
                <td><?= e($room["id"]) ?></td>
                <td><?= e($room["room_number"]) ?></td>
                <td><?= e($room["room_type"]) ?></td>
                <td><?= e($room["price"]) ?></td>
                <td><?= e($room["capacity"]) ?></td>
                <td><?= e($room["status"]) ?></td>
                <td>
                    <a href="edit.php?id=<?= (int)$room["id"] ?>">
                        Edit
                    </a>
                    |
                    <form method="POST" action="delete.php"
      style="display:inline"
      onsubmit="return confirm('Delete this room?')">

    <input type="hidden" name="id"
           value="<?= (int)$room["id"] ?>">

    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars(
               $_SESSION["csrf_token"], ENT_QUOTES, "UTF-8"
           ) ?>">

    <button type="submit">Delete</button>
</form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>
</body>
</html>
