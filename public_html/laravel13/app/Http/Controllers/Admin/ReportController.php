<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Delivery;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\SavedReport;
use App\Models\ShopInvoice;
use App\Models\InvoiceRange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
  public function index()
{
    $stats = [
        'total_bookings' => Booking::count(),
        'total_deliveries' => Delivery::count(),
        'total_customers' => Customer::count(),
        'total_revenue' => Payment::sum('amount'),
        'pending_deliveries' => Booking::where('status', 'pending')->count(),
        'urgent_bookings' => BookingItem::where('delivery_type', 'urgent')->where('status', '!=', 'delivered')->count(),
        'total_ranges' => InvoiceRange::count(),
        'total_invoices' => ShopInvoice::count(),
        'in_shop' => ShopInvoice::where('status', 'in_shop')->count(),
        'delivered' => ShopInvoice::where('status', 'delivered')->count(),
        'pending' => ShopInvoice::where('status', 'pending')->count(),
        'missing' => ShopInvoice::where('is_missing', true)->count(),
        'extra' => ShopInvoice::where('type', 'extra')->count()
    ];
    
    $savedReports = SavedReport::with('generator')->orderBy('created_at', 'desc')->limit(5)->get();
    $ranges = InvoiceRange::orderBy('created_at', 'desc')->get();
    $bookings = Booking::orderBy('invoice_no')->get(['id', 'invoice_no']);
    
    return view('admin.reports.index', compact('stats', 'savedReports', 'ranges', 'bookings'));
}
    public function bookingReport(Request $request)
    {
        $query = Booking::with(['customer', 'items.clothType', 'items.color']);
        
        if ($request->start_date) {
            $query->whereDate('booking_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('booking_date', '<=', $request->end_date);
        }
        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        if ($request->search && $request->search != '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('invoice_no', 'LIKE', $searchTerm)
                  ->orWhereHas('customer', function($cq) use ($searchTerm) {
                      $cq->where('name', 'LIKE', $searchTerm)
                        ->orWhere('mobile', 'LIKE', $searchTerm);
                  })
                  ->orWhere('customer_code_used', 'LIKE', $searchTerm);
            });
        }
        
        $bookings = $query->get()->sortBy(function($booking) {
            if (preg_match('/\d+-(\d+)/', $booking->invoice_no, $matches)) {
                return (int)$matches[1];
            }
            return 0;
        });
        
        $html = $this->generateCleanBookingReportHTML($bookings, $request);
        
        $reportData = [
            'type' => 'booking',
            'title' => 'Booking Report',
            'params' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'search' => $request->search
            ]
        ];
        $this->saveReport($reportData, $html);
        
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="booking_report_' . date('Y-m-d') . '.html"');
    }

    public function fullDeliveryReport(Request $request)
    {
        $deliveries = Delivery::with(['booking.customer', 'items.bookingItem.clothType', 'items.bookingItem.color'])
            ->whereHas('booking', function($q) {
                $q->where('status', 'delivered');
            });
        
        if ($request->start_date) {
            $deliveries->whereDate('delivery_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $deliveries->whereDate('delivery_date', '<=', $request->end_date);
        }
        
        $deliveries = $deliveries->get();
        
        $html = $this->generateCleanDeliveryReportHTML($deliveries, $request, 'Full Delivery Report');
        
        $reportData = [
            'type' => 'full_delivery',
            'title' => 'Full Delivery Report',
            'params' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]
        ];
        $this->saveReport($reportData, $html);
        
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="full_delivery_report_' . date('Y-m-d') . '.html"');
    }

    public function partialDeliveryReport(Request $request)
    {
        $bookings = Booking::with(['customer', 'items.clothType', 'items.color'])
            ->where('status', 'partial_delivered');
        
        if ($request->start_date) {
            $bookings->whereDate('booking_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $bookings->whereDate('booking_date', '<=', $request->end_date);
        }
        
        $bookings = $bookings->get();
        
        $html = $this->generateCleanPartialReportHTML($bookings, $request, 'Partial Delivery Report');
        
        $reportData = [
            'type' => 'partial_delivery',
            'title' => 'Partial Delivery Report',
            'params' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]
        ];
        $this->saveReport($reportData, $html);
        
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="partial_delivery_report_' . date('Y-m-d') . '.html"');
    }

    public function urgentBookingReport(Request $request)
    {
        $items = BookingItem::with(['booking.customer', 'clothType', 'color', 'booking'])
            ->where('delivery_type', 'urgent')
            ->where('status', '!=', 'delivered');
        
        if ($request->start_date) {
            $items->whereDate('expected_delivery_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $items->whereDate('expected_delivery_date', '<=', $request->end_date);
        }
        
        $items = $items->get();
        
        $html = $this->generateCleanUrgentReportHTML($items, $request, 'Urgent Orders Report');
        
        $reportData = [
            'type' => 'urgent',
            'title' => 'Urgent Orders Report',
            'params' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]
        ];
        $this->saveReport($reportData, $html);
        
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="urgent_report_' . date('Y-m-d') . '.html"');
    }

    public function nonDeliveredReport(Request $request)
    {
        $items = BookingItem::with(['booking.customer', 'clothType', 'color'])
            ->where('status', '!=', 'delivered')
            ->whereRaw('delivered_quantity < quantity')
            ->orderBy('booking_id')
            ->get();
        
        $totalPending = $items->sum(function($item) {
            return $item->quantity - ($item->delivered_quantity ?? 0);
        });
        
        return view('admin.reports.non-delivered', compact('items', 'totalPending'));
    }
    
    public function shortBookingReport(Request $request)
    {
        $query = Booking::with(['customer', 'items']);
        
        if ($request->start_date) {
            $query->whereDate('booking_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('booking_date', '<=', $request->end_date);
        }
        
        $allBookings = $query->get();
        
        $shortBookings = $allBookings->filter(function($booking) {
            $totalQuantity = $booking->items->sum('quantity');
            return $totalQuantity <= 3;
        });
        
        $stats = [
            'total' => $shortBookings->count(),
            'total_amount' => $shortBookings->sum('grand_total'),
            'total_paid' => $shortBookings->sum('paid_amount'),
            'total_due' => $shortBookings->sum('grand_total') - $shortBookings->sum('paid_amount'),
            'avg_items' => $shortBookings->avg(function($b) { return $b->items->sum('quantity'); }) ?? 0
        ];
        
        $html = $this->generateCleanShortBookingReportHTML($shortBookings, $stats, $request, 'Short Booking Report (≤ 3 items)');
        
        $reportData = [
            'type' => 'short_booking',
            'title' => 'Short Booking Report',
            'params' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]
        ];
        $this->saveReport($reportData, $html);
        
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="short_booking_report_' . date('Y-m-d') . '.html"');
    }

    public function longBookingReport(Request $request)
    {
        $query = Booking::with(['customer', 'items']);
        
        if ($request->start_date) {
            $query->whereDate('booking_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('booking_date', '<=', $request->end_date);
        }
        
        $allBookings = $query->get();
        
        $longBookings = $allBookings->filter(function($booking) {
            $totalQuantity = $booking->items->sum('quantity');
            return $totalQuantity >= 15;
        });
        
        $stats = [
            'total' => $longBookings->count(),
            'total_amount' => $longBookings->sum('grand_total'),
            'total_paid' => $longBookings->sum('paid_amount'),
            'total_due' => $longBookings->sum('grand_total') - $longBookings->sum('paid_amount'),
            'avg_items' => $longBookings->avg(function($b) { return $b->items->sum('quantity'); }) ?? 0,
            'max_items' => $longBookings->max(function($b) { return $b->items->sum('quantity'); }) ?? 0
        ];
        
        $html = $this->generateCleanLongBookingReportHTML($longBookings, $stats, $request, 'Long Booking Report (≥ 15 items)');
        
        $reportData = [
            'type' => 'long_booking',
            'title' => 'Long Booking Report',
            'params' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]
        ];
        $this->saveReport($reportData, $html);
        
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="long_booking_report_' . date('Y-m-d') . '.html"');
    }

