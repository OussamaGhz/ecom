<?php
include 'config/database.php';
include 'includes/header.php';

$query = "SELECT * FROM items";
$stmt = $conn->prepare($query);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1>Welcome to Our E-Commerce Store</h1>
    <div class="items-grid">
        <?php foreach($items as $item): ?>
            <div class="item-card">
                <img src="uploads/<?= $item['image']; ?>" alt="<?= $item['name']; ?>">
                <h2><?= $item['name']; ?></h2>
                <p>Price: <?= $item['price']; ?> DA</p>
                <a href="item.php?id=<?= $item['id']; ?>" class="btn">View Details</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
