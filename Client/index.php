<?php $system = \App\Models\System::first(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $system->site_name?> - Thời Trang Cao Cấp</title>
    <link rel="icon" href="<?php echo BASE_URL . '/public/' . $system->logo ?>">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="public/client/css/style.css?t=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php if (isset($_SESSION['success_login_client']) && $_SESSION['success_login_client']): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: '<?php echo $_SESSION['success_login_client']; ?>',
            showConfirmButton: false,
            timer: 2500
        });
    </script>
    <?php unset($_SESSION['success_login_client']); endif; ?>
    
    <?php include 'Client/header.php'; ?>
    <?php
        $page = isset($_GET['page']) ? $_GET['page'] : 'home';
        $page = str_replace(['/', '\\'], '', $page);
        switch ($page) {
            case 'home':
                include 'Client/banner.php'; 
                include 'Client/home.php';
                include 'Client/article-section.php';
                break;
            case 'product-list':
                include 'Client/product-list.php';
                break;
            case 'product-detail':
                include 'Client/product-detail.php';
                break;
            case 'about-page':
                include 'Client/about-page.php';
                break;
            case 'contact-page':
                include 'Client/contact-page.php';
                break;
            case 'account':
                include 'Client/account.php';
                break;
            case 'shop-cart':
                include 'Client/shop-cart.php';
                break;
            case 'check-out':
                include 'Client/check-out.php';
                break;
            case 'post':
                include 'Client/post.php';
                break;
            case 'timkiem_sanpham':
                include 'Client/timkiem_sanpham.php';
                break;
            default:
                echo "<h2>404 - Trang không tồn tại!</h2>";
                break;
        }
    ?>
    <?php include 'Client/footer.php'; ?>
    <script src="public/client/js/index.js?t=<?php echo time(); ?>"></script>
</body>
</html>