<?php

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\ProductVariant;

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
                            <h4 class="card-title mb-0 flex-grow-1">Đơn hàng </h4>
                        </div>

                        <div class="card-body">
                            <div class="live-preview">
                                <div class="table-responsive table-card">
                                <table class="table align-middle table-nowrap table-striped-columns mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Mã đơn hàng</th>
                                                <th scope="col">Người đặt </th>
                                                <th scope="col">Tổng tiền</th>
                                                <th scope="col">Ngày đặt</th>
                                                <th scope="col">Địa chỉ</th>
                                                <th scope="col">Phương thức thanh toán</th>
                                                <th scope="col">Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $order = Order::with(['user', 'orderItems'])->find($_GET['id']);
                                            
                                            // echo "<pre>";
                                            // print_r($order);
                                            // echo "</pre>";
                                            // die();
                                            $address = Address::where('user_id', $order->user_id)->first();
                                            $payment = OrderPayment::where('order_id', $order->id)->first();
                                            ?>
                                                <tr>
                                                    <td><?= $order->order_code; ?></td>
                                                    <td><?= $order->user->name; ?></td>
                                                    <td><?= number_format($order->total_amount, 0, ',', '.') ?> VNĐ</td>
                                                    <td><?= $order->order_date; ?></td>
                                                    <td><?= $address->street ?></td>
                                                    <td>
                                                        <?php
                                                        if ($payment->payment_method == 'cod') {
                                                            echo 'Thanh toán khi nhận hàng';
                                                        } else {
                                                            echo 'Thanh toán VNPAY';
                                                        }
                                                        ?>
                                                    </td>
                                                
                                                    <td>
                                                        <?php 
                                                        switch ($order->status) {
                                                            case 'pending':
                                                                echo '<span class="badge bg-primary">Chờ xử lý</span>';
                                                                break;
                                                            case 'processing':
                                                                echo '<span class="badge bg-info">Đang xử lý</span>';
                                                                break;
                                                            case 'shipped':
                                                                echo '<span class="badge bg-primary">Đang giao hàng</span>';
                                                                break;  
                                                            case 'delivered':
                                                                echo '<span class="badge bg-success">Đã giao hàng</span>';
                                                                break;
                                                            case 'cancelled':
                                                                echo '<span class="badge bg-danger">Đã hủy</span>';
                                                                break;     
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div><!-- end col -->
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Chi tiết đơn hàng </h4>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div class="live-preview">
                                <div class="table-responsive table-card">
                                <table class="table align-middle table-nowrap table-striped-columns mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">STT</th>
                                                <th scope="col">Mã sản phẩm</th>
                                                <th scope="col">Tên sản phẩm</th>
                                                <th scope="col">Số lượng</th>
                                                <th scope="col">Giá</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $orderItems = OrderItem::with(['variant'])->where('order_id', $order->id)->get();
                                            // echo "<pre>";
                                            // print_r($orderItems);
                                            // echo "</pre>";
                                            // die();
                                            $stt = 0;
                                            foreach ($orderItems as $orderItem) :
                                                $stt++;
                                            ?>
                                                <tr>
                                                    <td><?=  $stt; ?></td>
                                                    <td><?=  $orderItem->variant->sku; ?></td>
                                                    <td><?=  $orderItem->variant->product->name; ?></td>
                                                    <td><?=  $orderItem->quantity; ?></td>
                                                    <td><?= number_format($orderItem->unit_price, 0, ',', '.') ?> VNĐ</td>
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

<!-- Thêm script xử lý xóa danh mục -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $(document).on('click', '.delete-productVariant', function(e) {
            const productVariantId = $(this).data('id');
            console.log(productVariantId);
            

            Swal.fire({
                title: 'Bạn có chắc chắn?',
                text: "Bạn không thể hoàn tác sau khi xóa!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Có, xóa nó!',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Gửi request xóa
                    $.ajax({
                        url: '<?php echo BASE_URL; ?>/admin/product/variant/delete/' + productVariantId,
                        type: 'POST',
                        data: {
                            _method: 'DELETE'
                        },
                        dataType: 'json',
                        success: function(response) {
                            console.log('Delete response:', response);
                            if (response.success) {
                                Swal.fire(
                                    'Đã xóa!',
                                    'Sản phẩm đã được xóa thành công.',
                                    'success'
                                ).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Lỗi!',
                                    response.message || 'Không thể xóa sản phẩm này.',
                                    'error'
                                );
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Delete error:', {
                                xhr,
                                status,
                                error
                            });
                            let errorMessage = 'Có lỗi xảy ra khi xóa sản phẩm.';
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                console.error('Error parsing response:', e);
                            }
                            Swal.fire(
                                'Lỗi!',
                                errorMessage,
                                'error'
                            );
                        }
                    });
                }
            });
        });
    });
</script>

<?php include __DIR__ . '/../../Admin/footer.php';
?>