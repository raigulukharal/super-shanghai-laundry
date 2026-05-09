@extends('layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', 'Welcome back! Here\'s your business at a glance.')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase">Today's Bookings</p>
                    <p class="text-2xl font-bold">{{ $todayBookings }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="ri-shopping-cart-line text-blue-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase">Today's Revenue</p>
                    <p class="text-2xl font-bold">Rs. {{ number_format($todayRevenue, 2) }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="ri-money-rupee-circle-line text-green-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase">Pending Deliveries</p>
                    <p class="text-2xl font-bold">{{ $pendingDeliveries }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="ri-truck-line text-yellow-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase">Total Customers</p>
                    <p class="text-2xl font-bold">{{ $totalCustomers }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="ri-user-line text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-gray-500 text-xs">Total Revenue</p>
            <p class="text-xl font-bold text-green-600">Rs. {{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-gray-500 text-xs">Total Expenses</p>
            <p class="text-xl font-bold text-red-600">Rs. {{ number_format($totalExpenses, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-gray-500 text-xs">Net Profit</p>
            <p class="text-xl font-bold text-blue-600">Rs. {{ number_format($netProfit, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-gray-500 text-xs">Total Bookings</p>
            <p class="text-xl font-bold">{{ $totalBookings }}</p>
        </div>
    </div>

    <!-- Charts Row 1 - Revenue Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Revenue Chart -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Daily Revenue (Last 7 Days)</h3>
                <button onclick="refreshChart('daily')" class="text-blue-600 text-sm">Refresh</button>
            </div>
            <canvas id="dailyRevenueChart" height="250"></canvas>
        </div>
        
        <!-- Weekly Revenue Chart -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-lg font-semibold mb-4">Weekly Revenue (Last 6 Weeks)</h3>
            <canvas id="weeklyRevenueChart" height="250"></canvas>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Revenue Chart -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-lg font-semibold mb-4">Monthly Revenue (Last 12 Months)</h3>
            <canvas id="monthlyRevenueChart" height="250"></canvas>
        </div>
        
        <!-- Yearly Revenue Chart -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-lg font-semibold mb-4">Yearly Revenue (Last 5 Years)</h3>
            <canvas id="yearlyRevenueChart" height="250"></canvas>
        </div>
    </div>

    <!-- Charts Row 3 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue vs Expenses Chart -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-lg font-semibold mb-4">Revenue vs Expenses (Last 6 Months)</h3>
            <canvas id="revenueVsExpenseChart" height="250"></canvas>
        </div>
        
        <!-- Booking Status Distribution -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-lg font-semibold mb-4">Booking Status Distribution</h3>
            <canvas id="statusChart" height="250"></canvas>
        </div>
    </div>

    <!-- Charts Row 4 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payment Methods -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-lg font-semibold mb-4">Payment Methods Distribution</h3>
            <canvas id="paymentMethodChart" height="250"></canvas>
        </div>
        
        <!-- Expense by Category -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-lg font-semibold mb-4">Expense by Category</h3>
            <canvas id="expenseCategoryChart" height="250"></canvas>
        </div>
    </div>

    <!-- Top Customers Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">Top 5 Customers</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Customer Name</th>
                        <th class="px-4 py-3 text-right">Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topCustomers as $index => $customer)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">{{ $customer->name }}</td>
                        <td class="px-4 py-3 text-right font-semibold">Rs. {{ number_format($customer->total_spent, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Bookings Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">Recent Bookings</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">Invoice</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentBookings as $booking)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $booking->invoice_no }}</td>
                        <td class="px-4 py-3">{{ $booking->customer->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">Rs. {{ number_format($booking->grand_total, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($booking->status == 'delivered') bg-green-100 text-green-800
                                @elseif($booking->status == 'cancelled') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $booking->booking_date->format('d-m-Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// All chart instances
let dailyChart, weeklyChart, monthlyChart, yearlyChart, revenueVsExpenseChart, statusChart, paymentMethodChart, expenseCategoryChart;

// Data from PHP
const dailyLabels = {!! json_encode($dailyLabels) !!};
const dailyData = {!! json_encode($dailyRevenue) !!};
const weeklyLabels = {!! json_encode($weeklyLabels) !!};
const weeklyData = {!! json_encode($weeklyRevenue) !!};
const monthlyLabels = {!! json_encode($monthlyLabels) !!};
const monthlyData = {!! json_encode($monthlyRevenue) !!};
const yearlyLabels = {!! json_encode($yearlyLabels) !!};
const yearlyData = {!! json_encode($yearlyRevenue) !!};
const revenueVsExpenseLabels = {!! json_encode($revenueVsExpenseLabels) !!};
const revenueData = {!! json_encode(array_column($revenueVsExpense, 'revenue')) !!};
const expenseData = {!! json_encode(array_column($revenueVsExpense, 'expense')) !!};
const statusLabels = ['Pending', 'Partial Delivered', 'Delivered', 'Cancelled'];
const statusData = {!! json_encode($statusCounts) !!};
const paymentMethodLabels = {!! json_encode($paymentMethods->pluck('payment_method')) !!};
const paymentMethodData = {!! json_encode($paymentMethods->pluck('total')) !!};
const expenseCategoryLabels = {!! json_encode($expenseByCategory->pluck('name')) !!};
const expenseCategoryData = {!! json_encode($expenseByCategory->pluck('total')) !!};

// Initialize all charts
function initCharts() {
    // Daily Revenue Chart
    dailyChart = new Chart(document.getElementById('dailyRevenueChart'), {
        type: 'line', data: { labels: dailyLabels, datasets: [{ label: 'Revenue (Rs.)', data: dailyData, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', tension: 0.4, fill: true }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rs. ' + v } } } }
    });
    
    // Weekly Revenue Chart
    weeklyChart = new Chart(document.getElementById('weeklyRevenueChart'), {
        type: 'bar', data: { labels: weeklyLabels, datasets: [{ label: 'Revenue (Rs.)', data: weeklyData, backgroundColor: '#10b981' }] },
        options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rs. ' + v } } } }
    });
    
    // Monthly Revenue Chart
    monthlyChart = new Chart(document.getElementById('monthlyRevenueChart'), {
        type: 'line', data: { labels: monthlyLabels, datasets: [{ label: 'Revenue (Rs.)', data: monthlyData, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.1)', tension: 0.4, fill: true }] },
        options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rs. ' + v } } } }
    });
    
    // Yearly Revenue Chart
    yearlyChart = new Chart(document.getElementById('yearlyRevenueChart'), {
        type: 'bar', data: { labels: yearlyLabels, datasets: [{ label: 'Revenue (Rs.)', data: yearlyData, backgroundColor: '#ef4444' }] },
        options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rs. ' + v } } } }
    });
    
    // Revenue vs Expense Chart
    revenueVsExpenseChart = new Chart(document.getElementById('revenueVsExpenseChart'), {
        type: 'bar', data: { labels: revenueVsExpenseLabels, datasets: [{ label: 'Revenue', data: revenueData, backgroundColor: '#10b981' }, { label: 'Expenses', data: expenseData, backgroundColor: '#ef4444' }] },
        options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rs. ' + v } } } }
    });
    
    // Status Chart
    statusChart = new Chart(document.getElementById('statusChart'), {
        type: 'doughnut', data: { labels: statusLabels, datasets: [{ data: statusData, backgroundColor: ['#3b82f6', '#eab308', '#10b981', '#ef4444'] }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
    });
    
    // Payment Method Chart
    paymentMethodChart = new Chart(document.getElementById('paymentMethodChart'), {
        type: 'pie', data: { labels: paymentMethodLabels, datasets: [{ data: paymentMethodData, backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444'] }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
    });
    
    // Expense Category Chart
    expenseCategoryChart = new Chart(document.getElementById('expenseCategoryChart'), {
        type: 'bar', data: { labels: expenseCategoryLabels, datasets: [{ label: 'Expense (Rs.)', data: expenseCategoryData, backgroundColor: '#f97316' }] },
        options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rs. ' + v } } } }
    });
}

// Refresh chart with real-time data
function refreshChart(type) {
    fetch('/admin/api/chart-data')
        .then(response => response.json())
        .then(data => {
            if (type === 'daily') {
                dailyChart.data.labels = data.dailyRevenue.labels;
                dailyChart.data.datasets[0].data = data.dailyRevenue.values;
                dailyChart.update();
            }
        });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initCharts);
</script>
@endsection