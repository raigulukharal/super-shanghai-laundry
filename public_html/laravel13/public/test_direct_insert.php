<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

echo "<h1>Direct Database Insert Test</h1>";

// Get a booking
$booking = DB::table('bookings')->first();

if(!$booking) {
    echo "<p style='color:red'>No booking found! Please create a booking first.</p>";
    exit;
}

echo "<p>Found booking: <strong>" . $booking->invoice_no . "</strong> (ID: " . $booking->id . ")</p>";

// Try to insert
try {
    $inserted = DB::table('saved_invoices')->insert([
        'booking_id' => $booking->id,
        'invoice_no' => $booking->invoice_no,
        'file_name' => 'test_file_' . time() . '.html',
        'file_path' => 'invoices/test.html',
        'file_size' => 1234,
        'download_count' => 1,
        'created_by' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    if($inserted) {
        echo "<p style='color:green; font-size:18px;'>✅ SUCCESS! Data inserted into saved_invoices table!</p>";
        
        // Show the inserted data
        $lastRecord = DB::table('saved_invoices')->orderBy('id', 'desc')->first();
        echo "<h3>Last inserted record:</h3>";
        echo "<pre>";
        print_r($lastRecord);
        echo "</pre>";
    } else {
        echo "<p style='color:red'>❌ Insert failed</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// Show all records
echo "<h2>All Records in saved_invoices table:</h2>";
$records = DB::table('saved_invoices')->get();
if(count($records) > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr style='background:#ddd;'><th>ID</th><th>Invoice No</th><th>File Name</th><th>Download Count</th><th>Created At</th></tr>";
    foreach($records as $rec) {
        echo "<tr>";
        echo "<td>{$rec->id}</td>";
        echo "<td>{$rec->invoice_no}</td>";
        echo "<td>{$rec->file_name}</td>";
        echo "<td>{$rec->download_count}</td>";
        echo "<td>{$rec->created_at}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:orange'>No records found in saved_invoices table</p>";
}
?>