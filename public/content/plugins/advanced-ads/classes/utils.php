<?php
class Advanced_Ads_Utils {
	/**
	* Merges multiple arrays, recursively, and returns the merged array.
	*
	* This function is similar to PHP's array_merge_recursive() function, but it
	* handles non-array values differently. When merging values that are not both
	* arrays, the latter value replaces the former rather than merging with it.
	*
	* Example:
	* $link_options_1 = array( 'fragment' => 'x', 'class' => array( 'a', 'b' ) );
	* $link_options_2 = array( 'fragment' => 'y', 'class' => array( 'c', 'd' ) );
	* // This results in array( 'fragment' => 'y', 'class' => array( 'a', 'b', 'c', 'd' ) ).
	*
	* @param array $arrays An arrays of arrays to merge.
	* @param bool $preserve_integer_keys (optional) If given, integer keys will be preserved and merged instead of appended.
	* @return array The merged array.
	* @copyright Copyright 2001 - 2013 Drupal contributors. License: GPL-2.0+. Drupal is a registered trademark of Dries Buytaert.
	*/
	public static function merge_deep_array( array $arrays, $preserve_integer_keys = FALSE ) {
		$result = array();
		foreach ( $arrays as $array ) {
			if ( ! is_array( $array ) ) { continue; }

			foreach ( $array as $key => $value ) {
				// Renumber integer keys as array_merge_recursive() does unless
				// $preserve_integer_keys is set to TRUE. Note that PHP automatically
				// converts array keys that are integer strings (e.g., '1') to integers.
				if ( is_integer( $key ) && ! $preserve_integer_keys ) {
					$result[] = $value;
				}
				// Recurse when both values are arrays.
				elseif ( isset( $result[ $key ] ) && is_array( $result[ $key ] ) && is_array( $value ) ) {
					$result[ $key ] = self::merge_deep_array( array( $result[ $key ], $value ), $preserve_integer_keys );
				}
				// Otherwise, use the latter value, overriding any previous value.
				else {
					$result[ $key ] = $value;
				}
			}
		}
		return $result;
	}

	/**
	 * Convert array of html attributes to string.
	 *
	 * @param array $data
	 * @return string
	 * @since untagged
	 */
	public static function build_html_attributes( $data ) {
		$result = '';
		foreach ( $data as $_html_attr => $_values ){
			if ( $_html_attr == 'style' ){
				$_style_values_string = '';
				foreach ( $_values as $_style_attr => $_style_values ){
					if ( is_array( $_style_values ) ) {
						$_style_values_string .= $_style_attr . ': ' .implode( ' ', $_style_values ). '; '; }
					else {
						$_style_values_string .= $_style_attr . ': ' .$_style_values. '; '; }
				}
				$result .= " style=\"$_style_values_string\"";
			} else {
				if ( is_array( $_values ) ) {
					$_values_string = esc_attr( implode( ' ', $_values ) ); }
				else {
					$_values_string = esc_attr( $_values ); }
				$result .= " $_html_attr=\"$_values_string\"";
			}
		}
		return $result;
	}

	/**
	 * Get inline asset.
	 *
	 * @param str $content
	 * @return str $content
	 */
	public static function get_inline_asset( $content ) {
		if ( Advanced_Ads_Checks::active_autoptimize() ) {
			return '<!--noptimize-->' . $content . '<!--/noptimize-->';
		}
		return $content;
	}

	/**
	 * Get nested ads of an ad or a group.
	 *
	 * @param str $id Id.
	 * @param str $type Type (placement, ad or group).
	 * @return array of Advanced_Ads_Ad objects.
	 */
	public static function get_nested_ads( $id, $type ) {
		$result = array();

		switch( $type ) {
			case 'placement':
				$placements = Advanced_Ads::get_ad_placements_array();
				if ( isset( $placements[ $id ]['item'] ) ) {
					$item = explode( '_', $placements[ $id ]['item'] );
					if ( isset( $item[1] ) ) {
						return self::get_nested_ads( $item[1], $item[0] );
					}
				}
			case 'ad':
				$ad = new Advanced_Ads_Ad( $id );
				$result[] = $ad;
				if ( 'group' === $ad->type && ! empty( $ad->output['group_id'] ) ) {
					$result = array_merge( $result, self::get_nested_ads( $ad->output['group_id'], 'group' ) );
				}
				break;
			case 'group':
				$group = new Advanced_Ads_Group( $id );
				$ads = $group->get_all_ads();
				foreach ( $ads as $ad ) {
					$result = array_merge( $result, self::get_nested_ads( $ad->ID, 'ad' ) );
				}
				break;
		}
		return $result;
	}
}
?>
