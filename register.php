<?php

require_once 'includes/db.php';

$error = ""; // Initialize error message variable
$_SESSION['success_message'] = '';

// Fetch the current count of registered members for our real-time badge
try {
    $countStmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalMembers = $countStmt->fetchColumn();
} catch (PDOException $e) {
    $totalMembers = 0; // Fallback if table doesn't exist yet
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // New expanded fields
    $location = $_POST['location'] ?? '';
    $workplace = $_POST['workplace'] ?? '';
    
    // Handle "Other" profession safely
    $profession = $_POST['profession'] ?? '';
    if ($profession === 'Other' && !empty($_POST['profession_other'])) {
        $profession = $_POST['profession_other'];
    }
    
    $purpose = $_POST['purpose'] ?? '';
    if ($purpose === 'Other' && !empty($_POST['purpose_other'])) {
        $purpose = $_POST['purpose_other'];
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, location, workplace, profession, purpose) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $pass, $location, $workplace, $profession, $purpose]);
        //display success message and redirect to login page        
        $_SESSION['success_message'] = "Registration successful! You can now log in.";
        
        header("Location: login.php?msg=registered");
        exit;
    } catch (PDOException $e) {
        $error = "Registration failed. Email might already exist. " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IntelliMeteo | Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicons/favicon-16x16.png">
    <link rel="manifest" href="assets/favicons/site.webmanifest">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .logo-img { width: 30px; height: 30px; margin-right: 10px; border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
        .card { border-radius: 12px; }
        #other-profession-wrapper { display: none; }
        .info-banner { background-color: #e3f2fd; border-left: 4px solid #0d6efd; color: #0a58ca; }
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

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <!-- Expanded column width to col-md-6 to accommodate new fields nicely side-by-side -->
        <div class="col-md-6 card p-4 shadow-sm bg-white">
            <!-- Form Title & Active Members Badge -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold text-dark m-0">Create Account</h4>
                <span class="badge bg-success shadow-sm d-flex align-items-center gap-1">
                    <i class="bi bi-people-fill"></i> <?php echo number_format(233 + $totalMembers); ?> Active Members
                </span>
            </div>

            <!-- Brief Notification for Prospective Users -->
            <div class="p-3 mb-4 rounded info-banner shadow-sm text-small">
                <div class="fw-bold mb-1"><i class="bi bi-info-circle-fill me-1"></i> Why register an account?</div>
                An IntelliMeteo account unlocks personalized weather feeds, advanced airport TAF tracking tools, analytics custom-tailored to your location, and operational data logging.
            </div>

            <h4 class="text-center mb-4 fw-bold text-dark">Create Account</h4>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger p-2 small text-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php
            if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message'])) {
                echo '<div class="alert alert-success p-2 small text-center" role="alert">';
                echo '<i class="bi bi-check-circle-fill"></i> ' . htmlspecialchars($_SESSION['success_message']);
                echo '</div>';
                unset($_SESSION['success_message']);
            }
            ?>

            <form method="POST">
                <div class="row">
                    <!-- Full Name -->
                    <div class="col-md-6 mb-3">
                        <label class="small fw-bold form-label"><i class="bi bi-person"></i> Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="Temitope Ola" required>
                    </div>
                    <!-- Email Address -->
                    <div class="col-md-6 mb-3">
                        <label class="small fw-bold form-label"><i class="bi bi-envelope"></i> Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                    </div>
                </div>

                <div class="row">
                    <!-- Location / Base Airport -->
                    <div class="col-md-6 mb-3">
                        <label class="small fw-bold form-label"><i class="bi bi-geo-alt"></i> Location / Primary Base</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g., Kano" required>
                    </div>
                    <!-- Workplace / Agency -->
                    <div class="col-md-6 mb-3">
                        <label class="small fw-bold form-label"><i class="bi bi-building"></i> Workplace / Agency</label>
                        <input type="text" name="workplace" class="form-control" placeholder="e.g., NiMet, Airline, School" required>
                    </div>
                </div>

                <div class="row">
                    <!-- Profession -->
                    <div class="col-md-6 mb-3">
                        <label class="small fw-bold form-label"><i class="bi bi-briefcase"></i> Profession</label>
                        <select name="profession" id="profession-select" class="form-select" required>
                            <option value="" disabled selected>Select your profession</option>
                            <option value="Meteorologist">Meteorologist</option>
                            <option value="Pilot / Flight Crew">Pilot / Flight Crew</option>
                            <option value="Air Traffic Controller">Air Traffic Controller</option>
                            <option value="Dispatcher / Flight Ops">Dispatcher / Flight Ops</option>
                            <option value="Academic / Researcher">Academic / Researcher</option>
                            <option value="Other">Other (Specify...)</option>
                        </select>
                    </div>
                    <!-- Purpose of Signing Up -->
                    <div class="col-md-6 mb-3">
                        <label class="small fw-bold form-label"><i class="bi bi-question-circle"></i> Purpose of Registration</label>
                        <select name="purpose" id="purpose-select" class="form-select" required>
                            <option value="" disabled selected>Select purpose</option>
                            <option value="Aviation Analytics">Aviation Analytics / TAF Tracking</option>
                            <option value="Research & Education">Research & Education</option>
                            <option value="Operational Planning">Operational Planning</option>
                            <option value="General Interest">General Weather Monitoring</option>
                            <option value="Other">Other (Specify...)</option>
                        </select>
                    </div>
                </div>

                <!-- Hidden Input Field for Custom Profession (Triggers via JS) -->
                <div class="mb-3" id="other-profession-wrapper">
                    <label class="small fw-bold form-label text-warning"><i class="bi bi-pencil-square"></i> Please Specify Profession</label>
                    <input type="text" name="profession_other" id="profession-other" class="form-control" placeholder="Enter your profession">
                </div>

                <div class="mb-3" id="other-purpose-wrapper" style="display: none;">
                    <label class="small fw-bold form-label text-warning"><i class="bi bi-pencil-square"></i> Please Specify Purpose</label>
                    <input type="text" name="purpose_other" id="purpose-other" class="form-control" placeholder="Enter your purpose for signing up">
                </div>

                <!-- Password Row -->
                <div class="mb-3">
                    <label class="small fw-bold form-label"><i class="bi bi-lock"></i> Password</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                        <button class="btn btn-outline-secondary" type="button" id="reveal-password">
                            <i class="bi bi-eye p-icon"></i>
                        </button>
                    </div>
                    <div id="password-strength" class="small mt-1"></div>
                </div>

                <!-- Terms and Conditions -->
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label small" for="terms">
                        I agree to the <a href="legal/index.php" target="_blank">Terms of Reference</a>.
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold">Register Account</button>
            </form>
            <p class="mt-3 small text-center">Already have an account? <a href="login.php">Login</a> or continue as a <a href="index.php">Guest</a>.</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Password strength logic
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

    // Reveal password toggle logic
    const revealPasswordCheckbox = document.getElementById('reveal-password');
    revealPasswordCheckbox.addEventListener('click', () => {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            revealPasswordCheckbox.innerHTML = '<i class="bi bi-eye-slash p-icon"></i>';
        } else {
            passwordInput.type = 'password';
            revealPasswordCheckbox.innerHTML = '<i class="bi bi-eye p-icon"></i>';
        }
    });

    // Dynamic Visibility for "Other" profession field
    const professionSelect = document.getElementById('profession-select');
    const otherProfessionWrapper = document.getElementById('other-profession-wrapper');
    const otherProfessionInput = document.getElementById('profession-other');

    professionSelect.addEventListener('change', function() {
        if (this.value === 'Other') {
            otherProfessionWrapper.style.display = 'block';
            otherProfessionInput.setAttribute('required', 'required');
            otherProfessionInput.focus();
        } else {
            otherProfessionWrapper.style.display = 'none';
            otherProfessionInput.removeAttribute('required');
            otherProfessionInput.value = '';
        }
    });

    // Dynamic Visibility for "Other" purpose field
    const purposeSelect = document.getElementById('purpose-select');
    const otherPurposeWrapper = document.getElementById('other-purpose-wrapper');
    const otherPurposeInput = document.getElementById('purpose-other');

    purposeSelect.addEventListener('change', function() {
        if (this.value === 'Other') {
            otherPurposeWrapper.style.display = 'block';
            otherPurposeInput.setAttribute('required', 'required');
            otherPurposeInput.focus();
        } else {
            otherPurposeWrapper.style.display = 'none';
            otherPurposeInput.removeAttribute('required');
            otherPurposeInput.value = '';
        }
    });
</script>
<?php include 'footer.php'; ?>