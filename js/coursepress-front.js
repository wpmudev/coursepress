function disenroll_confirmed() {
    return confirm(student.disenroll_alert);
}

function disenroll() {
    if (disenroll_confirmed()) {
        return true;
    } else {
        return false;
    }
}
jQuery(document).ready(function() {
    jQuery('.save_elements_message_ok').delay(2000).fadeOut('slow'); 
});