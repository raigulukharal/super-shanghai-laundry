<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$routes = app('router')->getRoutes();

echo "<h1>Route Checker</h1>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Method</th><th>URI</th><th>Name</th></tr>";

foreach ($routes as $route) {
    $uri = $route->uri();
    if (strpos($uri, 'partial') !== false || 
        strpos($uri, 'deliveries') !== false || 
        strpos($uri, 'customers') !== false) {
        $methods = implode('|', $route->methods());
        $name = $route->getName();
        echo "<tr>";
        echo "<td>{$methods}</td>";
        echo "<td>{$uri}</td>";
        echo "<td>{$name}</td>";
        echo "</tr>";
    }
}
echo "</table>";