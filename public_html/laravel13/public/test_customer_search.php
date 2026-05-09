<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Customer;

echo "<h1>Customer Search Test</h1>";

// Test 1: Get all customers
$customers = Customer::with('codes')->take(10)->get();
echo "<h3>Customers in database:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Mobile</th><th>Codes</th></tr>";
foreach($customers as $c) {
    echo "<tr>";
    echo "<td>" . $c->id . "</td>";
    echo "<td>" . $c->name . "</td>";
    echo "<td>" . $c->mobile . "</td>";
    echo "<td>" . $c->codes->pluck('code')->implode(', ') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test 2: Search by name
echo "<h3>Search by name 'Ali':</h3>";
$search = Customer::with('codes')
    ->where('name', 'LIKE', '%Ali%')
    ->get();
echo "Found: " . $search->count() . "<br>";
foreach($search as $c) {
    echo "- " . $c->name . " (" . $c->codes->pluck('code')->implode(', ') . ")<br>";
}
?>