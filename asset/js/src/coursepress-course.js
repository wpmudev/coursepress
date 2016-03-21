/*global tinyMCE*/
/*global _coursepress*/

var CoursePress = CoursePress || {};
CoursePress.Events = CoursePress.Events || _.extend( {}, Backbone.Events );

(function( $ ) {
	CoursePress.Models = CoursePress.Models || {};

	CoursePress.Models.Course = CoursePress.Models.Course || Backbone.Model.extend( {
		url: _coursepress._ajax_url + '?action=update_course',
		parse: function( response ) {

			// Trigger course update events
			if ( true === response.success ) {
				this.set( 'response_data', response.data );
				this.trigger( 'coursepress:' + response.data.action + '_success', response.data );
			} else {
				this.set( 'response_data', {} );
				this.trigger( 'coursepress:' + response.data.action + '_error', response.data );
			}

			CoursePress.Course.set( 'action', '' );
		},
		defaults: {}
	} );

	CoursePress.Course = new CoursePress.Models.Course();

	// General Post
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

			CoursePress.Course.set( 'action', '' );
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

	CoursePress.Course.multiple_elements = function( items, needle ) {
		var item_count = 0;

		$.each( items, function( index, element ) {
			if ( needle === element.name ) {
				item_count += 1;
			}
		} );

		return item_count > 1;
	};

	CoursePress.Course.add_array_to_data = function( data, items ) {
		var item_count = 0;
		var last_item = '';

		$.each( items, function( index, element ) {
			var name = element.name.replace( /(\[|\]\[)/g, '/' ).replace( /\]/g, '' );//.replace(/\/$/g, '');

			if ( last_item !== name ) {
				item_count = 0;
			}

			if ( name.match( /\/$/g ) || CoursePress.Course.multiple_elements( items, element.name ) ) {
				//console.log( item_count + ': ' + element.name );
				if ( name.match( /\/$/g ) ) {
					CoursePress.utility.update_object_by_path( data, name + item_count, element.value );
				} else {
					CoursePress.utility.update_object_by_path( data, name + '/' + item_count, element.value );
				}
			} else {
				CoursePress.utility.update_object_by_path( data, name, element.value );
			}

			item_count += 1;
			last_item = name;
		} );
	};

	CoursePress.Course.fix_step_checkboxes = function( items, step, false_value ) {
		return CoursePress.utility.fix_checkboxes( items, '.step-content.step-' + step, false_value );
	};

	CoursePress.Course.get_step = function( step, action_type ) {
		if ( undefined === action_type ) {
			action_type = 'next';
		}

		var data = {}, meta_items;

		data.step = step;
		data.is_finished = false;

		// Step 1 Data
		if ( 1 <= step ) {
			data.course_id = $( '[name="course_id"]' ).val();
			data.course_name = $( '[name="course_name"]' ).val();

			data.course_excerpt = tinyMCE && tinyMCE.get( 'courseExcerpt' ) ? tinyMCE.get( 'courseExcerpt' ).getContent() : $( '[name="course_excerpt"]' ).val();

			meta_items = $( '.step-content.step-1 [name^="meta_"]' ).serializeArray();
			meta_items = CoursePress.Course.fix_step_checkboxes( meta_items, step );
			CoursePress.Course.add_array_to_data( data, meta_items );
		}

		// Step 2 Data
		if ( 2 <= step ) {
			data.course_description = tinyMCE && tinyMCE.get( 'courseDescription' ) ? tinyMCE.get( 'courseDescription' ).getContent() : $( '[name="course_description"]' ).val();

			meta_items = $( '.step-content.step-2 [name^="meta_"]' ).serializeArray();
			meta_items = CoursePress.Course.fix_step_checkboxes( meta_items, step, '0' );
			CoursePress.Course.add_array_to_data( data, meta_items );
		}

		// Step 3 Data
		if ( 3 <= step ) {
			meta_items = $( '.step-content.step-3 [name^="meta_"]' ).serializeArray();
			meta_items = CoursePress.Course.fix_step_checkboxes( meta_items, step, '0' );
			CoursePress.Course.add_array_to_data( data, meta_items );
		}

		// Step 4 Data
		if ( 4 <= step ) {
			meta_items = $( '.step-content.step-4 [name^="meta_"]' ).serializeArray();
			meta_items = CoursePress.Course.fix_step_checkboxes( meta_items, step, '0' );
			CoursePress.Course.add_array_to_data( data, meta_items );
		}

		// Step 1 Data
		if ( 5 <= step ) {
			meta_items = $( '.step-content.step-5 [name^="meta_"]' ).serializeArray();
			meta_items = CoursePress.Course.fix_step_checkboxes( meta_items, step, '0' );
			CoursePress.Course.add_array_to_data( data, meta_items );
		}

		// Step 1 Data
		if ( 6 <= step ) {
			meta_items = $( '.step-content.step-6 [name^="meta_"]' ).serializeArray();
			meta_items = CoursePress.Course.fix_step_checkboxes( meta_items, step, '0' );
			CoursePress.Course.add_array_to_data( data, meta_items );
		}

		var next_step = step;

		if ( 'next' === action_type ) {
			data.meta_setup_marker = step;
			next_step = next_step !== 6 ? next_step + 1 : next_step;
		}

		if ( 'update' === action_type ) {
			//data.meta_setup_marker = step;
		}

		if ( 'prev' === action_type ) {
			data.meta_setup_marker = step - 1;
			next_step = next_step !== 1 ? next_step - 1 : next_step;
		}

		if ( 'finish' === action_type ) {
			data.meta_setup_marker = step;
			data.is_finished = true;
		}

		data.nonce = get_setup_nonce();
		CoursePress.Course.set( 'data', data );
		CoursePress.Course.set( 'action', 'update_course' );
		CoursePress.Course.set( 'next_step', next_step );
	};

	function course_structure_update () {
		$.each( $( '.step-content .course-structure tr.unit' ), function( uidx, unit ) {

			// Make sure its a tree node
			var match;

			if ( match = $( unit ).attr( 'class' ).match( /treegrid-\d{1,10}/g )[ 0 ] ) {
				var unit_id = match.trim().split( '-' ).pop();

				var pages_selector = '.step-content .course-structure tr.page.treegrid-parent-' + unit_id;
				var pages = $( pages_selector );

				// Do pages first
				$.each( pages, function( pidx, page ) {
					var page_id = $( page ).attr( 'class' ).match( /treegrid-\d{1,10}/g )[ 0 ].trim().split( '-' ).pop();
					var modules_selector = '.step-content .course-structure tr.module.treegrid-parent-' + page_id;
					var modules_visible_boxes = modules_selector + ' [name*="meta_structure_visible_modules"]';
					var modules_visible_count = $( modules_visible_boxes ).length;
					var modules_visible_checked = $( modules_visible_boxes + ':checked' ).length;

					$( '.step-content .course-structure .treegrid-' + page_id + ' [name*=meta_structure_visible_pages]' ).prop(
						'checked',
						modules_visible_count === modules_visible_checked && modules_visible_checked > 0
					);

					var modules_preview_boxes = modules_selector + ' [name*="meta_structure_preview_modules"]';
					var modules_preview_count = $( modules_preview_boxes ).length;
					var modules_preview_checked = $( modules_preview_boxes + ':checked' ).length;

					$( '.step-content .course-structure .treegrid-' + page_id + ' [name*=meta_structure_preview_pages]' ).prop(
						'checked',
						modules_preview_count === modules_preview_checked && modules_preview_checked > 0
					);
				} );

				// Then do units
				var pages_visible_boxes = pages_selector + ' [name*="meta_structure_visible_pages"]';
				var pages_visible_count = $( pages_visible_boxes ).length;
				var pages_visible_checked = $( pages_visible_boxes + ':checked' ).length;

				$( '.step-content .course-structure .treegrid-' + unit_id + ' [name*=meta_structure_visible_units]' ).prop(
					'checked',
					pages_visible_count === pages_visible_checked && pages_visible_checked > 0
				);

				var pages_preview_boxes = pages_selector + ' [name*="meta_structure_preview_pages"]';
				var pages_preview_count = $( pages_preview_boxes ).length;
				var pages_preview_checked = $( pages_preview_boxes + ':checked' ).length;

				$( '.step-content .course-structure .treegrid-' + unit_id + ' [name*=meta_structure_preview_units]' ).prop(
					'checked',
					pages_preview_count === pages_preview_checked && pages_preview_checked > 0
				);
			}
		} );
	}

	function setup_UI() {
		// Setup Accordion.
		$( '#course-setup-steps' ).accordion( {
			disabled: true,
			autoHeight: false,
			collapsible: true,
			heightStyle: 'content',
			active: 0,
			animate: 200 // collapse will take 300ms
		} );

		// Slide Accordion into Position
		$( '#course-setup-steps .step-title' ).bind( 'click', function() {
			var self = jQuery( this );
			var step = parseInt( self.attr( 'class' ).match( /step-\d{1,10}/g )[ 0 ].trim().split( '-' ).pop() );
			var pre_step = 1 < ( step - 1 ) ? step - 1 : 1;

			if ( ! self.find( '.status' ).hasClass( 'saved' ) && !$( '.step-title.step-' + pre_step ).find( '.status' ).hasClass( 'saved' ) ) {
				self.effect( 'highlight', { color: '#ffabab', duration: 300 } );
				return;
			}

			// Manually handle the accordion so we don't progress too soon
			$( '#course-setup-steps' ).accordion( 'enable' ).accordion( { active: (step - 1) } ).accordion( 'disable' );

			setTimeout( function() {
				var theOffset = self.offset();
				$( 'body,html' ).animate( { scrollTop: theOffset.top - 110, duration: 200 } );
			}, 200 ); // ensure the collapse animation is done
		} );

		// Setup Chosen
		//$( '.chosen-select' ).chosen( { disable_search_threshold: 10 } );
		$( '.chosen-select.medium' ).chosen( { disable_search_threshold: 5, width: '40%' } );
		$( '.chosen-select.narrow' ).chosen( { disable_search_threshold: 5, width: '20%' } );

		// Tree for course structure
		$( 'table.course-structure-tree' ).treegrid( { initialState: 'expanded' } );

		// ===== DATE PICKERS =====
		$( '.dateinput' ).datepicker( {
			dateFormat: 'yy-mm-dd'
			//firstDay: coursepress.start_of_week
		} );

		$( '.date' ).click( function() {
			if ( !$( this ).parents( 'div' ).hasClass( 'disabled' ) ) {
				$( this ).find( '.dateinput' ).datepicker( 'show' );
			}
		} );

		$( '[name="meta_enrollment_open_ended"]' ).change( function() {
			if ( this.checked ) {
				$( this ).parents( '.enrollment-dates' ).find( '.start-date' ).addClass( 'disabled' );
				$( this ).parents( '.enrollment-dates' ).find( '.start-date input' ).attr( 'disabled', 'disabled' );
				$( this ).parents( '.enrollment-dates' ).find( '.end-date' ).addClass( 'disabled' );
				$( this ).parents( '.enrollment-dates' ).find( '.end-date input' ).attr( 'disabled', 'disabled' );
			} else {
				$( this ).parents( '.enrollment-dates' ).find( '.start-date' ).removeClass( 'disabled' );
				$( this ).parents( '.enrollment-dates' ).find( '.start-date input' ).removeAttr( 'disabled' );
				$( this ).parents( '.enrollment-dates' ).find( '.end-date' ).removeClass( 'disabled' );
				$( this ).parents( '.enrollment-dates' ).find( '.end-date input' ).removeAttr( 'disabled' );
			}
		} );

		$( '[name="meta_course_open_ended"]' ).change( function() {
			if ( this.checked ) {
				$( this ).parents( '.course-dates' ).find( '.end-date' ).addClass( 'disabled' );
				$( this ).parents( '.course-dates' ).find( '.end-date input' ).attr( 'disabled', 'disabled' );
			} else {
				$( this ).parents( '.course-dates' ).find( '.end-date' ).removeClass( 'disabled' );
				$( this ).parents( '.course-dates' ).find( '.end-date input' ).removeAttr( 'disabled' );
			}
		} );
		// ===== END DATE PICKERS =====

		// Spinners
		$( '.spinners' ).spinner();

		$( '[name="meta_class_limited"]' ).change( function() {
			// DEBUG code. remove it.
			window.console.log( 'yup' );
			window.console.log( $( this ).parents( '.class-size' ).find( '.num-students' ) );
			if ( this.checked ) {
				// DEBUG code. remove it.
				window.console.log( 'checked' );
				$( this ).parents( '.class-size' ).find( '.num-students' ).removeClass( 'disabled' );
				$( this ).parents( '.class-size' ).find( '.num-students input' ).removeAttr( 'disabled' );
			} else {
				// DEBUG code. remove it.
				window.console.log( 'unchecked' );
				$( this ).parents( '.class-size' ).find( '.num-students' ).addClass( 'disabled' );
				$( this ).parents( '.class-size' ).find( '.num-students input' ).attr( 'disabled', 'disabled' );
			}
		} );

		// ====== COURSEPRESS UI TOGGLES =====
		$( '.coursepress-ui-toggle-switch' ).coursepress_ui_toggle();
	}

	function bind_buttons() {
		// Show update button...
		$( '#course-setup-steps input' ).on( 'keyup change', function() {
			var step_box = $( this ).parents('.step-content')[0];
			$( step_box ).find('.button.update.hidden' ).removeClass('hidden');
		} );

		$( '#course-setup-steps select' ).on( 'change', function() {
			var step_box = $( this ).parents('.step-content')[0];
			$( step_box ).find('.button.update.hidden' ).removeClass('hidden');
		} );

		CoursePress.Events.on('editor:keyup', function( e ) {
			var step_box = $( e.container ).parents('.step-content')[0];
			$( step_box ).find('.button.update.hidden' ).removeClass('hidden');
		} );

		// NEXT BUTTON
		$( '.step-content .button.step.prev, .step-content .button.step.next, .step-content .button.step.update, .step-content .button.step.finish' ).on( 'click', function( e ) {

			var target = jQuery( e.currentTarget );
			var step;
			var action_type;

			// Get the right step
			step = target.hasClass( 'step-1' ) ? 1 : null;
			step = target.hasClass( 'step-2' ) ? 2 : step;
			step = target.hasClass( 'step-3' ) ? 3 : step;
			step = target.hasClass( 'step-4' ) ? 4 : step;
			step = target.hasClass( 'step-5' ) ? 5 : step;
			step = target.hasClass( 'step-6' ) ? 6 : step;

			// Get the type
			action_type = target.hasClass( 'prev' ) ? 'prev' : null;
			action_type = target.hasClass( 'next' ) ? 'next' : action_type;
			action_type = target.hasClass( 'update' ) ? 'update' : action_type;
			action_type = target.hasClass( 'finish' ) ? 'finish' : action_type;

			if ( null !== step ) {
				$( '.step-title.step-' + step ).find( '.status' ).removeClass( 'saved' );
				$( '.step-title.step-' + step ).find( '.status' ).removeClass( 'save-error' );
				$( '.step-title.step-' + step ).find( '.status' ).removeClass( 'save-attention' );
				$( '.step-title.step-' + step ).find( '.status' ).addClass( 'save-process' );
				CoursePress.Course.get_step( step, action_type );
				CoursePress.Course.save();
			}
		} );

		// BROWSE MEDIA BUTTONS
		$( '.button.browse-media-field' ).browse_media_field();

		// Handle Course Structure Checkboxes
		$( '.step-content .course-structure input[type="checkbox"]' ).on( 'click', function( e ) {
			var checkbox = e.currentTarget;
			var handled = false;
			var name = $( checkbox ).attr( 'name' );
			var type, parent_class, parent_id, parent_selector, page_selector, checked, pages;

			// Units
			if ( name.match( /meta_structure_.*_units.*/g ) ) {
				type = name.match( /meta_structure_visible_units.*/g ) ? 'visible' : 'preview';
				parent_class = $( $( '[name="' + name + '"]' ).parents( 'tr[class*="treegrid-"]' )[ 0 ] ).attr( 'class' ).match( /treegrid-\d{1,10}/g )[ 0 ].trim();
				parent_id = parent_class.split( '-' ).pop();
				parent_selector = '.step-content .course-structure .treegrid-parent-' + parent_id;
				page_selector = parent_selector + ' [name*="meta_structure_' + type + '_pages"]';
				checked = $( checkbox )[ 0 ].checked;

				pages = $( page_selector );

				$.each( pages, function( index, page ) {

					$( page ).prop( 'checked', checked );

					parent_class = $( $( page ).parents( 'tr[class*="treegrid-"]' )[ 0 ] ).attr( 'class' ).match( /treegrid-\d{1,10}/g )[ 0 ].trim();
					parent_id = parent_class.split( '-' ).pop();
					parent_selector = '.step-content .course-structure .treegrid-parent-' + parent_id;
					var module_selector = parent_selector + ' [name*="meta_structure_' + type + '_modules"]';

					$( module_selector ).prop( 'checked', checked );

				} );

				handled = true;
			}

			// Pages
			if ( ! handled && name.match( /meta_structure_.*_pages.*/g ) ) {
				type = name.match( /meta_structure_visible_pages.*/g ) ? 'visible' : 'preview';
				parent_class = $( $( '[name="' + name + '"]' ).parents( 'tr[class*="treegrid-"]' )[ 0 ] ).attr( 'class' ).match( /treegrid-\d{1,10}/g )[ 0 ].trim();
				parent_id = parent_class.split( '-' ).pop();
				parent_selector = '.step-content .course-structure .treegrid-parent-' + parent_id;

				checked = $( checkbox )[ 0 ].checked;
				var module_selector = parent_selector + ' [name*="meta_structure_' + type + '_modules"]';

				$( module_selector ).prop( 'checked', checked );
			}

			// Update the toggles
			course_structure_update();
		} );

		// ADD INSTRUCTOR.
		$( '.button.instructor-assign' ).on( 'click', function() {
			var instructor_id = parseInt( $( $( 'select[name="instructors"]' )[ 0 ] ).val() );
			var instructor_name = $( $( 'select[name="instructors"]' )[ 0 ] )[ 0 ].textContent;

			CoursePress.Course.set( 'action', 'add_instructor' );

			var data = {
				instructor_id: instructor_id,
				course_id: _coursepress.course_id,
				instructor_name: instructor_name,
				nonce: get_setup_nonce()
			};

			CoursePress.Course.set( 'data', data );
			CoursePress.Course.save();
		} );

		// REMOVE INSTRUCTOR
		bind_remove_button( '.instructor-avatar-holder .instructor-remove a' );
		bind_remove_button( '.instructor-avatar-holder .invite-remove a', true );

		// INSTRUCTOR INVITATIONS
		// Submit Invite on 'Return/Enter'
		$( '.instructor-invite input' ).keypress( function( event ) {
			if ( event.which === 13 ) {
				switch ( $( this ).attr( 'name' ) ) {
					case 'invite_instructor_first_name':
						$( '[name=invite_instructor_last_name]' ).trigger( 'focus' );
						break;

					case 'invite_instructor_last_name':
						$( '[name=invite_instructor_email]' ).trigger( 'focus' );
						break;

					case 'invite_instructor_email':
					case 'invite_instructor_trigger':
						$( '#invite-instructor-trigger' ).trigger( 'click' );
						$( '[name=invite_instructor_first_name]' ).trigger( 'focus' );
						break;
				}
				event.preventDefault();
			}
		} );

		$( '#invite-instructor-trigger' ).on( 'click', function() {
			// Really basic validation
			var email = $( '[name=invite_instructor_email]' ).val();
			var first_name = $( '[name=invite_instructor_first_name]' ).val();
			var last_name = $( '[name=invite_instructor_last_name]' ).val();
			var email_valid = email.match( _coursepress.email_validation_pattern ) !== null;

			if ( email_valid ) {
				CoursePress.Course.set( 'action', 'invite_instructor' );
				var data = {
					first_name: first_name,
					last_name: last_name,
					email: email,
					course_id: _coursepress.course_id,
					nonce: get_setup_nonce()
				};
				CoursePress.Course.set( 'data', data );
				CoursePress.Course.save();
			} else {
				// DEBUG code. remove it.
				window.console.log( 'DO SOMETHING TO THE UI!' );
			}
		} );

		$( '[name="meta_enrollment_type"]' ).on( 'change', function() {
			var options = $( this ).val();
			$( '.step-content.step-6 .enrollment-type-options' ).addClass( 'hidden' );
			$( '.step-content.step-6 .enrollment-type-options.' + options ).removeClass( 'hidden' );
		} );

		// "This is a paid course" checkbox
		$( '[name="meta_payment_paid_course"]' ).on( 'change', function() {
			if ( this.checked ) {
				$( this ).parents( '.step-content.step-6' ).find( '.payment-message' ).removeClass( 'hidden' );
				$( this ).parents( '.step-content.step-6' ).find( '.is_paid_toggle' ).removeClass( 'hidden' );
			} else {
				$( this ).parents( '.step-content.step-6' ).find( '.payment-message' ).addClass( 'hidden' );
				$( this ).parents( '.step-content.step-6' ).find( '.is_paid_toggle' ).addClass( 'hidden' );
			}
		} );

		$( '[name="publish-course-toggle"]' ).on( 'change', function( e, state ) {
			var nonce = $( this ).attr( 'data-nonce' );
			var status = 'off' === state ? 'draft' : 'publish';
			CoursePress.Course.set( 'action', 'toggle_course_status' );
			var data = {
				nonce: nonce,
				status: status,
				state: state,
				course_id: _coursepress.course_id
			};

			CoursePress.Course.set( 'data', data );
			CoursePress.Course.save();

		} );

		// Enroll New Student "Add Student" button.
		$( '.add-new-student-button' ).on( 'click', function( e ) {
			e.stopImmediatePropagation();
			e.preventDefault();

			var nonce = $( this ).attr( 'data-nonce' );

			CoursePress.Course.set( 'action', 'enroll_student' );
			var data = {
				nonce: nonce,
				course_id: _coursepress.course_id,
				student_id: $( '#student-add' ).val()
			};

			CoursePress.Course.set( 'data', data );
			CoursePress.Course.save();
		} );

		// Delete/Withdraw Student.
		$( 'a.withdraw-student' ).on( 'click', function() {
			if ( window.confirm( _coursepress.student_delete_confirm ) ) {
				CoursePress.Course.set( 'action', 'withdraw_student' );
				var data = {
					student_id: $( this ).attr( 'data-id' ),
					course_id: _coursepress.course_id,
					nonce: $( this ).attr( 'data-nonce' )
				};
				CoursePress.Course.set( 'data', data );
				CoursePress.Course.save();
				CoursePress.Course.on( 'coursepress:withdraw_student_success', function(){
					window.location = window.self.location;
				});
				return false;
			}
		} );

		// Delete/Withdraw ALL Students.
		$( 'a.withdraw-all-students' ).on( 'click', function() {
			if ( window.confirm( _coursepress.student_delete_all_confirm ) ) {
				CoursePress.Course.set( 'action', 'withdraw_all_students' );
				var data = {
					course_id: _coursepress.course_id,
					nonce: $( this ).attr( 'data-nonce' )
				};
				CoursePress.Course.set( 'data', data );
				CoursePress.Course.save();
				CoursePress.Course.on( 'coursepress:withdraw_all_students_success', function(){
					window.location = window.self.location;
				});
				return false;
			}
		} );

		// INSTRUCTOR INVITATIONS
		// Submit Invite on 'Return/Enter'
		$( '.coursepress_course_invite_student_wrapper input' ).keypress( function( ev ) {
			if ( ev.which === 13 ) {
				switch ( $( this ).attr( 'name' ) ) {
					case 'invite-firstname':
						$( '[name=invite-lastname]' ).trigger( 'focus' );
						break;

					case 'invite-lastname':
						$( '[name=invite-email]' ).trigger( 'focus' );
						break;

					case 'invite-email':
					case '.coursepress_course_invite_student_wrapper .invite-submit':
						$( '.coursepress_course_invite_student_wrapper .invite-submit' ).trigger( 'click' );
						$( '[name=invite-firstname]' ).trigger( 'focus' );
						break;
				}
				ev.preventDefault();
			}
		} );

		$( '.coursepress_course_invite_student_wrapper .invite-submit' ).on( 'click', function() {
			// Really basic validation
			var email = $( '[name=invite-email]' ).val();
			var first_name = $( '[name=invite-firstname]' ).val();
			var last_name = $( '[name=invite-lastname]' ).val();

			var email_valid = email.match( _coursepress.email_validation_pattern ) !== null;

			if ( email_valid ) {
				$( '.coursepress_course_invite_student_wrapper .invite-submit' ).prepend( '<i class="fa fa-spinner fa-spin invite-progress"></i> ' );

				CoursePress.Course.set( 'action', 'invite_student' );
				var data = {
					first_name: first_name,
					last_name: last_name,
					email: email,
					course_id: _coursepress.course_id,
					nonce: $( this ).attr( 'data-nonce' )
				};
				CoursePress.Course.set( 'data', data );
				CoursePress.Course.save();
			}

			$( '[name=invite-email]' ).val( '' );
			$( '[name=invite-firstname]' ).val( '' );
			$( '[name=invite-lastname]' ).val( '' );

		} );
	}

	/**
	 * Used to bind instructor boxes. Separated to be invoked on individual buttons.
	 * @param selector
	 */
	function bind_remove_button( selector, remove_pending ) {
		if ( undefined === remove_pending ) {
			remove_pending = false;
		}

		$( selector ).on( 'click', function( e ) {
			var target = e.currentTarget;
			var step_box, data;

			if ( ! remove_pending ) {
				var instructor_id = parseInt( $( $( target ).parents( '.instructor-avatar-holder' )[ 0 ] ).attr( 'id' ).match( /instructor_holder_\d{1,10}/g )[ 0 ].trim().split( '_' ).pop() );

				// Confirm before deleting
				if ( window.confirm( _coursepress.instructor_delete_confirm ) ) {

					step_box = $( target ).parents('.step-content')[0];
					$( step_box ).find('.button.update.hidden' ).removeClass('hidden');

					CoursePress.Course.set( 'action', 'delete_instructor' );
					data = {
						instructor_id: instructor_id,
						course_id: _coursepress.course_id,
						nonce: get_setup_nonce()
					};
					CoursePress.Course.set( 'data', data );
					CoursePress.Course.save();
				}
			} else {
				var invite_code = $( $( target ).parents( '.instructor-avatar-holder' )[ 0 ] ).attr( 'id' ).replace( 'instructor_holder_', '' );

				// Confirm before deleting
				if ( window.confirm( _coursepress.instructor_delete_invite_confirm ) ) {

					step_box = $( target ).parents('.step-content')[0];
					$( step_box ).find('.button.update.hidden' ).removeClass('hidden');

					CoursePress.Course.set( 'action', 'delete_instructor_invite' );
					data = {
						invite_code: invite_code,
						course_id: _coursepress.course_id,
						nonce: get_setup_nonce()
					};
					CoursePress.Course.set( 'data', data );
					CoursePress.Course.save();
				}
			}
		} );
	}

	function update_nonce( data ) {
		if ( data.nonce ) {
			$( '#course-setup-steps' ).attr( 'data-nonce', data.nonce );
		}
	}

	function get_setup_nonce() {
		return $( '#course-setup-steps' ).attr( 'data-nonce' );
	}

	function bind_coursepress_events() {

		/**
		 * COURSE UPDATE
		 */
		CoursePress.Course.on( 'coursepress:update_course_success', function( data ) {
			$( '.step-title.step-' + data.last_step ).find( '.status' ).addClass( 'saved' );
			$( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-error' );
			$( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-attention' );
			$( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-process' );
			if ( data.next_step !== data.last_step ) {
				$( '.step-title.step-' + data.next_step ).click();
			}

			var buttons = $( '.step-content.step-' + data.last_step ).find( '.course-step-buttons' )[0];

			$( '#step-done-message' ).remove();
			$( buttons ).append( '<span id="step-done-message">&nbsp;<i class="fa fa-check"></i></span>' );
			// Popup Message
			$( '#step-done-message' ).show( function() {
				$( this ).fadeOut( 1000 );
			} );

			$( buttons ).find( '.update' ).addClass('hidden');
			$( '[name="course_id"]' ).val( data.course_id );
			_coursepress.course_id = data.course_id;

			update_nonce( data );

			if ( data.redirect ) {
				var dest = location.href.replace( '&tab=setup', '' );

				if ( !/\&id/.test( dest ) ) {
					dest += '&id=' + data.course_id;
				}
				if ( !/\&action=edit/.test( dest ) ) {
					dest += '&action=edit';
				}

				location.href = dest + '&tab=units';
			}
		} );

		CoursePress.Course.on( 'coursepress:update_course_error', function( data ) {
			$( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'saved' );
			$( '.step-title.step-' + data.last_step ).find( '.status' ).addClass( 'save-error' );
			$( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-attention' );
			$( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-process' );

			// DEBUG code. remove it.
			window.console.log( data );
		} );

		/**
		 * INSTRUCTOR ACTIONS
		 */
		CoursePress.Course.on( 'coursepress:add_instructor_success', function( data ) {

			var remove_buttons = true; // permission required
			var content = '';
			// DEBUG code. remove it.
			window.console.log( data );

			var avatar = _coursepress.instructor_avatars[ 'default' ];

			if ( _coursepress.instructor_role_defined ) {
				avatar = _coursepress.instructor_avatars[ data.instructor_id ];
			}

			if ( remove_buttons ) {
				content += '<div class="instructor-avatar-holder" id="instructor_holder_' + data.instructor_id + '"><div class="instructor-status"></div><div class="instructor-remove"><a><span class="dashicons dashicons-dismiss"></span></a></div>' + avatar + '<span class="instructor-name">' + data.instructor_name + '</span></div><input type="hidden" id="instructor_' + data.instructor_id + '" name="instructor[]" value="' + data.instructor_id + '" />';
			} else {
				content += '<div class="instructor-avatar-holder" id="instructor_holder_' + data.instructor_id + '"><div class="instructor-status"></div>' + avatar + '<span class="instructor-name">' + data.instructor_name + '</span></div><input type="hidden" id="instructor_' + data.instructor_id + '" name="instructor[]" value="' + data.instructor_id + '" />';
			}

			if ( $( '.instructor-avatar-holder.empty' ).length > 0 ) {
				$( '.instructor-avatar-holder.empty' ).detach();
			}

			if ( $( '#instructor_holder_' + data.instructor_id ).length === 0 ) {
				$( '#instructors-info' ).append( content );
				bind_remove_button( '#instructor_holder_' + data.instructor_id + ' .instructor-remove a' );
			}

			update_nonce( data );
		} );

		CoursePress.Course.on( 'coursepress:delete_instructor_success', function( data ) {

			var empty_holder = '<div class="instructor-avatar-holder empty"><span class="instructor-name">' + _coursepress.instructor_empty_message + '</span></div>';

			// Remove Instructor Avatar
			$( '#instructor_holder_' + data.instructor_id ).detach();

			if ( $( '.instructor-avatar-holder' ).length === 0 ) {
				$( '#instructors-info' ).append( empty_holder );
			}

			update_nonce( data );
		} );

		CoursePress.Course.on( 'coursepress:invite_instructor_success', function( data ) {
			var email = data.data.email;
			var invite_code = data.invite_code;
			var img = CoursePress.utility.get_gravatar_image( email, 80 );

			var remove_buttons = true; // permission required
			var content = '';
			var message = '';

			if ( remove_buttons ) {
				content += '<div class="instructor-avatar-holder pending-invite" id="instructor_holder_' + invite_code + '"><div class="instructor-status">' + _coursepress.instructor_pednding_status + '</div><div class="invite-remove"><a><span class="dashicons dashicons-dismiss"></span></a></div>' + img + '<span class="instructor-name">' + data.data.first_name + ' ' + data.data.last_name + '</span></div>';
			} else {
				content += '<div class="instructor-avatar-holder pending-invite" id="instructor_holder_' + invite_code + '"><div class="instructor-status">' + _coursepress.instructor_pednding_status + '</div>' + img + '<span class="instructor-name">' + data.data.first_name + ' ' + data.data.last_name + '</span></div>';
			}

			if ( $( '.instructor-avatar-holder.empty' ).length > 0 ) {
				$( '.instructor-avatar-holder.empty' ).detach();
			}

			if ( $( '#instructor_holder_' + invite_code ).length === 0 ) {
				$( '#instructors-info' ).append( content );
				bind_remove_button( '#instructor_holder_' + invite_code + ' .invite-remove a', true );

				message = ' <span class="message"><span class="dashicons dashicons-yes"></span> ' + data.message[ 'sent' ] + '</span>';

			} else {
				message = ' <span class="message"><span class="dashicons dashicons-yes"></span> ' + data.message[ 'exists' ] + '</span>';
			}

			$( '.instructor-invite .submit-message' ).append( message );
			$( '.instructor-invite .submit-message .message' ).fadeOut( 3000 );

			update_nonce( data );
		} );

		CoursePress.Course.on( 'coursepress:invite_instructor_error', function( data ) {
			var message = ' <span class="message"><span class="dashicons dashicons-yes"></span> ' + data.message[ 'send_error' ] + '</span>';

			$( '.instructor-invite .submit-message' ).append( message );
			$( '.instructor-invite .submit-message .message' ).fadeOut( 3000 );

			update_nonce( data );
		} );

		CoursePress.Course.on( 'coursepress:delete_instructor_invite_success', function( data ) {
			$( '#instructor_holder_' + data.invite_code ).detach();
			update_nonce( data );
		} );

		CoursePress.Course.on( 'coursepress:toggle_course_status_success', function( data ) {
			$( '[name="publish-course-toggle"]' ).attr( 'data-nonce', data.nonce );
		} );

		CoursePress.Course.on( 'coursepress:toggle_course_status_error', function() {
			// Toggle back.
			var toggle = $( '[name="publish-course-toggle"]' );
			if ( $( toggle ).hasClass( 'on' ) ) {
				$( toggle ).removeClass( 'on' ).addClass( 'off' );
			} else if ( $( toggle ).hasClass( 'off' ) ) {
				$( toggle ).removeClass( 'off' ).addClass( 'on' );
			}
		} );

		CoursePress.Course.on( 'coursepress:enroll_student_success', function() {
			location.reload();
		} );

		CoursePress.Course.on( 'coursepress:withdraw_student_success', function() {
			location.reload();
		} );

		CoursePress.Course.on( 'coursepress:withdraw_all_students_success', function() {
			location.reload();
		} );

		CoursePress.Course.on( 'coursepress:invite_student_success', function( data ) {
			$( '.invite-progress' ).detach();
			$( '.coursepress_course_invite_student_wrapper .invite-submit' ).attr( 'data-nonce', data.nonce );
		} );

		CoursePress.Course.on( 'coursepress:invite_student_error', function( ) {
			$( '.invite-progress' ).detach();
		} );


	}

	function bind_assessment_events() {
		var ungraded_selector = '.coursepress_settings_wrapper.assessment .ungraded-elements [type="checkbox"]';
		var submitted_selector = '.coursepress_settings_wrapper.assessment .submitted-elements [type="checkbox"]';

		function update_modules() {
			var ungraded_checked = $( ungraded_selector ).is( ':checked' );
			var submitted_checked = $( submitted_selector ).is( ':checked' );

			$( 'tbody tr' ).css( 'display', 'none' );

			var submitted_class = '';
			if ( submitted_checked ) {
				$( 'tr.treegrid-expanded ~ tr.submitted' ).css( 'display', 'table-row' );
				$( 'tr.treegrid-expanded ~ tr.not-submitted' ).css( 'display', 'none' );
				submitted_class = '.submitted';
			} else {
				$( 'tr.treegrid-expanded ~ tr.submitted' ).css( 'display', 'table-row' );
				$( 'tr.treegrid-expanded ~ tr.not-submitted' ).css( 'display', 'table-row' );
			}


			if ( ungraded_checked ) {
				$( 'tr.treegrid-expanded ~ tr.ungraded' + submitted_class ).css( 'display', 'table-row' );
				$( 'tr.treegrid-expanded ~ tr.graded' + submitted_class ).css( 'display', 'none' );
			} else {
				$( 'tr.treegrid-expanded ~ tr.ungraded' + submitted_class ).css( 'display', 'table-row' );
				$( 'tr.treegrid-expanded ~ tr.graded' + submitted_class ).css( 'display', 'table-row' );
			}

			$( 'tbody tr.student-name' ).css( 'display', 'table-row' );
		}

		$( ungraded_selector ).on( 'click', function() {
			update_modules( this );
		} );

		$( submitted_selector ).on( 'click', function() {
			update_modules( this );
		} );

		$( '.coursepress_settings_wrapper.assessment table' ).treegrid().on( 'expand', function() {
			update_modules( this );
		} );

		$( '.coursepress_settings_wrapper.assessment table .instructor-feedback' ).link_popup( { link_text: '<span class="dashicons dashicons-admin-comments"></span>' } );

		$( '.coursepress_settings_wrapper.assessment [name="course-list"]' ).on( 'change', function() {
			var course_id = $( this ).val();
			location.href = _coursepress.assessment_grid_url + '&course_id=' + course_id;
		} );

		$( '.coursepress_settings_wrapper.assessment .collapse-all-students' ).on( 'click', function() {
			$( '.coursepress_settings_wrapper.assessment table' ).treegrid( 'collapseAll' );
		} );

		$( '.coursepress_settings_wrapper.assessment .expand-all-students' ).on( 'click', function() {
			$( '.coursepress_settings_wrapper.assessment table' ).treegrid( 'expandAll' );
		} );
	}

	function bind_reports_events() {
		$( '.coursepress_settings_wrapper.reports .help-tooltip' ).link_popup( {
			link_text: '<span class="dashicons dashicons-editor-help"></span>',
			offset_x: 20
		} );

		$( '.coursepress_settings_wrapper.reports [name="course-list"]' ).on( 'change', function() {
			var course_id = $( this ).val();
			location.href = _coursepress.assessment_report_url + '&course_id=' + course_id;
		} );

		$( '.coursepress_settings_wrapper.reports .column-report .pdf' ).on( 'click', function() {
			if ( false === $(this).data('click') ) {
				return false;
			}
			var form = $( this ).parents( 'form' )[ 0 ];
			var student = $( this ).attr( 'data-student' );

			$( form ).find( '[name=students]' ).val( student );
			form.submit();
		} );
	}

	function bind_notification_events() {
		$( '[name*="publish-notification-toggle"]' ).on( 'change', function( ev, state ) {
			CoursePress.Post.prepare( 'update_notification', 'notification:' );
			CoursePress.Post.set( 'action', 'toggle' );

			var nonce = $( this ).attr( 'data-nonce' );
			var status = 'off' === state ? 'draft' : 'publish';
			var notification_id = parseInt( $( this ).attr( 'id' ).replace( 'publish-notification-toggle-', '' ) );

			var data = {
				nonce: nonce,
				status: status,
				state: state,
				notification_id: notification_id
			};

			CoursePress.Post.set( 'data', data );
			CoursePress.Post.save();

		} );

		$( '.delete-notification-link' ).on( 'click', function( ev ) {
			ev.stopImmediatePropagation();
			ev.preventDefault();

			if ( window.confirm( _coursepress.notification_delete ) ) {
				CoursePress.Post.prepare( 'update_notification', 'notification:' );
				CoursePress.Post.set( 'action', 'delete' );

				var data = {
					nonce: $( this ).attr( 'data-nonce' ),
					notification_id: $( this ).attr( 'data-id' )
				};

				CoursePress.Post.set( 'data', data );
				CoursePress.Post.save();
			}
		} );

		CoursePress.Post.on( 'coursepress:notification:delete_success', function() {
			location.reload();
		} );

		// Could add these events, but won't need it
		//CoursePress.Post.on( 'coursepress:notification:delete_error', function( data ) {} );
		//CoursePress.Post.on( 'coursepress:notification:toggle_success', function( data ) {} );
		//CoursePress.Post.on( 'coursepress:notification:toggle_error', function( data ) {} );

		$( '.coursepress_communications_wrapper.notifications [id*="doaction"]' ).on( 'click', function() {
			var action = $( this ).siblings( '[id*="bulk-action-selector"]' ).val();

			if ( 'delete' === action && !window.confirm( _coursepress.notification_bulk_delete ) ) {
				return false;
			}

			if ( undefined !== action && -1 !== action ) {
				var ids = [];

				$.each( $( '[name*="bulk-actions"]' ), function( index, item ) {
					if ( $( item ).is( ':checked' ) ) {
						ids.push( $( item ).val() );
					}
				} );

				CoursePress.Post.prepare( 'update_notification', 'notification:' );
				CoursePress.Post.set( 'action', 'bulk_' + action );

				var data = {
					nonce: $( '.nonce-holder' ).attr( 'data-nonce' ),
					ids: ids
				};

				CoursePress.Post.set( 'data', data );
				CoursePress.Post.save();
			}

		} );

		CoursePress.Post.on( 'coursepress:notification:bulk_publish_success', function() {
			location.reload();
		} );

		CoursePress.Post.on( 'coursepress:notification:bulk_unpublish_success', function() {
			location.reload();
		} );

		CoursePress.Post.on( 'coursepress:notification:bulk_delete_success', function() {
			location.reload();
		} );
	}

	function bind_discussion_events() {
		$( '[name*="publish-discussion-toggle"]' ).on( 'change', function( e, state ) {
			CoursePress.Post.prepare( 'update_discussion', 'discussion:' );
			CoursePress.Post.set( 'action', 'toggle' );

			var nonce = $( this ).attr( 'data-nonce' );
			var status = 'off' === state ? 'draft' : 'publish';
			var discussion_id = parseInt( $( this ).attr( 'id' ).replace( 'publish-discussion-toggle-', '' ) );

			var data = {
				nonce: nonce,
				status: status,
				state: state,
				discussion_id: discussion_id
			};

			CoursePress.Post.set( 'data', data );
			CoursePress.Post.save();
		} );

		$( '.delete-discussion-link' ).on( 'click', function( e ) {
			e.stopImmediatePropagation();
			e.preventDefault();
			if ( window.confirm( _coursepress.discussion_delete ) ) {

				CoursePress.Post.prepare( 'update_discussion', 'discussion:' );
				CoursePress.Post.set( 'action', 'delete' );

				var data = {
					nonce: $( this ).attr( 'data-nonce' ),
					discussion_id: $( this ).attr( 'data-id' )
				};

				CoursePress.Post.set( 'data', data );
				CoursePress.Post.save();
			}
		} );

		CoursePress.Post.on( 'coursepress:discussion:delete_success', function() {
			location.reload();
		} );

		// Could add these events, but won't need it
		//CoursePress.Post.on( 'coursepress:discussion:delete_error', function( data ) {} );
		//CoursePress.Post.on( 'coursepress:discussion:toggle_success', function( data ) {} );
		//CoursePress.Post.on( 'coursepress:discussion:toggle_error', function( data ) {} );

		$( '.coursepress_communications_wrapper.discussions [id*="doaction"]' ).on( 'click', function() {
			var action = $( this ).siblings( '[id*="bulk-action-selector"]' ).val();

			if ( 'delete' === action && ! window.confirm( _coursepress.discussion_bulk_delete ) ) {
				return false;
			}

			if ( undefined !== action && -1 !== action ) {
				var ids = [];

				$.each( $( '[name*="bulk-actions"]' ), function( index, item ) {
					if ( $( item ).is( ':checked' ) ) {
						ids.push( $( item ).val() );
					}
				} );

				CoursePress.Post.prepare( 'update_discussion', 'discussion:' );
				CoursePress.Post.set( 'action', 'bulk_' + action );

				var data = {
					nonce: $( '.nonce-holder' ).attr( 'data-nonce' ),
					ids: ids
				};

				CoursePress.Post.set( 'data', data );
				CoursePress.Post.save();
			}
		} );

		CoursePress.Post.on( 'coursepress:discussion:bulk_publish_success', function() {
			location.reload();
		} );

		CoursePress.Post.on( 'coursepress:discussion:bulk_unpublish_success', function() {
			location.reload();
		} );

		CoursePress.Post.on( 'coursepress:discussion:bulk_delete_success', function() {
			location.reload();
		} );

		// Edit Page
		$( '.coursepress_communications_wrapper.discussions select#course_id' ).on( 'change', function() {
			var course_id = $( this ).val();

			$.each( $( '.coursepress_communications_wrapper.discussions select#unit_id' ).find( 'option' ), function( index, item ) {
				if ( 'course' !== $( item ).val() ) {
					$( item ).detach();
				}
			} );

			CoursePress.Post.prepare( 'update_discussion', 'discussion:' );
			CoursePress.Post.set( 'action', 'unit_items' );

			var data = {
				course_id: course_id
			};

			CoursePress.Post.set( 'data', data );
			CoursePress.Post.save();
		} );

		CoursePress.Post.on( 'coursepress:discussion:unit_items_success', function( data ) {
			if ( data.items.length > 0 ) {
				$.each( data.items, function( index, item ) {
					$( '.coursepress_communications_wrapper.discussions select#unit_id' ).append( '<option value="' + item.key + '">' + item.value + '</option>' );
				} );
			}
		} );
	}

	// Try to keep only one of these blocks and use functions/objects instead
	$( document ).ready( function() {
		setup_UI();
		bind_buttons();
		bind_coursepress_events();

		bind_assessment_events();
		bind_reports_events();

		bind_notification_events();
		bind_discussion_events();

		// Get setup marker and advance accordion.
		var setup_marker = jQuery( '#course-setup-steps .step-title .status.setup_marker' );
		setup_marker.click();
	} );
})( jQuery );
