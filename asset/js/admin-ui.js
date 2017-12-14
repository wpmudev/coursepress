/*! CoursePress - v2.1.4
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2017; * Licensed GPLv2+ */
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

	// Toggle the state of a form element if require another element
	var enableInput = function() {
		var input = $(this),
			form = input.parents( 'form' ).first(),
			childInput = $( 'input[data-required-imput="'+ input.attr("name") +'"]', form ),
			found = 0
		;

		if( childInput.length == 0 )
			return;

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

		childInput.attr( 'disabled' , 0 == found );
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

	var reloadPageByCourse = function() {
		var select = $(this),
			course_id = select.val(),
			url = window.location.toString()
		;

		url += '&course_id=' + course_id;
		window.location = url;
	};

	HighLightShortCode = function() {
		var input = $(this);
		input.select();
	};

	// Hooked events
	$(document)
		.ready( function() {
			// Transform normal dropdown unto select2
			$( 'select.dropdown, .post-type-course select[name="action"]' ).select2();
		})
		.on( 'change', '.input-key', canSubmit )
		.on( 'change', '.input-requiredby', enableInput )
		.on( 'submit', '.has-disabled', formSubmission )
		.on( 'change', '.course-reload', reloadPageByCourse )
		.on( 'focus', '.cp-sc-box', HighLightShortCode );
}(jQuery);
