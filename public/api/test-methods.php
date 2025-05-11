<?php
// test-methods.php - Test different HTTP methods with session

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Save test data in session if not exists
if (!isset($_SESSION['test_data'])) {
    $_SESSION['test_data'] = 'Session is working';
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get current session ID
$session_id = session_id();

// Output headers and request info
header('Content-Type: text/html');
echo "<h1>HTTP Method Test</h1>";
echo "<p>Current method: <strong>$method</strong></p>";
echo "<p>Session ID: <strong>$session_id</strong></p>";
echo "<p>Session data: <strong>" . $_SESSION['test_data'] . "</strong></p>";

// Show all headers
echo "<h2>Request Headers</h2>";
echo "<pre>";
$headers = getallheaders();
foreach ($headers as $name => $value) {
    echo htmlspecialchars("$name: $value") . "\n";
}
echo "</pre>";

// Show request parameters
echo "<h2>GET Parameters</h2>";
echo "<pre>" . print_r($_GET, true) . "</pre>";

echo "<h2>POST Parameters</h2>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

// Get raw input for PUT/POST/etc.
$input = file_get_contents('php://input');
echo "<h2>Raw Request Body</h2>";
echo "<pre>" . htmlspecialchars($input) . "</pre>";

// Test links for different methods
echo "<h2>Test Links</h2>";
echo "<ul>";
echo "<li><a href='?test=get_param'>Test GET</a></li>";
echo "<li><form method='POST'><input type='hidden' name='test' value='post_param'><button type='submit'>Test POST</button></form></li>";
echo "</ul>";

// JavaScript for PUT test
echo <<<HTML
<script>
function testPut() {
    fetch('test-methods.php?test=put_param', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'put_data=test_value'
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('putResult').innerHTML = data;
    });
}
</script>
<button onclick="testPut()">Test PUT (AJAX)</button>
<div id="putResult"></div>
HTML;

// Show the Session class if it exists
echo "<h2>Session Class</h2>";
if (class_exists('Portalbox\Session')) {
    echo "<p>Session class exists. Try adding debug code to Session::require_authorization method.</p>";
} else {
    echo "<p>Session class not found in this context.</p>";
}

// Output PHP session configuration
echo "<h2>PHP Session Configuration</h2>";
echo "<pre>";
echo "session.save_handler: " . ini_get('session.save_handler') . "\n";
echo "session.save_path: " . ini_get('session.save_path') . "\n";
echo "session.use_cookies: " . ini_get('session.use_cookies') . "\n";
echo "session.use_only_cookies: " . ini_get('session.use_only_cookies') . "\n";
echo "session.use_strict_mode: " . ini_get('session.use_strict_mode') . "\n";
echo "session.cookie_secure: " . ini_get('session.cookie_secure') . "\n";
echo "session.cookie_httponly: " . ini_get('session.cookie_httponly') . "\n";
echo "session.cookie_samesite: " . ini_get('session.cookie_samesite') . "\n";
echo "</pre>";
?>
