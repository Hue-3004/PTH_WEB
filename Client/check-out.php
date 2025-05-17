<?php
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use Illuminate\Support\Facades\DB;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /PTH_WEB/login');
    exit;
}
if (!isset($_SESSION['cart'])) {
    header('Location: /PTH_WEB');
    exit;
}

$user = User::find($_SESSION['user_id']);

// Lấy địa chỉ hiện tại của user nếu có
$current_address = Address::where('user_id', $user->id)
                         ->where('address_type', 'shipping')
                         ->first();

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        if (empty($_POST['shipping_name']) || empty($_POST['shipping_phone']) || 
            empty($_POST['province']) || empty($_POST['district']) || 
            empty($_POST['ward']) || empty($_POST['specific_address'])) {
            throw new Exception('Vui lòng điền đầy đủ thông tin giao hàng');
        }

        // Lấy thông tin từ form
        $payment_method = $_POST['payment_method'] ?? 'cod';
        $shipping_name = $_POST['shipping_name'];
        $shipping_phone = $_POST['shipping_phone'];
        $province = $_POST['province']; // code
        $district = $_POST['district']; // code
        $ward = $_POST['ward'];         // code
        $specific_address = $_POST['specific_address'];
        $note = $_POST['note'] ?? '';
        $shipping_method = $_POST['shipping_method'] ?? 'fast';
        $order_code = 'DH' . date('ymd') . rand(10, 99);

        // Validate cart
        if (empty($_SESSION['cart'])) {
            throw new Exception('Giỏ hàng trống');
        }

        // Tính phí vận chuyển
        $shipping_fee = $shipping_method === 'fast' ? 30000 : 25000;

        // Tính tổng tiền hàng
        $subtotal = 0;
        foreach ($_SESSION['cart'] as $item) {
            $variant = ProductVariant::find($item['variant_id']);
            if (!$variant) {
                throw new Exception('Sản phẩm không tồn tại');
            }
            $subtotal += $variant->price_new * $item['quantity'];
        }

        // Tổng tiền đơn hàng (bao gồm phí vận chuyển)
        $total_amount = $subtotal + $shipping_fee;

        try {
            // Kiểm tra và cập nhật hoặc tạo mới địa chỉ
            if ($current_address) {
                // Cập nhật địa chỉ hiện có
                $current_address->street = $specific_address;
                $current_address->postal_code = $ward;
                $current_address->state = $district;
                $current_address->city = $province;
                $current_address->country =0;
                $current_address->save();
                $shipping_address = $current_address;
            } else {
                // Tạo địa chỉ mới
                $shipping_address = new Address();
                $shipping_address->user_id = $user->id;
                $shipping_address->street = $specific_address;
                $shipping_address->postal_code = $ward;
                $shipping_address->state = $district;
                $shipping_address->city = $province;
                $shipping_address->country =0;
                $shipping_address->save();
            }

            // Tạo đơn hàng mới
            $order = new Order();
            $order->user_id = $user->id;
            $order->order_code = $order_code;
            $order->order_date = date('Y-m-d H:i:s');
            $order->status = 'pending';
            $order->total_amount = $total_amount;
            $order->shipping_address_id = $shipping_address->id;
            $order->billing_address_id = $shipping_address->id;
         //   $order->note = $note;
            $order->created_at = date('Y-m-d H:i:s');
            $order->updated_at = date('Y-m-d H:i:s');
            $order->save();

            // Thêm các sản phẩm vào order_items
            foreach ($_SESSION['cart'] as $item) {
                $variant = ProductVariant::find($item['variant_id']);
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->variant_id = $variant->id;
                $orderItem->quantity = $item['quantity'];
                $orderItem->unit_price = $variant->price_new;
                $orderItem->created_at = date('Y-m-d H:i:s');
                $orderItem->updated_at = date('Y-m-d H:i:s');
                $orderItem->save();
            }
            // Xóa giỏ hàng
            unset($_SESSION['cart']);

            // Xử lý thanh toán
            if ($payment_method === 'vnpay') {
                // Cấu hình VNPay
                $vnp_TmnCode = '8TKOSK63';
                $vnp_HashSecret = 'KWVSKMORO004EISIYKM91EVS2X5GSLH0';
                $vnp_Url = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
                $vnp_Returnurl = 'http://localhost/PTH_WEB/thank-you';
                createOrderVnpay($order, $vnp_TmnCode, $vnp_HashSecret, $vnp_Url, $vnp_Returnurl);
                exit;
            } else {
                // Thêm thanh toán COD vào bảng payments
                \App\Models\OrderPayment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'cod',
                    'amount' => $order->total_amount,
                    'payment_status' => 'pending',
                    'payment_date' => date('Y-m-d H:i:s'),
                    'transaction_id' => null,
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);
                header('Location: /PTH_WEB/thank-you?order_id=' . $order->id);
                exit;
            }

        } catch (Exception $e) {
            throw $e;
        }

    } catch (Exception $e) {
        $error_message = 'Có lỗi xảy ra khi xử lý đơn hàng: ' . $e->getMessage();
    }
}


