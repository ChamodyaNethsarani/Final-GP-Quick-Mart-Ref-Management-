<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

include '../db_connection.php';


// Handle form submission for adding a new shop
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_shop'])) {
    // Retrieve form data
    $shop_name = $_POST['shop_name'];
    $owner_name = $_POST['owner_name'];
    $location = $_POST['location'];
    $address = $_POST['address'];
    $contact_number = $_POST['contact_number'];
    $register_date = $_POST['register_date'];
    $register_time = $_POST['register_time'];
    $shop_type = $_POST['shop_type'];

    // Corrected SQL query for inserting a new shop
    $sql = "INSERT INTO shop (shop_name, owner_name, location, address, contact_number, register_date, register_time, shop_type)
            VALUES ('$shop_name', '$owner_name', '$location', '$address', '$contact_number', '$register_date', '$register_time', '$shop_type')";

    if ($conn->query($sql) === TRUE) {
        // After successful insertion, redirect to the same page to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit(); // Make sure no further code is executed after the redirect
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle form submission for updating an existing shop
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_shop'])) {
    $shop_id = $_POST['shop_id'];
    $shop_name = $_POST['shop_name'];
    $owner_name = $_POST['owner_name'];
    $location = $_POST['location'];
    $address = $_POST['address'];
    $contact_number = $_POST['contact_number'];
    $register_date = $_POST['register_date'];
    $register_time = $_POST['register_time'];
    $shop_type = $_POST['shop_type'];

    // SQL query to update shop details
    $sql = "UPDATE shop SET shop_name='$shop_name', owner_name='$owner_name', location='$location', address='$address', contact_number='$contact_number', register_date='$register_date', register_time='$register_time', shop_type='$shop_type' WHERE shop_id=$shop_id";

    if ($conn->query($sql) === TRUE) {
        // After successful update, redirect to the same page to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit(); // Make sure no further code is executed after the redirect
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM shop WHERE shop_id = $delete_id";

    if ($conn->query($sql) === TRUE) {
        echo "Shop deleted successfully!";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Fetch shops from the database
$shops = [];
$sql = "SELECT * FROM shop";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $shops[] = $row;
    }
}

// Fetch shop details for editing
$shop_to_edit = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $sql = "SELECT * FROM shop WHERE shop_id = $edit_id";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $shop_to_edit = $result->fetch_assoc();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Management Page</title>
    <link rel="stylesheet" href="../admin_css/admin_nav.css?v=1">
    <link rel="stylesheet" href="../admin_css/admin_shopReg.css">
    <link rel="stylesheet" href="../admin_css/admin_footer.css">

    <style>
        .low-stock-notification {
            background-color: #ffcccb;
            color: #d8000c;
            padding: 15px;
            border: 1px solid #d8000c;
            margin: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    
    <div class="shopreg">
<br>
    <h1>Shop Management Page</h1>
<br>
    <!-- New Shop Button -->
    <button class="btn" onclick="showPopup()">New Shop</button>

    <!-- Popup Form for Adding New Shop -->
    <div class="popup" id="popupForm">
        <h2>Add New Shop</h2>
        <form id="shopForm" method="POST">
            <label for="shopName">Shop Name:</label>
            <input type="text" id="shopName" name="shop_name" required><br><br>

            <label for="ownerName">Owner Name:</label>
            <input type="text" id="ownerName" name="owner_name" required><br><br>

            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required><br><br>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required><br><br>

            <label for="contactNumber">Contact Number:</label>
            <input type="text" id="contactNumber" name="contact_number" required><br><br>

            <label for="registerDate">Register Date:</label>
            <input type="date" id="registerDate" name="register_date" required><br><br>

            <label for="registerTime">Register Time:</label>
            <input type="time" id="registerTime" name="register_time" required><br><br>

             <label for="editShopType">Shop Type:</label>
        <select id="shopType" name="shop_type" required>
            <option value="" disabled>Select Shop</option>
            <option value="Retail" <?= $shop_to_edit['shop_type'] == 'Retail' ? 'selected' : ''; ?>>Retail</option>
            <option value="Whole Sale" <?= $shop_to_edit['shop_type'] == 'Whole Sale' ? 'selected' : ''; ?>>Whole Sale</option>
            <option value="Veg Shop" <?= $shop_to_edit['shop_type'] == 'Veg Shop' ? 'selected' : ''; ?>>Veg Shop</option>
            <option value="Pharmacy" <?= $shop_to_edit['shop_type'] == 'Pharmacy' ? 'selected' : ''; ?>>Pharmacy</option>
        </select><br><br>


            <button type="submit" class="btn" name="add_shop">Add Shop</button>
            <button type="button" class="btn" onclick="hidePopup()">Cancel</button>
        </form>
    </div>

    <!-- Popup Form for Editing Shop -->
<?php if ($shop_to_edit): ?>
<div class="edit-popup" id="editPopup">
    <h2>Edit Shop</h2>
    <form method="POST">
        <input type="hidden" name="shop_id" value="<?= htmlspecialchars($shop_to_edit['shop_id']); ?>">

        <label for="editShopName">Shop Name:</label>
        <input type="text" id="editShopName" name="shop_name" value="<?= htmlspecialchars($shop_to_edit['shop_name']); ?>" required><br><br>

        <label for="editOwnerName">Owner Name:</label>
        <input type="text" id="editOwnerName" name="owner_name" value="<?= htmlspecialchars($shop_to_edit['owner_name']); ?>" required><br><br>

        <label for="editLocation">Location:</label>
        <input type="text" id="editLocation" name="location" value="<?= htmlspecialchars($shop_to_edit['location']); ?>" required><br><br>

        <label for="editAddress">Address:</label>
        <input type="text" id="editAddress" name="address" value="<?= htmlspecialchars($shop_to_edit['address']); ?>" required><br><br>

        <label for="editContactNumber">Contact Number:</label>
        <input type="text" id="editContactNumber" name="contact_number" value="<?= htmlspecialchars($shop_to_edit['contact_number']); ?>" required><br><br>

        <label for="editRegisterDate">Register Date:</label>
        <input type="date" id="editRegisterDate" name="register_date" value="<?= htmlspecialchars($shop_to_edit['register_date']); ?>" required><br><br>

        <label for="editRegisterTime">Register Time:</label>
        <input type="time" id="editRegisterTime" name="register_time" value="<?= htmlspecialchars($shop_to_edit['register_time']); ?>" required><br><br>

        <label for="editShopType">Shop Type:</label>
        <select id="shopType" name="shop_type" required>
            <option value="" disabled>Select Shop</option>
            <option value="Retail" <?= $shop_to_edit['shop_type'] == 'Retail' ? 'selected' : ''; ?>>Retail</option>
            <option value="Whole Sale" <?= $shop_to_edit['shop_type'] == 'Whole Sale' ? 'selected' : ''; ?>>Whole Sale</option>
            <option value="Veg Shop" <?= $shop_to_edit['shop_type'] == 'Veg Shop' ? 'selected' : ''; ?>>Veg Shop</option>
            <option value="Pharmacy" <?= $shop_to_edit['shop_type'] == 'Pharmacy' ? 'selected' : ''; ?>>Pharmacy</option>
        </select><br><br>

        <!-- Update Shop Button -->
        <button type="submit" class="btn" name="update_shop">Update Shop</button>
        <button type="button" class="btn" onclick="hideEditPopup()">Cancel</button>
    </form>
</div>
<?php endif; ?>


    <!-- Shop Table -->
     <div class="table-responsive">
    <table class="shop-table" id="shopTable">
        <thead>
            <tr>
                <th>Shop Name</th>
                <th>Owner Name</th>
                <th>Location</th>
                <th>Address</th>
                <th>Contact Number</th>
                <th>Register Date</th>
                <th>Register Time</th>
                <th>Shop Type</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($shops as $shop): ?>
                <tr>
                    <td><?= htmlspecialchars($shop['shop_name']); ?></td>
                    <td><?= htmlspecialchars($shop['owner_name']); ?></td>
                    <td><?= htmlspecialchars($shop['location']); ?></td>
                    <td><?= htmlspecialchars($shop['address']); ?></td>
                    <td><?= htmlspecialchars($shop['contact_number']); ?></td>
                    <td><?= htmlspecialchars($shop['register_date']); ?></td>
                    <td><?= htmlspecialchars($shop['register_time']); ?></td>
                    <td><?= htmlspecialchars($shop['shop_type']); ?></td>
                    <td>
                        <a href="?edit_id=<?= $shop['shop_id']; ?>">
                            <button class="edit-btn">Edit</button>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div></div>

    <script>
    // Show the popup form for adding a new shop
    function showPopup() {
        document.getElementById('popupForm').classList.add('active');
    }

    // Hide the popup form for adding a new shop
    function hidePopup() {
        document.getElementById('popupForm').classList.remove('active');
    }

    // Show the popup form for editing a shop
    function showEditPopup() {
        document.getElementById('editPopup').classList.add('active');
    }

    // Hide the popup form for editing a shop
    function hideEditPopup() {
        document.getElementById('editPopup').classList.remove('active');
    }

    // Automatically show the edit popup if edit_id is set
    <?php if ($shop_to_edit): ?>
    window.onload = showEditPopup;
    <?php endif; ?>
    </script>

    <!-- Include the navigation bar -->
    <?php include '../emp_php/emp_nav.php'; ?>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
    <!-- Include the footer file -->
    <?php include '../admin_php/admin_footer.php'; ?>

</body>
</html>
