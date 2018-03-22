/* global CoursePress, _coursepress */

(function(){
    'use strict';

    CoursePress.Define( 'AssesmentsList', function($, doc, win) {
        var AssesmentsList;

        AssesmentsList = CoursePress.View.extend({
            el: $( '#coursepress-assessments' ),
            events: {
                'change [name="graded_ungraded"]': 'submitForm',
                'change [name="course_id"], [name="student_progress"], [name="display"]': 'submitForm',
                'click .cp-plus-icon, .cp-minus-icon': 'unitsExpandHide',
                'click .cp-expand-collapse': 'studentExpandHide',
                'click .edit-no-feedback, .edit-with-feedback': 'editNoFeedback',
                'click .cp-cancel': 'cancelEdit',
                'change .module-grade': 'enableSubmitButton',
                'click .cp-submit-grade': 'updateModuleGrade',
                'click .cp-save-as-draft': 'saveFeedbackAsDraft'
            },
            activeUnit: 'all',

            // Initialize.
            initialize: function() {
                this.on( 'view_rendered', this.setupUI, this );
                this.render();
            },

            // Setup UI elements.
            setupUI: function () {
                this.$('select').select2();
            },

            // Expand/hide student details.
            studentExpandHide: function ( ev ) {
                var selector = $( ev.currentTarget ),
                    tr = selector.closest( 'tr' );
                tr.find( '.cp-assessment-progress-hidden' ).toggleClass( 'inactive' );
                tr.find( '.cp-assessment-progress-expand' ).toggleClass( 'inactive' );
                tr.next( 'tr:not(.cp-assessment-main)' ).fadeToggle( 200 );
            },

            // Expand/hide questions.
            unitsExpandHide: function ( ev ) {
                var selector = $( ev.currentTarget ),
                    li = selector.closest( 'li' ),
                    table = li.find( '.cp-assessments-table-container' );
                selector.toggleClass('cp-plus-icon')
                    .toggleClass('cp-minus-icon');
                if ( table.length ) {
                    li.toggleClass('cp-assessments-units-expanded');
                    table.fadeToggle( 200 );
                }
            },

            // Submit filter form.
            submitForm: function () {
                this.$('#cp-search-form').submit();
            },

            editNoFeedback: function( ev ) {
               var btn = $( ev.currentTarget ),
         			nextButton = btn.siblings( 'button' ),
         			with_feedback = btn.is( '.edit-no-feedback' ) ? false : true,
         			parentTr = btn.parents( '.cp-question-title' ).next( 'tr.cp-grade-editor' ),
         			grade_box = $( '.module-grade', parentTr ),
         			editor_container = $( '.cp-feedback-editor', parentTr ),
         			editor_id = 'cp_editor_' + grade_box.data( 'module' ) + '_' + grade_box.data( 'student' ),
         			// editor_box = $( '.cp-grade-editor', parentTr ),
         			// unitDiv = parentTr.parents( '.cp-unit-div' ).first(),
         			// edit_grade_box = $( '.cp-edit-grade-box', parentTr ),
         			save_as_draft = $( '.cp-save-as-draft', parentTr )
         		;

         		if ( btn.is( '.disabled' ) ) {
         			// Don't process anything if button is disabled
         			return;
         		}
               $('.cp-cancel').click();
         		parentTr.slideDown();
         		nextButton.addClass('disabled' );

         		if ( with_feedback ) {
         			editor_container.show();
         			this.enableFeedbackEditor( editor_id, editor_container, parentTr );
         		// 	edit_grade_box.appendTo( editor_container );
         			save_as_draft.show();
         		} else {
         		// 	edit_grade_box.prependTo( editor_box );
         			save_as_draft.hide();
         		}
            },
            enableFeedbackEditor: function( editor_id, editor_container, container ) {
         		var textbox = $( '.cp_feedback_content', editor_container ),
         			old_content = textbox.val(),
         			has_editor = $( '.wp-editor-container', editor_container ).length > 0,
         			submitButton = $( '.cp-submit-grade', container ),
         			save_as_draft = $( '.cp-save-as-draft', container );

         		if ( ! has_editor ) {
         			CoursePress.Events.off( 'editor:keyup' );
         			CoursePress.Events.on( 'editor:keyup', function( ed ) {
         				var content = undefined !== typeof ed.getContent && ed.getContent ? ed.getContent() : $( '#' + editor_id ).val();

         				if ( content !== old_content ) {
         					textbox.val( content );
         					submitButton.removeClass( 'disabled' );
         					save_as_draft.removeClass( 'disabled' );
         				}
         			});
                  new CoursePress.Editor({
                     editor_id: editor_id,
                     editor_container: editor_container,
                     content: old_content
                  });
         		}
         	},
         	updateModuleGrade: function(ev) {
         		var btn = $( ev.currentTarget ),
                  me = this,
         			parentTr = btn.parents( 'tr.cp-grade-editor' ),
         			cancelButton = $( '.cp-cancel', parentTr ),
         			// draftButton = $( '.cp-save-as-draft', parentTr ),
         			module = $( '.module-grade', parentTr ),
         			feedback = $( '.cp_feedback_content', parentTr ),
         			with_feedback = $( '.edit-no-feedback' ).is( '.disabled' ) ? true : false,
         			cpCheck = parentTr.prev('.cp-question-title').find( '.cp-check' ),
         			currentGrade = parentTr.prev('.cp-question-title').find( '.cp-current-grade' ),
         			min_grade = parseInt( module.data( 'minimum') ),
         			grade = parseInt( module.val() ),
         			is_pass = grade >= min_grade,
         			// gradeInfo = parentTr.prev('.cp-question-title').find( '.cp-module-grade-info' ),
         			withFeedbackButton = parentTr.prev('.cp-question-title').find( '.edit-with-feedback' ),
         			noFeedbackButton = parentTr.prev('.cp-question-title').find( '.edit-no-feedback' ),
                  unit_id = module.data( 'unit' ),
         			course_id = module.data( 'courseid' ),
         			module_id = module.data( 'module' ),
         			student_id = module.data( 'student' ),
                  feedback_content = feedback.val(),
         			unitDiv = $( '.cp-unit-div' ).filter(function(){
            				var data = $(this).data();

            				return data.unit === unit_id && data.student === student_id;
            			})
         		;

         		if ( btn.is( '.disabled') ) {
         			// Don't save anything if button is disabled
         			return;
         		}

         		var progress = CoursePress.progressIndicator();

         		progress.icon.insertAfter( ev.currentTarget );
               var model = new CoursePress.Request();
               model.set( 'action', 'update_assessments_grade' );
               model.set( 'course_id', course_id );
               model.set( 'unit_id', unit_id );
               model.set( 'step_id', module_id );
               model.set( 'with_feedback', with_feedback );
               model.set( 'feedback_content', feedback.val() );
               model.set( 'student_grade', grade );
               model.set( 'student_id', student_id );
               model.off( 'coursepress:success_update_assessments_grade' );
               model.on( 'coursepress:success_update_assessments_grade', function(data){
                  CoursePress.Events.on( 'coursepress:progress:success', function() {
                     module.attr( 'data-grade', grade ).data( 'grade', grade );
                     cancelButton.trigger( 'click' );
                     currentGrade.html( grade + '%' );
                     if ( is_pass ) {
         					cpCheck.removeClass( 'cp-red' ).addClass( 'cp-green' ).html( win._coursepress.assessment_labels.pass );
         				} else {
         					cpCheck.removeClass( 'cp-green' ).addClass( 'cp-red' ).html( win._coursepress.assessment_labels.fail );
         				}
                     withFeedbackButton.html( win._coursepress.assessment_labels.edit_with_feedback );
               		noFeedbackButton.html( win._coursepress.assessment_labels.edit_no_feedback );

                     me.calculateFinalGrade( student_id, data.course_grade );
                     unitDiv.html( data.unit_grade + '%' );
                     if(data.has_pass_course_unit){
                        unitDiv.removeClass('cp-cross-icon').addClass('cp-tick-icon');
                     } else{
                        unitDiv.removeClass('cp-tick-icon').addClass('cp-cross-icon');
                     }
                        if ( with_feedback && '' !== feedback_content.trim() ) {
            					var feedback_editor = $( '.cp-instructor-feedback[data-courseid="' + course_id + '"][data-unit="' + unit_id + '"][data-module="' + module_id + '"][data-student="' + student_id + '"]' ).show(),
            						draft_icon = $( '.cp-draft-icon', feedback_editor )
            					;

            					draft_icon[ with_feedback ? 'hide' : 'show']();
            					$( '.description', feedback_editor ).hide(); // Hide no feedback info
            					$( '.cp-feedback-details', feedback_editor ).html( feedback_content );
            					$( 'cite', feedback_editor ).html( '- ' + win._coursepress.instructor_name );
            				}
                  });
                  progress.success();
               }, this );
               model.on( 'coursepress:error_update_assessments_grade', function(){
         			progress.error( win._coursepress.server_error );
         		}, this );
               model.save();

         	},
            saveFeedbackAsDraft: function(ev) {
         		var btn = $( ev.currentTarget ),
                  parentTr = btn.parents( 'tr.cp-grade-editor' ),
                  // moduleDiv = btn.parents( '.cp-module' ),
         			feedback = $( '.cp_feedback_content', parentTr ),
         			cancelButton = $( '.cp-cancel', parentTr ),
         			module = $( '.module-grade', parentTr ),
         			course_id = module.data( 'courseid' ),
         			unit_id = module.data( 'unit' ),
         			module_id = module.data( 'module' ),
         			student_id = module.data( 'student' ),
                  feedback_content = feedback.val();

         		if ( btn.is( '.disabled' ) ) {
         			// Nothing to save
         			return;
         		}

         		var progress = CoursePress.progressIndicator();

               progress.icon.insertAfter( ev.currentTarget );
               var model = new CoursePress.Request();
               model.set( 'action', 'save_draft_feedback' );
               model.set( 'course_id', course_id );
               model.set( 'unit_id', unit_id );
               model.set( 'step_id', module_id );
               model.set( 'feedback_content', feedback.val() );
               model.set( 'student_id', student_id );
               model.off( 'coursepress:success_save_draft_feedback' );
               model.on( 'coursepress:success_save_draft_feedback', function(){
                  CoursePress.Events.on( 'coursepress:progress:success', function() {
                     btn.addClass( 'disabled' );
         				var feedback_editor = $( '.cp-instructor-feedback[data-courseid="' + course_id + '"][data-unit="' + unit_id + '"][data-module="' + module_id + '"][data-student="' + student_id + '"]' ).show();
         				$( '.cp-draft-icon', feedback_editor ).show();
         				$( '.description', feedback_editor ).hide(); // Hide no feedback info
         				$( '.cp-feedback-details', feedback_editor ).html( feedback_content );
         				$( 'cite', feedback_editor ).html( '- ' + _coursepress.instructor_name );
         				cancelButton.trigger( 'click' );
                  });
                  progress.success();
               }, this );
               model.on( 'coursepress:error_save_draft_feedback', function(){
         			progress.error( win._coursepress.server_error );
         		}, this );
               model.save();
         	},
         	cancelEdit: function(ev) {
         		var btn = $( ev.currentTarget ),
         			parentTr = btn.parents( 'tr.cp-grade-editor' ),
         			buttons = $( '.cp-question-title button' ),
         			module = $( '.module-grade', parentTr ),
         			submitButton = $( '.cp-submit-grade', parentTr )
         		;

         		submitButton.addClass( 'disabled' );
         		parentTr.slideUp();
               module.val( module.data( 'grade' ) );
         		buttons.removeClass( 'disabled' );
         	},
            enableSubmitButton: function(ev) {
               var module = $( ev.currentTarget ),
         			submitButton = module.siblings( '.cp-submit-grade' ),
         			val = parseFloat( module.val() )
         		;

         		if ( val > 100 ) {
         			// Maximum grade is 100
         			val = 100;
         		}
         		if ( 0 > val ) {
         			// Minimum grade is 0
         			val = 0;
         		}
         		module.val( val );

         		submitButton[ val >= 0 ? 'removeClass' : 'addClass' ]('disabled');
            },
         	calculateFinalGrade: function( student_id, course_grade ) {
         		var finalDiv = $( '.final-grade[data-student="' + student_id + '"], [data-student="' + student_id + '"] .final-grade' );

         		if ( course_grade ) {
         			finalDiv.html( course_grade + '%' );
         		}
         	}
        });

        AssesmentsList = new AssesmentsList();
    });
})();
