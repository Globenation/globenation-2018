<!-- MOBILE ADS -->
<?php echo td_panel_generator::box_start('Mobile ads', false); ?>

<!-- AD title -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">AD TITLE:</span>
        <p>Displays a title above each ad, by default the ad title is not set.</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::input(array(
            'ds' => 'td_option',
            'option_id' => 'tds_ad_title_mob',
            'placeholder' => '- Advertisement -',
        ));
        ?>
    </div>
</div>

<div class="td-box-section-separator"></div>
<div class="td-box-section-title">General ADS</div>

<!-- Header AD -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">Header ad</span>
        <p>This ad appears bellow the menu.</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::textarea(array(
            'ds' => 'td_ads',
            'item_id' => 'header_mob',
            'option_id' => 'ad_code',
        ));
        ?>
    </div>
</div>

<!-- Footer AD -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">Footer ad</span>
        <p>This ad appears above the footer.</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::textarea(array(
            'ds' => 'td_ads',
            'item_id' => 'footer_mob',
            'option_id' => 'ad_code',
        ));
        ?>
    </div>
</div>

<!-- Smart list AD -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">Smart list ad</span>
        <p>This ad appears on smart lists.</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::textarea(array(
            'ds' => 'td_ads',
            'item_id' => 'smart_list_mob',
            'option_id' => 'ad_code',
        ));
        ?>
    </div>
</div>

<!-- Loop AD -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">Loop ad</span>
        <p>This ad appears on loop (blog, category, tag and archive pages).</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::textarea(array(
            'ds' => 'td_ads',
            'item_id' => 'loop_mob',
            'option_id' => 'ad_code',
        ));
        ?>
    </div>
</div>

<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">AFTER POST:</span>
        <p>After how many posts the loop ad will display. By default the ad is displayed after 5 posts.</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::input(array(
            'ds' => 'td_option',
            'option_id' => 'tds_loop_ad_module_mob',
            'placeholder' => 5,
        ));
        ?>
    </div>
</div>

<div class="td-box-section-separator"></div>
<div class="td-box-section-title">Article ADS</div>

<!-- Article top AD -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">Article top ad</span>
        <p>This ad appears single pages on top of the post content.</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::textarea(array(
            'ds' => 'td_ads',
            'item_id' => 'content_top_mob',
            'option_id' => 'ad_code',
        ));
        ?>
    </div>
</div>

<!-- Article bottom AD -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">Article bottom ad</span>
        <p>This ad appears on single pages at the bottom of the post content.</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::textarea(array(
            'ds' => 'td_ads',
            'item_id' => 'content_bottom_mob',
            'option_id' => 'ad_code',
        ));
        ?>
    </div>
</div>

<!-- Article inline AD -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">Article inline ad</span>
        <p>This ad appears single pages inside the post content.</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::textarea(array(
            'ds' => 'td_ads',
            'item_id' => 'content_inline_mob',
            'option_id' => 'ad_code',
        ));
        ?>
    </div>
</div>

<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">AFTER PARAGRAPH:</span>
        <p>After how many paragraphs the ad will display. The theme will analyze the content of each post and it will inject an ad after the selected number of paragraphs</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::input(array(
            'ds' => 'td_option',
            'option_id' => 'tds_inline_ad_paragraph_mob'
        ));
        ?>
    </div>
</div>

<?php echo td_panel_generator::box_end();?>