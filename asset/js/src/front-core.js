/* global _coursepress */

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
