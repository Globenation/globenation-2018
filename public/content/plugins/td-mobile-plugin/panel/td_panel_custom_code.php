<!-- CUSTOM CODE -->
<?php
echo td_panel_generator::box_start('Custom code', false); ?>

<!-- Custom CSS -->
<div class="td-box-row">
    <div class="td-box-description td-box-full">
        <span class="td-box-title">WRITE YOUR OWN CSS HERE</span>
        <p>The css from this box will load on all the pages of the site. Press <strong>ctrl + space</strong> while editing to bring up a suggestion box.</p>
    </div>
</div>


<div class="td-box-row-margin-bottom">
    <?php
    echo td_panel_generator::css_editor(array(
        'ds' => 'td_option',
        'option_id' => 'tds_custom_css_mob',
    ));
    ?>
</div>

<!-- Custom JS -->
<div class="td-box-row">
    <div class="td-box-description td-box-full">
        <span class="td-box-title">YOUR CUSTOM JAVASCRIPT</span>
        <p>Add custom javascript easly, using this editor. Please do not include the &lt;script&gt; &lt;/script&gt;.</p>
    </div>
</div>
<div class="td-box-row-margin-bottom">
    <?php
    echo td_panel_generator::js_editor(array(
        'ds' => 'td_option',
        'option_id' => 'tds_custom_javascript_mob',
    ));
    ?>
</div>

<!-- Custom HTML -->
<div class="td-box-row">
    <div class="td-box-description td-box-full">
        <span class="td-box-title">YOUR CUSTOM HTML</span>
        <p>Add custom html easly, using this editor. The html will be placed at the end of the page.</p>
    </div>
</div>
<div class="td-box-row-margin-bottom">
    <?php
    echo td_panel_generator::html_editor(array(
        'ds' => 'td_option',
        'option_id' => 'tds_custom_html_mob',
    ));
    ?>
</div>
<?php
echo td_panel_generator::box_end();
?>