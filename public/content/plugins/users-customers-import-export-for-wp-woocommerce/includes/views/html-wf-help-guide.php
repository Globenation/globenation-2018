<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style>
    .help-guide .cols {
        display: flex;
    }
    .help-guide .inner-panel {
        padding: 40px;
        background-color: #FFF;
        margin: 15px 10px;
        box-shadow: 1px 1px 5px 1px rgba(0,0,0,.1);
        text-align: center;
    }
    .help-guide .inner-panel p{
        margin-bottom: 20px;
    }
    .help-guide .inner-panel img{
        margin:30px 15px 0;
    }

</style>
<div class="pipe-main-box">
    <div class="tool-box bg-white p-20p pipe-view">
        <div id="tab-help" class="coltwo-col panel help-guide">
            <div class="cols">
                <div class="inner-panel" style="">
                    <img src="<?php echo plugins_url(basename(plugin_dir_path(WF_CustomerImpExpCsv_FILE))) . '/images/video.png'; ?>"/>
                    <h3><?php _e('How-to-setup', 'wf_customer_import_export'); ?></h3>
                    <p style=""><?php _e('Get to know about our produt in 3 minutes with this video', 'wf_customer_import_export'); ?></p>
                    <a href="https://www.webtoffee.com/setting-wordpress-users-woocommerce-customers-import-export-plugin/" target="_blank" class="button button-primary">
                        <?php _e('Setup Guide', 'wf_customer_import_export'); ?></a>
                </div>

                <div class="inner-panel" style="">
                    <img src="<?php echo plugins_url(basename(plugin_dir_path(WF_CustomerImpExpCsv_FILE))) . '/images/documentation.png'; ?>"/>
                    <h3><?php _e('Documentation', 'wf_customer_import_export'); ?></h3>
                    <p style=""><?php _e('Refer to our documentation to set and get started', 'wf_customer_import_export'); ?></p>
                    <a target="_blank" href="https://www.webtoffee.com/category/documentation/wordpress-users-woocommerce-customers-import-export/" class="button-primary"><?php _e('Documentation', 'wf_customer_import_export'); ?></a> 
                </div>

                <div class="inner-panel" style="">
                    <img src="<?php echo plugins_url(basename(plugin_dir_path(WF_CustomerImpExpCsv_FILE))) . '/images/support.png'; ?>"/>
                    <h3><?php _e('Support', 'wf_customer_import_export'); ?></h3>
                    <p style=""><?php _e('We would love to help you on any queries or issues.', 'wf_customer_import_export'); ?></p>
                    <a href="https://www.webtoffee.com/support/" target="_blank" class="button button-primary">
                        <?php _e('Contact Us', 'wf_customer_import_export'); ?></a>
                </div>
            </div>
        </div>
    </div>   
    <?php include_once("export/market.php"); ?>
    <div class="clearfix"></div>
</div>