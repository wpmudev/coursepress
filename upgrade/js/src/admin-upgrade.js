/* global _coursepress_upgrade */

_.extend( _coursepress_upgrade, {
	totalCourses: 0,
	totalSuccess: 0,
	totalSend: 0,
	events: Backbone.Events,

	upgrade: Backbone.Model.extend({
		url: _coursepress_upgrade.ajax_url + '?action=coursepress_upgrade_update',
		initialize: function( options ) {
			_.extend( this, options );
			this.on( 'error', this.server_error, this );

			var data = {
				_wpnonce: _coursepress_upgrade._wpnonce,
				course_id: this.course_id,
				user_id: this.user_id,
				container: false,
				total_courses: _coursepress_upgrade.totalCourses,
				total_success: _coursepress_upgrade.totalSuccess
			};
			this.set( data );
			this.save();
		},
		parse: function( response ) {
			var progress_div;

			progress_div = this.container.$el.find( '.course-progress' );

			if ( response.success ) {
				progress_div.addClass( 'success' );
				_coursepress_upgrade.totalSuccess += 1;
			} else {
				progress_div.addClass( 'error' );
				_coursepress_upgrade.totalError += 1;
			}

			_coursepress_upgrade.totalSend += 1;

			// Trigger the done event
			_coursepress_upgrade.events.trigger( 'coursepress_update_done', this );
		},
		server_error: function() {
			window.alert( _coursepress_upgrade.server_error );
		}
	}),

	view: Backbone.View.extend({
		className: 'coursepress-update-view',
		input: false,
		template: '<span class="course-title"></span> <span class="course-progress"></span>',
		initialize: function( options ) {
			_.extend( this, options );

			this.render();
		},
		render: function() {
			if ( this.input ) {
				// We'll update the course 1 by 1
				var course_title, id;

				id = this.input.val();
				this.input.parents().find( '#cp-updated-' + id ).remove();
				course_title = this.input.data( 'name' );

				this.$el.append( this.template );
				this.$el.attr( 'id', 'cp-updated-' + id ); // Marked the course
				this.$el.find( '.course-title' ).html( course_title );
				this.$el.insertAfter( this.input );

				if ( '' !== this.input.data('done') ) {
					// Don't update the already updated course
					_coursepress_upgrade.totalSuccess += 1;
					_coursepress_upgrade.totalSend += 1;
					this.$el.find( '.course-progress' ).addClass( 'success' );

					// Trigger the done event
					_coursepress_upgrade.events.trigger( 'coursepress_update_done', this );
				} else {
					this.sync = new _coursepress_upgrade.upgrade({
						course_id: this.input.val(),
						container: this,
						user_id: this.user_id
					});
				}
			}
		}
	})
});

(function($){
	var updateAllCourses = function() {
		var form = $(this),
			inputs = $( '[name="course"]', form ),
			update_nag = $( '.coursepress-upgrade-nag p' ),
			user_id = $( '[name="user_id"]', form ).val(),
			updateDone, wrap_title, timer, time, sender;

		if ( 0 === update_nag.length ) {
			// Update nag have been removed, recreate 1
			wrap_title = $( '.coursepress-upgrade-view h2' );
			update_nag = $( '<div class="notice notice-warning is-dismissible coursepress-upgrade-nag">' ).insertAfter( wrap_title );
			update_nag = $( '<p>' ).appendTo( update_nag );
		}

		update_nag.parent().removeClass( 'notice-error' ).addClass( 'notice-warning' );
		update_nag.html( _coursepress_upgrade.noloading );

		// Set the total # of courses to update
		_coursepress_upgrade.totalCourses = inputs.length;
		// Reset totalSend
		_coursepress_upgrade.totalSend = 0;
		// Reset successful
		_coursepress_upgrade.totalSuccess = 0;

		// Listen to every update done
		updateDone = function() {
			// Check if update is completed
			if ( _coursepress_upgrade.totalCourses === _coursepress_upgrade.totalSend ) {
				// Check if all are successfully updated

				if ( _coursepress_upgrade.totalCourses === _coursepress_upgrade.totalSuccess ) {
					update_nag.parent().removeClass( 'notice-warning' );
					update_nag.html( _coursepress_upgrade.success );

					// Send flush rewrite rules request
					_coursepress_upgrade._wpnonce = _coursepress_upgrade.flush_nonce;
					new _coursepress_upgrade.upgrade({
						container: sender,
						course_id: sender.course_id,
						user_id: sender.user_id
					});

					// Redirect user
					time = 5;
					timer = setInterval(function(){
						time -= 1;
						update_nag.find( '.coursepress-counter' ).html( time );

						if ( 0 === time ) {
							clearInterval(timer);
							window.location = _coursepress_upgrade.cp2_url;
						}
					}, 1000 );
				} else {
					// Update unsuccessful, notify the user
					update_nag.parent().removeClass( 'notice-warning' ).addClass( 'notice-error' );
					update_nag.html( _coursepress_upgrade.failed );
				}
			}
		};
		// Hook to done event
		_coursepress_upgrade.events.off( 'coursepress_update_done' );
		_coursepress_upgrade.events.on( 'coursepress_update_done', updateDone );

		inputs.each( function() {
			var input = $(this);

			sender = new _coursepress_upgrade.view({ input: input, user_id: user_id });
		});

		return false;
	};

	$( document ).on( 'submit', '#coursepress-update-form', updateAllCourses );
})(jQuery);