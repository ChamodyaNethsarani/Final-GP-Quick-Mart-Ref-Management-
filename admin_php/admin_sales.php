<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header("Location: ../admin_php/login.php");
    exit();
}


// Clear session variables on page load
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Unset the cart and selected shop on page refresh
    unset($_SESSION['cart']);
    unset($_SESSION['selected_shop']);
}

include '../db_connection.php';


// Calculate the total amount of the cart using the shop price
// Initialize total amount as 0
$totalAmount = 0;

// Debugging: Check if cart is empty or not
if (empty($_SESSION['cart'])) {
    echo "<script>console.log('Cart is empty');</script>";
} else {
    echo "<script>console.log('Cart is not empty, proceeding with calculation');</script>";
}

// Check if the cart is not empty
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $productId => $item) {
        // Query to get product details based on product ID
        $product_sql = "SELECT * FROM product WHERE product_id = $productId";
        $product_result = $conn->query($product_sql);
        
        if ($product_result->num_rows > 0) {
            $product = $product_result->fetch_assoc();
            
            // Debugging: Log product details
            echo "<script>console.log('Product Found: Product ID: $productId, Shop Price: " . $product['shop_price'] . ", Quantity: " . $item['quantity'] . "');</script>";
            
            // Ensure the shop price is available for the product
            if (isset($product['shop_price']) && is_numeric($product['shop_price'])) {
                $price = $product['shop_price'];  // Get the shop price of the product
                $quantity = $item['quantity'];    // Get the quantity added to the cart
                
                // Add to total amount: price * quantity
                $totalAmount += $price * $quantity;
            } else {
                echo "<script>alert('Error: Shop price not found or invalid for product ID: $productId');</script>";
            }
        } else {
            echo "<script>alert('Error: Product ID: $productId not found in database');</script>";
        }
    }
} else {
    // If the cart is empty, ensure totalAmount is 0
    $totalAmount = 0;
}

// Debugging: Log the final total amount
echo "<script>console.log('Total Amount Calculated: $totalAmount');</script>";


if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle shop selection
if (isset($_POST['select_shop'])) {
    $_SESSION['selected_shop'] = $_POST['shop_id'];
}

if (isset($_POST['clear_all'])) {
    // Unset the cart and selected shop
    unset($_SESSION['cart']);
    unset($_SESSION['selected_shop']);
    $_POST['shop_search_value'] = ''; // Reset search value
}

// Check if a shop is selected
$selectedShop = isset($_SESSION['selected_shop']) ? $_SESSION['selected_shop'] : null;

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];  // Ensure the product ID is captured correctly
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $selectedShop = isset($_SESSION['selected_shop']) ? $_SESSION['selected_shop'] : null;

    if (!$selectedShop) {
        echo "<script>alert('Please select a shop before adding products to the cart');</script>";
    } else {
        // Check if the product is already in the cart
        if (isset($_SESSION['cart'][$productId])) {
            // If the product exists, update the quantity
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            // Otherwise, add the new product to the cart
            $_SESSION['cart'][$productId] = [
                'quantity' => $quantity,
                'shop_id' => $selectedShop
            ];
        }
    }
}


// Fetch shop data for selection
$shop_sql = "SELECT * FROM shop";
$shops = [];
$shop_result = $conn->query($shop_sql);
if ($shop_result->num_rows > 0) {
    while ($row = $shop_result->fetch_assoc()) {
        $shops[] = $row;
    }
}

// Fetch all product data
$sql = "SELECT * FROM product";
$products = [];
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Remove from cart
if (isset($_POST['remove_from_cart'])) {
    $productId = $_POST['product_id'];
    unset($_SESSION['cart'][$productId]);
}

// Fetch shop data based on search criteria
$shopSearchBy = isset($_POST['shop_search_by']) ? $_POST['shop_search_by'] : 'shop_name';
$shopSearchValue = isset($_POST['shop_search_value']) ? $_POST['shop_search_value'] : '';

