/*
 * advanced ads functions to be used directly within ad codes
 */

advads = {
        /**
	 * check if localstorage is supported/enabled by client
	 */
        supports_localstorage: function() {
            "use strict";
            try {
                if (!window || window.localStorage === undefined) {
                    return false;
		}
                // storage might be full or disabled
                window.localStorage.setItem("x", "x");
                window.localStorage.removeItem("x");
		return true;
            } catch(e) { 
                return false; 
            }
        },
	/**
	 * check if the ad is displayed more than {max} times per session
	 * every check increases the counter
	 *
	 * @param {string} name (no id needed, just any id-formated string)
	 * @param {type} max number of maximum times the ad can be displayed within the period
	 * @returns {bool} true if limit is reached
	 */
	max_per_session: function(name, max){
		var num = 1;
		if(max === undefined || parseInt( max ) === 0) { max = 1; }

		// check if cookie exists and get the value
		if(this.cookie_exists( name )){
			if(this.get_cookie( name ) >= max) { return true; }
			num = num + parseInt( this.get_cookie( name ) );
		}
		this.set_cookie( name, num );
		return false;
	},
	/**
	 * increase a cookie with an integer value by 1
	 *
	 * @param {str} name of the cookie
	 * @param {int} exdays days until cookie expires
	 */
	count_up: function( name, exdays ){
		var num = 1;

		// check if cookie exists and get the value
		if(this.cookie_exists( name )){
			num = num + parseInt( this.get_cookie( name ) );
		}
		this.set_cookie( name, num );
	},
	/**
	 * return true, if cookie exists
	 * return false, if not
	 * if not exists, create it
	 * use case: to check if something already happened in this page impression
	 *
	 * @param {type} name
	 * @returns {unresolved}
	 */
	set_cookie_exists: function( name ){
		if( get_cookie(name) ){
		    return true;
		}
		set_cookie( name, '', 0 );
		return false;
	},
	/**
	 * get a cookie value
	 *
	 * @param {str} name of the cookie
	 */
	get_cookie: function (name) {
		var i, x, y, ADVcookies = document.cookie.split( ";" );
		for (i = 0; i < ADVcookies.length; i++)
		{
			x = ADVcookies[i].substr( 0, ADVcookies[i].indexOf( "=" ) );
			y = ADVcookies[i].substr( ADVcookies[i].indexOf( "=" ) + 1 );
			x = x.replace( /^\s+|\s+$/g, "" );
			if (x === name)
			{
				return unescape( y );
			}
		}
	},
	/**
	 * set a cookie value
	 *
	 * @param {str} name of the cookie
	 * @param {str} value of the cookie
	 * @param {int} exdays days until cookie expires
	 *  set 0 to expire cookie immidiatelly
	 *  set null to expire cookie in the current session
	 */
	set_cookie: function (name, value, exdays, path, domain, secure) {
		// days in seconds	
		var expiry = ( exdays == null ) ? null : exdays * 24 * 60 * 60;
		this.set_cookie_sec( name, value, expiry, path, domain, secure );
	},
	/**
	 * set a cookie with expiry given in seconds
	 *
	 * @param {str} name of the cookie
	 * @param {str} value of the cookie
	 * @param {int} expiry seconds until cookie expires
	 *  set 0 to expire cookie immidiatelly
	 *  set null to expire cookie in the current session
	 */
	set_cookie_sec: function (name, value, expiry, path, domain, secure) {
		var exdate = new Date();
		exdate.setSeconds( exdate.getSeconds() + parseInt( expiry ) );
		document.cookie = name + "=" + escape( value ) +
				((expiry == null) ? "" : "; expires=" + exdate.toUTCString()) +
				((path == null) ? "; path=/" : "; path=" + path) +
				((domain == null) ? "" : "; domain=" + domain) +
				((secure == null) ? "" : "; secure");
	},
	/**
	 * check if a cookie is set and contains a value
	 *
	 * @param {str} name of the cookie
	 * @returns {bool} true, if cookie is set
	 */
	cookie_exists: function (name)
	{
		var c_value = this.get_cookie( name );
		if (c_value !== null && c_value !== "" && c_value !== undefined)
		{
			return true;
		}
		return false;
	},
	/**
	 * move one element into another
	 *
	 * @param {str} element selector of the element that should be moved
	 * @param {str} target selector of the element where to move
	 * @param {arr} options
	 */
	move: function( element, target, options )
	{

		var el = jQuery(element);
		
		if( typeof options === 'undefined' ){
		    options = {};
		}
		if( typeof options.css === 'undefined' ){
		    options.css = {};
		}
		if( typeof options.method === 'undefined' ){
		    options.method = 'prependTo';
		}

		// search for abstract target element
		if( target === '' && typeof options.target !== 'undefined' ){
		    switch( options.target ){
			case 'wrapper' : // wrapper
			    var offset = 'left';
			    if( typeof options.offset !== 'undefined' ){
				    offset = options.offset;
			    }
			    target = this.find_wrapper( element, offset );
			    break;
		    }
		}
		
		// use only visible elements
		if( typeof options.moveintohidden === 'undefined' ){
		    target = jQuery( target ).filter(':visible');
		}

		// switch insert method
		switch( options.method ){
		    case 'insertBefore' :
			el.insertBefore(target);
			break;
		    case 'insertAfter' :
			el.insertAfter(target);
			break;
		    case 'appendTo' :
			el.appendTo(target);
			break;
		    case 'prependTo' :
			el.prependTo(target);
			break;
		    default :
			el.prependTo(target);
		}
	},

	/**
	 * Set 'relative' position for a parent element.
	 *
	 * @param {str} element selector
	 */
	set_parent_relative: function( element ) {
		var el = jQuery(element);
		// give "position" style to parent element, if missing
		var parent = el.parent();
		if(parent.css('position') === 'static' || parent.css('position') === ''){
			parent.css('position', 'relative');
		}
	},

	/**
	 * make an absolute position element fixed at the current position
	 * hint: use only after DOM is fully loaded in order to fix a wrong position
	 *
	 * @param {str} element selector
	 * @param {obj} options
	 */
	fix_element: function( element, options ){
		this.set_parent_relative( element );

		var el = jQuery(element);

		// fix element at current position
		// get position for hidden elements by showing them for a very short time
		if( typeof options !== 'undefined' && options.is_invisible ){
		    el.show();
		}
		var topoffset = parseInt(el.offset().top);
		var leftoffset = parseInt(el.offset().left);
		if( typeof options !== 'undefined' && options.is_invisible ){
		    el.hide();
		}
		// reset "right" to prevent conflicts
		el.css('position', 'fixed').css('top', topoffset + 'px').css('left', leftoffset + 'px').css('right', '');
	},
	/**
	 * find the main wrapper
	 *  either id or first of its class
	 *
	 *  @param {str} element selector
	 *  @param {str} offset which position of the offset to check (left or right)
	 *  @return {str} selector
	 */
	find_wrapper: function( element, offset ){
		// first margin: auto element after body
		var returnValue;
		jQuery('body').children().each(function(key, value){
			// exclude current element
			// TODO exclude <script>
			if( value.id !== element.substring(1) ){
				// check offset value
				var checkedelement = jQuery( value );
				// check if there is space left or right of the element
				if( ( offset === 'right' && ( checkedelement.offset().left + jQuery(checkedelement).width() < jQuery(window).width() ) ) ||
					( offset === 'left' && checkedelement.offset().left > 0 ) ){
					// fix element
					if( checkedelement.css('position') === 'static' || checkedelement.css('position') === ''){
						checkedelement.css('position', 'relative');
					}
					// set return value
					returnValue = value;
					return false;
				}
			}
		});
		return returnValue;
	},
	/**
	 * center fixed element on the screen
	 *
	 * @param {str} element selector
	 */
	center_fixed_element: function( element ){
		var el = jQuery(element);
		// half window width minus half element width
		var left = ( jQuery(window).width() / 2 ) - ( parseInt( el.css('width')) / 2 );
		el.css('left', left + 'px');
	},
	/**
	 * center element vertically on the screen
	 *
	 * @param {str} element selector
	 */
	center_vertically: function( element ){
		var el = jQuery(element);
		// half window height minus half element height
		var left = ( jQuery(window).height() / 2 ) - ( parseInt( el.css('height')) / 2 );
		el.css('top', left + 'px');
	},
	/**
	 * close an ad and add a cookie
	 *
	 * @param {str} element selector
	 */
	close: function( element ){
		var wrapper = jQuery(element);
		// remove the ad
		wrapper.remove();
	},
	/**
	 * Wait until images are ready.
	 *
	 * @param {obj} $el jQuery object.
	 * @param {function} ready_callback Ready callback.
	 * derrived from https://github.com/alexanderdickson/waitForImages/blob/master/dist/jquery.waitforimages.js
	 */
	wait_for_images: function( $el, ready_callback ) {
		var loaded_count = 0;
		var srcs = [];

		$el.find( 'img[src][src!=""]' ).each( function () {
			srcs.push( this.src );
		});

		if ( srcs.length === 0 ) {
			ready_callback.call( $el );
		}

		jQuery.each( srcs, function( i, src ) {
			var image = new Image();
			image.src = src;
			var events = 'load error';

			jQuery( image ).one( events, function me( event ) {
				// Remove remaining handler (either 'load' or 'error').
				jQuery( this ).off( events, me );
				loaded_count++;

				if ( loaded_count == srcs.length ) {
					ready_callback.call( $el[0] );
					return false;
				}
			} );
		} );
	},

	privacy: {
		/**
		 * Get consent state.
		 *
		 * @return str
		 *     'not_needed' - consent is not needed.
		 *     'accepted' - consent was given.
		 *     'unknown' - consent was not given yet.
		 */
		get_state: function() {
			if ( ! window.advads_options || ! window.advads_options.privacy ) {
				return 'not_needed';
			}

			var options = window.advads_options.privacy;

			if ( ! options.enabled ) {
				return 'not_needed';
			}

			var method = options['consent-method'] ? options['consent-method'] : '0';

			switch ( method ) {
				case '0':
					return 'not_needed';
					break;
				case 'custom':
					if ( options['custom-cookie-value' === undefined] || options['custom-cookie-value'] === undefined ) {
						return 'not_needed';
					}

					var found = advads.get_cookie( options['custom-cookie-name'] );
					if ( typeof found !== 'string' ) {
						return 'unknown';
					}
					if ( ( options['custom-cookie-value'] === '' && found === '' )
						|| ( options['custom-cookie-value'] !== '' && found.indexOf( options['custom-cookie-value'] ) !== -1 ) ) {
						return 'accepted';
					}
					return 'unknown';
					break;
				default:
					return ( advads.cookie_exists( method ) ) ? 'accepted' : 'unknown';
			}
		},
		is_adsense_npa_enabled: function() {
			if ( ! window.advads_options || ! window.advads_options.privacy ) {
				return true;
			}
			var options = window.advads_options.privacy;
			return !! options['show-non-personalized-adsense'];
		}
	}

};
// highlight elements in frontend, if local storage variable is set
jQuery(document).ready(function(){
    // only trigger if local storage is available
    if( advads.supports_localstorage() && localStorage.getItem('advads_frontend_picker') ) {
	var advads_picker_cur, advads_picker_overlay = jQuery("<div id='advads-picker-overlay'>"),
	    advads_picker_no = [document.body, document.documentElement, document];
	    advads_picker_overlay.css({position: 'absolute', border: 'solid 2px #428bca',
		backgroundColor: 'rgba(66,139,202,0.5)', boxSizing: 'border-box',
		zIndex: 1000000, pointerEvents: 'none'}).prependTo('body');
	jQuery(document).mousemove(function(e) {
	    if (e.target === advads_picker_cur) {
		return;
	    }

	    if (~advads_picker_no.indexOf(e.target)) {
		advads_picker_cur = null;
		advads_picker_overlay.hide();
		return;
	    }

	    var target = jQuery(e.target),
		offset = target.offset(),
		width = target.outerWidth(),
		height = target.outerHeight();

	    advads_picker_cur = e.target;

	    advads_picker_overlay.css({
		top: offset.top,
		left: offset.left,
		width: width,
		height: height
	    }).show();
	    // log path
	    console.log( jQuery( advads_picker_cur ).getPath());

	});
	// save on click
	jQuery(document).click(function(e) {
		//console.log( advads_picker_cur );
		var path = jQuery( advads_picker_cur ).getPath();
		localStorage.setItem( 'advads_frontend_element', path );
		// console.log( jQuery( advads_picker_cur ).getPath() );
		window.location = localStorage.getItem('advads_prev_url');
	});
    };
});
/*
derrived from jQuery-GetPath v0.01, by Dave Cardwell. (2007-04-27)
http://davecardwell.co.uk/javascript/jquery/plugins/jquery-getpath/
Usage:
var path = $('#foo').getPath();
*/

