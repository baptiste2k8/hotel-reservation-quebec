<?php
$appName = "Hotel Reservation System in Quebec";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title><?php echo $appName; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Hotel Reservation System in Quebec</h1>
        <p>Welcome to our hotel booking platform!</p>
    </header>

    <main>
        <h2>Discover Our Rooms</h2>
        <p>Find and reserve your perfect room in Quebec.</p>
        <a href="rooms/" class="btn">View Rooms</a>
        <a href="auth/login.php" class="btn">Login</a>
    </main>

    <footer>
        <p>&copy; 2026 Hotel Quebec - Educational Project</p>
    </footer>
</body>
</html>
