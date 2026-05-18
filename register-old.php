<?php

require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure hashing

    try {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $pass]);
        header("Location: login.php?msg=registered");
    } catch (PDOException $e) {
        $error = "Registration failed. Email might already exist.";
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
<!-- HTML for Register Form using Bootstrap -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4 card p-4 shadow-sm">
            <h4 class="text-center mb-4">Create Account</h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="small fw-bold"><i class="bi bi-envelope"></i> Email</label>
                    <input type="email" name="email" class="form-control mb-3" placeholder="Email Address" required>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold"><i class="bi bi-person"></i> Full Name</label>
                    <input type="text" name="full_name" class="form-control mb-3" placeholder="Full Name" required>
                </div>
                <div class="mb-3">
                    <!-- Add a password strength indicator here and also reveal password option -->
                    <label class="small fw-bold"><i class="bi bi-lock"></i> Password</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                        <button class="btn btn-outline-secondary" type="button" id="reveal-password"><i class="bi bi-eye p-icon"></i></button>
                    </div>
                    <div id="password-strength" class="small mt-1"></div>

                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label small" for="terms">
                        I agree to the <a href="legal/index.php" target="_blank">Terms of Reference</a>.
                    </label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="mt-3 small text-center">Already have an account? <a href="login.php">Login</a>  or continue as a  <a href="index.php">Guest</a>.</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add JavaScript for password strength indicator and reveal password functionality

    const passwordInput = document.querySelector('input[name="password"]');
    passwordInput.addEventListener('input', () => {
        const strengthText = document.getElementById('password-strength');
        const value = passwordInput.value;
        let strength = 'Weak';
        if (value.length > 8 && /[A-Z]/.test(value) && /[0-9]/.test(value) && /[\W]/.test(value)) {
            strength = 'Strong';
        } else if (value.length > 6) {
            strength = 'Medium';
        }
        if (strengthText) {
            strengthText.textContent = `Password Strength: ${strength}`;
            strengthText.className = `small mt-1 ${strength === 'Strong' ? 'text-success' : strength === 'Medium' ? 'text-warning' : 'text-danger'}`;
        }

    });
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
<?php include 'includes/footer.php'; ?>