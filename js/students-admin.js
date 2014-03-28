function delete_student_confirmed() {
    return confirm(student.delete_student_alert);
}

function removeStudent() {
    if (delete_student_confirmed()) {
        return true;
    } else {
        return false;
    }
}

jQuery(function() {
    // bind change event to select
    jQuery('#dynamic_courses').bind('change', function() {
        jQuery('#dynamic_classes').val('all');
        jQuery("#course-filter").submit();
    });

    jQuery('#ungraded').bind('change', function() {
        if (jQuery('#ungraded').is(':checked')) {
            jQuery('#ungraded').val('yes');
        } else {
            jQuery('#ungraded').val('no');
        }
        jQuery("#course-filter").submit();
    });

    jQuery('#units_accordion').accordion({
        heightStyle: "content"
    });
});