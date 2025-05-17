<?php

use App\Models\System;

$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$searchQuery = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$system = System::first();
?>

<nav class="navbar">
    <a href="<?php echo BASE_URL; ?>" class="logo">
        <img src="<?php echo BASE_URL . '/public/' . $system->logo ?>" alt="<?php echo $system->site_name ?>" height="22">
    </a>
    <!-- Nút Menu Mobile -->
    
    <div class="menu" id="menu">
        <a href="<?php echo BASE_URL; ?>">Trang Chủ</a>
        <a href="?page=product-list">Sản Phẩm</a>
        <a href="?page=post">Bài Viết</a>
        <a href="?page=about-page">Về Chúng Tôi</a>
        <a href="?page=contact-page">Liên Hệ</a>
    </div>
    <form class="search-box" action="" method="GET">
         <input type="hidden" placeholder="Tìm kiếm..." name="page" required value="timkiem_sanpham">
        <input type="text" placeholder="Tìm kiếm..." name="search" required value="<?php echo $searchQuery; ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    <div class="icons">
        <!-- Icon User -->
        <div class="user-icon" onclick="toggleUserMenu()">
            <i class="fas fa-user"></i>
            <span class="user-text">Tài Khoản</span>
            <div class="user-menu" id="user-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="?page=account">Thông tin tài khoản</a>
                    <a href="<?php echo BASE_URL; ?>/logout">Đăng xuất</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login">Đăng nhập</a>
                    <a href="<?php echo BASE_URL; ?>/register">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
        <!-- Icon Cart -->
        <div class="cart-icon">
            <a href="?page=shop-cart">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count"><?php echo $cartCount; ?></span>
            </a>
        </div>
    </div>
</nav>
