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

	if ( tab == 'overview' ) {
		// Mark as dirty
		$( '#' + ed.id ).parents('.course-section').addClass('dirty');
		active_editor = ed.id;
	}
	
}

// Detect mouse movement in the wp_editor
function cp_editor_mouse_move( ed, event ) {
}


/** AJAX UPDATES */
function step_1_update( attr ) {	
	var theStatus = attr['status'];
	
	if ( $( $( '.step-1.dirty' )[0] ).hasClass( 'dirty' ) ) {
		$( theStatus ).addClass( 'progress' );

		var course_id = 0;
		if ( $.urlParam('course_id') != 0 ) {
			course_id = $.urlParam('course_id');
		}

		var mce_id = $( $( '[name=course_name]' ).parents('.step-1')[0] ).find('[mce]').val();
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
		
        jQuery.post(
			'admin-ajax.php', 
			{	
				action: 'autoupdate_course_settings', 
				course_id: course_id,
				course_name: $( '[name=course_name]' ).val(),
				course_excerpt: content,
				meta_featured_url: $( '[name=meta_featured_url]' ).val(),
				_thumbnail_id: _thumbnail_id,
				meta_course_category: $( '[name=meta_course_category]' ).val(),
				meta_course_language: $( '[name=meta_course_language]' ).val(),
			}
		).done(function( data, status ) {
			if ( status == 'success' ) {
				$( theStatus ).removeClass( 'progress' );
				$( theStatus ).addClass( 'saved' );
			}
		}).fail( function( data ) {
		});

	} else {
		$( theStatus ).addClass( attr['niceStatus'] );
	}
}

function step_2_update( attr ) {
	var theStatus = attr['status'];
	
	if ( $( $( '.step-1.dirty' )[0] ).hasClass( 'dirty' ) ) {
		$( theStatus ).addClass( 'progress' );






	} else {
		$( theStatus ).addClass( attr['niceStatus'] );
	}
}

function step_3_update( attr ) {
	var theStatus = attr['status'];
	
	if ( $( $( '.step-1.dirty' )[0] ).hasClass( 'dirty' ) ) {
		$( theStatus ).addClass( 'progress' );






	} else {
		$( theStatus ).addClass( attr['niceStatus'] );
	}
}

function step_4_update( attr ) {
	var theStatus = attr['status'];
	
	if ( $( $( '.step-1.dirty' )[0] ).hasClass( 'dirty' ) ) {
		$( theStatus ).addClass( 'progress' );






	} else {
		$( theStatus ).addClass( attr['niceStatus'] );
	}
}

function step_5_update( attr ) {
	var theStatus = attr['status'];
	
	if ( $( $( '.step-1.dirty' )[0] ).hasClass( 'dirty' ) ) {
		$( theStatus ).addClass( 'progress' );






	} else {
		$( theStatus ).addClass( attr['niceStatus'] );
	}
}

function step_6_update( attr ) {
	var theStatus = attr['status'];
	
	if ( $( $( '.step-1.dirty' )[0] ).hasClass( 'dirty' ) ) {
		$( theStatus ).addClass( 'progress' );






	} else {
		$( theStatus ).addClass( attr['niceStatus'] );
	}
}

function courseAutoUpdate( step ) {
	$ = jQuery;
	var theStatus = $( $( '.course-section.step-' + step + ' .course-section-title h3' )[0] ).siblings( '.status' )[0];
	
	var statusNice = 'z';
	if( $( theStatus ).hasClass( 'saved' ) ) { statusNice = 'saved'; }
	if( $( theStatus ).hasClass( 'invalid' ) ) { statusNice = 'invalid'; }
	if( $( theStatus ).hasClass( 'attention' ) ) { statusNice = 'attention'; }
	$( theStatus ).removeClass( 'saved' );
	$( theStatus ).removeClass( 'invalid' );
	$( theStatus ).removeClass( 'attention' );
	
	var func = 'step_' + step + '_update';
	window[func]( { status: theStatus, niceStatus: statusNice } );
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