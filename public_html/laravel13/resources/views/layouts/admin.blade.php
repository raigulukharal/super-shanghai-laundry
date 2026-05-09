<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SSDC Laundry - @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; overflow-x: hidden; }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            overflow-y: auto;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-track { background: #334155; }
        .sidebar::-webkit-scrollbar-thumb { background: #64748b; border-radius: 5px; }
        
        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            margin: 4px 12px;
            border-radius: 12px;
            color: #cbd5e1;
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .sidebar-item i { font-size: 20px; width: 24px; }
        
        .sidebar-item:hover {
            background: rgba(59, 130, 246, 0.2);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar-item.active {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }
        
        .main-content { margin-left: 280px; min-height: 100vh; background: #f3f4f6; }
        
        .top-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        
        @media (max-width: 1024px) {
            .sidebar { width: 240px; }
            .main-content { margin-left: 240px; }
        }
        
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; z-index: 1000; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-menu-btn {
                display: block;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1001;
                background: #2563eb;
                color: white;
                border: none;
                border-radius: 10px;
                padding: 10px;
                cursor: pointer;
            }
        }
        
        @media (min-width: 769px) { .mobile-menu-btn { display: none; } }
    </style>
</head>
<body>

<button class="mobile-menu-btn" onclick="toggleSidebar()">
    <i class="ri-menu-line text-2xl"></i>
</button>

<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-[999] hidden" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<div class="sidebar">
    <div class="p-6 border-b border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                <i class="ri-laundry-line text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold">SSDC Laundry</h1>
                <p class="text-xs text-gray-400">Management System</p>
            </div>
        </div>
    </div>
    
    <div class="p-5 border-b border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center">
                <i class="ri-user-line text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email ?? 'admin@ssdc' }}</p>
            </div>
        </div>
    </div>

    <nav class="py-4">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="ri-dashboard-line"></i> <span>Dashboard</span>
        </a>
        
        <a href="{{ route('admin.bookings.index') }}" class="sidebar-item {{ request()->routeIs('admin.bookings.index') ? 'active' : '' }}">
            <i class="ri-shopping-cart-line"></i> <span>All Bookings</span>
        </a>
        
        <a href="{{ route('admin.bookings.create') }}" class="sidebar-item {{ request()->routeIs('admin.bookings.create') ? 'active' : '' }}">
            <i class="ri-add-circle-line"></i> <span>New Booking (POS)</span>
        </a>
        
        <!-- At Shop Link - Now points to Shop Management -->
        <a href="{{ route('admin.shop.index') }}" class="sidebar-item {{ request()->routeIs('admin.shop.*') ? 'active' : '' }}">
            <i class="ri-store-line"></i> <span>At Shop</span>
        </a>
        
        <a href="{{ route('admin.bookings.saved-invoices') }}" class="sidebar-item">
            <i class="ri-file-pdf-line"></i> <span>Saved Invoices</span>
        </a>
        
        <a href="{{ route('admin.customers.index') }}" class="sidebar-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
            <i class="ri-user-line"></i> <span>Customers</span>
        </a>
        
        <a href="{{ route('admin.deliveries.index') }}" class="sidebar-item {{ request()->routeIs('admin.deliveries.*') ? 'active' : '' }}">
            <i class="ri-truck-line"></i> <span>Deliveries</span>
        </a>
        
        <a href="{{ route('admin.reports.index') }}" class="sidebar-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <i class="ri-file-chart-line"></i> <span>Reports</span>
        </a>
        
        <a href="{{ route('admin.reports.saved') }}" class="sidebar-item">
            <i class="ri-save-line"></i> <span>Saved Reports</span>
        </a>
        
        <a href="{{ route('admin.expenses.index') }}" class="sidebar-item {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
            <i class="ri-wallet-line"></i> <span>Expenses</span>
        </a>
        
        <a href="{{ route('admin.accounts.revenue') }}" class="sidebar-item">
            <i class="ri-bar-chart-2-line"></i> <span>Revenue Report</span>
        </a>

        <!-- Backup Manager Link -->
        <a href="{{ route('admin.backup.index') }}" class="sidebar-item {{ request()->routeIs('admin.backup.*') ? 'active' : '' }}">
            <i class="ri-database-2-line"></i> <span>Backup Manager</span>
        </a>

        <div class="border-t border-gray-700 my-3 mx-5"></div>
        
        <a href="{{ route('admin.profile.edit') }}" class="sidebar-item">
            <i class="ri-settings-4-line"></i> <span>Profile Settings</span>
        </a>
        
        <form method="POST" action="{{ route('logout') }}" id="logoutForm">
            @csrf
            <button type="submit" class="sidebar-item w-full text-left">
                <i class="ri-logout-box-line"></i> <span>Logout</span>
            </button>
        </form>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="top-header">
        <div class="px-8 py-5">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">@yield('title', 'Dashboard')</h2>
                    <p class="text-gray-500 text-sm mt-1">@yield('subtitle', 'Welcome back!')</p>
                </div>
            </div>
        </div>
    </div>

    <div class="p-8">
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif
        
        @yield('content')
    </div>
</div>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('hidden');
    }
    
    function closeSidebar() {
        document.querySelector('.sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.add('hidden');
    }
</script>

@stack('scripts')
</body>
</html>