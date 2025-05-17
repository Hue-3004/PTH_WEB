<?php
// Định nghĩa các route cho admin
require_once __DIR__ . '/../config.php';

function checkAdminLogin() {
    // session_start();
    if (empty($_SESSION['admin_user'])) {
        header('Location:' . BASE_URL . '/admin/login');
        exit;
    }
}

$adminRoutes = [
    '/admin' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/index.php';
    },
    '/admin/login' => function() {
        // Không cần header và footer cho trang login
        include __DIR__ . '/../Admin/login.php';
    },

    // Sản phẩm
    '/admin/product' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/Products/index.php';
    },
    '/admin/product/create' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/Products/create.php';
    },
    '/admin/product/edit/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Products/edit.php';
    },
    '/admin/product/delete/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Products/delete.php';
    },

    // Đơn hàng
    '/admin/order' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/Oders/index.php';
    },
    '/admin/order/detail/{id}' => function($id) {  
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Oders/detail.php';
    },
    '/admin/order/update-status/{id}' => function($id) {  
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Oders/update_status.php';
    },

    // Thanh toán
    '/admin/payment' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/Payments/index.php';
    },
    '/admin/payment/detail/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Payments/detail.php';
    },

    // Sản phẩm biến thể
    '/admin/product/variant/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Products/list-variant.php';
    },
    '/admin/product/variant/create/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Products/create-variant.php';
    },
    '/admin/product/variant/edit/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Products/edit-variant.php';
    },
    '/admin/product/variant/delete/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Products/delete-variant.php';
    },

    // Danh mục
    '/admin/category' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/Categories/index.php';
    },
    '/admin/category/create' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/Categories/create.php';
    },
    '/admin/category/edit/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id; 
        include __DIR__ . '/../Admin/Categories/edit.php';
    },
    '/admin/category/delete/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Categories/delete.php';
    },

    // Banner
    '/admin/banner' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/Banners/index.php';
    },
    '/admin/banner/create' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/Banners/create.php';
    },  
    '/admin/banner/edit/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Banners/edit.php';
    },
    '/admin/banner/delete/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Banners/delete.php';
    },

    // Thương hiệu
    '/admin/brand' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/Brands/index.php';
    },
    '/admin/brand/create' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/Brands/create.php';
    },
    '/admin/brand/edit/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Brands/edit.php';
    },
    '/admin/brand/delete/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Brands/delete.php';
    },

    // Bài viết
    '/admin/post' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/Posts/index.php';
    },
    '/admin/post/create' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/Posts/create.php';
    },
    '/admin/post/edit/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Posts/edit.php';
    },
    '/admin/post/delete/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Posts/delete.php';
    },

    // Hệ thống
    '/admin/system' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/Systems/index.php';
    },
    '/admin/system/edit/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Systems/edit.php';
    },

    // Tài khoản
    '/admin/user' => function() {
        checkAdminLogin();
        include __DIR__ . '/../Admin/Users/index.php';
    },
    '/admin/user/detail/{id}' => function($id) {
        checkAdminLogin();
        $_GET['id'] = $id;
        include __DIR__ . '/../Admin/Users/detail.php';
    },
    
    




];
