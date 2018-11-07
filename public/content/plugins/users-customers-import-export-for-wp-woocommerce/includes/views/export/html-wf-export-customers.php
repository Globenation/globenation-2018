

<div class="tool-box bg-white p-20p pipe-view">
    <h3 class="title"><?php _e('Export Users in CSV Format:', 'wf_customer_import_export'); ?></h3>
    <p><?php _e('Export and download your Users in CSV format. This file can be used to import users back into your Website.', 'wf_customer_import_export'); ?></p>
    <form action="<?php echo admin_url('admin.php?page=hf_wordpress_customer_im_ex&action=export'); ?>" method="post">

        <table class="form-table">
            <tr>
                <th>
                    <label for="v_user_roles"><?php _e('User Roles', 'wf_customer_import_export'); ?></label>
                </th>
                <td>
                    <select id="v_user_roles" name="user_roles[]" data-placeholder="<?php _e('All Roles', 'wf_customer_import_export'); ?>" class="wc-enhanced-select" multiple="multiple">
                        
                        <?php
                            global $wp_roles;

                            foreach ( $wp_roles->role_names as $role => $name ) {
                                    echo '<option value="' . esc_attr( $role ) . '">' . $name . '</option>';
                            }
                        ?>
                    </select>
                                                        
                    <p style="font-size: 12px"><?php _e('Users with these roles will be exported.', 'wf_customer_import_export'); ?></p>
                </td>
            </tr>  
            <tr>
                <th>
                    <label for="v_offset"><?php _e('Offset', 'wf_customer_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="offset" id="v_offset" placeholder="<?php _e('0', 'wf_customer_import_export'); ?>" class="input-text" />
                    <p style="font-size: 12px"><?php _e('The number of users to skip before returning.', 'wf_customer_import_export'); ?></p>
                </td>
            </tr>            
            <tr>
                <th>
                    <label for="v_limit"><?php _e('Limit', 'wf_customer_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="limit" id="v_limit" placeholder="<?php _e('Unlimited', 'wf_customer_import_export'); ?>" class="input-text" />
                    <p style="font-size: 12px"><?php _e('The number of users to return.', 'wf_customer_import_export'); ?></p>
                </td>
            </tr>
            
                  
            
            
            
            <tr>
                <th>
                    <label for="v_columns"><?php _e('Columns', 'wf_customer_import_export'); ?></label>
                </th>
            <table id="datagrid">
                <th style="text-align: left;">
                    <label for="v_columns"><?php _e('Column', 'wf_customer_import_export'); ?></label>
                </th>
                <th style="text-align: left;">
                    <label for="v_columns_name"><?php _e('Column Name', 'wf_customer_import_export'); ?></label>
                </th>
                <?php 
                ?>
                <?php foreach ($post_columns as $pkey => $pcolumn) {
                            
                         ?>
            <tr>
                <td>
                    <input name= "columns[<?php echo $pkey; ?>]" type="checkbox" value="<?php echo $pkey; ?>" checked>
                    <label for="columns[<?php echo $pkey; ?>]"><?php _e($pcolumn, 'wf_customer_import_export'); ?></label>
                </td>
                <td>
                     <input type="text" name="columns_name[<?php echo $pkey; ?>]"  value="<?php echo $pkey; ?>" class="input-text" />
                </td>
            </tr>
                <?php } ?>
                
            </table><br/>
            </tr>
            
            
            
            
            

        </table>
        <p class="submit"><input type="submit" class="button button-primary" value="<?php _e('Export Users', 'wf_customer_import_export'); ?>" /></p>
    </form>
</div>

<?php if(!class_exists('WooCommerce')){ ?>
<script>
    jQuery(document).ready(function () {
        jQuery('.wc-enhanced-select').select2();

    });
</script>
<?php } ?>