jQuery(function() {
    // bind change event to select
    jQuery('#dynamic_courses').bind('change', function() {
        jQuery('#dynamic_classes').val('all');
        jQuery("#course-filter").submit();
    });

    jQuery('#dynamic_classes').bind('change', function() {
        jQuery("#course-filter").submit();
    });

});