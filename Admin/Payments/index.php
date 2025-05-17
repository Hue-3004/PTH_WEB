<?php

use App\Models\Order;
use App\Models\OrderPayment;

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
                            <h4 class="card-title mb-0 flex-grow-1">Thanh toán</h4>
                        </div>

                        <div class="card-body">
                            <div class="live-preview">
                                <div class="table-responsive table-card">
                                    <table class="table align-middle table-nowrap table-striped-columns mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">STT</th>
                                                <th scope="col">Mã đơn hàng</th>
                                                <th scope="col">Tổng tiền</th>
                                                <th scope="col">Phương thức thanh toán</th>
                                                <th scope="col">Ngày thanh toán</th>
                                                <th scope="col">Trạng thái</th>
                                                <!-- <th scope="col" style="width: 150px;">Hành động</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $payments = OrderPayment::with('order')->orderBy('id', 'desc')->get();
                                            $stt = 0;
                                            foreach ($payments as $payment) :
                                                $stt++;
                                            ?>
                                                <tr>
                                                    <td><?= $stt; ?></td>
                                                    <td><?= $payment->order->order_code; ?></td>
                                                    <td><?= number_format($payment->amount, 0, ',', '.') ?> VNĐ</td>
                                                    <td><?php 
                                                        switch ($payment->payment_method) {
                                                            case 'cod':
                                                                echo 'Thanh toán khi nhận hàng';
                                                                break;
                                                            case 'vnpay':
                                                                echo 'Thanh toán VNPAY';
                                                                break;
                                                        }
                                                    ?></td>
                                                    <td><?= $payment->payment_date; ?></td>
                                                    <td>
                                                        <?php 
                                                        switch ($payment->payment_status) {
                                                            case 'pending':
                                                                echo '<span class="badge bg-primary">Chờ thanh toán</span>';
                                                                break;
                                                            case 'completed':
                                                                echo '<span class="badge bg-success">Đã thanh toán</span>';
                                                                break;
                                                            case 'failed':
                                                                echo '<span class="badge bg-danger">Thất bại</span>';
                                                                break;
                                                        }
                                                    ?></td>
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

<?php include __DIR__ . '/../../Admin/footer.php';
?>