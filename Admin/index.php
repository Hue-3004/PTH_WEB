<?php
use App\Models\Order;
use App\Models\Product;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/header.php';
include __DIR__ . '/../config/database.php';


// Thống kê đơn hàng theo trạng thái
$pendingOrders = Order::where('status', 'pending')->count();
$processingOrders = Order::where('status', 'processing')->count();
$shippedOrders = Order::where('status', 'shipped')->count();
$deliveredOrders = Order::where('status', 'delivered')->count();
$cancelledOrders = Order::where('status', 'cancelled')->count();

// Thống kê sản phẩm
$totalProducts = Product::where('status', 1)->count();

// Sản phẩm bán chạy nhất
$topSellingProducts = Product::query()
    ->select('products.id', 'products.name', 'products.image', 'products.quantity')
    ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
    ->join('order_items', 'product_variants.id', '=', 'order_items.variant_id')
    ->join('orders', 'order_items.order_id', '=', 'orders.id')
    ->where('orders.status', '!=', 'cancelled')
    ->groupBy('products.id', 'products.name', 'products.image')
    ->selectRaw('SUM(order_items.quantity) as total_sold')
    ->orderByDesc('total_sold')
    ->take(5)
    ->get();

// Sản phẩm còn ít hàng (dưới 10 sản phẩm)
$lowStockProducts = Product::query()
    ->select('products.id', 'products.name', 'products.image', 'products.quantity')
    ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
    ->where('products.quantity', '<', 10)
    ->orderBy('products.quantity', 'asc')
    ->limit(5)
    ->get();
?>
<?php if (isset($_SESSION['login_success']) && $_SESSION['login_success']): ?>
   
<script>
    Swal.fire({
        icon: 'success',
        title: 'Đăng nhập thành công!',
        showConfirmButton: false,
        timer: 1500
    });
</script>
<?php unset($_SESSION['login_success']); endif; ?>

<div class="main-content">

    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col">

                    <div class="h-100">
                        <div class="row mb-3 pb-1">
                            <div class="col-12">
                                <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                                    <div class="flex-grow-1">
                                        <h4 class="fs-16 mb-1">Xin chào, <?= $_SESSION['admin_user']['name'] ?>!</h4>
                                        <p class="text-muted mb-0">Thống kê hệ thống</p>
                                    </div>
                                    
                                </div><!-- end card header -->
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->

                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-animate">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1 overflow-hidden">
                                                <p
                                                    class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                    Đơn hàng chờ xử lý</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-end justify-content-between mt-4">
                                            <div>
                                                <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= $pendingOrders ?></h4>
                                            </div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-warning-subtle rounded fs-3">
                                                    <i class="bx bx-time text-warning"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col -->

                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-animate">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1 overflow-hidden">
                                                <p
                                                    class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                    Đơn hàng đang xử lý</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-end justify-content-between mt-4">
                                            <div>
                                                <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= $processingOrders ?></h4>
                                            </div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-info-subtle rounded fs-3">
                                                    <i class="bx bx-refresh text-info"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col -->

                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-animate">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1 overflow-hidden">
                                                <p
                                                    class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                    Đơn hàng đang giao</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-end justify-content-between mt-4">
                                            <div>
                                                <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= $shippedOrders ?></h4>
                                            </div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-primary-subtle rounded fs-3">
                                                <i class="bx bxs-truck text-primary"></i> 
                                                </span>
                                            </div>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col -->

                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-animate">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1 overflow-hidden">
                                                <p
                                                    class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                    Đơn hàng đã giao</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-end justify-content-between mt-4">
                                            <div>
                                                <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= $deliveredOrders ?></h4>
                                            </div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-success-subtle rounded fs-3">
                                                    <i class="bx bx-check-circle text-success"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col -->
                        </div> <!-- end row-->

                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header align-items-center d-flex">
                                        <h4 class="card-title mb-0 flex-grow-1">Sản phẩm bán chạy nhất</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive table-card">
                                            <table class="table table-hover table-centered align-middle table-nowrap mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th scope="col">Sản phẩm</th>
                                                        <th scope="col">Hình ảnh</th>
                                                        <th scope="col">Đã bán</th>
                                                        <th scope="col">Tồn kho</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($topSellingProducts as $product): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-grow-1">
                                                                    <h5 class="fs-14 my-1"><?= $product->name ?></h5>
                                                                    <span class="text-muted"><?= $product->sku ?></span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <img src="<?= BASE_URL .'/public/'.$product->image ?>" alt="Hình ảnh sản phẩm" class="img-fluid rounded" style="width: 80px; height: 50px;">
                                                        </td>
                                                        <td><?= $product->total_sold ?></td>
                                                        <td><?= $product->quantity ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header align-items-center d-flex">
                                        <h4 class="card-title mb-0 flex-grow-1">Sản phẩm cần nhập thêm</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive table-card">
                                            <table class="table table-hover table-centered align-middle table-nowrap mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th scope="col">Sản phẩm</th>
                                                        <th scope="col">Hình ảnh</th>
                                                        <th scope="col">Tồn kho</th>
                                                        <th scope="col">Trạng thái</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($lowStockProducts as $product):?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-grow-1">
                                                                    <h5 class="fs-14 my-1"><?= $product->name ?></h5>
                                                                    
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <img src="<?= BASE_URL .'/public/'.$product->image ?>" alt="Hình ảnh sản phẩm" class="img-fluid rounded" style="width: 80px; height: 50px;">
                                                        </td>
                                                        <td><?= $product->quantity ?></td>
                                                        <td>
                                                            <?php if ($product->quantity <= 0): ?>
                                                                <span class="badge bg-danger">Hết hàng</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning">Sắp hết</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- end row-->

                    </div> <!-- end .h-100-->

                </div> <!-- end col -->
            </div>

        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
               
            </div>
        </div>
    </footer>
</div>

<?php
include __DIR__ . '/footer.php';
?>