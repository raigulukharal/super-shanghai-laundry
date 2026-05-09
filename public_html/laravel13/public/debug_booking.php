<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Booking;
use App\Models\BookingItem;

echo "<h1>Debug Booking Items</h1>";

// Get a booking with status pending
$booking = Booking::where('status', 'pending')->first();

if ($booking) {
    echo "<h2>Booking ID: {$booking->id} - Invoice: {$booking->invoice_no}</h2>";
    
    $items = BookingItem::where('booking_id', $booking->id)->get();
    
    echo "<h3>Items:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Cloth Type ID</th><th>Quantity</th><th>Delivered Quantity</th><th>Remaining</th></tr>";
    
    foreach ($items as $item) {
        $remaining = $item->quantity - ($item->delivered_quantity ?? 0);
        echo "<tr>";
        echo "<td>{$item->id}</td>";
        echo "<td>{$item->cloth_type_id}</td>";
        echo "<td>{$item->quantity}</td>";
        echo "<td>" . ($item->delivered_quantity ?? 0) . "</td>";
        echo "<td>{$remaining}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Test API Call:</h3>";
    $apiUrl = url('/admin/api/booking/' . $booking->id);
    echo "<p>API URL: <a href='{$apiUrl}' target='_blank'>{$apiUrl}</a></p>";
    
    // Test the API response
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    echo "<h3>API Response:</h3>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
} else {
    echo "<p style='color:red'>No pending bookings found!</p>";
}