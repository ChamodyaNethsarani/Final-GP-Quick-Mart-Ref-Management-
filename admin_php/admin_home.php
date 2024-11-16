<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Correct MySQL credentials
include '../db_connection.php';


// Fetch Recent Orders
$orderQuery = "SELECT invoice_number, status, sale_date FROM sales ORDER BY sale_date DESC LIMIT 3";
$orderResult = $conn->query($orderQuery);

// Fetch Low Stock Alerts
$stockQuery = "SELECT product_name, qty FROM product WHERE qty <= 10";
$stockResult = $conn->query($stockQuery);

// Fetch Stats
$shopQuery = "SELECT COUNT(*) as shop_count FROM shop";
$shopCount = $conn->query($shopQuery)->fetch_assoc()['shop_count'];

$orderCountQuery = "SELECT COUNT(*) as total_orders FROM sales";
$totalOrders = $conn->query($orderCountQuery)->fetch_assoc()['total_orders'];

$inventoryQuery = "SELECT COUNT(*) as active_items FROM product WHERE qty > 0";
$activeItems = $conn->query($inventoryQuery)->fetch_assoc()['active_items'];

$revenueQuery = "SELECT SUM(total_amount) as total_revenue FROM sales WHERE MONTH(sale_date) = MONTH(CURRENT_DATE) AND YEAR(sale_date) = YEAR(CURRENT_DATE)";
$totalRevenue = $conn->query($revenueQuery)->fetch_assoc()['total_revenue'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Quick Mart</title>
    <link rel="stylesheet" href="../admin_css/admin_nav.css?v=1">
    <link rel="stylesheet" href="../admin_css/admin_footer.css">
    <link rel="stylesheet" href="../admin_css/admin_home.css"> <!-- New CSS file for Home page -->
</head>
<body>

    <!-- Include the navigation bar -->
    <?php include '../admin_php/admin_nav.php'; ?>

    <div class="main-content">
        <div class="welcome-section">
            <br/><br/>
            <h1>Welcome to Quick Mart, <?= htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p>Explore our offerings and manage your shops efficiently.</p>
        </div>

        <div class="content-grid">
            <!-- About Quick Mart Section -->
            <section class="about-section">
                <h2>About Quick Mart</h2>
                <p>Quick Mart is your one-stop destination for all retail and wholesale needs. Manage your stores, check stock levels, and more—all from a single platform. 
                Our mission is to simplify shop management and help businesses thrive through an easy-to-use, all-in-one platform.</p>
                <img src="../logo.png" alt="Quick Mart" class="about-image">
            </section>

            <!-- Key Features Section -->
            <section class="key-features">
                <h2>Key Features</h2>
                <ul>
                    <li><strong>Shop Management:</strong> Easily manage multiple shops from one dashboard.</li>
                    <li><strong>Inventory Tracking:</strong> Keep an eye on your stock levels in real-time.</li>
                    <li><strong>Order Management:</strong> Track your orders and sales history at a glance.</li>
                    <li><strong>Notifications:</strong> Get alerts for low stock or order updates.</li>
                </ul>
            </section>
        </div>
<!-- Recent Activity Section -->
<section class="recent-activity">
    <h2>Recent Activity</h2>
    <div class="activity-grid">
        <div class="activity-item">
            <h3>Latest Orders</h3>
            <?php while ($row = $orderResult->fetch_assoc()) { ?>
                <p>Order #<?= $row['invoice_number'] ?> - <?= $row['status'] ?> on <?= date("M d", strtotime($row['sale_date'])) ?></p>
            <?php } ?>
            <a href="admin_salesHistory.php">View All Orders</a>
        </div>
        <div class="activity-item">
            <h3>Low Stock Alerts</h3>
            <?php while ($row = $stockResult->fetch_assoc()) { ?>
                <p>Item: <?= $row['product_name'] ?> - Only <?= $row['qty'] ?> left</p>
            <?php } ?>
            <a href="admin_productAdd.php">View Products</a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <h2>Your Stats</h2>
    <div class="stats-grid">
        <div class="stat-item">
            <h3>Shops Managed</h3>
            <p><?= $shopCount ?> Shops</p>
        </div>
        <div class="stat-item">
            <h3>Total Orders</h3>
            <p><?= $totalOrders ?> Orders</p>
        </div>
        <div class="stat-item">
            <h3>Active Inventory Items</h3>
            <p><?= $activeItems ?> Items</p>
        </div>
        <div class="stat-item">
            <h3>Revenue</h3>
            <p>Rs. <?= number_format($totalRevenue, 2) ?> This Month</p>
        </div>
    </div>
</section>

        <!-- Customer Support Section -->
        <section class="support-section">
            <h2>Need Help?</h2>
            <p>If you have any questions or need assistance, our support team is here to help. Visit our <a href="support.php">Support Page</a> for FAQs or contact customer service directly.</p>
        </section>
    </div>

    <!-- Include the footer file here -->
    <?php include '../admin_php/admin_footer.php'; ?>
    
</body>
</html>
