   
jQuery(function() {
    jQuery( ".spinners" ).spinner({
        min: 0
    });
    jQuery('.dateinput').datepicker({
        dateFormat : 'yy-mm-dd'
    });
/*jQuery("#course-start-date").datepicker();
jQuery("#course-end-date").datepicker();
jQuery("#enrollment-start-date").datepicker();
jQuery("#enrollment-end-date").datepicker();
*/
//getFullYear
//getDate
//getMonth

//var currentDate = $( ".selector" ).datepicker( "getDate" );

});
function delete_course_confirmed(){
    return confirm(coursepress.delete_course_alert);
}

function removeCourse(){
    if(delete_course_confirmed()){
        return true;
    }else{
        return false;
    }
}

function delete_instructor_confirmed(){
    return confirm(coursepress.delete_instructor_alert);
}

function removeInstructor(instructor_id){
    if(delete_instructor_confirmed()){
        jQuery("#instructor_holder_"+instructor_id).remove();
        jQuery("#instructor_"+instructor_id).remove();
    }
}
    
jQuery(document).ready(function() {
    
    

  
    
    jQuery('#enroll_type').change(function(){
        var enroll_type = jQuery("#enroll_type").val();
        if(enroll_type == 'passcode'){
            jQuery("#enroll_type_holder").css({
                'display': 'block'
            });
        }else{
            jQuery("#enroll_type_holder").css({
                'display': 'none'
            });
        }
    })
    
    jQuery('#add-instructor-trigger').click(function(){
        var instructor_id = jQuery('#instructors option:selected').val();
       
        if (jQuery("#instructor_holder_"+instructor_id).length == 0){
            jQuery('#instructors-info').append('<div class="instructor-avatar-holder" id="instructor_holder_'+instructor_id+'"><div class="instructor-remove"><a href="javascript:removeInstructor('+instructor_id+');"></a></div>'+instructor_avatars[instructor_id]+'<span class="instructor-name">'+jQuery('#instructors option:selected').text()+'</span></div><input type="hidden" id="instructor_'+instructor_id+'" name="instructor[]" value="'+instructor_id+'" />');
        }
    });
    
});