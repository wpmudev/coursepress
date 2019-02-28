/*! CoursePress - v2.2.1-beta.2
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2019; * Licensed GPLv2+ */
var CoursePress = {};
CoursePress.Models = CoursePress.Models || {};
CoursePress.Events = _.extend( {}, Backbone.Events );
CoursePress.UI = CoursePress.UI || {};
CoursePress.utility = CoursePress.utility || {};

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

CoursePress.WindowAlert = Backbone.View.extend({
	className: 'cp-mask cp-window-alert',
	message: '',
	callback: false,
	type: 'alert',
	html: '<div class="cp-alert-container"><p><button type="button" class="button">OK</button></p></div>',
	events: {
		'click .button': 'remove',
		'click .button-confirmed': 'doCallback'
	},
	initialize: function( options ) {
		_.extend( this, options );
		Backbone.View.prototype.initialize.apply( this, arguments );
		this.render();
	},
	render: function() {
		this.container = new Backbone.View({
			className: 'cp-alert-container',
		});

		this.container = this.container.$el.appendTo( this.$el );

		//this.$el.append( this.html );
		this.container = this.$el.find( '.cp-alert-container' );
		this.container.addClass( 'cp-' + this.type );
		this.container.prepend( '<p class="msg">' + this.message + '</p>' );

		var ok_button = new Backbone.View({
			tagName: 'button',
			attributes: {
				type: 'button',
				class: 'button'
			}
		});
		ok_button.$el.html( _coursepress.buttons.ok );
		this.container.append( ok_button.$el );

		if ( 'prompt' === this.type ) {
			var cancel_button = new Backbone.View({
				tagName: 'button',
				attributes: {
					type: 'button',
					class: 'button button-cancel'
				}
			});
			cancel_button.$el.html( _coursepress.buttons.cancel );
			cancel_button.$el.insertBefore( ok_button.$el );

			// Change the ok button class
			ok_button.$el.addClass( 'button-confirmed' );
		}

		this.$el.appendTo( 'body' );
	},
	doCallback: function() {
		if ( this.callback ) {
			this.callback.apply(this.callback, this);
		}
	}
});


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

CoursePress.removeErrorHint = function() {
	$( this ).removeClass( 'has-error' );
};

// OlD COURSEPRESS-FRONT

	// Actions and Filters
	CoursePress.actions = CoursePress.actions || {}; // Registered actions
	CoursePress.filters = CoursePress.filters || {}; // Registered filters

	/**
	 * Add a new Action callback to CoursePress.actions
	 *
	 * @param tag The tag specified by do_action()
	 * @param callback The callback function to call when do_action() is called
	 * @param priority The order in which to call the callbacks. Default: 10 (like WordPress)
	 */
	CoursePress.add_action = function( tag, callback, priority ) {
		if ( undefined === priority ) {
			priority = 10;
		}

		// If the tag doesn't exist, create it.
		CoursePress.actions[ tag ] = CoursePress.actions[ tag ] || [];
		CoursePress.actions[ tag ].push( { priority: priority, callback: callback } );
	};

	/**
	 * Add a new Filter callback to CoursePress.filters
	 *
	 * @param tag The tag specified by apply_filters()
	 * @param callback The callback function to call when apply_filters() is called
	 * @param priority Priority of filter to apply. Default: 10 (like WordPress)
	 */
	CoursePress.add_filter = function( tag, callback, priority ) {
		if ( undefined === priority ) {
			priority = 10;
		}

		// If the tag doesn't exist, create it.
		CoursePress.filters[ tag ] = CoursePress.filters[ tag ] || [];
		CoursePress.filters[ tag ].push( { priority: priority, callback: callback } );
	};

	/**
	 * Remove an Anction callback from CoursePress.actions
	 *
	 * Must be the exact same callback signature.
	 * Warning: Anonymous functions can not be removed.

	 * @param tag The tag specified by do_action()
	 * @param callback The callback function to remove
	 */
	CoursePress.remove_action = function( tag, callback ) {
		CoursePress.filters[ tag ] = CoursePress.filters[ tag ] || [];

		CoursePress.filters[ tag ].forEach( function( filter, i ) {
			if ( filter.callback === callback ) {
				CoursePress.filters[ tag ].splice(i, 1);
			}
		} );
	};

	/**
	 * Remove a Filter callback from CoursePress.filters
	 *
	 * Must be the exact same callback signature.
	 * Warning: Anonymous functions can not be removed.

	 * @param tag The tag specified by apply_filters()
	 * @param callback The callback function to remove
	 */
	CoursePress.remove_filter = function( tag, callback ) {
		CoursePress.filters[ tag ] = CoursePress.filters[ tag ] || [];

		CoursePress.filters[ tag ].forEach( function( filter, i ) {
			if ( filter.callback === callback ) {
				CoursePress.filters[ tag ].splice(i, 1);
			}
		} );
	};

	/**
	 * Calls actions that are stored in CoursePress.actions for a specific tag or nothing
	 * if there are no actions to call.
	 *
	 * @param tag A registered tag in Hook.actions
	 * @options Optional JavaScript object to pass to the callbacks
	 */
	CoursePress.do_action = function( tag, options ) {
		var actions = [];

		if ( undefined !== CoursePress.actions[ tag ] && CoursePress.actions[ tag ].length > 0 ) {
			CoursePress.actions[ tag ].forEach( function( hook ) {
				actions[ hook.priority ] = actions[ hook.priority ] || [];
				actions[ hook.priority ].push( hook.callback );
			} );

			actions.forEach( function( hooks ) {
				hooks.forEach( function( callback ) {
					callback( options );
				} );
			} );
		}
	};

	/**
	 * Calls filters that are stored in CoursePress.filters for a specific tag or return
	 * original value if no filters exist.
	 *
	 * @param tag A registered tag in Hook.filters
	 * @options Optional JavaScript object to pass to the callbacks
	 */
	CoursePress.apply_filters = function( tag, value, options ) {

		var filters = [];

		if ( undefined !== CoursePress.filters[ tag ] && CoursePress.filters[ tag ].length > 0 ) {

			CoursePress.filters[ tag ].forEach( function( hook ) {
				filters[ hook.priority ] = filters[ hook.priority ] || [];
				filters[ hook.priority ].push( hook.callback );
			} );

			filters.forEach( function( hooks ) {
				hooks.forEach( function( callback ) {
					value = callback( value, options );
				} );
			} );
		}

		return value;
	};

	/**
	 * proceder data-link if exists
	 */
	CoursePress.procederDataLink = function( e ) {
		var target = e.currentTarget;
		if ( $( target ).data( 'link' ) ) {
			window.location.href = $( target ).data( 'link' );
		}
	}

