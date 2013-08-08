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

    jQuery(function() {
        jQuery("#modules_accordion").sortable({
            handle: "h3",
            /*placeholder: "ui-state-highlight",*/
            stop: function(event, ui) {
                //update_sortable_indexes();
            }
        });

        jQuery("#sortable-units").disableSelection();
    });

    jQuery(function() {

        jQuery("#students_accordion").accordion({
            heightStyle: "content",
            active: parseInt(coursepress.active_student_tab)
        });

        jQuery("#modules_accordion").accordion({
            heightStyle: "content",
        }).sortable({
            axis: "y",
            stop: function(event, ui) {
                // IE doesn't register the blur when sorting
                // so trigger focusout handlers to remove .ui-state-focus
                //ui.item.children("h3").triggerHandler("focusout");
            }
        });


    });

    jQuery('#open_ended_course').change(function() {
        if (this.checked) {
            jQuery('#all_course_dates').hide(500);
        }else{
            jQuery('#all_course_dates').show(500);
        }
    });

});