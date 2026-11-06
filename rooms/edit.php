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
    exit("Invalid room ID.");
}

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    exit("Room not found.");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $number = trim($_POST["room_number"] ?? "");
    $type = trim($_POST["room_type"] ?? "");
    $price = $_POST["price"] ?? "";
    $capacity = $_POST["capacity"] ?? "";
    $status = $_POST["status"] ?? "";
    $description = trim($_POST["description"] ?? "");

    if ($number === "" ||
        !in_array($type, ["Single", "Double", "Suite", "Family"], true) ||
        !is_numeric($price) || (float)$price <= 0 ||
        filter_var($capacity, FILTER_VALIDATE_INT) === false ||
        (int)$capacity < 1 ||
        !in_array($status, ["available", "occupied", "maintenance"], true)) {

        $error = "Please enter valid room information.";

    } else {
        try {
            $stmt = $pdo->prepare(
                "UPDATE rooms
                 SET room_number = ?, room_type = ?,
                     price = ?, capacity = ?, status = ?,
                     description = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $number, $type, $price,
                $capacity, $status, $description, $id
            ]);

            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $error = "Could not update room.";
        }
    }
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Room</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/sidebar.php"; ?>

<div class="dashboard-content">
    <h1>Edit Room</h1>

    <?php if ($error): ?>
        <p><?= e($error) ?></p>
    <?php endif; ?>

    <form method="POST" class="room-form">
        <label>Room Number</label>
        <input name="room_number"
               value="<?= e($room["room_number"]) ?>" required>

        <label>Room Type</label>
        <select name="room_type">
            <?php foreach (["Single", "Double", "Suite", "Family"] as $type): ?>
                <option value="<?= e($type) ?>"
                    <?= $room["room_type"] === $type ? "selected" : "" ?>>
                    <?= e($type) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Price (CAD)</label>
        <input type="number" name="price" step="0.01"
               min="0.01" value="<?= e($room["price"]) ?>" required>

        <label>Capacity</label>
        <input type="number" name="capacity" min="1"
               value="<?= e($room["capacity"]) ?>" required>

        <label>Status</label>
        <select name="status">
            <?php foreach (["available", "occupied", "maintenance"] as $status): ?>
                <option value="<?= e($status) ?>"
                    <?= $room["status"] === $status ? "selected" : "" ?>>
                    <?= e(ucfirst($status)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Description</label>
        <textarea name="description"><?= e($room["description"]) ?></textarea>

        <button type="submit" class="btn">Update Room</button>
    </form>
</div>
</body>
</html>
