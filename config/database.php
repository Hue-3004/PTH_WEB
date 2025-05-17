<?php
use Illuminate\Database\Capsule\Manager as Capsule;

// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// echo "Bắt đầu kết nối database...<br>";

// Kiểm tra file autoload.php
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die('Error: vendor/autoload.php not found. Please run composer install');
}
require_once $autoloadPath;

// echo "Đã load autoload.php<br>";

$capsule = new Capsule;

try {
    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'hue_12_5_2025',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ]);

    // echo "Đã cấu hình kết nối database<br>";

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    // echo "Đã khởi tạo Eloquent<br>";

    // Kiểm tra kết nối
    $connection = $capsule->getConnection();
    $connection->getPdo();
    // echo "Kết nối database thành công!<br>";

} catch (\Exception $e) {
    echo "Lỗi kết nối database: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    die();
} 
?> 