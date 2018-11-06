<!-- MOBILE THEME COLORS -->
<?php echo td_panel_generator::box_start('Theme colors', false); ?>

<div class="td-box-section-title">Theme color</div>

<!-- Theme color -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">ACCENT COLOR</span>
        <p>Select theme accent color</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::color_picker(array(
            'ds' => 'td_option',
            'option_id' => 'tds_theme_color_mob',
            'default_color' => '#4db2ec'
        ));
        ?>
    </div>
</div>

<div class="td-box-section-separator"></div>
<div class="td-box-section-title">Menu bar</div>

<!-- Menu background color -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">BACKGROUND COLOR</span>
        <p>Select menu bar background color</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::color_picker(array(
            'ds' => 'td_option',
            'option_id' => 'tds_menu_background_mob',
            'default_color' => '#222'
        ));
        ?>
    </div>
</div>

<!-- Menu bar icons color -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">ICONS COLOR</span>
        <p>Select menu and search icons color</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::color_picker(array(
            'ds' => 'td_option',
            'option_id' => 'tds_menu_icon_color_mob',
            'default_color' => ''
        ));
        ?>
    </div>
</div>

<div class="td-box-section-separator"></div>
<div class="td-box-section-title">Mobile Menu</div>

<!-- Menu gradient color -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">GRADIENT COLOR</span>
        <p>Select menu panel background gradient</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::color_picker(array(
            'ds' => 'td_option',
            'option_id' => 'tds_menu_gradient_one_mob',
            'default_color' => '#333145'
        ));
        echo td_panel_generator::color_picker(array(
            'ds' => 'td_option',
            'option_id' => 'tds_menu_gradient_two_mob',
            'default_color' => '#b8333e'
        ));
        ?>
    </div>
</div>

<!-- Menu text color -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">TEXT/ICONS COLOR</span>
        <p>Select text/icons color</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::color_picker(array(
            'ds' => 'td_option',
            'option_id' => 'tds_menu_text_color_mob',
            'default_color' => '#ffffff'
        ));
        ?>
    </div>
</div>

<!-- Menu text active and hover color -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">ACTIVE/HOVER TEXT COLOR</span>
        <p>Select a text active/hover color for the opened menu</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::color_picker(array(
            'ds' => 'td_option',
            'option_id' => 'tds_menu_text_active_color_mob',
            'default_color' => ''
        ));
        ?>
    </div>
</div>

<!-- Buttons color -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">BUTTONS BACKGROUND/TEXT COLOR</span>
        <p>Select background and text color</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::color_picker(array(
            'ds' => 'td_option',
            'option_id' => 'tds_menu_button_background_mob',
            'default_color' => '#ffffff'
        ));
        echo td_panel_generator::color_picker(array(
            'ds' => 'td_option',
            'option_id' => 'tds_menu_button_color_mob',
            'default_color' => '#000000'
        ));
        ?>
    </div>
</div>


<div class="td-box-section-separator"></div>
<div class="td-box-section-title">Footer</div>

<!-- Footer background color -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">BACKGROUND COLOR</span>
        <p>Select footer background color</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::color_picker(array(
            'ds' => 'td_option',
            'option_id' => 'tds_footer_background_mob',
            'default_color' => '#222'
        ));
        ?>
    </div>
</div>

<!-- Footer text color -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">TEXT COLOR</span>
        <p>Select footer text color</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::color_picker(array(
            'ds' => 'td_option',
            'option_id' => 'tds_footer_text_color_mob',
            'default_color' => ''
        ));
        ?>
    </div>
</div>

<div class="td-box-section-separator"></div>
<div class="td-box-section-title">Sub-footer</div>

<!-- Sub-footer background color -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">BACKGROUND COLOR</span>
        <p>Select sub-footer background color</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::color_picker(array(
            'ds' => 'td_option',
            'option_id' => 'tds_sub_footer_background_mob',
            'default_color' => '#000'
        ));
        ?>
    </div>
</div>

<!-- Sub-footer text color -->
<div class="td-box-row">
    <div class="td-box-description">
        <span class="td-box-title">TEXT COLOR</span>
        <p>Select sub-footer text color(copyright).</p>
    </div>
    <div class="td-box-control-full">
        <?php
        echo td_panel_generator::color_picker(array(
            'ds' => 'td_option',
            'option_id' => 'tds_sub_footer_text_color_mob',
            'default_color' => ''
        ));
        ?>
    </div>
</div>

<?php echo td_panel_generator::box_end();?>