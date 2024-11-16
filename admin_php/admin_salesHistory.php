<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../db_connection.php';



if (isset($_GET['sale_id'])) {
    $sale_id = $_GET['sale_id'];

    // Query to fetch the sale details
    $sale_sql = "SELECT * FROM sales WHERE sale_id = $sale_id";
    $sale_result = $conn->query($sale_sql);

    if ($sale_result->num_rows > 0) {
        $sale = $sale_result->fetch_assoc();

        // Prepare the response with sale details
        $response = [
            'invoice_number' => htmlspecialchars($sale['invoice_number']),
            'shop_name' => htmlspecialchars($sale['shop_name']),
            'shop_location' => htmlspecialchars($sale['shop_location']),
            'shop_contact' => htmlspecialchars($sale['shop_contact']),
            'total_amount' => number_format($sale['total_amount'], 2),
            'paid_amount' => number_format($sale['paid_amount'], 2),
            'credit_balance' => number_format($sale['credit_balance'], 2),
            'payment_method' => htmlspecialchars($sale['payment_method']),
            'status' => htmlspecialchars($sale['status']),
            'product_details' => htmlspecialchars($sale['product_details']),
        ];

        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Sale not found.']);
    }

    $conn->close();
}
// Handle update sale details
if (isset($_POST['update_sale'])) {
    $sale_id = $_POST['sale_id'];
    $payment_method = $_POST['payment_method'];
    $paid_amount = $_POST['paid_amount'];
    $status = $_POST['status'];

    if (!empty($sale_id) && is_numeric($sale_id)) {
        $current_sale_sql = "SELECT credit_balance FROM sales WHERE sale_id = $sale_id";
        $current_sale_result = $conn->query($current_sale_sql);

        if ($current_sale_result->num_rows > 0) {
            $current_sale = $current_sale_result->fetch_assoc();
            $new_credit_balance = $current_sale['credit_balance'] - $paid_amount;

            $update_sql = "UPDATE sales 
                           SET payment_method='$payment_method', 
                               paid_amount=paid_amount + $paid_amount, 
                               credit_balance=$new_credit_balance, 
                               status='$status' 
                           WHERE sale_id = $sale_id";

            if ($conn->query($update_sql) === TRUE) {
                echo "<script>alert('Sale updated successfully');</script>";
                echo "<script>window.location.href = 'admin_salesHistory.php';</script>";
            } else {
                echo "<script>alert('Error updating sale');</script>";
            }
        } else {
            echo "<script>alert('Error: Sale not found.');</script>";
        }
    } else {
        echo "<script>alert('Invalid sale ID.');</script>";
    }
}

// Handle delete sale logic
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    if (!empty($delete_id) && is_numeric($delete_id)) {
        $delete_sql = "DELETE FROM sales WHERE sale_id = $delete_id";
        if ($conn->query($delete_sql) === TRUE) {
            echo "<script>alert('Sale record deleted successfully');</script>";
            echo "<script>window.location.href = 'admin_salesHistory.php';</script>";
        } else {
            echo "<script>alert('Error deleting sale record');</script>";
        }
    } else {
        echo "<script>alert('Invalid sale ID.');</script>";
    }
}

// Default SQL query to fetch all sales
$sales_sql = "SELECT * FROM sales";

// Check if the form is submitted for search
if (isset($_POST['search'])) {
    $search_by = $_POST['search_by']; // Filter (shop_name or invoice_number)
    $search_value = $_POST['search_value']; // User's search input

    if ($search_by == 'shop_name') {
        $sales_sql .= " WHERE shop_name LIKE '%$search_value%'";
    } elseif ($search_by == 'invoice_number') {
        $sales_sql .= " WHERE invoice_number LIKE '%$search_value%'";
    }
}


$sales_result = $conn->query($sales_sql);

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
    <title>Sales History</title>
    <link rel="stylesheet" href="../admin_css/admin_nav.css?v=1">
    <link rel="stylesheet" href="../admin_css/admin_salesHistory.css">
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

<!-- Include the navigation bar -->
    <?php include '../admin_php/admin_nav.php'; ?>
    <br><br><br><br><br>
<!-- Low stock notification right below the navigation bar -->
<?php if (!empty($low_stock_message)): ?>
    <div class="low-stock-notification">
        <?= $low_stock_message ?>
    </div>
<?php endif; ?>


<div class="header-buttons">
    <a href="admin_sales.php">Back to Sales</a>
</div>

<h1>Sales History</h1>

<form method="POST">
    <select name="search_by">
        <option value="shop_name">Shop Name</option>
        <option value="invoice_number">Invoice Number</option>
    </select>

    <input type="text" name="search_value" placeholder="Enter search term..." required>

    <button type="submit" name="search">Search</button>
