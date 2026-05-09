<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Customer;

echo "<h1>Customer Search API Test</h1>";

$term = 'Ali'; // Change this to test

$customers = Customer::with('codes')
    ->where(function($q) use ($term) {
        $q->where('name', 'LIKE', "%{$term}%")
          ->orWhere('mobile', 'LIKE', "%{$term}%")
          ->orWhereHas('codes', function($cq) use ($term) {
              $cq->where('code', 'LIKE', "%{$term}%");
          });
    })
    ->limit(10)
    ->get();

echo "<h2>Search Term: '{$term}'</h2>";
echo "<h2>Found: " . $customers->count() . " customers</h2>";

$results = [];
foreach ($customers as $customer) {
    $codeList = $customer->codes->pluck('code')->implode(', ');
    $results[] = [
        'id' => $customer->id,
        'text' => $customer->name . ' (' . $codeList . ')',
        'name' => $customer->name,
        'mobile' => $customer->mobile,
        'codes' => $codeList,
        'code_array' => $customer->codes->pluck('code')->toArray()
    ];
    echo "<div style='border:1px solid #ccc; padding:10px; margin:5px'>";
    echo "<strong>ID:</strong> {$customer->id}<br>";
    echo "<strong>Name:</strong> {$customer->name}<br>";
    echo "<strong>Mobile:</strong> {$customer->mobile}<br>";
    echo "<strong>Codes:</strong> " . implode(', ', $customer->codes->pluck('code')->toArray()) . "<br>";
    echo "<strong>JSON Response:</strong> <pre>" . json_encode($results[count($results)-1], JSON_PRETTY_PRINT) . "</pre>";
    echo "</div>";
}

echo "<h2>Final JSON Response:</h2>";
echo "<pre>" . json_encode(['results' => $results], JSON_PRETTY_PRINT) . "</pre>";