;(function(wp){
		
	/**
	 *  Shortcut variables
	 */
	var el = wp.element.createElement,
	registerBlockType = wp.blocks.registerBlockType;

	/**
	 * Register the single ad block type 
	 */
	registerBlockType( 'advads/gblock', {
		
		title: advadsGutenberg.i18n.advads,

		icon: 'chart-line',

		category: 'common',

		attributes: {
			itemID: {
				type: 'string',
			},
		},
		
		edit: function( props ) {
			
			var itemID = props.attributes.itemID;
			
			/**
			 * Update property on submit 
			 */
			function setItemID( event ) {
				var selected = event.target.querySelector( 'option:checked' );
				props.setAttributes( { itemID: selected.value } );
				event.preventDefault();
			}
			
			// the form children elements
			var children = [];
			
			// argument list (in array form) for the children creation
			var args = [];
			var ads = [];
			var groups = [];
			var placements = [];
			
			args.push( 'select' );
			args.push( { value: itemID, onChange: setItemID } );
			args.push( el( 'option', null, advadsGutenberg.i18n['--empty--'] ) );
			
			for ( var adID in advadsGutenberg.ads ) {
				if ( 'undefined' == typeof advadsGutenberg.ads[adID].id ) continue;
				ads.push( el( 'option', {value: 'ad_' + advadsGutenberg.ads[adID].id}, advadsGutenberg.ads[adID].title ) );
			}
			
			for ( var GID in advadsGutenberg.groups ) {
				if ( 'undefined' == typeof advadsGutenberg.groups[GID].id ) continue;
				groups.push( el( 'option', {value: 'group_' + advadsGutenberg.groups[GID]['id'] }, advadsGutenberg.groups[GID]['name'] ) );
				
			}
			
			if ( advadsGutenberg.placements ) {
				for ( var pid in advadsGutenberg.placements ) {
				if ( 'undefined' == typeof advadsGutenberg.placements[pid].id ) continue;
					placements.push( el( 'option', {value: 'place_' + advadsGutenberg.placements[pid]['id']}, advadsGutenberg.placements[pid]['name'] ) );
				}
			}
			
			if ( advadsGutenberg.placements ) {
				args.push( el( 'optgroup', {label: advadsGutenberg.i18n['placements']}, placements ) );
			}
			
			args.push( el( 'optgroup', {label: advadsGutenberg.i18n['adGroups']}, groups ) );
			
			args.push( el( 'optgroup', {label: advadsGutenberg.i18n['ads']}, ads ) );
			
			// add a <label /> first and style it.
			children.push( el( 'label', {style:{fontWeight:'bold',display:'block'}}, advadsGutenberg.i18n.advads ) );
			
			// then add the <select /> input with its own children
			children.push( el.apply( null, args ) );
			
			if ( itemID && advadsGutenberg.i18n['--empty--'] != itemID ) {
				
				var url = '#';
				if ( 0 === itemID.indexOf( 'place_' ) ) {
					url = advadsGutenberg.editLinks.placement;
				} else if ( 0 === itemID.indexOf( 'group_' ) ) {
					url = advadsGutenberg.editLinks.group;
				} else if ( 0 === itemID.indexOf( 'ad_' ) ) {
					var _adID = itemID.substr(3);
					url = advadsGutenberg.editLinks.ad.replace( '%ID%', _adID );
				}
				
				children.push(
					el(
						'a',
						{
							class: 'dashicons dashicons-external',
							style: {
								'vetical-align': 'middle',
								margin: 5,
							},
							href: url,
							target: '_blank',
						}
					)
				);
				
			}
			// return the complete form
			return el( 'form', { onSubmit: setItemID }, children );
			
		},

		save: function() {
			// server side rendering
			return null;
		},
		
	} );
	
})(window.wp);