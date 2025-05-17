<?php
// Định nghĩa các route cho Client
require_once __DIR__ . '/../config.php';
$routes = [
    '/' => function() {
        include __DIR__ . '/../Client/index.php';
    },
    '/login' => function() {
        include __DIR__ . '/../Client/login.php';
    },
    '/register' => function() {
        include __DIR__ . '/../Client/register.php';
    },
    '/logout' => function() {
        include __DIR__ . '/../Client/logout.php';
    },
    '/thank-you' => function() {
        include __DIR__ . '/../Client/thank-you.php';
    },
    '/vnpay-payment' => function() {
        include __DIR__ . '/../Client/vnpay-payment.php';
    },
    
    // Thêm các route khác nếu cần
]; 