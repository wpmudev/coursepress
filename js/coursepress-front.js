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
    
    /* Submit data on pagination */
    jQuery('.module-pagination a').click(function() {
        var action = jQuery("#modules_form").attr("action");
        jQuery("#modules_form").attr("action", action + 'page/' + jQuery(this).html() + '/');
        jQuery('.apply-button-enrolled').click();
        return false;
    });
    
    
    jQuery('.submit-elements-data-button').click(function() {
        var active_page = jQuery('#navigation-pagination .active a').html();
        var last_page  = jQuery('#navigation-pagination li:last-child a').html();
        var action = jQuery("#modules_form").attr("action");
        
        var next_page = 0;
        
        if(active_page != last_page){
            next_page = parseInt(active_page);
        }else{
            next_page = parseInt(last_page);
        }
       
        jQuery("#modules_form").attr("action", action + 'page/' + parseInt(next_page+1) + '/');
        //jQuery('.apply-button-enrolled').click();
        //return false;
    });

});