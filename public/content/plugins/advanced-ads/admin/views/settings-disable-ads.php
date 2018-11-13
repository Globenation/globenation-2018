<label><input id="advanced-ads-disable-ads-all" type="checkbox" value="1" name="<?php echo ADVADS_SLUG ?>[disabled-ads][all]" <?php 
	checked( $disable_all, 1 ); ?>><?php _e( 'Disable all ads in frontend', 'advanced-ads' ); ?></label>
<p class="description"><?php _e( 'Use this option to disable all ads in the frontend, but still be able to use the plugin.', 'advanced-ads' ); ?></p>

<label><input id="advanced-ads-disable-ads-404" type="checkbox" value="1" name="<?php 
	echo ADVADS_SLUG; ?>[disabled-ads][404]" <?php
	checked( $disable_404, 1 );
	?>><?php _e( 'Disable ads on 404 error pages', 'advanced-ads' ); ?></label>

<br/><label><input id="advanced-ads-disable-ads-archives" type="checkbox" value="1" name="<?php echo ADVADS_SLUG; ?>[disabled-ads][archives]" <?php
	checked( $disable_archives, 1 );
	?>><?php _e( 'Disable ads on non-singular pages', 'advanced-ads' ); ?></label>
	<p class="description"><?php _e( 'e.g. archive pages like categories, tags, authors, front page (if a list)', 'advanced-ads' ); ?></p>
<label><input id="advanced-ads-disable-ads-secondary" type="checkbox" value="1" name="<?php
	echo ADVADS_SLUG;
	?>[disabled-ads][secondary]" <?php
	checked( $disable_secondary, 1 );
	?>><?php _e( 'Disable ads on secondary queries', 'advanced-ads' ); ?></label>
	<p class="description"><?php _e( 'Secondary queries are custom queries of posts outside the main query of a page. Try this option if you see ads injected on places where they shouldn’t appear.', 'advanced-ads' ); ?></p>

<label><input id="advanced-ads-disable-ads-feed" type="checkbox" value="1" name="<?php
	echo ADVADS_SLUG;
	?>[disabled-ads][feed]" <?php
	checked( $disable_feed, 1 );
	?>><?php _e( 'Disable ads in Feed', 'advanced-ads' ); ?></label>