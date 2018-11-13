/**
 * Advanced Ads.
 *
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      http://webgilde.com
 * @copyright 2013-2017 Thomas Maier, webgilde GmbH
 */
;(function($){
	var AUTH_WINDOW = null;
	$( document ).on( 'click', '.preventDefault', function( ev ) {
		ev.preventDefault();
	} )
	
	$( document ).on( 'keypress', '#adsense input[type="text"]', function( ev ) {
		if ( $( this ).hasClass( 'preventDefault' ) ) {
			ev.preventDefault();
			return;
		}
		if ( ev.which == 13 || ev.keyCode == 13 ) {
			$( '#adsense .advads-settings-tab-main-form #submit' ).trigger( 'click' );
		}
	} );
	
	$( document ).on( 'click', '#connect-adsense', function(){
		if ( $( this ).hasClass( 'disabled' ) ) return;
		$( '#gadsense-modal' ).css( 'display', 'block' );
		var oW = window.outerWidth || $( window ).width(),
		oH = window.outerHeight || $( window ).height(),
		w = Math.min( oW, oH ) * 0.8,
		h = Math.min( oW, oH ) * 0.8,
		l = (oW - w) / 2,
		t = (oH - h) / 2,
		args = 'resize=1,titlebar=1,width=' + w + ',height=' + h + ',left=' + l + ',top=' + t;
		
		AUTH_WINDOW = window.open( AdsenseMAPI.oAuth2, 'advadsOAuth2', args );
		
	} );
	
	$( document ).on( 'click', '#gadsense-modal .dashicons-dismiss', function(){
		$( '#mapi-confirm-code' ).val( '' );
		$( '#gadsense-modal' ).css( 'display', 'none' );
	} );
	
	$( document ).on( 'click', '#revoke-token', function(){
		
		$( '#gadsense-freeze-all' ).css( 'display', 'block' );
		var ID = $( '#adsense-id' ).val();
		$.ajax({
			url: ajaxurl,
			type: 'post',
			data: {
				action: 'advads-mapi-revoke-token',
				adsenseId: ID,
				nonce: AdsenseMAPI.nonce,
			},
			success:function(response, status, XHR){
				window.location.reload();
			},
			error:function(request, status, error){
				$( '#gadsense-freeze-all' ).css( 'display', 'none' );
			},
		});
		
	} );
	
	$( document ).on( 'click', '#mapi-confirm-code', function(){
		
		var code = $( '#mapi-code' ).val();
		if ( '' == code ) return;
		$( '#gadsense-overlay' ).css( 'display', 'block' );
		var data = {
			action: 'advads_gadsense_mapi_confirm_code',
			code: code,
			nonce: AdsenseMAPI.nonce,
		};
		if ( $( '#mapi-autoads' ).prop( 'checked' ) ) {
			data['autoads'] = true;
		}
		$.ajax({
			url: ajaxurl,
			type: 'post',
			data: data,
			success:function(response, status, XHR){
				if ( null !== AUTH_WINDOW ) {
					AUTH_WINDOW.close();
				}
				if ( response.status && true === response.status ) {
					window.location.reload();
				} else {
					console.log( response );
					$( '#gadsense-overlay' ).css( 'display', 'none' );
					$( '#mapi-code' ).val( '' );
					$( '#mapi-autoads' ).prop( 'checked', false );
					$( '#gadsense-modal-content-inner .dashicons-dismiss' ).trigger( 'click' );
				}
			},
			error:function(request, status, error){
				$( '#gadsense-loading-overlay' ).css( 'display', 'none' );
			},
		});
		
	} );
	
	$( document ).on( 'click', '#adsense-manual-config', function(){
		$( '#adsense .form-table tr' ).css( 'display', 'table-row' );
		$( '#adsense #auto-adsense-settings-div' ).css( 'display', 'none' );
		$( '#adsense #full-adsense-settings-div' ).css( 'display', 'block' );
		$( '#adsense-id' ).after( $( '#connect-adsense' ).addClass( 'disabled' ) );
	} );
	
	$( document ).on( 'change', '#adsense-id', function(){
		if ( '' != $( this ).val().trim() ) {
			$( '#adsense #submit' ).parent().css( 'display', 'block' );
		}
	} );
	
	$( function(){
        // move the API connection code submission pop-up outside of the settings form
        $( '#wpwrap' ).append( $( '#gadsense-modal' ) );
        
		if ( '' == $( '#adsense-id' ).val().trim() ) {
			$( '#adsense #submit' ).parent().css( 'display', 'none' );
		}
	} );
	
})(window.jQuery);