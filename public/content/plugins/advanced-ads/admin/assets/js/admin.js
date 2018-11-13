jQuery( document ).ready(function ($) {
	if ( ! $.fn.accordion || ! $.fn.tooltip || ! advads_use_ui_buttonset() ) {
		$( '.advads-jqueryui-error').show();
	}

	function advads_load_ad_type_parameter_metabox(ad_type) {
		jQuery( '#advanced-ad-type input' ).prop( 'disabled', true );
		$( '#advanced-ads-tinymce-wrapper' ).hide();
		$( '#advanced-ads-ad-parameters' ).html( '<span class="spinner advads-ad-parameters-spinner advads-spinner"></span>' );
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				'action': 'load_ad_parameters_metabox',
				'ad_type': ad_type,
				'ad_id': $( '#post_ID' ).val(),
				'nonce': advadsglobal.ajax_nonce
			},
			success: function (data, textStatus, XMLHttpRequest) {
				// toggle main content field
				if (data) {
					$( '#advanced-ads-ad-parameters' ).html( data ).trigger( 'paramloaded' );
					advads_maybe_textarea_to_tinymce( ad_type );
				}
			},
			error: function (MLHttpRequest, textStatus, errorThrown) {
				$( '#advanced-ads-ad-parameters' ).html( errorThrown );
			}
		}).always( function ( MLHttpRequest, textStatus, errorThrown ) {
			jQuery( '#advanced-ad-type input').prop( 'disabled', false );
		});
	};

	$( document ).on('change', '#advanced-ad-type input', function () {
		var ad_type = $( this ).val()
		advads_load_ad_type_parameter_metabox( ad_type );
	});
	
	// trigger for ad injection after ad creation
	$( '#advads-ad-injection-box .advads-ad-injection-button' ).on( 'click', function(){
		var placement_type = this.dataset.placementType, // create new placement
			placement_slug = this.dataset.placementSlug, // use existing placement
			options = {};

		if ( ! placement_type && ! placement_slug ) { return; }

		// create new placement
		if ( placement_type ) {
		    // for content injection
		    if ( 'post_content' === placement_type ) {
				var paragraph = prompt( advadstxt.after_paragraph_promt, 1);
				if ( paragraph !== null ) {
				    options.index = parseInt( paragraph, 10 );
				}
		    }
		}
	    $( '#advads-ad-injection-box .advads-loader' ).show();
		$( '#advads-ad-injection-box-placements' ).hide();
	    $( 'body').animate({ scrollTop: $( '#advads-ad-injection-box' ).offset().top -40 }, 1, 'linear' );

	    $.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
		    action: 'advads-ad-injection-content',
		    placement_type: placement_type,
		    placement_slug: placement_slug,
		    ad_id: $( '#post_ID' ).val(),
		    options: options,
		    nonce: advadsglobal.ajax_nonce
		},
		success: function (r, textStatus, XMLHttpRequest) {
			if ( ! r ) {
				$( '#advads-ad-injection-box' ).html( 'an error occured'  );
				return;
			}

		    $( '#advads-ad-injection-box *' ).hide();
		    // append anchor to placement message
		    $( '#advads-ad-injection-message-placement-created .advads-placement-link' ).attr( 'href', $( '#advads-ad-injection-message-placement-created a' ).attr( 'href' ) + r );
		    $( '#advads-ad-injection-message-placement-created, #advads-ad-injection-message-placement-created *' ).show();
		},
		error: function (MLHttpRequest, textStatus, errorThrown) {
			$( '#advads-ad-injection-box' ).html( errorThrown );
		}
	    }).always( function ( MLHttpRequest, textStatus, errorThrown ) {
		    // jQuery( '#advanced-ad-type input').prop( 'disabled', false );
	    });
	});
	
	// activate general buttons
	if ( advads_use_ui_buttonset() ) {
		$( '.advads-buttonset' ).buttonset();
	}
	// activate accordions
	if ( $.fn.accordion ) {
		$( ".advads-accordion" ).accordion({
		    active: false,
		    collapsible: true,
		});
	}

	$( document ).on('click', '.advads-conditions-terms-buttons .button', function (e) {
		$( this ).remove();
	});
	// display input field to search for terms
	$( document ).on('click', '.advads-conditions-terms-show-search', function (e) {
		e.preventDefault();
		// display input field
		$( this ).siblings( '.advads-conditions-terms-search' ).show().focus();
		// register autocomplete
		advads_register_terms_autocomplete( $( this ).siblings( '.advads-conditions-terms-search' ) );
		$( this ).next( 'br' ).show();
		$( this ).hide();
	});
	// function for autocomplete
	function advads_register_terms_autocomplete( self ){
	    self.autocomplete({
		    source: function(request, callback){
			    // var searchField  = request.term;
			    advads_term_search( self, callback );
		    },
		    minLength: 1,
		    select: function( event, ui ) {
			    // append new line with input fields
			    $( '<label class="button ui-state-active">' + ui.item.label + '<input type="hidden" name="' + self.data('inputName') + '" value="' + ui.item.value + '"></label>' ).appendTo( self.siblings( '.advads-conditions-terms-buttons' ) );

			    // show / hide other elements
			    // $( '.advads-display-conditions-individual-post' ).hide();
			    // $( '.advads-conditions-postids-list .show-search a' ).show();
		    },
		    close: function( event, ui ) {
				    self.val( '' );
		    }
	    });
	}
	// display input field to search for post, page, etc.
	$( document ).on( 'click', '.advads-conditions-postids-show-search', function (e) {
		e.preventDefault();
		// display input field
		$( this).next().find( '.advads-display-conditions-individual-post' ).show();
		//$( '.advads-conditions-postids-search-line .description' ).hide();
		$( this ).hide();
	});
	// register autocomplete to display condition individual posts
	$( document ).on( "focus", ".advads-display-conditions-individual-post", function(e) {
		var self = this;
	        if ( !$(this).data("autocomplete") ) { // If the autocomplete wasn't called yet:
		    $( this ).autocomplete({
			    source: function(request, callback){
				    var searchParam  = request.term;
				    advads_post_search( searchParam, callback );
			    },
			    minLength: 1,
			    select: function( event, ui ) {
				    // append new line with input fields
				    var newline = $( '<label class="button ui-state-active">' + ui.item.label + '</label>' );
				    $( '<input type="hidden" name="' + self.dataset.fieldName + '[value][]" value="' + ui.item.value + '"/>' ).appendTo( newline );
				    newline.insertBefore( $( self ).parent( '.advads-conditions-postids-search-line' ) );
			    },
			    close: function( event, ui ) {
				    $( self ).val( '' );
			    },
		    })
		    .autocomplete().data("ui-autocomplete")._renderItem = function( ul, item ) {
			ul.addClass( "advads-conditions-postids-autocomplete-suggestions" );
			return $( "<li></li>" )
			  .append( "<span class='left'>" + item.label + "</span>&nbsp;<span class='right'>" + item.info + "</span>" )
			  .appendTo( ul );
		    };
		};
	});
	
	// remove individual posts from the display conditions post list
	$( document ).on('click', '.advads-conditions-postid-buttons .button', function(e){
		$( this ).remove();
	});
	// display/hide error message if no option was selected
	// is also called on every click
	function advads_display_condition_option_not_selected(){
	    $( '.advads-conditions-not-selected' ).each(function(){
		if( $( this ).siblings('input:checked').length ){
		    $( this ).hide();
		} else {
		    $( this ).show();
		}
	    });
	}
	advads_display_condition_option_not_selected();
	// update error messages when an item is clicked
	$( document ).on( 'click', '.advads-conditions-terms-buttons input[type="checkbox"], .advads-conditions-single input[type="checkbox"]', function(){
	    // needs a slight delay until the buttons are updated
	    window.setTimeout( advads_display_condition_option_not_selected, 200 );
	});
	// activate and toggle conditions connector option
	$('.advads-conditions-connector input').advads_button();
	// dynamically change label
	$(document).on('click', '.advads-conditions-connector input', function(){
	    if( $( this ).is(':checked' ) ){
		$( this ).advads_button( "option", "label", advadstxt.condition_or );
		$( this ).parents( '.advads-conditions-connector' ).addClass('advads-conditions-connector-or').removeClass('advads-conditions-connector-and');
	    } else {
		$( this ).advads_button( "option", "label", advadstxt.condition_and );
		$( this ).parents( '.advads-conditions-connector' ).addClass('advads-conditions-connector-and').removeClass('advads-conditions-connector-or');
	    }
	});
	// remove a line with a display or visitor condition
	$(document).on('click', '.advads-conditions-remove', function(){
	    $(this).parents('.advads-conditions-table tr').prev('tr').remove();
	    $(this).parents('.advads-conditions-table tr').remove();
	});
	
	// display new ad group form
	$( '#advads-new-ad-group-link' ).click(function(e){
		e.preventDefault();
		$( '#advads-new-group-form' ).show().find('input[type="text"]').focus();
	});
	
	// display ad groups form
	$( '#advads-ad-group-list a.edit, #advads-ad-group-list a.row-title' ).click(function(e){
		e.preventDefault();
		var advadsgroupformrow = $( this ).parents( '.advads-group-row' ).next( '.advads-ad-group-form' );
		if(advadsgroupformrow.is( ':visible' )){
			advadsgroupformrow.hide();
			// clear last edited id
			$('#advads-last-edited-group').val('');
		} else {
			advadsgroupformrow.show();
			var group_id = $( this ).parents( '.advads-group-row' ).find('.advads-group-id').val();
			$('#advads-last-edited-group').val( group_id );
			
		}
	});
	// display ad groups usage
	$( '#advads-ad-group-list a.usage' ).click(function(e){
		e.preventDefault();
		var usagediv = $( this ).parents( '.advads-group-row' ).find( '.advads-usage' );
		if(usagediv.is( ':visible' )){
			usagediv.hide();
		} else {
			usagediv.show();
		}
	});
	// display placement settings form
	$( '.advads-placements-table a.advads-placement-options-link' ).click(function(e){
		e.preventDefault();
		var advadsplacementformrow = $( this ).next( '.advads-placements-advanced-options' );
		if( advadsplacementformrow.is( ':visible' ) ){
			advadsplacementformrow.hide();
			// clear last edited id
			$('#advads-last-edited-placement').val('');
		} else {
			advadsplacementformrow.show();
			var placement_id = $( this ).parents( '.advads-placements-table-options' ).find('.advads-placement-slug').val();
			$('#advads-last-edited-placement').val( placement_id );
			
		}
	});
	// display manual placement usage
	$( '.advads-placements-table .usage-link' ).click(function(e){
		e.preventDefault();
		var usagediv = $( this ).parents( 'tr' ).find( '.advads-usage' );
		if(usagediv.is( ':visible' )){
			usagediv.hide();
		} else {
			usagediv.show();
		}
	});
	/** 
	 * automatically open all options and show usage link when this is the placement linked in the URL
	 * also highlight the box with an effect for a short time
	 */
	if( jQuery( window.location.hash ).length ){
		jQuery( window.location.hash ).find( '.advads-toggle-link + div, .advads-usage' ).show();
		
	}

	// group page: add ad to group
	$( '.advads-group-add-ad button' ).click( function() {
		var $settings_row = $( this ).closest( '.advads-ad-group-form' ),
			$ad = $settings_row.find( '.advads-group-add-ad-list-ads option:selected' )
			$weight_selector = $settings_row.find( '.advads-group-add-ad-list-weights' ).last(),
			$ad_table = $settings_row.find( '.advads-group-ads tbody' );
		// add new row if does not already exist
		if ( $ad.length && $weight_selector.length && ! $ad_table.find( '[name="' +  $ad.val() + '"]' ).length ) {
			$ad_table.append(
				$( '<tr></tr>' ).append(
					$( '<td></td>' ).html( $ad.text() ),
					$( '<td></td>' ).append( $weight_selector.clone().val( $weight_selector.val() ).prop( 'name', $ad.val() ) ),
					'<td><button type="button" class="advads-remove-ad-from-group button">x</button></td>'
				)
			);
		}
	});
	// group page: remove ad from group
	$( '#advads-ad-group-list' ).on( 'click', '.advads-remove-ad-from-group', function() {
		var $ad_row = $( this ).closest( 'tr' );

		if ( $ad_row.data( 'ad-id' ) ) {
			// save the ad id, it is needed when this ad is not included in any other group
			$( '#advads-ad-group-list form' ).append(
				'<input type="hidden" name="advads-groups-removed-ads[]" value="' +  $ad_row.data( 'ad-id' ) + '">'
			);
		}
		$ad_row.remove();
	});
	// group page: handle switching of group types based on a class derrived from that type
	$('.advads-ad-group-type input').click(function(){
		advads_show_group_options( $( this ) );
	});
	function advads_show_group_options( el ){
		// first, hide all options except title and type
		// iterate through all elements
		el.each( function(){
			var _this = jQuery( this );
			_this.parents('.advads-ad-group-form').find('.advads-option:not(.static)').hide();
			var current_type = _this.val();

			// now, show only the ones corresponding with the group type
			_this.parents('.advads-ad-group-form').find( '.advads-group-type-' + current_type  ).show();
		});
	}
	// set default group options for earch group
	
	advads_show_group_options( $( '.advads-ad-group-type input:checked' ) );
	// group page: hide ads if more than 4 – than only show 3
	$('.advads-ad-group-list-ads').each( function(){
		if( 5 <= $(this).find('li').length ){
		    $(this).find('li:gt(2)').hide();
		};
	});
	// show more than 3 ads when clicked on a link
	$('.advads-group-ads-list-show-more').click( function(){
		jQuery( this ).hide().parents('.advads-ad-group-list-ads').find('li').show();
	});

	/**
	 * SETTINGS PAGE
	 */

	// activate licenses
	$('.advads-license-activate').click(function(){

	    var button = $(this);
	    
	    if( ! this.dataset.addon ) { return }
	    
	    // hide button to prevent issues with activation when people click twice
	    button.hide();

	    var query = {
		action: 'advads-activate-license',
		addon: this.dataset.addon,
		pluginname: this.dataset.pluginname,
		optionslug: this.dataset.optionslug,
		license: $(this).parents('td').find('.advads-license-key').val(),
		security: $('#advads-licenses-ajax-referrer').val()
	    };

	    // show loader
	    $( '<span class="spinner advads-spinner"></span>' ).insertAfter( button );

	    // send and close message
	    $.post(ajaxurl, query, function (r) {
		// remove spinner
		$('span.spinner').remove();
		var parent = button.parents('td');

		if( r === '1' ){
		    parent.find('.advads-license-activate-error').remove();
		    parent.find('.advads-license-deactivate').show();
		    button.fadeOut();
		    parent.find('.advads-license-activate-active').fadeIn();
		    parent.find('input').prop('readonly', 'readonly');
		} else if( r === 'ex' ){
		    parent.find('.advads-license-activate-error').remove();
		    parent.find('.advads-license-expired-error').show();
		    button.show();
		} else {
		    parent.find('.advads-license-activate-error').show().text( r );
		    button.show();
		}
	    });
	});
	
	// deactivate licenses
	$('.advads-license-deactivate').click(function(){

	    var button = $(this);
	    
	    if( ! this.dataset.addon ) { return }
	    
	    // hide button to prevent issues with double clicking
	    button.hide();

	    var query = {
		action: 'advads-deactivate-license',
		addon: this.dataset.addon,
		pluginname: this.dataset.pluginname,
		optionslug: this.dataset.optionslug,
		security: $('#advads-licenses-ajax-referrer').val()
	    };

	    // show loader
	    $( '<span class="spinner advads-spinner"></span>' ).insertAfter( button );

	    // send and close message
	    $.post(ajaxurl, query, function (r) {
		// remove spinner
		$('span.spinner').remove();

		if( r === '1' ){
		    button.siblings('.advads-license-activate-error').hide();
		    button.siblings('.advads-license-activate-active').hide();
		    button.siblings('.advads-license-activate').show();
		    button.siblings('input').prop('readonly', false);
		    button.fadeOut();
		} else if( r === 'ex' ){
		    button.siblings('.advads-license-activate-error').hide();
		    button.siblings('.advads-license-activate-active').hide();
		    button.siblings('.advads-license-expired-error').show();
		    button.siblings('input').prop('readonly', false);
		    button.fadeOut();
		} else {
		    console.log( r );
		    button.siblings('.advads-license-activate-error').show().html( r );
		    button.siblings('.advads-license-activate-active').hide();
		    button.show();
		}
	    });
	});



	
	/**
	* PLACEMENTS
	*/
	 // show image tooltips
	if ( $.fn.tooltip ) {
		$( ".advads-placements-new-form .advads-placement-type" ).tooltip({
			items: "span",
			content: function() {
				return $( this ).parents('.advads-placement-type').find( '.advads-placement-description' ).html();
			}
		});
	}

	/**
         * Image ad uploader
         */
	$('body').on('click', '.advads_image_upload', function(e) {

		e.preventDefault();

		var button = $(this);

		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			// file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
			file_frame.open();
			return;
		}

		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media( {
			id: 'advads_type_image_wp_media',
			title: button.data( 'uploaderTitle' ),
			button: {
				text: button.data( 'uploaderButtonText' )
			},
			library: {
				type: 'image'
			},
			multiple: false // only allow one file to be selected
		} );

		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {

			var selection = file_frame.state().get('selection');
			selection.each( function( attachment, index ) {
				attachment = attachment.toJSON();
				if ( 0 === index ) {
					// place first attachment in field
					$( '#advads-image-id' ).val( attachment.id );
					$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( attachment.width );
					$( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( attachment.height );
					// update image preview
					var new_image = '<img width="'+ attachment.width +'" height="'+ attachment.height +
						'" title="'+ attachment.title +'" alt="'+ attachment.alt +'" src="'+ attachment.url +'"/>';
					$('#advads-image-preview').html( new_image );
					$('#advads-image-edit-link').attr( 'href', attachment.editLink );
					// process "reserve this space" checkbox
					$( '#advanced-ads-ad-parameters-size input[type=number]:first' ).change();
				}
			});
		});

		// Finally, open the modal
		file_frame.open();
	});

	// WP 3.5+ uploader
	var file_frame;
	window.formfield = '';

	// adblocker related code
	$( '#advanced-ads-use-adblocker' ).change( function() {
		advads_toggle_box( this, '#advads-adblocker-wrapper' );
	});

	// processing of the rebuild asset form and the FTP/SSH credentials form
	var $advads_adblocker_wrapper = $( '#advads-adblocker-wrapper' );
	$advads_adblocker_wrapper.find( 'input[type="submit"]' ).prop( 'disabled', false );
	$advads_adblocker_wrapper.on( 'submit', 'form', function( event ) {
		event.preventDefault();
		var $form = $( '#advanced-ads-rebuild-assets-form' );
		$form.prev( '.error' ).remove();
		$form.find( 'input[type="submit"]' ).prop( 'disabled', true ).after( '<span class="spinner advads-spinner"></span>' );

		var args = {
			data: {
				action: 'advads-adblock-rebuild-assets',
				nonce: advadsglobal.ajax_nonce,
			},
			done: function( data, textStatus, jqXHR ) {
				var $advads_adblocker_wrapper = $( '#advads-adblocker-wrapper' );
				$advads_adblocker_wrapper.html( data );
			},
			fail: function( jqXHR, textStatus, errorThrown ) {
				$form.before( '<div class="error"><p>' + textStatus  + ': ' + errorThrown + '</p></div>' );
				$form.find( 'input[type="submit"]' ).prop( 'disabled', false ).next( '.advads-spinner' ).remove();
			},
			on_modal_close: function() {
				var $form = $( '#advanced-ads-rebuild-assets-form' );
				$form.find( 'input[type="submit"]' ).prop( 'disabled', false ).next( '.advads-spinner' ).remove();
			}
		}

		$.each( $form.serializeArray(), function( i, o ) {
			args.data[ o.name ] = o.value;
		});

		advanced_ads_admin.filesystem.ajax( args );
	});

	// process "reserve this space" checkbox
	$( '#advanced-ads-ad-parameters' ).on( 'change', '#advanced-ads-ad-parameters-size input[type=number]', function() {
		if ( $( '#advanced-ads-ad-parameters-size input[type=number]' ).filter( function() {
			return parseInt( this.value, 10 ) > 0;
		}).length === 2 ) {
			$( '#advads-wrapper-add-sizes' ).prop( 'disabled', false );
		} else {
			$( '#advads-wrapper-add-sizes' ).prop( 'disabled', true ).prop( 'checked', false );
		}
	});
	// process "reserve this space" checkbox - ad type changed
	$( '#advanced-ads-ad-parameters' ).on( 'paramloaded', function() {
		$( '#advanced-ads-ad-parameters-size input[type=number]:first' ).change();
	})
	// process "reserve this space" checkbox - on load
	$( '#advanced-ads-ad-parameters-size input[type=number]:first' ).change();
	
	// move meta box markup to hndle headline
	$('.advads-hndlelinks').each(function(){
	    $(this).removeClass('hidden');
	    $(this).appendTo( $(this).parent('.inside').siblings('h2.hndle') );
	});
	// open tutorial link when clicked on it
	$('.advads-video-link').click(function( el ){
	    el.preventDefault();
	    var video_container = $(this).parents('h2').siblings('.inside').find('.advads-video-link-container');
	    video_container.html( video_container.data('videolink') );
	});
	// open inline tutorial link when clicked on it
	$('.advads-video-link-inline').click(function( el ){
	    el.preventDefault();
	    var video_container = $(this).parents('div').siblings('.advads-video-link-container');
	    video_container.html( video_container.data('videolink') );
	});
	// switch import type
	jQuery( '.advads_import_type' ).change( function() {
		if ( this.value === 'xml_content' ) {
			jQuery( '#advads_xml_file' ).hide();
			jQuery( '#advads_xml_content' ).show();
		} else {
			jQuery( '#advads_xml_file' ).show();
			jQuery( '#advads_xml_content' ).hide();
		}
	});

	// Find Adsense Auto Ads inside ad content.
	var ad_content = jQuery('textarea[name=advanced_ad\\[content\\]]').html();
	if ( ad_content && ad_content.indexOf( 'enable_page_level_ads' ) !== -1 ) {
		advads_show_adsense_auto_ads_warning();
	}
});


