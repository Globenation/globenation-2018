<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<style>
    .wf_customer_import_export.market-box{
    width:30%;
    float: right;
}

.wf_customer_import_export .pipe-premium-features{
    background: #fff;
    padding:5px;
}
.wf_customer_import_export .pipe-premium-features ul {
    padding-left: 20px;
    padding-right: 20px;
}
.wf_customer_import_export .pipe-premium-features li {
    margin-bottom: 15px;
    padding-left: 15px;
}
.wf_customer_import_export .pipe-premium-features li:before
{
    font-family: dashicons;
    text-decoration: inherit;
    font-weight: 400;
    font-style: normal;
    vertical-align: top;
    text-align: center;
    content: "\f147";
    margin-right: 10px;
    margin-left: -25px;
    font-size: 16px;
    color: #3085bb;
}
.wf_customer_import_export .pipe-premium-features .button {
    margin-bottom: 20px;
}
.wf_customer_import_export .pipe-premium-features .button-go-pro {
    box-shadow: none;
    border: 0;
    text-shadow: none;
    padding: 10px 15px;
    height: auto;
    font-size: 18px;
    border-radius: 4px;
    font-weight: 600;
    background: #00cb95;
    margin-top: 20px;
}
.wf_customer_import_export .pipe-premium-features .button-go-pro:hover,
.wf_customer_import_export .pipe-premium-features .button-go-pro:focus,
.wf_customer_import_export .pipe-premium-features .button-go-pro:active {
    background: #00a378;
}
.wf_customer_import_export .pipe-premium-features .button-doc-demo {
    border: 0;
    background: #d8d8dc;
    box-shadow: none;
    padding: 10px 15px;
    font-size: 15px;
    height: auto;
    margin-left: 10px;
    margin-right: 10px;
    margin-top: 10px;
}
.wf_customer_import_export .pipe-premium-features .button-doc-demo:hover,
.wf_customer_import_export .pipe-premium-features .button-doc-demo:focus,
.wf_customer_import_export .pipe-premium-features .button-doc-demo:active {
    background: #dfdfe4;
}
.wf_customer_import_export .xa-pipe-rating-link{color:#ffc600;}

.wf_customer_import_export .pipe-review-widget{    
    background: #fff;
    padding: 5px;
    /*margin-top: 23px;*/
}
.wf_customer_import_export .pipe-review-widget p{
    margin-right:5px;
    margin-left:5px;
}
</style>
<div class="wf_customer_import_export market-box table-box-main">
    <div class="pipe-review-widget">
        <?php
        echo sprintf(__('<div class=""><p><i>If you like the plugin please leave us a %1$s review!</i><p></div>', 'wf_customer_import_export'), '<a href="https://wordpress.org/support/plugin/users-customers-import-export-for-wp-woocommerce/reviews?rate=5#new-post" target="_blank" class="xa-pipe-rating-link" data-reviewed="' . esc_attr__('Thanks for the review.', 'wf_customer_import_export') . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>');
        ?>
    </div>
    <div class="pipe-premium-features">
    <center><a href="https://www.webtoffee.com/product/wordpress-users-woocommerce-customers-import-export/" target="_blank" class="button button-primary button-go-pro"><?php _e('Upgrade to Premium Version', 'wf_customer_import_export'); ?></a></center>
    <span>
        <ul>
            <li style='color:red;'><strong><?php _e('Your Business is precious! Go Premium!','wf_customer_import_export'); ?></strong></li>
        
                <li><?php _e('HikeForce Import Export Users Plugin Premium version helps you to seamlessly import/export Customer details into your Woocommerce Store.', 'wf_customer_import_export'); ?></li>
        
                <li><?php _e('Export/Import WooCommerce Customer details into a CSV file.', 'wf_customer_import_export'); ?><strong><?php _e('( Basic version supports only WordPress User details )', 'wf_customer_import_export'); ?></strong></li>
                <li><?php _e('Various Filter options for exporting Customers.', 'wf_customer_import_export'); ?></li>
                <li><?php _e('Map and Transform fields while Importing Customers.', 'wf_customer_import_export'); ?></li>
                <li><?php _e('Change values while importing Customers using Evaluation Fields.', 'wf_customer_import_export'); ?></li>
                <li><?php _e('Choice to Update or Skip existing imported Customers.', 'wf_customer_import_export'); ?></li>
                <li><?php _e('Choice to Send or Skip Emails for newly imported Customers.', 'wf_customer_import_export'); ?></li>
                <li><?php _e('Import/Export file from/to a remote server via FTP in Scheduled time interval with Cron Job.', 'wf_customer_import_export'); ?></li>
                <li><?php _e('Excellent Support for setting it up!', 'wf_customer_import_export'); ?></li>
        </ul>
    </span>
    <center> 
        
        <a href="https://www.webtoffee.com/category/documentation/wordpress-users-woocommerce-customers-import-export/" target="_blank" class="button button-doc-demo"><?php _e('Documentation', 'wf_customer_import_export'); ?></a></center>
    </div>
    
    </div>