jQuery.fn.extend({
	getPath: function( path, depth ) {
		// The first time this function is called, path won't be defined.
		if ( typeof path === 'undefined' ) path = '';
		if ( typeof depth === 'undefined' ) depth = 0;

		// If this element is <html> we've reached the end of the path.
		// also end after 2 elements
		if ( this.is('html')){
			return 'html > ' + path;
		} else if ( 3 === depth ){
			return path;
		}

		// Add the element name.
		var cur = this.get(0).nodeName.toLowerCase();

		// Determine the IDs and path.
		var el_id    = this.attr('id'),
		    el_class = this.attr('class');

		depth = depth + 1;

		// Add the #id if there is one. Ignore ID with number.
		if ( typeof el_id !== 'undefined' && ! /\d/.test( el_id ) ) {
			cur += '#' + el_id;
		} else if ( typeof el_class !== 'undefined' ){
			// Add classes if there is no id.
			el_class = el_class.split( /[\s\n]+/ );
			// Skip classes with numbers.
			el_class = jQuery.grep( el_class, function( element, index ) {
				return ! /\d/.test( element )
			});
			// Add 2 classes.
			if ( el_class.length ) {
				cur += '.' + el_class.slice( 0, 2 ).join( '.' );
			}
		}

		// add index if this element is not unique among its siblings
		if( this.siblings( cur ).length ){
			cur += ":eq(" + this.siblings( cur ).addBack().not( '#advads-picker-overlay' ).index( this ) + ")";
		}

		// Recurse up the DOM.
		if( path === '' ){
		    return this.parent().getPath( cur, depth );
		} else {
		    return this.parent().getPath( cur + ' > ' + path, depth );
		}
	}
});
