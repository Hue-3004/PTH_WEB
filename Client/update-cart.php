<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Product;
use App\Models\ProductVariant;

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $variantId = isset($_POST['variant_id']) ? (int)$_POST['variant_id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    $cart = &$_SESSION['cart'];

    if ($action === 'update') {
        foreach ($cart as &$item) {
            if ($item['product_id'] == $productId && $item['variant_id'] == $variantId) {
                $item['quantity'] = $quantity;
                break;
            }
        }
        unset($item);
    } elseif ($action === 'remove') {
        foreach ($cart as $k => $item) {
            if ($item['product_id'] == $productId && $item['variant_id'] == $variantId) {
                unset($cart[$k]);
                break;
            }
        }
        $_SESSION['cart'] = array_values($cart);
    }
    echo json_encode([
        'success' => true
    ]);
    exit;
} 