/**
 * store the action hash in settings form action
 * thanks for Yoast SEO for this idea
 */
function advads_set_tab_hashes() {
	// iterate through forms
	jQuery( '#advads-tabs' ).find( 'a' ).each(function () {
		var id = jQuery( this ).attr( 'id' ).replace( '-tab', '' );
		var optiontab = jQuery( '#' + id );

		var form = optiontab.children( '.advads-settings-tab-main-form' );
		if ( form.length ) {
			var currentUrl = form.attr( 'action' ).split( '#' )[ 0 ];
			form.attr( 'action', currentUrl + jQuery( this ).attr( 'href' ) );
		}
	});
}

/**
 * callback for term search autocomplete
 *
 * @param {type} search term
 * @param {type} callback
 * @returns {obj} json object with labels and values
 */
function advads_term_search(field, callback) {

	// return ['post', 'poster'];
	var query = {
		action: 'advads-terms-search',
		nonce: advadsglobal.ajax_nonce
	};
	
	query.search = field.val();
	query.tax = field.data('tagName');

	var querying = true;

	var results = {};
	jQuery.post(ajaxurl, query, function (r) {
		querying = false;
		var results = [];
		if(r){
			r.map(function(element, index){
				results[index] = {
					value: element.term_id,
					label: element.name
				};
			});
		}
		callback( results );
	}, 'json');
}

