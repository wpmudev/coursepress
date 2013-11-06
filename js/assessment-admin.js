jQuery(document).ready(function($) {
    jQuery('#tabs').tabs({
        //event: "mouseover" /*remove this if you want to open tabs on click */
    });
});



jQuery(function() {
    // bind change event to select
    jQuery('#dynamic_courses').bind('change', function() {
        var url = jQuery(this).val(); // get selected value
        if (url && url != '') { // require a URL
            window.location = url; // redirect
        }
        return false;
    });
});