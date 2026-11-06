<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

$name = $_SESSION["full_name"] ?? "User";
$role = $_SESSION["role"] ?? "customer";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Hotel Quebec</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include "includes/sidebar.php"; ?>

<div class="dashboard-content">

    <h1>Hotel Reservation Dashboard</h1>

    <p>
        Welcome,
        <?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>
    </p>

    <p>
        Your role:
        <?= htmlspecialchars($role, ENT_QUOTES, "UTF-8") ?>
    </p>

    <div class="stats-container">

        <div class="stat-card">
            <h2>Rooms</h2>
            <p>Manage hotel rooms</p>
        </div>

        <div class="stat-card">
            <h2>Reservations</h2>
            <p>Manage bookings</p>
        </div>

        <div class="stat-card">
            <h2>Customers</h2>
            <p>View registered customers</p>
        </div>

        <div class="stat-card">
            <h2>Reviews</h2>
            <p>View hotel feedback</p>
        </div>

    </div>

</div>

</body>
</html>
