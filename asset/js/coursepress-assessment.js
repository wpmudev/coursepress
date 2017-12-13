/*! CoursePress - v2.1.2
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2017; * Licensed GPLv2+ */
+(function($){
	CoursePress = CoursePress || {};
	CoursePress.Events = CoursePress.Events || _.extend( {}, Backbone.Events );

	CoursePress.Models.Units = CoursePress.Models.Post.extend({
		url: _coursepress._ajax_url + '?action=coursepress_assessments',
		parse: function( response ) {
			var action = this.get( 'action' );

			if ( true == response.success ) {
				this.trigger( 'coursepress:' + action + '_success', response.data );
			} else {
				this.trigger( 'coursepress:' + action + '_error', response.data );
			};

			this.set( 'action', '' );
		}
	});
	CoursePress.UnitsPost = new CoursePress.Models.Units();

	var currentPage = 1,
		activeUnit = 'all';

	var calculateUnitGrade = function( unit_id, student_id ) {
		var unitDiv = $( '.cp-unit-div' ).filter(function(){
				var data = $(this).data();

				return data.unit == unit_id && data.student == student_id;
			}),
			modules = $( '.module-grade', unitDiv ),
			assessable_modules = $( '.module-assessable .module-grade', unitDiv ),
			unitGrade = 0,
			unitAssessableGrade = 0,
			module_length = $( '.cp-total-unit-modules[data-unit="' + unit_id + '"]' ).first().val()
		;

		if ( 'all' != activeUnit ) {
			modules = assessable_modules;
		}

		_.each(modules, function( module ) {
			module = $(module);

			var grade = module.val();

			grade = ! grade || null == grade ? 0 : parseInt( grade );
			unitGrade += grade;
		});

		unitGrade = unitGrade > 0 && module_length > 0 ? Math.ceil( unitGrade / module_length ) : 0;

		return unitGrade;

	},
	calculateUnitPassingGrade = function( unit_id, student_id ) {
		var unitDiv = $( '.cp-unit-div[data-unit="' + unit_id + '"][data-student="' + student_id + '"]' ),
			modules = $( '.module-grade', unitDiv ),
			assessable_modules = $( '.module-assessable .module-grade', unitDiv )
			passingGrade = 0,
			isfull = $( '.modules-answer-wrapper' ).length > 0
		;

		if ( ! isfull ) {
			modules.parents( '.cp-module' ).hide();
		}

		if ( ! isfull && 'all' != activeUnit ) {
			modules = assessable_modules;
		}

		_.each(modules, function( module ) {
			module = $(module);

			var data = module.data(),
				minimum_grade = data.minimum
			;

			minimum_grade = ! minimum_grade || minimum_grade <= 0 ? 0 : parseInt( minimum_grade );
			passingGrade += minimum_grade;
			module.parents( '.cp-module' ).show();
		});

		passingGrade = passingGrade > 0 ? Math.ceil( passingGrade / modules.length ) : 0;

		return passingGrade;
	},
	calculateFinalGrade = function( student_id, course_grade ) {
		var finalDiv = $( '.final-grade[data-student="' + student_id + '"], [data-student="' + student_id + '"] .final-grade' );

		if ( course_grade ) {
			finalDiv.html( course_grade + '%' );
		}
	};

	// Edit module grade
	var editModuleGrade = function() {
		var btn = $(this),
			nextButton = btn.siblings( 'button' ),
			with_feedback = btn.is( '.edit-no-feedback' ) ? false : true,
			parentDiv = btn.parents( '.cp-grade-editor' ).first(),
			grade_box = $( '.module-grade', parentDiv ),
			editor_container = $( '.cp-feedback-editor', parentDiv ).hide(),
			editor_id = 'cp_editor_' + grade_box.data( 'module' ) + '_' + grade_box.data( 'student' ),
			editor_box = $( '.cp-grade-editor-box', parentDiv ),
			unitDiv = parentDiv.parents( '.cp-unit-div' ).first(),
			edit_grade_box = $( '.cp-edit-grade-box', parentDiv ),
			save_as_draft = $( '.cp-save-as-draft', parentDiv )
		;

		if ( btn.is( '.disabled' ) ) {
			// Don't process anything if button is disabled
			return;
		}

		editor_box.slideDown();
		nextButton.addClass('disabled' );

		if ( with_feedback ) {
			editor_container.show();
			enableFeedbackEditor( editor_id, editor_container, parentDiv );
			edit_grade_box.appendTo( editor_container );
			save_as_draft.show();
		} else {
			edit_grade_box.prependTo( editor_box );
			save_as_draft.hide();
		}
	},
	updateModuleGrade = function() {
		var button = $(this),
			parentDiv = button.parents( '.cp-grade-editor' ),
			moduleDiv = button.parents( '.cp-module' ),
			cancelButton = $( '.cp-cancel', parentDiv ),
			draftButton = $( '.cp-save-as-draft', moduleDiv ),
			module = $( '.module-grade', parentDiv ),
			feedback = $( '.cp_feedback_content', parentDiv ),
			with_feedback = $( '.edit-no-feedback' ).is( '.disabled' ) ? true : false,
			cpCheck = $( '.cp-check', parentDiv ),
			currentGrade = $( '.cp-current-grade', parentDiv ),
			min_grade = parseInt( module.data( 'minimum') ),
			grade = parseInt( module.val() ),
			is_pass = grade >= min_grade,
			gradeInfo = $( '.cp-module-grade-info', parentDiv ),
			withFeedbackButton = $( '.edit-with-feedback', parentDiv ),
			noFeedbackButton = $( '.edit-no-feedback', parentDiv ),
			unit_id = module.data( 'unit' ),
			module_id = module.data( 'module' ),
			student_id = module.data( 'student' ),
			unitDiv = parentDiv.parents( '.cp-unit-div' ).first(),
			unitGrade = $( '.unit-data .unit-grade', unitDiv )
		;

		if ( button.is( '.disabled') ) {
			// Don't save anything if button is disabled
			return;
		}

		var progress = CoursePress.ProgressIndicator(),
			param = {
				course_id: module.data( 'courseid' ),
				unit_id: unit_id,
				module_id: module_id,
				student_id: student_id,
				with_feedback: with_feedback,
				feedback_content: feedback.val(),
				student_grade: grade,
				action: 'update'
			};

		progress.icon.insertAfter( this );
		CoursePress.UnitsPost.save( param );
		CoursePress.UnitsPost.off( 'coursepress:update_success' );
		CoursePress.UnitsPost.on( 'coursepress:update_success', function(data){
			CoursePress.Events.on( 'coursepress:progress:success', function() {
				module.attr( 'data-grade', grade ).data( 'grade', grade );
				draftButton.addClass( 'disabled' );
				cancelButton.trigger( 'click' );
				currentGrade.html( grade + '%' );
				gradeInfo.show();

				if ( is_pass ) {
					cpCheck.removeClass( 'red' ).addClass( 'green' ).html( _coursepress.assessment_labels.pass );
				} else {
					cpCheck.removeClass( 'green' ).addClass( 'red' ).html( _coursepress.assessment_labels.fail );
				}
				withFeedbackButton.html( _coursepress.assessment_labels.edit_with_feedback );
				noFeedbackButton.html( _coursepress.assessment_labels.edit_no_feedback );

				var totalUnitGrade = calculateUnitGrade( unit_id, student_id ),
					totalCourseGrade = calculateFinalGrade( student_id, data.course_grade );
				unitGrade.html( data.unit_grade + '%' );

				if ( with_feedback && '' != param.feedback_content.trim() ) {
					var feedback_editor = $( '.cp-instructor-feedback', moduleDiv ).show(),
						draft_icon = $( '.cp-draft-icon', feedback_editor )
					;

					draft_icon[ with_feedback ? 'hide' : 'show']();
					$( '.description', feedback_editor ).hide(); // Hide no feedback info
					$( '.cp-feedback-details', feedback_editor ).html( param.feedback_content );
					$( 'cite', feedback_editor ).html( '- ' + _coursepress.instructor_name );
				}

				// Toggle certified icon
				var certified = $( '[data-student="' + student_id + '"] .cp-certified' );
				certified[ true === data.completed ? 'show' : 'hide' ]();
			});

			progress.success();
		});
		CoursePress.UnitsPost.on( 'coursepress:update_error', function(){
			progress.error( _coursepress.server_error );
		});
	},
	enableFeedbackEditor = function( editor_id, editor_container, container ) {
		var textbox = $( '.cp_feedback_content', container ),
			old_content = textbox.val(),
			has_editor = $( '.wp-editor-container', editor_container ).length > 0,
			submitButton = $( '.cp-submit-grade', container ),
			save_as_draft = $( '.cp-save-as-draft', container )
		;

		if ( ! has_editor ) {
			CoursePress.Events.off( 'editor:keyup' );
			CoursePress.Events.on( 'editor:keyup', function( ed ) {
				var content = undefined != typeof ed.getContent && ed.getContent ? ed.getContent() : $( '#' + editor_id ).val();

				if ( content != old_content ) {
					textbox.val( content );
					submitButton.removeClass( 'disabled' );
					save_as_draft.removeClass( 'disabled' );
				}
			});

			CoursePress.editor.create( editor_container, editor_id, editor_id, old_content );
		}
	},
	enableSubmitButton = function() {
		var module = $(this),
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
	cancelEdit = function() {
		var btn = $(this),
			parentDiv = btn.parents( '.cp-grade-editor' ).first(),
			editor_box = $( '.cp-grade-editor-box', parentDiv ),
			buttons = $( '.cp-assessment-div button' ),
			module = $( '.module-grade', parentDiv ),
			submitButton = $( '.cp-submit-grade', parentDiv )
		;

		module.val( module.data( 'grade' ) );
		submitButton.addClass( 'disabled' );
		editor_box.slideUp();
		buttons.removeClass( 'disabled' );
	},
	saveFeedbackAsDraft = function() {
		var btn = $(this),
			parentDiv = btn.parents( '.cp-grade-editor' ),
			moduleDiv = btn.parents( '.cp-module' ),
			feedback = $( '.cp_feedback_content', parentDiv ),
			cancelButton = $( '.cp-cancel', parentDiv ),
			module = $( '.module-grade', parentDiv ),
			course_id = module.data( 'courseid' ),
			unit_id = module.data( 'unit' ),
			module_id = module.data( 'module' ),
			student_id = module.data( 'student' )
		;

		if ( btn.is( '.disabled' ) ) {
			// Nothing to save
			return;
		}

		var progress = CoursePress.ProgressIndicator();
		progress.icon.addClass( 'cp-right' ).insertAfter( this );

		var param = {
			course_id: course_id,
			unit_id: unit_id,
			module_id: module_id,
			student_id: student_id,
			feedback_content: feedback.val(),
			action: 'save_draft_feedback'
		};

		CoursePress.UnitsPost.save( param );
		CoursePress.UnitsPost.off( 'coursepress:save_draft_feedback_success' );
		CoursePress.UnitsPost.on( 'coursepress:save_draft_feedback_success', function() {
			// Trigger several actions after progress completed
			CoursePress.Events.on( 'coursepress:progress:success', function() {
				btn.addClass( 'disabled' );
				var feedback_editor = $( '.cp-instructor-feedback', moduleDiv ).show(),
					draft_icon = $( '.cp-draft-icon', feedback_editor ).show();
				$( '.description', feedback_editor ).hide(); // Hide no feedback info
				$( '.cp-feedback-details', feedback_editor ).html( param.feedback_content );
				$( 'cite', feedback_editor ).html( '- ' + _coursepress.instructor_name );
				cancelButton.trigger( 'click' );
			});
			progress.success( _coursepress.assessment_labels.sucess );
		});
		CoursePress.UnitsPost.off( 'coursepress:save_draft_feedback_error' );
		CoursePress.UnitsPost.on( 'coursepress:save_draft_feedback_error', function() {
			progress.error( _coursepress.server_error );
		});
	};

	var filterStudentRows = function() {
		// Set the templates
		$( '.cp-content script' ).each(function() {
			var template_script = $( this )
				template = template_script.html()
			;
			template_script.replaceWith( template );
		});

		var units = $( '.cp-unit-div' ),
			table = $( '.cp-table' ),
			rows = $( '.student-row' ),
			unit_type = $( '#unit-list' ).val()
		;

		// Update final grade
		_.each( rows, function( row ) {
			row = $(row);

			var student_id = row.data( 'student' );

			calculateFinalGrade( student_id );
		});

		// Update grade per unit
		_.each( units, function( unit ) {
			unit = $(unit);

			var unit_id = unit.data( 'unit' ),
				student_id = unit.data( 'student' ),
				unit_title = $( '.cp-toggle', unit )
			;
			updateGradeView( unit_id, student_id );
			unit_title[ unit_type > 0 ? 'hide' : 'show']();
		});

		$( '.cp-edit-grade-box' ).attr( 'data-title', _coursepress.assessment_labels.help_tooltip );
	};

	// Update unit and final grade
	var updateGradeView = function( unit_id, student_id ) {
		var unitGrade = calculateUnitGrade( unit_id, student_id ),
			divs = $( '.cp-unit-div .unit-grade' ),
			pass_label = $( '<span class="cp-check green">' ).html( _coursepress.assessment_labels.pass ),
			fail_label = $( '<span class="cp-check red">' ).html( _coursepress.assessment_labels.fail )
		;

		_.each( divs, function( div ) {
			div = $(div);

			var data = div.data();

			if ( data.unit == unit_id && data.student == student_id ) {
				//div.show().html( unitGrade + '%' );
			}
		});

	};

	// Load student table
	var loadStudentTable = function() {
		var container = $( '#assessment-table-container' ),
			activeUnit = $( '#unit-list' ).val(),
			grade_type = $( '#ungraded-list' ).val(),
			course_id = $( '#course-list' ).val(),
			orderby = $( '#assessment-orderby' ).val(),
			order = $( '#assessment-order' ).val(),
			loader_info = $( '.cp-loader-info' ),
			search = $( '#search_student_box' ),
			reset_button = search.siblings( '#search_reset' )
		;

		if ( ! course_id ) {
			return;
		}

		if ( '' != search.val() ) {
			// Enable reset
			reset_button.removeClass( 'disabled' );
		} else {
			reset_button.addClass( 'disabled' );
		}

		var data = {
			course_id: course_id,
			unit_id: activeUnit,
			student_type: grade_type,
			paged: currentPage,
			action: 'table',
			search: search.val(),
			orderby: orderby,
			order: order
		};
		container.empty();
		loader_info.show();
		updateLocation();

		CoursePress.UnitsPost.save( data );
		CoursePress.UnitsPost.on( 'coursepress:table_success', function( data ) {
			container.html( data.html );
			loader_info.hide();
			filterStudentRows();
		});
	};

	// Search students
	var searchStudents = function() {
		var form = $(this),
			search_box = $( '#search_student_box', form ),
			button = $( '#search_student_submit', form ),
			reset_button = button.siblings( '#search_reset' );

		if ( ! search_box.val() ) {
			// No key term, bail!
			return false;
		}

		reset_button.removeClass( 'disabled' );
		loadStudentTable();

		return false;
	},
	// Reset table display when search was previously done
	resetStudentDisplay = function() {
		var button = $(this),
			search_box = $( '#search_student_box' )
		;

		search_box.val( '' );
		loadStudentTable();
		button.addClass( 'disabled' );
	};

	var updateLocation = function() {
		var unitDiv = $( '#unit-list' ),
			typeDiv = $( '#ungraded-list' ),
			base_location = $( '#base_location' ).val()
		;

		base_location += '&unit=' + unitDiv.val() + '&type=' + typeDiv.val();

		if ( currentPage > 1 ) {
			base_location += '&paged=' + currentPage;
		}

		history.pushState( {}, null, base_location );
	},
	updateNav = function() {
		var nav = $(this),
			href = nav.attr( 'href', '#' ),
			paged = nav.data( 'paged' )
		;

		currentPage = paged;
		loadStudentTable();
		return false;
	};

	// Change display type
	var changeDisplayType = function() {
		var type = $( this ).val(),
			url = window.location.toString()
		;
		url += '&display=' + type;
		window.location = url;
	};

	var toggleTitle = function() {
		var h3 = $(this),
			icon = h3.find( '.dashicons' ),
			siblings = h3.siblings(),
			isopen = siblings.is( ':visible' )
		;

		siblings[isopen ? 'slideUp' : 'slideDown']();
		icon[isopen ? 'removeClass' : 'addClass']('dashicons-arrow-down');
		icon[isopen ? 'addClass' : 'removeClass']('dashicons-arrow-up' );
	};

	var toggleStudentUnits = function() {
		var btn = $(this),
			data = btn.data(),
			template_script = $( '#student-grade-' + data.student ),
			isopen = false,
			template = wp.template("assessment-modules")
		;
		if ( 0 == template_script.length ) {
			var param = {
				course_id: parseInt( $( '#course-list' ).val() ),
				student_id: data.student,
				action: 'get_student_modules'
			};
			CoursePress.UnitsPost.save( param );
			CoursePress.UnitsPost.off( 'coursepress:get_student_modules_success' );
			CoursePress.UnitsPost.on( 'coursepress:get_student_modules_success', function( data ) {
				$("#user-" + data.student_id ).after( template( data ) );
			});
			template_script = $( '#student-grade-' + data.student );
			isopen = true;
		}
		isopen = template_script.is( ':visible' );
		template_script[ isopen ? 'hide' : 'show' ]();
		btn[ isopen ? 'removeClass' : 'addClass']('active');
	};

	// Reload new course selection
	var newCourse = function() {
		var base_url = $( '#base_location' ).val(),
			courselist = $( this )
		;
		base_url += '&course_id=' + courselist.val();

		window.location.assign( base_url );
	};

	CoursePress.Events.on( 'coursepress:assessment_loaded', function() {
		var modules_wrapper = $( '.modules-answer-wrapper' );

		if ( modules_wrapper.length > 0 ) {
			calculateFinalGrade( modules_wrapper.data( 'student' ) );
			$( '.cp-edit-grade-box' ).attr( 'data-title', _coursepress.assessment_labels.help_tooltip );
		} else {
			loadStudentTable();
		}
	} );

	//Hooked events
	$( document )
		.ready(function() {
			// Call assessments events
			CoursePress.Events.trigger( 'coursepress:assessment_loaded' );
		})
		.on( 'change', '#unit-list', loadStudentTable )
		.on( 'change', '#ungraded-list', loadStudentTable )
		.on( 'click', '.edit-no-feedback, .edit-with-feedback', editModuleGrade )
		.on( 'click', '.cp-submit-grade', updateModuleGrade )
		.on( 'change', '.module-grade', enableSubmitButton )
		.on( 'click', '.cp-cancel', cancelEdit )
		.on( 'click', '.cp-edit-grade', toggleStudentUnits )
		.on( 'click', '.next-page, .last-page, .first-page, .prev-page', updateNav )
		.on( 'click', '.modules-answer-wrapper .cp-toggle', toggleTitle )
		.on( 'click', '.cp-save-as-draft', saveFeedbackAsDraft )
		.on( 'change', '#grade-type', changeDisplayType )
		.on( 'change', '#course-list', newCourse )
		.on( 'submit', '.assessment-search-student-box', searchStudents )
		.on( 'click', '#search_reset', resetStudentDisplay );

})(jQuery);
