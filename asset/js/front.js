/*!  - v2.0.0
 * 
 * Copyright (c) 2016; * Licensed GPLv2+ */
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

/** Focus to the element **/
CoursePress.Focus = function( selector ) {
	var el = $( selector ), top;

	if ( 0 < el.length ) {
		top = el.offset().top;
		top -= 100;

		$(window).scrollTop( top );
	}

	return false;
};

/** Error Box **/
CoursePress.showError = function( error_message, container ) {
	var error_box = $( '<div class="cp-error cp-error-box"></div>' ),
		error = $( '<p>' ),
		closed = $( '<a class="cp-closed">&times;</a>' ),
		old_error_box = $( '.cp-error-box' ),
		removeError
	;

	if ( 0 < old_error_box.length ) {
		old_error_box.remove();
	}

	removeError = function() {
		error_box.remove();
	};

	error.html( error_message ).appendTo( error_box );
	closed.prependTo( error_box ).on( 'click', removeError );

	container.prepend( error_box );

	// Focus on the error box
	CoursePress.Focus( '.cp-error-box' );
};

/** Loader Mask **/
CoursePress.Mask = function( selector ) {
	selector = ! selector ? 'body' : selector;

	var mask = $( '<div class="cp-mask mask"></div>' );
	mask.appendTo( selector );

	return {
		mask: mask,
		done: function() {
			mask.remove();
		}
	};
};

/** Unit Progress **/
CoursePress.UnitProgressIndicator = function() {
	var a_col = $( 'ul.units-archive-list a' ).css('color');
	var p_col = $( 'body' ).css('color').replace('rgb(', '' ).replace(')', '' ).split( ',');
	var emptyFill = 'rgba(' + p_col[0] + ', ' + p_col[1] + ', ' + p_col[2] + ', 1)';

	var item_data = $( this ).data();
	var text_color = a_col;
	var text_align = 'center';
	var text_denominator = 4.5;
	var text_show = true;
	var animation = { duration: 1200, easing: "circleProgressEase" };

	if ( item_data.knobFgColor ) {
		a_col = item_data.knobFgColor;
	}
	if ( item_data.knobEmptyColor) {
		emptyFill = item_data.knobEmptyColor;
	}
	if ( item_data.knobTextColor ) {
		text_color = item_data.knobTextColor;
	}
	if ( item_data.knobTextAlign ) {
		text_align = item_data.knobTextAlign;
	}
	if ( item_data.knobTextDenominator ) {
		text_denominator = item_data.knobTextDenominator;
	}
	if ( 'undefined' !== typeof( item_data.knobTextShow ) ) {
		text_show = item_data.knobTextShow;
	}
	if ( 'undefined' !== typeof( item_data.knobAnimation ) ) {
		animation = item_data.knobAnimation;
	}

	var init = { color: a_col };
	$( this ).circleProgress( { fill: init, emptyFill: emptyFill, animation: animation } );

	var parent = $( this ).parents('ul')[0];

	$( this ).on( 'circle-animation-progress', function( e, v ) {
		var obj = $( this ).data( 'circle-progress' ),
			ctx = obj.ctx,
			s = obj.size,
			sv = (100 * v).toFixed(),
			ov = (100 * obj.value ).toFixed();
			sv = 100 - sv;

		if ( sv < ov ) {
			sv = ov;
		}
		ctx.save();

		if ( text_show ) {
			ctx.font = s / text_denominator + 'px sans-serif';
			ctx.textAlign = text_align;
			ctx.textBaseline = 'middle';
			ctx.fillStyle = text_color;
			ctx.fillText( sv + '%', s / 2 + s / 80, s / 2 );
		}
		ctx.restore();
	} );

	$( this ).on( 'circle-animation-end', function() {
		var obj = $( this ).data( 'circle-progress' ),
			ctx = obj.ctx,
			s = obj.size,
			sv = (100 * obj.value ).toFixed();
			obj.drawFrame( obj.value );

		if ( text_show ) {
			ctx.font = s / text_denominator + 'px sans-serif';
			ctx.textAlign = text_align;
			ctx.textBaseline = 'middle';
			ctx.fillStyle = text_color;
			ctx.fillText( sv + '%', s / 2, s / 2 );
		}
	} );

	// In case animation doesn't run
	var obj = $( this ).data( 'circle-progress' ),
		ctx = obj.ctx,
		s = obj.size,
		sv = (100 * obj.value ).toFixed();

	if ( text_show ) {
		ctx.font = s / text_denominator + 'px sans-serif';
		ctx.textAlign = text_align;
		ctx.textBaseline = 'middle';
		ctx.fillStyle = text_color;
		ctx.fillText( sv + '%', s / 2, s / 2 + s / 80 );
	}

	if (  'undefined' !== typeof( item_data.knobTextPrepend ) && item_data.knobTextPrepend ) {
		$( this ).parent().prepend(  '<span class="progress">'+sv + '%</span>' );
	}
};
// Initialize unit progress
CoursePress.unitProgressInit = function() {
	var discs = $( '.course-progress-disc' );

	if ( 0 < discs.length ) {
		discs.each( CoursePress.UnitProgressIndicator );
	}
};

