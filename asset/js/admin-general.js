/*!  - v2.0.0
 * 
 * Copyright (c) 2016; * Licensed GPLv2+ */

(function( $ ) {
	// Make the left menu sticky.
	if ( $.fn.sticky ) {
		$( '.sticky-tabs' ).sticky( { topSpacing: 45 } );
	}

	/*
	***** General Admin Patterns *****
	 */

	function on_content_handle_click() {
		var el = $( this ),
			box = el.closest( '.cp-content-box' );

		if ( box.hasClass('collapsed') ) {
			box.removeClass('collapsed');
		} else {
			box.addClass('collapsed');
		}
	}

	$(document).on( 'click', '.cp-content-box h3.hndle', on_content_handle_click );

})(jQuery);
