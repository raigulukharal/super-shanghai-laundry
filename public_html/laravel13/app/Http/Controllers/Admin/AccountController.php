<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountController extends Controller
{
    /**
     * Revenue Overview with Charts
     */
    public function revenue(Request $request)
    {
        $period = $request->period ?? 'monthly';
        
        // Get revenue data based on period
        $revenueData = $this->getRevenueData($period, $request);
        
        // Summary statistics - Using Payments Table
        $totalRevenue = Payment::sum('amount');
        $totalExpenses = Expense::sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;
        $pendingDue = Booking::sum(DB::raw('grand_total - paid_amount'));
        
        $summary = [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'pending_due' => $pendingDue,
            'total_bookings' => Booking::count(),
            'total_deliveries' => Delivery::count(),
            'avg_booking_value' => Booking::count() > 0 ? Booking::sum('grand_total') / Booking::count() : 0
        ];
        
        // Payment method breakdown
        $paymentMethods = Payment::select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get();
        
        // Top 5 customers by spending (using payments table)
        $topCustomers = Customer::select('customers.id', 'customers.name', DB::raw('SUM(payments.amount) as total_spent'))
            ->join('bookings', 'customers.id', '=', 'bookings.customer_id')
            ->join('payments', 'bookings.id', '=', 'payments.booking_id')
            ->groupBy('customers.id', 'customers.name')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();
        
        return view('admin.accounts.revenue', compact('revenueData', 'summary', 'period', 'paymentMethods', 'topCustomers'));
    }
    
    /**
     * Get revenue data based on period
     */
    private function getRevenueData($period, $request)
    {
        switch ($period) {
            case 'daily':
                return $this->getDailyRevenue($request);
            case 'weekly':
                return $this->getWeeklyRevenue($request);
            case 'yearly':
                return $this->getYearlyRevenue($request);
            case 'custom':
                return $this->getCustomRevenue($request);
            default:
                return $this->getMonthlyRevenue($request);
        }
    }
    
    /**
     * Daily Revenue (Last 30 days)
     */
    private function getDailyRevenue($request)
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now('Asia/Karachi')->subDays($i);
            $data[] = [
                'label' => $date->format('d M'),
                'revenue' => Payment::whereDate('payment_date', $date)->sum('amount'),
                'expense' => Expense::whereDate('expense_date', $date)->sum('amount'),
                'bookings' => Booking::whereDate('booking_date', $date)->count(),
                'deliveries' => Delivery::whereDate('delivery_date', $date)->count()
            ];
        }
        return $data;
    }
    
    /**
     * Weekly Revenue (Last 12 weeks)
     */
    private function getWeeklyRevenue($request)
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = Carbon::now('Asia/Karachi')->subWeeks($i)->startOfWeek();
            $end = Carbon::now('Asia/Karachi')->subWeeks($i)->endOfWeek();
            $data[] = [
                'label' => 'Week ' . $start->format('d M'),
                'revenue' => Payment::whereBetween('payment_date', [$start, $end])->sum('amount'),
                'expense' => Expense::whereBetween('expense_date', [$start, $end])->sum('amount'),
                'bookings' => Booking::whereBetween('booking_date', [$start, $end])->count(),
                'deliveries' => Delivery::whereBetween('delivery_date', [$start, $end])->count()
            ];
        }
        return $data;
    }
    
    /**
     * Monthly Revenue (Last 12 months)
     */
    private function getMonthlyRevenue($request)
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now('Asia/Karachi')->subMonths($i);
            $data[] = [
                'label' => $date->format('M Y'),
                'revenue' => Payment::whereYear('payment_date', $date->year)
                    ->whereMonth('payment_date', $date->month)
                    ->sum('amount'),
                'expense' => Expense::whereYear('expense_date', $date->year)
                    ->whereMonth('expense_date', $date->month)
                    ->sum('amount'),
                'bookings' => Booking::whereYear('booking_date', $date->year)
                    ->whereMonth('booking_date', $date->month)
                    ->count(),
                'deliveries' => Delivery::whereYear('delivery_date', $date->year)
                    ->whereMonth('delivery_date', $date->month)
                    ->count()
            ];
        }
        return $data;
    }
    
    /**
     * Yearly Revenue (Last 5 years)
     */
    private function getYearlyRevenue($request)
    {
        $data = [];
        for ($i = 4; $i >= 0; $i--) {
            $year = Carbon::now('Asia/Karachi')->subYears($i)->year;
            $data[] = [
                'label' => $year,
                'revenue' => Payment::whereYear('payment_date', $year)->sum('amount'),
                'expense' => Expense::whereYear('expense_date', $year)->sum('amount'),
                'bookings' => Booking::whereYear('booking_date', $year)->count(),
                'deliveries' => Delivery::whereYear('delivery_date', $year)->count()
            ];
        }
        return $data;
    }
    
    /**
     * Custom Range Revenue
     */
    private function getCustomRevenue($request)
    {
        $startDate = $request->start_date ?? Carbon::now('Asia/Karachi')->subDays(30);
        $endDate = $request->end_date ?? Carbon::now('Asia/Karachi');
        
        $data = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current <= $end) {
            $data[] = [
                'label' => $current->format('d M Y'),
                'revenue' => Payment::whereDate('payment_date', $current)->sum('amount'),
                'expense' => Expense::whereDate('expense_date', $current)->sum('amount'),
                'bookings' => Booking::whereDate('booking_date', $current)->count(),
                'deliveries' => Delivery::whereDate('delivery_date', $current)->count()
            ];
            $current->addDay();
        }
        
        return $data;
    }
    
    /**
     * Due Amounts Report
     */
    public function dueAmounts()
    {
        $dueBookings = Booking::with('customer')
            ->whereRaw('grand_total > paid_amount')
            ->where('status', '!=', 'cancelled')
            ->get();
        
        $totalDue = $dueBookings->sum(function($b) {
            return $b->grand_total - $b->paid_amount;
        });
        
        $totalBookings = Booking::count();
        $totalAmount = Booking::sum('grand_total');
        $totalPaid = Booking::sum('paid_amount');
        
        $duePercentage = $totalAmount > 0 ? ($totalDue / $totalAmount) * 100 : 0;
        
        return view('admin.accounts.due', compact('dueBookings', 'totalDue', 'totalBookings', 'totalAmount', 'totalPaid', 'duePercentage'));
    }
    
    /**
     * Daily Booking Summary
     */
    public function dailyBooking(Request $request)
    {
        $date = $request->date ?? Carbon::now('Asia/Karachi')->toDateString();
        
        $bookings = Booking::with('customer')
            ->whereDate('booking_date', $date)
            ->get();
        
        $summary = [
            'total' => $bookings->count(),
            'amount' => $bookings->sum('grand_total'),
            'paid' => $bookings->sum('paid_amount'),
            'due' => $bookings->sum('grand_total') - $bookings->sum('paid_amount'),
            'cash' => Payment::whereDate('payment_date', $date)->where('payment_method', 'cash')->sum('amount'),
            'card' => Payment::whereDate('payment_date', $date)->where('payment_method', 'card')->sum('amount'),
            'online' => Payment::whereDate('payment_date', $date)->where('payment_method', 'online')->sum('amount')
        ];
        
        return view('admin.accounts.daily-booking', compact('bookings', 'summary', 'date'));
    }
    
    /**
     * Daily Delivery Summary
     */
    public function dailyDelivery(Request $request)
    {
        $date = $request->date ?? Carbon::now('Asia/Karachi')->toDateString();
        
        $deliveries = Delivery::with('booking.customer', 'items')
            ->whereDate('delivery_date', $date)
            ->get();
        
        $summary = [
            'total' => $deliveries->count(),
            'items' => $deliveries->sum(function($d) { return $d->items->count(); }),
            'amount' => $deliveries->sum(function($d) { return $d->items->sum('total'); })
        ];
        
        return view('admin.accounts.daily-delivery', compact('deliveries', 'summary', 'date'));
    }
    
    /**
     * Cash Collection Report
     */
    public function cashCollection(Request $request)
    {
        $startDate = $request->start_date ?? Carbon::now('Asia/Karachi')->startOfMonth();
        $endDate = $request->end_date ?? Carbon::now('Asia/Karachi');
        
        $cashPayments = Payment::where('payment_method', 'cash')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->with('booking.customer')
            ->get();
        
        $totalCash = $cashPayments->sum('amount');
        
        $dailyCash = Payment::where('payment_method', 'cash')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->select(DB::raw('DATE(payment_date) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
        
        return view('admin.accounts.cash-collection', compact('cashPayments', 'totalCash', 'dailyCash', 'startDate', 'endDate'));
    }
}