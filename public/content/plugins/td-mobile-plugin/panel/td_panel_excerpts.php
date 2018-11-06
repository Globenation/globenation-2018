<!-- EXCERPTS -->
<?php
echo td_panel_generator::box_start('Excerpts', false);

foreach (td_api_module::get_all('mob') as $td_module_class => $td_module_array) {

    if (!empty($td_module_array['excerpt_title']) or !empty($td_module_array['excerpt_content'])) {

        $td_box_title = $td_module_array['text'];
        ?>

        <div class="td-box-section-title"><?php echo $td_box_title; ?></div>


        <?php if (!empty($td_module_array['excerpt_title'])) { ?>
            <!-- TITLE LENGTH -->
            <div class="td-box-row">
                <div class="td-box-description">
                    <span class=" td-box-title td-title-on-row">TITLE LENGTH</span>
                    <p></p>
                </div>
                <div class="td-box-control-full">
                    <?php
                    echo td_panel_generator::input(array(
                        'ds' => 'td_option',
                        'option_id' => $td_module_class . '_title_excerpt',
                        'placeholder' => $td_module_array['excerpt_title']
                    ));
                    ?>
                </div>
            </div>
        <?php } ?>


        <?php if (!empty($td_module_array['excerpt_content'])) { ?>
            <!-- CONTENT LENGTH LENGTH -->
            <div class="td-box-row">
                <div class="td-box-description">
                    <span class=" td-box-title td-title-on-row">CONTENT LENGTH</span>
                    <p></p>
                </div>
                <div class="td-box-control-full">
                    <?php
                    echo td_panel_generator::input(array(
                        'ds' => 'td_option',
                        'option_id' => $td_module_class . '_content_excerpt',
                        'placeholder' => $td_module_array['excerpt_content']
                    ));
                    ?>
                </div>
            </div>
        <?php } ?>

        <div class="td-box-section-separator"></div>

    <?php

    }
}
echo td_panel_generator::box_end();
?>