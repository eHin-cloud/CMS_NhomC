/**
 * JavaScript điều khiển Featured Posts Slider (Module 16) - Anh Quý
 *
 * Hỗ trợ chuyển slide tự động, nút Next/Prev, chỉ số trang (Dots),
 * tạm dừng khi hover và hỗ trợ vuốt chạm trên thiết bị di động.
 *
 * @package CMS_NhomC
 */

document.addEventListener('DOMContentLoaded', function () {
    var sliders = document.querySelectorAll('.cms-featured-slider');
    if (!sliders || sliders.length === 0) {
        return;
    }

    sliders.forEach(function (slider) {
        initSlider(slider);
    });

    function initSlider(container) {
        var slides = container.querySelectorAll('.cms-slider-slide');
        var prevBtn = container.querySelector('.cms-slider-nav-prev');
        var nextBtn = container.querySelector('.cms-slider-nav-next');
        var dots = container.querySelectorAll('.cms-slider-dot');
        var progressBar = container.querySelector('.cms-slider-progress-bar');

        if (!slides || slides.length <= 1) {
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            return;
        }

        var currentIndex = 0;
        var totalSlides = slides.length;
        var autoplayInterval = null;
        var autoplayDelay = 5000; // 5 giây tự động chuyển slide
        var isPaused = false;

        // Cập nhật slide hiển thị
        function updateSlide(index) {
            if (index < 0) {
                currentIndex = totalSlides - 1;
            } else if (index >= totalSlides) {
                currentIndex = 0;
            } else {
                currentIndex = index;
            }

            slides.forEach(function (slide, idx) {
                if (idx === currentIndex) {
                    slide.classList.add('is-active');
                    slide.setAttribute('aria-hidden', 'false');
                } else {
                    slide.classList.remove('is-active');
                    slide.setAttribute('aria-hidden', 'true');
                }
            });

            dots.forEach(function (dot, idx) {
                if (idx === currentIndex) {
                    dot.classList.add('is-active');
                    dot.setAttribute('aria-current', 'true');
                } else {
                    dot.classList.remove('is-active');
                    dot.removeAttribute('aria-current');
                }
            });

            resetProgressBar();
        }

        function nextSlide() {
            updateSlide(currentIndex + 1);
        }

        function prevSlide() {
            updateSlide(currentIndex - 1);
        }

        // Thanh tiến trình chạy theo autoplay
        function resetProgressBar() {
            if (!progressBar) return;
            progressBar.style.transition = 'none';
            progressBar.style.width = '0%';
            setTimeout(function () {
                if (!isPaused) {
                    progressBar.style.transition = 'width ' + autoplayDelay + 'ms linear';
                    progressBar.style.width = '100%';
                }
            }, 50);
        }

        // Tự động chuyển slide
        function startAutoplay() {
            stopAutoplay();
            resetProgressBar();
            autoplayInterval = setInterval(function () {
                if (!isPaused) {
                    nextSlide();
                }
            }, autoplayDelay);
        }

        function stopAutoplay() {
            if (autoplayInterval) {
                clearInterval(autoplayInterval);
                autoplayInterval = null;
            }
        }

        // Gắn sự kiện nút Prev / Next
        if (nextBtn) {
            nextBtn.addEventListener('click', function (e) {
                e.preventDefault();
                nextSlide();
                startAutoplay();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function (e) {
                e.preventDefault();
                prevSlide();
                startAutoplay();
            });
        }

        // Gắn sự kiện dots
        dots.forEach(function (dot) {
            dot.addEventListener('click', function (e) {
                e.preventDefault();
                var targetIdx = parseInt(this.getAttribute('data-slide-to'), 10);
                if (!isNaN(targetIdx) && targetIdx !== currentIndex) {
                    updateSlide(targetIdx);
                    startAutoplay();
                }
            });
        });

        // Tạm dừng khi rê chuột vào slider
        container.addEventListener('mouseenter', function () {
            isPaused = true;
            if (progressBar) {
                var computedWidth = window.getComputedStyle(progressBar).width;
                progressBar.style.transition = 'none';
                progressBar.style.width = computedWidth;
            }
        });

        container.addEventListener('mouseleave', function () {
            isPaused = false;
            startAutoplay();
        });

        // Xử lý vuốt chạm trên thiết bị cảm ứng (Mobile Swipe)
        var touchStartX = 0;
        var touchEndX = 0;

        container.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        container.addEventListener('touchend', function (e) {
            touchEndX = e.changedTouches[0].screenX;
            handleGesture();
        }, { passive: true });

        function handleGesture() {
            var diff = touchEndX - touchStartX;
            if (Math.abs(diff) > 45) { // Vuốt trên 45px
                if (diff < 0) {
                    nextSlide(); // Vuốt sang trái -> xem slide kế
                } else {
                    prevSlide(); // Vuốt sang phải -> xem slide trước
                }
                startAutoplay();
            }
        }

        // Khởi động slider đầu tiên
        updateSlide(0);
        startAutoplay();
    }
});
