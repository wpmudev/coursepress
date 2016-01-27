jQuery(function() {
    jQuery(".spinners").spinner({
        min: 0,
		stop: function( event, ui ) {
			// Trigger change event.
			jQuery(this).change();
		},
    });
    jQuery('.dateinput').datepicker({
        dateFormat: 'yy-mm-dd'
    });
    

});

function delete_confirmed() {
    return confirm(courses.delete_alert);
}

function removeInstructor(instructor_id) {
    if (delete_confirmed()) {
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
    })

    /*jQuery('#add-instructor-trigger').click(function() {
     var instructor_id = jQuery('#instructors option:selected').val();
     
     if (jQuery("#instructor_holder_" + instructor_id).length == 0) {
     jQuery('#instructors-info').append('<div class="instructor-avatar-holder" id="instructor_holder_' + instructor_id + '"><div class="instructor-remove"><a href="javascript:removeInstructor(' + instructor_id + ');"><i class="fa fa-times-circle cp-move-icon remove-btn"></i></a></div>' + instructor_avatars[instructor_id] + '<span class="instructor-name">' + jQuery('#instructors option:selected').text() + '</span></div><input type="hidden" id="instructor_' + instructor_id + '" name="instructor[]" value="' + instructor_id + '" />');
     }
     });*/

});