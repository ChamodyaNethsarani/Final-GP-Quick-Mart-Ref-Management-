<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../admin_php/login.php");
    exit();
}

// Include the shared database connection
include '../db_connection.php';

// Fetch all commissions to display in the table
$commissions = [];
$sql = "SELECT * FROM commission";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $commissions[] = $row;
    }
} else {
    echo "No commissions found.";
}

// Fetch the logged-in user's username or ID (depending on what you're using for identification)
$user_id = $_SESSION['user_id'];

// Function to fetch employee's own commission report
function getEmployeeOwnCommissions($conn, $user_id) {
    $sql = "
    SELECT 
        e.username, 
        sp.product_name, 
        SUM(sp.quantity) AS total_sales, 
        SUM(sp.quantity * c.commission_rate) AS total_commission, 
        DATE_FORMAT(s.sale_date, '%Y-%m') AS sale_month
    FROM 
        sales s
    JOIN 
        sales_products sp ON s.sale_id = sp.sale_id
    JOIN 
        commission c ON sp.product_name = c.product_name
    JOIN 
        employee e ON s.added_by = e.username
    WHERE 
        e.employee_id = ?
    GROUP BY 
        e.username, sp.product_name, sale_month
    ORDER BY 
        sale_month DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}


// Fetch the logged-in employee's commission report
$employeeOwnCommissions = getEmployeeOwnCommissions($conn, $user_id);


// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Commissions</title>
    <link rel="stylesheet" href="../emp_css/emp_commission.css">
    <link rel="stylesheet" href="../emp_css/emp_nav.css">
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

< class="container">
<!-- Include the navigation bar -->
<?php include '../emp_php/emp_nav.php'; ?>
<br><br><br>
<h1> Commission </h1>
<br><br>
<!-- Table displaying commissions -->
<table>
    <thead>
        <tr>
            <th>Category</th>
            <th>Product Name</th>
            <th>Number of Sales</th>
            <th>Commission Rate (%)</th>
            
        </tr>
    </thead>
    <tbody>
        <?php if (count($commissions) > 0): ?>
            <?php foreach ($commissions as $commission) : ?>
            <tr>
                <td><?= htmlspecialchars($commission['category']) ?></td>
                <td><?= htmlspecialchars($commission['product_name']) ?></td>
                <td><?= htmlspecialchars($commission['number_of_sales']) ?></td>
                <td><?= htmlspecialchars($commission['commission_rate']) ?>%</td>
                <td>
                    <!-- Actions like Edit or Delete could go here -->
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No commission data available.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<br><br>

    <!-- Employee Commission Report -->
    <h2>Your Commission Data</h2>
    <?php if ($employeeOwnCommissions->num_rows > 0): ?>
        <table class="commission-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Total Sales</th>
                    <th>Total Commission</th>
                    <th>Month</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $employeeOwnCommissions->fetch_assoc()): ?>
                    <tr>
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


<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
<?php include '../admin_php/admin_footer.php'; ?>

</body>
</html>