function createOrderVnpay($order, $vnp_TmnCode, $vnp_HashSecret, $vnp_Url, $vnp_Returnurl)
{
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $startTime = date("YmdHis");
    $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));
    $vnp_TxnRef = strval($order->id);
    $vnp_Amount = strval(intval($order->total_amount) * 100);
    $vnp_Locale = 'vn';
    $vnp_BankCode = '';
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
    $vnp_OrderInfo = "Thanh toán đơn hàng #" . $order->id;
    $inputData = array(
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => $startTime,
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => "other",
        "vnp_ReturnUrl" => $vnp_Returnurl,
        "vnp_TxnRef" => $vnp_TxnRef,
        "vnp_ExpireDate" => $expire,
    );
    if (!empty($vnp_BankCode)) {
        $inputData['vnp_BankCode'] = $vnp_BankCode;
    }
    ksort($inputData);
    $hashdata = '';
    $i = 0;
    $query = '';
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
    }
    $vnp_Url = $vnp_Url . "?" . $query;
    if (isset($vnp_HashSecret)) {
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
    }
    header('Location: ' . $vnp_Url);
    exit;
}

?>

<!-- Checkout Page Section -->
<section class="checkout-section">
    <div class="container">
        <div class="checkout-header">
            <h1 class="section-title">Thanh Toán</h1>
            <p class="section-subtitle">Hoàn tất đơn hàng của bạn</p>
        </div>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <!-- User Information Section -->

        <form method="POST" action="">
            <div class="checkout-grid">
                <div class="checkout-form">
                    <div class="form-section">
                        <h2 class="form-title">Thông Tin Giao Hàng</h2>
                        <div class="form-group">
                            <label>Họ và Tên</label>
                            <input type="text" name="shipping_name" value="<?php echo $user->name; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Số Điện Thoại</label>
                            <input type="tel" name="shipping_phone" value="<?php echo $user->phone; ?>" required>
                        </div>
                        <div class="address-row">
                            <div class="form-group">
                                <label>Tỉnh/Thành phố</label>
                                <select name="province" id="province" required value="<?php echo isset($current_address->city) ? $current_address->city : ''; ?>">
                                    <option value="">Chọn Tỉnh/Thành phố</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Quận/Huyện</label>
                                <select name="district" id="district" required value="<?php echo isset($current_address->state) ? $current_address->state : ''; ?>">
                                    <option value="">Chọn Quận/Huyện</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Phường/Xã</label>
                                <select name="ward" id="ward" required value="<?php echo isset($current_address->postal_code) ? $current_address->postal_code : ''; ?>">
                                    <option value="">Chọn Phường/Xã</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Địa Chỉ Cụ Thể</label>
                            <textarea name="specific_address" rows="3" placeholder="Số nhà, tên đường, tên khu phố..." required><?php echo $current_address ? $current_address->street : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Ghi Chú</label>
                            <textarea name="note" rows="3" placeholder="Ghi chú về đơn hàng, ví dụ: thời gian hay chỉ dẫn địa điểm giao hàng chi tiết hơn."></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2 class="form-title">Phương Thức Thanh Toán</h2>
                        <div class="payment-methods">
                            <div class="payment-method" onclick="selectPayment('cod')">
                                <div class="payment-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="payment-info">
                                    <h3>Thanh Toán Khi Nhận Hàng (COD)</h3>
                                    <p>Thanh toán bằng tiền mặt khi nhận hàng</p>
                                </div>
                            </div>
                            <div class="payment-method" onclick="selectPayment('vnpay')">
                                <div class="payment-icon">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div class="payment-info">
                                    <h3>Thanh Toán Qua VNPay</h3>
                                    <p>Thanh toán an toàn qua cổng thanh toán VNPay</p>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="payment_method" id="payment_method" value="cod">
                    </div>
                </div>

                <div class="order-summary">
                    <h2 class="form-title">Tổng Đơn Hàng</h2>
                    <div class="cart-items">
                        <?php
                        $total = 0;
                        foreach ($_SESSION['cart'] as $item) {
                            $variant = ProductVariant::find($item['variant_id']);
                            if ($variant) {
                                $product = Product::find($variant->product_id);
                                $subtotal = $variant->price_new * $item['quantity'];
                                $total += $subtotal;
                        ?>
                        <div class="cart-item">
                            <div class="cart-item-image">
                                <img src="<?php echo BASE_URL . '/public/' . $product->image; ?>" alt="<?php echo $product->name; ?>">
                            </div>
                            <div class="cart-item-info">
                                <h4><?php echo $product->name; ?></h4>
                                <p>Size: <?php echo $variant->size; ?> | Màu: <?php echo $variant->color; ?></p>
                                <p>Số lượng: <?php echo $item['quantity']; ?></p>
                                <p class="price"><?php echo number_format($subtotal); ?>đ</p>
                            </div>
                        </div>
                        <?php
                            }
                        }
                        ?>
                    </div>

                    <div class="summary-row">
                        <span>Tạm tính</span>
                        <span><?php echo number_format($total); ?>đ</span>
                    </div>
                    <div class="shipping-options">
                        <h3>Phương thức vận chuyển</h3>
                        <div class="shipping-option">
                            <input type="radio" name="shipping_method" id="fast" value="fast" checked>
                            <label for="fast">
                                <div class="shipping-info">
                                    <h4>Giao hàng nhanh</h4>
                                    <p>Giao hàng trong 1-2 ngày</p>
                                </div>
                                <div class="shipping-price">30.000đ</div>
                            </label>
                        </div>
                        <div class="shipping-option">
                            <input type="radio" name="shipping_method" id="economy" value="economy">
                            <label for="economy">
                                <div class="shipping-info">
                                    <h4>Giao hàng tiết kiệm</h4>
                                    <p>Giao hàng trong 3-5 ngày</p>
                                </div>
                                <div class="shipping-price">25.000đ</div>
                            </label>
                        </div>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng cộng</span>
                        <span id="total-amount"><?php echo number_format($total + 30000); ?>đ</span>
                    </div>

                    <button type="submit" class="checkout-button">
                        Hoàn Tất Đơn Hàng
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<style>
.checkout-section {
    padding: 60px 0;
    background-color: #f8f9fa;
}

