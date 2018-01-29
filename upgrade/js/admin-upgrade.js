/*!  - v2.1.4
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2018; * Licensed GPLv2+ */
_.extend( _coursepress_upgrade, {
	totalCourses: 0,
	totalSuccess: 0,
	totalSend: 0,
	events: Backbone.Events,

	upgrade: Backbone.Model.extend({
		url: _coursepress_upgrade.ajax_url + '?action=coursepress_upgrade_from_1x',
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
			var progress_div = this.container.$el.find( '.course-progress' );
			
			if ( response ) {
				if ( response.success ) {
					if ( ! progress_div.hasClass('error') ) {
						progress_div.addClass( 'success' );
						_coursepress_upgrade.totalSuccess += 1;
					}
				} else {
					if ( !progress_div.hasClass('success') ) {
						progress_div.addClass( 'error' );
						_coursepress_upgrade.totalError += 1;
					}
				}
			}

			_coursepress_upgrade.totalSend += 1;

			// Trigger the done event
			_coursepress_upgrade.events.trigger( 'coursepress_update_done', this );
		},
		server_error: function() {
			window.alert( _coursepress_upgrade.server_error );
		}
	}),

	checkStudents: Backbone.Model.extend({
		url: _coursepress_upgrade.ajax_url + '?action=coursepress_upgrade_from_1x',
		initialize: function (options) {
			_.extend(this, options);
			this.on('error', this.server_error, this);

			this.set({
				_wpnonce: _coursepress_upgrade._wpnonce,
				type: 'check-students',
				course_id: -1
			});
		},
		parse: function (response) {
			// If response is zero then the ajax method was not found which means that 2.0 has already been loaded successfully
			if(0 === response)
			{
				_coursepress_upgrade.events.trigger('all_students_upgraded', this);
				return;
			}

			if (response.success) {
				if (response.data.remaining_students <= 0) {
					_coursepress_upgrade.events.trigger('all_students_upgraded', this);
				}
				else {
					_coursepress_upgrade.events.trigger('students_upgraded', response.data.remaining_students, this);
				}
			}
			else {
				_coursepress_upgrade.events.trigger('students_upgrade_failed', this);
			}
		},
		server_error: function () {
			window.alert(_coursepress_upgrade.server_error);
		}
	}),

	studentsView: Backbone.View.extend({
		className: 'coursepress-update-view',
		template: '<span class="students-upgrade-message"></span> <span class="students-progress"></span> <span class="course-progress"></span>',
		initialize: function (options) {
			_.extend(this, options);

			this.remaining_students = '';

			_coursepress_upgrade.events.on('students_upgraded', _.bind(this.students_upgraded, this));
			_coursepress_upgrade.events.on('all_students_upgraded', _.bind(this.all_students_upgraded, this));
			_coursepress_upgrade.events.on('students_upgrade_failed', _.bind(this.students_upgrade_failed, this));
		},
		students_upgraded: function (remaining) {
			this.remaining_students = remaining;
			this.render();
		},
		all_students_upgraded: function () {
			this.$el.find('.students-progress').html('0');
			this.$el.find('.course-progress').removeClass('error').addClass('success');
		},
		students_upgrade_failed: function () {
			this.$el.find('.course-progress').removeClass('success').addClass('error');
		},
		render: function () {
			this.$el.html(this.template);
			this.$el.find('.students-upgrade-message').html(_coursepress_upgrade.upgrading_students);
			this.$el.find('.students-progress').html(this.remaining_students);
			this.$el.insertBefore(this.submit_button);

			var checkStudents = new _coursepress_upgrade.checkStudents({});
			checkStudents.save();
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
						type: this.input.data('type'),
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
			input_being_processed = 0,
			update_nag = $( '.coursepress-upgrade-nag p' ),
			user_id = $( '[name="user_id"]', form ).val(),
			submit_button = form.find( '[type="submit"]' ),
			updateDone, wrap_title, timer, time, sender, allStudentsUpgraded, studentUpgradeFailed, studentsView, studentsViewRefreshInterval;

		if ( submit_button.is( ':disabled') ) {
			return false;
		}

		if ( 0 === update_nag.length ) {
			// Update nag have been removed, recreate 1
			wrap_title = $( '.coursepress-upgrade-view h2' );
			update_nag = $( '<div class="notice notice-warning is-dismissible coursepress-upgrade-nag">' ).insertAfter( wrap_title );
			update_nag = $( '<p>' ).appendTo( update_nag );
		}

		submit_button.attr( 'disabled', 'disabled' );

		update_nag.parent().removeClass( 'notice-error' ).addClass( 'notice-warning' );
		update_nag.html( _coursepress_upgrade.noloading );

		// Set the total # of courses to update
		_coursepress_upgrade.totalCourses = inputs.length;
		// Reset totalSend
		_coursepress_upgrade.totalSend = 0;
		// Reset successful
		_coursepress_upgrade.totalSuccess = 0;

		function update_next_course()
		{
			var course_id_input = inputs.get(input_being_processed);
			sender = new _coursepress_upgrade.view({ input: $(course_id_input), user_id: user_id });
			input_being_processed++;
		}
		update_next_course();

		function doFailureActions() {
			// Update unsuccessful, notify the user
			update_nag.parent().removeClass('notice-warning').addClass('notice-error');
			update_nag.html(_coursepress_upgrade.failed);
		}

		function doSuccessActions() {
			update_nag.parent().removeClass('notice-warning');
			update_nag.html(_coursepress_upgrade.success);

			// Redirect user
			time = 5;
			timer = setInterval(function () {
				time -= 1;
				update_nag.find('.coursepress-counter').html(time);

				if (0 === time) {
					clearInterval(timer);
					window.location = _coursepress_upgrade.cp2_url;
				}
			}, 1000);
		}

		// Listen to every update done
		updateDone = function() {
			// Check if update is completed
			if ( _coursepress_upgrade.totalCourses === _coursepress_upgrade.totalSend ) {
				// Check if all are successfully updated

				if ( _coursepress_upgrade.totalCourses === _coursepress_upgrade.totalSuccess ) {
					// If all the courses have been updated then start updating the students
					studentsView = new _coursepress_upgrade.studentsView({ submit_button: submit_button.closest('p') });
					studentsView.render();
				} else {
					doFailureActions();
				}
			}
			else {
				update_next_course();
			}
		};

		allStudentsUpgraded = function() {
			clearInterval(studentsViewRefreshInterval);
			// Wait while some ajax requests are still pending.
			doSuccessActions();
		};

		studentUpgradeFailed = function() {
			clearInterval(studentsViewRefreshInterval);
			doFailureActions();
		};

		// Hook to done event
		_coursepress_upgrade.events.off( 'coursepress_update_done' );
		_coursepress_upgrade.events.on( 'coursepress_update_done', updateDone );
		_coursepress_upgrade.events.on( 'all_students_upgraded', allStudentsUpgraded );
		_coursepress_upgrade.events.on( 'students_upgrade_failed', studentUpgradeFailed );

		return false;
	};

	$( document ).on( 'submit', '#coursepress-update-form', updateAllCourses );
})(jQuery);