$shop_sql = "SELECT * FROM shop WHERE 1=1";
if (!empty($shopSearchValue)) {
    switch ($shopSearchBy) {
        case 'shop_name':
            $shop_sql .= " AND shop_name LIKE '%$shopSearchValue%'";
            break;
        case 'owner_name':
            $shop_sql .= " AND owner_name LIKE '%$shopSearchValue%'";
            break;
        case 'location':
            $shop_sql .= " AND location LIKE '%$shopSearchValue%'";
            break;
        case 'address':
            $shop_sql .= " AND address LIKE '%$shopSearchValue%'";
            break;
        case 'contact_number':
            $shop_sql .= " AND contact_number LIKE '%$shopSearchValue%'";
            break;
        case 'register_date':
            $shop_sql .= " AND register_date = '$shopSearchValue'";
            break;
        case 'register_time':
            $shop_sql .= " AND register_time = '$shopSearchValue'";
            break;
        case 'shop_type':
            $shop_sql .= " AND shop_type LIKE '%$shopSearchValue%'";
            break;
    }
}

$shops = [];
$shop_result = $conn->query($shop_sql);
if ($shop_result->num_rows > 0) {
    while ($row = $shop_result->fetch_assoc()) {
        $shops[] = $row;
    }
}

// Fetch product data based on search criteria
$searchBy = isset($_POST['search_by']) ? $_POST['search_by'] : 'product_name';
$searchValue = isset($_POST['search_value']) ? $_POST['search_value'] : '';

$sql = "SELECT * FROM product WHERE 1=1";
if (!empty($searchValue)) {
    switch ($searchBy) {
        case 'product_name':
            $sql .= " AND product_name LIKE '%$searchValue%'";
            break;
        case 'category':
            $sql .= " AND category LIKE '%$searchValue%'";
            break;
        case 'size':
            $sql .= " AND size LIKE '%$searchValue%'";
            break;
        case 'profit_price':
            $sql .= " AND profit_price = '$searchValue'";
            break;
        case 'shop_price':
            $sql .= " AND shop_price = '$searchValue'";
            break;
        case 'retail_price':
            $sql .= " AND retail_price = '$searchValue'";
            break;
    }
}

$products = [];
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$product_details = [];

// Add product details from cart
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $productId => $item) {
        $product_sql = "SELECT * FROM product WHERE product_id = $productId";
        $product_result = $conn->query($product_sql);
        if ($product_result->num_rows > 0) {
            $product = $product_result->fetch_assoc();

            // Calculate the total price for this product
            $total_price = $product['shop_price'] * $item['quantity'];

            // Add this product's details to the array
            $product_details[] = [
                'product_name' => $product['product_name'],
                'quantity' => $item['quantity'],
                'price' => $product['shop_price'],
                'total_price' => $total_price
            ];
        }
    }
}

