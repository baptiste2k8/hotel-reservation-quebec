<?php
session_start();
require_once "../config/database.php";

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

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    exit("Invalid reservation ID.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        http_response_code(403);
        exit("Invalid CSRF token.");
    }

    $status = $_POST["status"] ?? "";

    if (!in_array($status, ["pending", "confirmed", "cancelled"], true)) {
        exit("Invalid status.");
    }

    $stmt = $pdo->prepare(
        "UPDATE reservations SET status = ? WHERE id = ?"
    );
    $stmt->execute([$status, $id]);

    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, status FROM reservations WHERE id = ?"
);
$stmt->execute([$id]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    http_response_code(404);
    exit("Reservation not found.");
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Reservation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/sidebar.php"; ?>

<div class="dashboard-content">
    <h1>Update Reservation #<?= (int)$id ?></h1>

    <form method="POST" class="room-form">
        <input type="hidden" name="csrf_token"
               value="<?= e($_SESSION["csrf_token"]) ?>">

        <label>Status</label>
        <select name="status">
            <?php foreach (["pending", "confirmed", "cancelled"] as $status): ?>
                <option value="<?= e($status) ?>"
                    <?= $reservation["status"] === $status ? "selected" : "" ?>>
                    <?= e(ucfirst($status)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn">Save Status</button>
    </form>
</div>

</body>
</html>
