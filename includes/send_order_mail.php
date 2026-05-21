<?php
// Check if TCPDF exists before loading
if (!file_exists(__DIR__ . '/../vendor/TCPDF-main/tcpdf.php')) {
    if (!function_exists('generateInvoicePDF')) {
        function generateInvoicePDF($order, $items, $user) {
            return null;
        }
    }
    return;
}

require_once __DIR__ . '/send_mail.php';
require_once __DIR__ . '/../vendor/TCPDF-main/tcpdf.php';

function generateInvoicePDF($order, $items, $user) {
    try {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Document info
        $pdf->SetCreator('Green Life');
        $pdf->SetAuthor('Green Life');
        $pdf->SetTitle('Invoice #' . $order['id']);

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Add page
        $pdf->AddPage();
        $pdf->SetMargins(15, 15, 15);

        // ===== HEADER =====
        $pdf->SetFillColor(27, 94, 32);
        $pdf->Rect(0, 0, 210, 35, 'F');

        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY(15, 8);
        $pdf->Cell(0, 10, 'GreenLife', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetXY(15, 20);
        $pdf->Cell(0, 8,
            'Plant a Seed, Grow a Life | Karnataka, India',
            0, 1, 'L');

        // ===== INVOICE TITLE =====
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetXY(15, 42);
        $pdf->Cell(0, 10, 'INVOICE', 0, 1, 'L');

        // Invoice details box
        $pdf->SetFillColor(232, 245, 233);
        $pdf->RoundedRect(130, 40, 65, 38, 3, '1111', 'F');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY(133, 43);
        $pdf->Cell(25, 6, 'Invoice No:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(0, 6,
            '#ORD-' . str_pad($order['id'], 5, '0', STR_PAD_LEFT),
            0, 1, 'L');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY(133, 50);
        $pdf->Cell(25, 6, 'Date:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(0, 6,
            date('d M Y', strtotime($order['created_at'])),
            0, 1, 'L');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY(133, 57);
        $pdf->Cell(25, 6, 'Payment ID:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(0, 6,
            substr($order['payment_id'], 0, 20),
            0, 1, 'L');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY(133, 64);
        $pdf->Cell(25, 6, 'Status:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(46, 125, 50);
        $pdf->Cell(0, 6, 'PAID', 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);

        // ===== BILL TO =====
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetXY(15, 55);
        $pdf->Cell(0, 8, 'Bill To:', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY(15, 63);
        $pdf->Cell(0, 6, $order['full_name'], 0, 1, 'L');
        $pdf->SetXY(15, 69);
        $pdf->Cell(0, 6,
            $order['address'] . ', ' . $order['city'],
            0, 1, 'L');
        $pdf->SetXY(15, 75);
        $pdf->Cell(0, 6,
            'Karnataka - ' . $order['pincode'],
            0, 1, 'L');
        $pdf->SetXY(15, 81);
        $pdf->Cell(0, 6,
            'Mobile: ' . $order['mobile'],
            0, 1, 'L');
        $pdf->SetXY(15, 87);
        $pdf->Cell(0, 6,
            'Email: ' . ($user['email'] ?? ''),
            0, 1, 'L');

        // ===== ITEMS TABLE =====
        $pdf->SetXY(15, 102);

        // Table header
        $pdf->SetFillColor(27, 94, 32);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(80, 8, 'Product Name', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Category',    1, 0, 'C', true);
        $pdf->Cell(20, 8, 'Qty',         1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Unit Price',  1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Total',       1, 1, 'C', true);

        // Table rows
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 9);
        $fill = false;

        foreach ($items as $item) {
            $itemTotal = $item['quantity'] * $item['price'];
            $pdf->SetFillColor(241, 248, 233);
            $pdf->Cell(80, 8,
                substr($item['product_name'], 0, 35),
                1, 0, 'L', $fill);
            $pdf->Cell(30, 8,
                $item['cat_name'] ?? '-',
                1, 0, 'C', $fill);
            $pdf->Cell(20, 8,
                $item['quantity'],
                1, 0, 'C', $fill);
            $pdf->Cell(25, 8,
                'Rs.' . number_format($item['price'], 2),
                1, 0, 'R', $fill);
            $pdf->Cell(25, 8,
                'Rs.' . number_format($itemTotal, 2),
                1, 1, 'R', $fill);
            $fill = !$fill;
        }

        // ===== TOTALS =====
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(130, 8, '', 0, 0);
        $pdf->Cell(35, 8, 'Subtotal:', 1, 0, 'R');
        $pdf->Cell(35, 8,
            'Rs.' . number_format($order['total_amount'], 2),
            1, 1, 'R');

        if (!empty($order['discount']) && $order['discount'] > 0) {
            $pdf->Cell(130, 8, '', 0, 0);
            $pdf->SetTextColor(200, 0, 0);
            $pdf->Cell(35, 8, 'Discount:', 1, 0, 'R');
            $pdf->Cell(35, 8,
                '-Rs.' . number_format($order['discount'], 2),
                1, 1, 'R');
            $pdf->SetTextColor(0, 0, 0);
        }

        $pdf->SetFillColor(27, 94, 32);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(130, 10, '', 0, 0);
        $pdf->Cell(35, 10, 'TOTAL PAID:', 1, 0, 'R', true);
        $pdf->Cell(35, 10,
            'Rs.' . number_format($order['final_amount'], 2),
            1, 1, 'R', true);

        // ===== FOOTER =====
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'I', 9);
        $y = $pdf->GetY() + 15;
        $pdf->SetFillColor(232, 245, 233);
        $pdf->RoundedRect(15, $y, 180, 20, 3, '1111', 'F');
        $pdf->SetXY(18, $y + 3);
        $pdf->Cell(0, 6,
            'Thank you for shopping with GreenLife! ' .
            'Delivered in 3-7 working days.',
            0, 1, 'C');
        $pdf->SetXY(18, $y + 10);
        $pdf->Cell(0, 6,
            'support@greenlife.com | Karnataka, India',
            0, 1, 'C');

        // Return PDF as string
        return $pdf->Output('invoice_' . $order['id'] . '.pdf', 'S');

    } catch (Exception $e) {
        error_log("PDF generation error: " . $e->getMessage());
        return null;
    }
}
?>