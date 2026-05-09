@extends('layouts.admin')

@section('title', 'Revenue Analysis')
@section('subtitle', 'Complete revenue and expense analysis')

@section('content')
<div class="space-y-6">
    <!-- Period Selector -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex flex-wrap gap-2">
            <a href="?period=daily" class="px-4 py-2 rounded-lg {{ $period == 'daily' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">Daily (30 days)</a>
            <a href="?period=weekly" class="px-4 py-2 rounded-lg {{ $period == 'weekly' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">Weekly (12 weeks)</a>
            <a href="?period=monthly" class="px-4 py-2 rounded-lg {{ $period == 'monthly' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">Monthly (12 months)</a>
            <a href="?period=yearly" class="px-4 py-2 rounded-lg {{ $period == 'yearly' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">Yearly (5 years)</a>
            <a href="?period=custom" class="px-4 py-2 rounded-lg {{ $period == 'custom' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">Custom Range</a>
        </div>
        
        @if($period == 'custom')
        <form method="GET" class="mt-4 flex gap-2">
            <input type="hidden" name="period" value="custom">
            <input type="date" name="start_date" class="border rounded-lg px-3 py-2" required>
            <input type="date" name="end_date" class="border rounded-lg px-3 py-2" required>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Apply</button>
        </form>
        @endif
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-gray-500 text-xs">Total Revenue</p>
            <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($summary['total_revenue'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-500">
            <p class="text-gray-500 text-xs">Total Expenses</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($summary['total_expenses'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-gray-500 text-xs">Net Profit</p>
            <p class="text-2xl font-bold text-blue-600">Rs. {{ number_format($summary['net_profit'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-gray-500 text-xs">Pending Due</p>
            <p class="text-2xl font-bold text-yellow-600">Rs. {{ number_format($summary['pending_due'], 2) }}</p>
        </div>
    </div>

    <!-- Revenue vs Expenses Chart -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Revenue vs Expenses Analysis</h3>
        <canvas id="revenueChart" height="300"></canvas>
    </div>

    <!-- Payment Methods -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">Payment Methods Breakdown</h3>
            <canvas id="paymentChart" height="250"></canvas>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">Top 5 Customers</h3>
            <div class="space-y-3">
                @foreach($topCustomers as $customer)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="font-medium">{{ $customer->customer->name ?? 'N/A' }}</span>
                    <span class="text-green-600 font-bold">Rs. {{ number_format($customer->total_spent, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="text-lg font-semibold">Detailed Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Period</th>
                        <th class="px-4 py-3 text-right">Revenue (Rs.)</th>
                        <th class="px-4 py-3 text-right">Expenses (Rs.)</th>
                        <th class="px-4 py-3 text-right">Profit (Rs.)</th>
                        <th class="px-4 py-3 text-right">Margin (%)</th>
                        <th class="px-4 py-3 text-right">Bookings</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($revenueData as $data)
                    <tr class="border-b">
                        <td class="px-4 py-3 font-medium">{{ $data['label'] }}</td>
                        <td class="px-4 py-3 text-right text-green-600">Rs. {{ number_format($data['revenue'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-red-600">Rs. {{ number_format($data['expense'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-blue-600">Rs. {{ number_format($data['revenue'] - $data['expense'], 2) }}</td>
                        <td class="px-4 py-3 text-right">{{ $data['revenue'] > 0 ? round(($data['revenue'] - $data['expense']) / $data['revenue'] * 100, 1) : 0 }}%</td>
                        <td class="px-4 py-3 text-right">{{ $data['bookings'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td class="px-4 py-3 text-right">Total:</td>
                        <td class="px-4 py-3 text-right">Rs. {{ number_format(collect($revenueData)->sum('revenue'), 2) }}</td>
                        <td class="px-4 py-3 text-right">Rs. {{ number_format(collect($revenueData)->sum('expense'), 2) }}</td>
                        <td class="px-4 py-3 text-right">Rs. {{ number_format(collect($revenueData)->sum('revenue') - collect($revenueData)->sum('expense'), 2) }}</td>
                        <td class="px-4 py-3 text-right"></td>
                        <td class="px-4 py-3 text-right"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($revenueData, 'label')) !!},
        datasets: [
            {
                label: 'Revenue',
                data: {!! json_encode(array_column($revenueData, 'revenue')) !!},
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Expenses',
                data: {!! json_encode(array_column($revenueData, 'expense')) !!},
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rs. ' + value;
                    }
                }
            }
        }
    }
});

// Payment Methods Chart
const ctx2 = document.getElementById('paymentChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($paymentMethods->pluck('payment_method')) !!},
        datasets: [{
            data: {!! json_encode($paymentMethods->pluck('total')) !!},
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endsection