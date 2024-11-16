<?php
// Include Composer's autoload to load TCPDF
require '../vendor/autoload.php'; // Composer autoload

// Check if TCPDF is loaded
if (class_exists('TCPDF')) {
    echo "TCPDF class loaded successfully.<br>";
} else {
    echo "Failed to load TCPDF class.<br>";
}

// Create a new PDF document using TCPDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('Helvetica', 'B', 16);

// Add some content to the PDF
$pdf->Cell(0, 10, 'Hello World! This is a PDF document generated using TCPDF.', 0, 1, 'C');

// Output the PDF (will trigger download in the browser)
$pdf->Output('generated_test.pdf', 'D'); // 'D' forces download
?>
