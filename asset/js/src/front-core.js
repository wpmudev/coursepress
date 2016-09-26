/* global _coursepress */

var CoursePress = {};
CoursePress.Events = _.extend( {}, Backbone.Events );

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