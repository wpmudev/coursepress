/* UNIT MODULES */

function coursepress_module_click_action_toggle() {
    if (jQuery(this).parent().hasClass('open')) {
        jQuery(this).parent().removeClass('open').addClass('closed');
        jQuery(this).parents('.action').find('.action-body').removeClass('open').addClass('closed');
    } else {
        jQuery(this).parent().removeClass('closed').addClass('open');
        jQuery(this).parents('.action').find('.action-body').removeClass('closed').addClass('open');
    }
}

function coursepress_modules_ready() {

    jQuery('.draggable-module').draggable({
        opacity: 0.7,
        helper: 'clone',
        start: function(event, ui) {
            jQuery('input#beingdragged').val(jQuery(this).attr('id'));
        },
        stop: function(event, ui) {
            //jQuery('input#beingdragged').val('');
        }
    });

    jQuery('.module-droppable').droppable({
        hoverClass: 'hoveringover',
        drop: function(event, ui) {
            var stamp = new Date().getTime();
            var cloned = jQuery('.draggable-module-holder-' + jQuery('input#beingdragged').val()).html();
            cloned = '<div class="module-holder-' + jQuery('input#beingdragged').val() + ' module-holder-title">' + cloned + '</div>';

            jQuery('.modules_accordion').html(cloned + jQuery('.modules_accordion').html());

            var data = '';
            jQuery.post('admin-ajax.php?action=dynamic_wp_editor', data, function(response) {
                jQuery('#modules_accordion .editor_to_place').html(response);
            });

            /*            jQuery('textarea, div, a, input').each(function() {
             var attr = jQuery(this).attr('id');
             var str_to_replace = '';
             
             if (typeof attr !== 'undefined' && attr !== false) {
             var current_object_id = jQuery(this).attr('id');
             var matched_results = new Array();
             matched_results = current_object_id.match(/id_placeholder/g);
             if (typeof matched_results !== 'undefined' && matched_results !== null) {
             str_to_replace = jQuery(this).attr('id');
             
             jQuery(this).attr('id', str_to_replace.replace('id_placeholder','editor_' + stamp));
             }
             }
             });
             
             jQuery('a').each(function() {
             var attr = jQuery(this).attr('data-editor');
             var str_to_replace = '';
             
             if (typeof attr !== 'undefined' && attr !== false) {
             var current_object_id = jQuery(this).attr('data-editor');
             var matched_results = new Array();
             matched_results = current_object_id.match(/id_placeholder/g);
             if (typeof matched_results !== 'undefined' && matched_results !== null) {
             str_to_replace = jQuery(this).attr('data-editor');
             
             jQuery(this).attr('data-editor', str_to_replace.replace('id_placeholder','editor_' + stamp));
             }
             }
             });*/



            jQuery('.modules_accordion').accordion("refresh");

            moving = jQuery('input#beingdragged').val();
            if (moving != '') {
                /*
                 jQuery('#main-' + moving).prependTo('#' + ruleplace + '-holder');
                 jQuery('#' + moving).hide();
                 
                 // redisplay our one
                 jQuery('#main-' + moving).removeClass('closed').addClass('open');
                 */
            }
        }
    });

    jQuery('.action .action-top .action-button').click(coursepress_module_click_action_toggle);

}

jQuery(document).ready(coursepress_modules_ready);

/* END-UNIT MODULES*/

jQuery(function() {
    jQuery(".spinners").spinner({
        min: 0
    });
    jQuery('.dateinput').datepicker({
        dateFormat: 'yy-mm-dd'
    });

});

function unenroll_student_confirmed() {
    return confirm(coursepress.unenroll_student_alert);
}

function unenrollStudent() {
    if (unenroll_student_confirmed()) {
        return true;
    } else {
        return false;
    }
}

function delete_course_confirmed() {
    return confirm(coursepress.delete_course_alert);
}

function removeCourse() {
    if (delete_course_confirmed()) {
        return true;
    } else {
        return false;
    }
}

function removeUnit() {
    if (delete_unit_confirmed()) {
        return true;
    } else {
        return false;
    }
}

function delete_unit_confirmed() {
    return confirm(coursepress.delete_unit_alert);
}

function delete_instructor_confirmed() {
    return confirm(coursepress.delete_instructor_alert);
}

function removeInstructor(instructor_id) {
    if (delete_instructor_confirmed()) {
        jQuery("#instructor_holder_" + instructor_id).remove();
        jQuery("#instructor_" + instructor_id).remove();
    }
}

jQuery(document).ready(function() {
    jQuery('#enroll_type').change(function() {
        var enroll_type = jQuery("#enroll_type").val();
        if (enroll_type == 'passcode') {
            jQuery("#enroll_type_holder").css({
                'display': 'block'
            });
        } else {
            jQuery("#enroll_type_holder").css({
                'display': 'none'
            });
        }
    });

    jQuery('#add-instructor-trigger').click(function() {
        var instructor_id = jQuery('#instructors option:selected').val();

        if (jQuery("#instructor_holder_" + instructor_id).length == 0) {
            jQuery('#instructors-info').append('<div class="instructor-avatar-holder" id="instructor_holder_' + instructor_id + '"><div class="instructor-remove"><a href="javascript:removeInstructor(' + instructor_id + ');"></a></div>' + instructor_avatars[instructor_id] + '<span class="instructor-name">' + jQuery('#instructors option:selected').text() + '</span></div><input type="hidden" id="instructor_' + instructor_id + '" name="instructor[]" value="' + instructor_id + '" />');
        }
    });

    /*jQuery(function() {
     jQuery("#modules_accordion").sortable({
     handle: "h3",
     stop: function(event, ui) {
     //update_sortable_indexes();
     }
     });
     
     jQuery("#modules_accordion").disableSelection();
     });*/

    jQuery(function() {

        jQuery("#students_accordion").accordion({
            heightStyle: "content",
            active: parseInt(coursepress.active_student_tab)
        });

        jQuery("#modules_accordion").accordion({
            heightStyle: "content",
            header: "> div > h3"
        }).sortable({
            axis: "y",
            stop: function(event, ui) {
                // IE doesn't register the blur when sorting
                // so trigger focusout handlers to remove .ui-state-focus
                //ui.item.children("h3").triggerHandler("focusout");
                update_sortable_module_indexes();
            }
        });
    });

    function update_sortable_module_indexes() {
        jQuery('.module_order').each(function(i, obj) {
            jQuery(this).val(i + 1);
        });
    }



    jQuery('#open_ended_course').change(function() {
        if (this.checked) {
            jQuery('#all_course_dates').hide(500);
        } else {
            jQuery('#all_course_dates').show(500);
        }
    });

});