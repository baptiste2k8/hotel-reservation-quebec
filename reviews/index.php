
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

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        http_response_code(403);
        exit("Invalid CSRF token.");
    }

    $rating = filter_input(
        INPUT_POST, "rating", FILTER_VALIDATE_INT
    );

    $comment = trim($_POST["comment"] ?? "");

    if (!$rating || $rating < 1 || $rating > 5 ||
        $comment === "" || strlen($comment) > 2000) {
        $error = "Enter a rating (1-5) and a valid comment.";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO reviews (user_id, rating, comment)
             VALUES (?, ?, ?)"
        );

        $stmt->execute([
            $_SESSION["user_id"], $rating, $comment
        ]);

        header("Location: index.php");
        exit;
    }
}

$stmt = $pdo->query(
    "SELECT r.id, r.rating, r.comment, r.created_at,
            u.full_name
     FROM reviews r
     JOIN users u ON r.user_id = u.id
     ORDER BY r.id DESC"
);

$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hotel Reviews</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/sidebar.php"; ?>

<div class="dashboard-content">

    <h1>Hotel Reviews</h1>
    <p>Share your experience at Hotel Quebec.</p>

    <?php if ($error): ?>
        <p><?= e($error) ?></p>
    <?php endif; ?>

    <form method="POST" class="room-form">
        <input type="hidden" name="csrf_token"
               value="<?= e($_SESSION["csrf_token"]) ?>">

        <label>Rating</label>
        <select name="rating" required>
            <option value="5">5 - Excellent</option>
            <option value="4">4 - Very Good</option>
            <option value="3">3 - Good</option>
            <option value="2">2 - Fair</option>
            <option value="1">1 - Poor</option>
        </select>

        <label>Your Review</label>
        <textarea name="comment" required></textarea>

        <button type="submit" class="btn">
            Submit Review
        </button>
    </form>

    <h2>Customer Reviews</h2>

    <?php foreach ($reviews as $review): ?>
        <div class="review-card">
            <h3><?= e($review["full_name"]) ?></h3>
            <p>Rating: <?= (int)$review["rating"] ?>/5</p>
            <p><?= nl2br(e($review["comment"])) ?></p>
            <small><?= e($review["created_at"]) ?></small>
        </div>
    <?php endforeach; ?>

</div>
</body>
</html>
