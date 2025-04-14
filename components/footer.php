<?php
// Define base path
$isInViews = strpos($_SERVER['REQUEST_URI'], '/views/') !== false;
$basePath = $isInViews ? '../' : '';
?>
<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <!-- Footer Logo -->
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="d-flex align-items-center mb-3">
                    <img src="<?php echo $basePath; ?>assets/img/logo.png" alt="BKSpace Logo" class="logo">
                    <div>
                        <h3 class="h5 mb-0">BKSpace</h3>
                        <p class="mb-0">Smart Study Space at HCMUT</p>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="col-md-4 col-lg-4 mb-4 mb-md-0">
                <h3 class="h5 mb-3 mini-header">Thông tin liên hệ</h3>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2 mini-header">Sinh viên</li>
                    <li class="mb-2">
                        <a href="https://mybk.hcmut.edu.vn/my/index.action" target="_blank" class="text-light">
                            <i class="fa fa-caret-right"></i> MyBK
                        </a>
                    </li>
                    <li class="mb-2 mini-header">Quý khách</li>
                    <li class="mb-2">
                        <a href="mailto:dl-cntt@hcmut.edu.vn" target="_blank" class="text-light">
                            <i class="fa fa-caret-right"></i> E-mail
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="https://hcmut.edu.vn/contact" target="_blank" class="text-light">
                            <i class="fa fa-caret-right"></i> Biểu mẫu thông tin liên hệ
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Social Media -->
            <div class="col-md-4 col-lg-4">
                <h3 class="h5 mb-3">Follow Us</h3>
                <div class="social-icons d-flex">
                    <a href="https://www.facebook.com/truongdhbachkhoa?locale=vi_VN" target="_blank" class="btn rounded-1 me-2">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://www.youtube.com/@bkoisp" target="_blank" class="btn rounded-1 me-2">
                        <i class="bi bi-youtube"></i>
                    </a>
                    <a href="https://x.com/bachkhoa_hcmut" target="_blank" class="btn rounded-1 me-2">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    <a href="https://www.instagram.com/truongdaihocbachkhoa.1957/" target="_blank" class="btn rounded-1">
                        <i class="bi bi-instagram"></i>
                    </a>
                </div>
            </div>
        <!-- </div>
        <hr class="my-4">
        <div class="text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> BKSPACE. All rights reserved.</p>
        </div> -->
    </div>
</footer> 