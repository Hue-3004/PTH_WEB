<?php

use App\Models\Product;
use App\Models\ProductVariant;

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Lấy ID biến thể
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// var_dump($id);
// die();

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid variant ID']);
    exit;
}

try {
    $productVariant = ProductVariant::find($id);
    
    if ($productVariant) {
        $productIdToUpdate = $productVariant->product_id; // Lưu lại product_id trước khi xóa

        $productVariant->delete(); // Xóa biến thể TRƯỚC

        // Cập nhật tổng số lượng trong bảng products SAU KHI XÓA
        if ($productIdToUpdate) {
            $productToUpdate = Product::find($productIdToUpdate);

            if ($productToUpdate) {
                // Bây giờ $totalQuantity sẽ là tổng của các biến thể CÒN LẠI
                $totalQuantity = ProductVariant::where('product_id', $productIdToUpdate)
                                    ->sum('stock_quantity');
                $productToUpdate->quantity = $totalQuantity;
                $productToUpdate->save();
            }
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Variant not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 