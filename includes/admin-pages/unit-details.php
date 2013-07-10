<?php
global $page, $user_id, $coursepress_admin_notice;
global $M_Rules, $M_SectionRules;

$course_id = '';

if (isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
}

if (isset($_GET['unit_id'])) {
    $unit = new Unit($_GET['unit_id']);
    $unit_details = $unit->get_unit();
    $unit_id = $_GET['unit_id'];
} else {
    $unit = new Unit();
    $unit_id = 0;
}

if (isset($_POST['action']) && ($_POST['action'] == 'add_unit' || $_POST['action'] == 'update_unit')) {

    if (wp_verify_nonce($_REQUEST['_wpnonce'], 'unit_details_overview_' . $user_id)) {

        $new_post_id = $unit->update_unit();

        if ($new_post_id != 0) {
            wp_redirect('?page=' . $page . '&tab=units&course_id=' . $course_id . '&action=edit&unit_id=' . $new_post_id);
        } else {
            //an error occured
        }
    }
}
?>

<div class='wrap nocoursesub'>
    <form action="?page=<?php echo esc_attr($page); ?>&tab=units&course_id=<?php echo $course_id; ?>&action=add_new_unit" name="unit-add" method="post">

        <div class='course-liquid-left'>

            <div id='course-left'>


                <?php wp_nonce_field('unit_details_overview_' . $user_id); ?>

                <?php if (isset($unit_id)) { ?>
                    <input type="hidden" name="course_id" value="<?php echo esc_attr($course_id); ?>" />
                    <input type="hidden" name="unit_id" value="<?php echo esc_attr($unit_id); ?>" />
                    <input type="hidden" name="action" value="update_unit" />
                    <input type="hidden" name="plugin_notice" value="<?php _e('Unit has been updated.', 'cp'); ?>" />
                <?php } else { ?>
                    <input type="hidden" name="action" value="add_unit" />
                    <input type="hidden" name="plugin_notice" value="<?php _e('New Unit has been created.', 'cp'); ?>" />
                <?php } ?>

                <div id='edit-sub' class='course-holder-wrap'>

                    <div class='sidebar-name no-movecursor'>
                        <h3><?php _e('Unit Details', 'cp'); ?></h3>
                    </div>

                    <div class='course-holder'>
                        <div class='course-details'>
                            <label for='unit_name'><?php _e('Unit Name', 'cp'); ?></label>
                            <input class='wide' type='text' name='unit_name' id='unit_name' value='<?php echo esc_attr(stripslashes($unit_details->post_title)); ?>' />
                            <br/><br/>
                            <label for='unit_description'><?php _e('Unit Description', 'cp'); ?></label>
                            <?php
                            $args = array("textarea_name" => "unit_description", "textarea_rows" => 5);

                            if (!isset($unit_details->post_content)) {
                                $unit_details = new StdClass;
                                $unit_details->post_content = '';
                            }

                            $desc = '';
                            wp_editor(stripslashes($unit_details->post_content), "unit_description", $args);
                            ?>
                            <br/>

                        </div>

                        <div class="level-droppable-rules levels-sortable ui-droppable">
                            <?php _e('Drag & Drop unit modules here', 'cp'); ?>
                        </div>

                        <ul class="leveltabs">
                            <li class="positivetab"></li>
                        </ul>
                        <div class="positivecontent activecontent">TEST</div>

                        <div class="buttons">
                            <input type="submit" value="<?php ($unit_id == 0 ? _e('Create', 'cp') : _e('Update', 'cp')); ?>" class="button-primary" />
                        </div>

                    </div>
                </div>

            </div>
        </div> <!-- course-liquid-left -->

        <!--<div class='level-liquid-left'>

            <div id='level-left'>
                <form action='?page=<?php echo $page; ?>' name='leveledit' method='post'>
                    <input type='hidden' name='level_id' id='level_id' value='<?php echo $level->id; ?>' />

                    <input type='hidden' name='ontab' id='ontab' value='positive' />

                    <input type='hidden' name='beingdragged' id='beingdragged' value='' />
                    <input type='hidden' name='in-positive-rules' id='in-positive-rules' value='' />
                    <input type='hidden' name='in-negative-rules' id='in-negative-rules' value='' />

                    <input type='hidden' name='postive-rules-order' id='postive-rules-order' value='' />
                    <input type='hidden' name='negative-rules-order' id='negative-rules-order' value='' />

                    <div id='edit-level' class='level-holder-wrap'>
                        <div class='sidebar-name no-movecursor'>
                            <h3>Title</h3>
                        </div>
                        <div class='level-holder'>
                            <div class='level-details'>
                                <label for='level_title'><?php _e('Level title', 'membership'); ?></label><?php //echo $this->_tips->add_tip( __('This is the title used throughout the system to identify this level.','membership') );     ?><br/>
                                <input class='wide' type='text' name='level_title' id='level_title' value='<?php echo esc_attr($level->level_title); ?>' />
                                <br/><br/>
                                <label for='level_shortcode'><?php _e('Level shortcode', 'membership'); ?></label>
                                <?php
                                _e('Save your level to create the shortcode', 'membership');
                                ?>
                            </div>



                            <ul class='leveltabs'>
                                <li class='positivetab <?php echo $positivetab; ?>'><div class='downarrow'></div><a href='#positive'><div><?php _e('Positive Rules', 'membership'); ?></div></a></li>
                                <li class='negativetab <?php echo $negativetab; ?>'><div class='downarrow'></div><a href='#negative'><div><?php _e('Negative Rules', 'membership'); ?></div></a></li>
                                <li class='advancedtab <?php echo $advancedtab; ?>'><div class='downarrow'></div><a href='#advanced'><div><?php _e('Advanced (both)', 'membership'); ?></div></a></li>
                            </ul>

                            <div class='advancedtabwarning <?php echo $advancedcontent; ?>'>
                                <?php _e('<strong>Warning:</strong> using both positive and negative rules on the same level can cause conflicts and unpredictable behaviour.', 'membership'); ?>
                            </div>

                            <div class='positivecontent <?php echo $positivecontent; ?>'>
                                <h3 class='positive positivetitle <?php echo $advancedcontent; ?>'><?php _e('Positive rules', 'membership'); ?></h3>
                                <p class='description'><?php _e('These are the areas / elements that a member of this level can access.', 'membership'); ?></p>

                                <div id='positive-rules' class='level-droppable-rules levels-sortable'>
                                    <?php _e('Drop here', 'membership'); ?>
                                </div>

                                <div id='positive-rules-holder'>

                                    <?php
                                    $sections['modules'] = array("title" => __('Modules', 'cp'));

                                    foreach ($sections as $key => $section) {
                                        if (isset($M_SectionRules[$key])) {
                                            foreach ($M_SectionRules[$key] as $mrule => $mclass) {
                                                $rule = new $mclass();
                                                if (!array_key_exists($mrule, $rule)) {
                                                    $rule->admin_main(false);
                                                } else {
                                                    $rule->admin_main(true);
                                                }
                                            }
                                        }
                                    }
                                    ?>

                                </div>
                            </div>

                            <div class='negativecontent <?php echo $negativecontent; ?>'>
                                <h3 class='negative negativetitle <?php echo $advancedcontent; ?>'><?php _e('Negative rules', 'membership'); ?></h3>
                                <p class='description'><?php _e('These are the areas / elements that a member of this level doesn\'t have access to.', 'membership'); ?></p>

                                <div id='negative-rules' class='level-droppable-rules levels-sortable'>
                                    <?php _e('Drop here', 'membership'); ?>
                                </div>

                                <div id='negative-rules-holder'>



                                </div>
                            </div>


                            <div class='advancedcontent <?php echo $advancedcontent; ?>'>

                            </div>


                        </div>
                    </div>
                </form>
            </div>


        </div>--> <!-- level-liquid-left -->

        <div class='level-liquid-right'>
            <div class="level-holder-wrap">
                <?php
                $sections['modules'] = array("title" => __('Modules', 'cp'));

                foreach ($sections as $key => $section) {
                    ?>

                    <div class="sidebar-name no-movecursor">
                        <h3><?php echo $section['title']; ?></h3>
                    </div>

                    <div class="section-holder" id="sidebar-<?php echo $key; ?>" style="min-height: 98px;">
                        <ul class='levels level-levels-draggable'>
                            <?php
                            if (isset($M_SectionRules[$key])) {
                                foreach ($M_SectionRules[$key] as $mrule => $mclass) {
                                    $rule = new $mclass();
                                    if (!array_key_exists($mrule, $rule)) {
                                        $rule->admin_sidebar(false);
                                    } else {
                                        $rule->admin_sidebar(true);
                                    }
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <?php
                }
                ?>
            </div> <!-- level-holder-wrap -->

        </div> <!-- level-liquid-left -->

    </form>

</div> <!-- wrap -->