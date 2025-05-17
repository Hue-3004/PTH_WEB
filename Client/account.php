<?php
use App\Models\User;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$user = User::find($_SESSION['user_id']);

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate dữ liệu
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $birthday = $_POST['birthday'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $province = $_POST['province'] ?? '';
        $district = $_POST['district'] ?? '';
        $ward = $_POST['ward'] ?? '';
        $specific_address = $_POST['specific_address'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Cập nhật thông tin cơ bản
        $user->name = $name;
        $user->phone = $phone;
        $user->birthday = $birthday;
        $user->gender = $gender;

        // Xử lý địa chỉ
        if ($province && $district && $ward && $specific_address) {
            // Tìm địa chỉ hiện tại của user
            $address = Address::where('user_id', $user->id)
                             ->where('address_type', 'shipping')
                             ->first();

            if (!$address) {
                // Tạo địa chỉ mới nếu chưa có
                $address = new Address();
                $address->user_id = $user->id;
                $address->address_type = 'shipping';
            }

            // Cập nhật thông tin địa chỉ
            $address->street = $specific_address;
            $address->postal_code = $ward;
            $address->state = $district;
            $address->city = $province;
            $address->country =0;
            $address->save();
        }

        // Xử lý upload avatar
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatar = $_FILES['avatar'];
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $avatar['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                // Tạo thư mục nếu chưa tồn tại
                $upload_dir = __DIR__ . '/../public/uploads/avatars/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $new_filename = uniqid() . '.' . $ext;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($avatar['tmp_name'], $upload_path)) {
                    // Xóa avatar cũ nếu có
                    if ($user->avatar && file_exists(__DIR__ . '/../' . $user->avatar)) {
                        unlink(__DIR__ . '/../' . $user->avatar);
                    }
                    // Lưu đường dẫn tương đối
                    $user->avatar = 'public/uploads/avatars/' . $new_filename;
                } else {
                    throw new Exception('Không thể upload ảnh. Vui lòng thử lại!');
                }
            } else {
                throw new Exception('Định dạng ảnh không hợp lệ. Chỉ chấp nhận: jpg, jpeg, png, gif');
            }
        }

        // Xử lý đổi mật khẩu
        if ($new_password) {
            if ($new_password === $confirm_password) {
                $user->password = password_hash($new_password, PASSWORD_DEFAULT);
            } else {
                throw new Exception('Mật khẩu xác nhận không khớp!');
            }
        }

        // Lưu thay đổi
        if ($user->save()) {
            $success_message = 'Cập nhật thông tin thành công!';
        } else {
            throw new Exception('Có lỗi xảy ra khi cập nhật thông tin!');
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Lấy địa chỉ hiện tại của user
$currentAddress = Address::where('user_id', $user->id)
                         ->where('address_type', 'shipping')
                         ->first();

// Lấy danh sách đơn hàng
$orders = Order::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
$orderItems = OrderItem::with(['variant.product'])
    ->whereIn('order_id', $orders->pluck('id'))
    ->get();
$addresses = Address::where('user_id', $user->id)->first();
?>

<!-- Account Page Section -->
<section class="account-section">
    <?php if (isset($success_message)): ?>
    <div class="alert alert-success">
        <?php echo $success_message; ?>
    </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
    <div class="alert alert-error">
        <?php echo $error_message; ?>
    </div>
    <?php endif; ?>

    <div class="account-hero">
        <div class="container">
            <h1 class="account-title">Tài Khoản Của Tôi</h1>
            <p class="account-subtitle">Quản lý thông tin và đơn hàng của bạn</p>
        </div>
    </div>

    <div class="account-content">
        <div class="container">
            <div class="account-grid">
                <!-- Sidebar Navigation -->
                <div class="account-sidebar">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php if($user->avatar && file_exists(__DIR__ . '/../' . $user->avatar)): ?>
                                <img src="/PTH_WEB/<?php echo $user->avatar; ?>" alt="User Avatar">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <h3><?php echo $user->name ?? 'Chưa cập nhật'; ?></h3>
                        <p><?php echo $user->email; ?></p>
                    </div>
                    <nav class="account-nav">
                        <a href="#profile" class="nav-item active">
                            <i class="fas fa-user-circle"></i>
                            Thông Tin Cá Nhân
                        </a>
                        <a href="#orders" class="nav-item">
                            <i class="fas fa-shopping-bag"></i>
                            Đơn Hàng Của Tôi
                        </a>
                        <a href="#address" class="nav-item">
                            <i class="fas fa-map-marker-alt"></i>
                            Địa Chỉ
                        </a>
                        <a href="#wishlist" class="nav-item">
                            <i class="fas fa-heart"></i>
                            Sản Phẩm Yêu Thích
                        </a>
                        <a href="/PTH_WEB/logout" class="nav-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Đăng Xuất
                        </a>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="account-main">
                    <!-- Profile Section -->
                    <div id="profile" class="account-section-content active">
                        <h2>Thông Tin Cá Nhân</h2>
                        <form class="profile-form" method="POST" action="" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Họ và Tên</label>
                                    <input type="text" name="name" value="<?php echo $user->name ?? ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" value="<?php echo $user->email; ?>" disabled>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tên Đăng Nhập</label>
                                    <input type="text" value="<?php echo $user->username; ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label>Số Điện Thoại</label>
                                    <input type="tel" name="phone" value="<?php echo $user->phone ?? ''; ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Ngày Sinh</label>
                                    <input type="date" name="birthday" value="<?php echo $user->birthday ?? ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Giới Tính</label>
                                    <select name="gender">
                                        <option value="">Chọn giới tính</option>
                                        <option value="male" <?php echo ($user->gender ?? '') == 'male' ? 'selected' : ''; ?>>Nam</option>
                                        <option value="female" <?php echo ($user->gender ?? '') == 'female' ? 'selected' : ''; ?>>Nữ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Ảnh Đại Diện</label>
                                    <input type="file" name="avatar" accept="image/*">
                                </div>
                                <div class="form-group">
                                            <label>Tỉnh/Thành phố</label>
                                            <select name="province" id="province" required>
                                                <option value="">Chọn Tỉnh/Thành phố</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Quận/Huyện</label>
                                            <select name="district" id="district" required>
                                                <option value="">Chọn Quận/Huyện</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Phường/Xã</label>
                                            <select name="ward" id="ward" required>
                                                <option value="">Chọn Phường/Xã</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Địa Chỉ Cụ Thể</label>
                                            <textarea name="specific_address" rows="3" placeholder="Số nhà, tên đường, tên khu phố..." required><?php echo $currentAddress->street ?? ''; ?></textarea>
                                </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Mật Khẩu Mới</label>
                                    <input type="password" name="new_password" placeholder="Để trống nếu không muốn thay đổi">
                                </div>
                                <div class="form-group">
                                    <label>Xác Nhận Mật Khẩu</label>
                                    <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu mới">
                                </div>
                            </div>
                            <button type="submit" class="save-btn">Lưu Thay Đổi</button>
                        </form>
                    </div>

                    <!-- Orders Section -->
                    <div id="orders" class="account-section-content">
                        <h2>Đơn Hàng Của Tôi</h2>
                        <div class="orders-list">
                            <?php if(isset($orders) && count($orders) > 0): ?>
                                <?php foreach($orders as $order): ?>
                                <?php 
                                    $payment = OrderPayment::where('order_id', $order['id'])->first();
                                    $payment_status = '';
                                    $payment_method = $payment ? $payment->payment_method : 'cod';

                                    if ($payment) {
                                        if ($payment->payment_status == 'pending') {
                                            $payment_status = 'Thanh toán sau khi nhận hàng';
                                        } else if ($payment->payment_status == 'completed') {
                                            $payment_status = 'Đã thanh toán';
                                        } else if ($payment->payment_status == 'failed') {
                                            $payment_status = 'Thanh toán thất bại';
                                        }
                                    } else {
                                        $payment_status = 'Chưa thanh toán';
                                    }
                                ?>
                                    <div class="order-item">
                                        <div class="order-header">
                                            <div class="order-info">
                                                <h3>Đơn Hàng #<?php echo $order['order_code']; ?></h3>
                                                <p>Đặt ngày: <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></p>
                                            </div>
                                            <div class="order-status <?php echo $order['status']; ?>">
                                                <?php
                                                    $statusText = '';
                                                    if($order['status'] == 'pending'){
                                                        $statusText = 'Chờ xác nhận';
                                                        $statusClass = 'pending';
                                                        $statusIcon = 'clock';
                                                    }else if($order['status'] == 'processing'){
                                                        $statusText = 'Đang xử lý';
                                                        $statusClass = 'processing';
                                                        $statusIcon = 'sync';
                                                    }else if($order['status'] == 'shipped'){
                                                        $statusText = 'Đã giao hàng';
                                                        $statusClass = 'shipping';
                                                        $statusIcon = 'truck';
                                                    }else if($order['status'] == 'delivered'){
                                                        $statusText = 'Đã giao hàng';
                                                        $statusClass = 'success';
                                                        $statusIcon = 'check-circle';
                                                    }else if($order['status'] == 'cancelled'){
                                                        $statusText = 'Đã hủy';
                                                        $statusClass = 'cancelled';
                                                        $statusIcon = 'times-circle';
                                                    }
                                                ?>
                                                <i class="fas fa-<?php echo $statusIcon; ?>"></i>
                                                <?php echo $statusText; ?>
                                            </div>
                                        </div>
                                        <div class="order-products">
                                        <?php foreach($orderItems as $item): ?>
                                            <?php if($item->order_id == $order['id']): ?>
                                            <div class="product-item">
                                                <img src="/PTH_WEB/public/<?php echo $item->variant->image; ?>" alt="<?php echo $item->variant->product->name; ?>">
                                                <div class="product-info">
                                                    <h4><?php echo $item->variant->product->name; ?></h4>
                                                    <p>Size: <?php echo $item->variant->size; ?></p>
                                                    <p>Màu: <?php echo $item->variant->color; ?></p>
                                                    <p>Số lượng: <?php echo $item->quantity; ?></p>
                                                    <p class="price"><?php echo number_format($item->unit_price * $item->quantity); ?>đ</p>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        </div>
                                        <div class="order-footer">
                                            <div class="order-total">
                                                <span>Tổng tiền:</span>
                                                <strong><?php echo number_format($order['total_amount']); ?>đ</strong>
                                            </div>
                                            <button class="view-details-btn" onclick="viewOrderDetails(<?php echo $order['id']; ?>,'<?php echo $payment_method;?>','<?php echo $payment_status;?>')">Xem Chi Tiết</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-orders">
                                    <i class="fas fa-shopping-bag"></i>
                                    <p>Bạn chưa có đơn hàng nào</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Thêm vào cuối file, trước </section> -->
<div id="orderDetailModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-modal" onclick="closeOrderDetailModal()">&times;</span>
        <div id="order-detail-body">
            <!-- Nội dung chi tiết đơn hàng sẽ được hiển thị ở đây -->
        </div>
    </div>
</div>
<style>
    /* Thêm vào cuối thẻ <style> */
.modal {
    position: fixed;
    z-index: 9999;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
}
/* ...existing code... */
.modal-content {
    background: #fff;
    padding: 40px 50px;
    border-radius: 16px;
    min-width: 500px;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 8px 32px rgba(44, 44, 44, 0.18), 0 1.5px 6px rgba(212, 175, 55, 0.08);
    animation: modalShow 0.25s;
}
@keyframes modalShow {
    from { transform: translateY(40px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.close-modal {
    position: absolute;
    top: 18px; right: 28px;
    font-size: 2.2rem;
    color: #d4af37;
    cursor: pointer;
    transition: color 0.2s;
}
.close-modal:hover {
    color: #b38f2a;
}
#order-detail-body h3 {
    font-size: 1.4rem;
    color: #d4af37;
    margin-bottom: 10px;
}
#order-detail-body p {
    margin: 0 0 8px 0;
    color: #444;
}
#order-detail-body hr {
    border: none;
    border-top: 1px solid #eee;
    margin: 18px 0;
}
#order-detail-body strong {
    color: #d4af37;
    font-size: 1.1rem;
}
#order-detail-body .order-detail-product {
    display: flex;
    gap: 16px;
    margin-bottom: 14px;
    align-items: center;
    background: #faf9f6;
    border-radius: 8px;
    padding: 10px 12px;
}
#order-detail-body .order-detail-product img {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #eee;
}
#order-detail-body .order-detail-product div {
    flex: 1;
}
@media (max-width: 800px) {
    .modal-content {
        min-width: 90vw;
        max-width: 98vw;
        padding: 20px 8px;
    }
    #order-detail-body .order-detail-product img {
        width: 50px;
        height: 50px;
    }
}
/* ...existing code... */
.close-modal {
    position: absolute;
    top: 10px; right: 20px;
    font-size: 2rem;
    color: #d4af37;
    cursor: pointer;
}
.account-section {
    background-color: #f8f9fa;
}

