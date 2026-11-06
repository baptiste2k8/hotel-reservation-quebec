<?php
session_start();
require_once '../config/database.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare(
        "SELECT id, full_name, email, password, role
         FROM users WHERE email = ?"
    );

    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {

        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["full_name"] = $user["full_name"];
        $_SESSION["role"] = $user["role"];

        header("Location: ../dashboard.php");
        exit;

    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Hotel Quebec</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<main>
    <h2>Login to Hotel Quebec</h2>

    <?php if ($error): ?>
        <p style="color:red;">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <p>
       
     <input type="email" name="email"
                   placeholder="Email address" required>
        </p>

        <p>
            <input type="password" name="password"
                   placeholder="Password" required>
        </p>

        <button type="submit" class="btn">Login</button>
    </form>
</main>

</body>
</html>
