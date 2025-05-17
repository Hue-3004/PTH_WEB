<?php
use App\Models\Order;

include __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_GET['id'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($order_id && $status) {
        try {
            $order = Order::find($order_id);
            if ($order) {
                $order->status = $status;
                $order->save();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật trạng thái đơn hàng thành công'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu thông tin cần thiết'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không được hỗ trợ'
    ]);
} 