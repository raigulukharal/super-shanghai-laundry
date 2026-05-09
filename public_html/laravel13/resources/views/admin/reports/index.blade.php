@extends('layouts.admin')

@section('title', 'Reports Dashboard')
@section('subtitle', 'Generate and view business reports')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Reports Dashboard</h2>
            <p class="text-gray-500 text-sm mt-1">Generate and manage all business reports</p>
        </div>
        <a href="{{ route('admin.reports.saved') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition flex items-center gap-2">
            <i class="ri-folder-history-line text-xl"></i>
            View Saved Reports
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Total Bookings</p>
                    <p class="text-2xl font-bold">{{ $stats['total_bookings'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="ri-shopping-cart-line text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Total Revenue</p>
                    <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="ri-money-rupee-circle-line text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Urgent Orders</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['urgent_bookings'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="ri-alert-line text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Booking Report Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-5 py-3">
                <h3 class="text-white font-semibold flex items-center gap-2">
                    <i class="ri-file-list-line text-xl"></i>
                    Booking Report
                </h3>
            </div>
            <div class="p-5">
                <p class="text-gray-600 text-sm mb-4">Get complete booking details with date and status filters</p>
                <form action="{{ route('admin.reports.booking') }}" method="GET" target="_blank">
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" name="start_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <input type="date" name="end_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="partial_delivered">Partial Delivered</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm hover:bg-blue-700 transition">
                            <i class="ri-download-line"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delivery Reports Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-5 py-3">
                <h3 class="text-white font-semibold flex items-center gap-2">
                    <i class="ri-truck-line text-xl"></i>
                    Delivery Reports
                </h3>
            </div>
            <div class="p-5 space-y-3">
                <form action="{{ route('admin.reports.full-delivery') }}" method="GET" target="_blank">
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <input type="date" name="start_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <input type="date" name="end_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg text-sm hover:bg-green-700 transition mb-2">
                        <i class="ri-checkbox-circle-line"></i> Full Deliveries
                    </button>
                </form>
                <form action="{{ route('admin.reports.partial-delivery') }}" method="GET" target="_blank">
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <input type="date" name="start_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <input type="date" name="end_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <button type="submit" class="w-full bg-orange-600 text-white py-2 rounded-lg text-sm hover:bg-orange-700 transition">
                        <i class="ri-time-line"></i> Partial Deliveries
                    </button>
                </form>
            </div>
        </div>

        <!-- Special Reports Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition">
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-5 py-3">
                <h3 class="text-white font-semibold flex items-center gap-2">
                    <i class="ri-alert-line text-xl"></i>
                    Special Reports
                </h3>
            </div>
            <div class="p-5 space-y-3">
                <a href="{{ route('admin.reports.urgent') }}" target="_blank" class="block w-full bg-red-100 text-red-700 py-2 rounded-lg text-sm hover:bg-red-200 transition text-center">
                    <i class="ri-flashlight-line"></i> Urgent Orders
                </a>
                <a href="{{ route('admin.reports.non-delivered') }}" target="_blank" class="block w-full bg-yellow-100 text-yellow-700 py-2 rounded-lg text-sm hover:bg-yellow-200 transition text-center">
                    <i class="ri-time-line"></i> Non-Delivered Items
                </a>
            </div>
        </div>

        <!-- Size Based Reports Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition">
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-5 py-3">
                <h3 class="text-white font-semibold flex items-center gap-2">
                    <i class="ri-bar-chart-2-line text-xl"></i>
                    Size Based Reports
                </h3>
            </div>
            <div class="p-5 space-y-3">
                <a href="{{ route('admin.reports.short-booking') }}" target="_blank" class="block w-full bg-indigo-100 text-indigo-700 py-2 rounded-lg text-sm hover:bg-indigo-200 transition text-center">
                    <i class="ri-briefcase-line"></i> Short Bookings (≤ 3 items)
                </a>
                <a href="{{ route('admin.reports.long-booking') }}" target="_blank" class="block w-full bg-purple-100 text-purple-700 py-2 rounded-lg text-sm hover:bg-purple-200 transition text-center">
                    <i class="ri-briefcase-line"></i> Long Bookings (≥ 15 items)
                </a>
            </div>
        </div>

        <!-- At Shop Report Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-5 py-3">
                <h3 class="text-white font-semibold flex items-center gap-2">
                    <i class="ri-store-line text-xl"></i>
                    At Shop Report
                </h3>
            </div>
            <div class="p-5">
                <p class="text-gray-600 text-sm mb-4">Generate shop invoices report (excludes delivered items)</p>
                <form action="{{ route('admin.reports.at-shop') }}" method="GET" target="_blank">
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" name="start_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Start Date">
                            <input type="date" name="end_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="End Date">
                        </div>
                        <select name="range_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">All Ranges</option>
                            @if(isset($ranges))
                                @foreach($ranges as $range)
                                <option value="{{ $range->id }}">{{ $range->range_name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="all">All Types</option>
                                <option value="regular">Regular</option>
                                <option value="extra">Extra</option>
                            </select>
                            <select name="missing" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="all">All Invoices</option>
                                <option value="yes">Missing Only</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-emerald-600 text-white py-2 rounded-lg text-sm hover:bg-emerald-700 transition">
                            <i class="ri-download-line"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Accounts Report Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition">
            <div class="bg-gradient-to-r from-teal-600 to-teal-700 px-5 py-3">
                <h3 class="text-white font-semibold flex items-center gap-2">
                    <i class="ri-bar-chart-line text-xl"></i>
                    Accounts Reports
                </h3>
            </div>
            <div class="p-5 space-y-3">
                <a href="{{ route('admin.accounts.revenue') }}" target="_blank" class="block w-full bg-teal-100 text-teal-700 py-2 rounded-lg text-sm hover:bg-teal-200 transition text-center">
                    <i class="ri-line-chart-line"></i> Revenue Analysis
                </a>
                <a href="{{ route('admin.accounts.due') }}" target="_blank" class="block w-full bg-orange-100 text-orange-700 py-2 rounded-lg text-sm hover:bg-orange-200 transition text-center">
                    <i class="ri-wallet-line"></i> Due Amounts
                </a>
            </div>
        </div>
    </div>

    <!-- Saved Reports Section -->
    @if(isset($savedReports) && $savedReports->count() > 0)
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <i class="ri-history-line text-purple-600"></i>
                Recent Saved Reports
            </h3>
            <a href="{{ route('admin.reports.saved') }}" class="text-purple-600 hover:text-purple-800 text-sm">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Generated</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">By</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($savedReports as $report)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($report->report_type == 'booking') bg-blue-100 text-blue-700
                                @elseif($report->report_type == 'full_delivery') bg-green-100 text-green-700
                                @elseif($report->report_type == 'partial_delivery') bg-orange-100 text-orange-700
                                @elseif($report->report_type == 'urgent') bg-red-100 text-red-700
                                @elseif($report->report_type == 'short_booking') bg-indigo-100 text-indigo-700
                                @elseif($report->report_type == 'long_booking') bg-purple-100 text-purple-700
                                @elseif($report->report_type == 'at_shop') bg-emerald-100 text-emerald-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ str_replace('_', ' ', ucfirst($report->report_type)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $report->title }}</td>
                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($report->created_at)->format('d-m-Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $report->generator->name ?? 'Admin' }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex gap-2 justify-center">
                                <a href="{{ route('admin.reports.view-saved', $report->id) }}" target="_blank" class="text-blue-600 hover:text-blue-800" title="View">
                                    <i class="ri-eye-line text-lg"></i>
                                </a>
                                <a href="{{ route('admin.reports.download-saved', $report->id) }}" class="text-green-600 hover:text-green-800" title="Download">
                                    <i class="ri-download-line text-lg"></i>
                                </a>
                            </div>
                        </div>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Information Box -->
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <i class="ri-information-line text-blue-600 text-xl"></i>
            <div>
                <p class="text-sm text-blue-800 font-semibold">About Reports</p>
                <p class="text-sm text-blue-700 mt-1">
                    • All generated reports are automatically saved in <strong>storage/app/reports/</strong> folder and database.<br>
                    • You can view, download, or delete saved reports from the <strong>Saved Reports</strong> section.<br>
                    • Reports can be printed or saved as PDF using browser's print functionality (Ctrl+P).<br>
                    • <strong>Short Bookings:</strong> Bookings with total quantity ≤ 3 items | <strong>Long Bookings:</strong> Bookings with total quantity ≥ 15 items<br>
                    • <strong>At Shop Report:</strong> Only shows non-delivered invoices (In Shop, Pending, Partial Delivered). Delivered invoices are hidden.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection