<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

include __DIR__ . '/../../Admin/header.php';

$errors = [];
$success = false;

// Lấy ID sản phẩm cần sửa
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: ' . BASE_URL . '/admin/product');
    exit;
}

// Lấy thông tin sản phẩm
$product = Product::find($id);
if (!$product) {
    header('Location: ' . BASE_URL . '/admin/product');
    exit;
}

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Lấy dữ liệu từ form
        $name = trim($_POST['name'] ?? '');
        $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
        $brand_id = !empty($_POST['brand_id']) ? $_POST['brand_id'] : null;
        $status = $_POST['status'];
        $description = trim($_POST['description'] ?? '');
        $detail = trim($_POST['detail'] ?? '');

        // Validate dữ liệu
        if (empty($name)) {
            $errors['name'] = 'Tên sản phẩm không được để trống';
        } 

        // Xử lý upload ảnh
        $image = $product->image; // Giữ nguyên ảnh cũ nếu không upload ảnh mới
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
            $product->name = $name;
            $product->slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            $product->category_id = $category_id;
            $product->brand_id = $brand_id;
            $product->status = $status;
            $product->description = $description;
            $product->detail = $detail;
            $product->image = $image;
            $product->save();

            $success = true;
            echo "<script>
                Swal.fire({
                    title: 'Thành công!',
                    text: 'Sản phẩm đã được cập nhật thành công',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '" . BASE_URL . "/admin/product';
                    }
                });
            </script>";
            exit;
        }

    } catch (Exception $e) {
        $errors['system'] = $e->getMessage();
    }
}

// Lấy danh sách brands
$brands = Brand::where('status', 1)->get();

// Lấy danh sách categories gốc (không có parent)
$categories = Category::where('status', 1)
    ->whereNull('parent_id')
    ->with('children')
    ->get();

// Hàm đệ quy để hiển thị danh mục con
function displayCategories($categories, $level = 0, $selectedCategoryId = null) {
    foreach($categories as $category) {
        $prefix = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
        $selected = ($category->id == $selectedCategoryId) ? 'selected' : '';
        $disabled = ($category->status == 0) ? 'disabled' : '';
        
        echo '<option value="' . $category->id . '" ' . $selected . ' ' . $disabled . '>' 
            . $prefix . $category->name 
            . '</option>';
        
        // Hiển thị danh mục con nếu có
        if($category->children && $category->children->count() > 0) {
            displayCategories($category->children, $level + 1, $selectedCategoryId);
        }
    }
}
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Sửa sản phẩm</h4>
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
                        <form action="" method="POST" enctype="multipart/form-data">
                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                               name="name" value="<?php echo $product->name ?>">
                                        <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Danh mục</label>
                                    <div class="col-sm-10">
                                        <select class="form-select <?php echo isset($errors['category_id']) ? 'is-invalid' : ''; ?>" name="category_id">
                                            <?php displayCategories($categories, 0, $product->category_id); ?>
                                        </select>
                                        <?php if (isset($errors['category_id'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['category_id']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Thương hiệu</label>
                                    <div class="col-sm-10">
                                        <select class="form-select" name="brand_id">
                                            <?php foreach ($brands as $brand) : ?>
                                            <option value="<?php echo $brand->id; ?>" <?php echo ($product->brand_id == $brand->id) ? 'selected' : ''; ?>><?php echo $brand->name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Chi tiết</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" name="detail" rows="3"><?php echo $product->detail; ?></textarea>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Mô tả</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" name="description" rows="3"><?php echo $product->description; ?></textarea>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Ảnh</label>
                                    <div class="col-sm-10">
                                        <?php if ($product->image): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo BASE_URL . '/public/' . $product->image; ?>" alt="Current image" style="max-width: 200px;">
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" 
                                               name="image">
                                        <small class="text-muted">Chấp nhận file: jpg, jpeg, png, gif (tối đa 5MB)</small>
                                        <?php if (isset($errors['image'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['image']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Trạng thái</label>
                                    <div class="col-sm-10">
                                        <select class="form-select" name="status">
                                            <option value="1" <?php echo ($product->status == 1) ? 'selected' : ''; ?>>Hoạt động</option>
                                            <option value="0" <?php echo ($product->status == 0) ? 'selected' : ''; ?>>Không hoạt động</option>
                                        </select>
                                    </div>
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