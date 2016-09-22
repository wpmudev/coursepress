/*!  - v2.0.0
 * 
 * Copyright (c) 2016; * Licensed GPLv2+ */
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
(function( $ ) {
	CoursePress.LoadFocusModule = function() {
		var nav = $(this),
			data = nav.data(),
			container = $( '.coursepress-focus-view' ),
			url = [ _coursepress.home_url, 'coursepress_focus' ]
		;

		url.push( data.course, data.unit, data.type, data.id );
		url = url.join( '/' );

		container.load( data.url );

		return false;
	};

	CoursePress.ModuleSubmit = function() {
		var form = $(this),
			error_box = form.find( '.cp-error' ),
			focus_box = form.parents( '.coursepress-focus-view' ),
			iframe = false,
			timer = false
		;

		// Insert ajax marker
		form.append( '<input type="hidden" name="is_cp_ajax" value="1" />' );

		// Create iframe to trick the browser
		iframe = $( '<iframe name="cp_submitter" >' ).insertBefore( form );

		// Set the form to submit unto the iframe
		form.attr( 'target', 'cp_submitter' );

		// Apply tricks
		iframe.on( 'load', function() {
			var that = $(this).contents().find( 'body' );

			timer = setInterval(function() {
				var html = that.text();

				if ( '' != html ) {
					// Kill timer
					clearInterval( timer );

					var data = window.JSON.parse( html );

					if ( true === data.success ) {
						// Process success
						focus_box.html( data.data.html );
					} else {
						// Print error message
						error_box.empty().append( data.data.error_message );
					}
				}
			}, 100 );
		});
	};

	$( document )
		.on( 'submit', '.cp-form', CoursePress.ModuleSubmit )
		.on( 'click', '.focus-nav-prev', CoursePress.LoadFocusModule );

})(jQuery);