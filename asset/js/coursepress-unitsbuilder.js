/*! CoursePress - v2.2.2
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2019; * Licensed GPLv2+ */
/*global tinyMCEPreInit*/
/*global _coursepress*/


var CoursePress = CoursePress || {};

(function( $ ) {
	CoursePress.Views = CoursePress.Views || {};
	CoursePress.Models = CoursePress.Models || {};
	CoursePress.Collections = CoursePress.Collections || {};
	CoursePress.Helpers = CoursePress.Helpers || {};

	/** Expand the CoursePress Object for handling Modules **/
	CoursePress.Helpers.Module = CoursePress.Helpers.Module || {};
	CoursePress.Helpers.Module.quiz = CoursePress.Helpers.Module.quiz || {};
	CoursePress.Helpers.Module.form = CoursePress.Helpers.Module.form || {};

	CoursePress.Helpers.Module.refresh_ui = function() {

		// Bring on the Visual Editor
		$.each( $( '.unit-builder-modules .editor' ), function( index, editor ) {
			var id = $( editor ).attr( 'id' );

			/**
			 * init only new one
			 */
			if ( "undefined" === typeof tinyMCE.editors[id] ) {
				var content = $( '#' + id ).val();
				var name = $( editor ).attr( 'name' );
				var height = $( editor ).attr( 'data-height' ) ? $( editor ).attr( 'data-height' ) : 400;
				CoursePress.editor.create( editor, id, name, content, false, height );
			}
		} );

		// Fix Accordion
		if ( $( '.unit-builder-modules' ).hasClass( 'ui-accordion' ) ) {
			$( '.unit-builder-modules' ).accordion( 'destroy' );
		}

		var element = 0;
		if ( CoursePress.UnitBuilder.activeModuleRef && CoursePress.UnitBuilder.activeModuleRef.length > 0 ) {
			var active = $( '[data-cid="' + CoursePress.UnitBuilder.activeModuleRef + '"]' )[ 0 ];
			element = parseInt( $( active ).attr( 'data-order' ) ) - 1;
		}

		// Pass in heightStyle or it chops off the bottom of modules.
		$( '.unit-builder-modules' ).accordion( {
			heightStyle: 'content',
			collapsible: true,
			header: '> div > h3',
			active: element
		} ).sortable( {
			axis: 'y',
			handle: 'h3',
			stop: function( event, ui ) {
				ui.item.children( 'h3' ).triggerHandler( 'focusout', ui );
				var modules = $( '.module-holder' );
				$.each( modules, function( index, module ) {
					var current_order = parseInt( $( module ).attr( 'data-order' ) );
					var new_order = index + 1;
					var cid = $( module ).attr( 'data-cid' );
					$( module ).attr( 'data-order', new_order );
					if ( current_order !== new_order ) {
						CoursePress.UnitBuilder.module_collection._byId[ cid ].set_meta( 'module_order', new_order );
						$( module ).addClass( 'dirty' );
					}
				} );
				$( this ).accordion( 'refresh' );
				// Fix for TinyMCE breaking after sorting the unit module.
				var editor_tabs = this.getElementsByClassName('wp-editor-tabs');
				$.each( editor_tabs, function( index, editor_tab ){
					while (editor_tab.hasChildNodes()) {
						editor_tab.removeChild(editor_tab.lastChild);
					}
				});
				var editors = document.querySelectorAll('textarea[id^="post_content_"]');
				$.each( editors, function( index, editor ) {
					tinymce.execCommand("mceRemoveEditor", true, editor.id);
					tinymce.execCommand("mceAddEditor", true, editor.id);
				})
			}
		} );
		// Sortable Tabs
		$( '.unit-builder-tabs ul' ).sortable( {
			stop: function() {
				var units = $( '.unit-builder-tabs ul li' );
				$.each( units, function( index, unit ) {
					var current_order = parseInt( $( unit ).attr( 'data-order' ) );
					var new_order = index + 1;
					var cid = $( unit ).attr( 'data-cid' );
					$( unit ).attr( 'data-order', new_order );
					if ( current_order !== new_order ) {
						var meta = CoursePress.UnitBuilder.unit_collection._byId[ cid ].get( 'meta' );
						meta[ 'unit_order' ] = new_order;
						CoursePress.UnitBuilder.unit_collection._byId[ cid ].set( 'meta', meta );
						CoursePress.UnitBuilder.unit_collection._byId[ cid ].set( 'flag', 'dirty' );
						$( unit ).addClass( 'dirty' );
					}
				} );
			}
		} );
		// Attach Media Browser behavior
		$( '.button.browse-media-field' ).browse_media_field();
		$( '.unit-builder-pager ul li' ).removeClass( 'active' );
		$( '.unit-builder-pager ul li[data-page="' + CoursePress.UnitBuilder.activePage + '"]' ).addClass( 'active' );
		// ====== COURSEPRESS UI TOGGLES =====
		if ( 'publish' === CoursePress.UnitBuilder.activeUnitStatus ) {
			$( '#unit-live-toggle' ).removeClass( 'off' );
			$( '#unit-live-toggle' ).addClass( 'on' );
			$( '#unit-live-toggle-2' ).removeClass( 'off' );
			$( '#unit-live-toggle-2' ).addClass( 'on' );
		} else {
			$( '#unit-live-toggle' ).removeClass( 'on' );
			$( '#unit-live-toggle' ).addClass( 'off' );
			$( '#unit-live-toggle-2' ).removeClass( 'on' );
			$( '#unit-live-toggle-2' ).addClass( 'off' );
		}
		var activeUnit = CoursePress.UnitBuilder.unit_collection.get( CoursePress.UnitBuilder.activeUnitRef ),
			unit_user_cap = activeUnit && activeUnit.get( 'user_cap' ) ? activeUnit.get( 'user_cap' ) : {},
			can_publish = unit_user_cap['coursepress_change_unit_status_cap'];
		$( '.coursepress-ui-toggle-switch' ).each( function() {
			var	ui = $( this ),
				ui_name = ui.attr( 'name' ),
				is_toggle = true;
			if ( 'publish-course-toggle' === ui_name ) {
				if ( ! CoursePress.current_user_can( 'coursepress_change_status_cap' ) ) {
					is_toggle = false;
				}
			}
			if ( 'unit-live-toggle' === ui_name || 'unit-live-toggle-2' === ui_name ) {
				if ( ! can_publish ) {
					is_toggle = false;
					ui.unbind( 'click' );
				} 
			}
			if ( is_toggle ) {
				ui.coursepress_ui_toggle();
			}
		});
		// Hide delete button
		var unit_delete_button = CoursePress.UnitBuilder.$el.find( '.unit-delete-button' ),
			can_edit = unit_user_cap['coursepress_delete_course_units_cap'];
		unit_delete_button[ can_edit ? 'show' : 'hide' ]();
		// Delete Page button
		if ( CoursePress.UnitBuilder.totalPages === 1 ) {
			$( '.unit-delete-page-button' ).addClass( 'hidden' );
		} else {
			$( '.unit-delete-page-button' ).removeClass( 'hidden' );
		}
		// Make pages droppable
		$( '.unit-builder-pager ul li[data-page]' ).droppable( {
			activeClass: 'page-droppable',
			hoverClass: 'page-droppable-hover',
			accept: '.unit-builder-modules .group',
			tolerance: 'pointer',
			drop: function( event, ui ) {
				var el = event.target;
				var page = parseInt( $( el ).attr( 'data-page' ) );
				var current_page = CoursePress.UnitBuilder.activePage;
				var mod_el = ui.draggable;
				if ( current_page !== page ) {
					var mod_ref = $( $( mod_el ).find( '.module-holder' )[ 0 ] ).attr( 'data-cid' );
					var meta = CoursePress.UnitBuilder.module_collection._byId[ mod_ref ].get( 'meta' );
					meta[ 'module_page' ] = page;
					meta[ 'module_order' ] = meta[ 'module_order' ] + 900;
					CoursePress.UnitBuilder.module_collection._byId[ mod_ref ].set( 'meta', meta );
					CoursePress.UnitBuilder.module_collection._byId[ mod_ref ].trigger( 'change', CoursePress.UnitBuilder.module_collection._byId[ mod_ref ] );
					$( mod_el ).detach();
				} else {
				}
			}
		} );
		// ===== DATE PICKERS =====
		$( '.dateinput' ).datepicker( {
			dateFormat: 'yy-mm-dd'
			//firstDay: coursepress.start_of_week
		} );
		$( '.date' ).off( 'sync' );
		$( '.date' ).on( 'click', function() {
			$( this ).find( '.dateinput' ).datepicker( 'show' );
		} );
		// Fix heights if needed
		var button_position = $( '.button-add-new-unit' );
		
		if ( button_position.length > 0 ) {
			button_position = button_position.position().top + $( '.button-add-new-unit' ).innerHeight() + 20;
		} else {
			button_position = 0;
		}
		var current_min = parseFloat( $( '#unit-builder .tab-content' ).css( 'min-height' ).replace( 'px', '' ) );
		if ( current_min < 818 ) {
			current_min = 818;
			$( '#unit-builder .tab-content' ).css( 'min-height', current_min + 'px' );
		}
		if ( current_min < button_position ) {
			$( '#unit-builder .tab-content' ).css( 'min-height', button_position + 'px' );
		}
		// ===== QUIZ BUTTONS =====
		$( '.unit-builder-body .quiz-action-button').off( 'click' );
		$( '.unit-builder-body .quiz-action-button').on( 'click', function() {
			var el = this;
			var container = $( el ).parents('.module-components')[0];
			var mod_el = $( el).parents('.module-holder')[0];
			var el_all = $( container).find( '.quiz-question' );
			var total = el_all.length;
			var type = $( el).attr('data-type');
			var content = '<div class="quiz-question question-' + ( total + 1 ) + '" data-id="' + (total + 1) + '" data-type="' + type + '" style="position: relative; border: 1px solid rgba(0,0,0,0.2); margin-top: 10px; padding: 5px;">';
			content += '<div class="quiz-question-remove" style="position: absolute; top:5px; right:5px; font-weight: bolder; font-size: 1.2em; cursor: pointer;">X</div>';
			var question_type = '';
			var question_content = '<div class="question-answer">';
			switch( type ) {
				case 'single':
					var radio_name = 'single-' + ( total + 1 );
					question_type = _coursepress.unit_builder.question_type.single;
					question_content += '<div class="answer-group">';
					question_content += '<div class="answer"><input type="radio" name="' + radio_name + '" value="" />';
					question_content += '<input class="component-radio-answer wide" type="text" value="' + _coursepress.unit_l8n.pre_answers.a + '" /><span class="remove-quiz-item"><i class="fa fa-trash-o"></i></span></div>';
					question_content += '<div class="answer"><input type="radio" name="' + radio_name + '" value="" />';
					question_content += '<input class="component-radio-answer wide" type="text" value="' + _coursepress.unit_l8n.pre_answers.b + '" /><span class="remove-quiz-item"><i class="fa fa-trash-o"></i></span></div>';
					question_content += '</div>';
					question_content += '<a class="add-quiz-item">' + _coursepress.unit_builder_add_answer_label + '</a>';
					break;
				case 'multiple':
					question_type = _coursepress.unit_builder.question_type.multiple;
					question_content += '<div class="answer-group">';
					question_content += '<div class="answer"><input type="checkbox" name="" value="" />';
					question_content += '<input class="component-checkbox-answer wide" type="text" value="' + _coursepress.unit_l8n.pre_answers.a + '" name="" /><span class="remove-quiz-item"><i class="fa fa-trash-o"></i></span></div>';
					question_content += '<div class="answer"><input type="checkbox" name="" value="" />';
					question_content += '<input class="component-checkbox-answer wide" type="text" value="' + _coursepress.unit_l8n.pre_answers.b + '" name="" /><span class="remove-quiz-item"><i class="fa fa-trash-o"></i></span></div>';
					question_content += '</div>';
					question_content += '<a class="add-quiz-item">' + _coursepress.unit_builder_add_answer_label + '</a>';
					break;
				case 'short':
					question_type = _coursepress.unit_builder.question_type.short;
					question_content += '<div class="answer-group">';
					question_content += '<label data-key="label" class="wide">';
					question_content += '<span class="label">' + _coursepress.unit_builder_form_pleaceholder_label + '</span>';
					question_content += '<span class="description">' + _coursepress.unit_builder_form_pleaceholder_desc + '</span>';
					question_content += '<div class="placeholder"><input class="component-placeholder-text wide" type="text" name="" value="" />';
					question_content += '</label>';
					question_content += '</div>';
					question_content += '</div>';
					break;
				case 'long':
					question_type = _coursepress.unit_builder.question_type.long;
					question_content += '<div class="answer-group">';
					question_content += '<label data-key="label" class="wide">';
					question_content += '<span class="label">' + _coursepress.unit_builder_form_pleaceholder_label + '</span>';
					question_content += '<span class="description">' + _coursepress.unit_builder_form_pleaceholder_desc + '</span>';
					question_content += '<div class="placeholder"><input class="component-placeholder-text wide" type="text" name="" value="" />';
					question_content += '</label>';
					question_content += '</div>';
					question_content += '</div>';
					break;
			}
			question_content += '</div>'; // .question-answer
			// Same for all
			content += '<label class="wide" data-key="label">' +
					'<span class="label">' + question_type + ':</span>' +
				'</label>';
			content += '<textarea></textarea>';
			content += question_content;
			content += '</div>';
			$(container).append( content );
			CoursePress.Helpers.Module.quiz.update_meta( mod_el );
			CoursePress.Helpers.Module.quiz.bind_buttons();
		} );
		CoursePress.Helpers.Module.quiz.bind_add_item();
		CoursePress.Helpers.Module.form.bind_buttons();
		// ===== FORM BUTTONS =====
		$( '.unit-builder-body .form-action-button').off( 'click' );
		$( '.unit-builder-body .form-action-button').on( 'click', function() {
			var el = this;
			var container = $( el ).parents('.module-components')[0];
			var mod_el = $( el).parents('.module-holder')[0];
			var el_all = $( container).find( '.quiz-question' );
			var total = el_all.length;
			var type = $( el).attr('data-type');
			var content = '<div class="quiz-question question-' + ( total + 1 ) + '" data-id="' + (total + 1) + '" data-type="' + type + '" style="position: relative; border: 1px solid rgba(0,0,0,0.2); margin-top: 10px; padding: 5px;">';
			content += '<div class="quiz-question-remove" style="position: absolute; top:5px; right:5px; font-weight: bolder; font-size: 1.2em; cursor: pointer;">X</div>';
			var question_type = '';
			var question_content = '<div class="question-answer">';
			switch( type ) {
				case 'short':
					question_type = _coursepress.unit_builder.question_type.short;
					question_content += '<div class="answer-group">';
					question_content += '<label data-key="label" class="wide">';
					question_content += '<span class="label">' + _coursepress.unit_builder_form_pleaceholder_label + '</span>';
					question_content += '<span class="description">' + _coursepress.unit_builder_form_pleaceholder_desc + '</span>';
					question_content += '<div class="placeholder"><input class="component-placeholder-text wide" type="text" name="" value="" />';
					question_content += '</label>';
					question_content += '</div>';
					question_content += '</div>';
					break;
				case 'long':
					question_type = _coursepress.unit_builder.question_type.long;
					question_content += '<div class="answer-group">';
					question_content += '<label data-key="label" class="wide">';
					question_content += '<span class="label">' + _coursepress.unit_builder_form_pleaceholder_label + '</span>';
					question_content += '<span class="description">' + _coursepress.unit_builder_form_pleaceholder_desc + '</span>';
					question_content += '<div class="placeholder"><input class="component-placeholder-text wide" type="text" name="" value="" />';
					question_content += '</label>';
					question_content += '</div>';
					question_content += '</div>';
					break;
				case 'selectable':
					var radio_name = 'selectable-' + ( total + 1 );
					question_type = _coursepress.unit_builder.question_type.selectable;
					question_content += '<div class="answer-group">';
					question_content += '<div class="answer"><input type="radio" name="' + radio_name + '" value="" />';
					question_content += '<input class="component-select-answer wide" type="text" value="' + _coursepress.unit_l8n.pre_answers.a + '" /><span class="remove-form-item"><i class="fa fa-trash-o"></i></span></div>';
					question_content += '<div class="answer"><input type="radio" name="' + radio_name + '" value="" />';
					question_content += '<input class="component-select-answer wide" type="text" value="' + _coursepress.unit_l8n.pre_answers.b + '" /><span class="remove-form-item"><i class="fa fa-trash-o"></i></span></div>';
					question_content += '</div>';
					question_content += '<a class="add-form-item">' + _coursepress.unit_builder_add_answer_label + '</a>';
					break;
			}
			question_content += '</div>'; // .question-answer
			// Same for all
			content += '<label class="wide" data-key="label">' +
					'<span class="label">' + question_type + ':</span>' +
				'</label>';
			content += '<textarea></textarea>';
			content += question_content;
			content += '</div>';
			$(container).append( content );
			CoursePress.Helpers.Module.form.update_meta( mod_el );
			CoursePress.Helpers.Module.form.bind_buttons();
		} );
		CoursePress.Helpers.Module.form.bind_buttons();		
		// Enable/disable duration or minimum grade
		$( '.module-use-timer input, .module-assessable input' ).on( 'change', function() {
			var input = $( this ),
				is_checked = input.is( ':checked' ),
				target = $( input.data( 'target' ), input.parents( '.module-header' ).first() ),
				inputs = target.find( 'input' )
			;
			inputs.attr( 'readonly', ! is_checked );
		}).change();
	};

	CoursePress.Helpers.Module.quiz.render_component = function( module ) {
		var quiz = module.get_meta('questions');
		if ( undefined === quiz || quiz.length <= 0 ) {
			return '';
		}
		var content = '';
		$.each( quiz, function( index, item ) {
			content += '<div class="quiz-question question-' + ( index + 1 ) + '" data-id="' + (index + 1) + '" data-type="' + item.type + '" style="position: relative; border: 1px solid rgba(0,0,0,0.2); margin-top: 10px; padding: 5px;">';
			content += '<div class="quiz-question-remove" style="position: absolute; top:5px; right:5px; font-weight: bolder; font-size: 1.2em; cursor: pointer;">X</div>';
			var question_type = '';
			var question_content = '<div class="question-answer">';
			switch( item.type ) {
				case 'single':
					//question_type = 'Single Choice';
					//
					//question_content += '<div class="answer-group">';
					//
					//question_content += '<div class="answer"><input type="radio" name="" value="" />';
					//question_content += '<input type="text" value="' + _coursepress.unit_l8n.pre_answers.a + '" name="" /><span class="remove-item"><i class="fa fa-trash-o"></i></span></div>';
					//question_content += '<div class="answer"><input type="radio" name="" value="" />';
					//question_content += '<input type="text" value="' + _coursepress.unit_l8n.pre_answers.b + '" name="" /><span class="remove-item"><i class="fa fa-trash-o"></i></span></div>';
					//
					//question_content += '</div>';
					//question_content += '<a class="add-item">' + _coursepress.unit_builder_add_answer_label + '</a>';
					question_type = _coursepress.unit_builder.question_type.single;
					question_content += '<div class="answer-group">';
					item.options.answers = item.options.answers || [];
					$.each( item.options.answers, function( a_index, a_item ) {
						var checked = item.options.checked[a_index] ? 'checked=checked' : '';
						question_content += '<div class="answer"><input type="radio" name="question' + ( index + 1 ) + '" value="" ' + checked + ' />';
						question_content += '<input class="component-radio-answer wide" type="text" value="' + a_item + '" name="" /><span class="remove-quiz-item"><i class="fa fa-trash-o"></i></span></div>';
					} );
					question_content += '</div>';
					question_content += '<a class="add-quiz-item">' + _coursepress.unit_builder_add_answer_label + '</a>';
					break;
				case 'multiple':
					question_type = _coursepress.unit_builder.question_type.multiple;

					question_content += '<div class="answer-group">';

					$.each( item.options.answers, function( a_index, a_item ) {
						var checked = item.options.checked[a_index] ? 'checked=checked' : '';
						question_content += '<div class="answer"><input type="checkbox" name="" value="" ' + checked + ' />';
						question_content += '<input class="component-checkbox-answer wide" type="text" value="' + a_item + '" name="" /><span class="remove-quiz-item"><i class="fa fa-trash-o"></i></span></div>';
					} );

					question_content += '</div>';
					question_content += '<a class="add-quiz-item">' + _coursepress.unit_builder_add_answer_label + '</a>';

					break;
				case 'short':
					//question_type = 'Short Answer';
					//
					//question_content += '<div class="answer-group">';
					//question_content += '<label>Placeholder:</label>';
					//question_content += '<div class="placeholder"><input type="text" name="" value="Placeholder" />';
					//question_content += '</div>';

					break;
				case 'long':
					//question_type = 'Long Answer';
					//
					//question_content += '<div class="answer-group">';
					//question_content += '<label>Placeholder:</label>';
					//question_content += '<div class="placeholder"><input type="text" name="" value="Placeholder" />';
					//question_content += '</div>';

					break;

			}
			question_content += '</div>'; // .question-answer

			// Same for all
			content += '<label class="wide" data-key="label">' +
				'<span class="label">' + question_type + ':</span>' +
				'</label>';
			content += '<textarea>' + item.question + '</textarea>';

			content += question_content;


			content += '</div>';
		} );

		return content;

	};

	CoursePress.Helpers.Module.quiz.update_meta = function( quiz_el ) {

		var cid = $( quiz_el).attr('data-cid');
		var questions = {};

		var module = CoursePress.UnitBuilder.module_collection._byId[ cid ];

		var el_questions = $( quiz_el).find('.quiz-question');

		$.each( el_questions, function( index, item ) {

			var answers;
			questions[index] = {
				'type': $( item).attr('data-type'),
				'question': $( item).find('textarea').val(),
				'options': {}
			};

			switch( questions[index].type ) {

				case 'single':
					questions[index].options['answers'] = [];
					questions[index].options['checked'] = [];
					answers = $( item).find('.answer-group .answer');
					$.each( answers, function( a_idx, a_item ) {
						questions[index].options['answers'][a_idx] = $( a_item).find('[type="text"]').val();
						questions[index].options['checked'][a_idx] = $( a_item).find('[type="radio"]').is( ':checked' );
					});

					break;

				case 'multiple':

					questions[index].options['answers'] = [];
					questions[index].options['checked'] = [];
					answers = $( item).find('.answer-group .answer');
					$.each( answers, function( a_idx, a_item ) {
						questions[index].options['answers'][a_idx] = $( a_item).find('[type="text"]').val();
						questions[index].options['checked'][a_idx] = $( a_item).find('[type="checkbox"]').is( ':checked' );
					});

					break;

				case 'short':
					break;

				case 'long':
					break;

			}

		} );

		module.set_meta('questions', questions);
		module.set( 'flag', 'dirty' );

	};

	CoursePress.Helpers.Module.quiz.bind_add_item = function() {
		$('.quiz-question .add-quiz-item').off( 'click' );
		$('.quiz-question .add-quiz-item').on( 'click', function() {
			var el = this;
			var question = $( el).parents('.quiz-question')[0];
			var type = $( question).attr('data-type');

			var input = 'single' === type ? 'radio' : 'checkbox';
			var css_class = 'single' === type ? 'component-radio-answer wide' : 'component-checkbox-answer wide';

			var input_name = $( question).attr('data-type') + '-' + $( question).attr('data-id');

			var content = '<div class="answer">' +
				'<input type="' + input + '" value="" name="' + input_name + '">' +
				'<input type="text" name="" value="" class="' + css_class + '">' +
				'<span class="remove-quiz-item"><i class="fa fa-trash-o"></i></span>' +
				'</div>';

			$(question).find('.answer-group').append( content );
			CoursePress.Helpers.Module.quiz.bind_remove_item();
			CoursePress.Helpers.Module.quiz.bind_checkboxes();
			CoursePress.Helpers.Module.quiz.bind_textboxes();
		} );
	};

	CoursePress.Helpers.Module.quiz.bind_checkboxes = function() {
		$('.quiz-question [type="checkbox"], .quiz-question [type="radio"]').off( 'change' );
		$('.quiz-question [type="checkbox"], .quiz-question [type="radio"]').on( 'change', function() {
			var mod_el = $( this).parents('.module-holder')[0];
			CoursePress.Helpers.Module.quiz.update_meta( mod_el );
		} );
	};

	CoursePress.Helpers.Module.quiz.bind_textboxes = function() {
		$('.quiz-question [type="text"], .quiz-question textarea').off( 'keyup' );
		$('.quiz-question [type="text"], .quiz-question textarea').on( 'keyup', function() {
			var mod_el = $( this).parents('.module-holder')[0];
			CoursePress.Helpers.Module.quiz.update_meta( mod_el );
		} );
	};

	CoursePress.Helpers.Module.quiz.bind_remove_item = function() {
		$('.quiz-question .remove-quiz-item').off( 'click' );
		$('.quiz-question .remove-quiz-item').on( 'click', function() {
			var el = this;
			var parent = $( el).parents('.answer')[0];

			var mod_el = $( this).parents('.module-holder')[0];

			$( parent).detach();

			CoursePress.Helpers.Module.quiz.update_meta( mod_el );
		} );
	};

	CoursePress.Helpers.Module.quiz.bind_remove_question = function() {
		// Remove Quiz
		$('.quiz-question .quiz-question-remove').off( 'click' );
		$('.quiz-question .quiz-question-remove').on( 'click', function() {

			var el = this;
			var parent = $( el).parents( '.quiz-question')[0];
			var questions = $( parent).siblings('.quiz-question');
			var mod_el = $( this).parents('.module-holder')[0];

			$.each( questions, function( index, item ) {
				$( item).attr('class', '');
				$( item).addClass('quiz-question');
				$( item).addClass('question-' + (index+1));
				$( item).attr('data-id', (index+1));
			} );

			$( parent).detach();

			CoursePress.Helpers.Module.quiz.update_meta( mod_el );
		} );
	};


	CoursePress.Helpers.Module.quiz.bind_buttons = function() {
		CoursePress.Helpers.Module.quiz.bind_add_item();
		CoursePress.Helpers.Module.quiz.bind_remove_item();
		CoursePress.Helpers.Module.quiz.bind_remove_question();
		CoursePress.Helpers.Module.quiz.bind_checkboxes();
		CoursePress.Helpers.Module.quiz.bind_textboxes();
	};


	CoursePress.Helpers.Module.form.render_component = function( module ) {

		var form = module.get_meta('questions');
		if ( undefined === form || form.length <= 0 ) {
			return '';
		}

		var content = '';

		$.each( form, function( index, item ) {

			content += '<div class="quiz-question question-' + ( index + 1 ) + '" data-id="' + (index + 1) + '" data-type="' + item.type + '" style="position: relative; border: 1px solid rgba(0,0,0,0.2); margin-top: 10px; padding: 5px;">';
			content += '<div class="quiz-question-remove" style="position: absolute; top:5px; right:5px; font-weight: bolder; font-size: 1.2em; cursor: pointer;">X</div>';

			var question_type = '';
			var question_content = '<div class="question-answer">';
			switch( item.type ) {

				case 'short':
					question_type = _coursepress.unit_builder.question_type.short;
					
					question_content += '<div class="answer-group">';
					question_content += '<label data-key="label" class="wide">';
					question_content += '<span class="label">' + _coursepress.unit_builder_form_pleaceholder_label + '</span>';
					question_content += '<span class="description">' + _coursepress.unit_builder_form_pleaceholder_desc + '</span>';
					question_content += '<div class="placeholder"><input class="component-placeholder-text wide" type="text" name="" value="'+ item.placeholder +'" />';
					question_content += '</label>';
					question_content += '</div>';
					question_content += '</div>';


					break;
				case 'long':
					question_type = _coursepress.unit_builder.question_type.long;
					
					question_content += '<div class="answer-group">';
					question_content += '<label data-key="label" class="wide">';
					question_content += '<span class="label">' + _coursepress.unit_builder_form_pleaceholder_label + '</span>';
					question_content += '<span class="description">' + _coursepress.unit_builder_form_pleaceholder_desc + '</span>';
					question_content += '<div class="placeholder"><input class="component-placeholder-text wide" type="text" name="" value="'+ item.placeholder +'" />';
					question_content += '</label>';
					question_content += '</div>';
					question_content += '</div>';


					break;
				case 'selectable':
					question_type = _coursepress.unit_builder.question_type.selectable;

					question_content += '<div class="answer-group">';

					item.options.answers = item.options.answers || [];
					$.each( item.options.answers, function( a_index, a_item ) {
						var checked = item.options.checked[a_index] ? 'checked=checked' : '';
						question_content += '<div class="answer"><input type="radio" name="question' + ( index + 1 ) + '" value="" ' + checked + ' />';
						question_content += '<input class="component-select-answer wide" type="text" value="' + a_item + '" name="" /><span class="remove-form-item"><i class="fa fa-trash-o"></i></span></div>';
					} );

					question_content += '</div>';
					question_content += '<a class="add-form-item">' + _coursepress.unit_builder_add_answer_label + '</a>';

					break;

			}
			question_content += '</div>'; // .question-answer

			// Same for all
			content += '<label class="wide" data-key="label">' +
				'<span class="label">' + question_type + ':</span>' +
				'</label>';
			content += '<textarea>' + item.question + '</textarea>';

			content += question_content;


			content += '</div>';
		} );

		return content;

	};

	CoursePress.Helpers.Module.form.update_meta = function( form_el ) {

		var cid = $( form_el).attr('data-cid');
		var questions = {};

		var module = CoursePress.UnitBuilder.module_collection._byId[ cid ];

		var el_questions = $( form_el).find('.quiz-question');

		$.each( el_questions, function( index, item ) {

			var answers;
			questions[index] = {
				'type': $( item).attr('data-type'),
				'question': $( item).find('textarea').val(),
				'options': {}
			};

			switch( questions[index].type ) {

				case 'single':
					questions[index].options['answers'] = [];
					questions[index].options['checked'] = [];
					answers = $( item).find('.answer-group .answer');
					$.each( answers, function( a_idx, a_item ) {
						questions[index].options['answers'][a_idx] = $( a_item).find('[type="text"]').val();
						questions[index].options['checked'][a_idx] = $( a_item).find('[type="radio"]').is( ':checked' );
					});

					break;

				case 'multiple':

					questions[index].options['answers'] = [];
					questions[index].options['checked'] = [];
					answers = $( item).find('.answer-group .answer');
					$.each( answers, function( a_idx, a_item ) {
						questions[index].options['answers'][a_idx] = $( a_item).find('[type="text"]').val();
						questions[index].options['checked'][a_idx] = $( a_item).find('[type="checkbox"]').is( ':checked' );
					});

					break;

				case 'short':
					questions[index]['placeholder'] = $( item).find('.answer-group .placeholder input').val();
					break;

				case 'long':
					questions[index]['placeholder'] = $( item).find('.answer-group .placeholder input').val();
					break;

				case 'selectable':
					questions[index].options['answers'] = [];
					questions[index].options['checked'] = [];
					answers = $( item).find('.answer-group .answer');
					$.each( answers, function( a_idx, a_item ) {
						questions[index].options['answers'][a_idx] = $( a_item).find('[type="text"]').val();
						questions[index].options['checked'][a_idx] = $( a_item).find('[type="radio"]').is( ':checked' );
					});

					break;

			}

		} );

		module.set_meta('questions', questions);
		module.set( 'flag', 'dirty' );

	};

	CoursePress.Helpers.Module.form.bind_add_item = function() {
		$('.quiz-question .add-form-item').off( 'click' );
		$('.quiz-question .add-form-item').on( 'click', function() {
			var el = this;
			var question = $( el).parents('.quiz-question')[0];
			var type = $( question).attr('data-type');

			var input = 'single' === type || 'selectable' === type ? 'radio' : 'checkbox';
			var css_class = 'single' === type ? 'component-radio-answer wide' : 'component-checkbox-answer wide';
			if('selectable' === type) css_class = 'component-select-answer wide'

			var input_name = $( question).attr('data-type') + '-' + $( question).attr('data-id');

			var content = '<div class="answer">' +
				'<input type="' + input + '" value="" name="' + input_name + '">' +
				'<input type="text" name="" value="" class="' + css_class + '">' +
				'<span class="remove-form-item"><i class="fa fa-trash-o"></i></span>' +
				'</div>';

			$(question).find('.answer-group').append( content );
			CoursePress.Helpers.Module.form.bind_remove_item();
			CoursePress.Helpers.Module.form.bind_checkboxes();
			CoursePress.Helpers.Module.form.bind_textboxes();
		} );
	};

	CoursePress.Helpers.Module.form.bind_checkboxes = function() {
		$('.quiz-question [type="checkbox"], .quiz-question [type="radio"]').off( 'change' );
		$('.quiz-question [type="checkbox"], .quiz-question [type="radio"]').on( 'change', function() {
			var mod_el = $( this).parents('.module-holder')[0];
			CoursePress.Helpers.Module.form.update_meta( mod_el );
		} );
	};

	CoursePress.Helpers.Module.form.bind_textboxes = function() {
		$('.quiz-question [type="text"], .quiz-question textarea').off( 'keyup' );
		$('.quiz-question [type="text"], .quiz-question textarea').on( 'keyup', function() {
			var mod_el = $( this).parents('.module-holder')[0];
			CoursePress.Helpers.Module.form.update_meta( mod_el );
		} );
	};

	CoursePress.Helpers.Module.form.bind_remove_item = function() {
		$('.quiz-question .remove-form-item').off( 'click' );
		$('.quiz-question .remove-form-item').on( 'click', function() {
			var el = this;
			var parent = $( el).parents('.answer')[0];

			var mod_el = $( this).parents('.module-holder')[0];

			$( parent).detach();

			CoursePress.Helpers.Module.form.update_meta( mod_el );
		} );
	};

	CoursePress.Helpers.Module.form.bind_remove_question = function() {
		// Remove Quiz
		$('.quiz-question .quiz-question-remove').off( 'click' );
		$('.quiz-question .quiz-question-remove').on( 'click', function() {

			var el = this;
			var parent = $( el).parents( '.quiz-question')[0];
			var questions = $( parent).siblings('.quiz-question');
			var mod_el = $( this).parents('.module-holder')[0];

			$.each( questions, function( index, item ) {
				$( item).attr('class', '');
				$( item).addClass('quiz-question');
				$( item).addClass('question-' + (index+1));
				$( item).attr('data-id', (index+1));
			} );

			$( parent).detach();

			CoursePress.Helpers.Module.form.update_meta( mod_el );
		} );
	};

	CoursePress.Helpers.Module.form.bind_buttons = function() {
		CoursePress.Helpers.Module.form.bind_add_item();
		CoursePress.Helpers.Module.form.bind_remove_item();
		CoursePress.Helpers.Module.form.bind_remove_question();
		CoursePress.Helpers.Module.form.bind_checkboxes();
		CoursePress.Helpers.Module.form.bind_textboxes();
	};	

	/**
	 * Delete page/section.
	 */
	CoursePress.Helpers.Module.delete_page = function( e, unit_id, page ) {
		var nonce = $( '#unit-builder' ).attr( 'data-nonce' );
		CoursePress.UnitBuilder.module_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=modules_update_delete_section&course_id=' + _coursepress.course_id + '&unit_id=' + CoursePress.UnitBuilder.activeUnitID + '&page=' + CoursePress.UnitBuilder.activePage + '&wp_nonce=' + nonce + '&page='+page;
		Backbone.sync( 'update', CoursePress.UnitBuilder.module_collection, {
			success: function( response ) {
				$( '#unit-builder' ).attr( 'data-nonce', response[ 'nonce' ] );
			},
			'error': function() {
			}
		} );
	}

	CoursePress.Helpers.Module.save_unit = function( e, custom_event ) {
		$( '.unit-buttons .unit-save-button' ).prepend( '<i class="fa fa-spinner fa-spin save-progress"></i> ' );
		var nonce = $( '#unit-builder' ).attr( 'data-nonce' );
		var form = $( "#unit-builder" ).closest( "form" );
		var requireds = $( ".component-checkbox-answer, .component-radio-answer, .component-select-answer", form );
		/**
		 * Check option labels
		 */
		if ( 0 < requireds.length ) {
			var errors = [];
			var title = '';
			$.each( requireds, function( index, element ) {
				e = $(element);
				if ( "" === e.val() ) {
					module_title = $(".module-title .module-title-text", e.closest( '.module-holder' ) ).val();
					if ( title !== module_title ) {
						errors.push( "- " + module_title );
						title = module_title;
					}
				}
			});
			if ( 0 < errors.length ) {
				$( '.save-progress' ).detach();
				alert( _coursepress.unit_builder_form.messages.required_fields + "\n" + errors.join( "\n" ) );
				return false;
			}
		}
		// Save modules first... just in case the unit is deleted to avoid orphans
		CoursePress.UnitBuilder.module_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=modules_update&course_id=' + _coursepress.course_id + '&unit_id=' + CoursePress.UnitBuilder.activeUnitID + '&page=' + CoursePress.UnitBuilder.activePage + '&wp_nonce=' + nonce + '&x=1';
		Backbone.sync( 'update', CoursePress.UnitBuilder.module_collection, {
			success: function( response ) {
				$( '#unit-builder' ).attr( 'data-nonce', response[ 'nonce' ] );
			},
			'error': function() {
			}
		} );

		// Now update the units
		CoursePress.UnitBuilder.unit_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=units_update&course_id=' + _coursepress.course_id + '&wp_nonce=' + nonce;
		Backbone.sync( 'update', CoursePress.UnitBuilder.unit_collection, {
			success: function( response ) {
				$( '.save-progress' ).detach();
				nonce = response[ 'nonce' ];
				$( '#unit-builder' ).attr( 'data-nonce', nonce );
				CoursePress.UnitBuilder.unit_collection.trigger( custom_event, CoursePress.UnitBuilder.unit_collection );
				CoursePress.Helpers.Module.unit_show_message( _coursepress.unit_builder_form.messages.successfully_saved, 'success' );
			},
			error: function() {
				$( '.save-progress' ).detach();
				$( e.currentTarget ).prepend( '<i class="fa fa-info-circle save-progress"></i> ' );
				CoursePress.Helpers.Module.unit_show_message( _coursepress.unit_builder_form.messages.error_while_saving, 'error' );
			}
		} );

		// Reset URL
		CoursePress.UnitBuilder.unit_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=units&course_id=' + _coursepress.course_id;

		/**
		 * Add message
		 */
		CoursePress.Helpers.Module.unit_show_message( _coursepress.unit_builder_form.messages.saving_unit, 'info' );
	};

	CoursePress.Helpers.Module.unit_add_show_message = function( message, notice_class ) {
		$( ".section.unit-builder-components .notice" ).detach();
		$( ".section.unit-builder-components .description" ).after( '<div class="notice notice-' + notice_class + '"><p>'+message+'</p></div>' );
		if ( "success" === notice_class ) {
			setTimeout(function(){ $( ".section.unit-builder-components .notice" ).fadeOut(); }, 3000);
		}
	}

	CoursePress.Helpers.Module.unit_show_message = function( message, notice_class ) {
		$( ".unit-builder-header .unit-buttons .notice, .unit-builder-footer .unit-buttons .notice" ).detach();
		$( ".unit-builder-header .unit-buttons, .unit-builder-footer .unit-buttons" ).prepend( '<div class="notice notice-' + notice_class + '"><p>'+message+'</p></div>' );
		if ( "success" === notice_class ) {
			setTimeout(function(){ $( ".unit-builder-header .unit-buttons .notice, .unit-builder-footer .unit-buttons .notice" ).fadeOut(); }, 3000);
		}
	}

	CoursePress.Helpers.Module.toggle_unit_state = function() {
		var nonce = $( '#unit-builder' ).attr( 'data-nonce' );

		this.switch = function( state, unit_id, unit_ref ) {
			if ( 'publish' === state ) {
				$( '#unit-live-toggle' ).removeClass( 'off' );
				$( '#unit-live-toggle' ).addClass( 'on' );
				$( '#unit-live-toggle-2' ).removeClass( 'off' );
				$( '#unit-live-toggle-2' ).addClass( 'on' );
			} else {
				$( '#unit-live-toggle' ).removeClass( 'on' );
				$( '#unit-live-toggle' ).addClass( 'off' );
				$( '#unit-live-toggle-2' ).removeClass( 'on' );
				$( '#unit-live-toggle-2' ).addClass( 'off' );
			}

			var current_state = CoursePress.UnitBuilder.unit_collection._byId[ unit_ref ].get( 'post_status' );

			if ( current_state !== state ) {
				CoursePress.UnitBuilder.unit_collection._byId[ unit_ref ].set( 'post_status', state );
				CoursePress.UnitBuilder.unit_collection._byId[ unit_ref ].trigger( 'change', CoursePress.UnitBuilder.unit_collection._byId[ unit_ref ] );

				// Toggle the dot
				$( '.unit-builder-tabs [data-tab="' + unit_id + '"]' ).removeClass( 'unit-draft' );
				$( '.unit-builder-tabs [data-tab="' + unit_id + '"]' ).removeClass( 'unit-live' );
				if ( 'publish' === state ) {
					$( '.unit-builder-tabs [data-tab="' + unit_id + '"]' ).addClass( 'unit-live' );
				} else {
					$( '.unit-builder-tabs [data-tab="' + unit_id + '"]' ).addClass( 'unit-draft' );
				}
			}
		};

		var self = this;

		var unit_id = CoursePress.UnitBuilder.activeUnitID;
		var unit_ref = CoursePress.UnitBuilder.activeUnitRef;
		var state = CoursePress.UnitBuilder.unit_collection._byId[ unit_ref ].get( 'post_status' );
		state = 'publish' === state ? 'draft' : 'publish';

		CoursePress.UnitBuilder.unit_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=unit_toggle&course_id=' + _coursepress.course_id + '&wp_nonce=' + nonce + '&state=' + state + '&unit_id=' + unit_id;
		Backbone.sync( 'update', CoursePress.UnitBuilder.unit_collection, {
			success: function( response ) {
				self.switch( response[ 'post_status' ], unit_id, unit_ref );
				$( '#unit-builder' ).attr( 'data-nonce', response[ 'nonce' ] );
				CoursePress.UnitBuilder.activeUnitStatus = response[ 'post_status' ];
			},
			error: function( response ) {
				self.switch( response[ 'post_status' ], unit_id, unit_ref );
				$( '#unit-builder' ).attr( 'data-nonce', response[ 'nonce' ] );
			}
		} );

		// Reset URL
		CoursePress.UnitBuilder.unit_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=units&course_id=' + _coursepress.course_id;
	};

	// Start Rendering the Module
	CoursePress.Helpers.Module.render_module = function( module, current_order ) {
		var types = _coursepress.unit_builder_module_types;
		var labels = _coursepress.unit_builder_module_labels;
		var content;
		var data;

		if ( module.module_type() && _coursepress.unit_builder_templates[ module.module_type() ].trim().length > 0 ) {
			data = JSON.parse( _coursepress.unit_builder_templates[ module.module_type() ] );
		}

		if ( undefined === data || undefined === _coursepress.unit_builder_module_types[ data[ 'type' ] ] ) {
			return '';
		}

		// Replace template data
		data[ 'id' ] = module.get( 'ID' );
		data[ 'title' ] = module.get( 'post_title' );
		data[ 'duration' ] = module.get_meta( 'duration' );
		data[ 'type' ] = module.module_type();
		data[ 'mode' ] = types[ data[ 'type' ] ][ 'mode' ];
		data[ 'show_title' ] = module.fix_boolean( module.get_meta( 'show_title' ) );
		data[ 'mandatory' ] = module.fix_boolean( module.get_meta( 'mandatory' ) );
		data[ 'assessable' ] = module.fix_boolean( module.get_meta( 'assessable' ) );
		data[ 'minimum_grade' ] = module.get_meta( 'minimum_grade', 100 );
		data[ 'allow_retries' ] = module.fix_boolean( module.get_meta( 'allow_retries' ) );
		data[ 'retry_attempts' ] = module.get_meta( 'retry_attempts', 0 );
		data[ 'use_timer' ] = module.fix_boolean( module.get_meta( 'use_timer' ) );
		data['instructor_assessable'] = module.fix_boolean( module.get_meta( 'instructor_assessable' ) );
		var post_content = module.get( 'post_excerpt' );
		post_content = post_content && post_content.length > 0 ? post_content : module.get( 'post_content' );
		post_content = _.escape(post_content);

		data[ 'content' ] = post_content.trim();
		data[ 'order' ] = module.get_meta( 'order', 0 );
		data[ 'page' ] = module.get_meta( 'page', 1 );

		content = '<h3 class="module-holder-title ' + data[ 'type' ] + '"><span class="label">' + data[ 'title' ] + '</span><span class="module-type">' + types[ data[ 'type' ] ][ 'title' ] + '</span></h3>' +
		'<div class="module-holder ' + data[ 'type' ] + ' mode-' + data[ 'mode' ] + '" data-id="' + data[ 'id' ] + '" data-type="' + data[ 'type' ] + '" data-order="' + current_order + '" data-cid="' + module.cid + '">';

		var module_type = data['type'];

		function module_mandatory() {
			return '<label class="module-mandatory">' +
				'<input type="checkbox" name="meta_mandatory[' + module.cid + ']" value="1" ' + CoursePress.utility.checked(data['mandatory'], 1) + ' />' +
				'<span class="label">' + labels['module_mandatory'] + '</span>' +
				'<span class="description">' + labels['module_mandatory_desc'] + '</span>' +
				'</label>';
		}

		function module_assessable() {
			return '<label class="module-assessable">' +
				'<input type="checkbox" data-target=".module-minimum-grade" name="meta_assessable[' + module.cid + ']" value="1" ' + CoursePress.utility.checked(data['assessable'], 1) + ' />' +
				'<span class="label">' + labels['module_assessable'] + '</span>' +
				'<span class="description">' + labels['module_assessable_desc'] + '</span>' +
				'</label>';
		}

		function module_use_timer() {
			return '<label class="module-use-timer">' +
				'<input type="checkbox" data-target=".module-duration" name="meta_use_timer[' + module.cid + ']" value="1" ' + CoursePress.utility.checked(data['use_timer'], 1) + ' />' +
				'<span class="label">' + labels['module_use_timer'] + '</span><br />' +
				'<span class="description">' + labels['module_use_timer_desc'] + '</span>' +
				'</label>';
		}

		function module_allow_retries() {
			return '<label class="module-allow-retries">' +
				'<input type="checkbox" name="meta_allow_retries[' + module.cid + ']" value="1" ' + CoursePress.utility.checked(data['allow_retries'], 1) + ' />' +
				'<span class="label">' + labels['module_allow_retries'] + '</span>' +
				'<input type="number" name="meta_retry_attempts" value="' + data['retry_attempts'] + '" min="0" class="small-text" />' +
				'<span class="description">' + labels['module_allow_retries_desc'] + '</span>' +
				'</label>';
		}

		function module_minimum_grade() {
			return '<label class="module-minimum-grade">' +
				'<span class="label">' + labels['module_minimum_grade'] + '</span>' +
				'<input type="number" name="meta_minimum_grade" value="' + data['minimum_grade'] + '" min="0" max="100" class="small-text" />' +
				'<span class="description">' + labels['module_minimum_grade_desc'] + '</span>' +
				'</label>';
		}

		// Display the body of the module?
		if ( ( types[ data[ 'type' ] ][ 'body' ] && 'hidden' !== types[ data[ 'type' ] ][ 'body' ] ) || !types[ data[ 'type' ] ][ 'body' ] ) {

			content += '<div class="module-header">';

			content += '<div class="module module-title"><h4 class="label">' + labels[ 'module_title' ] + '</h4>' +
				'<span class="description">' + labels[ 'module_title_desc' ] + '</span>' +
				'<label class="show-title">' +
				'<input type="checkbox" class="show-title" name="meta_show_title[' + module.cid + ']" value="1" ' + CoursePress.utility.checked( data[ 'show_title' ], 1 ) + ' data-target=".module-title-text"/>' +
				labels[ 'module_show_title_desc' ] + '</label>' +

				'<input class="module-title-text" type="text" name="post_title" value="' + data[ 'title' ] + '" /></div>';

			content += '<input type="hidden" name="meta_module_type" value="' + data[ 'type' ] + '" />';

			// Only for user inputs (or discussion)
			if ( 'input' === data[ 'mode' ] || 'discussion' === data[ 'type' ] ) {

				// Mandatory
				content += module_mandatory();

				// Assessable
				content += module_assessable();

				if ( 'input' === data[ 'mode' ] ) {
					// Use Timer - For Quizzes
					//if ('input-quiz' === data['type']) {
					if ( module_type.match( /input/ ) != null ) {
						content += module_use_timer();
					}

					// Allow Retries
					content += module_allow_retries();

					// Minimum Grade
					content += module_minimum_grade();
				}
			}

			if( 'video' === data[ 'type' ] )
			{
				// Use Timer
				content += module_use_timer();

				// Allow Retries
				content += module_allow_retries();
			}

			content +=
				'<div class="module module-duration"><h4 class="div">' + labels[ 'module_duration' ] + '</h4>' +
				'<input type="text" name="meta_duration" value="' + data[ 'duration' ] + '" /></div>';

			if ( 'input-upload' === module_type ) {

					content += '<label class="module-assessable-2">'
						+ '<input type="checkbox" name="meta_instructor_assessable[' + module.cid + ']" value="1" ' + CoursePress.utility.checked(data['instructor_assessable'], 1) + ' />'
						+ '<span class="label">' + labels.module_instructor_assessable + '</span><br />'
						+ '<span class="description">' + labels.module_instructor_assessable_desc + '</span>'
						+ '</label>';
			}

			// Excerpt
			if ( ( types[ data[ 'type' ] ][ 'excerpt' ] && 'hidden' !== types[ data[ 'type' ] ][ 'excerpt' ] ) || !types[ data[ 'type' ] ][ 'excerpt' ] ) {

				var textarea_name = '';
				// Use timestamps to make sure we get fresh editors each time we render... redundant editors are deleted later
				if ( 0 === parseInt( data[ 'id' ] ) || _.isNaN( parseInt( data[ 'id' ] ) ) ) {
					textarea_name = 'post_content_' + new Date().getTime();
				} else {
					textarea_name = 'post_content_' + data[ 'id' ] + '_' + new Date().getTime();
				}

				var textareaID = textarea_name;

				var content_label = 'input' === data[ 'mode' ] ? labels[ 'module_question' ] : labels[ 'module_content' ];
				var content_descrtiption = 'input' === data[ 'mode' ] ? labels[ 'module_question_desc' ] : labels[ 'module_content_desc' ];
				var editor_height = data[ 'editor_height' ] ? 'data-height="' + data[ 'editor_height' ] + '"' : '';

				content += '<div class="module module-excerpt">' +
				'<h4 class="label">' + content_label + '</h4>' +
				'<span class="description">' + content_descrtiption + '</span>' +
				'<textarea class="editor" name="' + textarea_name + '" id="' + textareaID + '" ' + editor_height + '>' + data[ 'content' ] + '</textarea>' +
				'</div>';
			}

			// Now it gets tricky...
			content += '</div>';

			// RENDER COMPONENTS
			content += '<div class="module module-components">' +
			CoursePress.Helpers.Module.render_components( module, data ) +
			'</div>';

		}

		// Delete Module
		content += '<div class="unit-buttons"><div class="button unit-delete-module-button"><i class="fa fa-trash-o"></i> ' + labels[ 'module_delete' ] + '</div></div>';

		content += '</div>';

		return content;
	};

	CoursePress.Helpers.Module.render_components = function( module, data ) {
		var content = '';
		var components = _.isArray( data[ 'components' ] ) ? data[ 'components' ] : [];
		var component_data = {};

		// Deal with each components...
		$.each( components, function( key, component ) {

			var label = component[ 'label' ] ? component[ 'label' ] : '';
			var description = component[ 'description' ] ? component[ 'description' ] : '';
			var label_class = component[ 'class' ] ? 'class="' + component[ 'class' ] + '"' : '';
			var component_key = key;
			var component_selector = 'module-component-' + component_key;

			content += '<div class="module module-component ' + component_selector + '">' +
			'<label data-key="label" ' + label_class + '>' +
			'<span class="label">' + label + '</span>' +
			'<span class="description">' + description + '</span></label>';

			var items = _.isArray( component[ 'items' ] ) ? component[ 'items' ] : [];

			// Deal with each item of the components
			$.each( items, function( idx, item ) {

				var item_type = item[ 'type' ] ? item[ 'type' ] : '';
				var attr, text, name, answers, selected, placeholder, container_class, label, value;

				switch ( item_type ) {
					case 'text-input':
					case 'number-input':
						var meta_value = item[ 'name' ].replace( 'meta_', '' );
						meta_value = module.get_meta( meta_value );
						attr = item[ 'name' ] ? ' name="' + item[ 'name' ] + '"' : '';
						attr += item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';
						label = item[ 'label' ] ? item[ 'label' ] : '';
						var label_tag = item[ 'label_tag' ] ? item[ 'label_tag' ] : '';
						placeholder = item[ 'placeholder' ] ? item[ 'placeholder' ] : '';
						var input_type = item_type == 'number-input' ? 'number' : 'text';

						if ( label.length > 1 ) {
							content += '<' + label_tag + '>' + label + '</' + label_tag + '>';
						}

						content += '<input type="' + input_type + '"' + attr + ' value="' + meta_value + '" placeholder="' + placeholder + '" />';
						break;

					case 'text':
						attr = item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';
						text = item[ 'text' ] ? item[ 'text' ] : '';
						content += '<div' + attr + '>' + text + '</div>';
						break;

					case 'select-select':
					case 'radio-select':
						//var attr = item[ 'name' ] ? ' name="' + item[ 'name' ] + '[]"' : '';
						//attr += item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';
						name = item[ 'name' ] ? item[ 'name' ] : '';
						attr = item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';

						answers = module.get_meta( 'answers' );

						//answers = answers.length > 0 ? CoursePress.utility.unserialize( answers ) : item['answers'];
						answers = answers.length > 0 ? answers : item[ 'answers' ];

						selected = module.get_meta( 'answers_selected', parseInt( item[ 'selected' ] ) );
						content += '<div class="answer-group">';
						$.each( answers, function( index, answer ) {

							// Legacy answers
							if ( _.isNaN( parseInt( selected ) ) ) {
								selected = (selected === answer ? index : selected);
							}

							option_name = name + '_selected[' + module.cid + ']';
							content += '<div class="answer"><input type="radio" name="' + option_name + '" value="' + index + '" ' + CoursePress.utility.checked( parseInt( selected ), index ) + ' />';
							content += '<input type="text" ' + attr + ' value="' + answer + '" name="' + name + '[]" /> <span class="remove-item"></span><i class="fa fa-trash-o"></i></span></div>';
						} );
						content += '</div>';
						content += '<a class="add-item">' + _coursepress.unit_builder_add_answer_label + '</a>';
						break;

					case 'checkbox-select':
						name = item[ 'name' ] ? item[ 'name' ] : '';
						attr = item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';

						answers = module.get_meta( 'answers' );
						//answers = answers.length > 0 ? CoursePress.utility.unserialize( answers ) : item['answers'];
						answers = answers.length > 0 ? answers : item[ 'answers' ];

						selected = module.get_meta( 'answers_selected' );
						//selected = selected.length > 0 ? CoursePress.utility.unserialize( selected ) : item['selected'];
						selected = selected.length > 0 ? selected : item[ 'selected' ];

						// Deal with legacy
						if ( _.isNaN( parseInt( selected[ 0 ] ) ) ) {
							$.each( selected, function( index, item ) {
								selected[ index ] = _.indexOf( answers, item );
							} );
						}

						content += '<div class="answer-group">';
						$.each( answers, function( index, answer ) {
							var checked = _.indexOf( selected, index ) > -1 ? 'checked="checked"' : '';
							option_name = name + '_selected[' + module.cid + '][]';
							content += '<div class="answer"><input type="checkbox" name="' + option_name + '" value="' + index + '" ' + checked + ' />';
							content += '<input type="text" ' + attr + ' value="' + answer + '" name="' + name + '[]" /> <span class="remove-item"><i class="fa fa-trash-o"></i></span></div>';
						} );
						content += '</div>';
						content += '<a class="add-item">' + _coursepress.unit_builder_add_answer_label + '</a>';
						break;

					case 'media-caption-settings':
						container_class = item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';
						var option_class = item[ 'option_class' ] ? ' class="' + item[ 'option_class' ] + '"' : '';
						var show_caption = item[ 'label' ] ? item[ 'label' ] : '';
						var media_caption = item[ 'option_labels' ] ? item[ 'option_labels' ][ 'media' ] : '';
						var custom_caption = item[ 'option_labels' ] ? item[ 'option_labels' ][ 'custom' ] : '';
						placeholder = item[ 'placeholder' ] ? item[ 'placeholder' ] : '';
						var enable_name = item[ 'enable_name' ] ? item[ 'enable_name' ] : '';
						var option_name = item[ 'option_name' ] ? item[ 'option_name' ] : '';
						var option_text_name = item[ 'input_name' ] ? item[ 'input_name' ] : '';
						var no_caption = item[ 'no_caption' ] ? item[ 'no_caption' ] : '';

						var show_caption_value = module.get_meta( enable_name );
						var caption_type = module.get_meta( option_name );
						var caption_text = module.get_meta( option_text_name );

						enable_name = enable_name + '[' + module.cid + ']';
						option_name = option_name + '[' + module.cid + ']';

						content += '<div ' + container_class + '>' +
						'<label><input type="checkbox" value="1" name="' + enable_name + '" ' + CoursePress.utility.checked( show_caption_value, 1 ) + ' />' +
						'<span>' + show_caption + '</span></label>' +
						'<div ' + option_class + '>' +
						'<label><input type="radio" value="media" name="' + option_name + '" ' + CoursePress.utility.checked( caption_type, 'media' ) + ' />' +
						'<span>' + media_caption + '</span></label>' +
						'<div class="existing">' + no_caption + '</div>' +
						'<label><input type="radio" value="custom" name="' + option_name + '" ' + CoursePress.utility.checked( caption_type, 'custom' ) + ' />' +
						'<span>' + custom_caption + '</span></label><br />' +
						'<input type="text" placeholder="' + placeholder + '" value="' + caption_text + '" name="' + option_text_name + '" />' +
						'</div>' +
						'</div>';

						// Fetch caption asynchronously
						if ( component_data.media_url && component_data.media_url.length > 0 ) {
							CoursePress.utility.attachment_by_url( component_data.media_url, '.' + component_selector + ' .existing', no_caption );
							component_data.media_url = null; // Ready for the next time.
						}
						break;

					case 'media-browser':
						var media_type = item[ 'media_type' ] ? item[ 'media_type' ] : 'image';
						var class_value = item[ 'class' ] ? item[ 'class' ] : '';
						container_class = item[ 'container_class' ] ? item[ 'container_class' ] : '';
						var button_text = item[ 'button_text' ] ? item[ 'button_text' ] : '';
						placeholder = item[ 'placeholder' ] ? item[ 'placeholder' ] : '';
						name = item[ 'name' ] ? item[ 'name' ] : '';

						value = module.get_meta( name, '' );
						content += CoursePress.UI.browse_media_field( name, name, {
							value: value,
							type: media_type,
							container_class: container_class,
							textbox_class: class_value,
							placeholder: placeholder,
							button_text: button_text
						} );

						component_data.media_url = value;
						break;

					case 'checkbox':
						name = item[ 'name' ] ? item[ 'name' ] : '';
						label = item[ 'label' ] ? item[ 'label' ] : '';
						value = module.get_meta( name, '' );

						name = name + '[' + module.cid + ']';
						content += '<label class="normal"><input type="checkbox" value="1" name="' + name + '" ' + CoursePress.utility.checked( value, 1 ) + ' />' +
						'<span>' + label + '</span></label>';
						break;

					case 'action':
						var css_class = item['class'] || '';
						var action = item['action'] || '';
						var title = item['title'] || '';
						var dashicon = item['dashicon'] || '';

						content += '<div class="' + css_class + '" data-type="' + action + '"><a>';
						if ( dashicon ) {
							content += '<span class="dashicons dashicons-'+dashicon+'"></span>';
						}
						content += '</a>';
						if ( title ) {
							content += '<span class="element-label">'+title+'</span>';
						}
						content += '</div>';
						//content += '<span style="color:green">«- Developer note: Only multiple choice for now, will add others soon.</span>';

						break;

					case 'quiz':
						content += CoursePress.Helpers.Module.quiz.render_component( module );
						break;

					case 'form':
						content += CoursePress.Helpers.Module.form.render_component( module );
						break;

				}

			} );


			content += '</div>';

		} );


		return content;

	};

	/** Add the CoursePress Unit Builder Views **/
		// Parent View / Models / Collections
	CoursePress.Views.UnitBuilder = Backbone.View.extend( {
		initialize: function() {

			// Setup child views
			//this.tabView = new Backbone.View();
			//this.tabView.parentView = this;

			// Holds all the units for displaying
			this.unit_collection = new CoursePress.Collections.UnitTabs();
			this.unit_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=units&course_id=' + _coursepress.course_id;
			this.unit_collection.fetch();

			// Holds the modules for the current unit
			this.module_collection = new CoursePress.Collections.UnitModules();

			// Displays the tabs
			this.tabViewCollection = new CoursePress.Views.UnitTabViewCollection( {
				model: this.unit_collection,
				tagName: 'ul',
				className: 'sticky-tabs'
			} );
			this.tabViewCollection.parentView = this;

			// Displays the unit information
			this.headerView = new CoursePress.Views.UnitBuilderHeader();
			this.headerView.parentView = this;

			// Displays the content
			this.contentView = new CoursePress.Views.UnitBuilderBody( {
				model: this.module_collection,
				className: 'unit-builder-body'
			} );
			this.contentView.parentView = this;

			this.activePage = 1;
			this.totalPages = 1;
			this.activeUnitStatus = 'draft';

			this.activeModuleRef = '';

			// Render the container
			this.render();
		},
		events: {
			'click .unit-save-button': 'saveUnit',
			'change #unit-live-toggle': 'toggleUnitState',
			'change #unit-live-toggle-2': 'toggleUnitState',
			'click .unit-delete-module-button': 'deleteModule',
			'click .unit-delete-page-button': 'deletePage',
			'click .unit-delete-button': 'deleteUnit',
			'click .button-add-new-unit': 'newUnit',
			'click .unit-builder-tabs ul.sticky-tabs li': 'changeActive',
			'click .button-preview': 'toPreview'
		},
		render: function() {

			// Get the parent layout rendered first
			var template = _.template( $( '#unit-builder-template' ).html() );
			this.$el.html( template );

			this.$( '.unit-builder-tabs .sticky-wrapper .tabs' )
				.replaceWith( this.tabViewCollection.el );

			this.$( '.unit-builder-header' )
				.append( this.headerView.el );

			this.$( '.unit-builder-body' )
				.replaceWith( this.contentView.el );

			// UI
			$( '.sticky-wrapper-tabs' ).sticky( { topSpacing: 45 } );

			return this;
		},
		fetchModules: function( unit_id, page ) {

			this.module_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=modules&course_id=' + _coursepress.course_id + '&unit_id=' + unit_id + '&page=' + page;
			this.module_collection.fetch({
				success: function() {
					this.$('.unit-save-button').removeClass( 'disabled' );
				}
			});
			// Get the number of pages
			var meta = this.unit_collection._byId[ this.activeUnitRef ].get( 'meta' );
			this.totalPages = meta[ 'page_title' ] ? meta[ 'page_title' ].length : 1;
			this.totalPages = undefined === this.totalPages ? _.size( meta[ 'page_title' ] ) : 1;
			this.activeModuleRef = '';
		},
		userCap: function( cap ){
			var unit_caps = this.unit_collection._byId[ this.activeUnitRef ].get( 'user_cap' );
			return unit_caps[cap];
		},
		saveUnit: function( e ) {
			if ( $(e.target).hasClass('disabled' ) ) {
				return;
			}
			CoursePress.Helpers.Module.save_unit( e );
		},
		toggleUnitState: function( e ) {
			CoursePress.Helpers.Module.toggle_unit_state( e );
		},
		deleteModule: function( e ) {
			if ( window.confirm( _coursepress.unit_builder_delete_module_confirm ) ) {
				var el = e.currentTarget;
				var parent = $( el ).parents( '.module-holder' )[ 0 ];
				var model_ref = $( parent ).attr( 'data-cid' );
				this.module_collection.remove( model_ref );
				$( parent ).parents( '.group' ).detach();
			}
		},
		deletePage: function( e ) {
			var self = this;
			if ( ! window.confirm( _coursepress.unit_builder_delete_page_confirm ) ) {
				return;
			}
			var page = parseInt( this.activePage );
			// Trigger save
			CoursePress.Helpers.Module.delete_page( e, self.activeUnitID, page );
			/**
			 * reload window
			 */
			location.reload();
		},
		deleteUnit: function( e ) {

			if ( window.confirm( _coursepress.unit_builder_delete_unit_confirm ) ) {
				this.unit_collection.remove( this.activeUnitRef );

				//UI Update
				$( 'ul li[data-tab="' + this.activeUnitID + '"]' ).detach();
				$( $( '.unit-builder-body' )[ 0 ] ).empty();
				$( '.unit-detail input[type="text"]' ).val( '' );
				$( '.unit-detail input[type="checkbox"]' ).removeAttr( 'checked' );
				$( '#unit-live-toggle' ).removeClass( 'on' ).addClass( 'off' );
				$( '#unit-live-toggle-2' ).removeClass( 'on' ).addClass( 'off' );

				// Update Unit Count
				$( $( 'li[data-tab="units"] a' )[ 0 ] ).html( $( $( 'li[data-tab="units"] a' )[ 0 ] ).html().replace( /\d+/, $( '.unit-builder-tabs ul li' ).length ) );

				CoursePress.Helpers.Module.save_unit( e );
				var last_unit = this.tabViewCollection.$el.find( 'li' ).last();
				
				if ( last_unit && last_unit.length > 0 ) {
					last_unit.trigger( 'click', last_unit );
				}
			}
		},
		newUnit: function( e ) {
			this.is_add = true;
			var self = this;

			// Count current elements.
			var count = $( '.unit-builder-tabs .sticky-tabs li' ).length;

			var unit = new CoursePress.Models.Unit();
			unit.set_meta( 'unit_order', (count + 1) );
			unit.set_meta( 'page_title', { page_1: '' } );
			unit.set_meta( 'show_page_title', [ true ] );
			unit.set( 'post_title', _coursepress.unit_builder_new_unit_title );

			self.unit_collection.add( unit );
			CoursePress.Helpers.Module.save_unit( e, 'new_success' );

			var unitView = new CoursePress.Views.UnitTabView( { model: unit, tagName: 'li' } );
			var new_html = unitView.render().$el;

			$( '.button-add-new-unit .fa' ).removeClass( 'fa-plus-square' ).addClass( 'fa-spinner' ).addClass( 'fa-spin' );

			self.unit_collection.on( 'new_success', function() {
				self.unit_collection.fetch( {
					success: function() {
						$( '.button-add-new-unit .fa' ).removeClass( 'fa-spin' ).removeClass( 'fa-spinner' ).addClass( 'fa-plus-square' );
						$( '.unit-builder-tabs ul.sticky-tabs' ).append( new_html );
						CoursePress.Helpers.Module.refresh_ui();
						// Update Unit Count
						$( $( 'li[data-tab="units"] a' )[ 0 ] ).html( $( $( 'li[data-tab="units"] a' )[ 0 ] ).html().replace( /\d+/, $( '.unit-builder-tabs ul li' ).length ) );
					}
				} );

				self.unit_collection.off( 'new_success' );
			} );

		},
		changeActive: function( e ) {

			$( '#unit-builder .tab-tabs li' ).removeClass( 'active' );
			$( e.currentTarget ).addClass( 'active' );

			var model_id = $( e.currentTarget ).attr( 'data-tab' );
			// Get appropriate model
			var self = this;

			this.unit_collection.each( function( unit ) {
				if ( parseInt( model_id ) === parseInt( unit.get( 'ID' ) ) ) {
					CoursePress.Helpers.changeUnit( unit, self );
				}
			} );
		},

		toPreview: function( e ) {
			var preview_btn = $( e.currentTarget ),
				href = preview_btn.data( 'href' );
			preview_btn.attr( 'href', href + this.activeUnitID );
		}
	} );

	// Unit Tab View / Models / Collections

	CoursePress.Models.Unit = Backbone.Model.extend( {
		initialize: function() {
			this.url = _coursepress._ajax_url + '?action=unit_builder&task=unit_update&course_id=' + _coursepress.course_id + '&unit_id=' + CoursePress.UnitBuilder.activeUnitID;
			this.on( 'change', this.process_changed, this );
			this.on( 'sync', this.model_saved, this );
		},
		set_meta: function( key, value ) {

			key = key.replace( 'meta_', '' );

			var meta = this.get( 'meta' ) || {};

			meta[ key ] = value;

			this.set( 'flag', 'dirty' );
			this.set( 'meta', meta );
			this.trigger( 'change', this );
		},
		set_page_title: function( index, title ) {
			var meta = this.get( 'meta' ) || {};
			meta[ 'page_title' ] = meta[ 'page_title' ] || {};
			meta[ 'page_title' ][ 'page_' + index ] = title;
			this.set( 'meta', meta );
			this.trigger( 'change', this );
		},
		set_page_description: function( index, description ) {
			var meta = this.get( 'meta' ) || {};
			description = _.escape(description);
			meta[ 'page_description' ] = meta[ 'page_description' ] || {};
			meta[ 'page_description' ][ 'page_' + index ] = description;
			this.set( 'meta', meta );
			this.trigger( 'change', this );
		},
		set_page_image: function( index, image ) {
			var meta = this.get( 'meta' ) || {};
			meta[ 'page_feature_image' ] = meta[ 'page_feature_image' ] || {};
			meta[ 'page_feature_image' ][ 'page_' + index ] = image;
			this.set( 'meta', meta );
			this.trigger( 'change', this );
		},
		set_page_visibility: function( index, value ) {
			var meta = this.get( 'meta' ) || {};
			var idx = index - 1;
			if ( meta[ 'show_page_title' ] ) {
				meta[ 'show_page_title' ][ idx ] = value;
				this.set( 'meta', meta );
				this.trigger( 'change', this );
			}
		},
		get_page_title: function( index ) {
			var meta = this.get( 'meta' ) || {};
			if ( meta[ 'page_title' ] ) {
				return meta[ 'page_title' ][ 'page_' + index ];
			} else {
				return '';
			}
		},
		get_page_description: function( index ) {
			var meta = this.get( 'meta' ) || {};
			if ( meta[ 'page_description' ] ) {
				return meta[ 'page_description' ][ 'page_' + index ];
			} else {
				return '';
			}
		},
		get_page_image: function( index ) {
			var meta = this.get( 'meta' ) || {};
			if ( meta[ 'page_feature_image' ] ) {
				return meta[ 'page_feature_image' ][ 'page_' + index ];
			} else {
				return '';
			}
		},
		get_page_visibility: function( index ) {
			var meta = this.get( 'meta' ) || {};
			if ( meta[ 'show_page_title' ] ) {
				return meta[ 'show_page_title' ][ ( index - 1 ) ];
			} else {
				return true;
			}
		},
		process_changed: function() {
			this.set( 'flag', 'dirty' );
		},
		model_saved: function() {
		}

	} );
	CoursePress.Models.Module = Backbone.Model.extend( {
		initialize: function() {
			var nonce = $( '#unit-builder' ).attr( 'data-nonce' );
			this.url = _coursepress._ajax_url + '?action=unit_builder&task=module_add&course_id=' + _coursepress.course_id + '&unit_id=' + CoursePress.UnitBuilder.activeUnitID + '&wp_nonce=' + nonce;
			this.on( 'change', this.process_changed, this );
			this.on( 'sync', this.model_saved, this );
		},
		get_meta: function( key, default_value ) {

			key = key.replace( 'meta_', '' );

			if ( undefined === default_value ) {
				default_value = '';
			}

			var meta = this.get( 'meta' ) || {};
			var value = meta[ key ] ? meta[ key ] : default_value;

			var test_value = _.isString( value ) ? value.toLowerCase() : value;
			if ( test_value === 'yes' || test_value === 'on' || test_value === 'no' || test_value === 'off' ) {
				value = this.fix_boolean( value );
			}

			if ( ! meta.legacy_updated && ( value.length === 0 || value === false || value === 0 ) ) {
				value = this.get_legacy_meta( key, default_value );
			}

			return value;
		},
		get_legacy_meta: function( key, default_value ) {
			var meta, value;

			switch ( key ) {
				case 'duration':
					key = 'time_estimation';
					break;
				case 'show_title':
					key = 'show_title_on_front';
					break;
				case 'mandatory':
					key = 'mandatory_answer';
					break;
				case 'assessable':
					key = 'gradable_answer';
					break;
				case 'minimum_grade':
					key = 'minimum_grade_required';
					break;
				case 'allow_retries':
					meta = this.get( 'meta' );
					if ( meta[ 'limit_attempts' ] ) {
						value = meta[ 'limit_attempts' ][ 0 ];
						// Invert answer
						return !this.fix_boolean( value );
					} else {
						return default_value;
					}
					break;
				case 'retry_attempts':
					key = 'limit_attempts_value';
					break;
				case 'order':
					key = 'module_order';
					break;
				case 'page':
					key = 'module_page';
					break;
				case 'answers_selected':
					key = 'input-radio' === this.module_type() ? 'checked_answer' : 'checked_answers';
					break;
			}

			meta = this.get( 'meta' ) || {};
			value = meta[ key ] ? meta[ key ] : default_value;

			return value;
		},

		fix_legacy_module: function( new_mod_type ) {
			var self = this;
			var meta = self.get( 'meta' );
			self.set( 'flag', 'dirty' );

			// Fix meta that needs fixing
			meta.module_type = new_mod_type;

			if ( meta[ 'checked_answer' ] ) {
				meta.answers_selected = meta[ 'checked_answer' ];
			}
			if ( meta[ 'checked_answers' ] ) {
				meta.answers_selected = meta[ 'checked_answers' ];
			}

			if ( meta[ 'time_estimation' ] ) {
				meta.duration = meta[ 'time_estimation' ];
			}

			if ( meta[ 'show_title_on_front' ] ) {
				meta.show_title = self.fix_boolean( meta[ 'show_title_on_front' ] );
			}

			if ( meta[ 'mandatory_answer' ] ) {
				meta.mandatory = self.fix_boolean( meta[ 'mandatory_answer' ] );
			}

			if ( meta[ 'gradable_answer' ] ) {
				meta.assessable = self.fix_boolean( meta[ 'gradable_answer' ] );
			}

			if ( meta[ 'minimum_grade_required' ] ) {
				meta.minimum_grade = meta[ 'minimum_grade_required' ];
			}

			if ( meta[ 'limit_attempts' ] ) {
				var limited = self.fix_boolean( meta[ 'limit_attempts' ] );
				meta.allow_retries = !limited;
			}
			if ( meta[ 'limit_attempts_value' ] ) {
				meta.retry_attempts = meta[ 'limit_attempts_value' ];
			}

			self.set( 'meta', meta );

		},

		map_legacy_type: function( mod_type ) {
			var self = this;
			var legacy = {
				'audio_module': 'audio',
				'chat_module': 'chat',
				'checkbox_input_module': 'input-checkbox',
				'file_module': 'download',
				'file_input_module': 'input-upload',
				'image_module': 'image',
				'radio_input_module': 'input-radio',
				'page_break_module': 'section',
				'section_break_module': 'section',
				'text_module': 'text',
				'text_input_module': 'input-text',
				'textarea_input_module': 'input-textarea',
				'video_module': 'video'
			};

			if ( mod_type in legacy ) {
				// Fix the text input
				if ( 'text_module' && 'single' !== this.get_meta( 'checked_length', 'single' ) ) {
					mod_type = 'input-textarea';
				} else {
					mod_type = legacy[ mod_type ];
				}

				self.fix_legacy_module( mod_type );
			}

			return mod_type;
		},
		module_type: function() {
			return this.map_legacy_type( this.get_meta( 'module_type' ) );
		},
		fix_boolean: function( value ) {
			var test_value = _.isString( value ) ? value.toLowerCase() : value;
			return 1 === parseInt( test_value ) || 'on' === test_value || 'yes' === test_value || true === test_value;
		},
		set_meta: function( key, value ) {

			key = key.replace( 'meta_', '' );

			var meta = this.get( 'meta' ) || {};
			meta[ key ] = value;

			this.set( 'flag', 'dirty' );
			this.set( 'meta', meta );
			this.trigger( 'change', this );
		},
		from_template: function( template ) {

			var data = JSON.parse( _coursepress.unit_builder_templates[ template ] );
			this.set( 'ID', data[ 'id' ] );
			this.set( 'post_title', data[ 'title' ] );
			this.set_meta( 'duration', data[ 'duration' ] || '1:00' );
			this.set_meta( 'module_type', data[ 'type' ] );
			this.set_meta( 'mandatory', data[ 'mandatory' ] );
			this.set_meta( 'show_title', data[ 'show_title' ] || true );
			this.set_meta( 'assessable', data[ 'assessable' ] );
			this.set_meta( 'minimum_grade', data[ 'minimum_grade' ] );
			this.set_meta( 'allow_retries', data[ 'allow_retries' ] );
			this.set_meta( 'retry_attempts', data[ 'retry_attempts' ] );
			if ( 'input-quiz' === data[ 'type' ] ) {
				this.set_meta( 'use_timer', data[ 'use_timer' ] );
			}
			if ( 'input-form' === data[ 'type' ] ) {
				this.set_meta( 'use_timer', data[ 'use_timer' ] );
			}

			this.set( 'post_content', data['content'] || '' );
			this.set_meta( 'order', data[ 'order' ] );

			var self = this;
			$.each( data[ 'components' ], function( index, component ) {
				$.each( component[ 'items' ], function( idx, item ) {
					self.item_to_meta( item );
				} );
			} );

		},
		item_to_meta: function( item ) {
			var self = this;
			switch ( item[ 'type' ] ) {

				case 'media-browser':
				case 'text-input':
					self.set_meta( item[ 'name' ], '' );
					break;
				case 'checkbox-select':
				case 'select-select':
				case 'radio-select':
					self.set_meta( item[ 'name' ], item[ 'answers' ] );
					self.set_meta( item[ 'name' ] + '_selected', item[ 'selected' ] );
					break;
				case 'media-caption-settings':
					self.set_meta( item[ 'enable_name' ], false );
					self.set_meta( item[ 'option_name' ], 'media' );
					self.set_meta( item[ 'input_name' ], '' );
					break;
				case 'checkbox':
					self.set_meta( item[ 'name' ], false );
					break;
			}

		},
		model_saved: function( model ) {
			CoursePress.UnitBuilder.activeModuleRef = model.cid;
			CoursePress.UnitBuilder.gotoAdded = true;
		},
		process_changed: function() {
			CoursePress.UnitBuilder.activeModuleRef = this.cid;
			this.set( 'flag', 'dirty' );
		}


	} );

	CoursePress.Collections.UnitTabs = Backbone.Collection.extend( {
		model: CoursePress.Models.Unit
	} );

	CoursePress.Collections.UnitModules = Backbone.Collection.extend( {
		model: CoursePress.Models.Module
	} );

	// Single Tab View
	CoursePress.Views.UnitTabView = Backbone.View.extend( {

		render: function() {

			var post_status = this.model.get( 'post_status' );

			var meta = this.model.get( 'meta' );

			var variables = {
				unit_id: this.model.get( 'ID' ),
				unit_title: this.model.get( 'post_title' ),
				unit_live_class: 'publish' === post_status ? 'unit-live' : 'unit-draft',
				unit_active_class: this.first ? 'active' : '',
				unit_order: meta[ 'unit_order' ],
				unit_cid: this.model.cid
			};

			var template = _.template( $( '#unit-builder-tab-template' ).html() );

			this.$el = template( variables );

			return this;
		}

	} );

	// Tab Collection View
	CoursePress.Views.UnitTabViewCollection = Backbone.View.extend( {
		initialize: function() {
			this.model.on( 'sync', this.render, this );
		},
		events: {
			//"click li": "changeActive"
		},
		render: function() {

			var self = this;

			self.$el.empty();

			var first = 0, model_length = this.model.length;

			if ( this.parentView['is_add'] ) {
				first = model_length - 1;
			}

			this.model.each( function( unit, unit_index ) {
				var unitView = new CoursePress.Views.UnitTabView( { model: unit, tagName: 'li' } );
				unitView.first = first === unit_index;
				self.$el.append( unitView.render().$el );

				if ( first === unit_index ) {
					CoursePress.Helpers.changeUnit( unit, self );
				} else {
					self.parentView.contentView.initial = true;
					self.parentView.contentView.render();
				}

			} );

			this.model.trigger( 'rendered', this.model );

			return this;
		},
		changeActive: function( e ) {

			$( '#unit-builder .tab-tabs li' ).removeClass( 'active' );
			$( e.currentTarget ).addClass( 'active' );

			var model_id = $( e.currentTarget ).attr( 'data-tab' );
			// Get appropriate model
			var self = this;

			this.model.each( function( unit ) {
				if ( parseInt( model_id ) === parseInt( unit.get( 'ID' ) ) ) {
					CoursePress.Helpers.changeUnit( unit, self );
					$( 'body,html' ).animate( {
						scrollTop: $( '.section.unit-builder-header' ).offset().top - 20,
						duration: 200
					} );
				}
			} );

		}

	} );


	CoursePress.Helpers.changeUnit = function( unit, self, page ) {
		self = CoursePress.UnitBuilder;
		if ( undefined === page ) {
			page = 1;
		}

		self.activePage = page;

		self.headerView.template_variables.unit_id = unit.get( 'ID' );
		self.headerView.template_variables.unit_cid = unit.cid;
		self.headerView.template_variables.unit_title = unit.get( 'post_title' );
		self.headerView.template_variables.unit_content = unit.get( 'post_content' );
		var meta = unit.get( 'meta' );

		self.headerView.template_variables.unit_availability = meta.unit_availability;
		self.headerView.template_variables.unit_date_availability = meta.unit_date_availability ? meta.unit_date_availability : '';
		self.headerView.template_variables.unit_delay_days = meta.unit_delay_days ? meta.unit_delay_days : 0;
		self.headerView.template_variables.unit_feature_image = meta.unit_feature_image;

		var checked = meta.force_current_unit_completion;
		checked = 'on' === checked || true === checked || 1 === parseInt( checked ) ? 'checked="checked"' : '';
		self.headerView.template_variables.unit_force_completion_checked = checked;

		checked = meta.force_current_unit_successful_completion;
		checked = 'on' === checked || true === checked || 1 === parseInt( checked ) ? 'checked="checked"' : '';
		self.headerView.template_variables.unit_force_successful_completion_checked = checked;

		self.headerView.render();
		self.headerView.$el.find( '[name="meta_unit_availability"]' ).trigger( 'change', self.headerView );

		self.contentView.initial = true;
		self.contentView.render();

		// Trigger Module collection
		self.activeUnitID = unit.get( 'ID' );
		self.activeUnitRef = unit.cid;
		self.activeUnitStatus = unit.get( 'post_status' );
		self.fetchModules( self.activeUnitID, self.activePage );

		// Unit caps
		var user_cap = unit.get( 'user_cap' ),
			unit_info = self.$el.find( '.unit-builder-no-access' );

		if ( ! user_cap['coursepress_update_course_unit_cap'] ) {
			self.headerView.$el.hide();
			self.contentView.$el.hide();
			unit_info.show();
		} else {
			self.headerView.$el.show();
			self.contentView.$el.show();
			unit_info.hide();
		}

	};

	// Unit Header View
	CoursePress.Views.UnitBuilderHeader = Backbone.View.extend( {
		initialize: function() {
			this.template_variables = {
				unit_id: '',
				unit_cid: '',
				unit_title: '',
				unit_content: '',
				unit_feature_image: '',
				unit_availability: '',
				unit_date_availability: '',
				unit_delay_days: 0,
				unit_force_completion_checked: '',
				unit_force_successful_completion_checked: ''
			};

			this.render();

			CoursePress.Events.on( 'editor:keyup', this.editorChanged, this );
		},
		events: {
			'change .unit-detail input': 'fieldChanged',
			'keyup .unit-detail input[name=post_title]': 'updateTabTitle',
			'keyup .unit-detail .wp-unit-editor': 'editorChanged',
			'change #unit-live-toggle': 'toggleUnitState',
			'change #unit-live-toggle-2': 'toggleUnitState',
			'change #unit_feature_image': 'unitFeatureImageChange',
			'change [name="unit_feature_image-button"]': 'unitFeatureImageChange',
			'change [name="meta_unit_availability"]': 'toggleAvailability',
			'focus [name="meta_unit_date_availability"]' : 'setDate',
			'change select': 'fieldChanged'
			//'change #page_feature_image': 'featureImageChange',
			//'change [name=page_feature_image-button]': 'featureImageChange'
		},
		render: function() {
			var values = this.template_variables;
			if ( 0 === parseInt( values.unit_delay_days ) ) {
				values.unit_delay_days = '';
			}
			var template = _.template( $( '#unit-builder-header-template' ).html() );
			this.$el.html( template( values ) );
			$( '#unit_feature_image' ).val( values.unit_feature_image );
			this.updateUnitHeader();
			return this;
		},
		editorChanged: function( e ) {
			var the_id = e.id;
			var re = /^unit_description_\d+$/;
			if ( undefined === the_id ) {
				the_id = $( e.currentTarget ).attr('id');
			}
			if ( undefined !== the_id && re.test( the_id  ) ) {
				var el = $( '#' + the_id );
				var el_val = CoursePress.editor.content( the_id );
				var parent = $( el ).parents( '.unit-detail' )[ 0 ];
				var unit = this.parentView.unit_collection._byId[ $( parent ).attr( 'data-cid' ) ];
				el_val = _.escape(el_val);
				unit.set( 'post_content', el_val );
			}
		},
		fieldChanged: function( e ) {
			var el = $( e.currentTarget );
			var el_name = $( el ).attr( 'name' );
			if (
				el_name === 'unit_feature_image' ||
				el_name === 'unit_feature_image-button' ||
				el_name === 'page_feature_image' ||
				el_name === 'page_feature_image-button'
			) {
				return;
			}
			var el_val = $( el ).val();
			var parent = $( el ).parents( '.unit-detail' )[ 0 ];
			var unit = this.parentView.unit_collection._byId[ $( parent ).attr( 'data-cid' ) ];
			var type = $( el ).attr( 'type' );
			if ( 'checkbox' === type ) {
				el_val = $( el ).is( ':checked' );
			}
			if ( unit ) {
				if ( /meta_/.test( el_name ) ) {
					unit.set_meta( el_name, el_val );
				} else {
					unit.set( el_name, el_val );
				}
			}
		},
		unitFeatureImageChange: function( e ) {
			var el = $( e.currentTarget );
			var el_val = $( '#unit_feature_image' ).val();
			var parent = el.parents( '.unit-detail' ).first();
			var unit = this.parentView.unit_collection._byId[ parent.attr( 'data-cid' ) ];
			unit.set_meta( 'unit_feature_image', el_val );
		},
		updateTabTitle: function( e ) {
			$( '[data-tab="' + this.parentView.activeUnitID + '"] span' ).html( $( e.currentTarget ).val() );
		},
		toggleUnitState: function( e ) {
			CoursePress.Helpers.Module.toggle_unit_state( e );
		},
		updateUnitHeader: function() {
			// Bring on the Visual Editor
			$.each( $( '.unit-wp-editor' ), function( index, editor ) {
				var id = $( editor ).attr( 'id' );
				// Get rid of redundant editor
				tinyMCE.EditorManager.execCommand( 'mceRemoveEditor', true, id );
				var content = $( '#' + id ).val();
				var name = $( editor ).attr( 'name' );
				var height = $( editor ).attr( 'data-height' ) ? $( editor ).attr( 'data-height' ) : 200;
				CoursePress.editor.create( editor, id, name, content, false, height );
			} );
		},
		toggleAvailability:function( e ) {
			var select = $( e.currentTarget ),
				value = select.val(),
				setting_divs = select.parent().find( '.ua-div' ),
				active_setting = select.parent().find( '.div-' + value );
			setting_divs.hide();
			if ( active_setting.length > 0 ) {
				active_setting.show();
			}
		},
		setDate:function(ev) {
			var input = $(ev.target);
			input.datepicker();
		}
	} );

	CoursePress.Views.UnitBuilderFooter = Backbone.View.extend( {
		render: function() {
			var template = _.template( $( '#unit-builder-footer-template' ).html() );
			this.$el.empty();
			this.$el.html( template );
			return this;
		}
	} );

	// Unit Body View
	CoursePress.Views.UnitBuilderBody = Backbone.View.extend( {
		initialize: function() {
			this.initial = true;
			this.pagerView = new CoursePress.Views.UnitBuilderPager( { className: 'section unit-builder-pager' } );
			this.pagerView.parentView = this;
			this.pagerView.template_variables = {};

			this.pagerViewInfo = new CoursePress.Views.UnitBuilderPagerInfo( { className: 'section unit-builder-pager-info' } );
			this.pagerViewInfo.parentView = this;
			this.pagerViewInfo.template_variables = {};

			this.componentsView = new CoursePress.Views.UnitBuilderComponents( { className: 'section unit-builder-components' } );
			this.componentsView.parentView = this;
			this.componentsView.template_variables = {};

			this.modulesView = new CoursePress.Views.UnitBuilderModules( { className: 'section unit-builder-modules' } );
			this.modulesView.parentView = this;
			this.modulesView.template_variables = {};

			this.footerView = new CoursePress.Views.UnitBuilderFooter( { className: 'unit-buttons' } );
			this.footerView.parentView = this;

			this.model.on( 'sync', this.render, this );

			CoursePress.Events.on( 'editor:keyup', this.editorChanged, this );
		},
		render: function() {
			var template;
			if ( this.initial ) {
				template = _.template( $( '#unit-builder-content-placeholder' ).html() );
				this.$el.html( template );
				this.initial = false;
			} else {
				template = _.template( $( '#unit-builder-content-template' ).html() );
				this.$el.html( template );

				// Set variables first
				var unit = this.parentView.unit_collection._byId[ this.parentView.activeUnitRef ];

				// Always give at least 1 page
				this.pagerView.template_variables.unit_page_count = this.parentView.totalPages;
				this.pagerView.template_variables.pages_titles = unit.attributes.meta.page_title;

				this.$( '.unit-builder-pager' )
					.replaceWith( this.pagerView.render( this.pagerView.template_variables ).el );

				unit = this.parentView.unit_collection._byId[ this.parentView.activeUnitRef ];
				var show_page = unit.get_page_visibility( this.parentView.activePage );

				// Fix boolean
				if ( show_page || false === show_page ) {
					show_page = ( _.isString( show_page ) && ( 'yes' === show_page.toLowerCase() || 'on' === show_page.toLowerCase() ) ) || 1 === parseInt( show_page ) || true === show_page;
				} else {
					show_page = true;
				}

				this.pagerViewInfo.template_variables = {
					page_label_text: unit.get_page_title( this.parentView.activePage ),
					page_description: unit.get_page_description( this.parentView.activePage ),
					page_feature_image: unit.get_page_image( this.parentView.activePage ),
					page_label_checked: show_page ? 'checked="checked"' : ''
				};

				this.$( '.unit-builder-pager-info' )
					.replaceWith( this.pagerViewInfo.render( this.pagerViewInfo.template_variables ).el );

				this.componentsView.template_variables = {};

				this.$( '.unit-builder-components' )
					.replaceWith( this.componentsView.render( this.componentsView.template_variables ).el );

				this.$( '.unit-builder-modules' )
					.replaceWith( this.modulesView.render( this.parentView.module_collection.models ).el );

				this.$( '.unit-builder-footer' ).append( this.footerView.render().el );
				this.$('.unit-save-button').removeClass( 'disabled' );

				CoursePress.Helpers.Module.refresh_ui();
				this.updateSectionEditor();

				$( '#page_feature_image' ).val( this.pagerViewInfo.template_variables.page_feature_image );

				if ( CoursePress.UnitBuilder.gotoAdded === true ) {
					CoursePress.UnitBuilder.gotoAdded = false;
					var last_added = $( '[data-cid=' + CoursePress.UnitBuilder.activeModuleRef + ']' );
					$( 'body,html' ).scrollTop( $( last_added ).offset().top - 80 );
				}
			}
			/**
			 * bind page title change
			 */
			$('.page-info-holder input[name=page_title]').on('keyup', function() {
				label = $( this ).val();
				label = ( label.length > 19 ) ? label.substr( 0, 19 ) + "…" : label;
				$( '.unit-builder-pager li.active' ).html( label ) ;
			});
			return this;
		},
		events: {
			'click .unit-builder-components .output-element': 'add_element',
			'click .unit-builder-components .input-element': 'add_element',
			'change .button.browse-media-field': 'fieldChanged',
			'change .module-holder input': 'fieldChanged',
			'change textarea': 'fieldChanged',
			'change select': 'selectionChanged',
			'change .page-info-holder input': 'unitPageInfoChanged',
			'keyup .module-title-text': 'updateUIHeading',
			'click .unit-builder-pager ul li': 'changePage',
			'click .module-component .add-item': 'addAnswer',
			'click .module-component .remove-item': 'removeAnswer',
			'change #page_feature_image': 'pageFeatureImageChange',
			'change [name="page_feature_image-button"]': 'pageFeatureImageChange'
		},
		pageFeatureImageChange: function( e ) {
			var el = $( e.currentTarget );
			var el_val = $( '#page_feature_image' ).val();
			var page = this.parentView.activePage;

			var parent = $( $( el ).parents( '.unit-builder-content' )[ 0 ] ).find( '.unit-detail' )[0];
			var unit = this.parentView.unit_collection._byId[ $( parent ).attr( 'data-cid' ) ];

			unit.set_page_image( page, el_val );
		},
		add_element: function( e ) {
		CoursePress.Helpers.Module.unit_add_show_message( '<i class="fa fa-circle-o-notch fa-spin"></i> ' + _coursepress.unit_builder_form.messages.adding_module, 'info' );
			var el = e.currentTarget;
			var module_type = $( el ).attr( 'class' ).match( /module-(\w|-)*/g )[ 0 ].trim().replace( 'module-', '' );
			//Count current elements
			var count = $( '.module-holder' ).length;
			var module = new CoursePress.Models.Module();
			module.from_template( module_type );
			module.set_meta( 'module_order', (count + 1) );
			module.set_meta( 'module_page', this.parentView.activePage );
			module.save( null, {
				success: function( model, response ) {
					$( ".section.unit-builder-components .notice" ).detach();
				}
			});
			this.parentView.module_collection.add( module );
			//$( '.section.unit-builder-modules' ).append( CoursePress.Helpers.Module.render_module( module, (count + 1) ) );
			//CoursePress.Helpers.Module.refresh_ui();
		},
		fieldChanged: function( e ) {
			var el = $( e.currentTarget );
			var el_name = $( el ).attr( 'name' );

			if (
				el_name === 'unit_feature_image' ||
				el_name === 'unit_feature_image-button' ||
				el_name === 'page_feature_image' ||
				el_name === 'page_feature_image-button'
			) {
				return;
			}

			var el_val = $( el ).val();
			var parent = $( el ).parents( '.module-holder' )[ 0 ];
			var module = this.parentView.module_collection._byId[ $( parent ).attr( 'data-cid' ) ];

			var type = $( el ).attr( 'type' );
			var boxes;

			if ( 'checkbox' === type ) {
				boxes = $( '[name="' + el_name + '"]' );
				el_name = el_name.replace( /\[.*\]/, '' );

				if ( boxes.length > 1 ) {
					el_val = [];
					$.each( boxes, function( i, item ) {
						if ( $( item ).is( ':checked' ) ) {
							el_val.push( i );
						}
					} );
				} else {
					el_val = $( el ).is( ':checked' );
				}

			}
			if ( 'radio' === type ) {
				el_name = el_name.replace( /\[.*\]/, '' );
			}

			// Dynamic textboxes
			if ( 'text' === type || 'textbox' === type ) {
				if ( /\[\]/.test( el_name ) ) {


					// Should only be for answers, else keep going as per usual
					var component = $( el ).parents( '.module-component' );

					if ( component.length > 0 ) {

						boxes = $( component ).find( '[name="' + el_name + '"]' );
						el_name = el_name.replace( /\[.*\]/, '' );

						el_val = [];
						$.each( boxes, function( i, item ) {
							el_val.push( $( item ).val() );
						} );

						// Change meta now
						module.set_meta( el_name, el_val );

						return;
					}

				}

			}

			// Deal with Browse buttons
			if ( 'button' === type && $( el ).hasClass( 'browse-media-field' ) ) {
				var textbox = $( $( el )[ 0 ] ).siblings( '[type=text]' )[ 0 ];
				el_name = $( textbox ).attr( 'name' );
				el_val = $( textbox ).val();
			}

			if ( /meta_/.test( el_name ) ) {
				module.set_meta( el_name, el_val );
			} else {
				module.set( el_name, el_val );
			}

		},
		selectionChanged: function( e ) {
			var el = $( e.currentTarget );
			var el_name = $( el ).attr( 'name' );
			var el_val = $( el ).val();
			var parent = $( el ).parents( '.module-holder' )[ 0 ];
			var module = this.parentView.module_collection._byId[ $( parent ).attr( 'data-cid' ) ];

			if ( /meta_/.test( el_name ) ) {
				module.set_meta( el_name, el_val );
			} else {
				module.set( el_name, el_val );
			}
		},
		editorChanged: function( e ) {
			var el_name = e.id;
			var parent = $( '#' + el_name ).parents( '.module-holder' )[ 0 ];

			if ( undefined === parent ) {
				return;
			}

			var module = this.parentView.module_collection._byId[ $( parent ).attr( 'data-cid' ) ];

			var name = /post_content_/.test( el_name ) ? el_name.replace( /(_\d+)+$/, '' ) : el_name;
			var value = CoursePress.editor.content( el_name );

			if ( /meta_/.test( name ) ) {
				module.set_meta( name, value );
			} else {
				module.set( name, value );
			}

		},
		updateUIHeading: function( e ) {
			var el = $( e.currentTarget );
			var header = $( el.parents( '.module-holder' ).first() ).siblings( 'h3' ).first();
			$( header ).find( '.label' ).html( el.val() );
		},
		changePage: function( e ) {
			var self = this;
			var the_page = $( e.currentTarget ).attr( 'data-page' );
			var unit = self.parentView.unit_collection._byId[ self.parentView.activeUnitRef ];
			if ( the_page ) {
				self.parentView.activePage = $( e.currentTarget ).attr( 'data-page' );
				self.parentView.fetchModules( self.parentView.activeUnitID, self.parentView.activePage );
			} else {
				self.parentView.activePage = $( '.unit-builder-pager ul li' ).length;
				self.parentView.totalPages = self.parentView.activePage;

				// Update Pager
				unit.set_page_title( self.parentView.activePage, '' );
				unit.set_page_visibility( self.parentView.activePage, true );

				self.parentView.fetchModules( self.parentView.activeUnitID, self.parentView.activePage );
			}

		},
		unitPageInfoChanged: function( e ) {
			var el = $( e.currentTarget );
			var el_name = $( el ).attr( 'name' );
			var el_val = $( el ).val();
			var unit = this.parentView.unit_collection._byId[ this.parentView.activeUnitRef ];

			var type = $( el ).attr( 'type' );

			if ( 'checkbox' === type ) {
				el_val = $( el ).is( ':checked' );
			}

			switch ( el_name ) {
				case 'page_title':
					unit.set_page_title( this.parentView.activePage, el_val );
					break;

				case 'show_page_title':
					unit.set_page_visibility( this.parentView.activePage, el_val );
					break;
			}

			unit.trigger( 'change', unit );

			// A bit of UI help... to be developed further
			//$('.unit-builder-tabs ul li[data-tab="' + this.parentView.parentView.activeUnitID + '"' ).removeClass('unit-live');
			//$('.unit-builder-tabs ul li[data-tab="' + this.parentView.parentView.activeUnitID + '"' ).removeClass('unit-draft');
			//$('.unit-builder-tabs ul li[data-tab="' + this.parentView.parentView.activeUnitID + '"' ).addClass('unit-changed');

		},
		addAnswer: function( e ) {
			var el = e.currentTarget;
			var group = $( el ).siblings( '.answer-group' );
			var container = $( el ).parents( '.module-holder' );
			var cid = $( container ).attr( 'data-cid' );

			var boxes = $( container ).find( '.answer' );
			var value = boxes.length;

			var el_type = $( $(el).parents('.module-holder')[0] ).attr('class').match(/input-radio|input-select/) ? 'radio' : 'checkbox';
			var new_name = $( $(el).parents('.module-holder')[0] ).attr('class').match(/input-radio|input-select/) ? 'meta_answers_selected[' + cid + ']' : 'meta_answers_selected[' + cid + '][]';

			$( group ).append( '<div class="answer"><input type="' + el_type + '" value="' + value + '" name="' + new_name + '">' +
			'<input class="component-' + el_type + '-answer wide" type="text" name="meta_answers[]" value="">' +
			' <span class="remove-item"><i class="fa fa-trash-o"></i></span></div>' );

		},
		removeAnswer: function( e ) {
			var el = e.currentTarget;
			var answer_group = $( el ).parents( '.answer-group' );
			var answer = $( el ).parents( '.answer' );
			var container = $( el ).parents( '.module-holder' );
			var cid = $( container ).attr( 'data-cid' );
			var module = this.parentView.module_collection._byId[ cid ];

			answer.detach();

			// Fix Checkbox Indices
			var boxes = $( answer_group ).find( '[name="meta_answers_selected[' + cid + '][]"]' );

			var el_val = [];
			$.each( boxes, function( i, item ) {
				if ( $( item ).is( ':checked' ) ) {
					el_val.push( i );
				}
				$( item ).val( i );
			} );

			module.set_meta( 'meta_answers_selected', el_val );

			// Fix Textboxes
			boxes = $( answer_group ).find( '[name="meta_answers[]"]' );

			el_val = [];
			$.each( boxes, function( i, item ) {
				el_val.push( $( item ).val() );
			} );
			// Change meta now
			module.set_meta( 'meta_answers', el_val );
		},
		updateSectionEditor: function() {
			// Bring on the Visual Editor
			$.each( $( '.page-wp-editor' ), function( index, editor ) {
				var id = $( editor ).attr( 'id' );
				// Get rid of redundant editor
				tinyMCE.EditorManager.execCommand( 'mceRemoveEditor', true, id );
				var content = $( '#' + id ).val();
				var name = $( editor ).attr( 'name' );
				var height = $( editor ).attr( 'data-height' ) ? $( editor ).attr( 'data-height' ) : 200;
				CoursePress.editor.create( editor, id, name, content, false, height );
			} );
		}
	} );

	// Unit Body Pager View
	CoursePress.Views.UnitBuilderPager = Backbone.View.extend( {
		render: function( options ) {
			var template = _.template( $( '#unit-builder-pager-template' ).html() );
			this.$el.html( template( options ) );
			return this;
		}
	} );

	// Unit Body Pager Content View
	CoursePress.Views.UnitBuilderPagerInfo = Backbone.View.extend( {
		initialize: function() {
			CoursePress.Events.on( 'editor:keyup', this.editorChanged, this );
		},
		events: {
			'change .page-info-holder input': 'fieldChanged',
			'keyup .unit-detail .page-wp-editor': 'editorChanged',
		},
		render: function( options ) {
			var template = _.template( $( '#unit-builder-pager-info-template' ).html() );
			options.page_id = Math.floor( Math.random() * 1e10 );
			this.$el.html( template( options ) );
			return this;
		},
		editorChanged: function( e ) {
			var page = this.parentView.parentView.activePage;
			var the_id = e.id;
			var re = /^page_description_\d+$/;
			if ( undefined === the_id ) {
				the_id = $( e.currentTarget ).attr('id');
			}
			if ( undefined !== the_id && re.test( the_id ) ) {
				var el = $( '#' + the_id );
				var el_val = CoursePress.editor.content( the_id );
				var parent = $( $( el ).parents( '.unit-builder-content' )[ 0 ] ).find( '.unit-detail' )[0];
				var unit = this.parentView.parentView.unit_collection._byId[ $( parent ).attr( 'data-cid' ) ];
				unit.set_page_description( page, el_val );
			}
		}
	} );

	// Unit Body Components View
	CoursePress.Views.UnitBuilderComponents = Backbone.View.extend( {
		render: function( options ) {
			var template = _.template( $( '#unit-builder-components-template' ).html() );
			this.$el.html( template( options ) );

			return this;
		}
	} );

	// Unit Body Modules
	CoursePress.Views.UnitBuilderModules = Backbone.View.extend( {
		render: function() {
			var self = this;

			self.$el.empty();

			this.parentView.model.each( function( module ) {
				var moduleView = new CoursePress.Views.ModuleView( {
					model: module,
					tagName: 'div',
					className: 'group group-' + module.module_type()
				} );
				var order = module.get_meta( 'module_order' );

				self.$el.append( moduleView.render( module, order ).$el );
			} );

			return this;
		}
	} );

	// View for each module
	CoursePress.Views.ModuleView = Backbone.View.extend( {
		render: function( module, order ) {
			var self = this;
			self.$el.empty();

			//// Not using a template here
			self.$el.append( CoursePress.Helpers.Module.render_module( module, order ) );
			//
			CoursePress.Helpers.Module.refresh_ui();

			return this;
		}
	} );

	function init_course_builder() {
		CoursePress.UnitBuilder = new CoursePress.Views.UnitBuilder( { el: '#unit-builder' } );
	}

	$( document ).ready( init_course_builder );

})(jQuery);
