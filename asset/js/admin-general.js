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
			on_content_handle_click_calculate();
		}

		/**
		 * calculate (un)fold buttons
		 */
		function on_content_handle_click_calculate() {
			var all = jQuery('.cp-content-box').length;
			var collapsed = jQuery('.cp-content-box.collapsed').length;
			if ( 0 == collapsed ) {
				jQuery( 'input.button.hndle-items-unfold' ).addClass('disabled');
			} else {
				jQuery( 'input.button.hndle-items-unfold' ).removeClass('disabled');
			}
			if ( all == collapsed ) {
				jQuery( 'input.button.hndle-items-fold' ).addClass('disabled');
			} else {
				jQuery( 'input.button.hndle-items-fold' ).removeClass('disabled');
			}
		}

		jQuery( '.cp-content-box h3.hndle' ).on( 'click', on_content_handle_click );
		jQuery( 'input.button.hndle-items-unfold' ).on( 'click', function() {
			jQuery('.cp-content-box').removeClass('collapsed');
			on_content_handle_click_calculate();
		} );
		jQuery( 'input.button.hndle-items-fold' ).on( 'click', function() {
			jQuery('.cp-content-box').addClass('collapsed');
			on_content_handle_click_calculate();
		} );
	}());

}));
