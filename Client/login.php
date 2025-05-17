<?php

use App\Models\User;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$system = \App\Models\System::first();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $errors[] = "Vui lòng điền đầy đủ thông tin.";
    }
    if (empty($errors)) {
        try {
            $user = User::where('username', $username)
                ->orWhere('email', $username)
                ->first();
            if ($user && password_verify($password, $user->password)) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['email'] = $user->email;
                $_SESSION['name'] = $user->name;
                $_SESSION['role'] = $user->role;
                $success = true;
                $_SESSION['success_login_client'] = "Đăng nhập thành công!";
                header("Location: " . BASE_URL);
                exit();
            } else {
                $errors[] = "Tên đăng nhập hoặc mật khẩu không đúng.";
            }
        } catch (Exception $e) {
            $errors[] = "Đã có lỗi xảy ra: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $system->site_name?> - Đăng nhập</title>
    <link rel="icon" href="<?php echo BASE_URL . '/public/' . $system->logo ?>">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="public/client/css/style.css?t=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #cart-popup {
            position: fixed;
            top: 30px;
            right: 30px;
            z-index: 9999;
            display: none;
        }

        .cart-popup-content {
            background: #fff;
            color: #222;
            border-radius: 10px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.13);
            padding: 18px 32px;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 5px solid #28a745;
        }

        .cart-popup-content i {
            color: #28a745;
            font-size: 24px;
        }

        /* Login Container */
        .login-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            margin-top: 70px;
        }

        .login-box {
            background-color: #FFFFFF;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.5s ease-in-out;
        }

        /* Logo/Icon */
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-logo i {
            font-size: 50px;
            color: #D4A017;
        }

        /* Title */
        .login-title {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: #1A1A1A;
            text-align: center;
            margin-bottom: 30px;
        }

        /* Form Styles */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #E0E0E0;
            border-radius: 5px;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #D4A017;
            box-shadow: 0 0 5px rgba(212, 160, 23, 0.3);
        }

        .form-group input::placeholder {
            color: #A0A0A0;
            transition: opacity 0.3s;
        }

        .form-group input:focus::placeholder {
            opacity: 0;
        }

        /* Login Button */
        .login-btn {
            background-color: #D4A017;
            color: #FFFFFF;
            border: none;
            border-radius: 5px;
            padding: 12px;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .login-btn:hover {
            background-color: #B58900;
        }

        /* Links */
        .login-links {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 14px;
        }

        .login-links a {
            color: #1A1A1A;
            text-decoration: none;
            transition: color 0.3s;
        }

        .login-links a:hover {
            color: #D4A017;
        }

        .login-links span {
            color: #A0A0A0;
        }

        /* Footer */
        footer {
            background-color: #1A1A1A;
            color: #FFFFFF;
            text-align: center;
            padding: 20px;
            font-size: 14px;
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .login-box {
                padding: 20px;
                max-width: 90%;
            }

            .login-title {
                font-size: 20px;
            }

            .form-group input {
                font-size: 14px;
                padding: 10px;
            }

            .login-btn {
                font-size: 14px;
                padding: 10px;
            }

            .login-links {
                font-size: 12px;
            }

            header .logo {
                font-size: 24px;
            }

            header nav a {
                font-size: 14px;
                margin: 0 10px;
            }
        }

        .alert-danger {
            color: red;
        }
    </style>
</head>

<body>
    <?php if (isset($_SESSION['success_register_client']) && $_SESSION['success_register_client']): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: '<?php echo $_SESSION['success_register_client']; ?>',
                showConfirmButton: false,
                timer: 2500
            });
        </script>
    <?php unset($_SESSION['success_register_client']);
    endif; ?>
    <?php include 'Client/header.php'; ?>

    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <i class="fas fa-user-circle"></i>
            </div>
            <h2 class="login-title">Đăng nhập tài khoản</h2>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form class="login-form" method="post" action="#">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Email hoặc Tên đăng nhập" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Mật khẩu" required>
                </div>
                <button type="submit" class="login-btn">Đăng nhập</button>
            </form>
            <div class="login-links">
                <a href="#" class="forgot-link">Quên mật khẩu?</a>
                <span>|</span>
                <a href="<?php echo BASE_URL; ?>/register" class="register-link">Đăng ký tài khoản</a>
            </div>
        </div>
    </div>

    <?php include 'Client/footer.php'; ?>
    <script src="public/client/js/index.js?t=<?php echo time(); ?>"></script>
    </div>
</body>

</html>