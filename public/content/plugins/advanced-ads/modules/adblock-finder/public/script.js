/**
 * Check if an ad blocker is enabled.
 *
 * @param {function} callback A callback function that is executed after the check has been done.
 *                            The 'is_enabled' (bool) variable is passed as the callback's first argument.
 */
;advanced_ads_check_adblocker = ( function( callback ) {
	var pending_callbacks = [];
	var is_enabled = null;

	function RAF( RAF_callback ) {
		var fn = window.requestAnimationFrame
		|| window.mozRequestAnimationFrame
		|| window.webkitRequestAnimationFrame
		|| function( RAF_callback ) { return setTimeout( RAF_callback, 16 ); };

		fn.call( window, RAF_callback );
	}

	RAF( function() {
		// Create a bait.
		var ad = document.createElement( 'div' );
		ad.innerHTML = '&nbsp;';
		ad.setAttribute( 'class', 'ad_unit ad-unit text-ad text_ad pub_300x250' );
		ad.setAttribute( 'style', 'width: 1px !important; height: 1px !important; position: absolute !important; left: 0px !important; top: 0px !important; overflow: hidden !important;' );
		document.body.appendChild( ad );

		RAF( function() {
			var styles = window.getComputedStyle && window.getComputedStyle( ad );
			var moz_binding = styles && styles.getPropertyValue( '-moz-binding' );

			is_enabled = ( styles && styles.getPropertyValue( 'display' ) === 'none' )
			|| ( typeof moz_binding === 'string' && moz_binding.indexOf( 'about:' ) !== -1 );

			// Call pending callbacks.
			for ( var i = 0; i < pending_callbacks.length; i++ ) {
				pending_callbacks[ i ]( is_enabled );
			}
			pending_callbacks = [];
		} );
	} );

	return function( callback ) {
		if ( is_enabled === null ) {
			pending_callbacks.push( callback );
			return;
		}
		// Run the callback immediately
		callback( is_enabled );
	}
}());

(function() {
	var advadsTracker = function( name, UID ) {
		this.name = name;
		this.UID = UID;
		this.analyticsObject = null;
		var that = this;
		var data = {
			hitType: 'event',
			eventCategory: 'Advanced Ads',
			eventAction: 'AdBlock',
			eventLabel: 'Yes',
			nonInteraction: true,
			transport: 'beacon'
		};

		/**
		 * check if someone has already requested the analytics.js and created a GoogleAnalyticsObject
		 */
		this.analyticsObject = ( 'string' == typeof( GoogleAnalyticsObject ) && 'function' == typeof( window[GoogleAnalyticsObject] ) )? window[GoogleAnalyticsObject] : false;

		if ( false === this.analyticsObject ) {
			// No one has requested analytics.js at this point. Require it
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','https://www.google-analytics.com/analytics.js','_advads_ga');

			_advads_ga( 'create', that.UID, 'auto', this.name );
			if ( advanced_ads_ga_anonymIP ) {
				_advads_ga( 'set', 'anonymizeIp', true );
			}
			_advads_ga( that.name + '.send', data );
		} else {
			// someone has already created a variable, use it to avoid conflicts.
			window.console && window.console.log( "Advanced Ads Analytics >> using other's variable named `" + GoogleAnalyticsObject + "`" );
			window[GoogleAnalyticsObject]( 'create', that.UID, 'auto', this.name );
			window[GoogleAnalyticsObject]( 'set', 'anonymizeIp', true );
			window[GoogleAnalyticsObject]( that.name + '.send', data );
		}
	}

	advanced_ads_check_adblocker( function( is_enabled ) {
		// Send data to Google Analytics if an ad blocker was detected.
		if ( is_enabled && 'string' === typeof advanced_ads_ga_UID && advanced_ads_ga_UID ) {
			new advadsTracker( 'advadsTracker', advanced_ads_ga_UID );
		}
	} );
}());

