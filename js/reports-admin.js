jQuery(function() {
    // bind change event to select
    jQuery('#dynamic_courses').bind('change', function() {
        jQuery('#dynamic_classes').val('all');
        jQuery("#course-filter").submit();
    });

    jQuery('#dynamic_classes').bind('change', function() {
        jQuery("#course-filter").submit();
    });
    
    jQuery('.pdf').click(function(){
        jQuery(".check-column input").prop('checked', false);
        jQuery(this).closest('tr').find(".check-column input").prop('checked', true);
        jQuery("#generate-report").submit();
    });
    

});