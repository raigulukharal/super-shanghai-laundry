<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $booking->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .invoice { max-width: 800px; margin: 0 auto; background: white; border: 1px solid #ddd; }
        .header { background: #1e3a8a; color: white; padding: 25px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 5px; letter-spacing: 2px; }
        .header p { font-size: 12px; opacity: 0.8; }
        .body { padding: 25px; }
        .info-section { display: flex; justify-content: space-between; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #e5e7eb; }
        .info-box { flex: 1; }
        .info-box h3 { font-size: 11px; color: #6b7280; margin-bottom: 6px; letter-spacing: 1px; text-transform: uppercase; }
        .info-box p { font-size: 14px; margin-bottom: 4px; }
        .info-box .bold { font-weight: bold; font-size: 16px; color: #1e3a8a; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #f9fafb; padding: 10px; text-align: left; font-size: 11px; font-weight: bold; color: #6b7280; border-bottom: 1px solid #e5e7eb; text-transform: uppercase; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        .totals { text-align: right; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
        .totals div { margin: 6px 0; }
        .grand-total { font-size: 18px; font-weight: bold; color: #1e3a8a; }
        .footer { background: #f9fafb; padding: 15px; text-align: center; font-size: 11px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .status-badge { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-partial_delivered { background: #fed7aa; color: #9a3412; }
        .btn-print { background: #1e3a8a; color: white; border: none; padding: 10px 25px; border-radius: 6px; cursor: pointer; margin-bottom: 20px; font-size: 14px; font-weight: bold; }
        .btn-print:hover { background: #1e40af; }
        @media print { .no-print { display: none; } .invoice { margin: 0; border: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center;">
        <button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>
    </div>
    
    <div class="invoice">
        <div class="header">
            <h1>SSDC LAUNDRY</h1>
            <p>123 Main Street, Karachi, Pakistan | Tel: +92 300 1234567 | GST: 12-3456789</p>
        </div>
        
        <div class="body">
            <div class="info-section">
                <div class="info-box">
                    <h3>INVOICE DETAILS</h3>
                    <p class="bold">{{ $booking->invoice_no }}</p>
                    <p>Date: {{ $booking->booking_date->format('d-m-Y') }}</p>
                    <p>Time: {{ $booking->booking_date->format('h:i A') }}</p>
                </div>
                <div class="info-box">
                    <h3>CUSTOMER INFORMATION</h3>
                    <p class="bold">{{ $booking->customer->name ?? 'Walk-in Customer' }}</p>
                    <p>Mobile: {{ $booking->customer->mobile ?? 'N/A' }}</p>
                    <p>Email: {{ $booking->customer->email ?? 'N/A' }}</p>
                    <p>Area: {{ $booking->customer->area ?? 'N/A' }}</p>
                </div>
                <div class="info-box">
                    <h3>ORDER STATUS</h3>
                    <p><span class="status-badge status-{{ $booking->status }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span></p>
                    <p style="margin-top: 6px;">Payment: {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}</p>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Description</th>
                        <th>Color</th>
                        <th>Delivery Type</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Unit Price</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->clothType->name ?? 'N/A' }}</td>
                        <td>{{ $item->color->name ?? 'N/A' }}</td>
                        <td>{{ ucfirst($item->delivery_type ?? 'normal') }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">Rs. {{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: right; font-weight: bold;">Rs. {{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" style="text-align: right;">Subtotal:</td>
                        <td style="text-align: right;">Rs. {{ number_format($booking->total_amount, 2) }}</td>
                    </tr>
                    @if($booking->discount > 0)
                    <tr>
                        <td colspan="6" style="text-align: right; color: red;">Discount:</td>
                        <td style="text-align: right; color: red;">- Rs. {{ number_format($booking->discount, 2) }}</td>
                    </tr>
                    @endif
                    @if($booking->other_charges > 0)
                    <tr>
                        <td colspan="6" style="text-align: right; color: blue;">Other Charges:</td>
                        <td style="text-align: right; color: blue;">+ Rs. {{ number_format($booking->other_charges, 2) }}</td>
                    </tr>
                    @endif
                    <tr style="background: #f0f9ff;">
                        <td colspan="6" style="text-align: right; font-weight: bold; font-size: 16px;">GRAND TOTAL:</td>
                        <td style="text-align: right; font-weight: bold; font-size: 18px; color: #1e3a8a;">Rs. {{ number_format($booking->grand_total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            
            <div style="margin-top: 20px; background: #f0fdf4; padding: 12px; border-radius: 8px;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Amount Paid:</span>
                    <span><strong>Rs. {{ number_format($booking->paid_amount, 2) }}</strong></span>
                </div>
                @if(($booking->grand_total - $booking->paid_amount) > 0)
                <div style="display: flex; justify-content: space-between; margin-top: 5px; color: red;">
                    <span>Due Amount:</span>
                    <span><strong>Rs. {{ number_format($booking->grand_total - $booking->paid_amount, 2) }}</strong></span>
                </div>
                @endif
            </div>
            
            @if($booking->customer_notes)
            <div style="margin-top: 20px; background: #fefce8; padding: 12px; border-radius: 8px; border-left: 4px solid #eab308;">
                <strong>📝 Notes:</strong> {{ $booking->customer_notes }}
            </div>
            @endif
        </div>
        
        <div class="footer">
            <p>Thank you for choosing SSDC Laundry! Your satisfaction is our priority.</p>
            <p>This is a computer-generated invoice. Valid for 30 days from invoice date.</p>
            <p>For queries, contact: support@ssdc.shop | +92 300 1234567</p>
        </div>
    </div>
</body>
</html>