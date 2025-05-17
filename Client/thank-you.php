<?php
use App\Models\Order;
use App\Models\Address;
use App\Models\User;
use App\Models\OrderPayment;

// Nếu là callback từ VNPay thì insert vào bảng order_payments
if (isset($_GET['vnp_TxnRef']) && isset($_GET['vnp_ResponseCode'])) {
    $order_id = intval($_GET['vnp_TxnRef']);
    $payment_status = ($_GET['vnp_ResponseCode'] == '00') ? 'completed' : 'failed';
    $amount = isset($_GET['vnp_Amount']) ? ($_GET['vnp_Amount'] / 100) : 0;
    $transaction_id = $_GET['vnp_TransactionNo'] ?? null;

    // Chỉ insert nếu chưa có payment cho order này với phương thức vnpay
    if (!OrderPayment::where('order_id', $order_id)->where('payment_method', 'vnpay')->exists()) {
        OrderPayment::create([
            'order_id' => $order_id,
            'payment_method' => 'vnpay',
            'amount' => $amount,
            'payment_status' => $payment_status,
            'payment_date' => date('Y-m-d H:i:s'),
            'transaction_id' => $transaction_id,
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
        ]);
    }
}

// Ưu tiên lấy order_id từ vnp_TxnRef (VNPay trả về), nếu không có thì lấy từ order_id (COD)
$order_id = isset($_GET['vnp_TxnRef']) ? intval($_GET['vnp_TxnRef']) : (isset($_GET['order_id']) ? intval($_GET['order_id']) : 0);
$order = $order_id ? Order::find($order_id) : null;
$address = $order ? Address::find($order->shipping_address_id) : null;
$user = $order ? User::find($order->user_id) : null;

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt hàng thành công</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/css/style.css">
    <style>
        .thankyou-section {
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }
        .thankyou-box {
            background: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.07);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .thankyou-title {
            color: #d4af37;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .thankyou-msg {
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 25px;
        }
        .order-info {
            text-align: left;
            margin-bottom: 20px;
        }
        .order-info strong {
            color: #d4af37;
        }
        .back-home-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #d4af37;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        .back-home-btn:hover {
            background: #b38f2a;
        }
    </style>
</head>
<body>
<section class="thankyou-section">
    <div class="thankyou-box">
        <div class="thankyou-title">🎉 Đặt hàng thành công!</div>
        <div class="thankyou-msg">
            Cảm ơn bạn <strong><?php echo $user ? htmlspecialchars($user->name) : 'Quý khách'; ?></strong> đã đặt hàng tại cửa hàng của chúng tôi.<br>
            Chúng tôi sẽ liên hệ và giao hàng sớm nhất có thể.
        </div>
        <?php if ($order): ?>
        <div class="order-info">
            <div><strong>Mã đơn hàng:</strong> #<?php echo $order->order_code; ?></div>
            <div><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order->order_date)); ?></div>
            <div><strong>Số điện thoại:</strong> <?php echo $user->phone; ?></div>
            <div><strong>Tổng tiền:</strong> <?php echo number_format($order->total_amount); ?>đ</div>
            <?php if ($address): ?>
            <div id="address-detail"
                 data-province="<?php echo htmlspecialchars($address->city ?? ''); ?>"
                 data-district="<?php echo htmlspecialchars($address->state ?? ''); ?>"
                 data-ward="<?php echo htmlspecialchars($address->postal_code ?? ''); ?>">
                <strong>Giao tới:</strong>
                <?php echo htmlspecialchars($address->street ?? ''); ?>,
                <span id="ward-name"></span>,
                <span id="district-name"></span>,
                <span id="province-name"></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <a href="/PTH_WEB" class="back-home-btn">Về trang chủ</a>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addressDiv = document.getElementById('address-detail');
    if (!addressDiv) return;
    const provinceCode = addressDiv.getAttribute('data-province');
    const districtCode = addressDiv.getAttribute('data-district');
    const wardCode = addressDiv.getAttribute('data-ward');

    // Lấy tên tỉnh/thành
    if (provinceCode) {
        fetch('https://provinces.open-api.vn/api/p/' + provinceCode)
            .then(res => res.json())
            .then(province => {
                document.getElementById('province-name').textContent = province.name || '';
            });
    }
    // Lấy tên quận/huyện
    if (districtCode) {
        fetch('https://provinces.open-api.vn/api/d/' + districtCode)
            .then(res => res.json())
            .then(district => {
                document.getElementById('district-name').textContent = district.name || '';
            });
    }
    // Lấy tên phường/xã
    if (wardCode) {
        fetch('https://provinces.open-api.vn/api/w/' + wardCode)
            .then(res => res.json())
            .then(ward => {
                document.getElementById('ward-name').textContent = ward.name || '';
            });
    }
});
</script>
</body>
</html>
