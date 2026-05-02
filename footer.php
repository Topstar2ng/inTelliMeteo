<div class="footer bg-dark text-light mt-4" style="position: sticky; bottom: 0;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center py-3">
            <div class="small mb-0">&copy; <?php echo date("Y"); ?> IntelliMeteo. <span class="d-none d-md-inline">All rights reserved.</span></div>
            <?php if(isset($_SESSION['user_id'])): ?>
                <!-- LOGGED IN VIEW -->
                <span class="text-success small me-3"><i class="bi bi-person-check"></i> Hi, <?php echo htmlspecialchars(explode(' ', $_SESSION['full_name'])[1]); ?>!</span>
                <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Logout</span></a>
            <?php endif; ?>
            <span class="text-end"><a href="<?php echo file_exists("modules/help/manual.html") ? "modules/help/manual.html" : "../modules/help/manual.html"; ?>" class="text-warning"><i class="bi bi-journal-richtext"></i> Manual</a></span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>