// Handle payment submission and save sale details
if (isset($_POST['submit_payment'])) {
    $payment_method = $_POST['payment_method'];
    $totalAmount = floatval($_POST['total_amount']); // Total cart amount
    $sales_data = [];
    $shop_data = [];
    $status = $_POST['status']; // Retrieve the status field

    // Get selected shop details
    $selectedShop = isset($_SESSION['selected_shop']) ? $_SESSION['selected_shop'] : null;
    if ($selectedShop) {
        $shop_sql = "SELECT * FROM shop WHERE shop_id = $selectedShop";
        $shop_result = $conn->query($shop_sql);
        if ($shop_result->num_rows > 0) {
            $shop = $shop_result->fetch_assoc();
            $shop_data = [
                'shop_name' => $shop['shop_name'],
                'shop_location' => $shop['location'],
                'shop_contact' => $shop['contact_number'],
            ];
        }
    }

    // Handle payment details
    if ($payment_method == 'cash') {
        $paid_amount = floatval($_POST['paid_amount']);
        $change_amount = $paid_amount - $totalAmount; // Calculate change correctly

        if ($paid_amount < $totalAmount) {
            echo "<script>alert('Paid amount cannot be less than the total amount.');</script>";
        } else {
            $sales_data = [
                'payment_method' => 'Cash',
                'paid_amount' => $paid_amount,
                'change_amount' => $change_amount,
            ];
        }
    } elseif ($payment_method == 'credit') {
        $paid_amount = floatval($_POST['paid_amount']);
        $credit_balance = $totalAmount - $paid_amount;
        $sales_data = [
            'payment_method' => 'Credit',
            'paid_amount' => $paid_amount,
            'credit_balance' => $credit_balance,
        ];
    } elseif ($payment_method == 'cheque') {
        $cheque_number = $_POST['cheque_number'];
        $sales_data = [
            'payment_method' => 'Cheque',
            'cheque_number' => $cheque_number,
        ];
    } elseif ($payment_method == 'fund_transfer') {
        $reference_number = $_POST['reference_number'];
        $sales_data = [
            'payment_method' => 'Fund Transfer',
            'reference_number' => $reference_number,
        ];
    }

    // Generate a unique invoice number (e.g., INV-20241001-12345)
    $currentDate = date('Ymd'); // e.g., 20241001
    $randomNumber = rand(10000, 99999); // Generate a random 5-digit number
    $invoice_number = "INV-$currentDate-$randomNumber";
    $product_details_json = json_encode($product_details);

    // Get who added this sale (assuming it's stored in a session, for example)
    $added_by = htmlspecialchars($_SESSION['user_name']);;  // Adjust according to your session management

    // Insert sale details into the `sales` table if payment is valid
    if ($sales_data) {
        $stmt = $conn->prepare("INSERT INTO sales (shop_name, shop_location, shop_contact, payment_method, paid_amount, change_amount, credit_balance, cheque_number, reference_number, total_amount, sale_date, invoice_number, status, product_details, added_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssddssssssss", 
            $shop_data['shop_name'],
            $shop_data['shop_location'],
            $shop_data['shop_contact'],
            $sales_data['payment_method'],
            $sales_data['paid_amount'],
            $sales_data['change_amount'],
            $sales_data['credit_balance'],
            $sales_data['cheque_number'],
            $sales_data['reference_number'],
            $totalAmount,
            $invoice_number,
            $status,
            $product_details_json,  // Save the product details as JSON
            $added_by  // Store who added the sale
        );
        $stmt->execute();
        $sale_id = $stmt->insert_id;  // Get the inserted sale ID
        $stmt->close();

        // Insert products into the `sales_products` table
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $productId => $item) {
                $product_sql = "SELECT * FROM product WHERE product_id = $productId";
                $product_result = $conn->query($product_sql);
                if ($product_result->num_rows > 0) {
                    $product = $product_result->fetch_assoc();

                    // Insert each product detail into the sales_products table
                    $stmt = $conn->prepare("INSERT INTO sales_products (sale_id, product_name, quantity, shop_price, sale_price, size) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param(
                        "isidds",
                        $sale_id,
                        $product['product_name'],
                        $item['quantity'],
                        $product['shop_price'],
                        $product['retail_price'],
                        $product['size']
                    );
                    $stmt->execute();
                }
            }
        }

        // Reduce stock for each product in the cart
        foreach ($_SESSION['cart'] as $productId => $item) {
            // Fetch the current stock of the product
            $product_sql = "SELECT qty FROM product WHERE product_id = $productId";
            $product_result = $conn->query($product_sql);
            if ($product_result->num_rows > 0) {
                $product = $product_result->fetch_assoc();
                $current_stock = $product['qty'];

                // Check if the stock is sufficient
                if ($current_stock >= $item['quantity']) {
                    // Calculate the new stock value
                    $new_stock = $current_stock - $item['quantity'];

                    // Update the product stock in the database
                    $update_stock_sql = "UPDATE product SET qty = $new_stock WHERE product_id = $productId";
                    $conn->query($update_stock_sql);
                } else {
                    echo "<script>alert('Insufficient stock for product ID: $productId');</script>";
                }
            }
        }

        // Clear cart after saving the sale
        unset($_SESSION['cart']);
        unset($_SESSION['selected_shop']); // Also clear selected shop

        // Show a success message with change amount if payment was successful
        echo "<script>
            alert('Payment Successful! Your invoice number is $invoice_number. Your change is Rs." . number_format($change_amount, 2) . "' );
            window.location.href = 'admin_salesHistory.php';
        </script>";
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
    <link rel="stylesheet" href="../admin_css/admin_nav.css?v=1">
    <title>Select Shop and Add Products</title>
    <link rel="stylesheet" href="../admin_css/admin_sales.css">
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

    <!-- Low stock notification right below the navigation bar -->
<?php if (!empty($low_stock_message)): ?>
    <div class="low-stock-notification">
        <?= $low_stock_message ?>
    </div>
<?php endif; ?>

<!-- Add button to go to Sales History -->
<div class="sales_page">
    
