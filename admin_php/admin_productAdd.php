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


// Handle the form submission to add a new product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $size = $_POST['size'];
    $profit_price = $_POST['profit_price'];
    $shop_price = $_POST['shop_price'];
    $retail_price = $_POST['retail_price'];
    $qty = $_POST['qty'];  // Corrected

    $sql = "INSERT INTO product (product_name, category, size, profit_price, shop_price, retail_price, qty)
            VALUES ('$product_name', '$category', '$size', '$profit_price', '$shop_price', '$retail_price', '$qty')";  // Corrected

    if ($conn->query($sql) === TRUE) {
        // After successful insertion, redirect to the same page to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit; // Make sure no further code is executed after the redirect
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle update form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $size = $_POST['size'];
    $profit_price = $_POST['profit_price'];
    $shop_price = $_POST['shop_price'];
    $retail_price = $_POST['retail_price'];
    $qty = $_POST['qty'];  // Corrected

    $sql = "UPDATE product SET product_name='$product_name', category='$category', size='$size', profit_price='$profit_price', shop_price='$shop_price', retail_price='$retail_price', qty='$qty' WHERE product_id=$product_id";  // Corrected

    if ($conn->query($sql) === TRUE) {
        // After successful update, redirect to the same page to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit; // Make sure no further code is executed after the redirect
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM product WHERE product_id = $delete_id";

    if ($conn->query($sql) === TRUE) {
        echo "Product deleted successfully!";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Fetch products from the database
$products = [];
$sql = "SELECT * FROM product";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch product details for editing
$product_to_edit = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $sql = "SELECT * FROM product WHERE product_id = $edit_id";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $product_to_edit = $result->fetch_assoc();
    }
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


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management Page</title>
    <link rel="stylesheet" href="../admin_css/admin_nav.css?v=1">
    <link rel="stylesheet" href="../admin_css/admin_productAdd.css">
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
    <br/><br/><br/><br/>
    <div class="addprd">
    <!-- Low stock notification right below the navigation bar -->
<?php if (!empty($low_stock_message)): ?>
    <div class="low-stock-notification">
        <?= $low_stock_message ?>
    </div>
<?php endif; ?>

    <h1>Product Management Page</h1>

    <!-- New Product Button -->
    <button class="btn" onclick="showPopup()">New Product</button>

    <!-- Popup Form for Adding New Product -->
    <div class="popup" id="popupForm">
        <h2>Add New Product</h2>
        <form id="productForm" method="POST">
            <label for="productName">Product Name:</label>
            <input type="text" id="productName" name="product_name" required><br><br>

            <label for="editCategory">Category:</label>
            <select id="category" name="category" required>
                <option value="" disabled selected>Select Category</option>
                <option value="Biscuit">Biscuit</option>
                <option value="Choclate">Choclate</option>
                <option value="Snacks">Snacks</option>
                <option value="Cooking Essential">Cooking Essential</option>
                <option value="Spices">Spices</option>
            </select><br><br>

            <label for="size">Size:</label>
            <input type="text" id="size" name="size" required><br><br>

            <label for="profitPrice">Profit Price:</label>
            <input type="number" id="profitPrice" name="profit_price" required><br><br>

            <label for="shopPrice">Shop Price:</label>
            <input type="number" id="shopPrice" name="shop_price" required><br><br>

            <label for="retailPrice">Retail Price:</label>
            <input type="number" id="retailPrice" name="retail_price" required><br><br>

            <label for="qty">Qty:</label>
            <input type="number" id="qty" name="qty" required><br><br>

            <button type="submit" class="btn" name="add_product">Add Product</button>
            <button type="button" class="btn" onclick="hidePopup()">Cancel</button>
        </form>
    </div>

    <!-- Popup Form for Editing Product -->
    <?php if ($product_to_edit): ?>
    <div class="edit-popup" id="editPopup">
        <h2>Edit Product</h2>
        <form method="POST">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_to_edit['product_id']); ?>">

            <label for="editProductName">Product Name:</label>
            <input type="text" id="editProductName" name="product_name" value="<?= htmlspecialchars($product_to_edit['product_name']); ?>" required><br><br>

            <label for="editCategory">Category:</label>
            <select id="category" name="category" required>
                <option value="" disabled selected>Select Category</option>
                <option value="Biscuit">Biscuit</option>
                <option value="Choclate">Choclate</option>
                <option value="Snacks">Snacks</option>
                <option value="Cooking Essential">Cooking Essential</option>
                <option value="Spices">Spices</option>
            </select><br><br>

            <label for="editSize">Size:</label>
            <input type="text" id="editSize" name="size" value="<?= htmlspecialchars($product_to_edit['size']); ?>" required><br><br>

            <label for="editProfitPrice">Profit Price:</label>
            <input type="number" id="editProfitPrice" name="profit_price" value="<?= htmlspecialchars($product_to_edit['profit_price']); ?>" required><br><br>

            <label for="editShopPrice">Shop Price:</label>
            <input type="number" id="editShopPrice" name="shop_price" value="<?= htmlspecialchars($product_to_edit['shop_price']); ?>" required><br><br>

            <label for="editRetailPrice">Retail Price:</label>
            <input type="number" id="editRetailPrice" name="retail_price" value="<?= htmlspecialchars($product_to_edit['retail_price']); ?>" required><br><br>

            <label for="editQty">Qty:</label>
            <input type="number" id="editQty" name="qty" value="<?= htmlspecialchars($product_to_edit['qty']); ?>" required><br><br>

            <button type="submit" class="btn" name="update_product">Update Product</button>
            <button type="button" class="btn" onclick="hideEditPopup()">Cancel</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Product Table -->
    <table class="product-table" id="productTable">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Category</th>
                <th>Size</th>
                <th>Profit Price</th>
                <th>Shop Price</th>
                <th>Retail Price</th>
                <th>Qty</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['product_name']); ?></td>
                    <td><?= htmlspecialchars($product['category']); ?></td>
                    <td><?= htmlspecialchars($product['size']); ?></td>
                    <td><?= htmlspecialchars($product['profit_price']); ?></td>
                    <td><?= htmlspecialchars($product['shop_price']); ?></td>
                    <td><?= htmlspecialchars($product['retail_price']); ?></td>
                    <td><?= htmlspecialchars($product['qty']); ?></td>
                    <td>
                        <a href="?edit_id=<?= $product['product_id']; ?>">
                            <button class="edit-btn">Edit</button>
                        </a>
                        <a href="?delete_id=<?= $product['product_id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">
                            <button class="delete-btn">Delete</button>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <script>
    // Show the popup form for adding new product
    function showPopup() {
        document.getElementById('popupForm').classList.add('active');
    }

    // Hide the popup form for adding new product
    function hidePopup() {
        document.getElementById('popupForm').classList.remove('active');
    }

    // Show the popup form for editing product
    function showEditPopup() {
        document.getElementById('editPopup').classList.add('active');
    }

    // Hide the popup form for editing product
    function hideEditPopup() {
        document.getElementById('editPopup').classList.remove('active');
    }

    // Automatically show edit popup if edit_id is set
    <?php if ($product_to_edit): ?>
    window.onload = showEditPopup;
    <?php endif; ?>
    </script>

   
<!-- Include the navigation bar -->
    <?php include '../admin_php/admin_nav.php'; ?>

<br><br><br><br><br><br><br><br><br><br><br><br>
 <!-- Include the footer file here -->
<?php include '../admin_php/admin_footer.php'; ?>

</body>
</html>
