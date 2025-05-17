<?php
use App\Models\Category;
use App\Models\Product;

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Lấy ID danh mục
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
    exit;
}

try {
    // Tìm danh mục
    $category = Category::find($id);
    if (!$category) {
        echo json_encode(['success' => false, 'message' => 'Category not found']);
        exit;
    }

    // Kiểm tra xem danh mục có sản phẩm không
    $hasProducts = Product::where('category_id', $id)->exists();
    if ($hasProducts) {
        $productCount = Product::where('category_id', $id)->count();
        echo json_encode([
            'success' => false, 
            'message' => "Không thể xóa danh mục này vì nó đang chứa {$productCount} sản phẩm. Vui lòng xóa hoặc di chuyển các sản phẩm sang danh mục khác trước khi xóa danh mục này."
        ]);
        exit;
    }

    // // Kiểm tra xem danh mục có danh mục con không
    $hasChildren = Category::where('parent_id', $id)->exists();
    if ($hasChildren) {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa danh mục này vì nó đang chứa danh mục con']);
        exit;
    }

    // Xóa ảnh nếu có
    if ($category->image && file_exists(__DIR__ . '/../../public/' . $category->image)) {
        unlink(__DIR__ . '/../../public/' . $category->image);
    }

    // Xóa danh mục
    $category->delete();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 