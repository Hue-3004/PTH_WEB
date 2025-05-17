<?php

use App\Models\System;

include __DIR__ . '/../../Admin/header.php';

$errors = [];
$success = false;

// Lấy ID 
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: ' . BASE_URL . '/admin/system');
    exit;
}

// Lấy thông tin bài viết
$system = System::find($id);
if (!$system) {
    header('Location: ' . BASE_URL . '/admin/system');
    exit;
}

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Lấy dữ liệu từ form
        $site_name = trim($_POST['site_name'] ?? '');
        $hotline = trim($_POST['hotline'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');

        // Validate dữ liệu
        if (
            (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK)
            && empty($system->logo)
        ) {
            $errors['logo'] = 'Logo không được để trống';
        }
        if (empty($site_name)) {
            $errors['site_name'] = 'Tên trang không được để trống';
        }
        if (empty($hotline)) {
            $errors['hotline'] = 'Điện thoại không được để trống';
        }
        if (empty($email)) {
            $errors['email'] = 'Email không được để trống';
        }
        if (empty($address)) {
            $errors['address'] = 'Địa chỉ không được để trống';
        }

        // Xử lý upload ảnh
        $logo = $system->logo; // Giữ nguyên ảnh cũ nếu không upload ảnh mới
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/systems/';

            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    $errors['logo'] = 'Không thể tạo thư mục upload. Vui lòng kiểm tra quyền truy cập.';
                }
            }

            $fileExtension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors['logo'] = 'Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif)';
            }

            // Kiểm tra kích thước file (giới hạn 5MB)
            if ($_FILES['logo']['size'] > 5 * 1024 * 1024) {
                $errors['logo'] = 'Kích thước file không được vượt quá 5MB';
            }

            if (empty($errors)) {
                $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
                $uploadFile = $uploadDir . $fileName;

                if (!is_writable($uploadDir)) {
                    $errors['logo'] = 'Thư mục upload không có quyền ghi. Vui lòng kiểm tra quyền truy cập.';
                }

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadFile)) {
                    // Xóa ảnh cũ nếu tồn tại
                    if ($system->logo && file_exists(__DIR__ . '/../../public/' . $system->logo)) {
                        unlink(__DIR__ . '/../../public/' . $system->logo);
                    }
                    $logo = 'uploads/systems/' . $fileName;
                } else {
                    $errors['logo'] = 'Không thể upload file. Vui lòng kiểm tra quyền truy cập.';
                }
            }
        }

        // Nếu không có lỗi thì cập nhật vào database
        if (empty($errors)) {
            $system->logo = $logo;
            $system->site_name = $site_name;
            $system->hotline = $hotline;
            $system->email = $email;
            $system->address = $address;
            $system->save();

            $success = true;
            echo "<script>
                Swal.fire({
                    title: 'Thành công!',
                    text: 'Hệ thống đã được cập nhật thành công',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '" . BASE_URL . "/admin/system';
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
                        <h4 class="mb-sm-0">Sửa hệ thống</h4>
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
                                    <label class="col-sm-2 col-form-label">Logo </label>
                                    <div class="col-sm-10">
                                        <img src="<?php echo BASE_URL; ?>/public/<?php echo $system->logo; ?>" alt="Logo" class="img-fluid">
                                        <input type="file" class="form-control" <?php echo isset($errors['logo']) ? 'is-invalid' : ''; ?> name="logo" id="logo">
                                        <small class="text-muted">Chấp nhận file: jpg, jpeg, png, gif (tối đa 5MB)</small>
                                        <?php if (isset($errors['logo'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['logo']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Tên trang </label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" <?php echo isset($errors['site_name']) ? 'is-invalid' : ''; ?> name="site_name" value="<?php echo $system->site_name; ?>">
                                        <?php if (isset($errors['site_name'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['site_name']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Điện thoại</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" <?php echo isset($errors['hotline']) ? 'is-invalid' : ''; ?> name="hotline" value="<?php echo $system->hotline; ?>">
                                        <?php if (isset($errors['hotline'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['hotline']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Email: </label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?> name="email" value="<?php echo $system->email; ?>">
                                        <?php if (isset($errors['email'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Địa chỉ: </label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?> name="address" value="<?php echo $system->address; ?>">
                                        <?php if (isset($errors['address'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['address']; ?></div>
                                        <?php endif; ?>
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