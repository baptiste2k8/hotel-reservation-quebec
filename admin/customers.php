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

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Search customers
$search = trim($_GET["search"] ?? "");

$sql = "SELECT u.id, u.full_name, u.email, u.created_at,
               COUNT(r.id) AS total_reservations
        FROM users u
        LEFT JOIN reservations r ON u.id = r.user_id
        WHERE u.role = 'customer'";

$params = [];

if ($search !== "") {
    $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
    $params = ["%$search%", "%$search%"];
}

$sql .= " GROUP BY u.id, u.full_name, u.email, u.created_at
          ORDER BY u.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customers - Hotel Quebec</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/sidebar.php"; ?>

<div class="dashboard-content">

    <h1>Customer Management</h1>

    <p>View and search registered customers.</p>

    <form method="GET" class="search-form">
        <input type="text" name="search"
               placeholder="Search by name or email"
               value="<?= e($search) ?>">

        <button type="submit" class="btn">Search</button>
        <a href="customers.php" class="btn">Reset</a>
    </form>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Reservations</th>
                <th>Registered On</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($customers as $customer): ?>
            <tr>
                <td><?= e($customer["id"]) ?></td>
                <td><?= e($customer["full_name"]) ?></td>
                <td><?= e($customer["email"]) ?></td>
                <td><?= e($customer["total_reservations"]) ?></td>
                <td><?= e($customer["created_at"]) ?></td>
                <td>
                    <a href="customer.php?id=<?= (int)$customer["id"] ?>">
                        View
                    </a>

                    <?php if ((int)$customer["total_reservations"] === 0): ?>
                        |
                        <form method="POST" action="delete_customer.php"
                              style="display:inline"
                              onsubmit="return confirm('Delete this customer?')">

                            <input type="hidden" name="id"
                                   value="<?= (int)$customer["id"] ?>">

                            <input type="hidden" name="csrf_token"
                                   value="<?= e($_SESSION["csrf_token"]) ?>">

                            <button type="submit">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>
</body>
</html>