.account-hero {
    background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://js0fpsb45jobj.vcdn.cloud/storage/upload/media/nam-moi-2024/thang-42025/1600x635-2.jpg');
    background-size: cover;
    background-position: center;
    height: 200px;
    display: flex;
    align-items: center;
    text-align: center;
    color: #fff;
    margin-top: 76px;
}

.account-title {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.account-subtitle {
    font-size: 1.1rem;
    font-weight: 300;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.account-content {
    padding: 10px 0;
}

.account-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
}

/* Sidebar Styles */
.account-sidebar {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    padding: 30px;
}

.user-info {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.user-avatar {
    width: 100px;
    height: 100px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    overflow: hidden;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-avatar i {
    font-size: 2.5rem;
    color: #d4af37;
}

.user-info h3 {
    font-size: 1.2rem;
    margin-bottom: 5px;
    color: #333;
}

.user-info p {
    color: #666;
    font-size: 0.9rem;
}

.account-nav {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #666;
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.nav-item i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.nav-item:hover,
.nav-item.active {
    background: #f8f9fa;
    color: #d4af37;
}

/* Main Content Styles */
.account-main {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    padding: 30px;
}

.account-section-content {
    display: none;
}

.account-section-content.active {
    display: block;
}

.account-section-content h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    margin-bottom: 30px;
    color: #333;
}

/* Profile Form Styles */
.profile-form {
    max-width: 800px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #666;
}

.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.save-btn {
    background: #d4af37;
    color: #fff;
    padding: 12px 30px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.save-btn:hover {
    background: #b38f2a;
}
#orders{
    max-height: 510px;
    overflow: auto;
}
/* Orders List Styles */
.orders-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.order-item {
    border: 1px solid #eee;
    border-radius: 10px;
    overflow: hidden;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
}

.order-info h3 {
    font-size: 1.1rem;
    margin-bottom: 5px;
    color: #333;
}

.order-info p {
    color: #666;
    font-size: 0.9rem;
}

.order-status {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.9rem;
}

.order-status.success {
    background: #e8f5e9;
    color: #2e7d32;
}

.order-status.pending {
    background: #fff3e0;
    color: #ef6c00;
}

.order-products {
    padding: 20px;
}

.product-item {
    display: flex;
    gap: 20px;
    align-items: center;
}

.product-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 5px;
}

.product-info h4 {
    font-size: 1rem;
    margin-bottom: 5px;
    color: #333;
}

.product-info p {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.price {
    color: #d4af37 !important;
    font-weight: 500;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: #f8f9fa;
    border-top: 1px solid #eee;
}

.order-total {
    font-size: 1.1rem;
}

.order-total strong {
    color: #d4af37;
    margin-left: 10px;
}

.view-details-btn {
    background: transparent;
    border: 1px solid #d4af37;
    color: #d4af37;
    padding: 8px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.view-details-btn:hover {
    background: #d4af37;
    color: #fff;
}

@media (max-width: 768px) {
    .account-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .product-item {
        flex-direction: column;
        text-align: center;
    }
    
    .order-footer {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}

/* Additional styles for new elements */
.current-avatar {
    margin-top: 10px;
}

.current-avatar img {
    max-width: 150px;
    max-height: 150px;
    border-radius: 10px;
    border: 2px solid #d4af37;
}

select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    background-color: #fff;
}

textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    resize: vertical;
}

.no-orders {
    text-align: center;
    padding: 50px 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.no-orders i {
    font-size: 3rem;
    color: #d4af37;
    margin-bottom: 20px;
}

.no-orders p {
    color: #666;
    font-size: 1.1rem;
}

/* Status colors */
.order-status.processing {
    background: #e3f2fd;
    color: #1976d2;
}

.order-status.shipping {
    background: #fff3e0;
    color: #f57c00;
}

.order-status.cancelled {
    background: #ffebee;
    color: #d32f2f;
}

/* Add these new styles */
.alert {
    padding: 15px;
    margin: 20px 0;
    border-radius: 5px;
    text-align: center;
}

.alert-success {
    background-color: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}

.alert-error {
    background-color: #ffebee;
    color: #d32f2f;
    border: 1px solid #ffcdd2;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Navigation handling
    const navItems = document.querySelectorAll('.nav-item');
    const sections = document.querySelectorAll('.account-section-content');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if(this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                
                // Remove active class from all items
                navItems.forEach(nav => nav.classList.remove('active'));
                sections.forEach(section => section.classList.remove('active'));
                
                // Add active class to clicked item
                this.classList.add('active');
                
                // Show corresponding section
                const targetId = this.getAttribute('href').substring(1);
                document.getElementById(targetId).classList.add('active');
            }
        });
    });
    
    // Form validation
    const profileForm = document.querySelector('.profile-form');
    profileForm.addEventListener('submit', function(e) {
        const newPassword = this.querySelector('input[name="new_password"]').value;
        const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
        
        if(newPassword && newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Mật khẩu xác nhận không khớp!');
        }
    });

    // Load provinces on page load
    loadProvinces();
    
    // Load current address if exists
    <?php if ($currentAddress): ?>
    const currentAddress = {
        province: '<?php echo $currentAddress->city; ?>',
        district: '<?php echo $currentAddress->state; ?>',
        ward: '<?php echo $currentAddress->postal_code; ?>',
        specific_address: '<?php echo $currentAddress->street; ?>'
    };
    
    // Set initial values
    setTimeout(() => {
        // Set province
        const provinceSelect = document.getElementById('province');
        if (provinceSelect) {
            provinceSelect.value = currentAddress.province;
            loadDistricts(currentAddress.province);
            
            // Set district after districts are loaded
            setTimeout(() => {
                const districtSelect = document.getElementById('district');
                if (districtSelect) {
                    districtSelect.value = currentAddress.district;
                    loadWards(currentAddress.district);
                    
                    // Set ward after wards are loaded
                    setTimeout(() => {
                        const wardSelect = document.getElementById('ward');
                        if (wardSelect) {
                            wardSelect.value = currentAddress.ward;
                        }
                    }, 500);
                }
            }, 500);
        }
        
        // Set specific address
        const specificAddressInput = document.getElementById('specific_address');
        if (specificAddressInput) {
            specificAddressInput.value = currentAddress.specific_address;
        }
    }, 1000);
    <?php endif; ?>
});
const address = <?php echo json_encode($addresses); ?>;
if (address !== null) {
    var provinceCode = parseInt(address.city);
    var districtCode = parseInt(address.state);
    var wardCode = parseInt(address.postal_code);
    var street = address.street;
} else {
    var provinceCode = null;
    var districtCode = null;
    var wardCode = null;
    var street = '';
}
async function getAddressText() {
        try {
            // Kiểm tra dữ liệu đầu vào
            if (!provinceCode || !districtCode || !wardCode) {
                console.error('Mã tỉnh, quận, hoặc phường không hợp lệ:', { provinceCode, districtCode, wardCode });
                return 'Dữ liệu đầu vào không hợp lệ!';
            }

            // Lấy dữ liệu tỉnh và quận/huyện
            const provinceResponse = await fetch(`https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`);
            if (!provinceResponse.ok) {
                throw new Error(`Lỗi khi lấy dữ liệu tỉnh: ${provinceResponse.status}`);
            }
            const provinceData = await provinceResponse.json();
            const provinceName = provinceData.name || 'Tỉnh không xác định';

            // Tìm quận/huyện trong danh sách districts của tỉnh
            const district = provinceData.districts.find(d => d.code === districtCode);
            if (!district) {
                throw new Error(`Không tìm thấy quận/huyện với mã ${districtCode}`);
            }
            const districtName = district.name || 'Quận/Huyện không xác định';

            // Lấy dữ liệu phường/xã
            const districtResponse = await fetch(`https://provinces.open-api.vn/api/d/${districtCode}?depth=2`);
            if (!districtResponse.ok) {
                throw new Error(`Lỗi khi lấy dữ liệu phường/xã: ${districtResponse.status}`);
            }
            const districtData = await districtResponse.json();
            const ward = districtData.wards.find(w => w.code === wardCode);
            if (!ward) {
                throw new Error(`Không tìm thấy phường/xã với mã ${wardCode}`);
            }
            const wardName = ward.name || 'Phường/Xã không xác định';

            // Trả về chuỗi địa chỉ
            return `${street}, ${wardName}, ${districtName}, ${provinceName}`;
        } catch (e) {
            console.error('Lỗi khi lấy địa chỉ:', e);
            return 'Không lấy được địa chỉ!';
        }
    }
