<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

include __DIR__ . '/../../Admin/header.php';

$errors = [];

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate từng biến thể
        $variants = [];
        $errorMessages = [];

        for ($i = 0; $i < count($_POST['size']); $i++) {
            $hasErrors = false;
            $size = trim($_POST['size'][$i] ?? '');
            $color = trim($_POST['color'][$i] ?? '');
            $stock = intval($_POST['stock_quantity'][$i] ?? 0);
            $priceOld = floatval($_POST['price_old'][$i] ?? 0);
            $priceNew = floatval($_POST['price_new'][$i] ?? 0);

            // Validate dữ liệu
            if (empty($size)) {
                $errorMessages[] = "Biến thể " . ($i + 1) . ": Size không được để trống";
                $hasErrors = true;
            }
            if (empty($color)) {
                $errorMessages[] = "Biến thể " . ($i + 1) . ": Color không được để trống";
                $hasErrors = true;
            }
            if ($stock < 0) {
                $errorMessages[] = "Biến thể " . ($i + 1) . ": Số lượng không được âm";
                $hasErrors = true;
            }
            if ($priceOld < 0) {
                $errorMessages[] = "Biến thể " . ($i + 1) . ": Giá cũ không được âm";
                $hasErrors = true;
            }
            if ($priceNew < 0) {
                $errorMessages[] = "Biến thể " . ($i + 1) . ": Giá mới không được âm";
                $hasErrors = true;
            }
            if ($priceNew > $priceOld) {
                $errorMessages[] = "Biến thể " . ($i + 1) . ": Giá mới không được lớn hơn giá cũ";
                $hasErrors = true;
            }

            // Validate ảnh
            if (isset($_FILES['image']['name'][$i]) && $_FILES['image']['error'][$i] === UPLOAD_ERR_OK) {
                $fileExtension = strtolower(pathinfo($_FILES['image']['name'][$i], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($fileExtension, $allowedExtensions)) {
                    $errorMessages[] = "Biến thể " . ($i + 1) . ": Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif)";
                    $hasErrors = true;
                }

                if ($_FILES['image']['size'][$i] > 5 * 1024 * 1024) {
                    $errorMessages[] = "Biến thể " . ($i + 1) . ": Kích thước file không được vượt quá 5MB";
                    $hasErrors = true;
                }
            } elseif ($i === 0) {
                $errorMessages[] = "Biến thể " . ($i + 1) . ": Vui lòng chọn ảnh";
                $hasErrors = true;
            }

            // Nếu không có lỗi, thêm vào mảng variants
            if (!$hasErrors) {
                $variants[] = [
                    'size' => $size,
                    'color' => $color,
                    'stock_quantity' => $stock,
                    'price_old' => $priceOld,
                    'price_new' => $priceNew,
                    // Store temporary image path for later processing if needed
                    'image_tmp_name' => isset($_FILES['image']['tmp_name'][$i]) ? $_FILES['image']['tmp_name'][$i] : null,
                    'image_name' => isset($_FILES['image']['name'][$i]) ? $_FILES['image']['name'][$i] : null
                ];
            }
        }

        if (!empty($errorMessages)) {
            echo "<script>
                Swal.fire({
                    title: 'Lỗi!',
                    html: '" . implode("<br>", $errorMessages) . "',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>";
        } else {
            // Xử lý upload ảnh và lưu vào database
            $uploadDir = __DIR__ . '/../../public/uploads/products/';
            
            // Tạo thư mục nếu chưa tồn tại
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Lấy product_id từ URL hoặc form
            $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$product_id) {
                throw new Exception("Không tìm thấy sản phẩm");
            }

            // Kiểm tra sản phẩm tồn tại
            $product = Product::find($product_id);
            if (!$product) {
                throw new Exception("Sản phẩm không tồn tại");
            }

            // Bắt đầu transaction
            // DB::beginTransaction();
            try {
                foreach ($variants as $index => $variant) {
                    // Upload ảnh
                    $image = '';
                    if (isset($variant['image_tmp_name']) && $variant['image_tmp_name']) {
                        $fileExtension = strtolower(pathinfo($variant['image_name'], PATHINFO_EXTENSION));
                        $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
                        $uploadFile = $uploadDir . $fileName;

                        if (move_uploaded_file($variant['image_tmp_name'], $uploadFile)) {
                            $image = 'uploads/products/' . $fileName;
                        }
                    }

                    // Tạo biến thể mới
                    $productVariant = new ProductVariant();
                    $productVariant->product_id = $product_id;
                    
                    // Chuyển đổi size và color thành dạng không dấu và viết hoa
                    $size = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($variant['size']));
                    $color = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($variant['color']));
                    
                    // Tạo SKU với format: SP + product_id + 2 ký tự size + 2 ký tự color + 4 số ngẫu nhiên
                    $sku = 'SP' . $product_id . 
                           substr($size, 0, 2) . 
                           substr($color, 0, 2) . 
                           str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                    
                    $productVariant->sku = $sku;
                    $productVariant->size = $variant['size'];
                    $productVariant->color = $variant['color'];
                    $productVariant->image = $image;
                    $productVariant->stock_quantity = $variant['stock_quantity'];
                    $productVariant->price_old = $variant['price_old'];
                    $productVariant->price_new = $variant['price_new'];
                    $productVariant->save();

                    // Cập nhật tổng số lượng trong bảng products
                    $product = Product::find($product_id);
                    if ($product) {
                        // Lấy tổng số lượng từ tất cả các biến thể
                        $totalQuantity = ProductVariant::where('product_id', $product_id)
                            ->sum('stock_quantity');
                        
                        // Cập nhật số lượng trong bảng products
                        $product->quantity = $totalQuantity;
                        $product->save();
                    }
                }

                // Commit transaction
                // DB::commit();

                // Thông báo thành công
                echo "<script>
                    Swal.fire({
                        title: 'Thành công!',
                        text: 'Biến thể sản phẩm đã được thêm mới thành công',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '" . BASE_URL . "/admin/product';
                        }
                    });
                </script>";

            } catch (Exception $e) {
                // Rollback transaction nếu có lỗi
                // DB::rollBack();
                throw $e;
            }
        }

    } catch (Exception $e) {
        echo "<script>
            Swal.fire({
                title: 'Lỗi!',
                text: '" . $e->getMessage() . "',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Thêm sản phẩm biến thể</h4>
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
                                        <input type="text" class="form-control" name="size[]" placeholder="Nhập size">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Color <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="color[]" placeholder="Nhập màu">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Ảnh <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" name="image[]" accept="image/*">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="stock_quantity[]" value="0" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Giá cũ <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="price_old[]" placeholder="Nhập giá cũ" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Giá mới <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="price_new[]" placeholder="Nhập giá mới" min="0">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-info btn-sm copy-row" title="Sao chép">
                                                <i class="ri-file-copy-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm delete-row" title="Xóa">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="variant-container"></div>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <button type="button" class="btn btn-success" id="add-variant">
                                            <i class="fas fa-plus"></i> Thêm biến thể
                                        </button>
                                    </div>
                                </div>

                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const container = document.getElementById('variant-container');
                                    const addButton = document.getElementById('add-variant');
                                    const form = document.querySelector('form');

                                    // Kiểm tra sự tồn tại của các elements
                                    if (!container || !addButton || !form) {
                                        console.error('Required elements not found');
                                        return;
                                    }

                                    const template = document.querySelector('.row.mb-3').cloneNode(true);

                                    // Hàm validate số
                                    function validateNumber(input) {
                                        const value = parseFloat(input.value);
                                        if (isNaN(value) || value < 0) {
                                            input.value = 0;
                                        }
                                    }

                                    // Hàm validate form
                                    function validateForm() {
                                        const rows = document.querySelectorAll('.row.mb-3');
                                        let isValid = true;
                                        let errorMessage = '';

                                        rows.forEach((row, index) => {
                                            const size = row.querySelector('input[name="size[]"]')?.value.trim() || '';
                                            const color = row.querySelector('input[name="color[]"]')?.value.trim() || '';
                                            const image = row.querySelector('input[name="image[]"]')?.files[0];
                                            const stock = row.querySelector('input[name="stock_quantity[]"]')?.value || '0';
                                            const priceOld = row.querySelector('input[name="price_old[]"]')?.value || '0';
                                            const priceNew = row.querySelector('input[name="price_new[]"]')?.value || '0';

                                            if (!size) {
                                                errorMessage += `- Biến thể ${index + 1}: Size không được để trống<br>`;
                                                isValid = false;
                                            }
                                            if (!color) {
                                                errorMessage += `- Biến thể ${index + 1}: Color không được để trống<br>`;
                                                isValid = false;
                                            }
                                            if (!image && index === 0) {
                                                errorMessage += `- Biến thể ${index + 1}: Vui lòng chọn ảnh<br>`;
                                                isValid = false;
                                            }
                                            if (parseFloat(stock) < 0) {
                                                errorMessage += `- Biến thể ${index + 1}: Số lượng không được âm<br>`;
                                                isValid = false;
                                            }
                                            if (parseFloat(priceOld) < 0) {
                                                errorMessage += `- Biến thể ${index + 1}: Giá cũ không được âm<br>`;
                                                isValid = false;
                                            }
                                            if (parseFloat(priceNew) < 0) {
                                                errorMessage += `- Biến thể ${index + 1}: Giá mới không được âm<br>`;
                                                isValid = false;
                                            }
                                            if (parseFloat(priceNew) > parseFloat(priceOld)) {
                                                errorMessage += `- Biến thể ${index + 1}: Giá mới không được lớn hơn giá cũ<br>`;
                                                isValid = false;
                                            }
                                        });

                                        if (!isValid) {
                                            Swal.fire({
                                                title: 'Lỗi!',
                                                html: errorMessage,
                                                icon: 'error',
                                                confirmButtonText: 'OK'
                                            });
                                        }

                                        return isValid;
                                    }

                                    // Hàm tạo row mới
                                    function createNewRow() {
                                        const newRow = template.cloneNode(true);
                                        newRow.querySelectorAll('input').forEach(input => {
                                            input.value = '';
                                            if (input.type === 'number') {
                                                input.value = '0';
                                            }
                                        });
                                        return newRow;
                                    }

                                    // Thêm row mới
                                    addButton.addEventListener('click', function() {
                                        const newRow = createNewRow();
                                        container.appendChild(newRow);
                                        setupRowEvents(newRow);
                                    });

                                    // Xử lý sự kiện cho mỗi row
                                    function setupRowEvents(row) {
                                        // Validate số khi nhập
                                        row.querySelectorAll('input[type="number"]').forEach(input => {
                                            input.addEventListener('input', function() {
                                                validateNumber(this);
                                            });
                                        });

                                        // Xử lý nút copy
                                        const copyBtn = row.querySelector('.copy-row');
                                        if (copyBtn) {
                                            copyBtn.addEventListener('click', function() {
                                                const newRow = row.cloneNode(true);
                                                container.appendChild(newRow);
                                                setupRowEvents(newRow);
                                            });
                                        }

                                        // Xử lý nút delete
                                        const deleteBtn = row.querySelector('.delete-row');
                                        if (deleteBtn) {
                                            deleteBtn.addEventListener('click', function() {
                                                if (container.children.length > 0 || row.parentElement === container) {
                                                    row.remove();
                                                }
                                            });
                                        }
                                    }

                                    // Validate form trước khi submit
                                    form.addEventListener('submit', function(e) {
                                        if (!validateForm()) {
                                            e.preventDefault();
                                        }
                                    });

                                    // Thiết lập sự kiện cho row gốc
                                    const originalRow = document.querySelector('.row.mb-3');
                                    if (originalRow) {
                                        setupRowEvents(originalRow);
                                    }
                                });
                                </script>

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