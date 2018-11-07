
<div class=" woocommerce">
    <div class="icon32" id="icon-woocommerce-importer"><br></div>
    <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=hf_wordpress_customer_im_ex') ?>" class="nav-tab "><?php _e('User/Customer Export', 'wf_customer_import_export'); ?></a>
        <a href="<?php echo admin_url('admin.php?import=wordpress_hf_user_csv') ?>" class="nav-tab nav-tab-active"><?php _e('User/Customer Import', 'wf_customer_import_export'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=hf_wordpress_customer_im_ex&tab=help'); ?>" class="nav-tab"><?php _e('Help', 'wf_csv_import_export'); ?></a>
        <a href="https://www.webtoffee.com/product/wordpress-users-woocommerce-customers-import-export/" target="_blank" class="nav-tab nav-tab-premium"><?php _e('Upgrade to Premium for More Features', 'wf_csv_import_export'); ?></a>
    </h2>
    <?php
    include_once("market.php");
    ?>

</div>
<div class="tool-box bg-white p-20p pipe-view">
    <h3 class="title"><?php _e('Import Users in CSV Format:', 'wf_customer_import_export'); ?></h3>
    <p><?php _e('Import Users in CSV format from your computer.You can import users/customers (in CSV format) in to the shop.', 'wf_customer_import_export'); ?></p>
    <?php if (!empty($upload_dir['error'])) : ?>
        <div class="error"><p><?php _e('Before you can upload your import file, you will need to fix the following error:', 'wf_customer_import_export'); ?></p>
            <p><strong><?php echo $upload_dir['error']; ?></strong></p></div>

    <?php else : ?>
        <form enctype="multipart/form-data" id="import-upload-form" method="post" action="<?php echo esc_attr(wp_nonce_url($action, 'import-upload')); ?>">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th>
                            <label for="upload"><?php _e('Select a file from your computer', 'wf_customer_import_export'); ?></label>
                        </th>
                        <td>
                            <input type="file" id="upload" name="import" size="25" />
                            <input type="hidden" name="action" value="save" />
                            <input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
                            <small><?php printf(__('Maximum size: %s'), $size); ?></small>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" class="button button-primary" value="<?php esc_attr_e('Upload file and import'); ?>" />
            </p>
        </form>
    <?php endif; ?>
</div>