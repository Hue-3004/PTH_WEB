<?php

use App\Models\Brand;
use App\Models\User;

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
                            <h4 class="card-title mb-0 flex-grow-1">Tài khoản</h4>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div class="live-preview">
                                <div class="table-responsive table-card">
                                    <table class="table align-middle table-nowrap table-striped-columns mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">STT</th>
                                                <th scope="col">Tên </th>
                                                <th scope="col">Tên đăng nhập </th>
                                                <th scope="col">Email </th>
                                                <th scope="col">SĐT </th>
                                                <th scope="col">Vai trò</th>
                                                <th scope="col">Trạng thái</th>
                                                <th scope="col" style="width: 150px;">Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $users = User::all();
                                            $stt = 0;
                                            foreach ($users as $user) :
                                                $stt++;
                                            ?>
                                                <tr>
                                                    <td><?=  $stt; ?></td>
                                                    <td><?=  $user->name; ?></td>
                                                    <td><?=  $user->username; ?></td>
                                                    <td><?=  $user->email ?? 'Không có'; ?></td>
                                                    <td><?=  $user->phone ?? 'Không có'; ?></td>
                                                    <td><?=  $user->role; ?></td>
                                                    <td>
                                                        <?php if ($user->status == 1) : ?>
                                                            <span class="badge bg-success">Hoạt động </span>
                                                        <?php else : ?>
                                                            <span class="badge bg-danger">Không hoạt động</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?php echo BASE_URL; ?>/admin/user/detail/<?php echo $user->id; ?>" class="btn btn-primary btn-sm">
                                                            Chi tiết
                                                        </a>
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
    $(document).on('click', '.delete-brand', function(e) {
        const brandId = $(this).data('id');
        
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
                    url: '<?php echo BASE_URL; ?>/admin/brand/delete/' + brandId,
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
                                'Thương hiệu đã được xóa thành công.',
                                'success'
                            ).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Lỗi!',
                                response.message || 'Không thể xóa thương hiệu này.',
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Delete error:', {xhr, status, error});
                        let errorMessage = 'Có lỗi xảy ra khi xóa thương hiệu.';
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