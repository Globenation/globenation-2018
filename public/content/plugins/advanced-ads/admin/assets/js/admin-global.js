/*
 * global js functions for Advanced Ads
 */
jQuery( document ).ready(function () {

	/**
	 * ADMIN NOTICES
	 */
	// close button
	// .advads-notice-dismiss class can be used to add a custom close button (e.g., link)
	jQuery(document).on('click', '.advads-admin-notice button.notice-dismiss, .advads-admin-notice .advads-notice-dismiss', function(){
	    var messagebox = jQuery(this).parents('.advads-admin-notice');
	    if( messagebox.attr('data-notice') === undefined) return;

	    var query = {
		action: 'advads-close-notice',
		notice: messagebox.attr('data-notice'),
		nonce: advadsglobal.ajax_nonce
	    };
	    // send query
	    jQuery.post(ajaxurl, query, function (r) {
		messagebox.fadeOut();
	    });
	});
	// hide notice for 7 days
	jQuery(document).on('click', '.advads-admin-notice .advads-notice-hide', function(){
	    var messagebox = jQuery(this).parents('.advads-admin-notice');
	    if( messagebox.attr('data-notice') === undefined) return;

	    var query = {
		action: 'advads-hide-notice',
		notice: messagebox.attr('data-notice'),
		nonce: advadsglobal.ajax_nonce
	    };
	    // send query
	    jQuery.post(ajaxurl, query, function (r) {
		messagebox.fadeOut();
	    });
	});
	// autoresponder button
	jQuery('.advads-notices-button-subscribe').click(function(){
	    if(this.dataset.notice === undefined) return;
	    var messagebox = jQuery(this).parents('.advads-admin-notice');
	    messagebox.find('p').append( '<span class="spinner advads-spinner"></span>' );

	    var query = {
		action: 'advads-subscribe-notice',
		notice: this.dataset.notice,
		nonce: advadsglobal.ajax_nonce
	    };
	    // send and close message
	    jQuery.post(ajaxurl, query, function (r) {
		if(r === '1'){
		    messagebox.fadeOut();
		} else {
		    messagebox.find('p').html(r);
		    messagebox.removeClass('updated').addClass('error');
		}
	    });

	});
	
	/**
	 * DEACTIVATION FEEDBACK FORM
	 */
	// show overlay when clicked on "deactivate"
	advads_deactivate_link = jQuery('.wp-admin.plugins-php tr[data-slug="advanced-ads"] .row-actions .deactivate a');
	advads_deactivate_link_url = advads_deactivate_link.attr( 'href' );
	advads_deactivate_link.click(function ( e ) {
		e.preventDefault();
		// only show feedback form once per 30 days
		var c_value = advads_admin_get_cookie( "advads_hide_deactivate_feedback" );
		if (c_value === undefined){
		    jQuery( '#advanced-ads-feedback-overlay' ).show();
		} else {
		    // click on the link
		    window.location.href = advads_deactivate_link_url;
		}
	});
	// show text fields
	jQuery('#advanced-ads-feedback-content input[type="radio"]').click(function () {
		// show text field if there is one
		jQuery(this).parents('li').next('li').children('input[type="text"], textarea').show();
	});
	// handle technical issue feedback in particular
	jQuery('#advanced-ads-feedback-content .advanced_ads_disable_help_text').focus(function () {
		// show text field if there is one
		jQuery(this).parents('li').siblings('.advanced_ads_disable_reply').show();
	});
	// send form or close it
	jQuery('#advanced-ads-feedback-content .button').click(function ( e ) {
		e.preventDefault();
		var self = jQuery( this );
		// set cookie for 30 days
		var exdate = new Date();
		exdate.setSeconds( exdate.getSeconds() + 2592000 );
		document.cookie = "advads_hide_deactivate_feedback=1; expires=" + exdate.toUTCString() + "; path=/";
		// save if plugin should be disabled
		var disable_plugin = self.hasClass('advanced-ads-feedback-not-deactivate') ? false : true;
			
		// hide the content of the feedback form
		jQuery( '#advanced-ads-feedback-content form' ).hide();
		if ( self.hasClass('advanced-ads-feedback-submit') ) {
			// show feedback message
			jQuery( '#advanced-ads-feedback-after-submit-waiting' ).show();
			if( disable_plugin ){
				jQuery( '#advanced-ads-feedback-after-submit-disabling-plugin' ).show();
			}
			jQuery.ajax({
			    type: 'POST',
			    url: ajaxurl,
			    dataType: 'json',
			    data: {
				action: 'advads_send_feedback',
				feedback: self.hasClass('advanced-ads-feedback-not-deactivate') ? true : false,
				formdata: jQuery( '#advanced-ads-feedback-content form' ).serialize()
			    },
			    complete: function (MLHttpRequest, textStatus, errorThrown) {
				    // deactivate the plugin and close the popup with a timeout
				    setTimeout( function(){
					    jQuery( '#advanced-ads-feedback-overlay' ).remove();
				    }, 2000 )
				    if( disable_plugin ){
					window.location.href = advads_deactivate_link_url;
				    }

			    }
			});
		} else { // currently not reachable
			jQuery( '#advanced-ads-feedback-overlay' ).remove();
			window.location.href = advads_deactivate_link_url;
		}
	});
	// close form and disable the plugin without doing anything
	jQuery('.advanced-ads-feedback-only-deactivate').click(function ( e ) {
		// hide the content of the feedback form
		jQuery( '#advanced-ads-feedback-content form' ).hide();
		// show feedback message
		jQuery( '#advanced-ads-feedback-after-submit-goodbye' ).show();
		jQuery( '#advanced-ads-feedback-after-submit-disabling-plugin' ).show();
		// wait 3 seconds
		setTimeout(function(){
			jQuery( '#advanced-ads-feedback-overlay' ).hide();
			window.location.href = advads_deactivate_link_url;
		}, 3000);
	});
	// close button for feedback form
	jQuery('#advanced-ads-feedback-overlay-close-button').click(function ( e ) {
		jQuery( '#advanced-ads-feedback-overlay' ).hide();
	});
});

function advads_admin_get_cookie (name) {
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
}

/**
 * load RSS widget on dashboard page using AJAX to not block rendering the rest of the page
 */
function advads_load_dashboard_rss_widget_content(){
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
		    action: 'advads_load_rss_widget_content',
		    nonce: advadsglobal.ajax_nonce
		},
		success: function (data, textStatus, XMLHttpRequest) {
			if (data) {
				jQuery( '#advads-dashboard-widget-placeholder' ).before( data );
			}
		},
		complete: function (MLHttpRequest, textStatus, errorThrown) {
			// remove the placeholder
			jQuery( '#advads-dashboard-widget-placeholder' ).remove();

		}
	});
}