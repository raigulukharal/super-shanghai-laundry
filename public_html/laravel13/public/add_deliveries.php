<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Delivery;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

echo "<h1>Add Sample Deliveries</h1>";

// Get first booking
$booking = Booking::first();

if ($booking) {
    echo "<p>Found booking: " . $booking->invoice_no . " (ID: " . $booking->id . ")</p>";
    
    // Check if delivery already exists
    $existing = Delivery::where('booking_id', $booking->id)->first();
    
    if (!$existing) {
        $delivery = Delivery::create([
            'booking_id' => $booking->id,
            'receiver_name' => 'Test Receiver',
            'receiver_mobile' => '03001234567',
            'notes' => 'Test delivery',
            'delivery_date' => now(),
            'created_by' => 1
        ]);
        echo "<p style='color:green'>✅ Delivery added! ID: " . $delivery->id . "</p>";
    } else {
        echo "<p>Delivery already exists for this booking.</p>";
    }
    
    // Show all deliveries
    $deliveries = Delivery::with('booking')->get();
    echo "<h2>All Deliveries (" . $deliveries->count() . ")</h2>";
    echo "<pre>";
    foreach ($deliveries as $d) {
        echo "ID: " . $d->id . " - Invoice: " . ($d->booking ? $d->booking->invoice_no : 'N/A') . " - Receiver: " . $d->receiver_name . "\n";
    }
    echo "</pre>";
    
} else {
    echo "<p style='color:red'>No bookings found! Please create a booking first.</p>";
}

// Test search
echo "<h2>Testing Search API:</h2>";
$searchUrl = url('/admin/deliveries/search?term=test');
echo "<p>URL: " . $searchUrl . "</p>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $searchUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Status: " . $httpCode . "</p>";
echo "<p>Response: " . $response . "</p>";
?>