public function atShopReport(Request $request)
{
    $query = ShopInvoice::with(['invoiceRange']);
    
    // Sirf un invoices ko dikhao jinki booking delivered nahi hai
    $query->whereDoesntHave('booking', function($q) {
        $q->where('status', 'delivered');
    });
    
    if ($request->start_date) {
        $query->whereHas('invoiceRange', function($q) use ($request) {
            $q->whereDate('range_date', '>=', $request->start_date);
        });
    }
    if ($request->end_date) {
        $query->whereHas('invoiceRange', function($q) use ($request) {
            $q->whereDate('range_date', '<=', $request->end_date);
        });
    }
    
    if ($request->type && $request->type != 'all') {
        $query->where('type', $request->type);
    }
    
    if ($request->missing == 'yes') {
        $query->where('is_missing', true);
    }
    
    if ($request->range_id && $request->range_id != '') {
        $query->where('invoice_range_id', $request->range_id);
    }
    
    $shopInvoices = $query->orderBy('invoice_no')->get();
    $ranges = InvoiceRange::orderBy('created_at', 'desc')->get();
    
    $stats = [
        'total_invoices' => $shopInvoices->count(),
        'regular_invoices' => $shopInvoices->where('type', 'regular')->count(),
        'extra_invoices' => $shopInvoices->where('type', 'extra')->count(),
        'missing_invoices' => $shopInvoices->where('is_missing', true)->count(),
        'in_shop' => $shopInvoices->where('status', 'in_shop')->count(),
        'pending' => $shopInvoices->where('status', 'pending')->count(),
        'partial_delivered' => $shopInvoices->where('status', 'partial_delivered')->count()
    ];
    
    $html = $this->generateCleanAtShopReportHTML($shopInvoices, $stats, $ranges, $request, 'At Shop Report');
    
    $reportData = [
        'type' => 'at_shop',
        'title' => 'At Shop Report',
        'params' => [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $request->type,
            'missing' => $request->missing,
            'range_id' => $request->range_id
        ]
    ];
    $this->saveReport($reportData, $html);
    
    return response($html)
        ->header('Content-Type', 'text/html')
        ->header('Content-Disposition', 'inline; filename="atshop_report_' . date('Y-m-d') . '.html"');
}
    private function saveReport($reportData, $html)
    {
        $reportDir = storage_path('app/reports');
        if (!file_exists($reportDir)) {
            mkdir($reportDir, 0777, true);
        }
        
        $fileName = $reportData['type'] . '_report_' . date('Y-m-d_H-i-s') . '.html';
        $filePath = 'reports/' . $fileName;
        $fullPath = $reportDir . '/' . $fileName;
        
        file_put_contents($fullPath, $html);
        
        SavedReport::create([
            'report_type' => $reportData['type'],
            'title' => $reportData['title'],
            'parameters' => json_encode($reportData['params']),
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => strlen($html),
            'generated_by' => auth()->id()
        ]);
    }

    public function savedReports()
    {
        $reports = SavedReport::with('generator')->orderBy('created_at', 'desc')->paginate(20);
        
        $stats = [
            'total' => SavedReport::count(),
            'total_size' => SavedReport::sum('file_size'),
            'by_type' => SavedReport::select('report_type', DB::raw('count(*) as count'))
                ->groupBy('report_type')
                ->get()
        ];
        
        return view('admin.reports.saved', compact('reports', 'stats'));
    }

    public function viewSavedReport($id)
    {
        $report = SavedReport::findOrFail($id);
        $fullPath = storage_path('app/' . $report->file_path);
        if (file_exists($fullPath)) {
            $content = file_get_contents($fullPath);
        } else {
            $content = '<h1>Report file not found</h1>';
        }
        return response($content)->header('Content-Type', 'text/html');
    }

    public function downloadSavedReport($id)
    {
        $report = SavedReport::findOrFail($id);
        $fullPath = storage_path('app/' . $report->file_path);
        
        if (file_exists($fullPath)) {
            return response()->download($fullPath, $report->file_name);
        }
        
        return back()->with('error', 'Report file not found');
    }

    public function deleteSavedReport($id)
    {
        $report = SavedReport::findOrFail($id);
        
        $fullPath = storage_path('app/' . $report->file_path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        
        $report->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Report deleted successfully'
        ]);
    }

    private function getCleanReportCSS()
    {
        return '
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: "Segoe UI", Arial, sans-serif;
                background: #e2e8f0;
                padding: 30px 20px;
                min-height: 100vh;
            }
            .report-container {
                max-width: 1280px;
                margin: 0 auto;
                background: white;
                border-radius: 12px;
                box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }
            .header {
                background: linear-gradient(135deg, #1e3a8a, #1e40af);
                color: white;
                padding: 30px;
                text-align: center;
            }
            .company-info h1 { font-size: 28px; letter-spacing: 1px; margin-bottom: 10px; }
            .company-info p { font-size: 13px; opacity: 0.9; margin: 3px 0; }
            .report-title { margin-top: 20px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.2); }
            .report-title h2 { font-size: 22px; margin-bottom: 8px; }
            .report-title p { font-size: 12px; opacity: 0.85; }
            .search-form { padding: 15px 30px; background: white; border-bottom: 1px solid #e2e8f0; }
            .table-wrapper { flex: 1; overflow-x: auto; }
            table { width: 100%; border-collapse: collapse; }
            th { background: #f1f5f9; padding: 14px 12px; text-align: left; font-size: 13px; font-weight: 600; color: #475569; border-bottom: 2px solid #e2e8f0; }
            td { padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #334155; vertical-align: top; }
            tr:hover { background: #f8fafc; }
            .footer {
                background: #1e293b; color: #94a3b8; padding: 25px 30px 15px;
                font-size: 12px; margin-top: auto;
            }
            .footer-content { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px; margin-bottom: 20px; }
            .footer-left p, .footer-right p { margin: 4px 0; }
            .footer-bottom { text-align: center; padding-top: 15px; border-top: 1px solid #334155; font-size: 11px; }
            .btn-print {
                position: fixed; bottom: 20px; right: 20px;
                background: #1e3a8a; color: white; border: none;
                padding: 12px 24px; border-radius: 50px; cursor: pointer;
                font-size: 14px; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                z-index: 1000;
            }
            .btn-print:hover { background: #1e40af; transform: scale(1.05); }
            @media print {
                body { background: white; padding: 0; margin: 0; }
                .btn-print { display: none; }
                .header, .footer { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                .report-container { box-shadow: none; border-radius: 0; }
                .search-form { display: none; }
            }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .badge {
                display: inline-block; padding: 3px 8px; border-radius: 20px;
                font-size: 11px; font-weight: 500;
            }
            .badge-pending { background: #fef3c7; color: #d97706; }
            .badge-delivered { background: #d1fae5; color: #059669; }
            .badge-partial { background: #fed7aa; color: #ea580c; }
            .badge-in_shop { background: #dbeafe; color: #1e40af; }
            .badge-regular { background: #e0e7ff; color: #3730a3; }
            .badge-extra { background: #fed7aa; color: #9a3412; }
            .badge-missing { background: #fee2e2; color: #dc2626; }
            .description-cell { max-width: 200px; word-wrap: break-word; white-space: normal; }
        </style>';
    }

    private function getCleanInvoiceHeader($title, $period)
    {
        return '
        <div class="header">
            <div class="company-info">
                <h1>🧺 Super Shanghai Dry Cleaners</h1>
                <p>📍 Shora kothi road opposite Christian church Nankana Sahib</p>
                <p>📞 Phone: 03010562865 | 📧 Email: info@super-shanghai.com</p>
                <p>⏰ Mon-Sat: 9:00 AM - 9:00 PM | Sun: Closed</p>
            </div>
            <div class="report-title">
                <h2>' . $title . '</h2>
                <p>Period: ' . $period . '</p>
                <p>Generated: ' . date('d-m-Y H:i:s') . '</p>
            </div>
        </div>';
    }

    private function getCleanInvoiceFooter()
    {
        return '
        <div class="footer">
            <div class="footer-content">
                <div class="footer-left">
                    <p>📋 Generated by: ' . (auth()->user()->name ?? 'System') . '</p>
                    <p>🏢 Super Shanghai Dry Cleaners</p>
                    <p>📅 Report Date: ' . date('d-m-Y') . '</p>
                </div>
                <div class="footer-right">
                    <p>📍 Shora kothi road opposite Christian church</p>
                    <p>📍 Nankana Sahib, Pakistan</p>
                    <p>📞 Phone: 03010562865</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© ' . date('Y') . ' Super Shanghai Dry Cleaners. All rights reserved.</p>
                <p>This is a computer generated report - No signature required</p>
            </div>
        </div>';
    }

    private function generateCleanAtShopReportHTML($shopInvoices, $stats, $ranges, $request, $title)
    {
        $period = ($request->start_date ?? 'All') . ' to ' . ($request->end_date ?? 'All');
        $selectedRangeId = $request->range_id ?? '';
        $selectedType = $request->type ?? 'all';
        $selectedMissing = $request->missing ?? 'all';
        
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . $title . '</title>';
        $html .= $this->getCleanReportCSS();
        $html .= '</head><body>';
        $html .= '<button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>';
        $html .= '<div class="report-container">';
        $html .= $this->getCleanInvoiceHeader($title, $period);
        
        $rangeOptions = '';
        foreach ($ranges as $range) {
            $selected = ($selectedRangeId == $range->id) ? 'selected' : '';
            $rangeOptions .= '<option value="' . $range->id . '" ' . $selected . '>' . $range->range_name . ' (' . $range->range_date . ')</option>';
        }
        
        $html .= '
        <div class="search-form">
            <form method="GET" action="" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <select name="range_id" style="padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; min-width: 180px;">
                    <option value="">All Ranges</option>
                    ' . $rangeOptions . '
                </select>
                <input type="date" name="start_date" value="' . htmlspecialchars($request->start_date ?? '') . '" style="padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px;">
                <input type="date" name="end_date" value="' . htmlspecialchars($request->end_date ?? '') . '" style="padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px;">
                <select name="type" style="padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px;">
                    <option value="all" ' . ($selectedType == 'all' ? 'selected' : '') . '>All Types</option>
                    <option value="regular" ' . ($selectedType == 'regular' ? 'selected' : '') . '>Regular</option>
                    <option value="extra" ' . ($selectedType == 'extra' ? 'selected' : '') . '>Extra</option>
                </select>
                <select name="missing" style="padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px;">
                    <option value="all" ' . ($selectedMissing == 'all' ? 'selected' : '') . '>All Invoices</option>
                    <option value="yes" ' . ($selectedMissing == 'yes' ? 'selected' : '') . '>Missing Only</option>
                </select>
                <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">🔍 Filter</button>
                <a href="?" style="background: #6b7280; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px;">Reset</a>
            </form>
        </div>';
        
        $html .= '<div class="table-wrapper">';
        $html .= '<table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Range Name</th>
                    <th>Range Date</th>
                    <th>Type</th>
                    <th>Missing</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($shopInvoices as $inv) {
            $typeClass = $inv->type == 'regular' ? 'badge-regular' : 'badge-extra';
            $typeText = $inv->type == 'regular' ? 'Regular' : 'Extra';
            $missingBadge = $inv->is_missing ? '<span class="badge badge-missing">⚠️ Missing</span>' : '—';
            $notes = $inv->notes ?? '-';
            if ($notes == '') $notes = '-';
            
            $html .= '<tr>
                <td><strong>' . $inv->invoice_no . '</strong></td>
                <td>' . ($inv->invoiceRange->range_name ?? 'N/A') . '</td>
                <td>' . ($inv->invoiceRange->range_date ?? 'N/A') . '</td>
                <td><span class="badge ' . $typeClass . '">' . $typeText . '</span></td>
                <td>' . $missingBadge . '</td>
                <td class="description-cell">' . htmlspecialchars($notes) . '</td>
            </tr>';
        }
        
        if ($shopInvoices->count() == 0) {
            $html .= '<tr><td colspan="6" style="text-align:center; padding: 40px;">No shop invoices found (All delivered invoices are hidden)</td></tr>';
        }
        
        $html .= '</tbody>
        </table></div>';
        $html .= $this->getCleanInvoiceFooter();
        $html .= '</div></body></html>';
        
        return $html;
    }

    private function generateCleanBookingReportHTML($bookings, $request)
    {
        $period = ($request->start_date ?? 'All') . ' to ' . ($request->end_date ?? 'All');
        $searchTerm = $request->search ?? '';
        $status = $request->status ?? 'all';
        
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Booking Report</title>';
        $html .= $this->getCleanReportCSS();
        $html .= '</head><body>';
        $html .= '<button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>';
        $html .= '<div class="report-container">';
        $html .= $this->getCleanInvoiceHeader('Booking Report', $period);
        
        $html .= '
        <div class="search-form">
            <form method="GET" action="" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="🔍 Search by Invoice #, Customer Name, Mobile..." 
                       value="' . htmlspecialchars($searchTerm) . '" 
                       style="flex: 1; min-width: 250px; padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px;">
                <input type="date" name="start_date" value="' . htmlspecialchars($request->start_date ?? '') . '" style="padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px;">
                <input type="date" name="end_date" value="' . htmlspecialchars($request->end_date ?? '') . '" style="padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px;">
                <select name="status" style="padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px;">
                    <option value="all">All Status</option>
                    <option value="pending" ' . ($status == 'pending' ? 'selected' : '') . '>Pending</option>
                    <option value="partial_delivered" ' . ($status == 'partial_delivered' ? 'selected' : '') . '>Partial Delivered</option>
                    <option value="delivered" ' . ($status == 'delivered' ? 'selected' : '') . '>Delivered</option>
                    <option value="cancelled" ' . ($status == 'cancelled' ? 'selected' : '') . '>Cancelled</option>
                </select>
                <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">🔍 Search</button>
                <a href="?" style="background: #6b7280; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px;">Reset</a>
            </form>
        </div>';
        
        $html .= '<div class="table-wrapper">';
        $html .= '<table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th style="text-align:center">Items</th>
                    <th style="text-align:right">Amount</th>
                    <th style="text-align:right">Paid</th>
                    <th style="text-align:right">Due</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Description / Notes</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($bookings as $b) {
            $due = $b->grand_total - $b->paid_amount;
            $totalItems = $b->items->sum('quantity');
            $statusClass = '';
            if ($b->status == 'pending') $statusClass = 'badge-pending';
            elseif ($b->status == 'delivered') $statusClass = 'badge-delivered';
            elseif ($b->status == 'partial_delivered') $statusClass = 'badge-partial';
            else $statusClass = 'badge-pending';
            
            $notes = $b->customer_notes ?? '-';
            if ($notes == '') $notes = '-';
            
            $html .= '<tr>
                <td><strong>' . $b->invoice_no . '</strong></td>
                <td>' . ($b->customer->name ?? 'N/A') . '</td>
                <td>' . ($b->customer->mobile ?? 'N/A') . '</td>
                <td style="text-align:center">' . $totalItems . '</td>
                <td style="text-align:right">Rs. ' . number_format($b->grand_total, 2) . '</td>
                <td style="text-align:right">Rs. ' . number_format($b->paid_amount, 2) . '</td>
                <td style="text-align:right; ' . ($due > 0 ? 'color:#dc2626;font-weight:bold' : 'color:#10b981') . '">' . ($due > 0 ? 'Rs. ' . number_format($due, 2) : 'Paid') . '</td>
                <td><span class="badge ' . $statusClass . '">' . ucfirst(str_replace('_', ' ', $b->status)) . '</span></td>
                <td>' . $b->booking_date->format('d-m-Y') . '</td>
                <td class="description-cell">' . htmlspecialchars($notes) . '</td>
            </tr>';
        }
        
        if ($bookings->count() == 0) {
            $html .= '<tr><td colspan="10" style="text-align:center; padding: 40px;">No bookings found for the selected period</td></tr>';
        }
        
        $html .= '</tbody>
        </table></div>';
        $html .= $this->getCleanInvoiceFooter();
        $html .= '</div></body></html>';
        
        return $html;
    }

    private function generateCleanUrgentReportHTML($items, $request, $title)
    {
        $period = ($request->start_date ?? 'All') . ' to ' . ($request->end_date ?? 'All');
        
        $groupedItems = [];
        foreach ($items as $item) {
            $bookingId = $item->booking_id;
            if (!isset($groupedItems[$bookingId])) {
                $groupedItems[$bookingId] = [
                    'booking' => $item->booking,
                    'items' => []
                ];
            }
            $groupedItems[$bookingId]['items'][] = $item;
        }
        
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . $title . '</title>';
        $html .= $this->getCleanReportCSS();
        $html .= '</head><body>';
        $html .= '<button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>';
        $html .= '<div class="report-container">';
        $html .= $this->getCleanInvoiceHeader($title, $period);
        
        $html .= '<div class="table-wrapper">';
        $html .= '<table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th>Item</th>
                    <th>Color</th>
                    <th style="text-align:center">Qty</th>
                    <th style="text-align:center">Pending</th>
                    <th>Delivery Date</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($groupedItems as $group) {
            $booking = $group['booking'];
            $itemsList = $group['items'];
            
            foreach ($itemsList as $item) {
                $pending = $item->quantity - ($item->delivered_quantity ?? 0);
                $itemName = $item->clothType ? $item->clothType->name : 'N/A';
                $itemColor = $item->color ? $item->color->name : 'Not Selected';
                $notes = $booking->customer_notes ?? '-';
                if ($notes == '') $notes = '-';
                
                $html .= '<tr>
                    <td><strong>' . ($booking->invoice_no ?? 'N/A') . '</strong></td>
                    <td>' . ($booking->customer->name ?? 'N/A') . '</td>
                    <td>' . ($booking->customer->mobile ?? 'N/A') . '</td>
                    <td>' . $itemName . '</td>
                    <td>' . $itemColor . '</td>
                    <td style="text-align:center">' . $item->quantity . '</td>
                    <td style="text-align:center; color:#dc2626; font-weight:bold">' . $pending . '</td>
                    <td>' . ($item->expected_delivery_date ? $item->expected_delivery_date->format('d-m-Y') : 'N/A') . '</td>
                    <td class="description-cell">' . htmlspecialchars($notes) . '</td>
                </tr>';
            }
        }
        
        if (count($groupedItems) == 0) {
            $html .= '<tr><td colspan="9" style="text-align:center; padding: 40px;">No urgent orders found</td></tr>';
        }
        
        $html .= '</tbody>
        </table></div>';
        $html .= $this->getCleanInvoiceFooter();
        $html .= '</div></body></html>';
        
        return $html;
    }

    private function generateCleanDeliveryReportHTML($deliveries, $request, $title)
    {
        $period = ($request->start_date ?? 'All') . ' to ' . ($request->end_date ?? 'All');
        
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . $title . '</title>';
        $html .= $this->getCleanReportCSS();
        $html .= '</head><body>';
        $html .= '<button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>';
        $html .= '<div class="report-container">';
        $html .= $this->getCleanInvoiceHeader($title, $period);
        
        $html .= '<div class="table-wrapper">';
        $html .= '<table>
            <thead>
                <tr>
                    <th>Delivery ID</th>
                    <th>Invoice #</th>
                    <th>Customer Name</th>
                    <th>Receiver Name</th>
                    <th style="text-align:center">Items</th>
                    <th style="text-align:right">Amount</th>
                    <th>Delivery Date</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($deliveries as $d) {
            $deliveryAmount = $d->items->sum('total');
            $notes = $d->notes ?? '-';
            if ($notes == '') $notes = '-';
            
            $html .= '<tr>
                <td>#' . $d->id . '</td>
                <td><strong>' . ($d->booking->invoice_no ?? 'N/A') . '</strong></td>
                <td>' . ($d->booking->customer->name ?? 'N/A') . '</td>
                <td>' . $d->receiver_name . '</td>
                <td style="text-align:center">' . $d->items->count() . '</td>
                <td style="text-align:right">Rs. ' . number_format($deliveryAmount, 2) . '</td>
                <td>' . $d->delivery_date->format('d-m-Y') . '</td>
                <td class="description-cell">' . htmlspecialchars($notes) . '</td>
            </tr>';
        }
        
        if ($deliveries->count() == 0) {
            $html .= '<tr><td colspan="8" style="text-align:center; padding: 40px;">No deliveries found</td></tr>';
        }
        
        $html .= '</tbody>
        </table></div>';
        $html .= $this->getCleanInvoiceFooter();
        $html .= '</div></body></html>';
        
        return $html;
    }

    private function generateCleanPartialReportHTML($bookings, $request, $title)
    {
        $period = ($request->start_date ?? 'All') . ' to ' . ($request->end_date ?? 'All');
        
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . $title . '</title>';
        $html .= $this->getCleanReportCSS();
        $html .= '</head><body>';
        $html .= '<button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>';
        $html .= '<div class="report-container">';
        $html .= $this->getCleanInvoiceHeader($title, $period);
        
        $html .= '<div class="table-wrapper">';
        $html .= '<table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th style="text-align:center">Total Items</th>
                    <th style="text-align:center">Delivered</th>
                    <th style="text-align:center">Pending</th>
                    <th style="text-align:right">Amount</th>
                    <th style="text-align:right">Paid</th>
                    <th style="text-align:right">Due</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($bookings as $b) {
            $totalItems = $b->items->sum('quantity');
            $deliveredItems = $b->items->sum('delivered_quantity');
            $pendingItems = $totalItems - $deliveredItems;
            $due = $b->grand_total - $b->paid_amount;
            $notes = $b->customer_notes ?? '-';
            if ($notes == '') $notes = '-';
            
            $html .= '<tr>
                <td><strong>' . $b->invoice_no . '</strong></td>
                <td>' . ($b->customer->name ?? 'N/A') . '</td>
                <td>' . ($b->customer->mobile ?? 'N/A') . '</td>
                <td style="text-align:center">' . $totalItems . '</td>
                <td style="text-align:center; color:#10b981">' . $deliveredItems . '</td>
                <td style="text-align:center; color:#dc2626">' . $pendingItems . '</td>
                <td style="text-align:right">Rs. ' . number_format($b->grand_total, 2) . '</td>
                <td style="text-align:right">Rs. ' . number_format($b->paid_amount, 2) . '</td>
                <td style="text-align:right; ' . ($due > 0 ? 'color:#dc2626' : 'color:#10b981') . '">' . ($due > 0 ? 'Rs. ' . number_format($due, 2) : 'Paid') . '</td>
                <td class="description-cell">' . htmlspecialchars($notes) . '</td>
            </tr>';
        }
        
        if ($bookings->count() == 0) {
            $html .= '<tr><td colspan="10" style="text-align:center; padding: 40px;">No partial deliveries found</td></tr>';
        }
        
        $html .= '</tbody>
        </table></div>';
        $html .= $this->getCleanInvoiceFooter();
        $html .= '</div></body></html>';
        
        return $html;
    }

    private function generateCleanShortBookingReportHTML($bookings, $stats, $request, $title)
    {
        $period = ($request->start_date ?? 'All') . ' to ' . ($request->end_date ?? 'All');
        
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . $title . '</title>';
        $html .= $this->getCleanReportCSS();
        $html .= '</head><body>';
        $html .= '<button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>';
        $html .= '<div class="report-container">';
        $html .= $this->getCleanInvoiceHeader($title, $period);
        
        $html .= '<div class="table-wrapper">';
        $html .= '<table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th style="text-align:center">Items</th>
                    <th style="text-align:right">Amount</th>
                    <th style="text-align:right">Paid</th>
                    <th style="text-align:right">Due</th>
                    <th>Date</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($bookings as $b) {
            $totalItems = $b->items->sum('quantity');
            $due = $b->grand_total - $b->paid_amount;
            $notes = $b->customer_notes ?? '-';
            if ($notes == '') $notes = '-';
            
            $html .= '<tr>
                <td><strong>' . $b->invoice_no . '</strong></td>
                <td>' . ($b->customer->name ?? 'N/A') . '</td>
                <td>' . ($b->customer->mobile ?? 'N/A') . '</td>
                <td style="text-align:center"><span class="badge" style="background:#fef3c7;">' . $totalItems . ' items</span></td>
                <td style="text-align:right">Rs. ' . number_format($b->grand_total, 2) . '</td>
                <td style="text-align:right">Rs. ' . number_format($b->paid_amount, 2) . '</td>
                <td style="text-align:right; ' . ($due > 0 ? 'color:#dc2626' : 'color:#10b981') . '">' . ($due > 0 ? 'Rs. ' . number_format($due, 2) : 'Paid') . '</td>
                <td>' . $b->booking_date->format('d-m-Y') . '</td>
                <td class="description-cell">' . htmlspecialchars($notes) . '</td>
            </tr>';
        }
        
        if ($bookings->count() == 0) {
            $html .= '<td><td colspan="9" style="text-align:center; padding: 40px;">No short bookings found (≤ 3 items)</td></tr>';
        }
        
        $html .= '</tbody>
        </table></div>';
        $html .= $this->getCleanInvoiceFooter();
        $html .= '</div></body></html>';
        
        return $html;
    }

    private function generateCleanLongBookingReportHTML($bookings, $stats, $request, $title)
    {
        $period = ($request->start_date ?? 'All') . ' to ' . ($request->end_date ?? 'All');
        
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . $title . '</title>';
        $html .= $this->getCleanReportCSS();
        $html .= '</head><body>';
        $html .= '<button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>';
        $html .= '<div class="report-container">';
        $html .= $this->getCleanInvoiceHeader($title, $period);
        
        $html .= '<div class="table-wrapper">';
        $html .= '</td>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th style="text-align:center">Items</th>
                    <th style="text-align:right">Amount</th>
                    <th style="text-align:right">Paid</th>
                    <th style="text-align:right">Due</th>
                    <th>Date</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($bookings as $b) {
            $totalItems = $b->items->sum('quantity');
            $due = $b->grand_total - $b->paid_amount;
            $notes = $b->customer_notes ?? '-';
            if ($notes == '') $notes = '-';
            
            $html .= '<tr>
                <td><strong>' . $b->invoice_no . '</strong></td>
                <td>' . ($b->customer->name ?? 'N/A') . '</td>
                <td>' . ($b->customer->mobile ?? 'N/A') . '</td>
                <td style="text-align:center; background:#fee2e2; font-weight:bold">' . $totalItems . ' items</span></td>
                <td style="text-align:right">Rs. ' . number_format($b->grand_total, 2) . '</td>
                <td style="text-align:right">Rs. ' . number_format($b->paid_amount, 2) . '</td>
                <td style="text-align:right; ' . ($due > 0 ? 'color:#dc2626' : 'color:#10b981') . '">' . ($due > 0 ? 'Rs. ' . number_format($due, 2) : 'Paid') . '</td>
                <td>' . $b->booking_date->format('d-m-Y') . '</td>
                <td class="description-cell">' . htmlspecialchars($notes) . '</td>
            </tr>';
        }
        
        if ($bookings->count() == 0) {
            $html .= '<tr><td colspan="9" style="text-align:center; padding: 40px;">No long bookings found (≥ 15 items)</td></tr>';
        }
        
        $html .= '</tbody>
        </table></div>';
        $html .= $this->getCleanInvoiceFooter();
        $html .= '</div></body></html>';
        
        return $html;
    }
}