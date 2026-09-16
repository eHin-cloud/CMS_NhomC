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
            <i class="fa fa-search fas fa-search" aria-hidden="true"></i>
        </div>
        <!--end of col-->
        <div class="col search-input-col">
            <input class="form-control form-control-lg form-control-borderless search-input" 
                   type="search" 
                   name="s" 
                   placeholder="Search topics or keywords" 
                   value="<?php echo esc_attr(get_search_query()); ?>" 
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
