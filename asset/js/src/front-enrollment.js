/* global _coursepress */
/* global CoursePress */

(function( $ ) {
	CoursePress.Dialogs = {
		beforeSubmit: function() {
			var step = this.currentIndex;
			process_popup_enrollment( step );

			if ( step === ( CoursePress.Enrollment.dialog.views.length - 1 ) ) {
				$('.enrolment-container-div' ).addClass('hidden');
			}

			return false;
		},
		openAtAction: function( action ) {
			var steps = $( '[data-type="modal-step"]' );
			$.each( steps, function( i, step ) {
				var step_action = $( step ).attr('data-modal-action');
				if ( undefined !== step_action && action === step_action ) {
					CoursePress.Enrollment.dialog.openAt( i );
				}
			});
		},
		handle_signup_return: function( data ) {
			var signup_errors = data['signup_errors'];
			var steps = $( '[data-type="modal-step"]' );
			if ( 0 === signup_errors.length && data['user_data']['logged_in'] === true ) {
				// Check if the page is redirected from an invitation link
				if ( _coursepress.invitation_data ) {
					// Add user as instructor
					CoursePress.Enrollment.dialog.add_instructor( data );
				} else {
					$.each( steps, function( i, step ) {
						var action = $( step ).attr( 'data-modal-action' );
						if ( 'yes' === _coursepress.current_course_is_paid && 'paid_enrollment' === action ) {
							CoursePress.Enrollment.dialog.openAt( i );
						} else if ( 'enrolled' === action ) {
							if ( ! data['already_enrolled'] ) {
								// We're in! Now lets enroll
								CoursePress.Enrollment.dialog.attempt_enroll( data );
							} else {
								location.href = _coursepress.course_url;
							}
						}
					});
				}
			} else {
				if ( signup_errors.length > 0 ) {
					$( '.bbm-wrapper #error-messages' ).html('');

					// Display signup errors
					var err_msg = '<ul>';
					signup_errors.forEach( function( item ) {
						err_msg += '<li>' + item + '</li>';
					} );
					err_msg += '</ul>';

					$( '.bbm-wrapper #error-messages' ).html( err_msg );
					$( 'input[name=password]' ).val('');
					$( 'input[name=password_confirmation]' ).val('');
				} else {
					// Redirect to login
					$.each( steps, function( i, step ) {
						var action = step.attr('data-modal-action');
						if ( 'login' === action ) {
							CoursePress.Enrollment.dialog.openAt( i );
						}
					});
				}
			}
		},
		handle_login_return: function( data ) {
			var signup_errors = data['signup_errors'];
			var steps = $( '[data-type="modal-step"]' );
			if ( 0 === signup_errors.length && data['logged_in'] === true ) {
				// Check if the page is redirected from an invitation link
				if ( _coursepress.invitation_data ) {
					// Add user as instructor
					CoursePress.Enrollment.dialog.add_instructor( data );
				} else {
					$.each( steps, function( i, step ) {
						var action = $( step ).attr( 'data-modal-action' );
						if ( 'yes' === _coursepress.current_course_is_paid && 'paid_enrollment' === action ) {
							CoursePress.Enrollment.dialog.openAt( i );
						} else if ( 'enrolled' === action ) {
							if ( ! data['already_enrolled'] ) {
								// We're in! Now lets enroll
								CoursePress.Enrollment.dialog.attempt_enroll( data );
							} else {
								location.href = _coursepress.course_url;
							}
						}
					});
				}
			} else {
				if ( signup_errors.length > 0 ) {
					$( '.bbm-wrapper #error-messages' ).html('');
					// Display signup errors
					var err_msg = '<ul>';
					signup_errors.forEach( function( item ) {
						err_msg += '<li>' + item + '</li>';
					} );
					err_msg += '</ul>';
					$( '.bbm-wrapper #error-messages' ).html( err_msg );
					$( 'input[name=password]' ).val('');
				}
			}
		},
		handle_enroll_student_return: function( data ) {
			var steps = $( '[data-type="modal-step"]' );

			if ( true === data['success'] ) {
				$.each( steps, function( i, step ) {
					var action = $( step ).attr( 'data-modal-action' );
					if ( 'yes' === _coursepress.current_course_is_paid && 'paid_enrollment' === action ) {
						CoursePress.Enrollment.dialog.openAt( i );
					} else if ( 'enrolled' === action ) {
						CoursePress.Enrollment.dialog.openAt( i );
					}
				});
			}

			$('.enrolment-container-div' ).removeClass('hidden');
		},
		signup_validation: function() {
			var valid = true; // we're optimists
			$('.bbm-wrapper #error-messages' ).html('');

			var errors = [];
			// All fields required
			if (
				'' === $( 'input[name=first_name]' ).val().trim() ||
				'' === $( 'input[name=last_name]' ).val().trim() ||
				'' === $( 'input[name=username]' ).val().trim() ||
				'' === $( 'input[name=email]' ).val().trim() ||
				'' === $( 'input[name=password]' ).val().trim() ||
				'' === $( 'input[name=password_confirmation]' ).val().trim()
			) {
				valid = false;
				errors.push( _coursepress.signup_errors['all_fields'] );
			}

			var strength = CoursePress.utility.checkPasswordStrength(
				$('input[name=password]'),         // First password field
				$('input[name=password_confirmation]'), // Second password field
				$('#password-strength'),           // Strength meter
				false,
				[]        // Blacklisted words
			);

			// Can't have a weak password
			if ( strength <= 2 ) {
				valid = false;
				errors.push( _coursepress.signup_errors['weak_password'] );
			}

			// Passwords must match
			if ( strength === 5 ) {
				valid = false;
				errors.push( _coursepress.signup_errors['mismatch_password'] );
			}

			if ( errors.length > 0 ) {
				var err_msg = '<ul>';
				errors.forEach( function( item ) {
					err_msg += '<li>' + item + '</li>';
				} );
				err_msg += '</ul>';

				$( '.bbm-wrapper #error-messages' ).html( err_msg );
			}

			return valid;
		},
		login_validation: function() {
			var valid = true;
			$('.bbm-wrapper #error-messages' ).html('');

			// All fields required
			if (
				'' === $( 'input[name=log]' ).val().trim() ||
				'' === $( 'input[name=pwd]' ).val().trim()
			) {
				valid = false;
			}

			return valid;
		},
		signup_data: function( data ) {
			data.first_name = $( 'input[name=first_name]' ).val();
			data.last_name = $( 'input[name=last_name]' ).val();
			data.username = $( 'input[name=username]' ).val();
			data.email = $( 'input[name=email]' ).val();
			data.password = $( 'input[name=password]' ).val();
			data.nonce = $( '.bbm-modal-nonce.signup' ).attr('data-nonce');

			return data;
		},
		login_data: function( data ) {
			var course_id = $( '.enrollment-modal-container.bbm-modal__views' ).attr('data-course');
			data.username = $( 'input[name=log]' ).val();
			data.password = $( 'input[name=pwd]' ).val();
			data.course_id = course_id;
			data.nonce = $( '.bbm-modal-nonce.login' ).attr('data-nonce');
			return data;
		},
		attempt_enroll: function( enroll_data ) {
			var nonce = $( '.enrollment-modal-container.bbm-modal__views' ).attr('data-nonce');
			var course_id = $( '.enrollment-modal-container.bbm-modal__views' ).attr('data-course');

			if ( undefined === nonce || undefined === course_id ) {
				var temp = $(document.createElement('div'));
				temp.html( _.template( $( '#modal-template' ).html() )() );
				temp = $( temp ).find('.enrollment-modal-container')[0];
				nonce = $(temp).attr('data-nonce');
				course_id = $(temp).attr('data-course');
			}

			CoursePress.Post.prepare( 'course_enrollment', 'enrollment:' );
			CoursePress.Post.set( 'action', 'enroll_student' );

			var data = {
				nonce: nonce,
				student_id: enroll_data['user_data']['ID'],
				course_id: course_id,
				step: ''
			};
			CoursePress.Post.set( 'data', data );
			CoursePress.Post.save();

			// Manual hook here as this is not a step in the modal templates
			CoursePress.Post.off( 'coursepress:enrollment:enroll_student_success' );
			CoursePress.Post.on( 'coursepress:enrollment:enroll_student_success', function( data ) {
				// Update nonce
				$( '.enrollment-modal-container.bbm-modal__views' ).attr('data-nonce', data['nonce'] );

				if ( undefined !== data['callback'] ) {
					var fn = CoursePress.Enrollment.dialog[ data['callback'] ];
					if ( typeof fn === 'function' ) {
						fn( data );
						return;
					}
				}
			} );
		},
		new_nonce: function( nonce_name, callback ) {
			CoursePress.Post.prepare( 'course_enrollment', 'enrollment:' );
			CoursePress.Post.set( 'action', 'get_nonce' );

			var data = {
				action: 'get_nonce',
				nonce: nonce_name,
				step: ''
			};

			CoursePress.Post.set( 'data', data );
			CoursePress.Post.save();

			CoursePress.Post.off( 'coursepress:enrollment:get_nonce_success' );
			CoursePress.Post.on( 'coursepress:enrollment:get_nonce_success', callback );
		},
		add_instructor: function( return_data ) {

			CoursePress.Enrollment.dialog.new_nonce( 'coursepress_add_instructor', function( nonce ) {
				var course_id = _coursepress.invitation_data.course_id;

				CoursePress.Post.prepare( 'course_enrollment', 'enrollment:' );
				CoursePress.Post.set( 'action', 'add_instructor' );

				var data = {
					action: 'add_instructor',
					nonce: nonce.nonce,
					course_id: course_id,
					invite_code: _coursepress.invitation_data.code,
					instructor_id: return_data.user_data.ID,
					step: ''
				};

				CoursePress.Post.set( 'data', data );
				CoursePress.Post.save();

				CoursePress.Post.off( 'coursepress:enrollment:add_instructor_success' );
				CoursePress.Post.on( 'coursepress:enrollment:add_instructor_success', function() {
					CoursePress.Enrollment.dialog.openAtAction( 'instructor-verified' );
				} );

				CoursePress.Post.off( 'coursepress:enrollment:add_instructor_error' );
				CoursePress.Post.on( 'coursepress:enrollment:add_instructor_error', function() {
					CoursePress.Enrollment.dialog.openAtAction( 'verification-failed' );
				});

			});
		},
		init: function() {
			if ( ! CoursePress.Enrollment.dialog ) {
				CoursePress.Enrollment.dialog = new CoursePress.Modal();
				_.extend( CoursePress.Enrollment.dialog, CoursePress.Dialogs );
			}
		}
	};

	CoursePress.Enrollment = CoursePress.Enrollment || {};

	CoursePress.CustomLoginHook = function() {
		var newDiv = $( '<div class="cp-mask enrolment-container-div">' );

		newDiv.appendTo( 'body' );

		// Set modal
		CoursePress.Dialogs.init();

		newDiv.html( CoursePress.Enrollment.dialog.render().el );
		CoursePress.Enrollment.dialog.openAtAction( 'login' );

		return false;
	};

	// Hook the events
	$( document )
		.on( 'click', '.cp-custom-login', CoursePress.CustomLoginHook );

})(jQuery);