/**
 * callback for post search autocomplete
 *
 * @param {str} searchParam
 * @param {type} callback
 * @returns {obj} json object with labels and values
 */
function advads_post_search( searchParam, callback ) {

	// return ['post', 'poster'];
	var query = {
		action: 'advads-post-search',
		_ajax_linking_nonce: jQuery( '#_ajax_linking_nonce' ).val(),
		'search': searchParam,
		nonce: advadsglobal.ajax_nonce
	};

	var querying = true;

	var results = {};
	jQuery.post(ajaxurl, query, function (r) {
		querying = false;
		var results = [];
		if(r){
			r.map(function(element, index){
				results[index] = {
					label: element.title,
					value: element.ID,
					info: element.info
				};
			});
		}
		callback( results );
	}, 'json');
}

/**
 * toggle content elements (hide/show)
 *
 * @param selector jquery selector
 */
function advads_toggle(selector) {
	jQuery( selector ).slideToggle();
}

/**
 * toggle content elements with a checkbox (hide/show)
 *
 * @param selector jquery selector
 */
function advads_toggle_box(e, selector) {
	if (jQuery( e ).is( ':checked' )) {
		jQuery( selector ).slideDown();
	} else {
		jQuery( selector ).slideUp();
	}
}

/**
 * disable content of one box when selecting another
 *  only grey/disable it, don’t hide it
 *
 * @param selector jquery selector
 */
