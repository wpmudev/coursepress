/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'EnrollButton', function( $, doc ) {
        var redirect;

        redirect = function( ev ) {
			var target = ev.currentTarget;
			var url = $( target ).data( 'link' );

			if ( url ) {
				window.location.href = url;
			}
        };

        $(doc).on( 'click', '.apply-button.apply-button-details', redirect );
    });
})();
