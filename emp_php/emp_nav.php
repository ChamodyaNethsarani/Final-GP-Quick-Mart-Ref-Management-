<?php

// Start session to manage user login status
session_start();

// Sample login check
// Assuming you set $_SESSION['user'] when a user logs in
$is_logged_in = isset($_SESSION['user']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Navigation Bar</title>
    <link rel="stylesheet" href="../emp_css/emp_nav.css?v=1">


</head>
<style>
    body {
        background-color: whitesmoke;
    }
</style>

<body>
    <nav>
       <a href="home.php" class="logo">
            <img src="../logo.png" alt="Quick Mart Logo">
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
        <li style="display: inline;"><a href="emp_home.php" style="color: white; text-decoration: none; padding: 10px;">Home</a></li>
        <li style="display: inline;"><a href="emp_sales.php" style="color: white; text-decoration: none; padding: 10px;">Inventory</a></li>
        <li style="display: inline;"><a href="emp_shopReg.php" style="color: white; text-decoration: none; padding: 10px;">Shop</a></li>
        <li style="display: inline;"><a href="emp_commission.php" style="color: white; text-decoration: none; padding: 10px;">Commission</a></li>
        <li style="display: inline;">
<form action="logout.php" method="get">
    <button type="submit" style="color: white; background-color: red; border-radius: 5px; padding: 5px 20px; margin: 8px 0px 20px 10px; border: none; font-size: 14px; cursor: pointer; transition: background-color 0.3s;">
        LogOut
    </button>
</form>
        
        </ul>
    </nav>

    <script>
        // JavaScript to toggle the mobile menu and icons
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
