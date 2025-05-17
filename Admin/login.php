<?php
session_start();
$system = \App\Models\System::first();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config.php';

use App\Models\User;

// Nếu đã đăng nhập, chuyển vào trang admin
if (isset($_SESSION['admin_user'])) {
    header('Location: ' . BASE_URL . '/admin');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
    } else {
        $user = User::where('username', $username)->where('role', 'admin')->first();
        if ($user && password_verify($password, $user->password)) {
            // Đăng nhập thành công
            $_SESSION['admin_user'] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar' => $user->avatar
            ];
            
            $_SESSION['login_success'] = true;

            header('Location: ' . BASE_URL . '/admin');
            exit;
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
        }
    }
}




?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>

    <meta charset="utf-8" />
    <title>Đăng nhập</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="icon" href="<?php echo BASE_URL . '/public/' . $system->logo ?>">

    <!-- Layout config Js -->
    <script src="<?php echo BASE_URL; ?>/public/assets/js/layout.js"></script>
    <!-- Bootstrap Css -->
    <link href="<?php echo BASE_URL; ?>/public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="<?php echo BASE_URL; ?>/public/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="<?php echo BASE_URL; ?>/public/assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="<?php echo BASE_URL; ?>/public/assets/css/custom.min.css" rel="stylesheet" type="text/css" />
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

</head>

<body>

    <div class="auth-page-wrapper pt-5">
        <!-- auth page bg -->
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>

            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <!-- auth page content -->
        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <div>
                                <a href="index.html" class="d-inline-block auth-logo">
                                    <img src="assets/images/logo-light.png" alt="" height="20">
                                </a>
                            </div>
                            <p class="mt-3 fs-1 fw-medium text-white">Đăng nhập quản trị</p>
                        </div>
                    </div>
                </div>
                <!-- end row -->

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4">

                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Chào mừng trở lại !</h5>
                                    <p class="text-muted">Đăng nhập để tiếp tục.</p>
                                </div>
                                <div class="p-2 mt-4">
                                    <?php if (!empty($error)): ?>
                                        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                                    <?php endif; ?>
                                    <form action="" method="post">

                                        <div class="mb-3">
                                            <label for="username" class="form-label">Tên đăng nhập</label>
                                            <input type="text" class="form-control" id="username" name="username" placeholder="Nhập tên đăng nhập" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="password-input">Mật khẩu</label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password" class="form-control pe-5 password-input" placeholder="Nhập mật khẩu" id="password-input" name="password">
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit">Đăng nhập</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->

        <!-- footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">

                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>
    <!-- end auth-page-wrapper -->

    <!-- JAVASCRIPT -->
    <script src="<?php echo BASE_URL; ?>/public/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/libs/simplebar/simplebar.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/libs/node-waves/waves.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/libs/feather-icons/feather.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/plugins.js"></script>

    <!-- particles js -->
    <script src="<?php echo BASE_URL; ?>/public/assets/libs/particles.js/particles.js"></script>
    <!-- particles app js -->
    <script src="<?php echo BASE_URL; ?>/public/assets/js/pages/particles.app.js"></script>
    <!-- password-addon init -->
    <script src="<?php echo BASE_URL; ?>/public/assets/js/pages/password-addon.init.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>