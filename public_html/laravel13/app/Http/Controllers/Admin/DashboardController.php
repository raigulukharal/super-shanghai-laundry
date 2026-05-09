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

class DashboardController extends Controller
{
    public function index()
    {
        // ========== SET TIMEZONE ==========
        $today = Carbon::now('Asia/Karachi')->toDateString();
        
        // ========== STATS CARDS (Using Payments Table) ==========
        $todayBookings = Booking::whereDate('booking_date', $today)->count();
        $todayRevenue = Payment::whereDate('payment_date', $today)->sum('amount');
        $pendingDeliveries = Booking::where('status', 'pending')->count();
        $totalCustomers = Customer::count();
        $totalExpenses = Expense::sum('amount');
        $totalRevenue = Payment::sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;
        $totalBookings = Booking::count();
        
        // Get yesterday's bookings for comparison
        $yesterday = Carbon::now('Asia/Karachi')->subDay()->toDateString();
        $yesterdayBookings = Booking::whereDate('booking_date', $yesterday)->count();
        $bookingPercentageChange = $yesterdayBookings > 0 
            ? round((($todayBookings - $yesterdayBookings) / $yesterdayBookings) * 100) 
            : ($todayBookings > 0 ? 100 : 0);
        
        // ========== REVENUE CHART (Last 12 Months) ==========
        $monthlyRevenue = [];
        $monthlyLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now('Asia/Karachi')->subMonths($i);
            $monthlyLabels[] = $date->format('M Y');
            $monthlyRevenue[] = Payment::whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount');
        }
        
