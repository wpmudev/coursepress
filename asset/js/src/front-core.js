/* global _coursepress */

var CoursePress = {};
CoursePress.Events = _.extend( {}, Backbone.Events );

(function( $ ) {
CoursePress.SendRequest = Backbone.Model.extend( {
	url: _coursepress._ajax_url + '?action=coursepress_request',
	parse: function( response ) {
		var action = this.get( 'action' );

		// Trigger course update events
		if ( true === response.success ) {
			this.set( 'response_data', response.data );
			this.trigger( 'coursepress:' + action + '_success', response.data );
		} else {
			this.set( 'response_data', {} );
			this.trigger( 'coursepress:' + action + '_error', response.data );
		}
	}
} );

/** Reset browser URL **/
CoursePress.resetBrowserURL = function( url ) {
	if ( window.history.pushState ) {
		// Reset browser url
		window.history.pushState( {}, null, url );
	}
};

/** Error Box **/
CoursePress.showError = function( error_message, container ) {
	var error_box = $( '<div class="cp-error-box"></div>' ),
		error = $( '<p>' ),
		closed = $( '<a class="cp-closed">&times;</a>' ),
		removeError
	;

	removeError = function() {
		error_box.remove();
	};

	error.html( error_message ).appendTo( error_box );
	closed.prependTo( error_box ).on( 'click', removeError );

	container.prepend( error_box );
};

/** Focus to the element **/
CoursePress.Focus = function( selector ) {
	var el = $( selector ), top;

	if ( 0 < el.length ) {
		top = el.offset().top;
		$(window).scrollTop( top );
	}

	return false;
};

})(jQuery);