/*! CoursePress - v2.1.4
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2018; * Licensed GPLv2+ */
var CoursePress = CoursePress || {};

(function( $ ) {
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

	$( document ).ready( function( $ ) {
		$( '[name*="publish-course-toggle"]' ).on( 'change', function( e, state ) {
			var nonce = $( this ).attr( 'data-nonce' );
			var status = 'off' === state ? 'draft' : 'publish';
			var course_id = parseInt( $( this ).attr( 'id' ).replace( 'publish-course-toggle-', '' ) );
			CoursePress.Course.set( 'action', 'toggle_course_status' );
			var data = {
				nonce: nonce,
				status: status,
				state: state,
				course_id: course_id
			};

			CoursePress.Course.set( 'data', data );
			CoursePress.Course.save();
		} );

		CoursePress.Course.on( 'coursepress:toggle_course_status_success', function( data ) {
			$( '[name="publish-course-toggle-' + data.course_id + '"]' ).attr( 'data-nonce', data.nonce );
		} );

		CoursePress.Course.on( 'coursepress:toggle_course_status_error', function( data ) {
			// Toggle back
			var toggle = $( '[name="publish-course-toggle-' + data.course_id + '"]' );
			if ( $( toggle ).hasClass( 'on' ) ) {
				$( toggle ).removeClass( 'on' ).addClass( 'off' );
			} else if ( $( toggle ).hasClass( 'off' ) ) {
				$( toggle ).removeClass( 'off' ).addClass( 'on' );
			}
		} );

		$( '.bulkactions .button.action' ).on( 'click', function() {
			var nonce = $('.nonce-holder').attr( 'data-nonce' );
			var courses = [];
			var courses_titles = [];
			var form = $(this).parents( 'form' ).first();

			$.each( $( '[name="post[]"]' ), function( index, item ) {
				if ( $( item ).is( ':checked' ) ) {
					courses.push( $( item ).val() );
					courses_titles.push( $('.post_title strong', $(item).closest('tr')).html() );
				}
			} );

			if ( 0 === courses.length ) {
				return false;
			}

			var action = $( this ).siblings('select' ).val();
			action = action === '-1' ? '' : action;

			var proceed = true;

			if ( 'delete' === action ) {
				proceed = window.confirm( _coursepress.courselist_bulk_delete );
				var template;
				var template_name = 'coursepress-courses-delete-one';
				var data = {
					names: "",
					size: courses.length
				};

				if ( 1 == courses.length ) {
					data.names = courses_titles.join();
				} else {
					template_name = 'coursepress-courses-delete-more';
					data.names = "<ul><li>"+courses_titles.join( "</li><li>" )+ "</ul>";
				}
				template = wp.template( template_name );
				$('#wpbody-content h1').after( template( data ) );
			}

			/**
			 * export
			 */
			if ( 'export' === action ) {
				if ( 0 === courses.length ) {
					alert( _coursepress.courselist_export );
					return false;
				}
				var courses_param = '&';
				$.each( courses , function( index, item ) {
					courses_param += 'coursepress[courses]['+item+']='+item+'&';
				} );
				var export_nonce = $('.export-nonce-holder').attr( 'data-nonce' );
				window.location = './admin.php?page=coursepress_export'+courses_param+'coursepress_export='+export_nonce;
				return false;
			}

			if ( action.length > 0 && proceed ) {
				CoursePress.Course.set( 'action', 'bulk_actions' );

				var data = {
					nonce: nonce,
					the_action: action,
					courses: courses
				};

				CoursePress.Course.set( 'data', data );
				CoursePress.Course.save();
			}
		});

		CoursePress.Course.on( 'coursepress:bulk_actions_success', function( data ) {
			$('.nonce-holder').attr( 'data-nonce', data.nonce );
			window.location.reload();
		} );

		CoursePress.Course.on( 'coursepress:bulk_actions_error', function() {
			window.location.reload();
		} );

		$('.delete-course-link' ).on( 'click', function( e ) {
			e.stopImmediatePropagation();
			e.preventDefault();

			var link = $(this),
				parentTR = link.parents( 'tr' ).first()
			;

			if ( window.confirm( _coursepress.courselist_delete_course ) ) {
				data = {
					names: $('.post_title strong', parentTR).html()
				};
				template = wp.template( 'coursepress-courses-delete-one' );
				$('#wpbody-content h1').after( template( data ) );
				CoursePress.Course.set( 'action', 'delete_course' );

				var data = {
					nonce: $( this ).attr('data-nonce'),
					course_id: $( this ).attr('data-id')
				};

				CoursePress.Course.set( 'data', data );
				CoursePress.Course.save();
				CoursePress.Course.off( 'coursepress:delete_course_success' );
				CoursePress.Course.on( 'coursepress:delete_course_success', function() {
					window.location.reload();
				});
				// In case something went wrong while deleting, tell the user.
				CoursePress.Course.on( 'coursepress:delete_course_error', function() {
					alert( _coursepress.server_error );
				});

			}
		});

		$( '.duplicate-course-link' ).on( 'click', function( e ) {
			var data;
			e.stopImmediatePropagation();
			e.preventDefault();

			var link = $(this);
			var parentTR = link.parents( 'tr' ).first();

			if ( window.confirm( _coursepress.courselist_duplicate_course ) ) {
				data = {
					names: $('.post_title strong', parentTR).html()
				};
				template = wp.template( 'coursepress-courses-duplicate' );
				$('#wpbody-content h1').after( template( data ) );
				CoursePress.Course.set( 'action', 'duplicate_course' );

				data = {
					nonce: $( this ).attr('data-nonce'),
					course_id: $( this ).attr('data-id')
				};

				parentTR.addClass( 'duplicating' );

				CoursePress.Course.set( 'data', data );
				CoursePress.Course.save();
				CoursePress.Course.off( 'coursepress:duplicate_course_success' );
				CoursePress.Course.on( 'coursepress:duplicate_course_success', function() {
					window.location.reload();
				});

			}
		});

		// ====== COURSEPRESS UI TOGGLES =====
		$( '.coursepress-ui-toggle-switch' ).coursepress_ui_toggle();
	} );
})( jQuery );
