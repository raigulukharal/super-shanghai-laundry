<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $booking->invoice_no }}</title>
    <style>
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 0; }
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
            background: #e5e7eb;
        }
        .invoice {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
        }
        .shop-header {
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .shop-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        .shop-header p {
            font-size: 10px;
            opacity: 0.9;
            margin-top: 3px;
        }
        .customer-code {
            background: #eff6ff;
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }
        .customer-code .label {
            font-size: 10px;
            color: #6b7280;
        }
        .customer-code .code {
            font-size: 16px;
            font-weight: bold;
            color: #1e3a8a;
            margin-left: 8px;
        }
        .dates-row {
            display: flex;
            justify-content: space-between;
            background: #f0f9ff;
            padding: 10px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .dates-row div {
            font-size: 11px;
        }
        .dates-row strong {
            color: #1e3a8a;
        }
        .body {
            padding: 20px;
        }
        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 15px;
        }
        .info-box {
            background: #f9fafb;
            padding: 10px;
            border-radius: 6px;
            flex: 1;
        }
        .info-box h4 {
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-box p {
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 3px;
        }
        .info-box .small {
            font-size: 10px;
            font-weight: normal;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th {
            background: #f9fafb;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            border-bottom: 1px solid #e5e7eb;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            text-align: right;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
        }
        .totals div {
            margin: 5px 0;
            font-size: 11px;
        }
        .grand-total {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a8a;
            margin-top: 8px;
        }
        .footer {
            background: #f9fafc;
            padding: 12px;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            margin-top: 20px;
        }
        .btn-print {
            background: #1e3a8a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 15px;
            font-size: 14px;
            display: inline-block;
        }
        .btn-print:hover {
            background: #1e40af;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 15px;">
        <button onclick="window.print()" class="btn-print">
            🖨️ Print / Save as PDF
        </button>
        <p style="font-size: 11px; margin-top: 5px; color: #6b7280;">Press Ctrl+P or click above to save as PDF</p>
    </div>
    
    <div class="invoice">
        <div class="shop-header">
            <h1>Super Shanghai Dry Cleaners</h1>
            <p>Shora kothi road opposite Christian church near Nadra Office Nankana Sahib</p>
            <p>📞 03010562865</p>
        </div>
        
        <div class="customer-code">
            <span class="label">Customer Code</span>
            <span class="code">{{ $customerCode }}</span>
        </div>
        
        @php
            // Safe date formatting - check if it's a Carbon object
            $bookingDate = $booking->booking_date;
            if ($bookingDate && method_exists($bookingDate, 'format')) {
                $bookingDateFormatted = $bookingDate->format('d-m-Y');
            } else {
                $bookingDateFormatted = $bookingDate ?: date('d-m-Y');
            }
            
            $deliveryDate = $booking->expected_delivery_date;
            if ($deliveryDate && method_exists($deliveryDate, 'format')) {
                $deliveryDateFormatted = $deliveryDate->format('d-m-Y');
            } else {
                $deliveryDateFormatted = $deliveryDate ?: 'Not set';
            }
        @endphp
        
        <div class="dates-row">
            <div>📅 <strong>Booking Date:</strong> {{ $bookingDateFormatted }}</div>
            <div>🚚 <strong>Expected Delivery:</strong> {{ $deliveryDateFormatted }}</div>
        </div>
        
        <div class="body">
            <div class="info-grid">
                <div class="info-box">
                    <h4>Invoice #</h4>
                    <p>{{ $booking->invoice_no }}</p>
                </div>
                <div class="info-box">
                    <h4>Customer</h4>
                    <p>{{ $booking->customer->name ?? 'N/A' }}</p>
                    <p class="small">📱 {{ $booking->customer->mobile ?? 'N/A' }}</p>
                    <p class="small">📍 {{ $booking->customer->area ?? 'N/A' }}</p>
                </div>
                <div class="info-box">
                    <h4>Status</h4>
                    <p>{{ ucfirst($booking->status) }}</p>
                    <p class="small">Payment: {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}</p>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Item</th>
                        <th>Color</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->items as $index => $item)
                    @php
                        $categoryName = '';
                        if($item->category_id == 1) $categoryName = 'Male';
                        elseif($item->category_id == 2) $categoryName = 'Female';
                        elseif($item->category_id == 3) $categoryName = 'Child Male';
                        elseif($item->category_id == 4) $categoryName = 'Child Female';
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $categoryName }}</td>
                        <td><strong>{{ $item->clothType->name ?? 'N/A' }}</strong></td>
                        <td>{{ $item->color->name ?? 'N/A' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">Rs. {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">Rs. {{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="totals">
                <div>Subtotal: Rs. {{ number_format($booking->total_amount, 2) }}</div>
                @if($booking->discount > 0)
                <div>Discount: - Rs. {{ number_format($booking->discount, 2) }}</div>
                @endif
                @if($booking->other_charges > 0)
                <div>Other Charges: + Rs. {{ number_format($booking->other_charges, 2) }}</div>
                @endif
                <div class="grand-total">Grand Total: Rs. {{ number_format($booking->grand_total, 2) }}</div>
                <div>Paid: Rs. {{ number_format($booking->paid_amount, 2) }}</div>
                @if(($booking->grand_total - $booking->paid_amount) > 0)
                <div style="color: #dc2626;">Due: Rs. {{ number_format($booking->grand_total - $booking->paid_amount, 2) }}</div>
                @else
                <div style="color: #10b981;">✓ Fully Paid</div>
                @endif
            </div>
            
            @if($booking->customer_notes)
            <div style="background: #fefce8; padding: 10px; margin-top: 15px; border-radius: 6px;">
                <strong>📝 Notes:</strong><br>{{ nl2br(e($booking->customer_notes)) }}
            </div>
            @endif
        </div>
        
        <div class="footer">
            <p>Thank you for choosing Super Shanghai Dry Cleaners!</p>
            <p>For queries: 03010562865</p>
            <p>This is a computer-generated invoice. Valid for 30 days.</p>
        </div>
    </div>
</body>
</html>