<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../css/nav.css?v=1">
    <link rel="stylesheet" href="../css/home_emp.css">
</head>
<body>

    <nav>
        <a href="home.php" class="logo">
            <img src="Quick_Mart_Logo.jpg" alt="Quick Mart Logo">
        </a>
        <div class="menu-toggle" id="menu-toggle">
            <!-- Default hamburger icon -->
            <svg class="hamburger" viewBox="0 0 100 80" width="40" height="40">
                <rect width="100" height="20"></rect>
                <rect y="30" width="100" height="20"></rect>
                <rect y="60" width="100" height="20"></rect>
            </svg>
            <!-- Slice icon (hidden by default) -->
            <svg class="slice-icon" viewBox="0 0 100 100" width="40" height="40" style="display: none;">
                <circle cx="50" cy="50" r="45" stroke="white" stroke-width="10" fill="none" />
                <line x1="50" y1="50" x2="90" y2="10" stroke="white" stroke-width="10" />
            </svg>
        </div>
        <ul class="navbar">
        <li style="display: inline;"><a href="home.php" style="color: white; text-decoration: none; padding: 10px;">Home</a></li>
        <li style="display: inline;"><a href="sales.php" style="color: white; text-decoration: none; padding: 10px;">Inventory</a></li>
        <li style="display: inline;"><a href="shopReg.php" style="color: white; text-decoration: none; padding: 10px;">Shop Register</a></li>
        <li style="display: inline;"><a href="productAdd.php" style="color: white; text-decoration: none; padding: 10px;">Add Product</a></li>
        <li style="display: inline;"><a href="commission.php" style="color: white; text-decoration: none; padding: 10px;">Commission</a></li>
        <li style="display: inline;"><a href="reports.php" style="color: white; text-decoration: none; padding: 10px;">Reports</a></li>
        <li style="display: inline;"><a href="empReg.php" style="color: white; text-decoration: none; padding: 10px;">Employee Register</a></li>
        <li style="display: inline;">
<form action="logout.php" method="get">
    <button type="submit" style="color: white; background-color: red; border-radius: 5px; padding: 5px 20px; margin: 8px 0px 20px 10px; border: none; font-size: 14px; cursor: pointer; transition: background-color 0.3s;">
        LogOut
    </button>
</form>

        </li>
        </ul>
    </nav>
    
    <div class="main-content">
        <h1>Welcome to the Quick Mart,
             <?= htmlspecialchars($_SESSION['user_email']); ?>
            </h1>
        <img src="..\image\home.jpg" width="400" height="450" alt="home">
    </div>
    
    <script>
        const menuToggle = document.querySelector('#menu-toggle');
        const navbar = document.querySelector('.navbar');
        const hamburgerIcon = document.querySelector('.hamburger');
        const sliceIcon = document.querySelector('.slice-icon');

        menuToggle.addEventListener('click', () => {
            navbar.classList.toggle('active');

            // Toggle between hamburger and slice icons
            if (navbar.classList.contains('active')) {
                hamburgerIcon.style.display = 'none';
                sliceIcon.style.display = 'block';
            } else {
                hamburgerIcon.style.display = 'block';
                sliceIcon.style.display = 'none';
            }
        });
    </script>
</body>
</html>
