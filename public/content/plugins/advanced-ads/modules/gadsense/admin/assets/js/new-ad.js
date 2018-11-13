/**
 * Advanced Ads.
 *
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      http://webgilde.com
 * @copyright 2013-2018 Thomas Maier, webgilde GmbH
 */
;
(function ($) {
	"use strict";
	var parseCodeBtnClicked = false, advancedAdSenseHidden = false;
	$( document ).on( 'click', '.prevent-default', function( ev ) { ev.preventDefault() } );

	function resizeAdListHeader() {
		var th = $( '#mapi-list-header span' );
		var tb = $( '#mapi-table-wrap tbody tr' );
		var w = [];

		tb.first().find( 'td' ).each(function(){
			w.push( $( this ).width() );
		});

		th.each(function(i){
			if ( i != w.length - 1 ) {
				$( this ).width( w[i] );
			}
		});
	}

	$( window ).resize(function(){
		if ( $( '#mapi-wrap' ).length && $( '#mapi-wrap' ).is( ':visible' ) ) {
			resizeAdListHeader();
		}
	});

	function MapiMayBeSaveAdCode(){

		// MAPI not set up
		if ( 'undefined' == typeof AdsenseMAPI ) return;

		var slotId = $( '#unit-code' ).val();
		if ( !slotId ) return;

		var type = $( '#unit-type' ).val();
		var width = $( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val().trim();
		var height = $( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val().trim();
		var layout = $( '#ad-layout' ).val();
		var layoutKey = $( '#ad-layout-key' ).val();

		var code = false;

		switch ( type ) {
			case 'in-feed':
				code = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' +
						'<ins class="adsbygoogle" ' +
							 'style="display:block;" ' +
							 'data-ad-client="ca-' + AdsenseMAPI.pubId + '" ' +
							 'data-ad-slot="' + slotId + '" ' +
							 'data-ad-layout-key="' + layoutKey + '" ';
				if ( '' != layout ) {
					code += 'data-ad-layout="' + layout + '" ';
				}
				code += 'data-ad-format="fluid"></ins>' +
						'<script>' +
						'(adsbygoogle = window.adsbygoogle || []).push({});' +
						'</script>';
				break;
			case 'in-article':
				code = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' +
						'<ins class="adsbygoogle" ' +
							 'style="display:block;text-align:center;" ' +
							 'data-ad-client="ca-' + AdsenseMAPI.pubId + '" ' +
							 'data-ad-slot="' + slotId + '" ' +
							 'data-ad-layout="in-article" ' +
							 'data-ad-format="fluid"></ins>' +
						'<script>' +
						'(adsbygoogle = window.adsbygoogle || []).push({});' +
						'</script>';
				break;
			case 'matched-content':
				code = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' +
						'<ins class="adsbygoogle" ' +
							 'style="display:block;" ' +
							 'data-ad-client="ca-' + AdsenseMAPI.pubId + '" ' +
							 'data-ad-slot="' + slotId + '" ' +
							 'data-ad-format="autorelaxed"></ins>' +
						'<script>' +
						'(adsbygoogle = window.adsbygoogle || []).push({});' +
						'</script>';
				break;
			case 'link-responsive':
				code = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' +
						'<ins class="adsbygoogle" ' +
							 'style="display:block;" ' +
							 'data-ad-client="ca-' + AdsenseMAPI.pubId + '" ' +
							 'data-ad-slot="' + slotId + '" ' +
							 'data-ad-format="link"></ins>' +
						'<script>' +
						'(adsbygoogle = window.adsbygoogle || []).push({});' +
						'</script>';
				break;
			case 'link':
				code = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' +
						'<ins class="adsbygoogle" ' +
							 'style="display:block;width:' + width + 'px;height:' + height + 'px" ' +
							 'data-ad-client="ca-' + AdsenseMAPI.pubId + '" ' +
							 'data-ad-slot="' + slotId + '" ' +
							 'data-ad-format="link"></ins>' +
						'<script>' +
						'(adsbygoogle = window.adsbygoogle || []).push({});' +
						'</script>';
				break;
			case 'responsive':
				code = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' +
						'<ins class="adsbygoogle" ' +
							 'style="display:block;" ' +
							 'data-ad-client="ca-' + AdsenseMAPI.pubId + '" ' +
							 'data-ad-slot="' + slotId + '" ' +
							 'data-ad-format="auto"></ins>' +
						'<script>' +
						'(adsbygoogle = window.adsbygoogle || []).push({});' +
						'</script>';
				break;
			case 'normal':
				code = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' +
						'<ins class="adsbygoogle" ' +
							 'style="display:inline-block;width:' + width + 'px;height:' + height + 'px" ' +
							 'data-ad-client="ca-' + AdsenseMAPI.pubId + '" ' +
							 'data-ad-slot="' + slotId + '"></ins>' +
						'<script>' +
						'(adsbygoogle = window.adsbygoogle || []).push({});' +
						'</script>';
				break;
			default:
		}

		if ( code ) {
			MapiSaveAdCode( code, slotId );
		}

	}

	function MapiSaveAdCode( code, slot ) {
		if ( 'undefined' == typeof AdsenseMAPI.codes[ 'ca-' + AdsenseMAPI.pubId + ':' + slot ] ) {
			AdsenseMAPI.codes['ca-' + AdsenseMAPI.pubId + ':' + slot] = code;
			$( '#mapi-loading-overlay' ).css( 'display', 'block' );
			$.ajax({
				type: 'post',
				url: ajaxurl,
				data: {
					nonce: AdsenseMAPI.nonce,
					slot: slot,
					code: code,
					action: 'advads-mapi-reconstructed-code',
				},
				success: function( resp, status, XHR ) {
					$( '#mapi-loading-overlay' ).css( 'display', 'none' );
				},
				error: function( req, status, err ) {
					$( '#mapi-loading-overlay' ).css( 'display', 'none' );
				},
			});
		}
	}

	function makeReadOnly() {
		$( '#unit-code,#ad-layout,#ad-layout-key,[name="advanced_ad[width]"],[name="advanced_ad[height]"]' ).prop( 'readonly', true );
		$( '#unit-type option:not(:selected)' ).prop( 'disabled', true );
	}

	function undoReadOnly() {
		$( '#unit-code,#ad-layout,#ad-layout-key,[name="advanced_ad[width]"],[name="advanced_ad[height]"]' ).prop( 'readonly', false );
		$( '#unit-type option:not(:selected)' ).prop( 'disabled', false );
	}

	function closeAdSelector() {

		// close the ad unit selector
		setTimeout(function(){
			$( '#mapi-wrap' ).animate(
				{ height: 0, },
				360,
				function(){
					$( '#mapi-open-selector,.advads-adsense-show-code' ).css( 'display', 'inline' );
					$( '#mapi-wrap' ).css({
						display: 'none',
						height: 'auto',
					});
				}
			);
		}, 80);

	}


	/**
	 * Show / hide position warning.
	 */
	function show_float_warnings( unit_type ) {
		var resize_type = $('#ad-resize-type').val();
		var position = $( '#advanced-ad-output-position input[name="advanced_ad[output][position]"]:checked' ).val();

		if (
			( -1 !== [ 'link-responsive', 'matched-content', 'in-article', 'in-feed' ].indexOf( unit_type )
				|| ( 'responsive' === unit_type && 'manual' !== resize_type )
			)
			&& ( 'left' == position || 'right' == position )
		) {
			$('#ad-parameters-box-notices .advads-ad-notice-responsive-position').show();
		} else {
			$('#ad-parameters-box-notices .advads-ad-notice-responsive-position').hide();
		}
	}


	// On DOM ready
	$(function () {
		$( document ).on('click', '.advads-adsense-show-code', function(e){
			e.preventDefault();
			$( '.advads-adsense-code' ).show();
			$( '#mapi-open-selector' ).css( 'display', 'inline' );
			$( '#mapi-wrap' ).css( 'display', 'none' );
			$( this ).hide();
		})

		$( document ).on('click', '.advads-adsense-submit-code', function(ev){
			ev.preventDefault();
			parseCodeBtnClicked = true;
			var rawContent = $( '.advads-adsense-content' ).val();

			var parseResult = parseAdContent( rawContent );

			handleParseResult( parseResult );
		});

		$( document ).on( 'paramloaded', '#advanced-ads-ad-parameters', function(){
			var content = $( '#advanced-ads-ad-parameters input[name="advanced_ad[content]"]' ).val();
			var parseResult = parseAdContent( content );

			var adType = $( '[name="advanced_ad[type]"]:checked' ).val();

			if ( 'undefined' != typeof AdsenseMAPI ) {
				if ( 'adsense' != adType ) {
					if ( 'undefined' == typeof window['AdsenseMAPI'] ) {
						delete( window['AdsenseMAPI'] );
					}
				} else {
					if ( 'post-new.php' == gadsenseData.pagenow || ( 'draft' == AdsenseMAPI.adStatus && ! $( '#advads-ad-content-adsense' ).val() ) ) {
						advancedAdSenseHidden = true;
						$( '#mapi-wrap' ).siblings( 'label,div,hr,span.label' ).css( 'display', 'none' );
						$( '#mapi-open-selector a' ).trigger( 'click' );
						if ( '' == $( '#mapi-adunit-select' ).val() && 1 == $( '#mapi-adunit-select option' ).length ) {
							$( '#mapi-get-adunits' ).trigger( 'click' );
						}
					}
				}
			}
			handleParseResult( parseResult );
		} );

		function getAdCode( slotID ) {
			if ( 'undefined' != typeof AdsenseMAPI.codes[ slotID ] ) {
				getSavedDetails(slotID );
			} else {
				getRemoteCode( slotID );
			}

			// display ad slot and ad type for newly created AdSense ad
			if ( advancedAdSenseHidden ) {
				$( '#unit-type-block' ).add( $( '#unit-type-block' ).next() ).add( $( '#unit-type-block' ).next().next() ).css( 'display', 'block' );
				var codeBlock = $( '#unit-code' ).parent();
				codeBlock.add( codeBlock.prev() ).add( codeBlock.next() ).css( 'display', 'block' );
			}

		}

		$( document ).on('change', '#unit-type, #unit-code', function (ev) {
			if ( 'unit-code' == $( this ).attr( 'id' ) ) {
				var val = $( this ).val();
				if ( -1 != val.indexOf( gadsenseData.pubId.substr( 4 ) ) ) {
					$( '#advads-pubid-in-slot' ).css( 'display', 'block' );
					$( this ).css( 'background-color', 'rgba(255, 235, 59, 0.33)' );
				} else {
					$( '#unit-code' ).css( 'background-color', '#fff' );
					$( '#advads-pubid-in-slot' ).css( 'display', 'none' );
				}
			} else {
				$( '#unit-code' ).css( 'background-color', '#fff' );
				$( '#advads-pubid-in-slot' ).css( 'display', 'none' );
			}
			advads_update_adsense_type();
		});

		$( document ).on( 'change', '#ad-resize-type', function( ev ) {
			show_float_warnings( 'responsive' );
		} );

		function getRemoteCode( slotID ) {

			if ( '' == slotID ) return;
			$( '#mapi-loading-overlay' ).css( 'display', 'block' );

			$.ajax({
				type: 'post',
				url: ajaxurl,
				data: {
					nonce: AdsenseMAPI.nonce,
					action: 'advads_mapi_get_adCode',
					unit: slotID,
				},
				success: function(response,status,XHR){
					$( '#mapi-loading-overlay' ).css( 'display', 'none' );
					if ( 'undefined' != typeof response.code ) {
						$( '#remote-ad-code-msg' ).empty();
						var parsed = parseAdContent( response.code );
						if ( false !== parsed ) {
							AdsenseMAPI.codes[slotID] = response.code;
							undoReadOnly();
							setDetailsFromAdCode( parsed );
							makeReadOnly();
							$( '#remote-ad-code-error' ).css( 'display', 'none' );
							unitIsSupported( slotID );
						} else {
							$( '#remote-ad-code-error' ).css( 'display', 'block' );
						}

						// Update quota message if needed
						if (  1 == 0 ) {
							$( '#mapi-quota-message' ).text( response.quotaMsg );
							AdsenseMAPI.quota = response.quota;
							if ( 0 == response.quota ) {
								$( '#mapi-get-adcode,#mapi-get-adunits' ).prop( 'disabled', true );
							}
						}

						closeAdSelector();

					} else {
						if ( 'undefined' != typeof response.raw ) {
							$( '#remote-ad-code-msg' ).text( response.raw );
						} else if( 'undefined' != typeof response.msg ) {
							if ( 'doesNotSupportAdUnitType' == response.msg ) {
								unitIsNotSupported( slotID );
							} else {
								$( '#remote-ad-code-msg' ).text( response.msg );
							}
						}
					}
				},
				error: function(request,status,err){
					$( '#mapi-loading-overlay' ).css( 'display', 'none' );

				},
			});

		}

		function unitIsNotSupported( slotID ) {
			$( '#remote-ad-unsupported-ad-type' ).css( 'display', 'block' );
			AdsenseMAPI.unsupportedUnits[slotID] = 1;
			$( 'i[data-mapiaction="getCode"][data-slotid="' + slotID + '"]' ).addClass( 'disabled' );
			$( 'tr[data-slotid="' + slotID + '"] .unitcode > span' ).addClass( 'unsupported' );
			if ( ! $( 'tr[data-slotid="' + slotID + '"] .unittype a' ).length ) {
				var td = $( 'tr[data-slotid="' + slotID + '"] .unittype' );
				var content = td.text();
				td.html( '<a target="_blank" href="' + AdsenseMAPI.unsupportedLink + '" data-type="' + content + '">' + AdsenseMAPI.unsupportedText + '</a>' );
			}
			if ( ! $( 'tr[data-slotid="' + slotID + '"] .unitsize a' ).length ) {
				var td = $( 'tr[data-slotid="' + slotID + '"] .unitsize' );
				var content = td.text();
				td.html( '<a target="_blank" href="' + AdsenseMAPI.unsupportedLink + '" data-size="' + content + '">' + AdsenseMAPI.unsupportedText + '</a>' );
			}
			
		}
		
		function unitIsSupported( slotID ) {
			$( '#remote-ad-unsupported-ad-type' ).css( 'display', 'none' );
			if ( 'undefined' != typeof AdsenseMAPI.unsupportedUnits[slotID] ) {
				delete AdsenseMAPI.unsupportedUnits[slotID];
			}
			$( 'i[data-mapiaction="getCode"][data-slotid="' + slotID + '"]' ).removeClass( 'disabled' );
			$( 'tr[data-slotid="' + slotID + '"] .unitcode > span' ).removeClass( 'unsupported' );
			if ( $( 'tr[data-slotid="' + slotID + '"] .unittype a' ).length ) {
				var td = $( 'tr[data-slotid="' + slotID + '"] .unittype' );
				var content = $( 'tr[data-slotid="' + slotID + '"] .unittype a' ).attr( 'data-type' );
				td.text( content );
			}
			if ( $( 'tr[data-slotid="' + slotID + '"] .unitsize a' ).length ) {
				var td = $( 'tr[data-slotid="' + slotID + '"] .unitsize' );
				var content = $( 'tr[data-slotid="' + slotID + '"] .unitsize a' ).attr( 'data-size' );
				td.text( content );
			}
		}
		
		function getSavedDetails( slotID ) {
			if ( 'undefined' != typeof AdsenseMAPI.codes[slotID] ) {
				var code = AdsenseMAPI.codes[slotID];
				var parsed = parseAdContent( code );
				if ( false !== parsed ) {
					undoReadOnly();
					setDetailsFromAdCode( parsed );
					makeReadOnly();
					$( '#remote-ad-code-error' ).css( 'display', 'none' );
					$( '#remote-ad-unsupported-ad-type' ).css( 'display', 'none' );
					closeAdSelector();
				} else {
					$( '#remote-ad-code-error' ).css( 'display', 'block' );
				}
			}
		}

		/**
		 * Parse ad content.
		 *
		 * @return {!Object}
		 */
		function parseAdContent(content) {
			var rawContent = ('undefined' != typeof(content))? content.trim() : '';
			var theAd = {};
			var theContent = $( '<div />' ).html( rawContent );
			var adByGoogle = theContent.find( 'ins' );
			theAd.slotId = adByGoogle.attr( 'data-ad-slot' ) || '';
			if ('undefined' != typeof(adByGoogle.attr( 'data-ad-client' ))) {
				theAd.pubId = adByGoogle.attr( 'data-ad-client' ).substr( 3 );
			}

			if (undefined !== theAd.slotId && '' != theAd.pubId) {
				theAd.display = adByGoogle.css( 'display' );
				theAd.format = adByGoogle.attr( 'data-ad-format' );
				theAd.layout = adByGoogle.attr( 'data-ad-layout' ); // for InFeed and InArticle
				theAd.layout_key = adByGoogle.attr( 'data-ad-layout-key' ); // for InFeed
				theAd.style = adByGoogle.attr( 'style' ) || '';

				/* normal ad */
				if ('undefined' == typeof(theAd.format) && -1 != theAd.style.indexOf( 'width' )) {
					theAd.type = 'normal';
					theAd.width = adByGoogle.css( 'width' ).replace( 'px', '' );
					theAd.height = adByGoogle.css( 'height' ).replace( 'px', '' );
				}

				/* Responsive ad, auto resize */
				else if ('undefined' != typeof(theAd.format) && 'auto' == theAd.format) {
					theAd.type = 'responsive';
				}


				/* older link unit format; for new ads the format type is no longer needed; link units are created through the AdSense panel */
				else if ('undefined' != typeof(theAd.format) && 'link' == theAd.format) {

					if( -1 != theAd.style.indexOf( 'width' ) ){
					// is fixed size
					    theAd.width = adByGoogle.css( 'width' ).replace( 'px', '' );
					    theAd.height = adByGoogle.css( 'height' ).replace( 'px', '' );
					    theAd.type = 'link';
					} else {
					// is responsive
					    theAd.type = 'link-responsive';
					}
				}

				/* Responsive Matched Content */
				else if ('undefined' != typeof(theAd.format) && 'autorelaxed' == theAd.format) {
					theAd.type = 'matched-content';
				}

				/* InArticle & InFeed ads */
				else if ('undefined' != typeof(theAd.format) && 'fluid' == theAd.format) {

					// InFeed
					if('undefined' != typeof(theAd.layout) && 'in-article' == theAd.layout){
						theAd.type = 'in-article';
					} else {
					    // InArticle
						theAd.type = 'in-feed';
					}
				}
			}

			/**
			 *  Synchronous code
			 */
			if ( -1 != rawContent.indexOf( 'google_ad_slot' ) ) {
				var _client = rawContent.match( /google_ad_client ?= ?["']([^'"]+)/ );
				var _slot = rawContent.match( /google_ad_slot ?= ?["']([^'"]+)/ );
				var _format = rawContent.match( /google_ad_format ?= ?["']([^'"]+)/ );
				var _width = rawContent.match( /google_ad_width ?= ?([\d]+)/ );
				var _height = rawContent.match( /google_ad_height ?= ?([\d]+)/ );

				theAd = {};

				theAd.pubId = _client[1].substr( 3 );

				if ( null !== _slot ) {
					theAd.slotId = _slot[1];
				}
				if ( null !== _format ) {
					theAd.format = _format[1];
				}
				if ( null !== _width ) {
					theAd.width = parseInt( _width[1] );
				}
				if ( null !== _height ) {
					theAd.height = parseInt( _height[1] );
				}

				if ( 'undefined' == typeof theAd.format ) {
					theAd.type = 'normal';
				}

			}

			if ( '' == theAd.slotId && gadsenseData.pubId && '' != gadsenseData.pubId ) {
				theAd.type = $( '#unit-type' ).val();
			}

			/* Page-Level ad */
			if ( rawContent.indexOf( 'enable_page_level_ads' ) !== -1 ) {
				theAd = { 'parse_message': 'pageLevelAd' };
			}

			else if ( ! theAd.type ) {
				/* Unknown ad */
				theAd = { 'parse_message': 'unknownAd' };
			}

			$( document ).trigger( 'gadsenseParseAdContent', [ theAd, adByGoogle ] );
			return theAd;
		}

		/**
		 * Handle result of parsing content.
		 *
		 * @param {!Object}
		 */
		function handleParseResult( parseResult ) {
			$( '#pastecode-msg' ).empty();
			switch ( parseResult.parse_message ) {
				case 'pageLevelAd' :
					advads_show_adsense_auto_ads_warning();
				break;
				case 'unknownAd' :
					// Not recognized ad code
					if ( parseCodeBtnClicked && 'post-new.php' == gadsenseData.pagenow ) {
						// do not show if just afer switching to AdSense ad type on ad creation
						$( '#pastecode-msg' ).append( $( '<p />' ).css( 'color', 'red' ).html( gadsenseData.msg.unknownAd ) );
					}
				break;
				default:
					setDetailsFromAdCode( parseResult );
					if ( 'undefined' != typeof AdsenseMAPI && parseResult.pubId == AdsenseMAPI.pubId ) {
						var content = $( '#advanced-ads-ad-parameters input[name="advanced_ad[content]"]' ).val();
						MapiSaveAdCode( content, parseResult.slotId );
						makeReadOnly();
					}
					$( '.advads-adsense-code' ).hide();
					$( '.advads-adsense-show-code' ).show();
			}
		}

		/**
		 * Set ad parameters fields from the result of parsing ad code
		 */
		function setDetailsFromAdCode(theAd) {
			undoReadOnly();
			$( '#unit-code' ).val( theAd.slotId );
			$( '#advads-adsense-pub-id' ).val( theAd.pubId );
			if ('normal' == theAd.type) {
				$( '#unit-type' ).val( 'normal' );
				$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( theAd.width );
				$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( theAd.height );
			}
			if ('responsive' == theAd.type) {
				$( '#unit-type' ).val( 'responsive' );
				$( '#ad-resize-type' ).val( 'auto' );
				$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( '' );
				$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( '' );
			}
			if ('link' == theAd.type) {
				$( '#unit-type' ).val( 'link' );
				$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( theAd.width );
				$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( theAd.height );
			}
			if ('link-responsive' == theAd.type) {
				$( '#unit-type' ).val( 'link-responsive' );
				$( '#ad-resize-type' ).val( 'auto' );
				$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( '' );
				$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( '' );
			}
			if ('matched-content' == theAd.type) {
				$( '#unit-type' ).val( 'matched-content' );
				$( '#ad-resize-type' ).val( 'auto' );
				$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( '' );
				$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( '' );
			}
			if ('in-article' == theAd.type) {
				$( '#unit-type' ).val( 'in-article' );
				$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( '' );
				$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( '' );
			}
			if ('in-feed' == theAd.type) {
				$( '#unit-type' ).val( 'in-feed' );
				$( '#ad-layout' ).val( theAd.layout );
				$( '#ad-layout-key' ).val( theAd.layout_key );
				$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( '' );
				$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( '' );
			}
			var storedPubId = gadsenseData.pubId;
			if ( '' !== storedPubId && theAd.pubId != storedPubId && '' != theAd.slotId ) {
				$( '#adsense-ad-param-error' ).text( gadsenseData.msg.pubIdMismatch );
			} else {
				$( '#adsense-ad-param-error' ).empty();
			}
			$( document ).trigger( 'setDetailsFromAdCode', [ theAd ] );
			$( '#unit-type' ).trigger( 'change' );
		}

		/**
		 * Format the post content field
		 *
		 */
		window.gadsenseFormatAdContent = function () {
			var slotId = $( '#ad-parameters-box #unit-code' ).val();
			var unitType = $( '#ad-parameters-box #unit-type' ).val();
			var adContent = {
				slotId: slotId,
				unitType: unitType,
			};
			if ('responsive' == unitType) {
				var resize = $( '#ad-parameters-box #ad-resize-type' ).val();
				if (0 == resize) { resize = 'auto'; }
				adContent.resize = resize;
			}
			if ('in-feed' == unitType) {
				adContent.layout = $( '#ad-parameters-box #ad-layout' ).val();
				adContent.layout_key = $( '#ad-parameters-box #ad-layout-key' ).val();
			}
			if ('undefined' != typeof(adContent.resize) && 'auto' != adContent.resize) {
				$( document ).trigger( 'gadsenseFormatAdResponsive', [adContent] );
			}
			$( document ).trigger( 'gadsenseFormatAdContent', [adContent] );

			if ('undefined' != typeof(window.gadsenseAdContent)) {
				adContent = window.gadsenseAdContent;
				delete( window.gadsenseAdContent );
			}
			$( '#advads-ad-content-adsense' ).val( JSON.stringify( adContent, false, 2 ) );

		}

		$( document ).on( 'click', '#mapi-open-selector a', function(){
			$( '.advads-adsense-show-code' ).css( 'display', 'inline' );
			$( '#mapi-open-selector' ).css( 'display', 'none' );
			$( '.advads-adsense-code' ).css( 'display', 'none' );
			$( '#remote-ad-unsupported-ad-type' ).css( 'display', 'none' );
			var pubId = gadsenseData.pubId || false;
			var slotId = $( '#unit-code' ).val().trim();
			var tbody = $( '#mapi-table-wrap tbody' );
			tbody.find( 'tr' ).removeClass( 'selected' );
			if ( pubId && slotId ) {
				if ( $( '#mapi-table-wrap tr i[data-slotid="ca-' + pubId + ':' + slotId + '"]' ).length ) {
					tbody.find( 'tr i[data-slotid="ca-' + pubId + ':' + slotId + '"]' ).parents( 'tr' ).addClass( 'selected' );
				}
			}
			$( '#mapi-wrap' ).css( 'display', 'block' );
			if ( $( '#mapi-no-ad-units-found' ).length ) {
				$( '#mapi-no-ad-units-found' ).trigger( 'click' );
				return;
			}
			resizeAdListHeader();
		});

		$( document ).on( 'click', '#mapi-close-selector', function(){
			$( '#mapi-open-selector,.advads-adsense-show-code' ).css( 'display', 'inline' );
			$( '#mapi-wrap' ).css( 'display', 'none' );
			$( '#mapi-adclient-select' ).val( 'none' ).trigger( 'change' );
		} );

	function updateAdList() {

			$( '#mapi-loading-overlay' ).css( 'display', 'block' );

			$.ajax({
				type: 'post',
				url: ajaxurl,
				data: {
					nonce: AdsenseMAPI.nonce,
					action: 'advads_gadsense_mapi_get_adUnits',
					account: AdsenseMAPI.pubId,
				},
				success: function(response,status,XHR){
					$( '#mapi-loading-overlay' ).css( 'display', 'none' );
					if ( response.html ) {
						$( '#mapi-wrap' ).replaceWith( $( response.html ) );
						$( '#mapi-open-selector a' ).trigger( 'click' );
					}
				},
				error: function(request,status,err){
					$( '#mapi-loading-overlay' ).css( 'display', 'none' );
				},
			});

	}

	$( document ).on( 'click', '.mapiaction', function( ev ) {
		var action = $( this ).attr( 'data-mapiaction' );
		switch ( action ) {
			case 'updateList':
				updateAdList();
				break;
			case 'getCode':
				if ( $( this ).hasClass( 'disabled' ) ) {
					break;
				}
				var slotID = $( this ).attr( 'data-slotid' );
				getAdCode( slotID );
				break;
			case 'updateCode':
				var slotID = $( this ).attr( 'data-slotid' );
				getRemoteCode( slotID );
				break;
			default:
		}
	} );

		function advads_update_adsense_type(){
		    var type = $( '#unit-type' ).val();
			$( '.advads-adsense-layout' ).hide();
			$( '.advads-adsense-layout' ).next('div').hide();
			$( '.advads-adsense-layout-key' ).hide();
			$( '.advads-adsense-layout-key' ).next('div').hide();
			$( '.advads-ad-notice-in-feed-add-on' ).hide();
			$( '.clearfix-before' ).toggle ( type === 'responsive' );
			if ( 'responsive' == type || 'link-responsive' == type || 'matched-content' == type ) {
				$( '#advanced-ads-ad-parameters-size' ).css( 'display', 'none' );
				$( '#advanced-ads-ad-parameters-size' ).prev('.label').css( 'display', 'none' );
				$( '#advanced-ads-ad-parameters-size' ).next('.hr').css( 'display', 'none' );
			} else if ( 'in-feed' == type ) {
				$( '.advads-adsense-layout' ).css( 'display', 'block' );
				$( '.advads-adsense-layout' ).next('div').css( 'display', 'block' );
				$( '.advads-adsense-layout-key' ).css( 'display', 'block' );
				$( '.advads-adsense-layout-key' ).next('div').css( 'display', 'block' );
				$( '#advanced-ads-ad-parameters-size' ).css( 'display', 'none' );
				$( '#advanced-ads-ad-parameters-size' ).prev('.label').css( 'display', 'none' );
				$( '#advanced-ads-ad-parameters-size' ).next('.hr').css( 'display', 'none' );
				// show add-on notice
				$( '.advads-ad-notice-in-feed-add-on' ).show();
			} else if ( 'in-article' == type ) {
				$( '#advanced-ads-ad-parameters-size' ).css( 'display', 'none' );
				$( '#advanced-ads-ad-parameters-size' ).prev('.label').css( 'display', 'none' );
				$( '#advanced-ads-ad-parameters-size' ).next('.hr').css( 'display', 'none' );
			} else if ( 'normal' == type || 'link' == type ) {
				$( '#advanced-ads-ad-parameters-size' ).css( 'display', 'block' );
				$( '#advanced-ads-ad-parameters-size' ).prev('.label').css( 'display', 'block' );
				$( '#advanced-ads-ad-parameters-size' ).next('.hr').css( 'display', 'block' );
			}
			$( document ).trigger( 'gadsenseUnitChanged' );
			window.gadsenseFormatAdContent();

			show_float_warnings( type );
		}

		advads_update_adsense_type();

		if ( 'undefined' != typeof AdsenseMAPI ) {
			MapiMayBeSaveAdCode();
		}

	}); // DOM ready

})(jQuery);
