jQuery(document).ready(function($) {
    jQuery('.front-page-builder-elements-holder div').live('click', function() {


        var stamp = new Date().getTime();
        var module_count = 0;


        jQuery('input#beingdragged').val(jQuery(this).find('.add-element').attr('id'));//jQuery( "#unit-page-" + current_unit_page + " .unit-module-list option:selected" ).val()

        var cloned = jQuery('.draggable-module-holder-' + jQuery('input#beingdragged').val()).html();

        cloned = '<div class="module-holder-' + jQuery('input#beingdragged').val() + ' module-holder-title">' + cloned + '</div>';
        jQuery('.modules_accordion').append(cloned);

        var data = '';

        jQuery('.modules_accordion').accordion();
        jQuery('.modules_accordion').accordion("refresh");
        jQuery('.modules_accordion').accordion("option", "active", -1);

        moving = jQuery('input#beingdragged').val();

        if (moving != '') {

        }

        jQuery('.module_order').each(function(i, obj) {
            jQuery(this).val(i + 1);
            module_count = i;
        });

        module_count = module_count - jQuery(".unit-module-list option").size();

        /* Dynamic WP Editor */
        moving = jQuery('input#beingdragged').val();

        var rand_id = 'rand_id' + Math.floor((Math.random() * 99999) + 100) + '_' + Math.floor((Math.random() * 99999) + 100) + '_' + Math.floor((Math.random() * 99999) + 100);
        var text_editor = '<textarea name="' + moving + '_content[]" id="' + rand_id + '"></textarea>';

        var text_editor_whole =
                '<div id="wp-' + rand_id + '-wrap" class="wp-core-ui wp-editor-wrap tmce-active">' +
                '<div id="wp-' + rand_id + '-editor-tools" class="wp-editor-tools hide-if-no-js">' +
                '<div id="wp-' + rand_id + '-media-buttons" class="wp-media-buttons"><a href="#" class="button insert-media-cp add_media" data-editor="' + rand_id + '" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a></div>' +
                '<div id="wp-' + rand_id + '-editor-container" class="wp-editor-container">' +
                text_editor +
                '</div></div></div>';

        jQuery('.modules_accordion .editor_in_place').last().html(text_editor_whole);

        tinyMCE.init({
            mode: "exact",
            elements: rand_id,
            toolbar: "bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo",
            menubar: false
        });

        var accordion_elements_count = (jQuery(this).parent().parent().find('.modules_accordion div.module-holder-title').length);//find('.modules_accordion').length

        if ((current_unit_page == 1 && accordion_elements_count == 0) || (current_unit_page >= 2 && accordion_elements_count == 1)) {
            jQuery('#unit-page-' + current_unit_page + ' .elements-holder .no-elements').show();
        } else {
            jQuery('#unit-page-' + current_unit_page + ' .elements-holder .no-elements').hide();
        }

    });
});