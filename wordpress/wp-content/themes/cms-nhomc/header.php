<?php
/**
 * Header template cho Theme CMS Nhóm C
 *
 * @package CMS_NhomC
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="navbar-container">
        <!-- Khối bên trái: Group C, Home, Ô tìm kiếm -->
        <div class="nav-left">
            <div class="nav-brand">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    Group C
                </a>
            </div>

            <a href="<?php echo esc_url(home_url('/')); ?>" class="nav-home-tab">
                Home
            </a>

            <form role="search" method="get" class="nav-search-form" id="headerSearchForm" action="<?php echo esc_url(home_url('/')); ?>">
                <?php
                $header_query = get_search_query(false);
                $header_clean = is_string($header_query) ? $header_query : '';
                ?>
                <div class="search-input-wrapper">
                    <span class="search-icon-inside" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <input type="search" id="headerSearchInput" name="s" placeholder="<?php esc_attr_e('Search...', 'cms-nhomc'); ?>" value="<?php echo esc_attr($header_clean); ?>" minlength="2" maxlength="100" required title="<?php esc_attr_e('Vui lòng nhập từ khóa từ 2 đến 100 ký tự', 'cms-nhomc'); ?>" autocomplete="off" />
                </div>
                <button type="submit"><?php esc_html_e('Submit', 'cms-nhomc'); ?></button>
            </form>
        </div>

        <!-- Khối bên phải: Danh mục & Cụm nút thao tác (Menu, Search, Account) -->
        <div class="nav-right">
            <!-- Danh mục Thể thao, Khoa học, Tin tức -->
            <?php
            if (has_nav_menu('primary-menu')) {
                wp_nav_menu(array(
                    'theme_location' => 'primary-menu',
                    'container'      => false,
                    'menu_class'     => 'nav-categories',
                    'fallback_cb'    => false,
                ));
            } else {
            ?>
                <ul class="nav-categories">
                    <li><a href="<?php echo esc_url(home_url('/category/the-thao/')); ?>">Thể thao</a></li>
                    <li><a href="<?php echo esc_url(home_url('/category/khoa-hoc/')); ?>">Khoa học</a></li>
                    <li><a href="<?php echo esc_url(home_url('/category/tin-tuc/')); ?>">Tin tức</a></li>
                </ul>
            <?php } ?>

            <!-- Cụm Action Buttons: Menu, Search, Account -->
            <div class="nav-actions">
                <!-- Nút 3 chấm: Menu -->
                <button type="button" class="action-btn" id="toggleMenuBtn" title="Menu" aria-label="Mở Menu">
                    <svg viewBox="0 0 24 24" width="22" height="22">
                        <circle cx="5" cy="12" r="2" />
                        <circle cx="12" cy="12" r="2" />
                        <circle cx="19" cy="12" r="2" />
                    </svg>
                    <span class="btn-label">Menu</span>
                </button>

                <!-- Nút Kính lúp: Search -->
                <a href="<?php echo esc_url(home_url('/?s=')); ?>" class="action-btn icon-stroke" id="focusSearchBtn" title="Tìm kiếm" aria-label="Tìm kiếm">
                    <svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <span class="btn-label">Search</span>
                </a>

                <!-- Nút Account: Dropdown người dùng -->
                <div class="account-dropdown-wrapper">
                    <button type="button" class="action-btn icon-stroke" id="accountBtn" title="Tài khoản" aria-label="Tài khoản người dùng">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" stroke="none">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                        </svg>
                        <span class="btn-label">Account <span class="caret-icon">&#9662;</span></span>
                    </button>

                    <!-- Menu xổ xuống của Account -->
                    <ul class="account-menu" id="accountMenu">
                        <?php if (is_user_logged_in()) : 
                            $current_user = wp_get_current_user();
                        ?>
                            <li style="padding: 6px 16px; font-weight: 600; color: #111827; font-size: 13px;">
                                <?php echo esc_html($current_user->display_name); ?>
                            </li>
                            <li class="dropdown-divider"></li>
                            <li><a href="<?php echo esc_url(admin_url()); ?>">Bảng tin quản trị</a></li>
                            <li><a href="<?php echo esc_url(admin_url('profile.php')); ?>">Hồ sơ cá nhân</a></li>
                            <li class="dropdown-divider"></li>
                            <li><a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" style="color: #dc2626;">Đăng xuất</a></li>
                        <?php else : ?>
                            <li><a href="<?php echo esc_url(wp_login_url()); ?>">Đăng nhập</a></li>
                            <?php if (get_option('users_can_register')) : ?>
                                <li><a href="<?php echo esc_url(wp_registration_url()); ?>">Đăng ký tài khoản</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Lớp phủ mờ (Backdrop overlay) cho Drawer & Search trên Mobile -->
<div class="mobile-backdrop" id="mobileBackdrop"></div>

<!-- Drawer menu phụ cho nút 3 chấm / màn hình nhỏ -->
<div class="mobile-drawer" id="mobileDrawer">
    <div class="drawer-header">
        <strong class="drawer-brand">Menu Điều Hướng</strong>
        <button type="button" id="closeDrawerBtn" class="drawer-close-btn" aria-label="Đóng Menu">&times;</button>
    </div>
    <ul class="drawer-nav-list">
        <li><a href="<?php echo esc_url(home_url('/')); ?>"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li><a href="<?php echo esc_url(home_url('/?s=')); ?>"><i class="fa fa-search"></i> Tìm kiếm (Search)</a></li>
        <li><a href="<?php echo esc_url(home_url('/category/the-thao/')); ?>"><i class="fa fa-futbol-o"></i> Thể thao</a></li>
        <li><a href="<?php echo esc_url(home_url('/category/khoa-hoc/')); ?>"><i class="fa fa-flask"></i> Khoa học</a></li>
        <li><a href="<?php echo esc_url(home_url('/category/tin-tuc/')); ?>"><i class="fa fa-newspaper-o"></i> Tin tức</a></li>
        <li class="drawer-divider"></li>
        <li><a href="<?php echo esc_url(admin_url()); ?>"><i class="fa fa-cog"></i> Quản trị Admin</a></li>
    </ul>
</div>

<script>
// Xử lý sự kiện JavaScript cho Header
document.addEventListener('DOMContentLoaded', function() {
    var focusSearchBtn = document.getElementById('focusSearchBtn');
    var headerSearchInput = document.getElementById('headerSearchInput');
    var headerSearchForm = document.getElementById('headerSearchForm');
    var toggleMenuBtn = document.getElementById('toggleMenuBtn');
    var mobileDrawer = document.getElementById('mobileDrawer');
    var closeDrawerBtn = document.getElementById('closeDrawerBtn');
    var mobileBackdrop = document.getElementById('mobileBackdrop');
    var accountBtn = document.getElementById('accountBtn');
    var accountMenu = document.getElementById('accountMenu');

    function closeAllMenus() {
        if (mobileDrawer) mobileDrawer.classList.remove('open');
        if (headerSearchForm) headerSearchForm.classList.remove('is-active');
        if (accountMenu) accountMenu.classList.remove('show');
        if (mobileBackdrop) mobileBackdrop.classList.remove('active');
        document.body.classList.remove('drawer-open');
    }

    if (headerSearchForm && headerSearchInput) {
        headerSearchForm.addEventListener('submit', function(e) {
            var val = headerSearchInput.value.trim();
            if (val.length < 2) {
                e.preventDefault();
                headerSearchInput.focus();
                headerSearchInput.setCustomValidity('<?php echo esc_js(__('Vui lòng nhập từ khóa tối thiểu 2 ký tự.', 'cms-nhomc')); ?>');
                headerSearchInput.reportValidity();
                return false;
            }
            if (val.length > 100) {
                val = val.substring(0, 100);
            }
            headerSearchInput.value = val;
            headerSearchInput.setCustomValidity('');
        });

        headerSearchInput.addEventListener('input', function() {
            this.setCustomValidity('');
        });
    }

    // 1. Focus và bật/tắt ô tìm kiếm khi click nút Search
    if (focusSearchBtn) {
        focusSearchBtn.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && headerSearchForm) {
                e.preventDefault();
                e.stopPropagation();
                var isOpen = headerSearchForm.classList.toggle('is-active');
                if (isOpen) {
                    if (mobileDrawer) mobileDrawer.classList.remove('open');
                    if (accountMenu) accountMenu.classList.remove('show');
                    if (mobileBackdrop) mobileBackdrop.classList.add('active');
                    if (headerSearchInput) headerSearchInput.focus();
                } else {
                    if (mobileBackdrop) mobileBackdrop.classList.remove('active');
                }
            } else {
                var q = headerSearchInput ? headerSearchInput.value.trim() : '';
                if (q.length >= 2) {
                    e.preventDefault();
                    if (headerSearchForm) {
                        headerSearchForm.requestSubmit ? headerSearchForm.requestSubmit() : headerSearchForm.submit();
                    }
                } else if (q.length > 0) {
                    e.preventDefault();
                    if (headerSearchInput) {
                        headerSearchInput.focus();
                        headerSearchInput.setCustomValidity('<?php echo esc_js(__('Vui lòng nhập từ khóa tối thiểu 2 ký tự.', 'cms-nhomc')); ?>');
                        headerSearchInput.reportValidity();
                    }
                } else {
                    e.preventDefault();
                    window.location.href = this.getAttribute('href') || '<?php echo esc_js(home_url('/?s=')); ?>';
                }
            }
        });
    }

    // 2. Đóng mở Menu 3 chấm (Drawer)
    if (toggleMenuBtn && mobileDrawer) {
        toggleMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = mobileDrawer.classList.toggle('open');
            if (headerSearchForm) headerSearchForm.classList.remove('is-active');
            if (accountMenu) accountMenu.classList.remove('show');
            if (mobileBackdrop) {
                if (isOpen) {
                    mobileBackdrop.classList.add('active');
                    document.body.classList.add('drawer-open');
                } else {
                    mobileBackdrop.classList.remove('active');
                    document.body.classList.remove('drawer-open');
                }
            }
        });
    }

    if (closeDrawerBtn) {
        closeDrawerBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closeAllMenus();
        });
    }

    // 3. Dropdown Account
    if (accountBtn && accountMenu) {
        accountBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            accountMenu.classList.toggle('show');
            if (headerSearchForm) headerSearchForm.classList.remove('is-active');
            if (mobileDrawer) mobileDrawer.classList.remove('open');
            if (mobileBackdrop) mobileBackdrop.classList.remove('active');
        });
    }

    // Đóng khi click vào backdrop
    if (mobileBackdrop) {
        mobileBackdrop.addEventListener('click', function() {
            closeAllMenus();
        });
    }

    // Đóng khi click ra ngoài
    document.addEventListener('click', function(e) {
        if (mobileDrawer && !mobileDrawer.contains(e.target) && e.target !== toggleMenuBtn && !toggleMenuBtn.contains(e.target)) {
            mobileDrawer.classList.remove('open');
            if (mobileBackdrop) mobileBackdrop.classList.remove('active');
            document.body.classList.remove('drawer-open');
        }
        if (accountMenu && !accountMenu.contains(e.target) && e.target !== accountBtn && !accountBtn.contains(e.target)) {
            accountMenu.classList.remove('show');
        }
        if (headerSearchForm && !headerSearchForm.contains(e.target) && e.target !== focusSearchBtn && !focusSearchBtn.contains(e.target)) {
            headerSearchForm.classList.remove('is-active');
        }
    });
});
</script>
