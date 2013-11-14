jQuery(document).ready(function($) {
    jQuery('#tabs').tabs({
        //event: "mouseover" /*remove this if you want to open tabs on click */
    });
});

jQuery(function() {
    // bind change event to select
    jQuery('#dynamic_courses').bind('change', function() {
        jQuery('#dynamic_classes').val('all');
        jQuery("#course-filter").submit();
    });

    jQuery('#dynamic_classes').bind('change', function() {
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

});