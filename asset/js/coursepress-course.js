/*! CoursePress - v2.1.5-beta.1
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2018; * Licensed GPLv2+ */
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

	CoursePress.Course.hasError = false;
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

			meta_items = $( '.cp-box-content.step-1 [name^="meta_"]' ).serializeArray();
			meta_items = CoursePress.Course.fix_step_checkboxes( meta_items, step );
			CoursePress.Course.add_array_to_data( data, meta_items );
		}

		// Step 2 Data
		if ( 2 <= step ) {
			data.course_description = tinyMCE && tinyMCE.get( 'courseDescription' ) ? tinyMCE.get( 'courseDescription' ).getContent() : $( '[name="course_description"]' ).val();

			meta_items = $( '.cp-box-content.step-2 [name^="meta_"]' ).serializeArray();
			meta_items = CoursePress.Course.fix_step_checkboxes( meta_items, step, '0' );
			CoursePress.Course.add_array_to_data( data, meta_items );
		}

		// Step 3 Data
		if ( 3 <= step ) {
			meta_items = $( '.cp-box-content.step-3 [name^="meta_"]' ).serializeArray();
			meta_items = CoursePress.Course.fix_step_checkboxes( meta_items, step, '0' );
			CoursePress.Course.add_array_to_data( data, meta_items );
		}

		// Step 4 Data
		if ( 4 <= step ) {
			meta_items = $( '.cp-box-content.step-4 [name^="meta_"]' ).serializeArray();
			meta_items = CoursePress.Course.fix_step_checkboxes( meta_items, step, '0' );
			CoursePress.Course.add_array_to_data( data, meta_items );
		}

		// Step 1 Data
		if ( 5 <= step ) {
			meta_items = $( '.cp-box-content.step-5 [name^="meta_"]' ).serializeArray();
			meta_items = CoursePress.Course.fix_step_checkboxes( meta_items, step, '0' );
			CoursePress.Course.add_array_to_data( data, meta_items );
		}

		// Step 1 Data
		if ( 6 <= step ) {
			meta_items = $( '.cp-box-content.step-6 [name^="meta_"]' ).serializeArray();
			meta_items = CoursePress.Course.fix_step_checkboxes( meta_items, step, '0' );
			CoursePress.Course.add_array_to_data( data, meta_items );
		}

		// Step 1 Data
		if ( 7 <= step ) {
			course_completion_content = tinyMCE && tinyMCE.get( 'course-completion-editor-content' ) ? tinyMCE.get( 'course-completion-editor-content' ).getContent() : $( '[name="meta_course_completion_content"]' ).val();
			$( '[name="meta_course_completion_content"]' ).val( course_completion_content );
			pre_completion_content = tinyMCE && tinyMCE.get( 'pre-completion-content' ) ? tinyMCE.get( 'pre-completion-content' ).getContent() : $( '[name="meta_pre_completion_content"]' ).val();
			$( '[name="meta_pre_completion_content"]' ).val( pre_completion_content );
			failed_content = tinyMCE && tinyMCE.get( 'course-failed-content' ) ? tinyMCE.get( 'course-failed-content' ).getContent() : $( '[name="meta_course_failed_content"]' ).val();
			$( '[name="meta_course_failed_content"]' ).val( failed_content );

			basic_certificate_layout = tinyMCE && tinyMCE.get( 'basic-certificate-layout' ) ? tinyMCE.get( 'basic-certificate-layout' ).getContent() : $( '[name="meta_basic_certificate_layout"]' ).val();
			$( '[name="meta_basic_certificate_layout"]' ).val( basic_certificate_layout );
			meta_items = $( '.cp-box-content.step-7 [name^="meta_"]' ).serializeArray();

			var basic_cert = $('[name="meta_basic_certificate"]').is(':checked');

			if ( ! basic_cert ) {
				meta_items.push({name: 'meta_basic_certificate', value:false});
			}

			meta_items = CoursePress.Course.fix_step_checkboxes( meta_items, step, '0' );
			CoursePress.Course.add_array_to_data( data, meta_items );
		}

		// Save course category
		var checklist = $( '#course_categorychecklist input:checked' ), meta_category = [];

		if ( 0 < checklist.length ) {
			checklist.each( function() {
				var input = $(this);
				meta_category.push( {name: 'meta_course_category', value: input.val() } );
			});
			CoursePress.Course.add_array_to_data( data, meta_category );
		}

		var next_step = step;

		if ( 'next' === action_type ) {
			data.meta_setup_marker = step;
			next_step = next_step !== 7 ? next_step + 1 : next_step;
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
		/**
		 * post status
		 */
		data.post_status = $('#post_status').val();
		/**
		 * nonce
		 */
		data.nonce = get_setup_nonce();
		/**
		 * set
		 */
		CoursePress.Course.set( 'data', data );
		CoursePress.Course.set( 'action', 'update_course' );
		CoursePress.Course.set( 'next_step', next_step );
	};
	
	CoursePress.Course.show_message = function( message, notice_class ) {
		$( ".cp-box-content .course-step-buttons .notice" ).detach();
		$( ".cp-box-content.ui-accordion-content-active .course-step-buttons" ).prepend( '<div class="notice notice-' + notice_class + '"><p>'+message+'</p></div>' );
		if ( "success" === notice_class ) {
			setTimeout(function(){ $( ".cp-box-content .course-step-buttons .notice" ).fadeOut(); }, 3000);
		}
	}

	function course_structure_update () {
		$.each( $( '.cp-box-content .course-structure tr.unit' ), function( uidx, unit ) {

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

		/**
		 * course title
		 */
		if ( _coursepress.course_title ) {
			$('.coursepress_settings_wrapper h1').append( ': <small>'+_coursepress.course_title+'</small>');
			$('#course_name').on('change', function() {
				$('.coursepress_settings_wrapper h1 small').html($(this).val());
			});
		}

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
		var treegridtable = $( 'table.course-structure-tree' ),
			total_rows = $( 'tr', treegridtable );
		if ( total_rows < 100 ) {
			// Treegrid is causing js error when there are too many rows
			$( 'table.course-structure-tree' ).treegrid( { initialState: 'expanded' } );
		}

		// ----- DATE PICKERS -----
		if ( "function" === typeof( $(document).datetimepicker ) ) {
			var datetime = $( '.dateinput.timeinput' ).datetimepicker( {
				dateFormat: 'yy-mm-dd',
				timeFormat: 'HH:mm',
				showButtonPanel: false,
				timeInput: true,
				controlType: 'select',
				oneLine: true,
				autoclose: true,
				onSelect: function() {
					datetime.datepicker('hide');
				}
			} );
			$( '.dateinput' ).not( '.dateinput.timeinput' ).datepicker( {
				dateFormat: 'yy-mm-dd',
				autoclose: true
					//firstDay: coursepress.start_of_week
			} );
		}

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
		// ----- END DATE PICKERS -----

		// Spinners
		$( '.spinners' ).spinner();

		$( '[name="meta_class_limited"]' ).change( function() {
			if ( this.checked ) {
				$( this ).parents( '.class-size' ).find( '.num-students' ).removeClass( 'disabled' );
				$( this ).parents( '.class-size' ).find( '.num-students input' ).removeAttr( 'disabled' );
			} else {
				$( this ).parents( '.class-size' ).find( '.num-students' ).addClass( 'disabled' );
				$( this ).parents( '.class-size' ).find( '.num-students input' ).attr( 'disabled', 'disabled' );
			}
		} );

		// ------ COURSEPRESS UI TOGGLES -----
		$( '.coursepress-ui-toggle-switch' ).coursepress_ui_toggle();
	}

	function bind_buttons() {
		// Show update button...
		$( '#course-setup-steps input' ).on( 'keyup change', function() {
			var step_box = $( this ).parents('.cp-box-content')[0];
			$( step_box ).find('.button.update.hidden' ).removeClass('hidden');
		} );

		$( '#course-setup-steps select' ).on( 'change', function() {
			var step_box = $( this ).parents('.cp-box-content')[0];
			$( step_box ).find('.button.update.hidden' ).removeClass('hidden');
		} );

		CoursePress.Events.on('editor:keyup', function( e ) {
			var step_box = $( e.container ).parents('.cp-box-content')[0];
			$( step_box ).find('.button.update.hidden' ).removeClass('hidden');
		} );

		// NEXT BUTTON
		$( '.cp-box-content .button.step.prev, .cp-box-content .button.step.next, .cp-box-content .button.step.update, .cp-box-content .button.step.finish' ).on( 'click', function( e ) {

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
			step = target.hasClass( 'step-7' ) ? 7 : step;

			// Prevent from updating, moving forward if required fields are empty

			var required_fields = [];

			if ( 1 <= step ) {
				// Required fields for step 1
				required_fields.push( 'course_name', 'course_excerpt' );
			}
			if ( 2 <= step ) {
				required_fields.push( 'course_description' );
			}
			if ( 4 <= step ) {
				required_fields.push( 'meta_course_start_date' );

				// If the course is not open-ended, course end date must not be empty!
				if ( ! $( '[name="meta_course_open_ended"]' ).is( ':checked' ) ) {
					required_fields.push( 'meta_course_end_date' );
				}

				// If enrollment is not open, check the fields!
				if ( ! $( '[name="meta_enrollment_open_ended"]' ).is( ':checked' ) ) {
					required_fields.push( 'meta_enrollment_start_date', 'meta_enrollment_end_date' );
				}
			}
			if ( 7 <= step ) {
				required_fields.push( 'meta_minimum_grade_required', 'meta_pre_completion_title', 'meta_pre_completion_content' );
				required_fields.push( 'meta_course_completion_title', 'meta_course_completion_content', 'meta_course_failed_title', 'meta_course_failed_content' );
			}

			if ( required_fields.length > 0 ) {
				// Check for values
				var found = 0, mce_helper;

				// Get editor type field
				mce_helper = function( editor_id, editor ) {
					var content = tinyMCE && tinyMCE.get( editor_id ) ? tinyMCE.get( editor_id ).getContent() : editor.val();
					if ( '' == content ) {
						content = $('#'+editor_id).val();
					}
					return content;
				};

				_.each( required_fields, function( field_name ) {
					var field = $( '[name="' + field_name + '"]' ),
						val = field.val()
					;

					field.removeClass('error');

					if ( 'course_excerpt' == field_name ) {
						val = mce_helper( 'courseExcerpt', field );
					}
					if ( 'course_description' == field_name ) {
						val = mce_helper( 'courseDescription', field );
					}
					if ( 'meta_pre_completion_content' == field_name ) {
						val = mce_helper( 'pre-completion-content', field );
					}
					if ( 'meta_course_completion_content' == field_name ) {
						val = mce_helper( 'course-completion-editor-content', field );
					}
					if ( 'meta_course_failed_content' == field_name ) {
						val = mce_helper( 'course-failed-content', field );
					}

					if ( ! val || '' == val ) {
						field.addClass('error');
						found += 1;
					}

				});

				if ( found > 0 ) {
					CoursePress.Course.hasError = true;
					// Alert
					// @todo: Make this message info nicer!
					alert( _coursepress.labels.required_fields );

					return false;
				} else {
					CoursePress.Course.hasError = false;
				}
			}

			// Get the type
			action_type = target.hasClass( 'prev' ) ? 'prev' : null;
			action_type = target.hasClass( 'next' ) ? 'next' : action_type;
			action_type = target.hasClass( 'update' ) ? 'update' : action_type;
			action_type = target.hasClass( 'finish' ) ? 'finish' : action_type;

			if ( null !== step ) {
				CoursePress.Course.show_message( _coursepress.unit_builder_form.messages.setup.saving, 'info' );
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

		if ( $.fn.wpColorPicker ) {
			$('.certificate-color-picker').wpColorPicker();
		}

		// Handle Course Structure Checkboxes
		$( '.cp-box-content .course-structure input[type="checkbox"]' ).on( 'change', function( e ) {
			var checkbox = $( e.currentTarget ),
				target_name = checkbox.attr( 'name' ),
				type = null != target_name.match( /visible/ ) ? 'visible' : 'preview',
				base_name = 'meta_structure_' + type,
				treegrid = $( '.course-structure-tree' ),
				is_true = checkbox.is(':checked'),
				the_parent = checkbox.parents( 'tr' ).first(),
				unit_id = the_parent.attr( 'data-unitid' )
			;

			// Pages
			var handle_pages = function( checked ) {
				var pages = treegrid
					.find( 'tr[data-unitid="' + unit_id + '"]' )
					.find( '[name*="' + base_name + '_pages"]' )
				;

				pages.each( function() {
					var page = $(this), is_checked = page.is( ':checked' )
						page_number = page.parents( 'tr[data-pagenumber]' ).first().attr( 'data-pagenumber' )//,
						//old_state = page.data( 'checked' )
					;

					if ( ! checked ) {
						// Remember current state before unchecking
						// page.data( 'checked', is_checked );
						page.attr( 'checked', false );
					} else {
						// Set previous state
						// var old_state = page.data( 'checked' );
						page.attr( 'checked', true );
					}

					handle_modules( checked, page_number );
				});

			};

			// Handle modules
			var handle_modules = function( checked, page_number ) {
				var modules = treegrid
					.find( 'tr[data-unitid="' + unit_id + '"][data-pagenumber="' + page_number + '"]' )
					.find( '[name*="' + base_name + '_modules"]' )
				;

				modules.each( function() {
					var module = $(this),
						is_checked = module.is( ':checked' )
					;

					if ( ! checked ) {
						// Remember previous state
						// module.data( 'checked', is_checked );
						module.attr( 'checked', false );
					} else {
						// Set old state
						// var old_state = module.data( 'checked' );
						module.attr( 'checked', true );
					}
				} );
			}

			// Handle unit type
			if ( target_name.match( /_units/ ) ) {
				handle_pages( is_true );
			}
			// Handle page type
			else if ( target_name.match( /_pages/ ) ) {
				var page_number = checkbox.parents( 'tr' ).first().attr( 'data-pagenumber' ),
					unit_item = treegrid.find( '.unit-' + unit_id ).find( '[name*="' + base_name + '_unit"]' ),
					all_pages = treegrid.find( '.page[data-unitid="' + unit_id + '"]' )
						.find( '[name*="' + base_name + '_pages"]:checked' )
				;
				// Always checked the unit parent
				unit_item.attr( 'checked', all_pages.length > 0 );
				handle_modules( is_true, page_number );
			}
			// Handle module type
			else {
				var page_number = the_parent.attr( 'data-pagenumber' ),
					unit_item = treegrid.find( '.unit-' + unit_id ).find( '[name*="' + base_name + '_unit"]' ),
					page_item = treegrid.find( '.page-' + page_number + '[data-unitid="' + unit_id + '"]' )
						.find( '[name*="' + base_name + '_page"]' ),
					all_modules = treegrid.find( 'tr[data-unitid="' + unit_id + '"][data-pagenumber="' + page_number + '"]' )
						.find( '[name*="' + base_name + '_modules"]:checked' )
				;

				// Always check the unit and parent section
				page_item.attr( 'checked', all_modules.length > 0 );
				var all_pages = treegrid.find( '.page[data-unitid="' + unit_id + '"]' )
						.find( '[name*="' + base_name + '_pages"]:checked' );
				unit_item.attr( 'checked', all_pages.length > 0 );								
			}
		} );

		// ADD INSTRUCTOR.
		$( '.button.instructor-assign' ).on( 'click', function() {
			if ( $(this).hasClass( "disabled" ) ) {
				return false;
			}
			var instructor = $( 'select[name="instructors"]' ),
				instructor_id = parseInt( instructor.val() ),
				instructor_name = instructor.html(),
				is_done = $( '#instructor_holder_' + instructor_id ).length > 0,
				dropdown = $( '.select2-selection__rendered' ),
				container = $( '#instructors-info' )
			;

			if ( is_done ) {
				return; // Bail if instructor already exist
			}
			dropdown.html( _coursepress.labels.user_dropdown_placeholder );
			var div = $( '<div class="instructor-avatar-holder empty" id="instructor_holder_' + instructor_id + '"><span class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></span></div>' ).appendTo( container );

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

		// REMOVE INSTRUCTOR/FACILITATOR
		bind_remove_button( '.avatar-holder .remove a' );

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
			var type = $( '[name=invite_instructor_type]:checked' ).val();
			var email_valid = email.match( _coursepress.email_validation_pattern ) !== null;

			if ( email_valid ) {
				CoursePress.Course.set( 'action', 'invite_instructor' );
				var data = {
					first_name: first_name,
					last_name: last_name,
					who: type,
					email: email,
					course_id: _coursepress.course_id,
					nonce: get_setup_nonce()
				};
				CoursePress.Course.set( 'data', data );
				CoursePress.Course.on( 'coursepress:invite_instructor_success', function() {
					$('[name="invite_instructor_first_name"],[name="invite_instructor_last_name"],[name="invite_instructor_email"]' ).val('');
				});
				CoursePress.Course.save();
			}
		} );

		$( '[name="meta_enrollment_type"]' ).on( 'change', function() {
			var options = $( this ).val();
			$( '.cp-box-content.step-6 .enrollment-type-options' ).addClass( 'hidden' );
			$( '.step-content.step-6 .enrollment-type-options.' + options ).removeClass( 'hidden' );
		} );

		// "This is a paid course" checkbox
		$( '[name="meta_payment_paid_course"]' ).on( 'change', function() {
			if ( this.checked ) {
				$( this ).parents( '.cp-box-content.step-6' ).find( '.payment-message' ).removeClass( 'hidden' );
				$( this ).parents( '.cp-box-content.step-6' ).find( '.is_paid_toggle' ).removeClass( 'hidden' );
			} else {
				$( this ).parents( '.cp-box-content.step-6' ).find( '.payment-message' ).addClass( 'hidden' );
				$( this ).parents( '.cp-box-content.step-6' ).find( '.is_paid_toggle' ).addClass( 'hidden' );
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
					window.location.reload();
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
				CoursePress.Course.on( 'coursepress:withdraw_all_students_success', function() {
					window.location.reload();
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
				CoursePress.Course.on( 'coursepress:invite_student_success', function(){
					// Reload page
					window.location.reload();
				});
			}

			$( '[name=invite-email]' ).val( '' );
			$( '[name=invite-firstname]' ).val( '' );
			$( '[name=invite-lastname]' ).val( '' );

		} );

		//Resend student invitation
		$( '.column-actions .resend-invite' ).on( 'click', function(){
			var el = $(this),
				element_data = el.data();

			$( '[name=invite-email]' ).val( element_data.email );
			$( '[name=invite-firstname]' ).val( element_data.firstname );
			$( '[name=invite-lastname]' ).val( element_data.lastname );
			$( '.coursepress_course_invite_student_wrapper .invite-submit' ).trigger( 'click' );

			return false;
		});

		// Remove invitation from the list
		$( '.column-actions .remove-invite' ).on( 'click', function(){
			var that = this,
				student_data = $( this ).data(),
				data = {
					email: student_data.email,
					nonce: student_data.nonce,
					course_id: _coursepress.course_id
				};

			CoursePress.Course.set( 'action', 'remove_student_invitation' );
			CoursePress.Course.set( 'data', data );
			CoursePress.Course.save();

			CoursePress.Course.on( 'coursepress:remove_student_invitation_success', function() {
				$( that ).parents( 'tr' ).first().slideUp(function(){
					$(this).remove();
					// Check if there are no longer invitation
					var invited_list = $( '.invited-list' );

					if ( ! invited_list.length ) {
						$( '.invited-students' ).hide();
					}
				});
			});

			return false;
		});

		// Add course facilitator
		$( '.button.facilitator-assign' ).on( 'click', function() {
			if ( $(this).hasClass( "disabled" ) ) {
				return false;
			}
			var select = $( '[name="facilitators"]' ),
				facilitator_id = select.val(),
				facilitator_name = select.find( ':selected' ).text(),
				avatar = _coursepress.instructor_avatars['default'],
				container = $( '.facilitator-info' ),
				is_done = $( '#facilitator-' + facilitator_id ).length > 0,
				dropdown = $( '.select2-selection__rendered' )

			if ( is_done ) {
				return; // Bail if facilitator already exist
			}

			dropdown.html( _coursepress.labels.user_dropdown_placeholder );

			var data = {
				facilitator_id: facilitator_id,
				facilitator_name: facilitator_name,
				course_id: _coursepress.course_id,
				nonce: get_setup_nonce()
			};

			var div = $( '<div class="facilitator-avatar-holder empty" id="facilitator-' + facilitator_id + '"><span class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></span></div>' ).appendTo( container );

			CoursePress.Course.set( 'action', 'add_facilitator' );
			CoursePress.Course.set( 'data', data );
			CoursePress.Course.save();
		});

		// Remove course facilitator
		CoursePress.remove_facilitator = function() {
			var div = $( this ).parent(),
				facilitator_id = div.data( 'id' )
			;

			// Let's hide the div while sending the request
			div.hide();

			var data = {
				facilitator_id: facilitator_id,
				nonce: get_setup_nonce(),
				course_id: _coursepress.course_id
			};
			CoursePress.Course.set( 'action', 'remove_facilitator' );
			CoursePress.Course.set( 'data', data );
			CoursePress.Course.save();
		};
		$( '.facilitator-remove' ).on( 'click', CoursePress.remove_facilitator );

		/**
		 * send email to enroled students
		 */
		$( '.coursepress_course_email_enroled_students_wrapper .send-submit' ).on( 'click', function() {
			// Really basic validation
			var send_to = $( '[name="send_to"]' ).val();
			var subject = $( '[name=email-subject]' ).val();
			var body = $( '[name=email-body]' ).val();
			var message_container = $('#send-email-to-enroled-students');
			$( '.coursepress_course_email_enroled_students_wrapper .send-submit' ).hide();
			$('.coursepress-email-field', message_container).slideUp();
			$('.coursepress-email-sending', message_container).slideDown();

			CoursePress.Course.set( 'action', 'send_email' );
			var data = {
				send_to: send_to,
				subject: subject,
				body: body,
				course_id: _coursepress.course_id,
				nonce: $( this ).attr( 'data-nonce' )
			};
			CoursePress.Course.set( 'data', data );
			CoursePress.Course.save();
			CoursePress.Course.off( 'coursepress:send_email_success' );
			CoursePress.Course.on( 'coursepress:send_email_success', function( data ){
				$('.coursepress-email-sending td', message_container).html(data.message.info);
				$('.coursepress-email-field-subject td', message_container).html(data.message.subject);;
				$('.coursepress-email-field-body td', message_container).html(data.message.body);;
				$('.coursepress-email-field', message_container).slideDown();
			});
			CoursePress.Course.off( 'coursepress:send_email_error' );
			CoursePress.Course.on( 'coursepress:send_email_error', function( data ) {
				$('.coursepress-email-sending td', message_container).html(data.message.info);
				$('.coursepress-email-field-subject td', message_container).html(data.message.subject);;
				$('.coursepress-email-field-body td', message_container).html(data.message.body);;
				$('.coursepress-email-field', message_container).slideDown();
			});
		} );

	}

	/**
	 * Used to bind instructor boxes. Separated to be invoked on individual buttons.
	 * @param selector
	 */
	function bind_remove_button( selector ) {
		$( selector ).on( 'click', function( e ) {
			var target = e.currentTarget;
			var step_box, data, message;
			var meta = $(target).parents('.avatar-holder').data();
			switch( meta.status ) {
				case 'confirmed':
					message = _coursepress.instructor_delete_confirm;
					if ( 'facilitator' === meta.who ) {
						message = _coursepress.facilitator_delete_confirm;
					}
					// Confirm before deleting
					if ( window.confirm( message ) ) {
						$(target).html( '<span class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></span>' );
						$(target).parent().addClass('removing-process');
						step_box = $( target ).parents('.cp-box-content')[0];
						$( step_box ).find('.button.update.hidden' ).removeClass('hidden');
						CoursePress.Course.set( 'action', 'delete_instructor' );
						data = {
							instructor_id: meta.id,
							who: meta.who,
							course_id: _coursepress.course_id,
							nonce: get_setup_nonce()
						};
						CoursePress.Course.set( 'data', data );
						CoursePress.Course.save();
                        /**
                         * reset select2
                         */
                        $("#student-add, #facilitators, #instructors").select2("val", "");
					}
					break;
				case 'pending':
					message = _coursepress.instructor_delete_invite_confirm;
					if ( 'facilitator' === meta.who ) {
						message = _coursepress.facilitator_delete_invite_confirm;
					}
					// Confirm before deleting
					if ( window.confirm( message ) ) {
						$(target).html( '<span class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></span>' );
						$(target).parent().addClass('removing-process');
						step_box = $( target ).parents('.cp-box-content')[0];
						$( step_box ).find('.button.update.hidden' ).removeClass('hidden');
						CoursePress.Course.set( 'action', 'delete_instructor_invite' );
						data = {
							invite_code: meta.code,
							who: meta.who,
							course_id: _coursepress.course_id,
							nonce: get_setup_nonce()
						};
						CoursePress.Course.set( 'data', data );
						CoursePress.Course.save();
					}
					break;
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
			if ( data.last_step == data.next_step ) {
				CoursePress.Course.show_message( _coursepress.unit_builder_form.messages.setup.saved, 'success' );
			}
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

				if ( !/\&post/.test( dest ) ) {
					dest += '&post=' + data.course_id;
				}
				if ( !/\&action=edit/.test( dest ) ) {
					dest += '&action=edit';
				}
				if ( /post-new.php/.test( dest ) ) {
					dest = dest.replace( /post-new.php/, 'post.php' );
				}

				location.href = dest + '&tab=units';
			} else {
				var edit_link = $( '#edit_course_link_url' );
				if ( edit_link.length > 0 ) {
					if ( window.history.pushState ) {
						// Reset browser url
						window.history.pushState( {}, null, edit_link.val() );
					}
				}
			}
		} );

		CoursePress.Course.on( 'coursepress:update_course_error', function( data ) {
			CoursePress.Course.show_message( _coursepress.unit_builder_form.messages.setup.error, 'error' );
			$( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'saved' );
			$( '.step-title.step-' + data.last_step ).find( '.status' ).addClass( 'save-error' );
			$( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-attention' );
			$( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-process' );
		} );

		/**
		 * INSTRUCTOR ACTIONS
		 */
		CoursePress.Course.on( 'coursepress:add_instructor_success', function( data ) {

			var content = '';
			var avatar = _coursepress.instructor_avatars[ 'default' ];
			var template = wp.template('course-person');

			$( "input.button.instructor-assign" ).addClass( "disabled" );

			if ( data.avatar ) {
				avatar = data.avatar;
			}

			// Remove marker
			$( '#' + data.who + '_holder_' + data.id ).remove();

			/**
			 * process template
			 */
			content += template( data );

			if ( 'instructor' == data.who ) {
				if ( $( '.instructor-avatar-holder.empty' ).length > 0 ) {
					$( '.instructor-avatar-holder.empty' ).detach();
				}
			}

			if ( $( '#' + data.who + '_holder_' + data.id ).length === 0 ) {
				$( '#' + data.who + 's-info' ).append( content );
				bind_remove_button( '#' + data.who + '_holder_' + data.id + ' .remove a' );
			}

			update_nonce( data );
		} );

		CoursePress.Course.on( 'coursepress:delete_instructor_success', function( data ) {
			// Remove Instructor or Facilitator Avatar
			$( '#' + data.who + '_holder_' + data.instructor_id ).detach();
			if ( 'instructor' == data.who ) {
				var empty_holder = '<div class="instructor-avatar-holder empty"><span class="instructor-name">' + _coursepress.instructor_empty_message + '</span></div>';
				if ( $( '.instructor-avatar-holder' ).length === 0 ) {
					$( '#instructors-info' ).append( empty_holder );
				}
			}
			update_nonce( data );
		} );

		CoursePress.Course.on( 'coursepress:invite_instructor_success', function( data ) {
			var template = wp.template('course-invitation');
			var email = data.data.email;
			var invite_code = data.invite_code;
			var img = CoursePress.utility.get_gravatar_image( email, 80 );

			var remove_buttons = true; // permission required
			var content = '';
			var message = '';
			var who = 'instructor';
			var template_data = data.data;

			if ( "undefined" != typeof( data.data.who ) ) {
				who = data.data.who;
			}
			template_data.who = who;
			template_data.code = invite_code;
			template_data.avatar = data.avatar;
			/**
			 * process template
			 */
			content += template( template_data );

			if ( 'instructor' == who && $( '.instructor-avatar-holder.empty', parent ).length > 0 ) {
				$( '.instructor-avatar-holder.empty', parent ).detach();
			}
			/**
			 * add new invitation
			 */
			if ( $( '#' + who + '_holder_' + invite_code ).length === 0 ) {
				$( '#' + who + 's-info' ).append( content );
				bind_remove_button( '#' + who + '_holder_' + invite_code + ' .remove a' );
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
			var who = 'instructor';
			if ( "undefined" != typeof( data.who ) ) {
				who = data.who;
			}
			$( '#' + who + '_holder_' + data.invite_code ).detach();
			update_nonce( data );
		} );
		// Add facilitator
		CoursePress.Course.on( 'coursepress:add_facilitator_success', function( data ) {
			var template = wp.template('course-person');
			var facilitator_id = data.facilitator_id,
				avatar = _coursepress.instructor_avatars['default']
			;
			$( "input.button.facilitator-assign" ).addClass( "disabled" );
			if ( data.avatar ) {
				avatar = data.avatar;
			}
			/**
			 * process template
			 */
			content = template( data );
			if ( $( '#' + data.who + '_holder_' + data.id ).length === 0 ) {
				$( '#' + data.who + '-' + data.id ).detach();
				$( '#' + data.who + 's-info' ).append( content );
				bind_remove_button( '#' + data.who + '_holder_' + data.id + ' .remove a' );
			}

			update_nonce( data );
		});

		CoursePress.Course.on( 'coursepress:add_facilitator_error', function( data ) {
			var div = $( '#facilitator-' + data.facilitator_id ).empty();
			div.html(data.message);

			div.fadeOut(3000, function() {
				div.remove();
			});

			update_nonce( data );
		});

		// Remove facilitator
		CoursePress.Course.on( 'coursepress:remove_facilitator_success', function( data ) {
			var div = $( '#facilitator-' + data.facilitator_id );
			div.remove();

			update_nonce( data );
		});

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
		//CoursePress.Post.on( 'coursepress:notification:toggle_success', function( data ) { } );
		CoursePress.Post.on( 'coursepress:notification:toggle_error', function( data ) {
			var element = $( '#publish-notification-toggle-' + data.ID );
			if ( element.hasClass( 'on' ) ) {
				element.removeClass( 'on' ).addClass( 'off' );
			} else {
				element.removeClass( 'off' ).addClass( 'on' );
			}
		} );

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
		CoursePress.Post.on( 'coursepress:discussion:toggle_error', function( data ) {
			var element = $( '#publish-discussion-toggle-' + data.ID );
			if ( element.hasClass( 'on' ) ) {
				element.removeClass( 'on' ).addClass( 'off' );
			} else {
				element.removeClass( 'off' ).addClass( 'on' );
			}
		} );

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

	var toggleCertificatePreview = function() {
		var input = $(this),
			is_checked = input.is( ':checked' ),
			preview = $( '.btn-cert' )
		;
		var parent = $(this).closest( '.wide.course-certificate');
		preview[ is_checked ? 'addClass' : 'removeClass' ]( 'button-primary' );
		if ( is_checked ) {
			$( '.options, .btn-cert', parent).slideDown();
		} else {
			$( '.options, .btn-cert', parent).slideUp();
		}
	},
	testCertificateEmail = function() {
		var link = $(this),
			url = link.attr( 'href' )
		;
	}
	var toggleTimePreview = function() {
		var input = $(this);
		var is_checked = input.is( ':checked' );
		var parent = $(this).closest( '.wide');
		if ( is_checked ) {
			$( '.column-time', parent).show();
		} else {
			$( '.column-time', parent).hide();
		}
	}

	/**
	 * Search Users
	 */
	var Search_Params = {
		placeholder: _coursepress.labels.user_dropdown_placeholder,
		allowClear: true,
		ajax: {
			url: _coursepress._ajax_url,
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					q: params.term, // search term
					page: params.page,
					action: 'coursepress_user_search',
					course_id: _coursepress.course_id,
					_wpnonce: $(this).data('nonce-search')
				};
			},
			processResults: function (data, params) {
				// parse the results into the format expected by select2
				// since we are using custom formatting functions we do not need to
				// alter the remote JSON data, except to indicate that infinite
				// scrolling can be used
				params.page = params.page || 1;
				return {
					results: data.items,
					pagination: {
						more: (params.page * 30) < data.total_count
					}
				};
			},
			cache: true
		},
		escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
		minimumInputLength: 3,
		templateResult: formatData,
		templateSelection: formatDataSelection
	};

	function formatData (data) {
		if (data.loading) return data.text;

		var markup = '<div class="select2-result-course clearfix">' + data.gravatar + ' <span>' + data.display_name + '</span></div>';
		return markup;
	}

	function formatDataSelection (data ) {
		var markup = '';

		if ( ! data || ( ! data.gravatar && ! data.display_name ) ) {
			markup = _coursepress.labels.user_dropdown_placeholder;
		} else {
			markup = '<div class="select2-result-course clearfix">' + data.gravatar + ' <span>' + data.display_name + '</span></div>';
		}

		return markup;
	}

	// UPDATE COURSE
	CoursePress.updateCourse = function() {
		var form = $(this),
			step = $( '#course-setup-steps .cp-box-content:visible .step.next' )
			finishbutton = $( '#course-setup-steps .cp-box-content:visible .finish.step-7' )
		;

		if ( 0 === step.length && 0 === finishbutton.length ) {
			// Search students helper
			var s = $( '[name="s"]', form ),
				url = $( '[name="_wp_http_referer"]' );

			form.attr( 'action', url.val() );

			return true;
		}

		if ( 0 === finishbutton.length ) {
			step.trigger( 'click' );
		} else {
			finishbutton.trigger( 'click' );
			var params = form.serialize();

			if ( ! CoursePress.Course.hasError ) {
				$.post( form.attr('action'), params );
			}
		}

		return false;
	};

	CoursePress.maybeUpdateCourse = function() {
		var form = $( 'form#post' ),
			target = $(this);

		if ( 0 == form.length ) {
			return true;
		}

		if ( target.is( '#search-submit' ) ) {
			var referer = form.find( '[name="_wp_http_referer"]' ),
				s = form.find( '[name="s"]' ).val();
			referer = referer.val() + '&s=' + s;

			window.location = referer;
			return false;
		}

		if ( target.is( '#post-preview' ) ) {
			var href = target.attr('href');
			window.open(href);
		}

		form.unbind('submit').submit();

		return false;
	};

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

		// Trigger basic certificate
		$( '[name="meta_basic_certificate"]' ).each(toggleCertificatePreview);

		/**
		 * Check select2 exist first!
		 */
		if ( "function" == typeof($().select2) ) {
			$('#student-add, #facilitators, #instructors').select2( Search_Params )
			.on( "select2:selecting", function(e) {
				$( "input.button.disabled", $(this).closest( ".wide" ) ).removeClass( "disabled" );
			})
			.on( "select2:unselecting", function(e) {
				$( "input.button", $(this).closest( ".wide" ) ).addClass( "disabled" );
			});
		}

		/**
		 * Notification & Forum
		 */
		$( ".course-edit-notification .save-post-status, .course-edit-forums .save-post-status").click( function( event ) {
			$( "#post-status-display" ).html( $( "option:selected", $( "#post-status-select" ) ).text() );
		});
		$( ".course-edit-notification input[type=submit], .course-edit-forums input[type=submit]" ).click( function( event ) {
			var is_notification = $('.course-edit-notification').length > 0;
			var is_forum = $('.course-edit-forums').length > 0;
			var errors = [];
			var show_content_alert = false;
			/**
			 * Check title
			 */
			if ( '' === $('#titlewrap input[name=post_title]').val() ) {
				if ( is_notification ) {
					errors.push( _coursepress.messages.notification.empty_title );
				} else if ( is_forum ) {
					errors.push( _coursepress.messages.discussion.empty_title );
				} else {
					errors.push( _coursepress.messages.general.empty_title );
				}
			}
			/**
			 * Check content
			 */

			if ( $(".wp-editor-wrap").hasClass("tmce-active" ) ) {
				var body = tinymce.activeEditor.getBody(), text = tinymce.trim(body.innerText || body.textContent);
				if ( 0 === text.length ) {
					show_content_alert = true;
				}
			} else {
				if ( '' === $(".wp-editor-wrap .wp-editor-area").val() ) {
					show_content_alert = true;
				}
			}
			if ( show_content_alert ) {
				if ( is_notification ) {
					errors.push( _coursepress.messages.notification.empty_content );
				} else if ( is_forum ) {
					errors.push( _coursepress.messages.discussion.empty_content );
				} else {
					errors.push( _coursepress.messages.general.empty_content );
				}
			}
			if ( errors.length ) {
				alert( errors.join( "\n" ) );
				return false;
			}
			if ( $(this).hasClass('button-primary') && $(this).hasClass('force-publish' ) ) {
				$('#post_status').val('publish');
			}
			return true;
		});

		/**
		 * Visibility aka reciever
		 */
		$( "#course_id" ).change( function( event ) {
			if ( "all" === $( "option:selected", $(this) ).val() ) {
				$( "#post-visibility-display" ).html( $("#misc-publishing-actions").data("no-options") );
				$( "#visibility a.edit-visibility" ).hide();
			} else {
				$( "#post-visibility-display" ).html( $( "input:checked", $( "#visibility" ) ).data( "info" ) );
				$( "#visibility a.edit-visibility" ).show();
			}
		});
		$( ".course-edit-notification .save-post-visibility" ).click( function( event ) {
			$( "#post-visibility-display" ).html( $( "input:checked", $( "#visibility" ) ).data( "info" ) );
		});

	} )
	// Prevent from opening when inactive
	.on( 'click', '.btn-cert', function() {
		var link = $(this),
			is_active = link.is( '.button-primary' )
		;
		if ( ! is_active ) {
			return false;
		}

		tinymce.triggerSave();
		var certificate_settings = $('input, textarea', $('.course-certificate')).serialize(),
			preview_url_parts = [
				link.attr('href'),
				certificate_settings
			];

		window.open(preview_url_parts.join('&'), '_blank');
		return false;
	})
	.on( 'change', '[name="meta_basic_certificate"]', toggleCertificatePreview )
	.on( 'change', '[name="meta_structure_show_duration"]', toggleTimePreview )
	.on( 'click', '.post-type-course #publish, .post-type-course #search-submit, .post-type-course #post-preview', CoursePress.maybeUpdateCourse )
	.on( 'submit', '.post-type-course form#post', CoursePress.updateCourse );

})( jQuery );