        // ========== DAILY REVENUE CHART (Last 7 Days) ==========
        $dailyRevenue = [];
        $dailyLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now('Asia/Karachi')->subDays($i);
            $dailyLabels[] = $date->format('d M');
            $dailyRevenue[] = Payment::whereDate('payment_date', $date)->sum('amount');
        }
        
        // ========== WEEKLY REVENUE CHART (Last 6 Weeks) ==========
        $weeklyRevenue = [];
        $weeklyLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now('Asia/Karachi')->subWeeks($i)->startOfWeek();
            $end = Carbon::now('Asia/Karachi')->subWeeks($i)->endOfWeek();
            $weeklyLabels[] = 'Week ' . $start->format('d M');
            $weeklyRevenue[] = Payment::whereBetween('payment_date', [$start, $end])->sum('amount');
        }
        
        // ========== YEARLY REVENUE CHART (Last 5 Years) ==========
        $yearlyRevenue = [];
        $yearlyLabels = [];
        for ($i = 4; $i >= 0; $i--) {
            $year = Carbon::now('Asia/Karachi')->subYears($i)->year;
            $yearlyLabels[] = $year;
            $yearlyRevenue[] = Payment::whereYear('payment_date', $year)->sum('amount');
        }
        
        // ========== BOOKING STATUS DISTRIBUTION ==========
        $statusCounts = [
            Booking::where('status', 'pending')->count(),
            Booking::where('status', 'partial_delivered')->count(),
            Booking::where('status', 'delivered')->count(),
            Booking::where('status', 'cancelled')->count()
        ];
        
        // ========== PAYMENT METHOD DISTRIBUTION ==========
        $paymentMethods = Payment::select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get();
        
        // ========== TOP 5 CUSTOMERS ==========
        $topCustomers = Customer::select('customers.id', 'customers.name', DB::raw('SUM(payments.amount) as total_spent'))
            ->join('bookings', 'customers.id', '=', 'bookings.customer_id')
            ->join('payments', 'bookings.id', '=', 'payments.booking_id')
            ->groupBy('customers.id', 'customers.name')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();
        
        // ========== EXPENSE BY CATEGORY ==========
        $expenseByCategory = Expense::select('expense_categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->groupBy('expense_categories.name')
            ->get();
        
        // ========== RECENT BOOKINGS ==========
        $recentBookings = Booking::with('customer')
            ->latest()
            ->limit(10)
            ->get();
        
        // ========== REVENUE VS EXPENSES (Last 6 Months) ==========
        $revenueVsExpense = [];
        $revenueVsExpenseLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now('Asia/Karachi')->subMonths($i);
            $revenueVsExpenseLabels[] = $date->format('M Y');
            $revenueVsExpense[] = [
                'revenue' => Payment::whereYear('payment_date', $date->year)
                    ->whereMonth('payment_date', $date->month)
                    ->sum('amount'),
                'expense' => Expense::whereYear('expense_date', $date->year)
                    ->whereMonth('expense_date', $date->month)
                    ->sum('amount')
            ];
        }
        
        return view('admin.dashboard', compact(
            'todayBookings', 'todayRevenue', 'pendingDeliveries', 'totalCustomers',
            'totalExpenses', 'totalRevenue', 'netProfit', 'totalBookings',
            'monthlyRevenue', 'monthlyLabels', 'dailyRevenue', 'dailyLabels',
            'weeklyRevenue', 'weeklyLabels', 'yearlyRevenue', 'yearlyLabels',
            'statusCounts', 'paymentMethods', 'topCustomers', 'recentBookings',
            'expenseByCategory', 'revenueVsExpense', 'revenueVsExpenseLabels',
            'bookingPercentageChange'
        ));
    }
    
    // API endpoint for real-time data refresh
    public function getChartData()
    {
        return response()->json([
            'dailyRevenue' => $this->getDailyRevenueData(),
            'monthlyRevenue' => $this->getMonthlyRevenueData(),
            'weeklyRevenue' => $this->getWeeklyRevenueData(),
            'yearlyRevenue' => $this->getYearlyRevenueData(),
            'statusCounts' => $this->getStatusCounts(),
            'revenueVsExpense' => $this->getRevenueVsExpenseData()
        ]);
    }
    
    private function getDailyRevenueData()
    {
        $data = ['labels' => [], 'values' => []];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now('Asia/Karachi')->subDays($i);
            $data['labels'][] = $date->format('d M');
            $data['values'][] = Payment::whereDate('payment_date', $date)->sum('amount');
        }
        return $data;
    }
    
    private function getMonthlyRevenueData()
    {
        $data = ['labels' => [], 'values' => []];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now('Asia/Karachi')->subMonths($i);
            $data['labels'][] = $date->format('M Y');
            $data['values'][] = Payment::whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount');
        }
        return $data;
    }
    
    private function getWeeklyRevenueData()
    {
        $data = ['labels' => [], 'values' => []];
        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now('Asia/Karachi')->subWeeks($i)->startOfWeek();
            $end = Carbon::now('Asia/Karachi')->subWeeks($i)->endOfWeek();
            $data['labels'][] = 'Week ' . $start->format('d M');
            $data['values'][] = Payment::whereBetween('payment_date', [$start, $end])->sum('amount');
        }
        return $data;
    }
    
    private function getYearlyRevenueData()
    {
        $data = ['labels' => [], 'values' => []];
        for ($i = 4; $i >= 0; $i--) {
            $year = Carbon::now('Asia/Karachi')->subYears($i)->year;
            $data['labels'][] = $year;
            $data['values'][] = Payment::whereYear('payment_date', $year)->sum('amount');
        }
        return $data;
    }
    
    private function getStatusCounts()
    {
        return [
            Booking::where('status', 'pending')->count(),
            Booking::where('status', 'partial_delivered')->count(),
            Booking::where('status', 'delivered')->count(),
            Booking::where('status', 'cancelled')->count()
        ];
    }
    
    private function getRevenueVsExpenseData()
    {
        $data = ['labels' => [], 'revenue' => [], 'expense' => []];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now('Asia/Karachi')->subMonths($i);
            $data['labels'][] = $date->format('M Y');
            $data['revenue'][] = Payment::whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount');
            $data['expense'][] = Expense::whereYear('expense_date', $date->year)
                ->whereMonth('expense_date', $date->month)
                ->sum('amount');
        }
        return $data;
    }
}