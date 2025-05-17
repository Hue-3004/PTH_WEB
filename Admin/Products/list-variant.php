<?php

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
                            <h4 class="card-title mb-0 flex-grow-1">Sản phẩm </h4>
                        </div>

                        <div class="card-body">
                            <div class="live-preview">
                                <div class="table-responsive table-card">
                                    <table class="table align-middle table-nowrap table-striped-columns mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Tên </th>
                                                <th scope="col">Ảnh</th>
                                                <th scope="col">Danh mục</th>
                                                <th scope="col">Thương hiệu</th>
                                                <th scope="col">Số lượng</th>
                                                <th scope="col">Trạng thái</th>    
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $id = $_GET['id'];
                                            $product = Product::with('category', 'brand')->where('id', $id)->first();
                                            ?>
                                            <tr>
                                                <td><?= $product->name; ?></td>
                                                <td><img src="<?= BASE_URL; ?>/public/<?= $product->image; ?>" alt="" width="100"></td>
                                                <td><?= $product->category->name; ?></td>
                                                <td><?= $product->brand->name; ?></td>
                                                <td><?= $product->quantity; ?></td>
                                                <td>
                                                    <?php if ($product->status == 1) : ?>
                                                        <span class="badge bg-success">Hoạt động </span>
                                                    <?php else : ?>
                                                        <span class="badge bg-danger">Không hoạt động</span>
                                                    <?php endif; ?>
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
                            <h4 class="card-title mb-0 flex-grow-1">Sản phẩm biến thể</h4>

                            <div class="flex-shrink-0">
                                <div class="d-flex align-items-center gap-2">
                                    <a href="<?php echo BASE_URL; ?>/admin/product/variant/create/<?php echo $product->id; ?>" class="btn btn-primary">Thêm</a>
                                </div>
                            </div>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div class="live-preview">
                                <div class="table-responsive table-card">
                                <table class="table align-middle table-nowrap table-striped-columns mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Mã</th>
                                                <th scope="col">Màu sắc </th>
                                                <th scope="col">Kích thước</th>
                                                <th scope="col">Ảnh</th>
                                                <th scope="col">Số lượng</th>
                                                <th scope="col">Giá cũ</th>
                                                <th scope="col">Giá mới</th>
                                                <th scope="col" style="width: 150px;">Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $productVariants = ProductVariant::where('product_id', $product->id)->get();
                                            $stt = 0;
                                            foreach ($productVariants as $productVariant) :
                                                $stt++;
                                            ?>
                                                <tr>
                                                    <td><?=  $productVariant->sku; ?></td>
                                                    <td><?=  $productVariant->color; ?></td>
                                                    <td><?=  $productVariant->size; ?></td>
                                                    <td><img src="<?= BASE_URL; ?>/public/<?= $productVariant->image; ?>" alt="" width="100"></td>
                                                    <td><?=  $productVariant->stock_quantity; ?></td>
                                                    <td><?=  number_format($productVariant->price_old, 0, ',', '.'); ?></td>
                                                    <td><?=  number_format($productVariant->price_new, 0, ',', '.'); ?></td>
                                                    <td class="text-center">
                                                        <a href="<?php echo BASE_URL; ?>/admin/product/variant/edit/<?php echo $productVariant->id; ?>" class="btn btn-primary btn-sm">
                                                            Sửa
                                                        </a>
                                                        <button type="button" class="btn btn-danger btn-sm delete-productVariant" data-id="<?php echo $productVariant->id; ?>">
                                                            Xoá
                                                        </button>
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