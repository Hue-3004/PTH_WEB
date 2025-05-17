<?php

use App\Models\Order;

include __DIR__ . '/../../Admin/header.php';
include __DIR__ . '/../../config/database.php';
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Đơn hàng</h4>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div class="live-preview">
                                <div class="table-responsive table-card">
                                    <table class="table align-middle table-nowrap table-striped-columns mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">STT</th>
                                                <th scope="col">Mã đơn hàng</th>
                                                <th scope="col">Người đặt </th>
                                                <th scope="col">Tổng tiền</th>
                                                <th scope="col">Ngày đặt</th>
                                                <th scope="col">Trạng thái</th>
                                                <th scope="col" style="width: 150px;">Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $orders = Order::orderBy('id', 'desc')->get();
                                            $stt = 0;
                                            foreach ($orders as $order) :
                                                $stt++;
                                            ?>
                                                <tr>
                                                    <td><?= $stt; ?></td>
                                                    <td><?= $order->order_code; ?></td>
                                                    <td><?= $order->user->name; ?></td>
                                                    <td><?= number_format($order->total_amount, 0, ',', '.') ?> VNĐ</td>
                                                    <td><?= $order->order_date; ?></td>
                                                    <td>
                                                        <select class="form-select status-select" name="status" data-order-id="<?= $order->id ?>" style="width: 150px;">
                                                            <option value="pending" <?= $order->status == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                                                            <option value="processing" <?= $order->status == 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                                                            <option value="shipped" <?= $order->status == 'shipped' ? 'selected' : '' ?>>Đang giao hàng</option>
                                                            <option value="delivered" <?= $order->status == 'delivered' ? 'selected' : '' ?>>Đã giao hàng</option>
                                                            <option value="cancelled" <?= $order->status == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?php echo BASE_URL; ?>/admin/order/detail/<?php echo $order->id; ?>" class="btn btn-primary btn-sm">
                                                            Xem chi tiết
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div><!-- end col -->
            </div>
        </div>
    </div>
</div>

<!-- Thêm thư viện SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelects = document.querySelectorAll('.status-select');
    
    // Hàm kiểm tra và disable các option không hợp lệ
    function updateStatusOptions(select) {
        const currentStatus = select.value;
        const options = select.options;
        
        // Reset tất cả options về trạng thái bình thường
        for (let option of options) {
            option.disabled = false;
        }
        
        // Disable các option không hợp lệ dựa trên trạng thái hiện tại
        switch(currentStatus) {
            case 'pending':
                // Đơn hàng mới chỉ có thể chuyển sang processing hoặc cancelled
                for (let option of options) {
                    if (option.value === 'shipped' || option.value === 'delivered') {
                        option.disabled = true;
                    }
                }
                break;
                
            case 'processing':
                // Đơn hàng đang xử lý có thể chuyển sang shipped hoặc cancelled
                for (let option of options) {
                    if (option.value === 'delivered' || option.value === 'pending') {
                        option.disabled = true;
                    }
                }
                break;
                
            case 'shipped':
                // Đơn hàng đang giao chỉ có thể chuyển sang delivered hoặc cancelled
                for (let option of options) {
                    if (option.value === 'pending' || option.value === 'processing') {
                        option.disabled = true;
                    }
                }
                break;
                
            case 'delivered':
                // Đơn hàng đã giao không thể chuyển sang trạng thái khác
                for (let option of options) {
                    if (option.value !== 'delivered') {
                        option.disabled = true;
                    }
                }
                break;
                
            case 'cancelled':
                // Đơn hàng đã hủy không thể chuyển sang trạng thái khác
                for (let option of options) {
                    if (option.value !== 'cancelled') {
                        option.disabled = true;
                    }
                }
                break;
        }
    }
    
    statusSelects.forEach(select => {
        // Cập nhật các option khi trang được load
        updateStatusOptions(select);
        
        select.addEventListener('change', function() {
            const orderId = this.dataset.orderId;
            const newStatus = this.value;
            
            // Hiển thị loading
            this.disabled = true;
            
            // Gửi request cập nhật trạng thái
            fetch(`<?php echo BASE_URL; ?>/admin/order/update-status/${orderId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hiển thị thông báo thành công
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    // Cập nhật lại các option sau khi thay đổi thành công
                    updateStatusOptions(this);
                } else {
                    // Hiển thị thông báo lỗi
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: data.message
                    });
                    // Reset về giá trị cũ
                    this.value = this.getAttribute('data-original-value');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Có lỗi xảy ra khi cập nhật trạng thái'
                });
                // Reset về giá trị cũ
                this.value = this.getAttribute('data-original-value');
            })
            .finally(() => {
                // Bỏ disabled
                this.disabled = false;
            });
        });
        
        // Lưu giá trị ban đầu
        select.setAttribute('data-original-value', select.value);
    });
});
</script>

<?php include __DIR__ . '/../../Admin/footer.php';
?>