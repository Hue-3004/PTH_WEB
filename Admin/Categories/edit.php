<?php

use App\Models\Category;

include __DIR__ . '/../../Admin/header.php';

$errors = [];
$success = false;

// Lấy ID danh mục cần sửa
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: ' . BASE_URL . '/admin/category');
    exit;
}

// Lấy thông tin danh mục
$category = Category::find($id);
if (!$category) {
    header('Location: ' . BASE_URL . '/admin/category');
    exit;
}

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // echo 1111111;
    try {
        // Lấy dữ liệu từ form
        $name = trim($_POST['name'] ?? '');
        $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
        $status = $_POST['status'];
        $description = trim($_POST['description'] ?? '');

        // Validate dữ liệu
        if (empty($name)) {
            $errors['name'] = 'Tên danh mục không được để trống';
        } elseif (strlen($name) < 2) {
            $errors['name'] = 'Tên danh mục phải có ít nhất 2 ký tự';
        } elseif (strlen($name) > 255) {
            $errors['name'] = 'Tên danh mục không được vượt quá 255 ký tự';
        }

        // Kiểm tra danh mục cha
        if (!empty($parent_id)) {
            $parentCategory = Category::find($parent_id);
            if (!$parentCategory) {
                $errors['parent_id'] = 'Danh mục cha không tồn tại';
            }
            // Kiểm tra không cho phép chọn chính nó làm danh mục cha
            if ($parent_id == $id) {
                $errors['parent_id'] = 'Không thể chọn chính nó làm danh mục cha';
            }
        }

        // Xử lý upload ảnh
        $image = $category->image; // Giữ nguyên ảnh cũ nếu không upload ảnh mới
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/categories/';
            
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
                    if ($category->image && file_exists(__DIR__ . '/../../public/' . $category->image)) {
                        unlink(__DIR__ . '/../../public/' . $category->image);
                    }
                    $image = 'uploads/categories/' . $fileName;
                } else {
                    $errors['image'] = 'Không thể upload file. Vui lòng kiểm tra quyền truy cập.';
                }
            }
        }

        // Nếu không có lỗi thì cập nhật vào database
        if (empty($errors)) {
            $category->name = $name;
            $category->slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            $category->parent_id = $parent_id;
            $category->status = $status;
            $category->description = $description;
            $category->image = $image;
            $category->save();

            $success = true;
            echo "<script>
                Swal.fire({
                    title: 'Thành công!',
                    text: 'Danh mục đã được cập nhật thành công',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '" . BASE_URL . "/admin/category';
                    }
                });
            </script>";
            exit;
        }

    } catch (Exception $e) {
        $errors['system'] = $e->getMessage();
    }
}

// Lấy danh sách categories gốc (không có parent)
$categories = Category::where('status', 1)
    ->whereNull('parent_id')
    ->with('children')
    ->get();

// Hàm đệ quy để hiển thị danh mục con
function displayCategories($categories, $level = 0, $currentId = null, $currentParentId = null) {
    foreach($categories as $category) {
        if ($category->id != $currentId) { // Không hiển thị chính nó trong danh sách
            $prefix = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
            $selected = ($category->id == $currentParentId) ? 'selected' : '';
            echo '<option value="' . $category->id . '" ' . $selected . '>' . $prefix . $category->name . '</option>';
            
            if($category->children->count() > 0) {
                displayCategories($category->children, $level + 1, $currentId, $currentParentId);
            }
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
                        <h4 class="mb-sm-0">Sửa danh mục</h4>
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
                                    <label class="col-sm-2 col-form-label">Tên danh mục <span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                               name="name" value="<?php echo $category->name ?>">
                                        <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Danh mục cha</label>
                                    <div class="col-sm-10">
                                        <select class="form-select <?php echo isset($errors['parent_id']) ? 'is-invalid' : ''; ?>" name="parent_id">
                                            <option value="">Chọn danh mục cha</option>
                                            <?php 
                                            // Lấy parent_id hiện tại của category
                                            $currentParentId = $category->parent_id;
                                            displayCategories($categories, 0, $category->id, $currentParentId); 
                                            ?>
                                        </select>
                                        <?php if (isset($errors['parent_id'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['parent_id']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Mô tả</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" name="description" rows="3"><?php echo $category->description ?></textarea>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Ảnh</label>
                                    <div class="col-sm-10">
                                        <?php if ($category->image): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo BASE_URL . '/public/' . $category->image; ?>" alt="Current image" style="max-width: 200px;">
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
                                            <option value="1" <?php echo $category->status == 1 ? 'selected' : ''; ?>>Hoạt động</option>
                                            <option value="0" <?php echo $category->status == 0 ? 'selected' : ''; ?>>Không hoạt động</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">Cập nhật</button>
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