<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IntelliMeteo | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicons/favicon-16x16.png">
    <link rel="manifest" href="assets/favicons/site.webmanifest">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero-card { background: linear-gradient(45deg, #1e3c72, #2a5298); color: white; border-radius: 15px; }
        .search-btn { border-radius: 0 5px 5px 0; }
        .search-input { border-radius: 5px 0 0 5px; }
        .logo-img { width: 30px; height: 30px; margin-right: 10px; border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
        .page-img { width: 150px; height: 100px; margin-bottom: 10px; border-radius: 50%; box-shadow: 0 0 25px rgba(0,0,0,0.2); }
         @media (max-width: 576px) {
            .hero-card { text-align: center; }
            .hero-card .row { flex-direction: column; }
            .hero-card .col-md-6 { text-align: center; }
        }

    /* Subtle rotation effect for the gear icon on hover */
    .hover-rotate:hover i {
        display: inline-block;
        transform: rotate(45deg);
        transition: transform 0.3s ease;
        color: #ffc107; /* Warning yellow color to match your theme */
    }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <img src="assets/images/intellimeteo_icon.png" class="logo-img"> 
            IntelliMeteo <span class="d-none d-md-inline">: A Weather & Meteo Analytics Portal</span>
        </a>        
    </div>
</nav>
<!-- HTML for Login Form -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4 card p-4 shadow-sm border-0">
            <div class="text-center mb-4">
                <img src="assets/images/intellimeteo_icon.png" class="page-img">
                <h4 class="mt-2">IntelliMeteo Login</h4>
            </div>
            <?php if(isset($error)) echo "<div class='alert alert-danger small'>$error</div>"; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="small fw-bold"><i class="bi bi-envelope"></i> Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <!-- Add a password strength indicator here and also reveal password option -->
                    <label class="small fw-bold"><i class="bi bi-lock"></i> Password</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                        <button class="btn btn-outline-secondary" type="button" id="reveal-password"><i class="bi bi-eye p-icon"></i></button>
                    </div>

                </div>

                <button type="submit" class="btn btn-dark w-100">Enter Portal</button>
            </form>
            <p class="mt-3 small text-center">Don't have an account? <a href="register.php">Register</a> or continue as a  <a href="index.php">Guest</a>.</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add a checkbox to reveal password and toggle the input type between 'password' and 'text'
    const revealPasswordCheckbox = document.getElementById('reveal-password');
    const passwordInputField = document.querySelector('input[name="password"]');
    revealPasswordCheckbox.addEventListener('click', () => {
        if (passwordInputField.type === 'password') {
            passwordInputField.type = 'text';
            revealPasswordCheckbox.innerHTML = '<i class="bi bi-eye-slash p-icon"></i>';
        } else {
            passwordInputField.type = 'password';
            revealPasswordCheckbox.innerHTML = '<i class="bi bi-eye p-icon"></i>';
        }
    });
</script>
</body>
</html>