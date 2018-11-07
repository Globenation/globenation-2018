<div class="wrap woocommerce">
    <div class="icon32" id="icon-woocommerce-importer"><br></div>
    <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=hf_wordpress_customer_im_ex') ?>" class="nav-tab <?php echo ($tab == 'export') ? 'nav-tab-active' : ''; ?>"><?php _e('User/Customer Export', 'wf_customer_import_export'); ?></a>
        <a href="<?php echo admin_url('admin.php?import=wordpress_hf_user_csv') ?>" class="nav-tab <?php echo ($tab == 'import') ? 'nav-tab-active' : ''; ?>"><?php _e('User/Customer Import', 'wf_customer_import_export'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=hf_wordpress_customer_im_ex&tab=help'); ?>" class="nav-tab <?php echo ('help' == $tab) ? 'nav-tab-active' : ''; ?>"><?php _e('Help', 'wf_csv_import_export'); ?></a>
        <a href="https://www.webtoffee.com/product/wordpress-users-woocommerce-customers-import-export/" target="_blank" class="nav-tab nav-tab-premium"><?php _e('Upgrade to Premium for More Features', 'wf_csv_import_export'); ?></a>
    </h2>
    <?php
    switch ($tab) {
        case "export" :
            $this->admin_export_page();
            include_once("export/market.php");
            break;
        case "help" :
            $this->admin_help_page();
            break;
        default :
            $this->admin_export_page();
            break;
    }
    ?>
    
</div>