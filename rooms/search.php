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

$type = trim($_GET["type"] ?? "");
$maxPrice = trim($_GET["max_price"] ?? "");
$status = trim($_GET["status"] ?? "");

$sql = "SELECT * FROM rooms WHERE 1=1";
$params = [];

if ($type !== "") {
    $sql .= " AND room_type = ?";
    $params[] = $type;
}

if ($maxPrice !== "") {
    if (!is_numeric($maxPrice) || (float)$maxPrice < 0) {
        http_response_code(400);
        exit("Invalid maximum price.");
    }
    $sql .= " AND price <= ?";
    $params[] = $maxPrice;
}

if ($status !== "") {
    if (!in_array($status, ["available", "occupied", "maintenance"], true)) {
        http_response_code(400);
        exit("Invalid room status.");
    }
    $sql .= " AND status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY price ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Rooms - Hotel Quebec</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/sidebar.php"; ?>

<div class="dashboard-content">

    <h1>Search Hotel Rooms</h1>

    <form method="GET" class="room-form">

        <label>Room Type</label>
        <select name="type">
            <option value="">All Types</option>
            <?php foreach (["Single", "Double", "Suite", "Family"] as $option): ?>
                <option value="<?= e($option) ?>"
                    <?= $type === $option ? "selected" : "" ?>>
                    <?= e($option) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Maximum Price (CAD)</label>
        <input type="number" name="max_price"
               min="0" step="0.01"
               value="<?= e($maxPrice) ?>">

        <label>Availability Status</label>
        <select name="status">
            <option value="">All Statuses</option>
            <?php foreach (["available", "occupied", "maintenance"] as $option): ?>
                <option value="<?= e($option) ?>"
                    <?= $status === $option ? "selected" : "" ?>>
                    <?= e(ucfirst($option)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn">Search</button>
        <a href="search.php" class="btn">Reset</a>
    </form>

    <h2>Search Results</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>Room</th>
                <th>Type</th>
                <th>Price (CAD)</th>
                <th>Capacity</th>
                <th>Status</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rooms as $room): ?>
            <tr>
                <td><?= e($room["room_number"]) ?></td>
                <td><?= e($room["room_type"]) ?></td>
                <td><?= e($room["price"]) ?></td>
                <td><?= e($room["capacity"]) ?></td>
                <td><?= e($room["status"]) ?></td>
                <td><?= e($room["description"]) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (count($rooms) === 0): ?>
        <p>No rooms found.</p>
    <?php endif; ?>

</div>
</body>
</html>
