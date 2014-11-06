function withdraw_confirmed() {
    return confirm(front_vars.withdraw_alert);
}

function withdraw() {
    if (withdraw_confirmed()) {
        return true;
    } else {
        return false;
    }
}

jQuery(document).ready(function() {
    /* Prevent click on disabled navigation links */
    jQuery('.disabled-link a').click(function(e) {
        e.preventDefault();
    });
});


jQuery(document).ready(function($) {
    // Use data-link attribute to follow links
    $('button').click(function(event) {
        if ($(this).data('link')) {
			event.preventDefault();
            window.location.href = $(this).data('link');
        }
    });
});

jQuery(document).ready(function($) {
    jQuery(".knob").knob();
});

jQuery(document).ready(function($){
$('#tos_agree').parent().find('br').remove();
$('[name="tos_agree"]').parent().find('br').remove();
});