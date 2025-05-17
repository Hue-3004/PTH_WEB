<?php

use App\Models\Brand;
use App\Models\Product;

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Lấy ID thương hiệu
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
    exit;
}

try {
    $brand = Brand::find($id);
    
    // Kiểm tra xem thương hiệu có sản phẩm không
    $products = Product::where('brand_id', $id)->count();
    if ($products > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Không thể xóa thương hiệu này vì đang có sản phẩm liên kết. Vui lòng xóa hoặc chuyển các sản phẩm sang thương hiệu khác trước.'
        ]);
        exit;
    }
    
    $brand->delete();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 