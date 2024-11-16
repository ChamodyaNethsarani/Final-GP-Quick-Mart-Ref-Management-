<?php

include '../db_connection.php';


// Handle the form submission to add a new commission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_commission'])) {
    $category = $_POST['category'];
    $product_name = $_POST['product_name'];
    $number_of_sales = $_POST['number_of_sales'];
    $commission_rate = $_POST['commission_rate'];  // Store as entered, no conversion

    $sql = "INSERT INTO commission (category, product_name, number_of_sales, commission_rate) 
            VALUES ('$category', '$product_name', '$number_of_sales', '$commission_rate')";

    if ($conn->query($sql) === TRUE) {
        // Redirect to the same page to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle the update form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_commission'])) {
    $commission_id = $_POST['commission_id']; 
    $category = $_POST['category'];
    $product_name = $_POST['product_name'];
    $number_of_sales = $_POST['number_of_sales'];
    $commission_rate = $_POST['commission_rate'];  // Store as entered, no conversion

    $sql = "UPDATE commission 
            SET category='$category', product_name='$product_name', number_of_sales='$number_of_sales', commission_rate='$commission_rate' 
            WHERE commission_id='$commission_id'";

    if ($conn->query($sql) === TRUE) {
        // Redirect to the same page to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Handle delete request using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_commission'])) {
    $delete_id = $_POST['commission_id'];
    $sql = "DELETE FROM commission WHERE commission_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        // After successful deletion, redirect to the same page
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Fetch all commissions to display in the table
$commissions = [];
$sql = "SELECT * FROM commission";
$result = $conn->query($sql);

if (!$result) {
    die("Error executing query: " . $conn->error);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $commissions[] = $row;
    }
} else {
    echo "No commissions found.";
}

// Query to check if any product has 20 or fewer items in stock
$low_stock_sql = "SELECT category, product_name, qty FROM product WHERE qty <= 20";
$low_stock_result = $conn->query($low_stock_sql);

// Check if there are products with low stock
if ($low_stock_result->num_rows > 0) {
    // Notify the admin of low stock products
    $low_stock_message = "Warning: The following products are low in stock (20 or less):<br>";
    
    while ($row = $low_stock_result->fetch_assoc()) {
        $low_stock_message .= "Category: " . htmlspecialchars($row['category']) . " - Product: " . htmlspecialchars($row['product_name']) . " (Stock: " . htmlspecialchars($row['qty']) . ")<br>";
    }   
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Commissions</title>
    <link rel="stylesheet" href="../admin_css/admin_commission.css">
    <link rel="stylesheet" href="../admin_css/admin_nav.css">
    <link rel="stylesheet" href="../admin_css/admin_footer.css">
</head>
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
<body>

<!-- Include the navigation bar -->
<?php include '../admin_php/admin_nav.php'; ?>

<div class="addprd">
    <!-- Low stock notification right below the navigation bar -->
<?php if (!empty($low_stock_message)): ?>
    <div class="low-stock-notification">
        <?= $low_stock_message ?>
    </div>
<?php endif; ?>

<h2>Admin Commission Management</h2>

<!-- New Commission Button -->
<button class="btn" onclick="showPopup()">New Commission</button>

<!-- Popup Form for Adding New Commission -->
<div class="popup" id="popupForm">
    <h2>Add New Commission</h2>
    <form method="POST">
        <label for="product_name">Product Name</label>
        <input type="text" name="product_name" required><br>

        <label for="category">Product Category</label>
        <select name="category" required>
            <option value="Biscuit">Biscuit</option>
            <option value="Chocalate">Chocalate</option>
            <option value="Snacks">Snacks</option>
            <option value="Cooking essential">Cooking essential</option>
            <option value="Spices">Spices</option>
        </select><br>

        <label for="number_of_sales">Number of Sales</label>
        <input type="number" name="number_of_sales" required><br>

        <label for="commission_rate">Commission Rate (%)</label>
        <!-- Input as plain number, and we will append % sign when displaying -->
        <input type="number" name="commission_rate" step="0.01" required><br>

        <button type="submit" name="add_commission">Submit</button>
        <button type="button" onclick="hidePopup()">Cancel</button>
    </form>
</div>

<!-- Edit Popup Form for Editing Commission -->
<?php if (isset($_GET['edit_id'])): ?>
<?php
    // Fetch the commission to edit
    $commission_id = $_GET['edit_id'];
    $conn = new mysqli($servername, $username, $password, $dbname); // Reconnect to DB to fetch commission
    $sql = "SELECT * FROM commission WHERE commission_id = $commission_id";
    $result = $conn->query($sql);
    $commission_to_edit = $result->fetch_assoc();
?>
<div class="edit-popup" id="editPopup">
    <h2>Edit Commission</h2>
    <form method="POST">
        <input type="hidden" name="commission_id" value="<?= htmlspecialchars($commission_to_edit['commission_id']); ?>">

        <label for="product_name">Product Name</label>
        <input type="text" name="product_name" value="<?= htmlspecialchars($commission_to_edit['product_name']); ?>" required><br>

        <label for="category">Product Category</label>
        <select name="category" required>
            <option value="Biscuit" <?= ($commission_to_edit['category'] == 'Biscuit') ? 'selected' : '' ?>>Biscuit</option>
            <option value="Chocalate" <?= ($commission_to_edit['category'] == 'Chocalate') ? 'selected' : '' ?>>Chocalate</option>
            <option value="Snacks" <?= ($commission_to_edit['category'] == 'Snacks') ? 'selected' : '' ?>>Snacks</option>
            <option value="Cooking essential" <?= ($commission_to_edit['category'] == 'Cooking essential') ? 'selected' : '' ?>>Cooking essential</option>
            <option value="Spices" <?= ($commission_to_edit['category'] == 'Spices') ? 'selected' : '' ?>>Spices</option>
        </select><br>

        <label for="number_of_sales">Number of Sales</label>
        <input type="number" name="number_of_sales" value="<?= htmlspecialchars($commission_to_edit['number_of_sales']); ?>" required><br>

        <label for="commission_rate">Commission Rate (%)</label>
        <input type="number" name="commission_rate" step="0.01" value="<?= htmlspecialchars($commission_to_edit['commission_rate']); ?>" required><br>

        <button type="submit" name="update_commission">Update Commission</button>
        <button type="button" onclick="hideEditPopup()">Cancel</button>
    </form>
</div>
<?php endif; ?>

<!-- Table displaying commissions -->
<table>
    <thead>
        <tr>
            <th>Category</th>
            <th>Product Name</th>
            <th>Number of Sales</th>
            <th>Commission Rate (%)</th> <!-- Display as % -->
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($commissions as $commission) : ?>
        <tr>
            <td><?= htmlspecialchars($commission['category']) ?></td>
            <td><?= htmlspecialchars($commission['product_name']) ?></td>
            <td><?= htmlspecialchars($commission['number_of_sales']) ?></td>
            <td><?= htmlspecialchars($commission['commission_rate']) ?>%</td> <!-- Display as % -->
            <td>
                <a href="?edit_id=<?= $commission['commission_id']; ?>"><button>Edit</button></a>
                <!-- Delete Form -->
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="commission_id" value="<?= $commission['commission_id']; ?>">
                    <button type="submit" name="delete_commission" onclick="return confirm('Are you sure you want to delete this commission?');">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
// Popup handling
function showPopup() {
    document.getElementById('popupForm').style.display = 'block';
}

function hidePopup() {
    document.getElementById('popupForm').style.display = 'none';
}

function showEditPopup() {
    document.getElementById('editPopup').style.display = 'block';
}

function hideEditPopup() {
    document.getElementById('editPopup').style.display = 'none';
}

// Automatically show the edit popup if edit_id is set
<?php if (isset($_GET['edit_id'])): ?>
    showEditPopup();
<?php endif; ?>
</script>

<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
<?php include '../admin_php/admin_footer.php'; ?>

</body>
</html>
