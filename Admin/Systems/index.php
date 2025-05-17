<?php

use App\Models\System;

include __DIR__ . '/../../Admin/header.php';
include __DIR__ . '/../../config/database.php';

$system = System::first();
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Hệ thống</h4>
                        </div>

                        <div class="card-body">
                            <div class="live-preview">
                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Logo: </label>
                                    <div class="col-sm-10">
                                        <img src="<?php echo BASE_URL; ?>/public/<?php echo $system->logo; ?>" alt="Logo" class="img-fluid">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Tên trang: </label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" name="site_name" value="<?php echo $system->site_name; ?>" readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Điện thoại: </label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" name="hotline" value="<?php echo $system->hotline; ?>" readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Email: </label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" name="email" value="<?php echo $system->email; ?>" readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Địa chỉ: </label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" name="address" value="<?php echo $system->address; ?>" readonly>
                                    </div>
                                </div>

                                <div class="mb-3 text-end">
                                    <div class="">
                                        <a href="<?php echo BASE_URL; ?>/admin/system/edit/<?php echo $system->id; ?>" class="btn btn-primary btn-sm px-4">
                                            Sửa
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../Admin/footer.php';
?>