<div class="header-buttons">
    <a href="admin_salesHistory.php">Sales History</a>
    <form method="POST" action="">
        <button type="submit" name="clear_all">Clear All</button>
    </form>
</div>

<!-- Shop Search Form -->
<h2>Shop Data</h2>
<form method="POST" action="">
    <div class="filter-container">
        <select name="shop_search_by">
            <option value="shop_name" <?= ($shopSearchBy == 'shop_name') ? 'selected' : '' ?>>Shop Name</option>
            <option value="owner_name" <?= ($shopSearchBy == 'owner_name') ? 'selected' : '' ?>>Owner Name</option>
            <option value="address" <?= ($shopSearchBy == 'address') ? 'selected' : '' ?>>Address</option>
            <option value="contact_number" <?= ($shopSearchBy == 'contact_number') ? 'selected' : '' ?>>Contact Number</option>
        </select>

        <input type="text" name="shop_search_value" placeholder="Search here..." value="<?= htmlspecialchars($shopSearchValue) ?>">

        <button type="submit">Search</button>

        <button type="button" onclick="window.location.href=window.location.href">Clear</button>
    </div>
</form>

<form method="POST" action="">
    <div class="table-responsive">
    <table class="shop-table">
        <thead>
            <tr>
                <th>Select</th>
                <th>Shop Name</th>
                <th>Owner Name</th>
                <th>Location</th>
                <th>Address</th>
                <th>Contact Number</th>
                <th>Register Date</th>
                <th>Register Time</th>
                <th>Shop Type</th>
            </tr>
        </thead>
        <tbody id="shopTableBody">
            <?php if (count($shops) > 0): ?>
                <?php foreach ($shops as $index => $shop): ?>
                    <tr class="<?= ($index >= 5) ? 'hidden-row' : '' ?>">
                        <td>
                            <input type="radio" name="shop_id" value="<?= htmlspecialchars($shop['shop_id']); ?>" <?= ($selectedShop == $shop['shop_id']) ? 'checked' : '' ?>>
                        </td>
                        <td><?= htmlspecialchars($shop['shop_name']); ?></td>
                        <td><?= htmlspecialchars($shop['owner_name']); ?></td>
                        <td>
                                <button onclick="window.open('<?= htmlspecialchars($shop['location']) ?>', '_blank')">View Location</button>
                            </td>
                        <td><?= htmlspecialchars($shop['address']); ?></td>
                        <td><?= htmlspecialchars($shop['contact_number']); ?></td>
                        <td><?= htmlspecialchars($shop['register_date']); ?></td>
                        <td><?= htmlspecialchars($shop['register_time']); ?></td>
                        <td><?= htmlspecialchars($shop['shop_type']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No shops found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

    <button type="submit" name="select_shop">Select Shop</button>
    
    <?php if (count($shops) > 5): ?>
        <button type="button" id="showMoreBtn">Show More</button>
    <?php endif; ?>
</form>

<br/>
<!-- Product Search Form -->
<h2>Product Data</h2>
<form method="POST" action="">
    <div class="filter-container">
        <select name="search_by">
            <option value="product_name" <?= ($searchBy == 'product_name') ? 'selected' : '' ?>>Product Name</option>
            <option value="size" <?= ($searchBy == 'size') ? 'selected' : '' ?>>Size</option>
            <option value="shop_price" <?= ($searchBy == 'shop_price') ? 'selected' : '' ?>>Shop Price</option>
        </select>

        <input type="text" name="search_value" placeholder="Search here..." value="<?= htmlspecialchars($searchValue) ?>">

        <button type="submit">Search</button>

        <button type="button" onclick="window.location.href=window.location.href">Clear</button>
    </div>
</form>

<!-- Product Table -->
<div class="table-responsive">
    <table class="product-table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Size</th>
                <th>Shop Price</th>
                <th>Retail Price</th>
                <th>Stock</th> <!-- New column for Stock -->
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['product_name']); ?></td>
                        <td><?= htmlspecialchars($product['size']); ?></td>
                        <td><?= htmlspecialchars($product['shop_price']); ?></td>
                        <td><?= htmlspecialchars($product['retail_price']); ?></td>
                        <td><?= htmlspecialchars($product['qty']); ?></td> <!-- Displaying stock -->
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>">
                                <input type="number" name="quantity" min="1" value="1" style="width: 60px;">
                                <button type="submit" name="add_to_cart">Add to Cart</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No products found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>



