<?php
class Advanced_Ads_Compatibility {
	public function __construct() {
		// Elementor plugin
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			add_filter( 'advanced-ads-placement-content-injection-xpath', array( $this, 'content_injection_elementor' ), 10, 1 );
		}
		if ( defined( 'WP_ROCKET_VERSION' ) ) {
			add_filter( 'rocket_excluded_inline_js_content', array( $this, 'exclude_inline_js' ) );
		}
	}

	/**
	 * Modify xPath expression for Elementor plugin.
	 * The plugin does not wrap newly created text in 'p' tags.
	 *
	 * @param str $tag
	 * @return xPath expression
	 */
	public function content_injection_elementor( $tag ) {
		if ( $tag === 'p' ) {
			// 'p' or 'div.elementor-widget-text-editor' without nested 'p'
			$tag = "*[self::p or self::div[@class and contains(concat(' ', normalize-space(@class), ' '), ' elementor-widget-text-editor ') and not(descendant::p)]]";
		}
		return $tag;
	}

	/**
	 * Prevent the 'advanced_ads_ready' function declaration from being merged with other JS
	 * and outputted into the footer. This is needed because WP Rocket does not output all
	 * the code that depends on this function into the footer.
	 *
	 * @param array $pattern Patterns to match in inline JS content.
	 */
	function exclude_inline_js( $pattern ) {
		$pattern[] = 'advanced_ads_ready';
		return $pattern;
	}
}
