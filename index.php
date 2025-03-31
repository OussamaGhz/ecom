<?php
session_start();
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch items from the database
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT * FROM items";
if ($search) {
    $query .= " WHERE name LIKE :search OR description LIKE :search";
}

$stmt = $conn->prepare($query);

if ($search) {
    $searchParam = "%$search%";
    $stmt->bindParam(':search', $searchParam);
}

$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$username = $_SESSION['username'];
$is_admin = $_SESSION['is_admin'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>E-commerce Shop</title>
    <link rel="stylesheet" href="assets/css/styles.css"> <!-- Correct path to styles.css -->
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    
    <?php if ($is_admin): ?>
        <a href="admin.php">Go to Admin Panel</a><br>
    <?php endif; ?>
    
    <a href="logout.php">Logout</a>

    <form method="GET" action="index.php">
        <input type="text" name="search" placeholder="Search items..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>

    <div class="items">
        <?php foreach ($items as $item): ?>
            <div class="item">

                <?php if ($item['image']): ?>
                    <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                <?php endif; ?>
                    <h2><?php echo htmlspecialchars($item['name']); ?></h2>
                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                    <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                    <a href="item.php?id=<?php echo $item['id']; ?>">View Details</a>
                </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