<!-- Cart Display -->
<div class="">
<h2>Your Cart</h2>

<table class="cart-table">
    <thead>
        <tr>
            <th>Product ID</th>
            <th>Quantity</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($_SESSION['cart'])): ?>
            <?php foreach ($_SESSION['cart'] as $productId => $cartItem): ?>
                <tr>
                    <td><?= htmlspecialchars($productId); ?></td>
                    <td><?= htmlspecialchars($cartItem['quantity']); ?></td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="product_id" value="<?= $productId; ?>">
                            <button type="submit" name="remove_from_cart">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr class="empty-cart-row">
                <td colspan="3">Your cart is empty.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<!-- Cart Summary -->
 <div class="cart-total">
<h2>Cart Total: Rs.<?= number_format($totalAmount, 2) ?></h2>
</div>

<!-- Payment Form -->
 <div class="payment-options">
<h2>Payment Options</h2>
<form method="POST" action="">
    <input type="hidden" name="total_amount" value="<?= $totalAmount ?>">

    <label for="payment_method">Select Payment Method:</label>
    <select name="payment_method" id="payment_method" required>
        <option value="cash">Cash</option>
        <option value="credit">Credit</option>
        <option value="cheque">Cheque</option>
        <option value="fund_transfer">Fund Transfer</option>
    </select>

    <!-- Combined Cash and Credit Payment -->
    <div id="cash_credit_payment" class="payment-details" style="display: none;">
        <label for="paid_amount">Paid Amount:</label>
        <input type="number" name="paid_amount" step="0.01" min="0">
    </div>

    <!-- Cheque Payment -->
    <div id="cheque_payment" class="payment-details" style="display: none;">
        <label for="cheque_number">Cheque Number:</label>
        <input type="text" name="cheque_number">
    </div>

    <!-- Fund Transfer Payment -->
    <div id="fund_transfer_payment" class="payment-details" style="display: none;">
        <label for="reference_number">Reference Number:</label>
        <input type="text" name="reference_number">
    </div>

    <div class="description">
        <label for="status">Description :</label>
        <input type="text" name="status" placeholder="Enter Any Special Details" required>
    </div>
    <div class="submit-button"></div>
    <button type="submit" name="submit_payment">Submit Payment</button>
    </div>
</form>
</div>
</div>

</body>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Show/hide payment details based on the selected payment method
        const paymentMethodSelect = document.getElementById('payment_method');
        const paymentDetailsDivs = {
            cash_credit: document.getElementById('cash_credit_payment'),
            cheque: document.getElementById('cheque_payment'),
            fund_transfer: document.getElementById('fund_transfer_payment')
        };

        paymentMethodSelect.addEventListener('change', function() {
            // Hide all payment fields initially
            Object.values(paymentDetailsDivs).forEach(div => div.style.display = 'none');

            // Show the paid amount field for both "Cash" and "Credit"
            if (this.value === 'cash' || this.value === 'credit') {
                paymentDetailsDivs.cash_credit.style.display = 'block';
            }

            // Show only the relevant field based on the selected payment method
            if (this.value && paymentDetailsDivs[this.value]) {
                paymentDetailsDivs[this.value].style.display = 'block';
            }
        });

        // Trigger change event to set initial visibility when the page loads
        paymentMethodSelect.dispatchEvent(new Event('change'));

        // Validate Paid Amount
        const paymentForm = document.querySelector('form');
        paymentForm.addEventListener('submit', function(event) {
            const totalAmount = parseFloat(document.querySelector('input[name="total_amount"]').value);
            const paidAmountInput = document.querySelector('input[name="paid_amount"]');
            if (paidAmountInput && paidAmountInput.value && parseFloat(paidAmountInput.value) < totalAmount && paymentMethodSelect.value === 'cash') {
                event.preventDefault();  // Prevent form submission if paid amount is less than total for cash
                alert('Paid amount cannot be less than the total amount.');
            }
        });
    });
</script>
   
<!-- Include the navigation bar -->
    <?php include '../admin_php/admin_nav.php'; ?>

 <!-- Include the footer file here -->
<?php include '../admin_php/admin_footer.php'; ?>

</html>
