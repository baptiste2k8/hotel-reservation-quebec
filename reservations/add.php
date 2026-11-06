<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$error = "";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$rooms = $pdo->query(
    "SELECT id, room_number, room_type, price
     FROM rooms WHERE status = 'available'
     ORDER BY room_number"
)->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        http_response_code(403);
        exit("Invalid CSRF token.");
    }

    $roomId = filter_input(INPUT_POST, "room_id", FILTER_VALIDATE_INT);
    $checkIn = $_POST["check_in"] ?? "";
    $checkOut = $_POST["check_out"] ?? "";

    $start = DateTimeImmutable::createFromFormat("!Y-m-d", $checkIn);
    $end = DateTimeImmutable::createFromFormat("!Y-m-d", $checkOut);

    if (!$roomId || !$start || !$end ||
        $start->format("Y-m-d") !== $checkIn ||
        $end->format("Y-m-d") !== $checkOut ||
        $end <= $start ||
        $start < new DateTimeImmutable("today")) {

        $error = "Please enter valid reservation dates.";

    } else {
        $stmt = $pdo->prepare(
            "SELECT price FROM rooms
             WHERE id = ? AND status = 'available'"
        );
        $stmt->execute([$roomId]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$room) {
            $error = "Room is not available.";
        } else {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM reservations
                 WHERE room_id = ?
                   AND status IN ('pending', 'confirmed')
                   AND check_in < ?
                   AND check_out > ?"
            );
            $stmt->execute([$roomId, $checkOut, $checkIn]);

            if ((int)$stmt->fetchColumn() > 0) {
                $error = "Room is already reserved for these dates.";
            } else {
                $nights = (int)$start->diff($end)->days;
                $total = $nights * (float)$room["price"];

                $stmt = $pdo->prepare(
                    "INSERT INTO reservations
                     (user_id, room_id, check_in, check_out, total_price)
                     VALUES (?, ?, ?, ?, ?)"
                );

                $stmt->execute([
                    $_SESSION["user_id"],
                    $roomId,
                    $checkIn,
                    $checkOut,
                    $total
                ]);

                header("Location: index.php");
                exit;
            }
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
    <title>New Reservation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/sidebar.php"; ?>

<div class="dashboard-content">
    <h1>New Reservation</h1>

    <?php if ($error): ?>
        <p><?= e($error) ?></p>
    <?php endif; ?>

    <form method="POST" class="room-form">
        <input type="hidden" name="csrf_token"
               value="<?= e($_SESSION["csrf_token"]) ?>">

        <label>Room</label>
        <select name="room_id" required>
            <?php foreach ($rooms as $room): ?>
                <option value="<?= (int)$room["id"] ?>">
                    Room <?= e($room["room_number"]) ?>
                    - <?= e($room["room_type"]) ?>
                    - $<?= e($room["price"]) ?> CAD/night
                </option>
            <?php endforeach; ?>
        </select>

        <label>Check-in</label>
        <input type="date" name="check_in" required>

        <label>Check-out</label>
        <input type="date" name="check_out" required>

        <button type="submit" class="btn">Book Room</button>
    </form>
</div>

</body>
</html>
