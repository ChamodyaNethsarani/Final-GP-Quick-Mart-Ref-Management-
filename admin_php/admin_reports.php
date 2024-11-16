<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header("Location: ../admin_php/login.php");
    exit();
}

include '../db_connection.php';


// Handle the filtering by 'added_by'
$added_by_filter = isset($_POST['added_by_filter']) ? $_POST['added_by_filter'] : '';

// Prepare the SQL query to fetch sales, with an optional filter for 'added_by'
$sql = "SELECT * FROM sales";
if (!empty($added_by_filter)) {
    $sql .= " WHERE added_by = ?";
}

// Fetch unique users from the 'sales' table for filtering
$users_sql = "SELECT DISTINCT added_by FROM sales";
$users_result = $conn->query($users_sql);
$users = [];
if ($users_result->num_rows > 0) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row['added_by'];
    }
}

// Prepare and execute the SQL query for sales data
$stmt = $conn->prepare($sql);
if (!empty($added_by_filter)) {
    $stmt->bind_param("s", $added_by_filter);
}
$stmt->execute();
$sales_result = $stmt->get_result();

// Function to fetch daily sales report
function getDailySales($conn) {
    $sql = "SELECT 
                DATE(sale_date) AS sale_day, 
                SUM(paid_amount) AS total_paid, 
                SUM(total_amount) AS total_sales, 
                COUNT(*) AS total_transactions
            FROM sales
            WHERE DATE(sale_date) = CURDATE()
            GROUP BY sale_day";
    return $conn->query($sql);
}

// Function to fetch monthly sales report
function getMonthlySales($conn) {
    $sql = "SELECT 
                DATE_FORMAT(sale_date, '%Y-%m') AS sale_month, 
                SUM(paid_amount) AS total_paid, 
                SUM(total_amount) AS total_sales, 
                COUNT(*) AS total_transactions
            FROM sales
            WHERE YEAR(sale_date) = YEAR(CURDATE()) 
            AND MONTH(sale_date) = MONTH(CURDATE())
            GROUP BY sale_month";
    return $conn->query($sql);
}

// Function to fetch annual sales report
function getAnnualSales($conn) {
    $sql = "SELECT 
                YEAR(sale_date) AS sale_year, 
                SUM(paid_amount) AS total_paid, 
                SUM(total_amount) AS total_sales, 
                COUNT(*) AS total_transactions
            FROM sales
            WHERE YEAR(sale_date) = YEAR(CURDATE())
            GROUP BY sale_year";
    return $conn->query($sql);
}

// Function to fetch product sales report
function getProductSales($conn) {
    $sql = "
        SELECT 
            product_name, 
            SUM(quantity) AS total_quantity_sold, 
            SUM(quantity * sale_price) AS total_revenue
        FROM 
            sales_products
        GROUP BY 
            product_name
        ORDER BY 
            total_quantity_sold DESC
    ";
    $result = $conn->query($sql);
    
    // Check if the query succeeded
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    
    return $result;
}

// Function to fetch employee commission report
function getEmployeeCommissions($conn) {
    $sql = "
    SELECT 
    employee.username, 
    sales_products.product_name, 
    SUM(sales_products.quantity) AS total_sales, 
    SUM(sales_products.quantity * commission.commission_rate) AS total_commission, 
    DATE_FORMAT(sales.sale_date, '%Y-%m') AS sale_month
FROM 
    sales
JOIN 
    sales_products ON sales.sale_id = sales_products.sale_id
JOIN 
    commission ON sales_products.product_name = commission.product_name
JOIN 
    employee ON sales.added_by = employee.username
GROUP BY 
    employee.username, product_name, sale_month
ORDER BY 
    sale_month DESC";
    
    return $conn->query($sql);
}

// Fetch all the reports
$dailySales = getDailySales($conn);
$monthlySales = getMonthlySales($conn);
$annualSales = getAnnualSales($conn);
$productSales = getProductSales($conn);
$employeeCommissions = getEmployeeCommissions($conn); // Fetch employee commissions


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
    <title>Admin Reports</title>
    <link rel="stylesheet" href="../admin_css/admin_nav.css">
    <link rel="stylesheet" href="../admin_css/admin_footer.css">
    <link rel="stylesheet" href="../admin_css/admin_reports.css">

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
<br><br>
<!-- Low stock notification right below the navigation bar -->
<?php if (!empty($low_stock_message)): ?>
    <div class="low-stock-notification">
        <?= $low_stock_message ?>
    </div>