.checkout-header {
    text-align: center;
    margin-bottom: 40px;
}

.section-title {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 10px;
}

.section-subtitle {
    color: #666;
    font-size: 1.1rem;
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
}

.checkout-form {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.form-section {
    margin-bottom: 30px;
}

.form-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    margin-bottom: 20px;
    color: #333;
    padding-bottom: 10px;
    border-bottom: 2px solid #d4af37;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #666;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #d4af37;
    outline: none;
}

.payment-methods {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.payment-method {
    border: 2px solid #ddd;
    border-radius: 10px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 15px;
}

.payment-method:hover {
    border-color: #d4af37;
}

.payment-method.selected {
    border-color: #d4af37;
    background-color: rgba(212, 175, 55, 0.05);
}

.payment-icon {
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #d4af37;
}

.payment-info h3 {
    font-size: 1.1rem;
    margin-bottom: 5px;
    color: #333;
}

.payment-info p {
    color: #666;
    font-size: 0.9rem;
}

.order-summary {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 20px;
}

.cart-items {
    margin-bottom: 20px;
}

.cart-item {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #ddd;
}

.cart-item-image {
    width: 80px;
    height: 80px;
    border-radius: 5px;
    overflow: hidden;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-item-info h4 {
    font-size: 1rem;
    margin-bottom: 5px;
    color: #333;
}

.cart-item-info p {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.price {
    color: #d4af37;
    font-weight: 500;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
}

.summary-row.total {
    font-size: 1.2rem;
    font-weight: bold;
    color: #333;
    border-bottom: none;
}

.checkout-button {
    width: 100%;
    padding: 15px;
    background-color: #d4af37;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-family: 'Poppins', sans-serif;
}

.checkout-button:hover {
    background-color: #b38f2a;
}

@media (max-width: 768px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }

    .payment-methods {
        grid-template-columns: 1fr;
    }

    .section-title {
        font-size: 2rem;
    }
}

/* Add these new styles */
select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    background-color: white;
    cursor: pointer;
    transition: border-color 0.3s ease;
}

select:focus {
    border-color: #d4af37;
    outline: none;
}

select option {
    padding: 10px;
}

/* Add loading state styles */
select:disabled {
    background-color: #f8f9fa;
    cursor: not-allowed;
}

/* Add error state styles */
select.error {
    border-color: #dc3545;
}

/* Add success state styles */
select.success {
    border-color: #28a745;
}

/* Add these new styles for address row */
.address-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.address-row .form-group {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .address-row {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}

/* Add these new styles for shipping options */
.shipping-options {
    margin: 20px 0;
    padding: 20px 0;
    border-top: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
}

.shipping-options h3 {
    font-size: 1.1rem;
    margin-bottom: 15px;
    color: #333;
}

.shipping-option {
    margin-bottom: 10px;
}

.shipping-option:last-child {
    margin-bottom: 0;
}

.shipping-option input[type="radio"] {
    display: none;
}

.shipping-option label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.shipping-option input[type="radio"]:checked + label {
    border-color: #d4af37;
    background-color: rgba(212, 175, 55, 0.05);
}

.shipping-info h4 {
    font-size: 1rem;
    margin-bottom: 5px;
    color: #333;
}

.shipping-info p {
    font-size: 0.9rem;
    color: #666;
    margin: 0;
}

.shipping-price {
    font-weight: 500;
    color: #d4af37;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

/* Add these new styles for user information section */
.user-info-section {
    margin-bottom: 30px;
}

.user-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.user-info-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.info-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.2rem;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #d4af37;
}

.info-content {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.info-item {
    display: flex;
    gap: 10px;
}

.info-item .label {
    color: #666;
    min-width: 120px;
}

.info-item .value {
    color: #333;
    font-weight: 500;
}

@media (max-width: 768px) {
    .user-info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function selectPayment(method) {
    // Remove selected class from all payment methods
    document.querySelectorAll('.payment-method').forEach(el => {
        el.classList.remove('selected');
    });

    // Add selected class to clicked payment method
    event.currentTarget.classList.add('selected');

    // Update hidden input value
    document.getElementById('payment_method').value = method;
}

// Select COD by default
document.querySelector('.payment-method').classList.add('selected');

const currentAddress = {
    province: '<?php echo isset($current_address->city) ? $current_address->city : ''; ?>',
    district: '<?php echo isset($current_address->state) ? $current_address->state : ''; ?>',
    ward: '<?php echo isset($current_address->postal_code) ? $current_address->postal_code : ''; ?>',
    specific_address: '<?php echo isset($current_address->street) ? $current_address->street : ''; ?>'
};


document.addEventListener('DOMContentLoaded', function() {
    // Load provinces
    fetch('https://provinces.open-api.vn/api/?depth=1')
        .then(response => response.json())
        .then(data => {
            const provinceSelect = document.getElementById('province');
            data.forEach(province => {
                const option = document.createElement('option');
                option.value = province.code;
                option.textContent = province.name;
                if (province.code == currentAddress.province) option.selected = true;
                provinceSelect.appendChild(option);
            });
            if (currentAddress.province) provinceSelect.dispatchEvent(new Event('change'));
        });
});

document.getElementById('province').addEventListener('change', function() {
    const provinceCode = this.value;
    const districtSelect = document.getElementById('district');
    const wardSelect = document.getElementById('ward');
    districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
    if (provinceCode) {
        fetch(`https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`)
            .then(response => response.json())
            .then(data => {
                data.districts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.code;
                    option.textContent = district.name;
                    if (district.code == currentAddress.district) option.selected = true;
                    districtSelect.appendChild(option);
                });
                if (currentAddress.district) districtSelect.dispatchEvent(new Event('change'));
            });
        // Lưu tên tỉnh vào hidden input
        const selected = this.options[this.selectedIndex];
        document.getElementById('province_name').value = selected.textContent;
    }
});

document.getElementById('district').addEventListener('change', function() {
    const districtCode = this.value;
    const wardSelect = document.getElementById('ward');
    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
    if (districtCode) {
        fetch(`https://provinces.open-api.vn/api/d/${districtCode}?depth=2`)
            .then(response => response.json())
            .then(data => {
                data.wards.forEach(ward => {
                    const option = document.createElement('option');
                    option.value = ward.code;
                    option.textContent = ward.name;
                    if (ward.code == currentAddress.ward) option.selected = true;
                    wardSelect.appendChild(option);
                });
            });
        // Lưu tên quận/huyện vào hidden input
        const selected = this.options[this.selectedIndex];
        document.getElementById('district_name').value = selected.textContent;
    }
});

document.getElementById('ward').addEventListener('change', function() {
    // Lưu tên phường/xã vào hidden input
    const selected = this.options[this.selectedIndex];
    document.getElementById('ward_name').value = selected.textContent;
});

// Add shipping cost calculation
document.querySelectorAll('input[name="shipping_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const shippingCost = this.value === 'fast' ? 30000 : 25000;
        const subtotal = <?php echo $total; ?>;
        const total = subtotal + shippingCost;
        document.getElementById('total-amount').textContent = total.toLocaleString('vi-VN') + 'đ';
    });
});
</script>
