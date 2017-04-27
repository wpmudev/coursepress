/* global _coursepress */
/* global CoursePress */

(function( $ ) {
	CoursePress.Models.CourseFront = Backbone.Model.extend( {
		url: _coursepress._ajax_url + '?action=course_front',
		parse: function( response ) {
			// Trigger course update events
			if ( true === response.success ) {
				this.set( 'response_data', response.data );
				this.trigger( 'coursepress:' + response.data.action + '_success', response.data );
			} else {
				this.set( 'response_data', {} );
				if ( response.data ) {
					this.trigger( 'coursepress:' + response.data.action + '_error', response.data );
				}
			}
		},
		defaults: {}
	} );

	// AJAX Posts
	CoursePress.Models.Post = CoursePress.Models.Post || Backbone.Model.extend( {
		url: _coursepress._ajax_url + '?action=',
		parse: function( response ) {
			var context = this.get( 'context' );

			// Trigger course update events
			if ( true === response.success ) {
				if ( undefined === response.data ) {
					response.data = {};
				}

				this.set( 'response_data', response.data );
				var method = 'coursepress:' + context + response.data.action + '_success';
				this.trigger( method, response.data );
			} else {
				if ( 0 !== response ) {
					this.set( 'response_data', {} );
					this.trigger( 'coursepress:' + context + response.data.action + '_error', response.data );
				}
			}
			CoursePress.Post.set( 'action', '' );
		},
		prepare: function( action, context ) {
			this.url = this.get( 'base_url' ) + action;

			if ( undefined !== context ) {
				this.set( 'context', context );
			}
		},
		defaults: {
			base_url: _coursepress._ajax_url + '?action=',
			context: 'response:'
		}
	} );

	CoursePress.Post = new CoursePress.Models.Post();

	CoursePress.checkWeakPassword = function() {
		var container = $(this).closest('form'),
			password_field = $('[name="password"]', container),
			confirm_password_field = $('[name="password_confirmation"]', container),
			strength_indicator = $('.password-strength-meter', container),
			confirm_weak_checkbox = $('.weak-password-confirm', container),
			password_strength_input = $('[name="password_strength_level"]', container);

		// If the password strength meter script has not been enqueued then we can't check strength
		if(typeof wp.passwordStrength.meter === 'undefined' || !_coursepress.password_strength_meter_enabled)
		{
			return;
		}

		var pass1 = password_field.val();
		var pass2 = confirm_password_field.val();

		// Reset the form & meter
		confirm_weak_checkbox.hide();
		strength_indicator.removeClass('short bad good strong').html('');

		if (!pass1 && !pass2) {
			return;
		}

		// Get the password strength
		var strength = wp.passwordStrength.meter(pass1, wp.passwordStrength.userInputBlacklist(), pass2);

		password_strength_input.val(strength);

		// Add the strength meter results
		switch (strength) {
			case 2:
				strength_indicator.addClass('bad').html(pwsL10n.bad);
				break;

			case 3:
				strength_indicator.addClass('good').html(pwsL10n.good);
				break;

			case 4:
				strength_indicator.addClass('strong').html(pwsL10n.strong);
				break;

			case 5:
				strength_indicator.addClass('bad').html(pwsL10n.mismatch);
				break;

			default:
				strength_indicator.addClass('bad').html(pwsL10n.short);

		}

		// The meter function returns a result even if pass2 is empty,
		// enable only the submit button if the password is strong and
		// both passwords are filled up
		if (strength < 3) {
			confirm_weak_checkbox.show();
		}
	};

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
					if ( "login" == action ) {
						$(window).scrollTop( $( "div.cp-mask.enrolment-container-div" ).offset().top - 100 );
					}
				}
			});
		},
		handle_signup_return: function( data ) {
			var signup_errors = data['signup_errors'];
			var steps = $( '[data-type="modal-step"]' );
			/**
			 * remove spinner
			 */
			$("span.fa-circle-o-notch").detach();
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
			} else {
				$.each( steps, function( i, step ) {
					var action = $( step ).attr( 'data-modal-action' );
					if ( 'passcode' == _coursepress.current_course_type && 'passcode' === action ) {
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

			var password = $('[name="password"]').val();
			var password_confirmed = $('[name="password_confirmation"]').val();

			// Passwords must match
			if ( password !== password_confirmed ) {
				valid = false;
				errors.push( _coursepress.signup_errors['mismatch_password'] );
			}

			if( typeof wp.passwordStrength.meter !== "undefined" && _coursepress.password_strength_meter_enabled )
			{
				var confirm_weak = $( '[name="confirm_weak_password"]'),
					strength = wp.passwordStrength.meter(
						password,
						[],
						password_confirmed
					);

				// Can't have a weak password
				if ( strength <= 2 && !confirm_weak.is( ':checked' ) ) {
					valid = false;
					errors.push( _coursepress.signup_errors['weak_password'] );
				}
			}

			if ( errors.length > 0 ) {
				var err_msg = '<ul>';
				errors.forEach( function( item ) {
					err_msg += '<li>' + item + '</li>';
				} );
				err_msg += '</ul>';

				$( '.bbm-wrapper #error-messages' ).first().html( err_msg );
			}

			return valid;
		},
		login_validation: function() {
			var valid = true,
				error_wrapper = $('.bbm-wrapper #error-messages' ),
				log = $( 'input[name="log"]' ),
				pwd = $( 'input[name="pwd"]' )
			;

			error_wrapper.html( '' );
			log.removeClass( 'has-error' );
			pwd.removeClass( 'has-error' );
			// All fields required
			if ( '' === log.val().trim() ) {
				valid = false;
				log.addClass( 'has-error' );
			}
			if ( '' === pwd.val().trim() ) {
				valid = false;
				pwd.addClass( 'has-error' );
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
			var cpmask = $( '.cp-mask' );

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
			CoursePress.Post.off( 'coursepress:enrollment:enroll_student_error' );
			CoursePress.Post.on( 'coursepress:enrollment:enroll_student_error', function( data ) {

				if ( undefined !== data['callback'] ) {
					var fn = CoursePress.Enrollment.dialog[ data['callback'] ];
					if ( typeof fn === 'function' ) {
						fn( data );
						return;
					}
				}
			});
			CoursePress.Post.off( 'coursepress:enrollment:enroll_student_success' );
			CoursePress.Post.on( 'coursepress:enrollment:enroll_student_success', function( data ) {
				cpmask.removeClass( 'loading' );

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
		$(this).attr( 'href', '#');
		var newDiv = $( '<div class="cp-mask enrolment-container-div">' );

		newDiv.appendTo( 'body' );

		// Set modal
		CoursePress.Dialogs.init();

		newDiv.html( CoursePress.Enrollment.dialog.render().el );
		CoursePress.Enrollment.dialog.openAtAction( 'login' );

		return false;
	};
	CoursePress.EnrollStudent = function() {
		var newDiv = $( '<div class="cp-mask enrolment-container-div">' );

		newDiv.appendTo( 'body' );

		// Set modal
		CoursePress.Dialogs.init();

		// Is paid course?
		if ( 'yes' === _coursepress.current_course_is_paid ) {
			$(newDiv).html(CoursePress.Enrollment.dialog.render().el);
			CoursePress.Enrollment.dialog.openAtAction('paid_enrollment');
		} else {
			$(newDiv ).addClass('loading');
			var enroll_data = {
				user_data: {
					ID: parseInt( _coursepress.current_student )
				}
			};
			
			// We're logged in, so lets try to enroll
			CoursePress.Enrollment.dialog.attempt_enroll( enroll_data );
			$(newDiv).html(CoursePress.Enrollment.dialog.render().el);
		}

		return false;
	};

	CoursePress.validateEnrollment = function() {
		var form = $(this);

		return false;
	};

	function process_popup_enrollment( step ) {
		if ( undefined === step ) {
			return false;
		}

		var action = $( $( '[data-type="modal-step"]' )[ step ] ).attr('data-modal-action');
		var nonce = $( '.enrollment-modal-container.bbm-modal__views' ).attr('data-nonce');
		var fn;

		CoursePress.Post.prepare( 'course_enrollment', 'enrollment:' );
		CoursePress.Post.set( 'action', action );

		if ( action === 'signup' ) {
			fn = CoursePress.Enrollment.dialog[ 'signup_validation' ];
			if ( typeof fn === 'function' && true !== fn() ) {
				return;
			}
		}

		if ( action === 'login' ) {
			fn = CoursePress.Enrollment.dialog[ 'login_validation' ];
			if ( typeof fn === 'function' && true !== fn() ) {
				return;
			}
		}

		var data = {
			nonce: nonce,
			step: step
		};

		fn = CoursePress.Enrollment.dialog[ action + '_data' ];
		if ( typeof fn === 'function' ) {
			data = fn( data );
		}

		/**
		 * Add indicator
		 */
		if ( "signup" == action ) {
			$("input.signup").after('<span class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></span>');
		}

		CoursePress.Post.set( 'data', data );
		CoursePress.Post.save();

		CoursePress.Post.on( 'coursepress:enrollment:' + action + '_success', function( data ) {

			// Update nonce
			$( '.enrollment-modal-container.bbm-modal__views' ).attr('data-nonce', data['nonce'] );

			if ( undefined !== data['callback'] ) {
				fn = CoursePress.Enrollment.dialog[ data['callback'] ];
				if ( typeof fn === 'function' ) {
					fn( data );
					return;
				}
			}
			if ( undefined !== data.last_step && parseInt( data.last_step ) < ( CoursePress.Enrollment.dialog.views.length -1 ) ) {
				CoursePress.Enrollment.dialog.openAt( parseInt( data.last_step ) + 1 );
				$('.enrolment-container-div' ).removeClass('hidden');
			}

		} );

		CoursePress.Post.on( 'coursepress:enrollment:' + action + '_error', function( data ) {
			if ( undefined !== data['callback'] ) {
				fn = CoursePress.Enrollment.dialog[ data['callback'] ];
				if ( typeof fn === 'function' ) {
					fn( data );
					return;
				}

			}
		} );
	};

	CoursePress.validatePassCode = function() {
		var form = $(this),
			passcode = form.find( '[name="passcode"]' )
			student_id = form.find( '[name="student_id"]' ).val(),
			course_id = form.find( '[name="course_id"]' ).val()
		;

		if ( '' === passcode.val() ) {
			new CoursePress.WindowAlert({
				message: _coursepress.module_error.passcode_required
			});
			return false;
		} else {
			CoursePress.Post.prepare( 'course_enrollment', 'enrollment:' );
			CoursePress.Post.set( 'action', 'enroll_with_passcode' );
			CoursePress.Post.set( 'data', {
				passcode: passcode.val(),
				student_id: student_id,
				course_id: course_id,
				step: 0
			});
			CoursePress.Post.off( 'coursepress:enrollment:enroll_with_passcode_success' );
			CoursePress.Post.on( 'coursepress:enrollment:enroll_with_passcode_success', function(data){
				var newDiv = $( '<div class="cp-mask enrolment-container-div">' );

				newDiv.appendTo( 'body' );
				// Set modal
				CoursePress.Dialogs.init();
				$(newDiv).html(CoursePress.Enrollment.dialog.render().el);
				CoursePress.Enrollment.dialog.openAtAction( 'enrolled' );
			});
			CoursePress.Post.off( 'coursepress:enrollment:enroll_with_passcode_error' );
			CoursePress.Post.on( 'coursepress:enrollment:enroll_with_passcode_error', function(data){
				new CoursePress.WindowAlert({
					message: data.message
				});
			});
			CoursePress.Post.save();
		}

		return false;
	};

	// Hook the events
	$( document )
		.on( 'click', '.cp-custom-login', CoursePress.CustomLoginHook )
		.on( 'click', '.apply-button.enroll', CoursePress.EnrollStudent )
		.on( 'submit', '[name="enrollment-process"][data-type="passcode"]', CoursePress.validatePassCode )
		.on( 'keyup', '.signup-form [name="password"], .signup-form [name="password_confirmation"], .student-settings [name="password"], .student-settings [name="password_confirmation"]', CoursePress.checkWeakPassword )
		.on( 'submit', '.apply-box .enrollment-process', CoursePress.validateEnrollment );

})(jQuery);
