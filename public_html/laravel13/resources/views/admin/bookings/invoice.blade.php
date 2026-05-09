<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $booking->invoice_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #fff;
            padding: 20px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 1px solid #e0e0e0;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .invoice-header {
            background: #1e3a8a;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .invoice-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .invoice-header p {
            font-size: 12px;
            opacity: 0.8;
        }
        
        .invoice-body {
            padding: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-box {
            flex: 1;
        }
        
        .info-box h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-box p {
            font-size: 14px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background: #f3f4f6;
            font-weight: bold;
            font-size: 12px;
        }
        
        td {
            font-size: 14px;
        }
        
        .totals {
            text-align: right;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .totals div {
            margin: 5px 0;
        }
        
        .grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
        }
        
        .footer {
            background: #f3f4f6;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        
        .btn-print {
            background: #1e3a8a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .btn-print:hover {
            background: #1e40af;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>SSDC LAUNDRY SYSTEM</h1>
            <p>123 Main Street, Karachi, Pakistan | Phone: +92 300 1234567 | Email: info@ssdc.shop</p>
        </div>
        
        <div class="invoice-body">
            <!-- Print Button (only visible on screen, not in print) -->
            <div class="no-print" style="text-align: right; margin-bottom: 20px;">
                <button onclick="window.print()" class="btn-print">🖨️ Print Invoice</button>
            </div>
            
            <!-- Invoice Info -->
            <div class="info-row">
                <div class="info-box">
                    <h3>INVOICE #</h3>
                    <p>{{ $booking->invoice_no }}</p>
                </div>
                <div class="info-box">
                    <h3>INVOICE DATE</h3>
                    <p>{{ $booking->booking_date->format('d-m-Y') }}</p>
                </div>
                <div class="info-box">
                    <h3>STATUS</h3>
                    <p>
                        <span class="status-badge status-{{ $booking->status }}">
                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                        </span>
                    </p>
                </div>
            </div>
            
            <!-- Customer Info -->
            <div class="info-row">
                <div class="info-box">
                    <h3>BILL TO</h3>
                    <p><strong>{{ $booking->customer->name ?? 'N/A' }}</strong></p>
                    <p>Mobile: {{ $booking->customer->mobile ?? 'N/A' }}</p>
                    <p>Email: {{ $booking->customer->email ?? 'N/A' }}</p>
                    <p>Area: {{ $booking->customer->area ?? 'N/A' }}</p>
                </div>
                <div class="info-box">
                    <h3>DELIVERY INFORMATION</h3>
                    <p>Type: {{ ucfirst($booking->items->first()->delivery_type ?? 'Normal') }}</p>
                    <p>Expected Date: {{ $booking->items->first()->expected_delivery_date ? $booking->items->first()->expected_delivery_date->format('d-m-Y') : 'N/A' }}</p>
                </div>
            </div>
            
            <!-- Items Table -->
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th>Color</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->clothType->name ?? 'N/A' }}</td>
                        <td>{{ $item->color->name ?? 'N/A' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rs. {{ number_format($item->unit_price, 2) }}</td>
                        <td>Rs. {{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: right; font-weight: bold;">Subtotal:</td>
                        <td>Rs. {{ number_format($booking->total_amount, 2) }}</td>
                    </tr>
                    @if($booking->discount > 0)
                    <tr>
                        <td colspan="5" style="text-align: right; color: red;">Discount:</td>
                        <td style="color: red;">- Rs. {{ number_format($booking->discount, 2) }}</td>
                    </tr>
                    @endif
                    @if($booking->other_charges > 0)
                    <tr>
                        <td colspan="5" style="text-align: right;">Other Charges:</td>
                        <td>+ Rs. {{ number_format($booking->other_charges, 2) }}</td>
                    </tr>
                    @endif
                    <tr style="background: #f3f4f6;">
                        <td colspan="5" style="text-align: right; font-weight: bold; font-size: 16px;">GRAND TOTAL:</td>
                        <td style="font-weight: bold; font-size: 16px;">Rs. {{ number_format($booking->grand_total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            
            <!-- Payment Info -->
            <div class="info-row">
                <div class="info-box">
                    <h3>PAYMENT SUMMARY</h3>
                    <p>Total Amount: Rs. {{ number_format($booking->grand_total, 2) }}</p>
                    <p>Paid Amount: Rs. {{ number_format($booking->paid_amount, 2) }}</p>
                    <p>Due Amount: Rs. {{ number_format($booking->grand_total - $booking->paid_amount, 2) }}</p>
                    <p>Payment Status: {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}</p>
                </div>
                @if($booking->customer_notes)
                <div class="info-box">
                    <h3>NOTES</h3>
                    <p>{{ $booking->customer_notes }}</p>
                </div>
                @endif
            </div>
            
            <!-- Thank You Note -->
            <div style="text-align: center; margin-top: 30px; padding: 15px; background: #f3f4f6; border-radius: 5px;">
                <p style="font-size: 14px;">Thank you for choosing SSDC Laundry!</p>
                <p style="font-size: 12px; color: #666;">For any queries, please contact us at +92 300 1234567</p>
            </div>
        </div>
        
        <div class="footer">
            <p>This is a computer-generated invoice and does not require a signature.</p>
            <p>Please retain this invoice for future reference.</p>
        </div>
    </div>
</body>
</html>