// Hook into document
$(document)
	.ready( CoursePress.unitProgressInit ) // Call unit progress init
	.on( 'focus', '.cp-mask .has-error, .cp .has-error', CoursePress.removeErrorHint )
	.on( "click", ".single_show_cart_button, .featured-course-link button", CoursePress.procederDataLink );

})(jQuery);

/** MODULES **/
(function( $ ) {
	CoursePress.timer = function( container ) {
		var timer_span = container.find( '.quiz_timer' ).show(),
			module_elements = container.find( '.module-elements' );

		if ( 0 === timer_span.length ) {
			return;
		}
		// Don't run the timer when module element is hidden
		if ( ! module_elements.is( ':visible' ) ) {
			timer_span.hide();
			return;
		}

		var duration = timer_span.data( 'limit' ), repeat = timer_span.data( 'retry' ),
			hours = 0, minutes = 0, seconds = 0, total_limit = 0, timer,
			_seconds = 60, _minutes = '00', _hours = '00', dtime, info, send, expired, inputs;

		duration = duration.split( ':' );

		seconds = duration.pop();
		_seconds = seconds = parseInt( seconds );

		if ( duration.length > 0 ) {
			_minutes = minutes = duration.pop();
			minutes = parseInt( minutes ) * 60;
		}

		if ( duration.length > 0 ) {
			_hours = hours = duration.pop();
			hours = parseInt( hours ) * 60 * 60;
		}

		total_limit = hours + minutes + seconds;

		info = container.find( '.quiz_timer_info' );
		inputs = container.find( '.module-elements input, .module_elements select, .module-elements textarea, .module-elements .video_player' );
		inputs.removeAttr('disabled');

		expired = function() {
			inputs.attr( 'disabled', 'disabled' );
			info.show();
		};

		if ( 0 === total_limit ) {
			if ( 'no' === repeat ) {
				expired();
			} else {
				timer_span.hide();
			}
			return;
		}

		timer = setInterval(function(){

			if ( 60 == _seconds ) {
				if ( parseInt( _minutes ) > 0 ) {
					_minutes = parseInt( _minutes ) - 1;
				}
			}

			_seconds = parseInt( _seconds ) - 1;

			if ( _seconds <= 0 && _minutes <= 0 && _hours <= 0 ) {
				clearInterval( timer );
				expired();
				// Send record data in silence
				send = new CoursePress.SendRequest();
				send.set({
					cpnonce: _coursepress.cpnonce,
					className: 'CoursePress_Module',
					method: 'record_expired_answer',
					module_id: container.data( 'id' ),
					course_id: container.find( '[name="course_id"]' ).val(),
					unit_id: container.find( '[name="unit_id"]' ).val(),
					student_id: container.find( '[name="student_id"]' ).val(),
					action: 'record_time'
				});
				send.save();
				// Enable retry button here
				info.on( 'click', function() {
					inputs.removeAttr( 'disabled' );
					info.hide();
					CoursePress.timer( container );
				});
			}
			if ( _seconds < 0 ) {
				_seconds = 59;
				if ( parseInt( _minutes ) > 0 ) {
					_minutes = parseInt( _minutes ) - 1;
				}
				if ( parseInt( _minutes ) <= 0 ) {
					if ( parseInt( _hours ) > 0 ) {
						_hours = parseInt( _hours ) - 1;
						_minutes = 59;
						if ( _hours < 10 ) {
							_hours = '0' + parseInt( _hours );
						}
					}
				}
			}
			if ( _seconds < 10 ) {
				_seconds = '0' + parseInt( _seconds );
			}
			if ( _minutes < 10 ) {
				_minutes = '0' + parseInt( _minutes );
			}
			if ( '00' == _hours ) {
				dtime = _minutes + ':' + _seconds;
			} else {
				dtime = _hours + ':' + _minutes + ':' + _seconds;
			}
			timer_span.html( dtime );
		}, 1000);
	};

	CoursePress.MediaElements = function( container ) {
		if ( $.fn.mediaelementplayer ) {
			var media = $( 'audio,video', container );

			if(videojs.getPlayers()) {
				var player = videojs(media[0].id);
			}

			if ( media.length > 0 ) {
				media.mediaelementplayer();
			}
		}
	};

	CoursePress.LoadFocusModule = function() {
		var nav = $(this),
			data = nav.data(),
			container = $( '.coursepress-focus-view' ),
			url = [ _coursepress.home_url, 'coursepress_focus' ],
			parents = $( '.cp, .coursepress-focus-view' )
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

		if ( data.unit ) {
			//Find current unit serve
			var current_unit = parents.find( '[name="unit_id"]' );

			if ( 0 === current_unit.length || data.unit !== current_unit.val() ) {
				window.location = data.url;
				return;
			}
		}

		url.push( data.course, data.unit, data.type, data.id );
		url = url.join( '/' );
		container.load( url, function() {
			CoursePress.resetBrowserURL( data.url );
			CoursePress.timer( container.find( '.cp-module-content' ) );
			CoursePress.MediaElements( container.find( '.cp-module-content' ) );
		});

		return false;
	};

	CoursePress.validateUploadModule = function() {
		var input_file = $(this),
			parentDiv = input_file.parents( '.module-elements' ).first(),
			warningDiv = parentDiv.find( '.invalid-extension, .current-file' ),
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
		} else {
			var file = input_file.get(0);

			if ( file.files && file.files.length ) {
				for (var i=0; i < file.files.length; i++) {
					filename = file.files[i].name;
				}
			}

			warningDiv = $( '<div class="current-file"></div>' ).html( filename );
			warningDiv.insertAfter( input_file.parent() );
		}
	};

	CoursePress.ModuleSubmit = function() {
		var form = $(this),
			error_box = form.find( '.cp-error-box' ),
			focus_box = form.parents( '.coursepress-focus-view, .cp.unit-wrapper' ),
			iframe = false,
			timer = false,
			module_elements = $( '.module-elements[data-required="1"]', form ),
			module_response = module_elements.next( '.module-response' ),
			is_focus = form.parents( '.coursepress-focus-view' ).length > 0,
			error = 0, mask,
            validate = $('[name=save_progress_and_exit]').length < 1
		;
		if ( 0 < error_box.length ) {
			error_box.remove();
		}

		// Validate required submission
		if ( validate ) {
			module_elements.each( function() {
				var module = $(this),
				module_type = module.data( 'type' ),
				input;
				// Validate radio and checkbox
				if ( _.contains( ['input-checkbox', 'input-radio', 'input-quiz'], module_type ) ) {
					input = $( ':checked', module );
					if ( 0 == input.length ) {
						error += 1;
					}
				} else if ( 'input-upload' === module_type && 0 === module_response.length ) {
					input = $( '[type="file"]', module );
					if ( '' === input.val() ) {
						error += 1;
					}
					// Validate input module
				} else if ( _.contains( ['input-text', 'input-textarea', 'input-select'], module_type ) ) {
					input = $( 'input,textarea,select', module );
					if ( '' === input.val() ) {
						error += 1;
					}
				}
			} );
			if ( error > 0 ) {
				// Don't submit if an error is found!
				new CoursePress.WindowAlert({
					message: _coursepress.module_error[ is_focus ? 'required' : 'normal_required' ]
				});
				return false;
			}
		}

		// Mask the page
		mask = CoursePress.Mask();

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
					// Remove the mask
					mask.done();

					var data = window.JSON.parse( html );

					if ( true === data.success ) {
						// Process success
						if ( data.data.url ) {
							if ( false === is_focus || true === data.data.is_reload || data.data.type && 'completion' === data.data.type ) {
								window.location = data.data.url;
							} else {
								focus_box.html( data.data.html );
								CoursePress.resetBrowserURL( data.data.url );
								CoursePress.timer( focus_box.find( '.cp-module-content' ) );
								CoursePress.MediaElements( focus_box.find( '.cp-module-content' ) );
							}
						}
					} else {
						// Focus on the error box
						if ( data.data.html ) {
							focus_box.html( data.data.html );
						}
						new CoursePress.WindowAlert({
							message: data.data.error_message
						});
					}
				}
			}, 100 );
		});
	};

	CoursePress.toggleModuleState = function() {
		var button = $(this),
			parentDiv = button.closest( '.cp-module-content' ),
			elementsDiv = $( '.module-elements', parentDiv ),
			responseDiv = $( '.module-response', parentDiv ),
			moduleHidden = $( '.cp-is-hidden-module', parentDiv )
		;

		responseDiv.hide();
		elementsDiv.show();
		moduleHidden.val(0);
		CoursePress.timer( parentDiv );

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
		form.hide();
		comment_div.append( form.slideDown() );

		cancel_link.off( 'click' );
		cancel_link.show().on( 'click', function() {
			form.insertBefore( tempDiv );
			cancel_link.hide();
			tempDiv.remove();

			return false;
		});

		// Focus to the form
		CoursePress.Focus( form );
		// Focus to textarea
		form.find( 'textarea[name="comment"]' ).focus();

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
			new CoursePress.WindowAlert({
				message: _coursepress.comments.require_valid_comment
			});

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

	CoursePress.singleFolded = function() {
			var target = $('>ul', $(this).parent() );
			var unit = $('.unit-archive-single-title', $(this).parent());
			var modules_container = $('.unit-archive-module-wrapper', $(this).parent());
			var container = $(this);
			if ( container.hasClass('folded') ) {
				target.slideDown( function() {
					container.removeClass('folded');
					container.closest('li').removeClass('folded').addClass('unfolded');
					unit.attr('href', unit.data('original-href'));
					unit.off('click');
				});
			} else {
				target.slideUp(function() {
					container.addClass('folded');
					container.closest('li').removeClass('unfolded').addClass('folded');
					if ( "undefined" == typeof( unit.data('href') ) ) {
						/**
						 * find last seen module
						 */
						var module = $('.module-seen', modules_container).last();
						if ( module.length ) {
							module = $('.module-title', module );
							if ( module.length ) {
								unit.attr('href', unit.attr('href') + '#module-'+ module.data('id') );
								return false;
							}
						}
						/**
						 * find last seen section
						 */
						var section = $('.section-seen', modules_container).last();
						if ( section.length ) {
							section = $('.section-title', section );
							if ( section.length ) {
								unit.attr('href', unit.attr('href') + '#section-'+ section.data('id') );
								return false;
							}
						}
					}
				});
			}
			return false;
	};

	CoursePress.unitFolded = function() {
			var span = $(this),
				container = span.parents( 'li' ).first(),
				module_wrapper = container.find( '.unit-structure-modules' ),
				is_open = container.is( '.folded' )
			;

			if ( is_open ) {
				container.removeClass( 'folded' ).addClass( 'unfolded' );
				span.removeClass( 'folded' );
				module_wrapper.slideDown();
			} else {
				container.removeClass( 'unfolded' ).addClass( 'folded' );
				span.addClass( 'folded' );
				module_wrapper.slideUp();
			}
	};

	/**
	 * Save Progress & Exit
	 */
	CoursePress.saveProgressAndExit = function() {
		var form = $(this).closest('form');
		$("#respond", form).detach();
		form.append( '<input type="hidden" name="save_progress_and_exit" value="1" />' );
		form.submit();
	}

	CoursePress.hookModuleVideos = function() {

		$('.video-js').each(function(){
			var video_id = $(this).attr('id');
			var video = videojs(video_id);

			video.on('ready', function(){
				var player = this,
					player_element = $(player.el());

				function change_video_status(player)
				{
					if( $(player.el()).closest('.video_player').is('[disabled="disabled"]') )
					{
						player.pause();
					}
				}

				if(player_element.is('[autoplay]'))
				{
					player.play();
				}

				if(player_element.is('[muted]'))
				{
					player.muted(true);
				}

				player.one('click', function(){
					player.play();
				});

				player.one('play', function(){
					CoursePress.timer(player_element.closest('.cp-module-content'));
				});

				player.on('play', function(){
					change_video_status(player);
				});

				player.on('timeupdate', function(){
					change_video_status(player);
				});
			});
		});
	};

	$( document )
		.ready(function(){
			$('.cp-module-content').each(function(){
				var content = $(this);
				if(content.data('type') !== 'video')
				{
					CoursePress.timer(content);
				}
			});

			CoursePress.hookModuleVideos();
		})
		.on( 'submit', '.cp-form', CoursePress.ModuleSubmit )
		.on( 'click', '.focus-nav-prev, .focus-nav-next', CoursePress.LoadFocusModule )
		.on( 'click', '.button-reload-module', CoursePress.toggleModuleState )
		.on( 'click', '.cp-module-content .comment-reply-link', CoursePress.commentReplyLink )
		.on( 'click', '.cp-comment-submit', CoursePress.addComment )
		.on( 'change', '.cp-module-content .file input', CoursePress.validateUploadModule )
		.on( 'click', '.unit-archive-single .fold', CoursePress.singleFolded )
		.on( 'click', '.course-structure-block .unit .fold, .unit-archive-list .fold', CoursePress.unitFolded )
		.on( 'click', '.save-progress-and-exit', CoursePress.saveProgressAndExit );


})(jQuery);

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

			var container = 'div.enrolment-container-div ';
			var errors = [];
			var password = $( container + '[name="password"]' ).val().trim();
			var password_confirmed = $( container + '[name="password_confirmation"]' ).val().trim();
			// All fields required
			if (
				'' === $( container + 'input[name=first_name]' ).val().trim() ||
				'' === $( container + 'input[name=last_name]' ).val().trim() ||
				'' === $( container + 'input[name=username]' ).val().trim() ||
				'' === $( container + 'input[name=email]' ).val().trim() ||
				'' === password ||
				'' === password_confirmed
			) {
				valid = false;
				errors.push( _coursepress.signup_errors['all_fields'] );
			}


			// Passwords must match
			if ( password !== password_confirmed ) {
				valid = false;
				errors.push( _coursepress.signup_errors['mismatch_password'] );
			}

			if( typeof wp.passwordStrength.meter !== "undefined" && _coursepress.password_strength_meter_enabled )
			{
				var confirm_weak = $( container + '[name="confirm_weak_password"]'),
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
				container = 'div.enrolment-container-div ',
				error_wrapper = $('.bbm-wrapper #error-messages' ),
				log = $( container + 'input[name="log"]' ),
				pwd = $( container + 'input[name="pwd"]' )
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
			var container = 'div.enrolment-container-div ';
			data.first_name = $( container + 'input[name=first_name]' ).val();
			data.last_name = $( container + 'input[name=last_name]' ).val();
			data.username = $( container + 'input[name=username]' ).val();
			data.email = $( container + 'input[name=email]' ).val();
			data.password = $( container + 'input[name=password]' ).val();
			data.nonce = $( '.bbm-modal-nonce.signup' ).attr('data-nonce');

			return data;
		},
		login_data: function( data ) {
			var container = 'div.enrolment-container-div ';
			var course_id = $( '.enrollment-modal-container.bbm-modal__views' ).attr('data-course');
			data.username = $( container + 'input[name=log]' ).val();
			data.password = $( container + 'input[name=pwd]' ).val();
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

/* global CoursePress */

(function($){
	var confirmWithdrawal = function() {
		var href = $(this).attr( 'href' ),
			win = new CoursePress.WindowAlert({
			type: 'prompt',
			message: _coursepress.confirmed_withdraw,
			callback: function() {
				window.location = href;
			}
		});
		return false;
	};

	var confirmManage= function() {
		var href = $(this).data( 'link' ),
			win = new CoursePress.WindowAlert({
			type: 'prompt',
			message: _coursepress.confirmed_edit,
			callback: function() {
				window.location = href;
			}
		});

		return false;
	};

	$(document)
		.on( 'click', '.cp-withdraw-student', confirmWithdrawal )
		.on( 'click', '.coursepress-course-link', confirmManage );

})(jQuery);
