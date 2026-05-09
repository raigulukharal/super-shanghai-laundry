<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\ShopController;

/*
|--------------------------------------------------------------------------
| Guest Routes (Login)
|--------------------------------------------------------------------------
*/

Route::get('/', [AdminAuthController::class, 'showLogin'])->name('login');
Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login.get');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Routes (Protected)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/chart-data', [DashboardController::class, 'getChartData'])->name('api.chart-data');

    /*
    |--------------------------------------------------------------------------
    | Booking Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/api/booking/{id}', [BookingController::class, 'getApiData'])->name('api.booking');
    Route::get('/api/cloth-types-by-category', [BookingController::class, 'getClothTypesByCategory'])->name('api.cloth-types-by-category');
    Route::get('/bookings/{id}/download-invoice', [BookingController::class, 'downloadInvoice'])->name('bookings.download-invoice');
    Route::get('/bookings/saved-invoices', [BookingController::class, 'savedInvoices'])->name('bookings.saved-invoices');
    Route::delete('/bookings/delete-invoice/{fileName}', [BookingController::class, 'deleteInvoiceFile'])->name('bookings.delete-invoice');
    Route::post('/bookings/search', [BookingController::class, 'search'])->name('bookings.search');
    Route::delete('/bookings/{id}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{id}/payment', [BookingController::class, 'addPayment'])->name('bookings.addPayment');
    Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');
    Route::post('/bookings/{id}/partial-delivery', [BookingController::class, 'processPartialDelivery'])->name('bookings.partial-delivery');
    
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    Route::get('/bookings/{id}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{id}', [BookingController::class, 'update'])->name('bookings.update');
    
    Route::resource('bookings', BookingController::class);
    
    Route::get('/at-shop', [ShopController::class, 'index'])->name('bookings.at-shop');
    Route::get('/test-partial/{id}', [BookingController::class, 'testPartialDelivery']);
    
    /*
    |--------------------------------------------------------------------------
    | Customer Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::post('/customers/search', [CustomerController::class, 'search'])->name('customers.search.post');
    Route::post('/customers/{id}/add-code', [CustomerController::class, 'addCode'])->name('customers.add-code');
    Route::delete('/customers/{id}/code/{codeId}', [CustomerController::class, 'removeCode'])->name('customers.remove-code');
    Route::get('/api/customer-codes/{id}', [CustomerController::class, 'getCustomerCodes'])->name('api.customer-codes');
    Route::resource('customers', CustomerController::class);

    /*
    |--------------------------------------------------------------------------
    | Delivery Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/deliveries/search', [DeliveryController::class, 'search'])->name('deliveries.search');
    Route::post('/deliveries/search', [DeliveryController::class, 'search'])->name('deliveries.search.post');
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('/deliveries/create/{bookingId?}', [DeliveryController::class, 'create'])->name('deliveries.create');
    Route::get('/deliveries/{id}', [DeliveryController::class, 'show'])->name('deliveries.show');
    Route::post('/deliveries/partial/{bookingId}', [DeliveryController::class, 'partialDelivery'])->name('deliveries.partial');
    Route::post('/deliveries/full/{bookingId}', [DeliveryController::class, 'fullDelivery'])->name('deliveries.full');
    Route::get('/deliveries/non-delivered', [DeliveryController::class, 'nonDeliveredItems'])->name('deliveries.non-delivered');
    Route::get('/api/deliveries/non-delivered-items/{bookingId}', [DeliveryController::class, 'getNonDeliveredItems'])->name('deliveries.api.non-delivered');
    
    /*
    |--------------------------------------------------------------------------
    | Report Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/booking', [ReportController::class, 'bookingReport'])->name('booking');
        Route::get('/delivery-full', [ReportController::class, 'fullDeliveryReport'])->name('full-delivery');
        Route::get('/delivery-partial', [ReportController::class, 'partialDeliveryReport'])->name('partial-delivery');
        Route::get('/urgent', [ReportController::class, 'urgentBookingReport'])->name('urgent');
        Route::get('/non-delivered', [ReportController::class, 'nonDeliveredReport'])->name('non-delivered');
        Route::get('/short-booking', [ReportController::class, 'shortBookingReport'])->name('short-booking');
        Route::get('/long-booking', [ReportController::class, 'longBookingReport'])->name('long-booking');
        Route::get('/at-shop', [ReportController::class, 'atShopReport'])->name('at-shop');
        Route::get('/saved', [ReportController::class, 'savedReports'])->name('saved');
        Route::get('/view-saved/{id}', [ReportController::class, 'viewSavedReport'])->name('view-saved');
        Route::get('/download-saved/{id}', [ReportController::class, 'downloadSavedReport'])->name('download-saved');
        Route::delete('/delete-saved/{id}', [ReportController::class, 'deleteSavedReport'])->name('delete-saved');
    });

    /*
    |--------------------------------------------------------------------------
    | Expense Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('expenses', ExpenseController::class);
    Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show', 'edit', 'create']);

    /*
    |--------------------------------------------------------------------------
    | Account Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/revenue', [AccountController::class, 'revenue'])->name('revenue');
        Route::get('/due', [AccountController::class, 'dueAmounts'])->name('due');
        Route::get('/daily-booking', [AccountController::class, 'dailyBooking'])->name('daily-booking');
        Route::get('/daily-delivery', [AccountController::class, 'dailyDelivery'])->name('daily-delivery');
    });

    /*
    |--------------------------------------------------------------------------
    | Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    /*
    |--------------------------------------------------------------------------
    | Backup Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/create', [BackupController::class, 'createBackup'])->name('create');
        Route::get('/download/{id}', [BackupController::class, 'download'])->name('download');
        Route::post('/restore/{id}', [BackupController::class, 'restore'])->name('restore');
        Route::delete('/delete/{id}', [BackupController::class, 'delete'])->name('delete');
        Route::get('/info/{id}', [BackupController::class, 'info'])->name('info');
    });

    /*
    |--------------------------------------------------------------------------
    | Master Data AJAX
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/cloth-types', [MasterDataController::class, 'getClothTypes'])->name('cloth-types');
        Route::get('/cloth-types-by-category', [MasterDataController::class, 'getClothTypesByCategory'])->name('cloth-types-by-category');
        Route::get('/colors', [MasterDataController::class, 'getColors'])->name('colors');
        Route::get('/categories', [MasterDataController::class, 'getCategories'])->name('categories');
        Route::post('/cloth-types', [MasterDataController::class, 'storeClothType'])->name('cloth-types.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Shop Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('shop')->name('shop.')->group(function () {
        Route::get('/', [ShopController::class, 'index'])->name('index');
        Route::post('/generate-range', [ShopController::class, 'generateRange'])->name('generate-range');
        Route::get('/available-invoices', [ShopController::class, 'getAvailableInvoices'])->name('available-invoices');
        Route::delete('/range/{id}', [ShopController::class, 'deleteRange'])->name('range.delete');
        Route::get('/range/{id}', [ShopController::class, 'getRangeDetails'])->name('range-details');
        Route::post('/add-extra', [ShopController::class, 'addExtraInvoice'])->name('add-extra');
        Route::delete('/invoice/{id}', [ShopController::class, 'removeInvoice'])->name('remove-invoice');
        Route::post('/invoice/{id}/missing', [ShopController::class, 'markMissing'])->name('mark-missing');
        Route::put('/invoice/{id}/status', [ShopController::class, 'updateInvoiceStatus'])->name('update-status');
        Route::get('/search', [ShopController::class, 'searchInvoices'])->name('search');
        Route::get('/booking/{invoiceNo}', [ShopController::class, 'getBookingDetails'])->name('booking-details');
        Route::post('/undeliver/{bookingId}', [ShopController::class, 'undeliverInvoice'])->name('undeliver');
    });
});

/*
|--------------------------------------------------------------------------
| Debug Routes
|--------------------------------------------------------------------------
*/

Route::get('/debug-partial/{id}', function($id) {
    return response()->json([
        'message' => 'Route is working',
        'booking_id' => $id,
        'url' => url('/admin/bookings/' . $id . '/partial-delivery')
    ]);
});

Route::get('/debug', function () {
    return [
        'status' => 'Laravel is working',
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'time' => now()->toDateTimeString()
    ];
});