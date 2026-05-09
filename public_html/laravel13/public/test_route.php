<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "<h1>Route Test</h1>";

// Check if route exists
$routes = app('router')->getRoutes();

echo "<h2>All Delivery Routes:</h2>";
echo "<ul>";
foreach ($routes as $route) {
    if (strpos($route->uri(), 'deliveries') !== false) {
        echo "<li>" . $route->uri() . " - " . implode(',', $route->methods()) . "</li>";
    }
}
echo "</ul>";

// Try to call the search route
echo "<h2>Testing Search Route:</h2>";
try {
    $response = file_get_contents('https://ssdc.shop/admin/deliveries/search?term=test');
    echo "Response: " . $response;
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>