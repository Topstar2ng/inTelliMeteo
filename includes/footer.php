<div class="footer bg-dark text-light mt-4" style="position: sticky; bottom: 0;">
    <div class="container">
    <div class="d-flex justify-content-between align-items-center py-3">
        <div class="small mb-0">&copy; <span class="d-none d-md-inline"><?php echo date("Y"); ?></span> IntelliMeteo. <span class="d-none d-md-inline">All rights reserved.</span>
        </div>
        
        <div>
            <?php if(isset($_SESSION['user_id'])): ?>
                <!-- LOGGED IN VIEW -->
                <span class="text-success small me-3"><i class="bi bi-person-check"></i> Hi, <?php echo htmlspecialchars(explode(' ', $_SESSION['full_name'])[1]); ?>!</span>
                <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Logout</span></a>
            <?php endif; ?>
        </div>
        <div class="text-end py-3">           
            
            
                <a href="<?php echo BASE_URL; ?>modules/help/manual.html" class="btn btn-primary btn-sm ms-2 d-inline-flex align-items-center" title="Manual">
                    <i class="bi bi-journal-richtext"></i> 
                    <span class="d-none d-md-inline ms-1">Manual</span>
                </a>
            
            <!-- Support Button: Icon on mobile, Text on desktop -->
            <a href="<?php echo BASE_URL; ?>supportme/" class="btn btn-warning btn-sm ms-2 d-inline-flex align-items-center" title="Support Me">
                <i class="bi bi-cup-hot-fill"></i>
                <span class="d-none d-md-inline ms-1">Support Me</span>
            </a>
            
        </div>
    </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script version="1.0">
    //use sweetalert to show a welcome message once on page load if user is not signed in
    <?php if(!isset($_SESSION['user_id'])): ?>
    window.onload = function() {
        Swal.fire({
            title: 'IntelliMeteo is FREE!',
            text: 'Please sign in for the best experience and to access most tools. If you encounter any issues, feel free to reach out to our support team. Enjoy exploring the weather data and analytics!',
            icon: 'info',
            confirmButtonText: 'Got it!'
        });
    };
    <?php endif; ?>

    //update page every 15 minutes to fetch new data if user is signed in
    setInterval(function() {
        if(<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
            location.reload();
        }
    }, 900000); // 900,000 milliseconds = 15 minutes
</script>
<script>
    // custome JS for preloader
    window.addEventListener("load", function() {
        const loader = document.getElementById("preloader");
        // Add a slight delay for a smoother "reveal" effect
        setTimeout(() => {
            loader.classList.add("loader-hidden");
        }, 500); 
    });
    //create a function to return the current time in GMT
    function updateGMTTime() {
        const gmtElement = document.querySelector('.time-gmt');
        const now = new Date();
        const gmtTime = now.toUTCString().split(' ')[4]; // Get HH:MM:SS from UTC string
        gmtElement.textContent = `GMT Time: ${gmtTime}`;
    }
    // Update GMT time every second
    setInterval(updateGMTTime, 1000);
    // Initial call to display time immediately on page load
    updateGMTTime();

</script>
</body>
</html>