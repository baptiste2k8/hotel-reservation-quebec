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

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $number = trim($_POST["room_number"] ?? "");
    $type = trim($_POST["room_type"] ?? "");
    $price = $_POST["price"] ?? "";
    $capacity = $_POST["capacity"] ?? "";
    $status = $_POST["status"] ?? "";
    $description = trim($_POST["description"] ?? "");

    $validStatuses = ["available", "occupied", "maintenance"];

    if ($number === "" || $type === "" ||
        !is_numeric($price) || (float)$price <= 0 ||
        filter_var($capacity, FILTER_VALIDATE_INT) === false ||
        (int)$capacity < 1 ||
        !in_array($status, $validStatuses, true)) {

        $error = "Please enter valid room information.";

    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO rooms
                 (room_number, room_type, price, capacity, status, description)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $number, $type, $price,
                $capacity, $status, $description
            ]);

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            $error = "Could not add room. Check the room number.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Room</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/sidebar.php"; ?>

<div class="dashboard-content">
    <h1>Add New Room</h1>

    <?php if ($error): ?>
        <p><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></p>
    <?php endif; ?>

    <form method="POST" class="room-form">
        <label>Room Number</label>
        <input name="room_number" required>

        <label>Room Type</label>
        <select name="room_type" required>
            <option value="Single">Single</option>
            <option value="Double">Double</option>
            <option value="Suite">Suite</option>
            <option value="Family">Family</option>
        </select>

        <label>Price (CAD)</label>
        <input type="number" name="price"
               min="0.01" step="0.01" required>

        <label>Capacity</label>
        <input type="number" name="capacity"
               min="1" required>

        <label>Status</label>
        <select name="status">
            <option value="available">Available</option>
            <option value="occupied">Occupied</option>
            <option value="maintenance">Maintenance</option>
        </select>

        <label>Description</label>
        <textarea name="description"></textarea>

        <button type="submit" class="btn">Save Room</button>
    </form>
</div>
</body>
</html>
