<?php global $coursepress_front_page_modules, $coursepress_front_page_modules_labels, $coursepress_front_page_modules_descriptions, $coursepress_front_page_modules_ordered, $save_elements; ?>


<div class="wrap nosubsub coursepress_page_course_details">
    <h2><?php _e('Front Page Builder', 'cp'); ?></h2>

    <div class="wrap mp-wrap nocoursesub">

        <div class="mp-settings"><!--course-liquid-left-->
            <form action="<?php echo esc_attr(admin_url('admin.php?page=front_page_builder'));?>" name="front-page-builder-form" id="front-page-builder-form" class="unit-add" method="post">
                <?php wp_nonce_field('front_page_builder_' . $user_id); ?>
                <div class="section elements-section">
                    <input type="hidden" name="beingdragged" id="beingdragged" value="">
                    <div id="course">


                        <div id="edit-sub" class="course-holder-wrap elements-wrap">

                            <div class="course-holder front-page-builder">

                                <div id="unit-pages">
                                    <div class="page-builder-title"><span><?php _e('Front Page Builder', 'cp') ?></span></div>

                                    <?php
                                    $save_elements = true;

                                    $module = new Front_Page_Module();
                                    $modules = $module->get_modules();
                                    ?>
                                    <div id="unit-page-<?php echo $i; ?>">
                                        <div class='course-details front-page-builder-elements-holder'>
                                            <div class="unit_page_title">
                                                <div class="description"><?php _e('Click to add elements to the Front page', 'cp'); ?></div>
                                            </div>
                                            <?php
                                            foreach ($coursepress_front_page_modules_ordered['output'] as $element) {
                                                ?>
                                                <div class="output-element <?php echo $element; ?>">
                                                    <span class="element-label">
                                                        <?php
                                                        $module = new $element;
                                                        echo $module->label;
                                                        ?>
                                                    </span>
                                                    <a class="add-element" id="<?php echo $element; ?>"></a>
                                                </div>
                                                <?php
                                            }

                                            $save_elements = false;
                                            ?>

                                            <hr />

                                            <span class="no-elements"><?php _e('No elements have been added to this page yet'); ?></span>

                                        </div>

                                        <div class="modules_accordion">
                                            <!--modules will appear here-->
                                            <?php
                                            foreach ($modules as $mod) {
                                                $class_name = $mod->module_type;
                                                if (class_exists($class_name)) {
                                                    $module = new $class_name();
                                                    $module->admin_main($mod);
                                                }
                                            }
                                            ?>
                                        </div>

                                    </div>

                                </div>

                                <div class="course-details-unit-controls front-page-builder-controls">
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