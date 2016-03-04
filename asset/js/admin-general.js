/*!  - v2.0.0
 * 
 * Copyright (c) 2016; * Licensed GPLv2+ */
(jQuery(function() {

	// Make the left menu sticky.
	jQuery( '.sticky-tabs' ).sticky( { topSpacing: 45 } );

	/*
	***** General Admin Patterns *****
	 */

	(function() {
		function on_content_handle_click() {
			var el = jQuery( this ),
				box = el.closest( '.cp-content-box' );

			if ( box.hasClass('collapsed') ) {
				box.removeClass('collapsed');
			} else {
				box.addClass('collapsed');
			}
		}

		jQuery( '.cp-content-box h3.hndle' ).on( 'click', on_content_handle_click );
	}());

}));
