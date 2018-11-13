<?php
/**
 * Template for a single row in the group list
 *
 * @package   Advanced_Ads_Admin
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright since 2013 Thomas Maier, webgilde GmbH
 *
 */

?><tr class="advads-group-row">
	<td>
		<input type="hidden" class="advads-group-id" name="advads-groups[<?php echo $group->id; ?>][id]" value="<?php echo absint( $group->id ); ?>"/>
		<strong><a class="row-title" href="#"><?php echo $group->name; ?></a></strong>
		<p class="description"><?php echo $group->description; ?></p>
		<?php echo $this->render_action_links( $group ); ?>
		<div class="hidden advads-usage">
		    <label><?php esc_attr_e( 'shortcode', 'advanced-ads' ); ?>
				<code><input type="text" onclick="this.select();" style="width: 200px;" value='[the_ad_group id="<?php echo absint( $group->id ); ?>"]'/></code>
			</label><br/>
			<label><?php esc_attr_e( 'template', 'advanced-ads' ); ?>
				<code><input type="text" onclick="this.select();" value="the_ad_group(<?php echo absint( $group->id ); ?>);"/></code>
			</label>
			<p><?php printf( __( 'Learn more about using groups in the <a href="%s" target="_blank">manual</a>.', 'advanced-ads' ), ADVADS_URL . 'manual/ad-groups/#utm_source=advanced-ads&utm_medium=link&utm_campaign=groups' ); ?></p>
		</div>
	</td>
	<td>
		<ul><?php $_type = isset( $this->types[ $group->type ]['title'] ) ? $this->types[ $group->type ]['title'] : 'default'; ?>
		    <li><strong><?php 
		    /*
		     * translators: %s is the name of a group type
		     */
		    printf(esc_attr__( 'Type: %s', 'advanced-ads' ), $_type ); ?></strong></li>
		    <li><?php 
		    /*
		     * translators: %s is the ID of an ad group
		     */
		    printf(esc_attr__( 'ID: %s', 'advanced-ads' ), $group->id ); ?></li>
		</ul>
	</td>
	<td class="advads-ad-group-list-ads"><?php $this->render_ads_list( $group ); ?></td>
</tr>
