function delete_class_confirmed() {
    return confirm(coursepress_units.delete_class);
}

function deleteClass() {
    if (delete_class_confirmed()) {
        return true;
    } else {
        return false;
    }
}

function unenroll_all_from_class_confirmed() {
    return confirm(coursepress_units.unenroll_class_alert);
}

function unenrollAllFromClass() {
    if (unenroll_all_from_class_confirmed()) {
        return true;
    } else {
        return false;
    }
}

jQuery(function() {
    jQuery("#sortable-units").sortable({
        placeholder: "ui-state-highlight",
        stop: function(event, ui) {
            update_sortable_indexes();
        }
    });

    jQuery("#sortable-units").disableSelection();
});

function update_sortable_indexes() {
    jQuery('.numberCircle').each(function(i, obj) {
        jQuery(this).html(i + 1);
    });

    jQuery('.unit_order').each(function(i, obj) {
        jQuery(this).val(i + 1);
    });

    var positions = new Array();

    jQuery('.unit_id').each(function(i, obj) {
        positions[i] = jQuery(this).val();
    });

    var data = {
        action: 'update_units_positions',
        positions: positions.toString()
    };

    jQuery.post(ajaxurl, data, function(response) {
        //alert(response);
    });

}

/* Native WP media browser for audio module (unit module) */

jQuery(document).ready(function()
{
    jQuery('.audio_url_button').click(function()
    {
        var target_url_field = jQuery(this).prevAll(".audio_url:first");
        
        wp.media.editor.send.attachment = function(props, attachment)
        {
            jQuery(target_url_field).val(attachment.url);
        };
        wp.media.editor.open(this);
        return false;
    });
});

/* Native WP media browser for video module (unit module) */

jQuery(document).ready(function()
{
    jQuery('.video_url_button').click(function()
    {
        var target_url_field = jQuery(this).prevAll(".video_url:first");

        wp.media.editor.send.attachment = function(props, attachment)
        {
            jQuery(target_url_field).val(attachment.url);
        };

        wp.media.editor.open(this);
        return false;
    });
});

/* Native WP media browser for file module (for instructors) */

jQuery(document).ready(function()
{
    jQuery('.file_url_button').click(function()
    {
        
        var target_url_field = jQuery(this).prevAll(".file_url:first");

        wp.media.editor.send.attachment = function(props, attachment)
        {
            jQuery(target_url_field).val(attachment.url);
        };

        wp.media.editor.open(this);
        return false;
    });
});