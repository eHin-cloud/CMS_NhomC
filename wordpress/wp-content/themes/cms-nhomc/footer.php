<?php
/**
 * Footer template cho Theme CMS Nhóm C
 * Thiết kế chính xác 100% theo mẫu: https://bootsnipp.com/snippets/rlXdE
 *
 * @package CMS_NhomC
 */
?>

<!-- Footer -->
<section id="footer">
    <!-- Nút 3 chấm góc phải trên nếu có theo mẫu Bootsnipp -->
    <div class="footer-top-options">
        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
    </div>

    <div class="container">
        <div class="row text-center text-xs-center text-sm-left text-md-left">
            <!-- Cột 1: Quick links -->
            <div class="col-xs-12 col-sm-4 col-md-4">
                <h5>Quick links</h5>
                <ul class="list-unstyled quick-links">
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>Home</a></li>
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>About</a></li>
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>FAQ</a></li>
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>Get Started</a></li>
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>Videos</a></li>
                </ul>
            </div>

            <!-- Cột 2: Quick links -->
            <div class="col-xs-12 col-sm-4 col-md-4">
                <h5>Quick links</h5>
                <ul class="list-unstyled quick-links">
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>Home</a></li>
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>About</a></li>
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>FAQ</a></li>
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>Get Started</a></li>
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>Videos</a></li>
                </ul>
            </div>

            <!-- Cột 3: Quick links -->
            <div class="col-xs-12 col-sm-4 col-md-4">
                <h5>Quick links</h5>
                <ul class="list-unstyled quick-links">
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>Home</a></li>
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>About</a></li>
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>FAQ</a></li>
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>Get Started</a></li>
                    <li><a href="javascript:void(0);"><i class="fa fa-angle-double-right"></i>Imprint</a></li>
                </ul>
            </div>
        </div>

        <!-- Hàng Social Icons -->
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 mt-2 mt-sm-5">
                <ul class="list-unstyled list-inline social text-center">
                    <li class="list-inline-item"><a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fa fa-facebook"></i></a></li>
                    <li class="list-inline-item"><a href="https://twitter.com" target="_blank" rel="noopener noreferrer" aria-label="Twitter"><i class="fa fa-twitter"></i></a></li>
                    <li class="list-inline-item"><a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa fa-instagram"></i></a></li>
                    <li class="list-inline-item"><a href="https://plus.google.com" target="_blank" rel="noopener noreferrer" aria-label="Google Plus"><i class="fa fa-google-plus"></i></a></li>
                    <li class="list-inline-item"><a href="mailto:contact@nhomc.com" aria-label="Email"><i class="fa fa-envelope"></i></a></li>
                </ul>
            </div>
        </div>

        <!-- Hàng bản quyền pháp lý và tác giả -->
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 mt-2 mt-sm-2 text-center text-white">
                <p><a href="https://www.nationaltransaction.com/" target="_blank" rel="noopener noreferrer" class="footer-legal-link">National Transaction Corporation</a> is a Registered MSP/ISO of Elavon, Inc. Georgia [a wholly owned subsidiary of U.S. Bancorp, Minneapolis, MN]</p>
                <p class="h6">&copy; All right Reversed. <a class="text-green ml-2" href="https://bootsnipp.com/snippets/rlXdE" target="_blank" rel="noopener noreferrer">Sunlimetech</a></p>
            </div>
        </div>
    </div>
</section>
<!-- ./Footer -->

<!-- Nút Cuộn lên đầu trang (Back to Top) tối ưu trải nghiệm trên di động -->
<button type="button" id="backToTopBtn" class="back-to-top" aria-label="Cuộn lên đầu trang" title="Lên đầu trang">
    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="18 15 12 9 6 15"></polyline>
    </svg>
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var backToTopBtn = document.getElementById('backToTopBtn');
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 280) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        }, { passive: true });

        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});
</script>

<?php wp_footer(); ?>
</body>
</html>
