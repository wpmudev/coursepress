/*! CoursePress - v2.2.0-beta.1
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2018; * Licensed GPLv2+ */
(function( $ ){
    $( document ).ready( function() {
		$( '.cp_featured_widget_course_link .apply-button.apply-button-details' ).on( 'click', function( e ) {
			var target = e.currentTarget;

			if ( $( target ).attr( 'data-link' ) ) {
				window.location.href = $( target ).attr( 'data-link' );
			}
		} );
    
    } );
})( jQuery );