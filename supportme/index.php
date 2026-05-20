<?php
require_once '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in to auto-fill email, otherwise use placeholder empty strings
$userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$fullName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Guest';

// Fetch payment history if user is logged in
$history = [];
if (!empty($userEmail)) {
    try {
        $histStmt = $pdo->prepare("SELECT amount, reference, status, paid_at FROM server_donations WHERE donor_email = ? ORDER BY paid_at DESC");
        $histStmt->execute([$userEmail]);
        $history = $histStmt->fetchAll();
    } catch (PDOException $e) {
        // Silently fail or log error
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support IntelliMeteo | Server Fund</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/favicons/favicon-16x16.png">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .logo-img { width: 30px; height: 30px; margin-right: 10px; border-radius: 50%; }
        .hero-gradient { background: linear-gradient(45deg, #111827, #1f2937); color: white; border-radius: 15px; }
        .amount-badge { cursor: pointer; border: 2px solid #dee2e6; border-radius: 10px; transition: all 0.2s ease; font-weight: bold; }
        .amount-badge:hover, .amount-badge.active { border-color: #3ec2cf; background-color: rgba(62, 194, 207, 0.1); color: #3ec2cf; }
        .btn-paystack { background-color: #3ec2cf; color: #ffffff; font-weight: bold; border: none; }
        .btn-paystack:hover { background-color: #33a1cc; color: #ffffff; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../">
            <img src="../assets/images/intellimeteo_icon.png" class="logo-img"> IntelliMeteo
        </a>
        <a href="../" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>
</nav>
 
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            
            <div class="p-5 text-center hero-gradient shadow mb-4">
                <i class="bi bi-cup-hot-fill text-warning display-4"></i>
                <h2 class="fw-bold mt-3">Keep IntelliMeteo Online & Accurate</h2>
                <p class="lead text-muted-light max-w-2xl mx-auto px-md-4">
                    IntelliMeteo processes real-time OpenWeather API calculations, meteorological matrices, and aviation tracking loops 24/7 completely free of charge. Your small support keeps our team active and server speeds blazing fast.
                </p>
            </div>

            <div class="card p-4 shadow-sm border-0 bg-white">
                <h5 class="fw-bold mb-3 text-center">Fund Server Metrics</h5>
                <form id="paymentForm">
                    
                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Your Full Name</label>
                        <input type="text" id="donorName" class="form-control" value="<?php echo htmlspecialchars($fullName); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Email Address</label>
                        <input type="email" id="emailAddress" class="form-control" value="<?php echo htmlspecialchars($userEmail); ?>" placeholder="name@example.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-2">Select Donation Amount (NGN)</label>
                        <div class="row g-2 text-center mb-3">
                            <div class="col-4"><div class="p-2 amount-badge active" data-amount="2000">₦2,000</div></div>
                            <div class="col-4"><div class="p-2 amount-badge" data-amount="5000">₦5,000</div></div>
                            <div class="col-4"><div class="p-2 amount-badge" data-amount="10000">₦10,000</div></div>
                        </div>
                        <label class="small fw-bold text-muted mb-1">Or Enter Custom Amount</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">₦</span>
                            <input type="number" id="customAmount" class="form-control" placeholder="Other amount">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-paystack w-100 py-2 mt-2 shadow-sm text-uppercase">
                        <i class="bi bi-shield-lock-fill me-1"></i> Pay Securely with Paystack
                    </button>
                </form>
            </div>

            <?php if (!empty($userEmail)): ?>
            <div class="card p-4 shadow-sm border-0 bg-white mt-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-history me-2"></i>Your Contribution History</h5>
                <?php if (count($history) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-uppercase">
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Ref</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $row): ?>
                                <tr>
                                    <td class="small"><?php echo date("M j, Y", strtotime($row['paid_at'])); ?></td>
                                    <td class="fw-bold">₦<?php echo number_format($row['amount'], 2); ?></td>
                                    <td class="text-muted small"><?php echo htmlspecialchars($row['reference']); ?></td>
                                    <td class="text-end">
                                        <span class="badge rounded-pill <?php echo $row['status'] === 'success' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <p class="text-muted mb-0">You haven't made any donations yet. Your support helps keep our servers running!</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="mt-4 card p-3 border-0 bg-white shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="fs-4 text-info px-2"><i class="bi bi-cpu"></i></div>
                    <div class="small text-muted">
                        <strong>Infrastructure Note:</strong> This gesture is completely voluntary. IntelliMeteoFunds go directly toward covering active database instance hosting and raw JSON forecast requests. Payments are processed natively via Paystack over a secure PCI-DSS channel.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://js.paystack.co/v1/inline.js"></script>

<script>
    // Handle switching active state styling across money buttons
    const badges = document.querySelectorAll('.amount-badge');
    const customAmountInput = document.getElementById('customAmount');

    badges.forEach(badge => {
        badge.addEventListener('click', () => {
            badges.forEach(b => b.classList.remove('active'));
            badge.classList.add('active');
            customAmountInput.value = ''; // clear out text input if preset selected
        });
    });

    customAmountInput.addEventListener('input', () => {
        // Drop highlighting on preset badges if developer inputs custom metrics
        badges.forEach(b => b.classList.remove('active'));
    });

    // Handle form intercept checkout processing loops
    const paymentForm = document.getElementById('paymentForm');
    paymentForm.addEventListener("submit", function(e) {
        e.preventDefault();

        // 1. Determine Amount (Convert strictly to Kobo currency format as expected by Paystack API)
        let finalAmountInNaira = 0;
        const activeBadge = document.querySelector('.amount-badge.active');

        if (activeBadge) {
            finalAmountInNaira = parseFloat(activeBadge.getAttribute('data-amount'));
        } else if (customAmountInput.value) {
            finalAmountInNaira = parseFloat(customAmountInput.value);
        }

        if (isNaN(finalAmountInNaira) || finalAmountInNaira < 100) {
            alert("Please select or enter a valid amount of ₦100 or higher.");
            return;
        }

        let amountInKobo = finalAmountInNaira * 100;

        // 2. Fetch Field Metadata values safely
        const email = document.getElementById('emailAddress').value;
        const name = document.getElementById('donorName').value;

        // 3. Initialize Paystack Inline Handler Checkout
        let handler = PaystackPop.setup({
            key: '<?php echo PAYSTACK_PUBLIC_KEY; ?>',
            email: email,
            amount: amountInKobo,
            currency: 'NGN',
            ref: 'IM-' + Math.floor((Math.random() * 1000000000) + 1), // Unique platform reference tracking tag
            metadata: {
                custom_fields: [
                    {
                        display_name: "Donor Name",
                        variable_name: "donor_name",
                        value: name
                    }
                ]
            },
            callback: function(response) {
                // This block triggers automatically upon transaction success clear loops
                alert('Thank you! Payment successful. Reference ID: ' + response.reference);
                // 1. Alert the user briefly so they know the checkout process finished successfully
                alert('Thank you! Payment authorized. Finalizing server validation metrics...');
                
                // 2. Redirect the browser context to your backend verification handler with the safe reference tag
                window.location.href = "verify_payment.php?reference=" + encodeURIComponent(response.reference);

                // 3. The verify_payment.php script will handle the rest of the verification, logging, and user feedback loops securely on the server side before bouncing the user back home
            },
            onClose: function() {
                alert('Transaction aborted. Your forecasting dashboard context remains safe.');
            }
        });

        handler.openIframe();
    });
</script>
</body>
</html>