function advads_toggle_box_enable(e, selector) {
	if (jQuery( e ).is( ':checked' )) {
		jQuery( selector ).find( 'input' ).removeAttr( 'disabled', '' );
	} else {
		jQuery( selector ).find( 'input' ).attr( 'disabled', 'disabled' );
	}
}

/**
 * validate placement form on submit
 */
function advads_validate_placement_form(){
	// check if placement type was selected
	if( ! jQuery('.advads-placement-type input:checked').length){
		jQuery('.advads-placement-type-error').show();
		return false;
	} else {
		jQuery('.advads-placement-type-error').hide();
	}
	// check if placement name was entered
	if( jQuery('.advads-new-placement-name').val() == '' ){
		jQuery('.advads-placement-name-error').show();
		return false;
	} else {
		jQuery('.advads-placement-name-error').hide();
	}
	return true;
}

/**
 * replace textarea with TinyMCE editor for Rich Content ad type
 */
function advads_maybe_textarea_to_tinymce( ad_type ) {
	var textarea            = jQuery( '#advads-ad-content-plain' ),
		textarea_html       = textarea.val(),
		tinymce_id          = 'advanced-ads-tinymce',
		tinymce_id_ws       = jQuery( '#' + tinymce_id ),
		tinymce_wrapper_div = jQuery ( '#advanced-ads-tinymce-wrapper' );

	if ( ad_type !== 'content' ) {
		tinymce_id_ws.prop('name', tinymce_id );
		tinymce_wrapper_div.hide();
		return false;
	}

	if ( typeof tinyMCE === 'object' && tinyMCE.get( tinymce_id ) !== null ) {
		// visual mode
		if ( textarea_html ) {
			// see BeforeSetContent in the wp-includes\js\tinymce\plugins\wordpress\plugin.js
			var wp = window.wp,
			hasWpautop = ( wp && wp.editor && wp.editor.autop && tinyMCE.get( tinymce_id ).getParam( 'wpautop', true ) );
			if ( hasWpautop ) {
				textarea_html = wp.editor.autop( textarea_html );
			}
			tinyMCE.get( tinymce_id ).setContent( textarea_html );
		}
		textarea.remove();
		tinymce_id_ws.prop('name', textarea.prop( 'name' ) );
		tinymce_wrapper_div.show();
	} else if ( tinymce_id_ws.length ) {
		// text mode
		tinymce_id_ws.val( textarea_html );
		textarea.remove();
		tinymce_id_ws.prop('name', textarea.prop( 'name' ) );
		tinymce_wrapper_div.show();
	}
}

