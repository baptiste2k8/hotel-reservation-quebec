
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/database.php";

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

$userId = $_SESSION["user_id"];
$message = "";

// INTENTIONALLY VULNERABLE:
// No CSRF token validation.

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullName = trim($_POST["full_name"] ?? "");

    if ($fullName !== "" && strlen($fullName) <= 150) {

        $stmt = $pdo->prepare(
            "UPDATE users SET full_name = ?
             WHERE id = ?"
        );

        $stmt->execute([$fullName, $userId]);

        $_SESSION["full_name"] = $fullName;

        $message = "Profile updated successfully.";
    } else {
        $message = "Invalid name.";
    }
}

$stmt = $pdo->prepare(
    "SELECT full_name, email, role
     FROM users WHERE id = ?"
);

$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    exit("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CSRF Security Lab</title>
    <link rel="stylesheet"
          href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/sidebar.php"; ?>

<div class="dashboard-content">

    <h1>CSRF Security Lab</h1>

    <p>
        This page intentionally omits CSRF
        protection for educational purposes.
    </p>

    <?php if ($message): ?>
        <p><?= e($message) ?></p>
    <?php endif; ?>

    <h2>Update Profile</h2>

    <form method="POST" class="room-form">

        <label>Full Name</label>
        <input type="text"
               name="full_name"
               maxlength="150"
               value="<?= e($user["full_name"]) ?>"
               required>

        <label>Email</label>
        <input type="email"
               value="<?= e($user["email"]) ?>"
               disabled>

        <label>Role</label>
        <input type="text"
               value="<?= e($user["role"]) ?>"
               disabled>

        <button type="submit" class="btn">
            Update Profile
        </button>

    </form>

</div>
</body>
</html>
