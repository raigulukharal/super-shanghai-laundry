<?php
// Laravel APP_KEY Generator
echo "<!DOCTYPE html>
<html>
<head>
    <title>APP_KEY Generator - SSDC Laundry</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .key-box { background: #2d2d2d; color: #0f0; padding: 20px; border-radius: 10px; font-family: monospace; font-size: 18px; margin: 20px 0; word-break: break-all; }
        .alt-keys { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0; font-family: monospace; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #0056b3; }
        .success { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔑 Laravel APP_KEY Generator for SSDC Laundry</h1>
    <h3>Domain: ssdc.shop</h3>";

// Generate primary key
$randomBytes = random_bytes(32);
$primaryKey = 'base64:' . base64_encode($randomBytes);

echo "<div class='key-box'>";
echo "APP_KEY=" . $primaryKey;
echo "</div>";

echo "<button onclick='copyToClipboard(\"" . $primaryKey . "\")'>📋 Copy Key</button>";
echo "<button onclick='window.location.reload()'>🔄 Generate New</button>";

// Generate alternative keys
echo "<h3>🔄 Alternative Keys (if above doesn't work):</h3>";
for($i = 1; $i <= 5; $i++) {
    $altKey = 'base64:' . base64_encode(random_bytes(32));
    echo "<div class='alt-keys'>";
    echo "Option $i: " . $altKey;
    echo " <button onclick='copyToClipboard(\"" . $altKey . "\")'>Copy</button>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>📋 Instructions:</h3>";
echo "<ol>
        <li>Click <strong>Copy Key</strong> button above</li>
        <li>Go to File Manager → <strong>/public_html/laravel13/.env</strong></li>
        <li>Find line: <strong>APP_KEY=</strong></li>
        <li>Replace with: <strong>APP_KEY=" . $primaryKey . "</strong></li>
        <li>Save the file</li>
        <li>Clear cache: <a href='/clear_cache'>Click here to clear cache</a></li>
      </ol>";

echo "<hr>";
echo "<h3>✅ Or use this ready key directly:</h3>";
echo "<div class='key-box'>" . $primaryKey . "</div>";

echo "<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
    alert('Key copied to clipboard!');
}
</script>";

// Check current .env file
$envPath = __DIR__ . '/../.env';
if(file_exists($envPath)) {
    echo "<hr>";
    echo "<h3>📁 Current .env file status:</h3>";
    $content = file_get_contents($envPath);
    if(preg_match('/APP_KEY=(.*)/', $content, $matches)) {
        echo "<div class='alt-keys'>";
        echo "Current APP_KEY: " . $matches[1] . "<br>";
        if(strlen($matches[1]) < 10) {
            echo "<span class='success'>⚠️ APP_KEY is missing or too short! Update it now.</span>";
        } else {
            echo "<span class='success'>✅ APP_KEY is set</span>";
        }
        echo "</div>";
    } else {
        echo "<div class='alt-keys' style='background:#ffe0e0'>";
        echo "❌ APP_KEY not found in .env file! Add it now.";
        echo "</div>";
    }
} else {
    echo "<div class='alt-keys' style='background:#ffe0e0'>";
    echo "❌ .env file not found at: " . $envPath;
    echo "</div>";
}
?>
</body>
</html>