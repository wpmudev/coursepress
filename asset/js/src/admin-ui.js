+function( $ ){
	CoursePress = CoursePress || {};
	CoursePress.Events = CoursePress.Events || _.extend( {}, Backbone.Events );

	// Toggle the state of a form's submit button
	var canSubmit = function() {
		var form = $(this).parents( 'form' ).first(),
			inputs = $( '.input-key', form ),
			submit_button = $( '[type="submit"]', form ),
			found = 0
		;

		_.each( inputs, function( input ) {
			input = $( input );

			var input_type = input.attr( 'type' );

			if ( ( 'checkbox' == input_type || 'radio' == input_type ) ) {
				if ( input.is( ':checked' ) ) {
					found += 1;
				}
			} else {
				if ( '' != input.val().trim() ) {
					found += 1;
				}
			}
		});

		submit_button[ 0 == found ? 'addClass' : 'removeClass' ]('disabled');
	};

	// Prevent a form from submitting if submit button is disabled
	var formSubmission = function(e) {
		var form = $(this),
			submitButton = $( '[type="submit"]', form ),
			can_submit = ! submitButton.is( '.disabled' )

		if ( ! can_submit ) {
			e.stopImmediatePropagation();

			return false;
		}
	};

	// Add progress indicator
	var progressIndicator = function() {
		var progress = $( '<span class="cp-progress-indicator"><i class="fa fa-spinner fa-spin"></i></span>' ),
			check = '<i class="fa fa-check"></i>',
			error = '<i class="fa fa-remove"></i>'
		;

		return {
			icon: progress,
			success: function( message ) {
				message = ! message ? '' : message;
				progress.addClass( 'success' ).html( check + message );
				progress.fadeOut( 3500, progress.remove );

				CoursePress.Events.trigger( 'coursepress:progress:success' );
			},
			error: function( message ) {
				message = ! message ? '' : message;
				progress.addClass( 'error' ).html( error + message );
				progress.fadeOut( 3500, progress.remove );

				CoursePress.Events.trigger( 'coursepress:progress:error' );
			}
		};
	};
	CoursePress.ProgressIndicator = progressIndicator;

	// Hooked events
	$(document)
		.ready( function() {
			// Transform normal dropdown unto select2
			$( 'select.dropdown' ).select2();
		})
		.on( 'change', '.input-key', canSubmit )
		.on( 'submit', '.has-disabled', formSubmission );

}(jQuery);