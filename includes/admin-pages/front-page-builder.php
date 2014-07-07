<?php global $coursepress_front_page_modules; ?>
<div class="wrap nosubsub coursepress_page_course_details">
    <h2><?php _e('Front Page Builder', 'cp'); ?></h2>

    <div class="wrap mp-wrap nocoursesub">

        <div class="mp-settings"><!--course-liquid-left-->
            <form action="" name="unit-add" id="unit-add" class="unit-add" method="post">

                <div class="section elements-section">
                    <input type="hidden" name="beingdragged" id="beingdragged" value="">
                    <div id="course">


                        <div id="edit-sub" class="course-holder-wrap elements-wrap">

                            <div class="course-holder">

                                <div id="unit-pages" >
                                    <div class="page-builder-title"><span><?php _e('Front Page Builder', 'cp') ?></span></div>

                                    <div id="unit-page-1" aria-labelledby="ui-id-1" class="ui-tabs-panel ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="true" aria-hidden="false">
                                        <div class="course-details elements-holder">
                                            <div class="unit_page_title">
                                                <div class="description"><?php _e('Click to add elements to the Front page', 'cp'); ?></div>
                                            </div>
                                            <div class="output-element audio_module">
                                                <span class="element-label">
                                                    Audio                                                    </span>
                                                <a class="add-element" id="audio_module"></a>
                                            </div>
                                            <div class="output-element chat_module">
                                                <span class="element-label">
                                                    Live Chat                                                    </span>
                                                <a class="add-element" id="chat_module"></a>
                                            </div>
                                            <div class="output-element file_module">
                                                <span class="element-label">
                                                    File Download                                                    </span>
                                                <a class="add-element" id="file_module"></a>
                                            </div>
                                            <div class="output-element image_module">
                                                <span class="element-label">
                                                    Image                                                    </span>
                                                <a class="add-element" id="image_module"></a>
                                            </div>
                                            <div class="output-element section_break_module">
                                                <span class="element-label">
                                                    Section Break                                                    </span>
                                                <a class="add-element" id="section_break_module"></a>
                                            </div>
                                            <div class="output-element text_module">
                                                <span class="element-label">
                                                    Text                                                    </span>
                                                <a class="add-element" id="text_module"></a>
                                            </div>
                                            <div class="output-element video_module">
                                                <span class="element-label">
                                                    Video                                                    </span>
                                                <a class="add-element" id="video_module"></a>
                                            </div>





                                            <div class="input-element page_break_module">
                                                <span class="element-label">
                                                    Page Break                                                    </span>
                                                <a class="add-element" id="page_break_module"></a>
                                            </div>

                                            <hr>

                                            <span class="no-elements">No elements have been added to front page yet</span>

                                        </div>



                                        <div class="modules_accordion ui-accordion ui-widget ui-helper-reset ui-sortable" role="tablist">
                                            <!--modules will appear here-->
                                        </div>

                                    </div>
                                </div>

                                <div class="course-details-unit-controls">
                                    <div class="unit-control-buttons">

                                        <input type="submit" name="submit-unit" class="button button-units save-unit-button" value="Save">

                                    </div>
                                </div>

                            </div><!--/course-holder-->
                        </div><!--/course-holder-wrap-->
                    </div><!--/course-->
                </div> <!-- /section -->
            </form>			
        </div> <!-- course-liquid-left -->

        <div class='level-liquid-right' style="display:block;">
            <div class="level-holder-wrap">
                <?php
                if (isset($coursepress_front_page_modules['output'])) {
                    foreach ($coursepress_front_page_modules['output'] as $mmodule => $mclass) {
                        $module = new $mclass();
                        $module->admin_main(array());
                    }
                }
                ?>
            </div> <!-- level-holder-wrap -->

        </div> <!-- level-liquid-right -->

        <script type="text/javascript">
            /*jQuery(document).ready(function() {
             //coursepress_no_elements();
             jQuery('.modules_accordion .switch-tmce').each(function() {
             jQuery(this).trigger('click');
             });
             
             var current_page = jQuery('#unit-pages .ui-tabs-nav .ui-state-active a').html();
             var elements_count = jQuery('#unit-page-1 .modules_accordion .module-holder-title').length;
             
             //jQuery('#unit-page-' + current_unit_page + ' .elements-holder .no-elements').show();
             
             if ((current_page == 1 && elements_count == 0) || (current_page >= 2 && elements_count == 1)) {
             jQuery('#unit-page-' + current_page + ' .elements-holder .no-elements').show();
             } else {
             jQuery('#unit-page-' + current_page + ' .elements-holder .no-elements').hide();
             }
             });*/
        </script>
    </div> <!-- wrap -->
</div>