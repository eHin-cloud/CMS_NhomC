<?php
/**
 * Template form tìm kiếm chuẩn Bootsnipp 35V6b
 * (Bootstrap 4 Search Bar: https://bootsnipp.com/snippets/35V6b)
 *
 * @package CMS_NhomC
 */
?>
<form role="search" method="get" class="card card-sm bootsnipp-search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <div class="card-body row no-gutters align-items-center">
        <div class="col-auto search-icon-col">
            <svg class="search-icon-svg" viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2.3" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7.5"></circle>
                <line x1="21" y1="21" x2="16.5" y2="16.5"></line>
            </svg>
        </div>
        <!--end of col-->
        <div class="col search-input-col">
            <?php
            $raw_s = get_search_query(false);
            $form_val = (have_posts() && is_string($raw_s) && trim($raw_s) !== '') ? trim($raw_s) : '';
            ?>
            <input class="form-control form-control-lg form-control-borderless search-input" 
                   type="search" 
                   name="s" 
                   placeholder="Search topics or keywords" 
                   value="<?php echo esc_attr($form_val); ?>" 
                   autocomplete="off" 
                   aria-label="Search topics or keywords" />
        </div>
        <!--end of col-->
        <div class="col-auto search-btn-col">
            <button class="btn btn-lg btn-success search-submit-btn" type="submit">Search</button>
        </div>
        <!--end of col-->
    </div>
</form>