/**
 * Show a message depending on whether Adsense Auto ads are enabled.
 */
function advads_show_adsense_auto_ads_warning() {
	$msg = jQuery( '.advads-auto-ad-in-ad-content' ).show();
	$msg.on( 'click', 'button', function() {
		$msg.hide();
		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'advads-adsense-enable-pla',
				nonce: advadsglobal.ajax_nonce
			},
		} ).done(function( data ) {
			$msg.show().html( advadstxt.page_level_ads_enabled );
		} ).fail(function( jqXHR, textStatus ) {
			$msg.show();
		} );
	});
}

// Change JQueryUI names to fix name collision with other libraries, eg. Bootstrap
jQuery.fn.advads_button = jQuery.fn.button;

/**
 * check if jQueryUI button/buttonset can be used
 */
var advads_use_ui_buttonset = ( function() {
	var ret = null;
	return function () {
		if ( null === ret ) {
			var needle = 'var g="string"==typeof f,h=c.call(arguments,1)'; //string from jquery-ui source code
			ret = jQuery.fn.advads_button && jQuery.fn.buttonset && jQuery.fn.button.toString().indexOf( needle ) !== -1;
		}
		return ret;
	};
})();

window.advanced_ads_admin = window.advanced_ads_admin || {};
advanced_ads_admin.filesystem = {
	/**
	 * Holds the current job while the user writes data in the 'Connection Information' modal.
	 *
	 * @type {obj}
	 */
	_locked_job: false,

	/**
	 * Toggle the 'Connection Information' modal.
	 */
	_requestForCredentialsModalToggle: function() {
		this.$filesystemModal.toggle();
		jQuery( 'body' ).toggleClass( 'modal-open' );
	},

	_init: function() {
		this._init = function() {}
		var self = this;

		self.$filesystemModal = jQuery( '#advanced-ads-rfc-dialog' );
		/**
		 * Sends saved job.
		 */
		self.$filesystemModal.on( 'submit', 'form', function( event ) {
			event.preventDefault();

			self.ajax( self._locked_job, true );
			self._requestForCredentialsModalToggle()
		} );

		/**
		 * Closes the request credentials modal when clicking the 'Cancel' button.
		 */
		self.$filesystemModal.on( 'click', '[data-js-action="close"]', function() {
			if ( jQuery.isPlainObject( self._locked_job ) && self._locked_job.on_modal_close ) {
				self._locked_job.on_modal_close();
			}

			self._locked_job = false;
			self._requestForCredentialsModalToggle();
		} );
	},

	/**
	 * Sends AJAX request. Shows 'Connection Information' modal if needed.
	 *
	 * @param {object} args
	 * @param {bool} skip_modal
	 */
	ajax: function( args, skip_modal ) {
		this._init();

		if ( ! skip_modal && this.$filesystemModal.length > 0 ) {
			this._requestForCredentialsModalToggle();
			this.$filesystemModal.find( 'input:enabled:first' ).focus();

			// Do not send request.
			this._locked_job = args;
			return;
		}

		var options = {
			method: 'POST',
			url: window.ajaxurl,
			data: {
				username:        jQuery( '#username' ).val(),
				password:        jQuery( '#password' ).val(),
				hostname:        jQuery( '#hostname' ).val(),
				connection_type: jQuery( 'input[name="connection_type"]:checked' ).val(),
				public_key:      jQuery( '#public_key' ).val(),
				private_key:     jQuery( '#private_key' ).val(),
				_fs_nonce:       jQuery( '#_fs_nonce' ).val()

			}
		};

		options.data = jQuery.extend( options.data, args.data );
		var request = jQuery.ajax( options );

		if ( args.done ) {
			request.done( args.done );
		}

		if ( args.fail ) {
			request.fail( args.fail );
		}
	}
}