/** Modal Dialog **/
CoursePress.Modal = Backbone.Modal.extend( {
	//template: _.template( $( '#modal-template' ).html() ),
	viewContainer: '.enrollment-modal-container',
	submitEl: '.done',
	cancelEl: '.cancel',
	options: 'meh',
	initialize: function() {
		this.template = _.template( $( '#modal-template' ).html() );
		this.views = this.getViews();
	},
	// Dynamically create the views from the templates.
	// This allows for WP filtering to add/remove steps
	getViews: function() {
		var object = {},
			steps = $( '[data-type="modal-step"]' );

		if ( 0 === steps.length ) {
			return;
		}

		$.each( steps, function( index, item ) {
			var step = index + 1;
			var id = $( item ).attr( 'id' );

			if ( undefined !== id ) {
				object['click #step' + step] = {
					view: _.template( $( '#' + id ).html() ),
					onActive: 'setActive'
				};
			}
		} );

		return object;
	},
	events: {
		'click .previous': 'previousStep',
		'click .next': 'nextStep',
		'click .cancel-link': 'closeDialog'
	},
	previousStep: function( e ) {
		e.preventDefault();
		this.previous();
		if ( typeof this.onPrevious === 'function' ) {
			this.onPrevious();
		}
	},
	nextStep: function( e ) {
		e.preventDefault();
		this.next();
		if ( typeof this.onNext === 'function' ) {
			this.onNext();
		}
	},
	closeDialog: function() {
		$('.enrolment-container-div' ).detach();
		return false;
	},
	setActive: function( options ) {
		this.trigger( 'modal:updated', { view: this, options: options } );
	},
	cancel: function() {
		$('.enrolment-container-div' ).detach();
		return false;
	}
} );

