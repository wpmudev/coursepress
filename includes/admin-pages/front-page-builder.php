<div class="wrap nosubsub coursepress_page_course_details">
    <h2><?php _e('Front Page Builder', 'cp'); ?></h2>

    <div class="wrap mp-wrap nocoursesub">

        <div class="mp-settings"><!--course-liquid-left-->
            <form action="http://localhost/wpmu/wp-admin/admin.php?page=course_details&amp;tab=units&amp;course_id=1441&amp;action=add_new_unit&amp;ms=uu#unit-page-1" name="unit-add" id="unit-add" class="unit-add" method="post">

                <input type="hidden" id="_wpnonce" name="_wpnonce" value="335fb12d48"><input type="hidden" name="_wp_http_referer" value="/wpmu/wp-admin/admin.php?page=course_details&amp;tab=units&amp;course_id=1441&amp;unit_id=1442&amp;action=edit">            <input type="hidden" name="unit_state" id="unit_state" value="unpublished">

                <input type="hidden" name="course_id" value="1441">
                <input type="hidden" name="unit_id" value="1442">
                <input type="hidden" name="action" value="update_unit">



                <div class="section elements-section">
                    <input type="hidden" name="beingdragged" id="beingdragged" value="">
                    <div id="course">


                        <div id="edit-sub" class="course-holder-wrap elements-wrap">

                            <div class="course-holder">

                                <div id="unit-pages" >
                                    <div class="page-builder-title"><span><?php _e('Front Page Builder', 'cp')?></span></div>

                                    <div id="unit-page-1" aria-labelledby="ui-id-1" class="ui-tabs-panel ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="true" aria-hidden="false">
                                        <div class="course-details elements-holder">
                                            <div class="unit_page_title">





                                                <div class="description">Click to add elements to the Front page</div>
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

        <div class="level-liquid-right" style="display:none;">
            <div class="level-holder-wrap">

                <div class="sidebar-name no-movecursor">
                    <h3>Input Elements</h3>
                </div>

                <div class="section-holder" id="sidebar-input" style="min-height: 98px;">
                    <ul class="modules">
                        <li class="draggable-module ui-draggable" id="checkbox_input_module">
                            <div class="action action-draggable">
                                <div class="action-top closed">
                                    <a href="#available-actions" class="action-button hide-if-no-js"></a>
                                    Multiple Choice                    </div>
                                <div class="action-body closed">
                                    <p>
                                        Multiple choice question where multiple options can be selected                            </p>

                                </div>
                            </div>
                        </li>
                        <div class="draggable-module-holder-checkbox_input_module module-holder-title" style="display:none;">

                            <h3 class="module-title sidebar-name">
                                <span class="h3-label">
                                    <span class="h3-label-left">Untitled</span>
                                    <span class="h3-label-right">Multiple Choice</span>
                                    <span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
                                </span>
                            </h3>

                            <div class="module-content">
                                <!--<input type="hidden" name="checkbox_input_module_checked_index[]" class='checked_index' value="0" />-->

                                <input type="hidden" name="checkbox_input_module_module_order[]" class="module_order" value="1">
                                <input type="hidden" name="module_type[]" value="checkbox_input_module">
                                <input type="hidden" name="checkbox_input_module_id[]" value="">

                                <label class="bold-label">Element Title            <div class="module_time_estimation">Time Estimation (mins) <input type="text" name="checkbox_input_module_time_estimation[]" value="1:00"></div>
                                </label>
                                <input type="text" class="element_title" name="checkbox_input_module_title[]" value="">

                                <div class="group-check">
                                    <label class="show_title_on_front">Show Title                        <input type="checkbox" name="checkbox_input_module_show_title_on_front[]" value="yes" checked="">
                                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">×</div>
                                            <div class="tooltip-content">
                                                The title is used to identify this element – useful for assessment. If checked, the title is displayed as a heading for this element for the student as well.                            </div>
                                        </div>
                                    </label>

                                    <label class="mandatory_answer">Mandatory Answer                        <input type="checkbox" name="checkbox_input_module_mandatory_answer[]" value="yes" checked="">
                                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">×</div>
                                            <div class="tooltip-content">
                                                Student will need to provide a response on this question in order to continue the unit.                            </div>
                                        </div>
                                    </label>

                                    <label class="mandatory_answer">Assessable                        <input type="checkbox" name="checkbox_input_module_gradable_answer[]" value="yes" checked="">
                                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">×</div>
                                            <div class="tooltip-content">
                                                If checked, this question will be graded. If not checked, the response can still be viewed within the Assessment section but listed as Non-assessable.                            </div>
                                        </div>
                                    </label>
                                </div>

                                <label class="bold-label">Question</label>

                                <div class="editor_in_place">

                                    <div id="wp-6966-wrap" class="wp-core-ui wp-editor-wrap tmce-active"><div id="wp-6966-editor-tools" class="wp-editor-tools hide-if-no-js"><div id="wp-6966-media-buttons" class="wp-media-buttons"><a href="#" id="insert-media-button" class="button insert-media add_media" data-editor="6966" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a></div>
                                            <div class="wp-editor-tabs"></div>
                                        </div>
                                        <div id="wp-6966-editor-container" class="wp-editor-container"><div id="mce_93" class="mce-tinymce mce-container mce-panel" hidefocus="1" tabindex="-1" role="application" style="visibility: hidden; border-width: 1px;"><div id="mce_93-body" class="mce-container-body mce-stack-layout"><div id="mce_94" class="mce-toolbar-grp mce-container mce-panel mce-first mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group"><div id="mce_94-body" class="mce-container-body mce-stack-layout"><div id="mce_95" class="mce-container mce-toolbar mce-first mce-last mce-stack-layout-item" role="toolbar"><div id="mce_95-body" class="mce-container-body mce-flow-layout"><div id="mce_96" class="mce-container mce-first mce-last mce-flow-layout-item mce-btn-group" role="group"><div id="mce_96-body"><div id="mce_81" class="mce-widget mce-btn mce-first" tabindex="-1" aria-labelledby="mce_81" role="button" aria-label="Bold"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bold"></i></button></div><div id="mce_82" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_82" role="button" aria-label="Italic"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-italic"></i></button></div><div id="mce_83" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_83" role="button" aria-label="Underline"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-underline"></i></button></div><div id="mce_84" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_84" role="button" aria-label="Blockquote"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-blockquote"></i></button></div><div id="mce_85" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_85" role="button" aria-label="Strikethrough"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-strikethrough"></i></button></div><div id="mce_86" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_86" role="button" aria-label="Bullet list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bullist"></i></button></div><div id="mce_87" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_87" role="button" aria-label="Numbered list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-numlist"></i></button></div><div id="mce_88" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_88" role="button" aria-label="Align left"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignleft"></i></button></div><div id="mce_89" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_89" role="button" aria-label="Align center"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-aligncenter"></i></button></div><div id="mce_90" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_90" role="button" aria-label="Align right"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignright"></i></button></div><div id="mce_91" class="mce-widget mce-btn mce-disabled" tabindex="-1" aria-labelledby="mce_91" role="button" aria-label="Undo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-undo"></i></button></div><div id="mce_92" class="mce-widget mce-btn mce-last mce-disabled" tabindex="-1" aria-labelledby="mce_92" role="button" aria-label="Redo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-redo"></i></button></div></div></div></div></div></div></div><div id="mce_97" class="mce-edit-area mce-container mce-panel mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><iframe id="6966_ifr" src='javascript:""' frameborder="0" allowtransparency="true" title="Rich Text Area. Press ALT-F9 for menu. Press ALT-F10 for toolbar. Press ALT-0 for help" style="width: 100%; height: 100px; display: block;"></iframe></div><div id="mce_98" class="mce-statusbar mce-container mce-panel mce-last mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><div id="mce_98-body" class="mce-container-body mce-flow-layout"><div id="mce_99" class="mce-path mce-first mce-flow-layout-item"><div role="button" class="mce-path-item mce-last" data-index="0" tabindex="-1" id="mce_99-0" aria-level="0">p</div></div><div id="mce_100" class="mce-last mce-flow-layout-item mce-resizehandle"><i class="mce-ico mce-i-resize"></i></div></div></div></div></div><textarea class="wp-editor-area" rows="5" autocomplete="off" cols="40" name="checkbox_input_module_content[]" id="6966" style="display: none;" aria-hidden="true"></textarea></div>
                                    </div>

                                </div>

                                <div class="checkbox-editor">
                                    <table class="form-table">
                                        <tbody class="ci_items">
                                            <tr>

                                                <th width="90%">
                                        <div class="checkbox_answer_check">Answers</div>
                                        <div class="checkbox_answer"></div>
                                        </th>

                                        <th width="10%">
                                            <!--<a class="checkbox_new_link">Add New</a>-->
                                        </th>

                                        </tr>

                                        <tr>
                                            <td class="label" colspan="2">Set the correct answer</td>
                                        </tr>


                                        <tr>
                                            <td width="90%">
                                                <input class="checkbox_answer_check" type="checkbox" name="checkbox_input_module_checkbox_check[1][]" checked="">
                                                <input class="checkbox_answer" type="text" name="checkbox_input_module_checkbox_answers[1][]">
                                            </td>
                                            <td width="10%">&nbsp;</td>  
                                        </tr>

                                        <tr>
                                            <td width="90%">
                                                <input class="checkbox_answer_check" type="checkbox" name="checkbox_input_module_checkbox_check[1][]">
                                                <input class="checkbox_answer" type="text" name="checkbox_input_module_checkbox_answers[1][]">
                                            </td>
                                            <td width="10%">&nbsp;</td>  
                                        </tr>
                                        </tbody>
                                    </table>

                                    <a class="checkbox_new_link button-secondary">Add New</a>

                                </div>

                                <a class="remove_module_link" onclick="if (removeModule()) {

                                            jQuery(this).parent().parent().remove();
                                            update_sortable_module_indexes();
                                            /* jQuery(this).parent().parent().remove();*/

                                        }"><i class="fa fa-trash-o"></i> Remove</a>

                            </div>


                        </div>

                        <li class="draggable-module ui-draggable" id="file_input_module">
                            <div class="action action-draggable">
                                <div class="action-top closed">
                                    <a href="#available-actions" class="action-button hide-if-no-js"></a>
                                    File Upload                    </div>
                                <div class="action-body closed">
                                    <p>
                                        Add file upload blocks to the unit. Useful if students need to send you various files like essay, homework etc.                            </p>

                                </div>
                            </div>
                        </li>

                        <div class="draggable-module-holder-file_input_module module-holder-title" style="display:none;">

                            <h3 class="module-title sidebar-name">
                                <span class="h3-label">
                                    <span class="h3-label-left">Untitled</span>
                                    <span class="h3-label-right">File Upload</span>
                                    <span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
                                </span>
                            </h3>

                            <div class="module-content">
                                <input type="hidden" name="file_input_module_module_order[]" class="module_order" value="2">
                                <input type="hidden" name="module_type[]" value="file_input_module">
                                <input type="hidden" name="file_input_module_id[]" value="">

                                <label class="bold-label">Element Title            <div class="module_time_estimation">Time Estimation (mins) <input type="text" name="file_input_module_time_estimation[]" value="1:00"></div>
                                </label>
                                <input type="text" class="element_title" name="file_input_module_title[]" value="">

                                <div class="group-check">
                                    <label class="show_title_on_front">Show Title                        <input type="checkbox" name="file_input_module_show_title_on_front[]" value="yes" checked="">
                                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">×</div>
                                            <div class="tooltip-content">
                                                The title is used to identify this element – useful for assessment. If checked, the title is displayed as a heading for this element for the student as well.                            </div>
                                        </div>
                                    </label>

                                    <label class="mandatory_answer">Mandatory Answer                        <input type="checkbox" name="file_input_module_mandatory_answer[]" value="yes" checked="">
                                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">×</div>
                                            <div class="tooltip-content">
                                                Student will need to provide a response on this question in order to continue the unit.                            </div>
                                        </div>
                                    </label>

                                    <label class="mandatory_answer">Assessable                        <input type="checkbox" name="file_input_module_gradable_answer[]" value="yes" checked="">
                                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">×</div>
                                            <div class="tooltip-content">
                                                If checked, this question will be graded. If not checked, the response can still be viewed within the Assessment section but listed as Non-assessable.                            </div>
                                        </div>
                                    </label>
                                </div>

                                <label class="bold-label">Content</label>

                                <div class="editor_in_place">                    <div id="wp-5120-wrap" class="wp-core-ui wp-editor-wrap tmce-active"><div id="wp-5120-editor-tools" class="wp-editor-tools hide-if-no-js"><div id="wp-5120-media-buttons" class="wp-media-buttons"><a href="#" id="insert-media-button" class="button insert-media add_media" data-editor="5120" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a></div>
                                            <div class="wp-editor-tabs"></div>
                                        </div>
                                        <div id="wp-5120-editor-container" class="wp-editor-container"><div id="mce_53" class="mce-tinymce mce-container mce-panel" hidefocus="1" tabindex="-1" role="application" style="visibility: hidden; border-width: 1px;"><div id="mce_53-body" class="mce-container-body mce-stack-layout"><div id="mce_54" class="mce-toolbar-grp mce-container mce-panel mce-first mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group"><div id="mce_54-body" class="mce-container-body mce-stack-layout"><div id="mce_55" class="mce-container mce-toolbar mce-first mce-last mce-stack-layout-item" role="toolbar"><div id="mce_55-body" class="mce-container-body mce-flow-layout"><div id="mce_56" class="mce-container mce-first mce-last mce-flow-layout-item mce-btn-group" role="group"><div id="mce_56-body"><div id="mce_41" class="mce-widget mce-btn mce-first" tabindex="-1" aria-labelledby="mce_41" role="button" aria-label="Bold"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bold"></i></button></div><div id="mce_42" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_42" role="button" aria-label="Italic"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-italic"></i></button></div><div id="mce_43" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_43" role="button" aria-label="Underline"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-underline"></i></button></div><div id="mce_44" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_44" role="button" aria-label="Blockquote"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-blockquote"></i></button></div><div id="mce_45" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_45" role="button" aria-label="Strikethrough"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-strikethrough"></i></button></div><div id="mce_46" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_46" role="button" aria-label="Bullet list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bullist"></i></button></div><div id="mce_47" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_47" role="button" aria-label="Numbered list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-numlist"></i></button></div><div id="mce_48" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_48" role="button" aria-label="Align left"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignleft"></i></button></div><div id="mce_49" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_49" role="button" aria-label="Align center"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-aligncenter"></i></button></div><div id="mce_50" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_50" role="button" aria-label="Align right"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignright"></i></button></div><div id="mce_51" class="mce-widget mce-btn mce-disabled" tabindex="-1" aria-labelledby="mce_51" role="button" aria-label="Undo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-undo"></i></button></div><div id="mce_52" class="mce-widget mce-btn mce-last mce-disabled" tabindex="-1" aria-labelledby="mce_52" role="button" aria-label="Redo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-redo"></i></button></div></div></div></div></div></div></div><div id="mce_57" class="mce-edit-area mce-container mce-panel mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><iframe id="5120_ifr" src='javascript:""' frameborder="0" allowtransparency="true" title="Rich Text Area. Press ALT-F9 for menu. Press ALT-F10 for toolbar. Press ALT-0 for help" style="width: 100%; height: 100px; display: block;"></iframe></div><div id="mce_58" class="mce-statusbar mce-container mce-panel mce-last mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><div id="mce_58-body" class="mce-container-body mce-flow-layout"><div id="mce_59" class="mce-path mce-first mce-flow-layout-item"><div role="button" class="mce-path-item mce-last" data-index="0" tabindex="-1" id="mce_59-0" aria-level="0">p</div></div><div id="mce_60" class="mce-last mce-flow-layout-item mce-resizehandle"><i class="mce-ico mce-i-resize"></i></div></div></div></div></div><textarea class="wp-editor-area" rows="5" autocomplete="off" cols="40" name="file_input_module_content[]" id="5120" style="display: none;" aria-hidden="true"></textarea></div>
                                    </div>

                                </div>
                                <a class="remove_module_link" onclick="if (removeModule()) {

                                            jQuery(this).parent().parent().remove();
                                            update_sortable_module_indexes();
                                            /* jQuery(this).parent().parent().remove();*/

                                        }"><i class="fa fa-trash-o"></i> Remove</a>
                            </div>

                        </div>

                        <li class="draggable-module ui-draggable" id="radio_input_module">
                            <div class="action action-draggable">
                                <div class="action-top closed">
                                    <a href="#available-actions" class="action-button hide-if-no-js"></a>
                                    Single Choice                    </div>
                                <div class="action-body closed">
                                    <p>
                                        Multiple choice question where only one option can be selected                            </p>

                                </div>
                            </div>
                        </li>

                        <div class="draggable-module-holder-radio_input_module module-holder-title" style="display:none;">

                            <h3 class="module-title sidebar-name">
                                <span class="h3-label">
                                    <span class="h3-label-left">Untitled</span>
                                    <span class="h3-label-right">Single Choice</span>
                                    <span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
                                </span>
                            </h3>

                            <div class="module-content">
                                <input type="hidden" name="radio_input_module_checked_index[]" class="checked_index" value="0">

                                <input type="hidden" name="radio_input_module_module_order[]" class="module_order" value="3">
                                <input type="hidden" name="module_type[]" value="radio_input_module">
                                <input type="hidden" name="radio_input_module_id[]" value="">

                                <label class="bold-label">Element Title            <div class="module_time_estimation">Time Estimation (mins) <input type="text" name="radio_input_module_time_estimation[]" value="1:00"></div>
                                </label>
                                <input type="text" class="element_title" name="radio_input_module_title[]" value="">

                                <div class="group-check">
                                    <label class="show_title_on_front">Show Title                        <input type="checkbox" name="radio_input_module_show_title_on_front[]" value="yes" checked="">
                                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">×</div>
                                            <div class="tooltip-content">
                                                The title is used to identify this element – useful for assessment. If checked, the title is displayed as a heading for this element for the student as well.                            </div>
                                        </div>
                                    </label>

                                    <label class="mandatory_answer">Mandatory Answer                        <input type="checkbox" name="radio_input_module_mandatory_answer[]" value="yes" checked="">
                                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">×</div>
                                            <div class="tooltip-content">
                                                Student will need to provide a response on this question in order to continue the unit.                            </div>
                                        </div>
                                    </label>

                                    <label class="mandatory_answer">Assessable                        <input type="checkbox" name="radio_input_module_gradable_answer[]" value="yes" checked="">
                                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">×</div>
                                            <div class="tooltip-content">
                                                If checked, this question will be graded. If not checked, the response can still be viewed within the Assessment section but listed as Non-assessable.                            </div>
                                        </div>
                                    </label>
                                </div>

                                <label class="bold-label">Question</label>

                                <div class="editor_in_place">
                                    <div id="wp-557-wrap" class="wp-core-ui wp-editor-wrap tmce-active"><div id="wp-557-editor-tools" class="wp-editor-tools hide-if-no-js"><div id="wp-557-media-buttons" class="wp-media-buttons"><a href="#" id="insert-media-button" class="button insert-media add_media" data-editor="557" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a></div>
                                            <div class="wp-editor-tabs"></div>
                                        </div>
                                        <div id="wp-557-editor-container" class="wp-editor-container"><div id="mce_13" class="mce-tinymce mce-container mce-panel" hidefocus="1" tabindex="-1" role="application" style="visibility: hidden; border-width: 1px;"><div id="mce_13-body" class="mce-container-body mce-stack-layout"><div id="mce_14" class="mce-toolbar-grp mce-container mce-panel mce-first mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group"><div id="mce_14-body" class="mce-container-body mce-stack-layout"><div id="mce_15" class="mce-container mce-toolbar mce-first mce-last mce-stack-layout-item" role="toolbar"><div id="mce_15-body" class="mce-container-body mce-flow-layout"><div id="mce_16" class="mce-container mce-first mce-last mce-flow-layout-item mce-btn-group" role="group"><div id="mce_16-body"><div id="mce_1" class="mce-widget mce-btn mce-first" tabindex="-1" aria-labelledby="mce_1" role="button" aria-label="Bold"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bold"></i></button></div><div id="mce_2" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_2" role="button" aria-label="Italic"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-italic"></i></button></div><div id="mce_3" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_3" role="button" aria-label="Underline"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-underline"></i></button></div><div id="mce_4" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_4" role="button" aria-label="Blockquote"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-blockquote"></i></button></div><div id="mce_5" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_5" role="button" aria-label="Strikethrough"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-strikethrough"></i></button></div><div id="mce_6" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_6" role="button" aria-label="Bullet list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bullist"></i></button></div><div id="mce_7" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_7" role="button" aria-label="Numbered list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-numlist"></i></button></div><div id="mce_8" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_8" role="button" aria-label="Align left"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignleft"></i></button></div><div id="mce_9" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_9" role="button" aria-label="Align center"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-aligncenter"></i></button></div><div id="mce_10" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_10" role="button" aria-label="Align right"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignright"></i></button></div><div id="mce_11" class="mce-widget mce-btn mce-disabled" tabindex="-1" aria-labelledby="mce_11" role="button" aria-label="Undo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-undo"></i></button></div><div id="mce_12" class="mce-widget mce-btn mce-last mce-disabled" tabindex="-1" aria-labelledby="mce_12" role="button" aria-label="Redo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-redo"></i></button></div></div></div></div></div></div></div><div id="mce_17" class="mce-edit-area mce-container mce-panel mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><iframe id="557_ifr" src='javascript:""' frameborder="0" allowtransparency="true" title="Rich Text Area. Press ALT-F9 for menu. Press ALT-F10 for toolbar. Press ALT-0 for help" style="width: 100%; height: 100px; display: block;"></iframe></div><div id="mce_18" class="mce-statusbar mce-container mce-panel mce-last mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><div id="mce_18-body" class="mce-container-body mce-flow-layout"><div id="mce_19" class="mce-path mce-first mce-flow-layout-item"><div role="button" class="mce-path-item mce-last" data-index="0" tabindex="-1" id="mce_19-0" aria-level="0">p</div></div><div id="mce_20" class="mce-last mce-flow-layout-item mce-resizehandle"><i class="mce-ico mce-i-resize"></i></div></div></div></div></div><textarea class="wp-editor-area" rows="5" autocomplete="off" cols="40" name="radio_input_module_content[]" id="557" style="display: none;" aria-hidden="true"></textarea></div>
                                    </div>

                                </div>

                                <div class="radio-editor">
                                    <table class="form-table">
                                        <tbody class="ri_items">
                                            <tr>
                                                <th width="90%">
                                        <div class="radio_answer_check">Answer</div>
                                        <div class="radio_answer"></div>
                                        </th>
                                        <th width="10%">
                                            <!--<a class="radio_new_link">Add New</a>-->
                                        </th>
                                        </tr>

                                        <tr>
                                            <td class="label" colspan="2">Set the correct answer</td>
                                        </tr>


                                        <tr>
                                            <td width="90%">
                                                <input class="radio_answer_check" type="radio" name="radio_input_module_radio_check[3][]" checked="">
                                                <input class="radio_answer" type="text" name="radio_input_module_radio_answers[3][]">
                                            </td>
                                            <td width="10%">&nbsp;</td>  
                                        </tr>

                                        <tr>
                                            <td width="90%">
                                                <input class="radio_answer_check" type="radio" name="radio_input_module_radio_check[3][]">
                                                <input class="radio_answer" type="text" name="radio_input_module_radio_answers[3][]">
                                            </td>
                                            <td width="10%">&nbsp;</td>  
                                        </tr>

                                        </tbody>
                                    </table>

                                    <a class="radio_new_link button-secondary">Add New</a>

                                </div>
                                <a class="remove_module_link" onclick="if (removeModule()) {

                                            jQuery(this).parent().parent().remove();
                                            update_sortable_module_indexes();
                                            /* jQuery(this).parent().parent().remove();*/

                                        }"><i class="fa fa-trash-o"></i> Remove</a>
                            </div>

                        </div>



                        <li class="draggable-module ui-draggable" id="text_input_module">
                            <div class="action action-draggable">
                                <div class="action-top closed">
                                    <a href="#available-actions" class="action-button hide-if-no-js"></a>
                                    Answer Field                    </div>
                                <div class="action-body closed">
                                    <p>
                                        Allow students to enter a single line of text                            </p>

                                </div>
                            </div>
                        </li>

                        <div class="draggable-module-holder-text_input_module module-holder-title" style="display:none;">

                            <h3 class="module-title sidebar-name">
                                <span class="h3-label">
                                    <span class="h3-label-left">Untitled</span>
                                    <span class="h3-label-right">Answer Field</span>
                                    <span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
                                </span>
                            </h3>

                            <div class="module-content">
                                <input type="hidden" name="text_input_module_module_order[]" class="module_order" value="4">
                                <input type="hidden" name="module_type[]" value="text_input_module">
                                <input type="hidden" name="text_input_module_id[]" value="">

                                <label class="bold-label">Element Title            <div class="module_time_estimation">Time Estimation (mins) <input type="text" name="text_input_module_time_estimation[]" value="1:00"></div>
                                </label>
                                <input type="text" class="element_title" name="text_input_module_title[]" value="">

                                <div class="group-check">
                                    <label class="show_title_on_front">Show Title                        <input type="checkbox" name="text_input_module_show_title_on_front[]" value="yes" checked="">
                                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">×</div>
                                            <div class="tooltip-content">
                                                The title is used to identify this element – useful for assessment. If checked, the title is displayed as a heading for this element for the student as well.                            </div>
                                        </div>
                                    </label>

                                    <label class="mandatory_answer">Mandatory Answer                        <input type="checkbox" name="text_input_module_mandatory_answer[]" value="yes" checked="">
                                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">×</div>
                                            <div class="tooltip-content">
                                                Student will need to provide a response on this question in order to continue the unit.                            </div>
                                        </div>
                                    </label>

                                    <label class="mandatory_answer">Assessable                        <input type="checkbox" name="text_input_module_gradable_answer[]" value="yes" checked="">
                                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">×</div>
                                            <div class="tooltip-content">
                                                If checked, this question will be graded. If not checked, the response can still be viewed within the Assessment section but listed as Non-assessable.                            </div>
                                        </div>
                                    </label>
                                </div>

                                <label class="bold-label">Content</label>

                                <div class="editor_in_place">
                                    <div id="wp-5568-wrap" class="wp-core-ui wp-editor-wrap tmce-active"><div id="wp-5568-editor-tools" class="wp-editor-tools hide-if-no-js"><div id="wp-5568-media-buttons" class="wp-media-buttons"><a href="#" id="insert-media-button" class="button insert-media add_media" data-editor="5568" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a></div>
                                            <div class="wp-editor-tabs"></div>
                                        </div>
                                        <div id="wp-5568-editor-container" class="wp-editor-container"><div id="mce_73" class="mce-tinymce mce-container mce-panel" hidefocus="1" tabindex="-1" role="application" style="visibility: hidden; border-width: 1px;"><div id="mce_73-body" class="mce-container-body mce-stack-layout"><div id="mce_74" class="mce-toolbar-grp mce-container mce-panel mce-first mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group"><div id="mce_74-body" class="mce-container-body mce-stack-layout"><div id="mce_75" class="mce-container mce-toolbar mce-first mce-last mce-stack-layout-item" role="toolbar"><div id="mce_75-body" class="mce-container-body mce-flow-layout"><div id="mce_76" class="mce-container mce-first mce-last mce-flow-layout-item mce-btn-group" role="group"><div id="mce_76-body"><div id="mce_61" class="mce-widget mce-btn mce-first" tabindex="-1" aria-labelledby="mce_61" role="button" aria-label="Bold"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bold"></i></button></div><div id="mce_62" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_62" role="button" aria-label="Italic"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-italic"></i></button></div><div id="mce_63" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_63" role="button" aria-label="Underline"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-underline"></i></button></div><div id="mce_64" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_64" role="button" aria-label="Blockquote"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-blockquote"></i></button></div><div id="mce_65" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_65" role="button" aria-label="Strikethrough"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-strikethrough"></i></button></div><div id="mce_66" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_66" role="button" aria-label="Bullet list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bullist"></i></button></div><div id="mce_67" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_67" role="button" aria-label="Numbered list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-numlist"></i></button></div><div id="mce_68" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_68" role="button" aria-label="Align left"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignleft"></i></button></div><div id="mce_69" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_69" role="button" aria-label="Align center"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-aligncenter"></i></button></div><div id="mce_70" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_70" role="button" aria-label="Align right"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignright"></i></button></div><div id="mce_71" class="mce-widget mce-btn mce-disabled" tabindex="-1" aria-labelledby="mce_71" role="button" aria-label="Undo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-undo"></i></button></div><div id="mce_72" class="mce-widget mce-btn mce-last mce-disabled" tabindex="-1" aria-labelledby="mce_72" role="button" aria-label="Redo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-redo"></i></button></div></div></div></div></div></div></div><div id="mce_77" class="mce-edit-area mce-container mce-panel mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><iframe id="5568_ifr" src='javascript:""' frameborder="0" allowtransparency="true" title="Rich Text Area. Press ALT-F9 for menu. Press ALT-F10 for toolbar. Press ALT-0 for help" style="width: 100%; height: 100px; display: block;"></iframe></div><div id="mce_78" class="mce-statusbar mce-container mce-panel mce-last mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><div id="mce_78-body" class="mce-container-body mce-flow-layout"><div id="mce_79" class="mce-path mce-first mce-flow-layout-item"><div role="button" class="mce-path-item mce-last" data-index="0" tabindex="-1" id="mce_79-0" aria-level="0">p</div></div><div id="mce_80" class="mce-last mce-flow-layout-item mce-resizehandle"><i class="mce-ico mce-i-resize"></i></div></div></div></div></div><textarea class="wp-editor-area" rows="5" autocomplete="off" cols="40" name="text_input_module_content[]" id="5568" style="display: none;" aria-hidden="true"></textarea></div>
                                    </div>

                                </div>

                                <div class="answer_length">  
                                    <label class="bold-label">Answer Length</label>
                                    <input type="radio" name="text_input_module_answer_length[]" value="single" checked=""> Single Line<br><br>
                                    <input type="radio" name="text_input_module_answer_length[]" value="multi"> Multiple Lines                </div>

                                <div class="placeholder_holder">
                                    <label>Placeholder Text                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">×</div>
                                            <div class="tooltip-content">
                                                Additional instructions visible in the input field as a placeholder                            </div>
                                        </div>
                                    </label>
                                    <input type="text" class="placeholder_text" name="text_input_module_placeholder_text[]" value="">
                                </div>
                                <a class="remove_module_link" onclick="if (removeModule()) {

                                            jQuery(this).parent().parent().remove();
                                            update_sortable_module_indexes();
                                            /* jQuery(this).parent().parent().remove();*/

                                        }"><i class="fa fa-trash-o"></i> Remove</a>
                            </div>

                        </div>

                    </ul>
                </div>

                <div class="sidebar-name no-movecursor">
                    <h3>Output Elements</h3>
                </div>

                <div class="section-holder" id="sidebar-output" style="min-height: 98px;">
                    <ul class="modules">
                        <li class="draggable-module ui-draggable" id="audio_module">
                            <div class="action action-draggable">
                                <div class="action-top closed">
                                    <a href="#available-actions" class="action-button hide-if-no-js"></a>
                                    Audio                    </div>
                                <div class="action-body closed">
                                    <p>
                                        Add audio files with player to the unit                            </p>

                                </div>
                            </div>
                        </li>

                        <div class="draggable-module-holder-audio_module module-holder-title" style="display:none;">

                            <h3 class="module-title sidebar-name">
                                <span class="h3-label">
                                    <span class="h3-label-left">Untitled</span>
                                    <span class="h3-label-right">Audio</span>
                                    <span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
                                </span>
                            </h3>

                            <div class="module-content">

                                <input type="hidden" name="audio_module_module_order[]" class="module_order" value="5">
                                <input type="hidden" name="module_type[]" value="audio_module">
                                <input type="hidden" name="audio_module_id[]" value="">

                                <label class="bold-label">Element Title            <div class="module_time_estimation">Time Estimation (mins) <input type="text" name="audio_module_time_estimation[]" value="1:00"></div>
                                </label>
                                <input type="text" class="element_title" name="audio_module_title[]" value="">


                                <label class="show_title_on_front">Show Title                    <input type="checkbox" name="audio_module_show_title_on_front[]" value="yes" checked="">
                                    <a class="help-icon" href="javascript:;"></a>
                                    <div class="tooltip">
                                        <div class="tooltip-before"></div>
                                        <div class="tooltip-button">×</div>
                                        <div class="tooltip-content">
                                            The title is used to identify this element – useful for assessment. If checked, the title is displayed as a heading for this element for the student as well.                        </div>
                                    </div>
                                </label>

                                <label class="bold-label">Content</label>

                                <div class="editor_in_place">

                                    <div id="wp-7268-wrap" class="wp-core-ui wp-editor-wrap tmce-active"><div id="wp-7268-editor-tools" class="wp-editor-tools hide-if-no-js"><div id="wp-7268-media-buttons" class="wp-media-buttons"><a href="#" id="insert-media-button" class="button insert-media add_media" data-editor="7268" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a></div>
                                            <div class="wp-editor-tabs"></div>
                                        </div>
                                        <div id="wp-7268-editor-container" class="wp-editor-container"><div id="mce_113" class="mce-tinymce mce-container mce-panel" hidefocus="1" tabindex="-1" role="application" style="visibility: hidden; border-width: 1px;"><div id="mce_113-body" class="mce-container-body mce-stack-layout"><div id="mce_114" class="mce-toolbar-grp mce-container mce-panel mce-first mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group"><div id="mce_114-body" class="mce-container-body mce-stack-layout"><div id="mce_115" class="mce-container mce-toolbar mce-first mce-last mce-stack-layout-item" role="toolbar"><div id="mce_115-body" class="mce-container-body mce-flow-layout"><div id="mce_116" class="mce-container mce-first mce-last mce-flow-layout-item mce-btn-group" role="group"><div id="mce_116-body"><div id="mce_101" class="mce-widget mce-btn mce-first" tabindex="-1" aria-labelledby="mce_101" role="button" aria-label="Bold"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bold"></i></button></div><div id="mce_102" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_102" role="button" aria-label="Italic"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-italic"></i></button></div><div id="mce_103" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_103" role="button" aria-label="Underline"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-underline"></i></button></div><div id="mce_104" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_104" role="button" aria-label="Blockquote"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-blockquote"></i></button></div><div id="mce_105" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_105" role="button" aria-label="Strikethrough"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-strikethrough"></i></button></div><div id="mce_106" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_106" role="button" aria-label="Bullet list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bullist"></i></button></div><div id="mce_107" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_107" role="button" aria-label="Numbered list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-numlist"></i></button></div><div id="mce_108" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_108" role="button" aria-label="Align left"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignleft"></i></button></div><div id="mce_109" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_109" role="button" aria-label="Align center"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-aligncenter"></i></button></div><div id="mce_110" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_110" role="button" aria-label="Align right"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignright"></i></button></div><div id="mce_111" class="mce-widget mce-btn mce-disabled" tabindex="-1" aria-labelledby="mce_111" role="button" aria-label="Undo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-undo"></i></button></div><div id="mce_112" class="mce-widget mce-btn mce-last mce-disabled" tabindex="-1" aria-labelledby="mce_112" role="button" aria-label="Redo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-redo"></i></button></div></div></div></div></div></div></div><div id="mce_117" class="mce-edit-area mce-container mce-panel mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><iframe id="7268_ifr" src='javascript:""' frameborder="0" allowtransparency="true" title="Rich Text Area. Press ALT-F9 for menu. Press ALT-F10 for toolbar. Press ALT-0 for help" style="width: 100%; height: 100px; display: block;"></iframe></div><div id="mce_118" class="mce-statusbar mce-container mce-panel mce-last mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><div id="mce_118-body" class="mce-container-body mce-flow-layout"><div id="mce_119" class="mce-path mce-first mce-flow-layout-item"><div role="button" class="mce-path-item mce-last" data-index="0" tabindex="-1" id="mce_119-0" aria-level="0">p</div></div><div id="mce_120" class="mce-last mce-flow-layout-item mce-resizehandle"><i class="mce-ico mce-i-resize"></i></div></div></div></div></div><textarea class="wp-editor-area" rows="5" autocomplete="off" cols="40" name="audio_module_content[]" id="7268" style="display: none;" aria-hidden="true"></textarea></div>
                                    </div>

                                </div>

                                <div class="audio_url_holder">
                                    <label>Put a URL or Browse for an audio file. Supported audio extensions ( mp3,ogg,wma,m4a,wav )                        <input class="audio_url" type="text" size="36" name="audio_module_audio_url[]" value="">
                                        <input class="audio_url_button" type="button" value="Browse">
                                    </label>
                                </div>

                                <div class="audio_additional_controls">
                                    <label>Play in a loop</label>
                                    <input type="radio" name="audio_module_loop[]" value="Yes"> Yes<br><br>
                                    <input type="radio" name="audio_module_loop[]" value="No" checked="checked"> No<br><br>

                                    <label>Autoplay</label>
                                    <input type="radio" name="audio_module_autoplay[]" value="Yes"> Yes<br><br>
                                    <input type="radio" name="audio_module_autoplay[]" value="No" checked="checked"> No<br><br>
                                </div>

                                <a class="remove_module_link" onclick="if (removeModule()) {

                                            jQuery(this).parent().parent().remove();
                                            update_sortable_module_indexes();
                                            /* jQuery(this).parent().parent().remove();*/

                                        }"><i class="fa fa-trash-o"></i> Remove</a>

                            </div>

                        </div>

                        <li class="draggable-module ui-draggable" id="chat_module">
                            <div class="action action-draggable">
                                <div class="action-top closed">
                                    <a href="#available-actions" class="action-button hide-if-no-js"></a>
                                    Live Chat                    </div>
                                <div class="action-body closed">
                                    <p>
                                        Add a chat box from the Wordpress Chat plugin                            </p>

                                </div>
                            </div>
                        </li>

                        <div class="draggable-module-holder-chat_module module-holder-title" style="display:none;">

                            <h3 class="module-title sidebar-name">
                                <span class="h3-label">
                                    <span class="h3-label-left">Untitled</span>
                                    <span class="h3-label-right">Live Chat</span>
                                    <span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
                                </span>
                            </h3>

                            <div class="module-content">
                                <input type="hidden" name="chat_module_module_order[]" class="module_order" value="6">
                                <input type="hidden" name="module_type[]" value="chat_module">
                                <input type="hidden" name="chat_module_id[]" value="">

                                <label class="bold-label">Element Title</label>
                                <input type="text" class="element_title" name="chat_module_title[]" value="">


                                <label class="show_title_on_front">Show Title                        <input type="checkbox" name="chat_module_show_title_on_front[]" value="yes" checked="">
                                    <a class="help-icon" href="javascript:;"></a>
                                    <div class="tooltip">
                                        <div class="tooltip-before"></div>
                                        <div class="tooltip-button">×</div>
                                        <div class="tooltip-content">
                                            The title is used to identify this element – useful for assessment. If checked, the title is displayed as a heading for this element for the student as well.                            </div>
                                    </div>

                                </label>

                                <label class="bold-label">Content</label>

                                <div class="editor_in_place">

                                    <div id="wp-8609-wrap" class="wp-core-ui wp-editor-wrap tmce-active"><div id="wp-8609-editor-tools" class="wp-editor-tools hide-if-no-js"><div id="wp-8609-media-buttons" class="wp-media-buttons"><a href="#" id="insert-media-button" class="button insert-media add_media" data-editor="8609" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a></div>
                                            <div class="wp-editor-tabs"></div>
                                        </div>
                                        <div id="wp-8609-editor-container" class="wp-editor-container"><div id="mce_133" class="mce-tinymce mce-container mce-panel" hidefocus="1" tabindex="-1" role="application" style="visibility: hidden; border-width: 1px;"><div id="mce_133-body" class="mce-container-body mce-stack-layout"><div id="mce_134" class="mce-toolbar-grp mce-container mce-panel mce-first mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group"><div id="mce_134-body" class="mce-container-body mce-stack-layout"><div id="mce_135" class="mce-container mce-toolbar mce-first mce-last mce-stack-layout-item" role="toolbar"><div id="mce_135-body" class="mce-container-body mce-flow-layout"><div id="mce_136" class="mce-container mce-first mce-last mce-flow-layout-item mce-btn-group" role="group"><div id="mce_136-body"><div id="mce_121" class="mce-widget mce-btn mce-first" tabindex="-1" aria-labelledby="mce_121" role="button" aria-label="Bold"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bold"></i></button></div><div id="mce_122" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_122" role="button" aria-label="Italic"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-italic"></i></button></div><div id="mce_123" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_123" role="button" aria-label="Underline"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-underline"></i></button></div><div id="mce_124" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_124" role="button" aria-label="Blockquote"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-blockquote"></i></button></div><div id="mce_125" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_125" role="button" aria-label="Strikethrough"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-strikethrough"></i></button></div><div id="mce_126" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_126" role="button" aria-label="Bullet list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bullist"></i></button></div><div id="mce_127" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_127" role="button" aria-label="Numbered list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-numlist"></i></button></div><div id="mce_128" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_128" role="button" aria-label="Align left"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignleft"></i></button></div><div id="mce_129" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_129" role="button" aria-label="Align center"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-aligncenter"></i></button></div><div id="mce_130" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_130" role="button" aria-label="Align right"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignright"></i></button></div><div id="mce_131" class="mce-widget mce-btn mce-disabled" tabindex="-1" aria-labelledby="mce_131" role="button" aria-label="Undo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-undo"></i></button></div><div id="mce_132" class="mce-widget mce-btn mce-last mce-disabled" tabindex="-1" aria-labelledby="mce_132" role="button" aria-label="Redo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-redo"></i></button></div></div></div></div></div></div></div><div id="mce_137" class="mce-edit-area mce-container mce-panel mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><iframe id="8609_ifr" src='javascript:""' frameborder="0" allowtransparency="true" title="Rich Text Area. Press ALT-F9 for menu. Press ALT-F10 for toolbar. Press ALT-0 for help" style="width: 100%; height: 100px; display: block;"></iframe></div><div id="mce_138" class="mce-statusbar mce-container mce-panel mce-last mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><div id="mce_138-body" class="mce-container-body mce-flow-layout"><div id="mce_139" class="mce-path mce-first mce-flow-layout-item"><div role="button" class="mce-path-item mce-last" data-index="0" tabindex="-1" id="mce_139-0" aria-level="0">p</div></div><div id="mce_140" class="mce-last mce-flow-layout-item mce-resizehandle"><i class="mce-ico mce-i-resize"></i></div></div></div></div></div><textarea class="wp-editor-area" rows="5" autocomplete="off" cols="40" name="chat_module_content[]" id="8609" style="display: none;" aria-hidden="true"></textarea></div>
                                    </div>

                                </div>

                                <a class="remove_module_link" onclick="if (removeModule()) {

                                            jQuery(this).parent().parent().remove();
                                            update_sortable_module_indexes();
                                            /* jQuery(this).parent().parent().remove();*/

                                        }"><i class="fa fa-trash-o"></i> Remove</a>

                            </div>

                        </div>

                        <li class="draggable-module ui-draggable" id="file_module">
                            <div class="action action-draggable">
                                <div class="action-top closed">
                                    <a href="#available-actions" class="action-button hide-if-no-js"></a>
                                    File Download                    </div>
                                <div class="action-body closed">
                                    <p>
                                        Ask students to upload a file. Useful if students need to send you various files like essays, homework etc.                            </p>

                                </div>
                            </div>
                        </li>

                        <div class="draggable-module-holder-file_module module-holder-title" style="display:none;">

                            <h3 class="module-title sidebar-name">
                                <span class="h3-label">
                                    <span class="h3-label-left">Untitled</span>
                                    <span class="h3-label-right">File Download</span>
                                    <span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
                                </span>
                            </h3>

                            <div class="module-content">
                                <input type="hidden" name="file_module_module_order[]" class="module_order" value="7">
                                <input type="hidden" name="module_type[]" value="file_module">
                                <input type="hidden" name="file_module_id[]" value="">

                                <label class="bold-label">Element Title</label>
                                <input type="text" class="element_title" name="file_module_title[]" value="">


                                <label class="show_title_on_front">Show Title                    <input type="checkbox" name="file_module_show_title_on_front[]" value="yes" checked="">
                                    <a class="help-icon" href="javascript:;"></a>
                                    <div class="tooltip">
                                        <div class="tooltip-before"></div>
                                        <div class="tooltip-button">×</div>
                                        <div class="tooltip-content">
                                            The title is used to identify this element – useful for assessment. If checked, the title is displayed as a heading for this element for the student as well.                        </div>
                                    </div>
                                </label>

                                <div class="file_url_holder">
                                    <label>Link Text                        <input type="text" name="file_module_link_text[]" value="Download">
                                    </label>

                                    <label>Enter a URL or Browse for a file.                        <input class="file_url" type="text" size="36" name="file_module_file_url[]" value="">
                                        <input class="file_url_button" type="button" value="Browse">
                                    </label>
                                </div>

                                <a class="remove_module_link" onclick="if (removeModule()) {

                                            jQuery(this).parent().parent().remove();
                                            update_sortable_module_indexes();
                                            /* jQuery(this).parent().parent().remove();*/

                                        }"><i class="fa fa-trash-o"></i> Remove</a>
                            </div>

                        </div>

                        <li class="draggable-module ui-draggable" id="image_module">
                            <div class="action action-draggable">
                                <div class="action-top closed">
                                    <a href="#available-actions" class="action-button hide-if-no-js"></a>
                                    Image                    </div>
                                <div class="action-body closed">
                                    <p>
                                        Image, 100% width                            </p>

                                </div>
                            </div>
                        </li>

                        <div class="draggable-module-holder-image_module module-holder-title" style="display:none;">

                            <h3 class="module-title sidebar-name">
                                <span class="h3-label">
                                    <span class="h3-label-left">Untitled</span>
                                    <span class="h3-label-right">Image</span>
                                    <span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
                                </span>
                            </h3>

                            <div class="module-content">
                                <input type="hidden" name="image_module_module_order[]" class="module_order" value="8">
                                <input type="hidden" name="module_type[]" value="image_module">
                                <input type="hidden" name="image_module_id[]" value="">

                                <label class="bold-label">Element Title            <div class="module_time_estimation">Time Estimation (mins) <input type="text" name="image_module_time_estimation[]" value="1:00"></div>
                                </label>
                                <input type="text" class="element_title" name="image_module_title[]" value="">


                                <label class="show_title_on_front">Show Title                    <input type="checkbox" name="image_module_show_title_on_front[]" value="yes" checked="">
                                    <a class="help-icon" href="javascript:;"></a>
                                    <div class="tooltip">
                                        <div class="tooltip-before"></div>
                                        <div class="tooltip-button">×</div>
                                        <div class="tooltip-content">
                                            The title is used to identify this element. If checked, the title is displayed as a heading for this element for the student as well.                        </div>
                                    </div>
                                </label>

                                <div class="file_url_holder">
                                    <label>Enter a URL or Browse for an image.                        <input class="file_url" type="text" size="36" name="image_module_image_url[]" value="">
                                        <input class="file_url_button" type="button" value="Browse">
                                    </label>
                                </div>
                                <a class="remove_module_link" onclick="if (removeModule()) {

                                            jQuery(this).parent().parent().remove();
                                            update_sortable_module_indexes();
                                            /* jQuery(this).parent().parent().remove();*/

                                        }"><i class="fa fa-trash-o"></i> Remove</a>
                            </div>

                        </div>

                        <li class="draggable-module ui-draggable" id="section_break_module">
                            <div class="action action-draggable">
                                <div class="action-top closed">
                                    <a href="#available-actions" class="action-button hide-if-no-js"></a>
                                    Section Break                    </div>
                                <div class="action-body closed">
                                    <p>
                                        Inserts section break ( </p><hr> element )                            <p></p>

                                </div>
                            </div>
                        </li>

                        <div class="draggable-module-holder-section_break_module module-holder-title" style="display:none;">

                            <h3 class="module-title sidebar-name">
                                <span class="h3-label">

                                    <span class="h3-label-left">Section Break</span>
                                    <span class="page-break-dashed"></span>
                                    <span class="page-break-right-fix">...</span>
                                    <span class="h3-label-right">Section Break</span>
                                    <span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
                                </span>
                            </h3>

                            <div class="module-content">
                                <input type="hidden" name="section_break_module_module_order[]" class="module_order" value="9">
                                <input type="hidden" name="module_type[]" value="section_break_module">
                                <input type="hidden" name="section_break_module_id[]" value="">
                                <input type="hidden" name="section_break_module_title[]" value="">

                                <a class="remove_module_link" onclick="if (removeModule()) {

                                            jQuery(this).parent().parent().remove();
                                            update_sortable_module_indexes();
                                            /* jQuery(this).parent().parent().remove();*/

                                        }"><i class="fa fa-trash-o"></i> Remove</a>
                            </div>
                        </div>

                        <li class="draggable-module ui-draggable" id="text_module">
                            <div class="action action-draggable">
                                <div class="action-top closed">
                                    <a href="#available-actions" class="action-button hide-if-no-js"></a>
                                    Text                    </div>
                                <div class="action-body closed">
                                    <p>
                                        Add text block to the unit.                            </p>

                                </div>
                            </div>
                        </li>

                        <div class="draggable-module-holder-text_module module-holder-title" style="display:none;">

                            <h3 class="module-title sidebar-name">
                                <span class="h3-label">
                                    <span class="h3-label-left">Untitled</span>
                                    <span class="h3-label-right">Text</span>
                                    <span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
                                </span>
                            </h3>

                            <div class="module-content">

                                <input type="hidden" name="text_module_module_order[]" class="module_order" value="10">
                                <input type="hidden" name="module_type[]" value="text_module">
                                <input type="hidden" name="text_module_id[]" value="">

                                <label class="bold-label">Element Title            <div class="module_time_estimation">Time Estimation (mins) <input type="text" name="text_module_time_estimation[]" value="1:00"></div>
                                </label>
                                <input type="text" class="element_title" name="text_module_title[]" value="">


                                <label class="show_title_on_front">Show Title                    <input type="checkbox" name="text_module_show_title_on_front[]" value="yes" checked="">
                                    <a class="help-icon" href="javascript:;"></a>
                                    <div class="tooltip">
                                        <div class="tooltip-before"></div>
                                        <div class="tooltip-button">×</div>
                                        <div class="tooltip-content">
                                            The title is used to identify this element – useful for assessment. If checked, the title is displayed as a heading for this element for the student as well.                        </div>
                                    </div>
                                </label>

                                <label class="bold-label">Content</label>

                                <div class="editor_in_place">
                                    <div id="wp-9090-wrap" class="wp-core-ui wp-editor-wrap tmce-active"><div id="wp-9090-editor-tools" class="wp-editor-tools hide-if-no-js"><div id="wp-9090-media-buttons" class="wp-media-buttons"><a href="#" id="insert-media-button" class="button insert-media add_media" data-editor="9090" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a></div>
                                            <div class="wp-editor-tabs"></div>
                                        </div>
                                        <div id="wp-9090-editor-container" class="wp-editor-container"><div id="mce_153" class="mce-tinymce mce-container mce-panel" hidefocus="1" tabindex="-1" role="application" style="visibility: hidden; border-width: 1px;"><div id="mce_153-body" class="mce-container-body mce-stack-layout"><div id="mce_154" class="mce-toolbar-grp mce-container mce-panel mce-first mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group"><div id="mce_154-body" class="mce-container-body mce-stack-layout"><div id="mce_155" class="mce-container mce-toolbar mce-first mce-last mce-stack-layout-item" role="toolbar"><div id="mce_155-body" class="mce-container-body mce-flow-layout"><div id="mce_156" class="mce-container mce-first mce-last mce-flow-layout-item mce-btn-group" role="group"><div id="mce_156-body"><div id="mce_141" class="mce-widget mce-btn mce-first" tabindex="-1" aria-labelledby="mce_141" role="button" aria-label="Bold"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bold"></i></button></div><div id="mce_142" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_142" role="button" aria-label="Italic"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-italic"></i></button></div><div id="mce_143" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_143" role="button" aria-label="Underline"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-underline"></i></button></div><div id="mce_144" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_144" role="button" aria-label="Blockquote"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-blockquote"></i></button></div><div id="mce_145" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_145" role="button" aria-label="Strikethrough"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-strikethrough"></i></button></div><div id="mce_146" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_146" role="button" aria-label="Bullet list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bullist"></i></button></div><div id="mce_147" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_147" role="button" aria-label="Numbered list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-numlist"></i></button></div><div id="mce_148" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_148" role="button" aria-label="Align left"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignleft"></i></button></div><div id="mce_149" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_149" role="button" aria-label="Align center"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-aligncenter"></i></button></div><div id="mce_150" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_150" role="button" aria-label="Align right"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignright"></i></button></div><div id="mce_151" class="mce-widget mce-btn mce-disabled" tabindex="-1" aria-labelledby="mce_151" role="button" aria-label="Undo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-undo"></i></button></div><div id="mce_152" class="mce-widget mce-btn mce-last mce-disabled" tabindex="-1" aria-labelledby="mce_152" role="button" aria-label="Redo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-redo"></i></button></div></div></div></div></div></div></div><div id="mce_157" class="mce-edit-area mce-container mce-panel mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><iframe id="9090_ifr" src='javascript:""' frameborder="0" allowtransparency="true" title="Rich Text Area. Press ALT-F9 for menu. Press ALT-F10 for toolbar. Press ALT-0 for help" style="width: 100%; height: 100px; display: block;"></iframe></div><div id="mce_158" class="mce-statusbar mce-container mce-panel mce-last mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><div id="mce_158-body" class="mce-container-body mce-flow-layout"><div id="mce_159" class="mce-path mce-first mce-flow-layout-item"><div role="button" class="mce-path-item mce-last" data-index="0" tabindex="-1" id="mce_159-0" aria-level="0">p</div></div><div id="mce_160" class="mce-last mce-flow-layout-item mce-resizehandle"><i class="mce-ico mce-i-resize"></i></div></div></div></div></div><textarea class="wp-editor-area" rows="5" autocomplete="off" cols="40" name="text_module_content[]" id="9090" style="display: none;" aria-hidden="true"></textarea></div>
                                    </div>

                                </div>
                                <a class="remove_module_link" onclick="if (removeModule()) {

                                            jQuery(this).parent().parent().remove();
                                            update_sortable_module_indexes();
                                            /* jQuery(this).parent().parent().remove();*/

                                        }"><i class="fa fa-trash-o"></i> Remove</a>
                            </div>

                        </div>

                        <li class="draggable-module ui-draggable" id="video_module">
                            <div class="action action-draggable">
                                <div class="action-top closed">
                                    <a href="#available-actions" class="action-button hide-if-no-js"></a>
                                    Video                    </div>
                                <div class="action-body closed">
                                    <p>
                                        Allows adding video files and video embeds to the unit                            </p>

                                </div>
                            </div>
                        </li>

                        <div class="draggable-module-holder-video_module module-holder-title" style="display:none;">

                            <h3 class="module-title sidebar-name">
                                <span class="h3-label">
                                    <span class="h3-label-left">Untitled</span>
                                    <span class="h3-label-right">Video</span>
                                    <span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
                                </span>
                            </h3>

                            <div class="module-content">

                                <input type="hidden" name="video_module_module_order[]" class="module_order" value="11">
                                <input type="hidden" name="module_type[]" value="video_module">
                                <input type="hidden" name="video_module_id[]" value="">

                                <label class="bold-label">Element Title            <div class="module_time_estimation">Time Estimation (mins) <input type="text" name="video_module_time_estimation[]" value="1:00"></div>
                                </label>
                                <input type="text" class="element_title" name="video_module_title[]" value="">


                                <label class="show_title_on_front">Show Title                    <input type="checkbox" name="video_module_show_title_on_front[]" value="yes" checked="">
                                    <a class="help-icon" href="javascript:;"></a>
                                    <div class="tooltip">
                                        <div class="tooltip-before"></div>
                                        <div class="tooltip-button">×</div>
                                        <div class="tooltip-content">
                                            The title is used to identify this element – useful for assessment. If checked, the title is displayed as a heading for this element for the student as well.                        </div>
                                    </div>
                                </label>

                                <label class="bold-label">Content</label>

                                <div class="editor_in_place">

                                    <div id="wp-1393-wrap" class="wp-core-ui wp-editor-wrap tmce-active"><div id="wp-1393-editor-tools" class="wp-editor-tools hide-if-no-js"><div id="wp-1393-media-buttons" class="wp-media-buttons"><a href="#" id="insert-media-button" class="button insert-media add_media" data-editor="1393" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a></div>
                                            <div class="wp-editor-tabs"></div>
                                        </div>
                                        <div id="wp-1393-editor-container" class="wp-editor-container"><div id="mce_33" class="mce-tinymce mce-container mce-panel" hidefocus="1" tabindex="-1" role="application" style="visibility: hidden; border-width: 1px;"><div id="mce_33-body" class="mce-container-body mce-stack-layout"><div id="mce_34" class="mce-toolbar-grp mce-container mce-panel mce-first mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group"><div id="mce_34-body" class="mce-container-body mce-stack-layout"><div id="mce_35" class="mce-container mce-toolbar mce-first mce-last mce-stack-layout-item" role="toolbar"><div id="mce_35-body" class="mce-container-body mce-flow-layout"><div id="mce_36" class="mce-container mce-first mce-last mce-flow-layout-item mce-btn-group" role="group"><div id="mce_36-body"><div id="mce_21" class="mce-widget mce-btn mce-first" tabindex="-1" aria-labelledby="mce_21" role="button" aria-label="Bold"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bold"></i></button></div><div id="mce_22" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_22" role="button" aria-label="Italic"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-italic"></i></button></div><div id="mce_23" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_23" role="button" aria-label="Underline"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-underline"></i></button></div><div id="mce_24" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_24" role="button" aria-label="Blockquote"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-blockquote"></i></button></div><div id="mce_25" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_25" role="button" aria-label="Strikethrough"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-strikethrough"></i></button></div><div id="mce_26" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_26" role="button" aria-label="Bullet list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bullist"></i></button></div><div id="mce_27" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_27" role="button" aria-label="Numbered list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-numlist"></i></button></div><div id="mce_28" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_28" role="button" aria-label="Align left"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignleft"></i></button></div><div id="mce_29" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_29" role="button" aria-label="Align center"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-aligncenter"></i></button></div><div id="mce_30" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mce_30" role="button" aria-label="Align right"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignright"></i></button></div><div id="mce_31" class="mce-widget mce-btn mce-disabled" tabindex="-1" aria-labelledby="mce_31" role="button" aria-label="Undo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-undo"></i></button></div><div id="mce_32" class="mce-widget mce-btn mce-last mce-disabled" tabindex="-1" aria-labelledby="mce_32" role="button" aria-label="Redo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-redo"></i></button></div></div></div></div></div></div></div><div id="mce_37" class="mce-edit-area mce-container mce-panel mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><iframe id="1393_ifr" src='javascript:""' frameborder="0" allowtransparency="true" title="Rich Text Area. Press ALT-F9 for menu. Press ALT-F10 for toolbar. Press ALT-0 for help" style="width: 100%; height: 100px; display: block;"></iframe></div><div id="mce_38" class="mce-statusbar mce-container mce-panel mce-last mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;"><div id="mce_38-body" class="mce-container-body mce-flow-layout"><div id="mce_39" class="mce-path mce-first mce-flow-layout-item"><div role="button" class="mce-path-item mce-last" data-index="0" tabindex="-1" id="mce_39-0" aria-level="0">p</div></div><div id="mce_40" class="mce-last mce-flow-layout-item mce-resizehandle"><i class="mce-ico mce-i-resize"></i></div></div></div></div></div><textarea class="wp-editor-area" rows="5" autocomplete="off" cols="40" name="video_module_content[]" id="1393" style="display: none;" aria-hidden="true"></textarea></div>
                                    </div>

                                </div>

                                <div class="video_url_holder">
                                    <label>Put a URL or Browse for a video file.                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">×</div>
                                            <div class="tooltip-content">
                                                You can enter a Youtube or Vimeo link e.g. https://www.youtube.com/watch?v=y_bIr1yAELw  ( oEmbed support is required ). Alternatively you can Browse for a file - supported video extensions ( mp4,m4v,webm,ogv,wmv,flv ) 
                                            </div>
                                        </div>
                                        <input class="video_url" type="text" size="36" name="video_module_video_url[]" value="">
                                        <input class="video_url_button" type="button" value="Browse">
                                    </label>
                                </div>

                                <div class="video_additional_controls">

                                    <label>Player Width ( pixels )</label>
                                    <input type="text" name="video_module_player_width[]" value="960">

                                </div>
                                <a class="remove_module_link" onclick="if (removeModule()) {

                                            jQuery(this).parent().parent().remove();
                                            update_sortable_module_indexes();
                                            /* jQuery(this).parent().parent().remove();*/

                                        }"><i class="fa fa-trash-o"></i> Remove</a>
                            </div>

                        </div>

                    </ul>
                </div>

                <div class="sidebar-name no-movecursor">
                    <h3>Invisible Elements</h3>
                </div>

                <div class="section-holder" id="sidebar-invisible" style="min-height: 98px;">
                    <ul class="modules">
                        <li class="draggable-module ui-draggable" id="page_break_module">
                            <div class="action action-draggable">
                                <div class="action-top closed">
                                    <a href="#available-actions" class="action-button hide-if-no-js"></a>
                                    Page Break                    </div>
                                <div class="action-body closed">
                                    <p>
                                        Breaks the Unit into more pages                            </p>

                                </div>
                            </div>
                        </li>

                        <div class="draggable-module-holder-page_break_module module-holder-title" style="display:none;">

                            <h3 class="module-title sidebar-name">
                                <span class="h3-label">

                                    <span class="h3-label-left">Page Break</span>
                                    <span class="page-break-dashed"></span>
                                    <span class="page-break-right-fix">...</span>
                                    <span class="h3-label-right">Page Break</span>
                                    <span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
                                </span>
                            </h3>

                            <!--<div class="module-content">-->
                            <input type="hidden" name="page_break_module_module_order[]" class="module_order" value="12">
                            <input type="hidden" name="module_type[]" value="page_break_module">
                            <input type="hidden" name="page_break_module_id[]" value="">

                            <input type="hidden" name="page_break_module_title[]" value="">

                                        <!--<p>Breaks the Unit into more pages</p>-->

                            <!--</div>-->
                            <a class="remove_module_link" onclick="if (removeModule()) {

                                        jQuery(this).parent().parent().remove();
                                        update_sortable_module_indexes();
                                        /* jQuery(this).parent().parent().remove();*/

                                    }"><i class="fa fa-trash-o"></i> Remove</a>
                        </div>

                    </ul>
                </div>
            </div> <!-- level-holder-wrap -->

        </div> <!-- level-liquid-right -->


        <script type="text/javascript">
            jQuery(document).ready(function() {
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
            });
        </script>
    </div> <!-- wrap -->
</div>