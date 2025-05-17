<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

include __DIR__ . '/../../Admin/header.php';

$errors = [];
$success = false;

// Lấy ID sản phẩm biến thể cần sửa
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: ' . BASE_URL . '/admin/product/variant/' . $product_id);
    exit;
}
$productVariant = ProductVariant::where('id', $id)->first();
// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Lấy dữ liệu từ form
        $size = trim($_POST['size'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $stock_quantity = trim($_POST['stock_quantity'] ?? '');
        $price_old = trim($_POST['price_old'] ?? '');
        $price_new = trim($_POST['price_new'] ?? '');

        // Validate dữ liệu
        if (empty($size)) {
            $errors['size'] = 'Size không được để trống';
        }
        if (empty($color)) {
            $errors['color'] = 'Color không được để trống';
        }
        if (empty($stock_quantity)) {
            $errors['stock_quantity'] = 'Số lượng không được để trống';
        }
        if (empty($price_old)) {
            $errors['price_old'] = 'Giá cũ không được để trống';
        }
        if (empty($price_new)) {
            $errors['price_new'] = 'Giá mới không được để trống';
        }

        // Xử lý upload ảnh
        $image = $productVariant->image; // Giữ nguyên ảnh cũ nếu không upload ảnh mới
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/products/';

            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    $errors['image'] = 'Không thể tạo thư mục upload. Vui lòng kiểm tra quyền truy cập.';
                }
            }

            $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors['image'] = 'Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif)';
            }

            // Kiểm tra kích thước file (giới hạn 5MB)
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $errors['image'] = 'Kích thước file không được vượt quá 5MB';
            }

            if (empty($errors)) {
                $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
                $uploadFile = $uploadDir . $fileName;

                if (!is_writable($uploadDir)) {
                    $errors['image'] = 'Thư mục upload không có quyền ghi. Vui lòng kiểm tra quyền truy cập.';
                }

                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                    // Xóa ảnh cũ nếu tồn tại
                    if ($product->image && file_exists(__DIR__ . '/../../public/' . $product->image)) {
                        unlink(__DIR__ . '/../../public/' . $product->image);
                    }
                    $image = 'uploads/products/' . $fileName;
                } else {
                    $errors['image'] = 'Không thể upload file. Vui lòng kiểm tra quyền truy cập.';
                }
            }
        }

        // Nếu không có lỗi thì cập nhật vào database
        if (empty($errors)) {
            $productVariant->size = $size;
            $productVariant->color = $color;
            $productVariant->stock_quantity = $stock_quantity;
            $productVariant->price_old = $price_old;
            $productVariant->price_new = $price_new;
            $productVariant->image = $image;
            $productVariant->save();

            // Cập nhật tổng số lượng trong bảng products
            if (isset($productVariant) && !empty($productVariant->product_id)) {
                $productIdToUpdate = $productVariant->product_id;
                $productToUpdate = Product::find($productIdToUpdate);

                if ($productToUpdate) {
                    $totalQuantity = ProductVariant::where('product_id', $productIdToUpdate)
                                        ->sum('stock_quantity');
                    $productToUpdate->quantity = $totalQuantity;
                    $productToUpdate->save();
                }
            }

            $success = true;
            echo "<script>
                Swal.fire({
                    title: 'Thành công!',
                    text: 'Biến thể đã được cập nhật thành công',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '" . BASE_URL . "/admin/product/variant/" . $productVariant->product_id . "';
                    }
                });
            </script>";
            exit;
        }
    } catch (Exception $e) {
        $errors['system'] = $e->getMessage();
    }
}


?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Sửa sản phẩm biến thể</h4>
                    </div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data" id="variantForm">
                                <div class="row mb-3 d-flex align-items-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Size <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="size" placeholder="Nhập size" value="<?= $productVariant->size ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Color <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="color" placeholder="Nhập màu" value="<?= $productVariant->color ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Ảnh <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" name="image" accept="image/*" value="<?= $productVariant->image ?>">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="stock_quantity" value="<?= $productVariant->stock_quantity ?>" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Giá cũ <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="price_old" placeholder="Nhập giá cũ" min="0" value="<?= intval($productVariant->price_old) ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Giá mới <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="price_new" placeholder="Nhập giá mới" min="0" value="<?= intval($productVariant->price_new) ?>">
                                    </div>
                                </div>

                                <div class="row mb-3 d-flex align-items-center">
                                    <div class="col-md-2"></div>
                                    <div class="col-md-2"></div>
                                    <div class="col-md-3">
                                    <img src="<?= BASE_URL . '/public/' . $productVariant->image ?>" alt="" class="img-fluid">
                                    </div>
                                    <div class="col-md-1"></div>
                                    <div class="col-md-2"></div>
                                    <div class="col-md-2"></div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">Lưu</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../Admin/footer.php';
?>