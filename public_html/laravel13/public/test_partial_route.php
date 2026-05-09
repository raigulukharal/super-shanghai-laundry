<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use App\Models\Booking;
use App\Models\BookingItem;

echo "<h1>Partial Delivery Debug Tool</h1>";

// Get a booking with pending items
$booking = Booking::where('status', 'pending')->first();

if ($booking) {
    echo "<h2>Testing Booking ID: {$booking->id} - Invoice: {$booking->invoice_no}</h2>";
    
    // Check booking items
    $items = BookingItem::where('booking_id', $booking->id)
        ->whereRaw('delivered_quantity < quantity OR delivered_quantity IS NULL')
        ->get();
    
    echo "<h3>Pending Items Count: " . $items->count() . "</h3>";
    
    foreach ($items as $item) {
        $remaining = $item->quantity - ($item->delivered_quantity ?? 0);
        echo "<div style='border:1px solid #ccc; padding:10px; margin:5px'>";
        echo "<strong>Item ID:</strong> {$item->id}<br>";
        echo "<strong>Cloth Type:</strong> " . ($item->clothType->name ?? 'N/A') . "<br>";
        echo "<strong>Quantity:</strong> {$item->quantity}<br>";
        echo "<strong>Delivered:</strong> " . ($item->delivered_quantity ?? 0) . "<br>";
        echo "<strong>Remaining:</strong> {$remaining}<br>";
        echo "</div>";
    }
    
    // Test the partial delivery API
    echo "<h3>Testing Partial Delivery API:</h3>";
    
    $testData = [
        'items' => [
            [
                'booking_item_id' => $items->first()->id,
                'delivered_quantity' => 1
            ]
        ],
        'payment_amount' => 0,
        'payment_method' => 'cash',
        'receiver_name' => 'Test Receiver',
        'receiver_mobile' => '03001234567',
        'notes' => 'Test delivery',
        '_token' => csrf_token()
    ];
    
    echo "<pre>";
    echo "Request URL: /admin/bookings/{$booking->id}/partial-delivery\n";
    echo "Request Data: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n";
    echo "</pre>";
    
    // Make actual request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, url('/admin/bookings/' . $booking->id . '/partial-delivery'));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-CSRF-TOKEN: ' . csrf_token()
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<h3>API Response:</h3>";
    echo "<p>HTTP Status: {$httpCode}</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
} else {
    echo "<p style='color:red'>No pending bookings found!</p>";
}

// Check if route exists
echo "<h3>Checking Routes:</h3>";
$routes = app('router')->getRoutes();
$found = false;
foreach ($routes as $route) {
    if (strpos($route->uri(), 'partial-delivery') !== false) {
        $found = true;
        echo "<p style='color:green'>✅ Route found: " . $route->uri() . " - Methods: " . implode(',', $route->methods()) . "</p>";
    }
}
if (!$found) {
    echo "<p style='color:red'>❌ partial-delivery route not found!</p>";
}