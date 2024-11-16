<?php
// Start session and connect to the database
session_start();

include '../db_connection.php';

// Handle viewing product details via AJAX
if (isset($_GET['sale_id'])) {
    $view_id = $_GET['sale_id'];
    $sql = "SELECT product_details FROM sales WHERE id = $view_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $sale = $result->fetch_assoc();
        $product_details = json_decode($sale['product_details'], true); // Decode JSON to array

        if ($product_details) {
            echo '<table>';
            echo '<thead>';
            echo '<tr><th>Product Name</th><th>Quantity</th><th>Price</th><th>Total Price</th></tr>';
            echo '</thead>';
            echo '<tbody>';
            foreach ($product_details as $product) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($product['product_name']) . '</td>';
                echo '<td>' . htmlspecialchars($product['quantity']) . '</td>';
                echo '<td>Rs.' . number_format($product['price'], 2) . '</td>';
                echo '<td>Rs.' . number_format($product['total_price'], 2) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo 'No product details found.';
        }
    } else {
        echo 'No product details found.';
    }
}

$conn->close();
?>
