<?php
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
?>