<?php endif; ?>

<div class="container">
    <br/><br/>
    <h1>Sales Report</h1>
    <br><br>

    <!-- Filter Form for 'Added By' -->
    <form method="POST" action="admin_reports.php">
        <label for="added_by_filter">Filter by Added By:</label>
        <select name="added_by_filter" id="added_by_filter">
            <option value="">All Users</option>
            <?php foreach ($users as $user): ?>
                <option value="<?= htmlspecialchars($user); ?>" <?= ($added_by_filter == $user) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($user); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
    </form>
    <br><br>
    <!-- Sales Table -->
    <h2>Sales Data</h2>
    <?php if ($sales_result->num_rows > 0): ?>
        <table class="sales-table">
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Shop Name</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Payment Method</th>
                    <th>Sale Date</th>
                    <th>Added By</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $sales_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['invoice_number']); ?></td>
                        <td><?= htmlspecialchars($row['shop_name']); ?></td>
                        <td><?= htmlspecialchars($row['total_amount']); ?></td>
                        <td><?= htmlspecialchars($row['paid_amount']); ?></td>
                        <td><?= htmlspecialchars($row['payment_method']); ?></td>
                        <td><?= htmlspecialchars($row['sale_date']); ?></td>
                        <td><?= htmlspecialchars($row['added_by']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No sales records found for this user.</p>
    <?php endif; ?>

    <!-- Daily Sales Report -->
    <h2>Daily Sales</h2>
    <?php if ($dailySales->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Sale Day</th>
                    <th>Total Paid</th>
                    <th>Total Sales</th>
                    <th>Total Transactions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $dailySales->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['sale_day']) ?></td>
                        <td><?= htmlspecialchars($row['total_paid']) ?></td>
                        <td><?= htmlspecialchars($row['total_sales']) ?></td>
                        <td><?= htmlspecialchars($row['total_transactions']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No sales data for today.</p>
    <?php endif; ?>
    <br>
    <!-- Monthly Sales Report -->
    <h2>Monthly Sales</h2>
    <?php if ($monthlySales->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Sale Month</th>
                    <th>Total Paid</th>
                    <th>Total Sales</th>
                    <th>Total Transactions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $monthlySales->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['sale_month']) ?></td>
                        <td><?= htmlspecialchars($row['total_paid']) ?></td>
                        <td><?= htmlspecialchars($row['total_sales']) ?></td>
                        <td><?= htmlspecialchars($row['total_transactions']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No sales data for this month.</p>
    <?php endif; ?>
    <br>
    <!-- Annual Sales Report -->
    <h2>Annual Sales</h2>
    <?php if ($annualSales->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Sale Year</th>
                    <th>Total Paid</th>
                    <th>Total Sales</th>
                    <th>Total Transactions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $annualSales->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['sale_year']) ?></td>
                        <td><?= htmlspecialchars($row['total_paid']) ?></td>
                        <td><?= htmlspecialchars($row['total_sales']) ?></td>
                        <td><?= htmlspecialchars($row['total_transactions']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No sales data for this year.</p>
    <?php endif; ?>
    <br>
    <!-- Product Sales Report -->
    <h2>Product Sales</h2>
    <?php if ($productSales->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Total Quantity Sold</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $productSales->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td><?= htmlspecialchars($row['total_quantity_sold']) ?></td>
                        <td><?= htmlspecialchars($row['total_revenue']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No product sales data available.</p>
    <?php endif; ?>

    <br>
    <!-- Employee Commission Report -->
    <h2>Employee Commission Report</h2>
    <?php if ($employeeCommissions->num_rows > 0): ?>
        <table class="commission-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Product Name</th>
                    <th>Total Sales</th>
                    <th>Total Commission</th>
                    <th>Month</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $employeeCommissions->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']); ?></td>
                        <td><?= htmlspecialchars($row['product_name']); ?></td>
                        <td><?= htmlspecialchars($row['total_sales']); ?></td>
                        <td><?= htmlspecialchars($row['total_commission']); ?></td>
                        <td><?= htmlspecialchars($row['sale_month']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No commission data found.</p>
    <?php endif; ?>
    <br>
    </div>
<!-- Include the footer file -->
<?php include '../admin_php/admin_footer.php'; ?>

</body>
</html>
