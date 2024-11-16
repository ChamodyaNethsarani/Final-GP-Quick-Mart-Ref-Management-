<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header("Location: ../admin_php/admin_login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../emp_css/emp_nav.css?v=1">
    <link rel="stylesheet" href="../admin_css/admin_footer.css">
    <link rel="stylesheet" href="../emp_css/emp_home.css">
</head>
<body>

   
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
    </div>    
        <!-- Customer Support Section -->
        <section class="support-section">
            <h2>Need Help?</h2>
            <p>If you have any questions or need assistance, our support team is here to help. Visit our <a href="support.php">Support Page</a> for FAQs or contact customer service directly.</p>
        </section>
    </div>
    <br><br>
    

<!-- Include the navigation bar -->
    <?php include '../emp_php/emp_nav.php'; ?>

     <!-- Include the footer file here -->
<?php include '../admin_php/admin_footer.php'; ?>

</body>
</html>












