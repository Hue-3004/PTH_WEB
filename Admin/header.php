<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
$system = \App\Models\System::first();
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none" data-preloader="disable">

<head>

    <meta charset="utf-8" />
    <title>Trang quản trị</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Admin Dashboard" name="description" />

    <!-- App favicon -->
    <link rel="icon" href="<?php echo BASE_URL . '/public/' . $system->logo ?>">

    <!-- jsvectormap css -->
    <link href="<?php echo BASE_URL; ?>/public/assets/libs/jsvectormap/css/jsvectormap.min.css" rel="stylesheet" type="text/css" />

    <!--Swiper slider css-->
    <link href="<?php echo BASE_URL; ?>/public/assets/libs/swiper/swiper-bundle.min.css" rel="stylesheet" type="text/css" />

    <!-- Layout config Js -->
    <script src="<?php echo BASE_URL; ?>/public/assets/js/layout.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap Css -->
    <link href="<?php echo BASE_URL; ?>/public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="<?php echo BASE_URL; ?>/public/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="<?php echo BASE_URL; ?>/public/assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <!-- custom Css-->
    <link href="<?php echo BASE_URL; ?>/public/assets/css/custom.min.css" rel="stylesheet" type="text/css" />

</head>

<body>
    <div id="layout-wrapper">

        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex">
                        <!-- LOGO -->
                         <?php 
                         $system = \App\Models\System::first();
                         ?>
                        <div class="navbar-brand-box horizontal-logo">
                            <a href="<?php echo BASE_URL; ?>/admin" class="logo logo-dark">
                                <span class="logo-sm">
                                    <img src="<?php echo BASE_URL . '/public/' . $system->logo ?>" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="<?php echo BASE_URL . '/public/' . $system->logo ?>" alt="" height="17">
                                </span>
                            </a>

                            <a href="<?php echo BASE_URL; ?>/admin" class="logo logo-light">
                                <span class="logo-sm">
                                    <img src="<?php echo BASE_URL . '/public/' . $system->logo ?>" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="<?php echo BASE_URL . '/public/' . $system->logo ?>" alt="" height="17">
                                </span>
                            </a>
                        </div>

                        <button type="button"
                            class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger"
                            id="topnav-hamburger-icon">
                            <span class="hamburger-icon">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>

                        <!-- App Search-->
                        <!--  -->
                    </div>

                    <div class="d-flex align-items-center">

                        <div class="dropdown d-md-none topbar-head-dropdown header-item">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                                id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="bx bx-search fs-22"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                                aria-labelledby="page-header-search-dropdown">
                                <form class="p-3">
                                    <div class="form-group m-0">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search ..."
                                                aria-label="Recipient's username">
                                            <button class="btn btn-primary" type="submit"><i
                                                    class="mdi mdi-magnify"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="ms-1 header-item d-none d-sm-flex">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                                data-toggle="fullscreen">
                                <i class='bx bx-fullscreen fs-22'></i>
                            </button>
                        </div>

                        <div class="ms-1 header-item d-none d-sm-flex">
                            <button type="button"
                                class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode">
                                <i class='bx bx-moon fs-22'></i>
                            </button>
                        </div>

                        <div class="dropdown ms-sm-3 header-item topbar-user">
                            <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center">
                                <?php
                                    if(isset($_SESSION['admin_user']['avatar'])){
                                        echo '<img class="rounded-circle header-profile-user" src="' . BASE_URL . '/public/' . $_SESSION['admin_user']['avatar'] . '" alt="Header Avatar">';
                                    }else{
                                        echo '<img class="rounded-circle header-profile-user" src="' . BASE_URL . '/public/uploads/avatars/default-avatar.png" alt="Header Avatar">';
                                    }
                                    ?>
                                    <span class="text-start ms-xl-2">
                                        <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">
                                            <?php echo isset($_SESSION['admin_user']['name']) ? $_SESSION['admin_user']['name'] : ''; ?>
                                        </span>
                                        <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">
                                            <?php echo isset($_SESSION['admin_user']['role']) ? $_SESSION['admin_user']['role'] : ''; ?>
                                        </span>
                                    </span>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <!-- item-->
                                <h6 class="dropdown-header">
                                    <?php echo 'Chào mừng ' . (isset($_SESSION['admin_user']['name']) ? $_SESSION['admin_user']['name'] : '') . '!'; ?>
                                </h6>
                                <a class="dropdown-item" href="<?php echo BASE_URL . '/admin/user/detail/' . $_SESSION['admin_user']['id']; ?>"><i
                                        class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span
                                        class="align-middle">Tài khoản</span></a>
                                <a class="dropdown-item" href="#" id="logout-btn"><i
                                        class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span
                                        class="align-middle" data-key="t-logout">Đăng xuất</span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- ========== App Menu ========== -->
        <div class="app-menu navbar-menu">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <!-- Dark Logo-->
                <a href="<?php echo BASE_URL; ?>/admin" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="<?php echo BASE_URL . '/public/' . $system->logo ?>" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="<?php echo BASE_URL . '/public/' . $system->logo ?>" alt="" height="17">
                    </span>
                </a>
                <!-- Light Logo-->
                <a href="<?php echo BASE_URL; ?>/admin" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="<?php echo BASE_URL . '/public/' . $system->logo ?>" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="<?php echo BASE_URL . '/public/' . $system->logo ?>" alt="" height="17">
                    </span>
                </a>
                <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
                    id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
            </div>

            <div id="scrollbar">
                <div class="container-fluid">

                    <div id="two-column-menu">
                    </div>
                    <ul class="navbar-nav" id="navbar-nav">
                        <li class="menu-title"><span data-key="t-menu">Quản lý</span></li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL . '/admin' ?>" class="nav-link" data-key="t-crm"> <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Thống kê</span> </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarApps" data-bs-toggle="collapse" role="button"
                                aria-expanded="false" aria-controls="sidebarApps">
                                <i class="ri-apps-2-line"></i> <span data-key="t-apps">Sản phẩm</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarApps">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="<?php echo BASE_URL . '/admin/category' ?>" class="nav-link" data-key="t-chat"> Danh mục </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?php echo BASE_URL . '/admin/product' ?>" class="nav-link" data-key="t-chat"> Sản phẩm </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo BASE_URL . '/admin/order' ?>" class="nav-link" data-key="t-crm"> <i class="ri-shopping-cart-line"></i> <span data-key="t-dashboards">Đơn hàng</span> </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo BASE_URL . '/admin/payment' ?>" class="nav-link" data-key="t-crm"> <i class="ri-refund-line"></i> <span data-key="t-dashboards">Thanh toán</span> </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo BASE_URL . '/admin/brand' ?>" class="nav-link" data-key="t-crm"> <i class="ri-honour-line"></i> <span data-key="t-dashboards">Thương hiệu</span> </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo BASE_URL . '/admin/user' ?>" class="nav-link" data-key="t-crm"> <i class="ri-account-box-line"></i> <span data-key="t-dashboards">Tài khoản</span> </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo BASE_URL . '/admin/post' ?>" class="nav-link" data-key="t-crm"> <i class="ri-article-line"></i> <span data-key="t-dashboards">Bài viết</span> </a>
                        </li>

                        <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">Hệ thống </span></li>

                        <li class="nav-item">
                            <a href="<?php echo BASE_URL . '/admin/banner' ?>" class="nav-link" data-key="t-crm"> <i class="ri-slideshow-4-line"></i> <span data-key="t-dashboards">Banner</span> </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo BASE_URL . '/admin/systems' ?>" class="nav-link" data-key="t-crm"> <i class="ri-settings-3-line"></i> <span data-key="t-dashboards">Hệ thống</span> </a>
                        </li>

                    </ul>
                </div>
                <!-- Sidebar -->
            </div>

            <div class="sidebar-background"></div>
        </div>
        <!-- Left Sidebar End -->

        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var logoutBtn = document.getElementById('logout-btn');
                if (logoutBtn) {
                    logoutBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Bạn có chắc chắn muốn đăng xuất?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Đăng xuất',
                            cancelButtonText: 'Hủy'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'logout.php';
                            }
                        });
                    });
                }
            });
        </script>
    </div>
</body>
</html>