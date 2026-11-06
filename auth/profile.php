
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
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

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$userId = $_SESSION["user_id"];
$error = "";
$success = "";

$stmt = $pdo->prepare(
    "SELECT id, full_name, email, role
     FROM users
     WHERE id = ?"
);
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    exit("User not found.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["csrf_token"], $token)) {
        http_response_code(403);
        exit("Invalid CSRF token.");
    }

    $fullName = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($fullName === "" ||
        strlen($fullName) > 150 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150) {

        $error = "Please enter a valid name and email.";

    } else {

        $check = $pdo->prepare(
            "SELECT id FROM users
             WHERE email = ? AND id <> ?"
        );
        $check->execute([$email, $userId]);

        if ($check->fetch()) {
            $error = "This email is already registered.";
        } else {

            $update = $pdo->prepare(
                "UPDATE users
                 SET full_name = ?, email = ?
                 WHERE id = ?"
            );

            $update->execute([
                $fullName,
                $email,
                $userId
            ]);

            $_SESSION["full_name"] = $fullName;

            $success = "Profile updated successfully.";

            $user["full_name"] = $fullName;
            $user["email"] = $email;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Profile - Hotel Quebec</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">
</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="dashboard-content">

    <h1>My Profile</h1>

    <p>View and update your personal information.</p>

    <?php if ($error): ?>
        <p style="color:red;"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color:green;"><?= e($success) ?></p>
    <?php endif; ?>

    <form method="POST" class="room-form">

        <input type="hidden"
               name="csrf_token"
               value="<?= e($_SESSION["csrf_token"]) ?>">

        <label>Full Name</label>
        <input type="text"
               name="full_name"
               maxlength="150"
               value="<?= e($user["full_name"]) ?>"
               required>

        <label>Email Address</label>
        <input type="email"
               name="email"
               maxlength="150"
               value="<?= e($user["email"]) ?>"
               required>

        <label>Account Role</label>
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

