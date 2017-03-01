/** global _coursepress **/
window.WPMUCoursePress = (function($, doc, win) {
	var cp = this, calls = [], loaded = false;

	/**
	 * Extend undercore to cater validation on template files.
	 *
	 * @since 2.0.x
	 **/
	_.mixin({
		isTrue: function( value1, value2 ) {
			var isTrue = false;

			if ( _.isArray( value2 ) ) {
				isTrue = _.contains( value2, value1 );
			} else if ( _.isObject( value2 ) ) {
				isTrue = value2[value1];
			} else {
				isTrue = value2 === value1;
			}

			return isTrue;
		},

		/**
		 * Check and compare value1 to value2
		 *
		 * @param (mixed) value1
		 * @param (mixed) value2
		 **/
		checked: function( value1, value2 ) {
			return _.isTrue( value1, value2 ) ? ' checked="checked"' : '';
		},

		selected: function( value1, value2 ) {
			return _.isTrue( value1, value2 ) ? ' selected="selected"' : '';
		},

		disabled: function( value1, value2 ) {
			return _.isTrue( value1, value2 ) ? ' disabled="disabled"' : '';
		}
	});

	this.Events = Backbone.Events;
	this.vars = _coursepress;

	this.Define = function( module_name, module ) {
		module_name = module_name.split('.');
		var mod = false;

		var callModule = function(){
			if ( module_name.length ) {
				if ( ! mod ) {
					mod = module_name.shift();
					if ( ! cp[mod] ) {
						mod = cp[mod] = module.call(null, $, doc, win, cp);
					} else {
						mod = cp[mod];
					}
				} else {
					var next = module_name.shift();
					if ( next && ! mod[next] ) {
						mod = mod[next] = module.call(null, $, doc, win, cp);
					}
				}

				if ( module_name.length ) {
					// Recursively call the module
					callModule();
				}
			}
		};

		if ( ! loaded ) {
			calls.push(callModule);
		} else {
			callModule();
		}
	};

	this.Post = Backbone.Model.extend({
		url: cp.vars.ajax_url + '?action=courspress_request',
		defaults: {
			_wpnonce: cp.vars._wpnonce
		},

		initialize: function( options ) {
			_.defaults( this, options );

			this.on( 'error', this.serverErrorMessage, this );
		},

		process: function( response ) {
			var action = this.get( 'action' );

			if ( response.success ) {
				this.trigger( 'coursepress:' + action + '_success', response.data );
			} else {
				this.trigger( 'coursepress:' + action + '_error', response );
			}
		},

		serverErrorMessage: function() {
			new cp.View.PopUp({
				message: cp.vars.l0n.server_error
			});
		}
	});

	this.View = Backbone.View.extend({
		id: '',
		template_id: '',
		parent: 'body',
		options: {},

		initialize: function( args ) {
			var options = args && args.options ? args.options : {};
			this.model = new cp.Post();
			this.model.set(_.extend({}, this.options, options));

			if ( ! this.id ) {
				this.id = _.uniqueId( 'wpmu-coursepress' );
			}

			if ( this._render ) {
				this.once( 'coursepress:render_' + this.id, this._render, this );
			}
			if ( this._rendered ) {
				this.once( 'coursepress:rendered_' + this.id, this._rendered, this );
			}

			this.render();
		},

		init: function() {},

		render: function() {
			this.trigger( 'coursepress:render_' + this.id, this );

			if ( this.template_id ) {
					_.templateSettings = {
						interpolate: /\{\{(.+?)\}\}/g,
						evaluate: /<#(.+?)#>/g
					};

					var template = _.template( $('#' + this.template_id).html() );
				if ( template ) {
					this.$el.append( template( this.options ) );
				}
			} else if ( this.html ) {
				this.$el.append( this.html );
			}

			$(this.parent).append(this.$el);

			this.trigger( 'coursepress:rendered_' + this.id, this );
			cp.Events.trigger( 'coursepress::rendered', this );

			return this;
		}
	});

	$(document).ready(function() {
		loaded = true;

		calls.map(function( call ) {
			call();
		});
		calls = [];

		cp.Events.trigger( 'coursepress:loaded', $, doc, win, cp );
	});

	return this;
}(jQuery, document, window))();