// Hook into document
$(document).ready( CoursePress.unitProgressInit ); // Call unit progress init

})(jQuery);
(function( $ ) {
	CoursePress.LoadFocusModule = function() {
		var nav = $(this),
			data = nav.data(),
			container = $( '.coursepress-focus-view' ),
			url = [ _coursepress.home_url, 'coursepress_focus' ]
		;

		if ( 'submit' === nav.attr( 'type' ) ) {
			// It's a submit button, continue submission
			return;
		}
		if ( 'course' === data.type ) {
			// Reload
			window.location = data.url;
			return;
		}

		url.push( data.course, data.unit, data.type, data.id );
		url = url.join( '/' );
		container.load( url );
		CoursePress.resetBrowserURL( data.url );

		return false;
	};

	CoursePress.validateUploadModule = function() {
		var input_file = $(this),
			parentDiv = input_file.parents( '.module-elements' ).first(),
			warningDiv = parentDiv.find( '.invalid-extension' ),
			filename = input_file.val(),
			extension = filename.split( '.' ).pop(),
			allowed_extensions = _.keys( _coursepress.allowed_student_extensions )
		;

		if ( 0 < warningDiv.length ) {
			// Remove warningdiv
			warningDiv.detach();
		}

		if ( ! _.contains( allowed_extensions, extension ) ) {
			warningDiv = $( '<div class="invalid-extension">' ).insertAfter( input_file.parent() );
			warningDiv.html( _coursepress.invalid_upload_message )
		}
	};

	CoursePress.ModuleSubmit = function() {
		var form = $(this),
			error_box = form.find( '.cp-error' ),
			focus_box = form.parents( '.coursepress-focus-view' ),
			iframe = false,
			timer = false
		;

		if ( 0 === error_box.length ) {
			error_box = $( '<div class="cp-error">' ).prependTo( form );
		}

		// Insert ajax marker
		form.append( '<input type="hidden" name="is_cp_ajax" value="1" />' );

		// Create iframe to trick the browser
		iframe = $( '<iframe name="cp_submitter" style="display:none;">' ).insertBefore( form );

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
						if ( data.data.url ) {
							if ( data.data.type && 'completion' === data.data.type ) {
								window.location = data.data.url;
							} else {
								focus_box.html( data.data.html );
								CoursePress.resetBrowserURL( data.data.url );
							}
						}
					} else {
						// Focus on the error box
						CoursePress.showError( data.data.error_message, form );
					}
				}
			}, 100 );
		});
	};

	CoursePress.toggleModuleState = function() {
		var button = $(this),
			parentDiv = button.closest( '.cp-module-content' ),
			elementsDiv = $( '.module-elements', parentDiv ),
			responseDiv = $( '.module-response', parentDiv )
		;

		responseDiv.hide();
		elementsDiv.show();

		return false;
	};

	// Recreate comment-reply js
	CoursePress.commentReplyLink = function() {
		var link = $(this),
			datacom = link.parents( '[data-comid]' ).first(),
			com_id = datacom.data( 'comid' ),
			module_content = link.parents( '.cp-module-content' ).first(),
			form = $( '#respond', module_content ),
			comment_div = $( '#comment-' + com_id ),
			comment_parent = $( '[name="comment_parent"]', form ),
			tempDiv = $( '.cp-temp-div' ),
			cancel_link = form.find( '#cancel-comment-reply-link' )
		;

		// Add marker to the original form position
		if ( 0 === tempDiv.length ) {
			tempDiv = $( '<div class="cp-temp-div"></div>' ).insertAfter( form );
		}

		comment_parent.val( com_id );
		comment_div.append( form );

		cancel_link.show().on( 'click', function() {
			form.insertBefore( tempDiv );
			cancel_link.hide();
			tempDiv.remove();

			return false;
		});

		// Focus to the form
		CoursePress.Focus( form );

		return false;
	};

	CoursePress.addComment = function(ev) {
		var button = $(this),
			module_content = button.parents( '.cp-module-content' ).first(),
			form = $( '#respond', module_content ),
			cp_form = $( '.cp-comment-form', module_content ),
			comment = $( '[name="comment"]', form ),
			comment_parent = $( '[name="comment_parent"]', form ),
			comment_post_ID = $( '[name="comment_post_ID"]', form ),
			subscribe = $( '[name="coursepress_subscribe"]', form ),
			student_id = $( '[name="student_id"]', module_content ),
			course_id = $( '[name="course_id"]', module_content ),
			unit_id = $( '[name="module_content"]', module_content ),
			cp_error = $( '.cp-error-box', form ),
			comment_list = $( '.comment-list', module_content ),
			params = {},
			is_reply = 0 < parseInt( comment_parent.val() ),
			request = new CoursePress.SendRequest(),
			restore_form,
			mask
		;

		// Remove previous error box
		cp_error.remove();

		if ( '' === comment.val() ) {
			// Alert the user
			CoursePress.showError( _coursepress.comments.require_valid_comment, form );

			// Prevent the form from submitting
			ev.stopImmediatePropagation();
			return false;
		}

		params = {
			comment: comment.val(),
			comment_parent: comment_parent.val(),
			comment_post_ID: comment_post_ID.val(),
			subscribe: subscribe.val(),
			cpnonce: _coursepress.cpnonce,
			method: 'add_single_comment',
			className: 'CoursePress_Module',
			course_id: course_id,
			unit_id: unit_id,
			student_id: student_id.val(),
			action: 'add_single_comment'
		};

		mask = CoursePress.Mask();
		restore_form = function() {
			var cancel_link = form.find( '#cancel-comment-reply-link' );

			comment.val( '' );
			comment_parent.val( '' );

			if ( cancel_link.is( ':visible' ) ) {
				cancel_link.trigger( 'click' );
			}

			// Remove cover mask
			mask.done();
		};


		request.set( params );
		request.off( 'coursepress:add_single_comment_success' );
		request.on( 'coursepress:add_single_comment_success', function( data ) {
			// Restore the form to it's orig position
			restore_form();

			if ( 0 < comment_list ) {
				comment_list = $( '<ol class="comment-list"></ol>' ).insertAfter( form );
			}

			var current_parent = comment_list,
				insert_type = cp_form.is( '.comment-form-desc' ) ? 'append' : 'prepend',
				child_list;

			if ( true === is_reply ) {
				current_parent = $( '#comment-' + params.comment_parent );
				child_list = current_parent.find( '.children' );

				if ( 0 === child_list.length ) {
					// Create a new .children ul
					current_parent.append( '<ul class="children"></ul>' );
					child_list = current_parent.find( '.children' );
				} else {
					child_list = 'append' === insert_type ? child_list.last() : child_list.first();
				}
				child_list[ insert_type ]( data.html );
			} else {
				current_parent[ insert_type ]( data.html );
			}

			// Focus to the last inserted comment
			CoursePress.Focus( '#comment-' + data.comment_id );
		} );
		request.on( 'coursepress:add_single_comment_error', function() {
			// Remove cover mask
			mask.done();
			// Alert the user
			CoursePress.showError( _coursepress.server_error, form );
		});
		request.save();

		// Prevent the form from submitting
		ev.stopImmediatePropagation();

		return false;
	};

	$( document )
		.on( 'submit', '.cp-form', CoursePress.ModuleSubmit )
		.on( 'click', '.focus-nav-prev, .focus-nav-next', CoursePress.LoadFocusModule )
		.on( 'click', '.button-reload-module', CoursePress.toggleModuleState )
		.on( 'click', '.cp-module-content .comment-reply-link', CoursePress.commentReplyLink )
		.on( 'click', '.cp-comment-submit', CoursePress.addComment )
		.on( 'change', '.cp-module-content .file input', CoursePress.validateUploadModule );

})(jQuery);
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