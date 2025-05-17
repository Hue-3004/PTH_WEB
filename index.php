<?php
session_start();
require_once __DIR__ . '/config.php';

// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load database configuration
require_once __DIR__ . '/config/database.php';

// Load routes
require_once __DIR__ . '/routes/web.php';
require_once __DIR__ . '/routes/admin.php';

// Lấy URI hiện tại và loại bỏ prefix /PTH_WEB
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/PTH_WEB', '', $uri);
if (empty($uri)) {
    $uri = '/';
}

// Debug: In ra thông tin chi tiết
// echo "Current URI: " . $uri . "<br>";
// echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
// echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
// echo "Available routes: <pre>" . print_r(array_keys($routes), true) . "</pre>";
// echo "Available admin routes: <pre>" . print_r(array_keys($adminRoutes), true) . "</pre>";

// Kiểm tra và gọi route admin nếu có prefix /admin
if (strpos($uri, '/admin') === 0) {
    $matched = false;
    foreach ($adminRoutes as $route => $handler) {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        if (preg_match($pattern, $uri, $matches)) {
            // Remove the full match from matches array
            array_shift($matches);
            // Call the handler with the matched parameters
            call_user_func_array($handler, $matches);
            $matched = true;
            break;
        }
    }
    
    if (!$matched) {
        http_response_code(404);
        echo '404 Not Found - Admin route not found';
    }
} elseif (isset($routes[$uri])) {
    $routes[$uri]();
} else {
    http_response_code(404);
    echo '404 Not Found';
}
?>