async function viewOrderDetails(orderId, payment_method, payment_status) {
    const orders = <?php echo json_encode($orders ?? []); ?>;
    const items = <?php echo json_encode($orderItems ?? []); ?>;
    const order = orders.find(o => o.id == orderId);

    if (!order) {
        alert("Không tìm thấy đơn hàng!");
        return;
    }

    const orderItemsList = items.filter(i => i.order_id == orderId);
    let statusText = '', statusClass = '', statusIcon = '';
    switch (order.status) {
        case 'pending':
            statusText = 'Chờ xác nhận';
            statusClass = 'pending';
            statusIcon = 'clock';
            break;
        case 'processing':
            statusText = 'Đang xử lý';
            statusClass = 'processing';
            statusIcon = 'sync';
            break;
        case 'shipped':
            statusText = 'Đã giao hàng';
            statusClass = 'shipping';
            statusIcon = 'truck';
            break;
        case 'delivered':
            statusText = 'Đã giao hàng';
            statusClass = 'success';
            statusIcon = 'check-circle';
            break;
        case 'cancelled':
            statusText = 'Đã hủy';
            statusClass = 'cancelled';
            statusIcon = 'times-circle';
            break;
        default:
            statusText = order.status;
            statusClass = '';
            statusIcon = '';
    }

    let html = `
        <h3>Đơn Hàng #${order.id}</h3>
        <p>Ngày đặt: ${new Date(order.created_at).toLocaleDateString('vi-VN')}</p>
        <p>Trạng thái: <span class="order-status ${statusClass}">${statusText}</span></p>
        <hr>
        <p>Phương thức thanh toán: <span class="order-status ${statusClass}">${payment_method}</span></p>
        <hr>
        <p>Trạng thái thanh toán: <span class="order-status ${statusClass}">${payment_status}</span></p>
        <hr>
        <div id="shipping-address-detail"><em>Đang tải địa chỉ nhận hàng...</em></div>
        <hr>`;

    orderItemsList.forEach(item => {
        html += `
        <div class="order-detail-product">
            <img src="/PTH_WEB/public/${item.variant.image}" alt="">
            <div>
                <strong>${item.variant.product.name}</strong><br>
                Size: ${item.variant.size} - Màu: ${item.variant.color}<br>
                Số lượng: ${item.quantity} x ${Number(item.unit_price).toLocaleString()}đ
            </div>
        </div>`;
    });

    html += `<hr><strong>Tổng tiền: ${Number(order.total_amount).toLocaleString()}đ</strong>`;

    document.getElementById('order-detail-body').innerHTML = html;
    document.getElementById('orderDetailModal').style.display = 'flex';

    try {
        const addressText = await getAddressText(order.shipping_address_id);
        document.getElementById('shipping-address-detail').innerHTML = `<strong>Địa chỉ nhận hàng:</strong> ${addressText}`;
    } catch (error) {
        document.getElementById('shipping-address-detail').innerHTML = `<strong>Lỗi khi tải địa chỉ:</strong> ${error.message}`;
    }
}


