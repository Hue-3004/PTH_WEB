<?php

use App\Models\User;

include __DIR__ . '/../../Admin/header.php';
include __DIR__ . '/../../config/database.php';

// Get user ID from URL parameter
$user_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$user_id) {
    header('Location: index.php');
    exit();
}

// Fetch user data using Eloquent
$user = User::find($user_id);

if (!$user) {
    header('Location: index.php');
    exit();
}
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Chi tiết tài khoản</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center mb-4">
                                    <div class="avatar-container position-relative">
                                        <div class="avatar-wrapper">
                                            <?php if (!empty($user->avatar)): ?>
                                                <img src="<?php echo BASE_URL .'/public/'. $user->avatar; ?>" alt="Avatar" class="img-fluid rounded-circle avatar-image" style="width: 200px; height: 200px; object-fit: cover; border: 4px solid #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
                                            <?php else: ?>
                                                <img src="<?php echo BASE_URL . '/public/uploads/avatars/default-avatar.png'; ?>" alt="Default Avatar" class="img-fluid rounded-circle avatar-image" style="width: 200px; height: 200px; object-fit: cover; border: 4px solid #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
                                            <?php endif; ?>
                                        </div>
                                        <div class="avatar-status mt-3">
                                            <?php if ($user->status == 1): ?>
                                                <span class="badge bg-success p-2 px-3 rounded-pill">
                                                    <i class="fas fa-check-circle me-1"></i> Đang hoạt động
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger p-2 px-3 rounded-pill">
                                                    <i class="fas fa-times-circle me-1"></i> Không hoạt động
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th style="width: 200px;">Họ và tên</th>
                                                    <td><?php echo $user->name ?? 'Không có'; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Email</th>
                                                    <td><?php echo $user->email ?? 'Không có'; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Tên đăng nhập</th>
                                                    <td><?php echo $user->username ?? 'Không có'; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Ngày sinh</th>
                                                    <td><?php echo $user->birthday ?? 'Không có'; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Giới tính</th>
                                                    <td><?php echo $user->gender == 'male' ? 'Nam' : 'Nữ'; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Địa chỉ</th>
                                                    <td><?php echo $user->address ?? 'Không có'; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Số điện thoại</th>
                                                    <td><?php echo $user->phone ?? 'Không có'; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Vai trò</th>
                                                    <td>
                                                        <?php
                                                        if ($user->role == 'admin') {
                                                            echo 'Admin';
                                                        } else {
                                                            echo 'Khách hàng';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Ngày tạo</th>
                                                    <td><?php echo $user->created_at ?? 'Không có'; ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12 d-flex justify-content-end">
                                    <a href="<?php echo BASE_URL . '/admin/user'; ?>" class="btn btn-secondary">Quay lại</a>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../Admin/footer.php'; ?>