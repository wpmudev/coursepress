jQuery(document).ready(function() {

    jQuery('#add_student_class').live('input', function() {
        return false;
    });

    jQuery('.element_title').live('input', function() {
        jQuery(this).parent().parent().find('.h3-label-left').html(jQuery(this).val());
    });

    jQuery('#unit_name').live('input', function() {
        jQuery('.mp-wrap .mp-tab.active a').html(jQuery(this).val());
    });

    function submit_elements() {

        jQuery("input[name*='radio_input_module_radio_check']:checked").each(function() {
            var vl = jQuery(this).parent().find('.radio_answer').val();
            jQuery(this).closest(".module-content").find('.checked_index').val(vl);
        });

        jQuery("input[name*='radio_answers']").each(function(i, obj) {
            jQuery(this).attr("name", "radio_input_module_radio_answers[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });

        jQuery("input[name*='radio_check']").each(function(i, obj) {
            jQuery(this).attr("name", "radio_input_module_radio_check[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });

        jQuery("#unit-add").submit();
    }

    jQuery(".unit-control-buttons .save-unit-button").click(function() {
        submit_elements();
    });

    jQuery(".unit-control-buttons .button-publish").click(function() {
        submit_elements();
    });
});

function delete_class_confirmed() {
    return confirm(coursepress_units.delete_class);
}

function deleteClass() {
    if (delete_class_confirmed()) {
        return true;
    } else {
        return false;
    }
}

function withdraw_all_from_class_confirmed() {
    return confirm(coursepress_units.withdraw_class_alert);
}

function withdrawAllFromClass() {
    if (withdraw_all_from_class_confirmed()) {
        return true;
    } else {
        return false;
    }
}

jQuery(function() {

    jQuery("#sortable-units").sortable({
        placeholder: "ui-state-highlight",
        items: "li:not(.static)",
        stop: function(event, ui) {
            update_sortable_indexes();
        }
    });

    jQuery("#sortable-units").disableSelection();


    var current_unit_page = 0;//current selected unit page

    current_unit_page = jQuery('#unit-pages .ui-tabs-nav .ui-state-active a').html();

    jQuery("#unit-page-" + current_unit_page + " .unit-module-list").change(function() {
        jQuery("#unit-page-" + current_unit_page + " .module_description").html(jQuery(this).find(':selected').data('module-description'));
    });

    jQuery("#unit-page-" + current_unit_page + " .module_description").html(jQuery(this).find(':selected').data('module-description'));

});

function update_sortable_indexes() {
    jQuery('.numberCircle').each(function(i, obj) {
        jQuery(this).html(i + 1);
    });

    jQuery('.unit_order').each(function(i, obj) {
        jQuery(this).val(i + 1);
    });

    var positions = new Array();

    jQuery('.unit_id').each(function(i, obj) {
        positions[i] = jQuery(this).val();
    });

    var data = {
        action: 'update_units_positions',
        positions: positions.toString()
    };

    jQuery.post(ajaxurl, data, function(response) {
        //alert(response);
    });

}

/* Native WP media browser for audio module (unit module) */

jQuery(document).ready(function()
{
    jQuery('.audio_url_button').live('click', function()
    {
        var target_url_field = jQuery(this).prevAll(".audio_url:first");

        wp.media.editor.send.attachment = function(props, attachment)
        {
            jQuery(target_url_field).val(attachment.url);
        };
        wp.media.editor.open(this);
        return false;
    });
});

/* Native WP media browser for video module (unit module) */

jQuery(document).ready(function()
{
    jQuery('.video_url_button').live('click', function()
    {
        var target_url_field = jQuery(this).prevAll(".video_url:first");

        wp.media.editor.send.attachment = function(props, attachment)
        {
            jQuery(target_url_field).val(attachment.url);
        };

        wp.media.editor.open(this);
        return false;
    });

    jQuery('.course_video_url_button').live('click', function()
    {
        var target_url_field = jQuery(this).prevAll(".course_video_url:first");

        wp.media.string.props = function(props, attachment)
        {
            jQuery(target_url_field).val(props.url);
        }

        wp.media.editor.send.attachment = function(props, attachment)
        {
            jQuery(target_url_field).val(attachment.url);
        };

        wp.media.editor.open(this);
        return false;
    });

});

/* Native WP media browser for file module (for instructors) */

jQuery(document).ready(function()
{
    jQuery('.file_url_button').live('click', function()
    {

        var target_url_field = jQuery(this).prevAll(".file_url:first");
        wp.media.editor.send.attachment = function(props, attachment)
        {
            jQuery(target_url_field).val(attachment.url);
        };
        wp.media.editor.open(this);
        return false;
    });
});


jQuery(document).ready(function()
{
    jQuery('.insert-media-cp').live('click', function()
    {

        var rand_id = jQuery(this).attr("data-editor");

        wp.media.editor.send.attachment = function(props, attachment)
        {
            tinyMCE.execCommand('mceFocus', false, rand_id);
            var ed = tinyMCE.get(rand_id);
            var range = ed.selection.getRng();
            var image = ed.getDoc().createElement("img");

            var image_width = eval('attachment.sizes' + '.' + props.size + '.' + 'width');
            var image_height = eval('attachment.sizes' + '.' + props.size + '.' + 'height');

            image.setAttribute('class', 'align' + props.align + ' size-' + props.size + ' wp-image-' + rand_id);
            image.src = attachment.url;
            image.alt = attachment.alt;
            image.width = image_width;
            image.height = image_height;
            range.insertNode(image);
        };

        wp.media.editor.open(this);

        return false;
    });

    //tinyMCE.activeEditor.selection.moveToBookmark(bm);
});


jQuery.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null){
       return null;
    }
    else{
       return results[1] || 0;
    }
}

// Detect key changes in the wp_editor
var active_editor;
function cp_editor_key_down( ed, page, tab ) {
	$ = jQuery;

	if ( page == 'coursepress_page_course_details' ) {
		if ( tab == '' || tab == 'overview' ){

			// Mark as dirty when wp_editor content changes on 'Course Setup' page.
			$( '#' + ed.id ).parents('.course-section').addClass('dirty');
			active_editor = ed.id;
			
		}
	}
	
}

// Detect mouse movement in the wp_editor
function cp_editor_mouse_move( ed, event ) {
}


function set_update_progress( step , value  ) {
	
	$('input[name="meta_course_setup_progress[' + step + ']"]').val( value );
	
}

function get_meta_course_setup_progress() {
	var meta_course_setup_progress = {
		'step-1': $('input[name="meta_course_setup_progress[step-1]"]').val(),
		'step-2': $('input[name="meta_course_setup_progress[step-2]"]').val(),
		'step-3': $('input[name="meta_course_setup_progress[step-3]"]').val(),
		'step-4': $('input[name="meta_course_setup_progress[step-4]"]').val(),
		'step-5': $('input[name="meta_course_setup_progress[step-5]"]').val(),	
		'step-6': $('input[name="meta_course_setup_progress[step-6]"]').val(),														
	}
	
	return meta_course_setup_progress;
}

function autosave_course_setup_done( data, status, step, statusElement ) {
	if ( status == 'success' ) {
		$( $( '.' + step + '.dirty' )[0] ).removeClass( 'dirty' )
		$( statusElement ).removeClass( 'progress' );
		$( statusElement ).addClass( 'saved' );
	} else {
		$( statusElement ).removeClass( 'progress' );
		$( statusElement ).addClass( 'invalid' );				
		set_update_progress( step, 'invalid' );
	}	
};

/** Prepare AJAX post vars */
function step_1_update( attr ) {	
	var theStatus = attr['status'];
	var initialVars = attr['initialVars'];
	
	var content = '';
	if ( tinyMCE.get('course_excerpt') ){
		content = tinyMCE.get('course_excerpt').getContent();
	} else {
		content = $( '[name=course_excerpt]' ).val();
	}
	
	var _thumbnail_id = '';
	if ( $( '[name=_thumbnail_id]' ) ) {
		_thumbnail_id = $( '[name=_thumbnail_id]' ).val()
	}
	
	return {
		// Don't remove
		action: initialVars['action'],
		course_id: initialVars['course_id'],
		course_name: initialVars['course_name'],

		// Alter as required
		course_excerpt: content,
		meta_featured_url: $( '[name=meta_featured_url]' ).val(),
		_thumbnail_id: _thumbnail_id,
		meta_course_category: $( '[name=meta_course_category]' ).val(),
		meta_course_language: $( '[name=meta_course_language]' ).val(),
		
		// Don't remove
		meta_course_setup_progress: initialVars['meta_course_setup_progress'],
		meta_course_setup_marker: 'step-2',
	}
}

function step_2_update( attr ) {
	var theStatus = attr['status'];
	var initialVars = attr['initialVars'];
	
	var content = '';
	if ( tinyMCE.get('course_description') ){
		content = tinyMCE.get('course_description').getContent();
	} else {
		content = $( '[name=course_description]' ).val();
	}
	
	return {
		// Don't remove
		action: initialVars['action'],
		course_id: initialVars['course_id'],
		course_name: initialVars['course_name'],
				
		// Alter as required
		meta_course_video_url: $( '[name=meta_course_video_url]' ).val(),
		course_description: content,
		meta_course_structure_options: $( '[name=meta_course_structure_options]' ).is( ':checked' ) ? 'on' : 'off',
		meta_course_structure_time_display: $( '[name=meta_course_structure_time_display]' ).is( ':checked' ) ? 'on' : 'off',
		
		// Don't remove
		meta_course_setup_progress: initialVars['meta_course_setup_progress'],
		meta_course_setup_marker: 'step-3',
	}
}

function step_3_update( attr ) {
	var theStatus = attr['status'];
	var initialVars = attr['initialVars'];
	
	var instructors = $("input[name^=instructor]").map(function(){return $(this).val();}).get();
	if ( $( instructors ).length == 0 ){
		instructors = 0;
	}
	
	return {
		// Don't remove
		action: initialVars['action'],
		course_id: initialVars['course_id'],
		course_name: initialVars['course_name'],
				
		// Alter as required
		instructor: instructors,
		
		// Don't remove
		meta_course_setup_progress: initialVars['meta_course_setup_progress'],
		meta_course_setup_marker: 'step-4',
	}
}

function step_4_update( attr ) {
	var theStatus = attr['status'];
	var initialVars = attr['initialVars'];
	
	return {
		// Don't remove
		action: initialVars['action'],
		course_id: initialVars['course_id'],
		course_name: initialVars['course_name'],
				
		// Alter as required
		meta_open_ended_course: $( '[name=meta_open_ended_course]' ).is( ':checked' ) ? 'on' : 'off',
		meta_course_start_date: $( '[name=meta_course_start_date]' ).val(),
		meta_course_end_date: $( '[name=meta_course_end_date]' ).val(),
		meta_open_ended_enrollment: $( '[name=meta_open_ended_enrollment]' ).is( ':checked' ) ? 'on' : 'off',
		meta_enrollment_start_date: $( '[name=meta_enrollment_start_date]' ).val(),
		meta_enrollment_end_date: $( '[name=meta_enrollment_end_date]' ).val(),
		
		// Don't remove
		meta_course_setup_progress: initialVars['meta_course_setup_progress'],
		meta_course_setup_marker: 'step-5',
	}
}

function step_5_update( attr ) {
	var theStatus = attr['status'];
	var initialVars = attr['initialVars'];
	
	return {
		// Don't remove
		action: initialVars['action'],
		course_id: initialVars['course_id'],
		course_name: initialVars['course_name'],
				
		// Alter as required
		meta_limit_class_size: $( '[name=meta_limit_class_size]' ).is( ':checked' ) ? 'on' : 'off',
		meta_class_size: $( '[name=meta_class_size]' ).val(),
		meta_allow_course_discussion: $( '[name=meta_allow_course_discussion]' ).is( ':checked' ) ? 'on' : 'off',
		meta_allow_workbook_page: $( '[name=meta_allow_workbook_page]' ).is( ':checked' ) ? 'on' : 'off',
		
		// Don't remove
		meta_course_setup_progress: initialVars['meta_course_setup_progress'],
		meta_course_setup_marker: 'step-6',
	}
}

function step_6_update( attr ) {
	var theStatus = attr['status'];
	var initialVars = attr['initialVars'];
	
	return {
		// Don't remove
		action: initialVars['action'],
		course_id: initialVars['course_id'],
		course_name: initialVars['course_name'],
				
		// Alter as required
		
		// Don't remove
		meta_course_setup_progress: initialVars['meta_course_setup_progress'],
		meta_course_setup_marker: initialVars['meta_course_setup_marker'],
	}
}

function courseAutoUpdate( step ) {
	$ = jQuery;
	var theStatus = $( $( '.course-section.step-' + step + ' .course-section-title h3' )[0] ).siblings( '.status' )[0];

	var statusNice = '';
	if( $( theStatus ).hasClass( 'saved' ) ) { statusNice = 'saved'; }
	if( $( theStatus ).hasClass( 'invalid' ) ) { statusNice = 'invalid'; }
	if( $( theStatus ).hasClass( 'attention' ) ) { statusNice = 'attention'; }
	$( theStatus ).removeClass( 'saved' );
	$( theStatus ).removeClass( 'invalid' );
	$( theStatus ).removeClass( 'attention' );
	
	if ( $( '.step-' + step + '.dirty' )[0] ) {
		$( theStatus ).addClass( 'progress' );
		
		// Course ID
		var course_id = $( '[name=course_id]' ).val();
		if ( ! course_id ) {
			course_id = $.urlParam('course_id');
			$( '[name=course_id]' ).val( course_id );
		}
		
		// Setup course progress markers and statuses		
		set_update_progress( 'step-' + step, 'saved' );
		var meta_course_setup_progress = get_meta_course_setup_progress();

	    var initial_vars = {
	   		action: 'autoupdate_course_settings',
	   		course_id: course_id,
			course_name: $( '[name=course_name]' ).val(),
	   		meta_course_setup_progress: meta_course_setup_progress,
	   		meta_course_setup_marker: 'step-' + step,
	   	}

		var func = 'step_' + step + '_update';
		// Get the AJAX post vars from step_[x]_update();
		var post_vars = window[func]( { status: theStatus, initialVars: initial_vars } );
		
		// AJAX CALL
        $.post(
			'admin-ajax.php', post_vars
		).done( function( data, status ) {
			// Set a course_id if its still empty
			course_id = $( data ).find('response_data').text();
			console.log( course_id );
			if ( $( '[name=course_id]' ).val() == 0 && course_id ) {
				$( '[name=course_id]' ).val( course_id );
			}
			// Handle return
			autosave_course_setup_done( data, status, 'step-' + step, theStatus );
		}).fail( function( data ) {
		});
		
	} else {
		$( theStatus ).addClass( statusNice );
	}
}

function sendInstructotInvitation() {
	
	
	
	
}

/** Handle Course Setup Wizard */
jQuery(document).ready(function( $ ){

	/** Proceed to next step. */
	$( '.course-section.step input.next' ).click( function( e ) {

		/**
		 * Get the current step we're on. 
		 *
		 * Looks for <div class="course-section step step-[x]"> and extracts the number.
		 **/
		var course_section = $( this ).parents( '.course-section.step' )[0];
		var step = $( course_section ).attr( 'class' ).match(/step-\d+/)[0].replace( /^\D+/g, '');
		
		// Next section
		var nextStep = parseInt( step ) + 1;
		
		// Attempt to get the next section.
		var nextSection = $( this ).parents('.course-details .course-section').siblings('.step-' + nextStep)[0];
		
		// If next section exists
		if ( nextSection ) {
			// There is a 'next section'. What do you want to do with it?
			var newTop = $('.step-'+step).position().top + 130;
			
			// Jump first, then animate		
			$( document ).scrollTop( newTop );

			$( nextSection ).children('.course-form').slideDown( 500 );
			$( nextSection ).children('.course-section-title').animate( { backgroundColor: '#0091cd' }, 500);
			$( nextSection ).children('.course-section-title').animate( { color: '#FFFFFF' }, 500);
			$( this ).parents('.course-form').slideUp( 500 );
			$( this ).parents('.course-section').children('.course-section-title').animate( { backgroundColor: '#F1F1F1' }, 500);
			$( this ).parents('.course-section').children('.course-section-title').animate( { color: '#222' }, 500);
			
			$( nextSection ).addClass('active');
			$( this ).parents('.course-section').removeClass('active');
			
			/* Time to call some Ajax */
			courseAutoUpdate( step );
			
		} else {
			// There is no 'next sections'. Now what?
		}
	});
	
	/** Return to previous step. */
	$( '.course-section.step input.prev' ).click( function( e ) {

		/**
		 * Get the current step we're on. 
		 *
		 * Looks for <div class="course-section step step-[x]"> and extracts the number.
		 **/
		var step = $( $( this ).parents( '.course-section.step' )[0] ).attr( 'class' ).match(/step-\d+/)[0].replace( /^\D+/g, '');
		
		// Previous section
		var prevStep = parseInt( step ) -1;
		
		// Attempt to get the previous section.
		var prevSection = $( this ).parents('.course-details .course-section').siblings('.step-' + prevStep)[0];

		// If previous section exists
		if ( prevSection ) {
			// There is a 'previous section'. What do you want to do with it?
			var newTop = $('.step-'+prevStep).offset().top - 50;			
			$( prevSection ).children('.course-form').slideDown( 500 );
			$( prevSection ).children('.course-section-title').animate( { backgroundColor: '#0091cd' }, 500);
			$( prevSection ).children('.course-section-title').animate( { color: '#FFFFFF' }, 500);
			$( this ).parents('.course-form').slideUp( 500 );
			$( this ).parents('.course-section').children('.course-section-title').animate( { backgroundColor: '#F1F1F1' }, 500);
			$( this ).parents('.course-section').children('.course-section-title').animate( { color: '#222' }, 500);
			
			// Animate first then jump
			$( document ).scrollTop( newTop );			
			$( prevSection ).addClass('active');			
			$( this ).parents('.course-section').removeClass('active');						
			
			/* Time to call some Ajax */
			courseAutoUpdate( step );
			
		} else {
			// There is no 'previous sections'. Now what?
		}
	});
	
	$( '.course-section.step .course-section-title h3' ).click( function( e ) {
		
		// Get current "active" step
		var activeElement = $( '.course-section.step.active' )[0];
		var activeStep = $( activeElement ).attr( 'class' ).match(/step-\d+/)[0].replace( /^\D+/g, '');

		var thisElement = $( this ).parents( '.course-section.step' )[0];
		var thisStep = $( thisElement ).attr( 'class' ).match(/step-\d+/)[0].replace( /^\D+/g, '');
				
		var thisStatus = $( this ).siblings( '.status' )[0];
		
		// Only move to a saved step or a previous step (asuming that it has to be saved)
		if ( $( thisStatus ).hasClass( 'saved' ) || $( thisStatus ).hasClass( 'attention' ) || thisStep < activeStep ) {

			// There is a 'previous section'. What do you want to do with it?
			if ( thisStep < activeStep ) {
				var newTop = $( thisElement ).position().top + 130;							
			} else {
				var step = thisStep + 1;
				var newTop = $( thisElement ).prev( '.step' ).offset().top + 20;	
			}

			$( thisElement ).children('.course-form').slideDown( 500 );
			$( thisElement ).children('.course-section-title').animate( { backgroundColor: '#0091cd' }, 500);
			$( thisElement ).children('.course-section-title').animate( { color: '#FFFFFF' }, 500);
			$( activeElement ).children('.course-form').slideUp( 500 );
			$( activeElement ).children('.course-section-title').animate( { backgroundColor: '#F1F1F1' }, 500);
			$( activeElement ).children('.course-section-title').animate( { color: '#222' }, 500);
			
			// Animate first then jump
			$( document ).scrollTop( newTop );			
			$( thisElement ).addClass('active');			
			$( activeElement ).removeClass('active');						

		} else {
			$( $( this ).parent() ).effect( 'shake', { distance: 10 }, 100 );
		}
	});	
	
    $( '#invite-instructor-trigger' ).click( function() {
		
        // var instructor_id = jQuery( '#instructors option:selected' ).val();
		
		// Mark as dirty
		// var parent_section = jQuery( this ).parents( '.course-section.step' )[0];
		// if ( parent_section ) {
		// 	if ( ! jQuery( parent_section ).hasClass( 'dirty' ) ) {
		// 		jQuery( parent_section ).addClass( 'dirty' );
		// 	}
		// }

        // if ( jQuery( "#instructor_holder_" + instructor_id ).length == 0 ) {
// 			jQuery( '.instructor-avatar-holder.empty' ).hide();
//             jQuery( '#instructors-info' ).append( '<div class="instructor-avatar-holder" id="instructor_holder_' + instructor_id + '"><div class="instructor-status"></div><div class="instructor-remove"><a href="javascript:removeInstructor( ' + instructor_id + ' );"><i class="fa fa-times-circle cp-move-icon remove-btn"></i></a></div>' + instructor_avatars[instructor_id] + '<span class="instructor-name">' + jQuery( '#instructors option:selected' ).text() + '</span></div><input type="hidden" id="instructor_' + instructor_id + '" name="instructor[]" value="' + instructor_id + '" />' );
//         }

		// Course ID
		var course_id = $( '[name=course_id]' ).val();
		if ( ! course_id ) {
			course_id = $.urlParam('course_id');
			$( '[name=course_id]' ).val( course_id );
		}
		
	
		$.post(
			'admin-ajax.php', {
				action: 'send_instructor_invite',
				first_name: $( '[name=invite_instructor_first_name]' ).val(),
				last_name: $( '[name=invite_instructor_last_name]' ).val(),
				email: $( '[name=invite_instructor_email]' ).val(),
				course_id: course_id,
			}
		).done( function( data, status ) {
			// Handle return
			if ( status == 'success' ) {
			} else {
			}	
		}).fail( function( data ) {
		});
		
        // jQuery.get( 'admin-ajax.php', {action: 'assign_instructor_capabilities', user_id: instructor_id} )
        //         .success( function( data ) {
        //             //alert( data );
        //         } );
    } );
	

	/** Mark "dirty" content */
	$( '.course-form input' ).change(function() {
		if ( ! $( $( this ).parents( '.course-section.step' )[0] ).hasClass( 'dirty' ) ) {
			$( $( this ).parents( '.course-section.step' )[0] ).addClass( 'dirty' );
		}
	});
	$( '.course-form textarea' ).change(function() {
		if ( ! $( $( this ).parents( '.course-section.step' )[0] ).hasClass( 'dirty' ) ) {
			$( $( this ).parents( '.course-section.step' )[0] ).addClass( 'dirty' );
		}
	});
	$( '.course-form select' ).change(function() {
		if ( ! $( $( this ).parents( '.course-section.step' )[0] ).hasClass( 'dirty' ) ) {
			$( $( this ).parents( '.course-section.step' )[0] ).addClass( 'dirty' );
		}
	});
	
	
			
});