function closeOrderDetailModal() {
    document.getElementById('orderDetailModal').style.display = 'none';
}
// Hàm load danh sách tỉnh/thành phố
async function loadProvinces() {
    try {
        const response = await fetch('https://provinces.open-api.vn/api/');
        const provinces = await response.json();
        
        const provinceSelect = document.getElementById('province');
        provinceSelect.innerHTML = '<option value="">Chọn Tỉnh/Thành phố</option>';
        
        provinces.forEach(province => {
            const option = document.createElement('option');
            option.value = province.code;
            option.textContent = province.name;
            provinceSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading provinces:', error);
    }
}

// Hàm load danh sách quận/huyện
async function loadDistricts(provinceCode) {
    try {
        const response = await fetch(`https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`);
        const province = await response.json();
        
        const districtSelect = document.getElementById('district');
        districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
        
        province.districts.forEach(district => {
            const option = document.createElement('option');
            option.value = district.code;
            option.textContent = district.name;
            districtSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading districts:', error);
    }
}

// Hàm load danh sách phường/xã
async function loadWards(districtCode) {
    try {
        const response = await fetch(`https://provinces.open-api.vn/api/d/${districtCode}?depth=2`);
        const district = await response.json();
        
        const wardSelect = document.getElementById('ward');
        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        
        district.wards.forEach(ward => {
            const option = document.createElement('option');
            option.value = ward.code;
            option.textContent = ward.name;
            wardSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading wards:', error);
    }
}

// Thêm event listeners cho các select box
document.getElementById('province').addEventListener('change', function() {
    if (this.value) {
        loadDistricts(this.value);
        // Reset district và ward khi đổi tỉnh
        document.getElementById('district').value = '';
        document.getElementById('ward').value = '';
    }
});

document.getElementById('district').addEventListener('change', function() {
    if (this.value) {
        loadWards(this.value);
        // Reset ward khi đổi quận/huyện
        document.getElementById('ward').value = '';
    }
});
</script>
