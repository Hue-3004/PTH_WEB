<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = (int)$_POST['product_id'];
    $variantId = (int)$_POST['variant_id'];
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $productId && $item['variant_id'] == $variantId) {
            $item['quantity'] = isset($item['quantity']) ? $item['quantity'] + $quantity : $quantity;
            $found = true;
            break;
        }
    }
    unset($item);

    if (!$found) {
        $_SESSION['cart'][] = [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity' => $quantity
        ];
    }

    // Đếm tổng số lượng sản phẩm trong giỏ
    $totalQty = 0;
    foreach ($_SESSION['cart'] as $item) {
        $totalQty += isset($item['quantity']) ? $item['quantity'] : 1;
    }
    echo $totalQty;
    exit;
}