</form>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Shop Name</th>
            <th>Total Amount</th>
            <th>Payment Method</th>
            <th>Change Amount</th>
            <th>Credit Balance</th>
            <th>Invoice Number</th>
            <th>Status</th>
            <th>Added By</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($sales_result->num_rows > 0): ?>
            <?php while ($sale = $sales_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($sale['sale_date']) ?></td>
                    <td><?= htmlspecialchars($sale['shop_name']) ?></td>
                    <td>Rs.<?= number_format($sale['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($sale['payment_method']) ?></td>
                    <td>Rs.<?= number_format($sale['change_amount'] ?? 0, 2) ?></td>
                    <td>Rs.<?= number_format($sale['credit_balance'] ?? 0, 2) ?></td>
                    <td><?= htmlspecialchars($sale['invoice_number']) ?></td>
                    <td><?= htmlspecialchars($sale['status'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($sale['added_by']) ?></td>
                    <td class="actions">
                        <a href="#" class="btn update-sale" data-sale-id="<?= $sale['sale_id'] ?>">Update</a>
                        <a href="?delete_id=<?= $sale['sale_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this sale?')">Delete</a>
                        <a href="#" class="view-details btn" data-sale-id="<?= $sale['sale_id'] ?>">View</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="10">No sales records found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Modal Structure for Update Sale -->
<div id="updateSaleModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Update Sale</h2>
        <form method="POST" action="">
            <input type="hidden" name="sale_id" id="sale_id_input" value="">
            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="cash">Cash</option>
                <option value="credit">Credit</option>
                <option value="cheque">Cheque</option>
                <option value="fund_transfer">Fund Transfer</option>
            </select>
            <p>
                You have Rs. <span id="credit_balance"></span> outstanding balance.
            </p>
            <label for="paid_amount">Paid Amount (Rs.):</label>
            <input type="number" name="paid_amount" id="paid_amount" step="0.01" required>
            <label for="status">Description:</label>
            <input type="text" name="status" id="status" required>
            <button type="submit" name="update_sale">Submit Payment</button>
        </form>
    </div>
</div>

<!-- Modal Structure for View Sale Details -->
<div id="viewSaleModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Sale Details</h2>
        <div id="viewSaleContent">
            <!-- Sale details and products will be injected here dynamically -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var updateModal = document.getElementById("updateSaleModal");
    var viewModal = document.getElementById("viewSaleModal");
    var closeButtons = document.getElementsByClassName("close");

    // Close modals
    Array.from(closeButtons).forEach(function(closeBtn) {
        closeBtn.onclick = function() {
            updateModal.style.display = "none";
            viewModal.style.display = "none";
        };
    });

    // Close modals if the user clicks outside the modal
    window.onclick = function(event) {
        if (event.target == updateModal) {
            updateModal.style.display = "none";
        }
        if (event.target == viewModal) {
            viewModal.style.display = "none";
        }
    };

    // Handle "Update" button click
    var updateButtons = document.querySelectorAll('.update-sale');
    Array.from(updateButtons).forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            var saleId = this.getAttribute('data-sale-id');
            document.getElementById("sale_id_input").value = saleId;
            updateModal.style.display = "block";
        });
    });

  // Handle "View" button click
    var viewButtons = document.querySelectorAll('.view-details');
    Array.from(viewButtons).forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            var saleId = this.getAttribute('data-sale-id');

            // Make an AJAX request to fetch sale details
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "get_sale_details.php?sale_id=" + saleId, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);

                    if (response.error) {
                        document.getElementById("viewSaleContent").innerHTML = 
                            `<p>${response.error}</p>`;
                    } else {
                        // Populate the modal with sale details
                        document.getElementById("viewSaleContent").innerHTML = `
                            <h3>Invoice Number: ${response.invoice_number}</h3>
                            <p><strong>Shop Name:</strong> ${response.shop_name}</p>
                            <p><strong>Location:</strong> ${response.shop_location}</p>
                            <p><strong>Contact:</strong> ${response.shop_contact}</p>
                            <p><strong>Total Amount:</strong> Rs. ${response.total_amount}</p>
                            <p><strong>Paid Amount:</strong> Rs. ${response.paid_amount}</p>
                            <p><strong>Credit Balance:</strong> Rs. ${response.credit_balance}</p>
                            <p><strong>Payment Method:</strong> ${response.payment_method}</p>
                            <p><strong>Status:</strong> ${response.status}</p>
                            <h4>Product Details:</h4>
                            <p>${response.product_details}</p>
                        `;
                    }

                    viewModal.style.display = "block";
                }
            };
            xhr.send();
        });
    });
});

</script>




<!-- Include the footer file -->
<?php include '../admin_php/admin_footer.php'; ?>

</body>
</html>
