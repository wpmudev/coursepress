<script type="text/template" id="coursepress-course-completion-tpl">
    <div class="cp-box-heading">
        <h2 class="box-heading-title"><?php _e( 'Course Completion', 'cp' ); ?></h2>
    </div>

    <div class="cp-box-content cp-sep">
        <div class="box-label-area">
            <label class="label"><?php _e( 'Pre-completion', 'cp' ); ?></label>
            <p class="description"></p>
        </div>

        <div class="box-inner-content">
            <div class="cp-box">
                <label class="label" for="pre-completion-title"><?php _e( 'Page Title', 'cp' ); ?></label>
                <input type="text" id="pre-completion-title" class="widefat" name="meta_pre_completion_title" value="{{pre_completion_title}}" />
            </div>
            <div class="cp-box">
                <label class="label" for="pre-completion-content"><?php _e( 'Content', 'cp' ); ?></label>
                <?php coursepress_visual_editor( '{{pre_completion_content}}', 'pre-completion-content', array( 'media_buttons' => false, 'name' => 'pre_completion_content' ) ); ?>
            </div>
        </div>
    </div>

    <div class="cp-box-content cp-sep">
        <div class="box-label-area">
            <label class="label"><?php _e( 'Successful Completion', 'cp' ); ?></label>
            <p class="description"></p>
        </div>

        <div class="box-inner-content">
            <div class="cp-box">
                <label class="label" for="pre-completion-title"><?php _e( 'Page Title', 'cp' ); ?></label>
                <input type="text" id="pre-completion-title" class="widefat" name="meta_pre_completion_title" value="{{course_completion_title}}" />
            </div>
            <div class="cp-box">
                <label class="label" for="pre-completion-content"><?php _e( 'Content', 'cp' ); ?></label>
                <?php coursepress_visual_editor( '{{course_completion_content}}', 'course-completion-content', array( 'media_buttons' => false, 'name' => 'course_completion_content' ) ); ?>
            </div>
        </div>
    </div>

    <div class="cp-box-content cp-sep">
        <div class="box-label-area">
            <label class="label"><?php _e( 'Failed', 'cp' ); ?></label>
            <p class="description"></p>
        </div>

        <div class="box-inner-content">
            <div class="cp-box">
                <label class="label" for="pre-completion-title"><?php _e( 'Page Title', 'cp' ); ?></label>
                <input type="text" id="pre-completion-title" class="widefat" name="meta_pre_completion_title" value="{{course_failed_title}}" />
            </div>
            <div class="cp-box">
                <label class="label" for="pre-completion-content"><?php _e( 'Content', 'cp' ); ?></label>
                <?php coursepress_visual_editor( '{{course_failed_content}}', 'course-failed-content', array( 'media_buttons' => false, 'name' => 'course_failed_content' ) ); ?>
            </div>
        </div>
    </div>

    <div class="cp-box-content">
        <div class="box-label-area">
            <label class="label"><?php _e( 'Certificate', 'cp' ); ?></label>
        </div>

        <div class="box-inner-content">
            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" name="basic_certificate" class="cp-toggle-input" autocomplete="off" {{_.checked(true,basic_certificate)}} /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php _e( 'Enable custom certificate', 'cp' ); ?></span>
                </label>
                <p class="description"><?php _e( 'Creates custom certificate for this course that overrides default certificate settings.', 'cp' ); ?></p>
            </div>

            <div id="custom-certificate-setting" class="{{!_.isTrue(basic_certificate)?'hidden':''}}">
                <div class="cp-box">
                    <label class="label"><?php _e( 'Certificate Content', 'cp' ); ?></label>
                    <?php coursepress_teeny_editor( '{{basic_certificate_layout}}', 'basic-certificate-layout', array( 'name' => 'basic_certificate_layout' ) ); ?>
                </div>
                <div class="cp-box">
                    <label class="label"><?php _e( 'Background Image', 'cp' ); ?></label>
                    <input type="text" class="cp-add-image-input" name="certificate_background" value="{{certificate_background}}" data-thumbnail="20" data-size="medium" data-title="<?php _e( 'Select Background Image', 'cp' ); ?>" />
                </div>
                <div class="cp-box">
                    <label class="label"><?php _e( 'Content Margin', 'cp' ); ?></label>
                    <div class="cp-flex">
                        <div class="">
                            <label class="label"><?php _e( 'Top', 'cp' ); ?></label>
                            <input type="text" value="{{cert_margin.top}}" />
                        </div>
                        <div class="">
                            <label class="label"><?php _e( 'Left', 'cp' ); ?></label>
                            <input type="text" value="{{cert_margin.left}}" />
                        </div>
                        <div class="">
                            <label class="label"><?php _e( 'Right', 'cp' ); ?></label>
                            <input type="text" value="{{cert_margin.right}}" />
                        </div>
                    </div>
                </div>
                <div class="cp-box">
                    <label class="label"><?php _e( 'Page Orientation', 'cp' ); ?></label>
                    <select name="page_orientation">
                        <option value="landscape" {{_.selected('L', page_orientation)}}><?php _e( 'Landscape', 'cp' ); ?></option>
                        <option value="portrait" {{_.selected('P', page_orientation)}}><?php _e( 'Portrait', 'cp' ); ?></option>
                    </select>
                </div>
                <div class="cp-box">
                    <button type="button" class="cp-btn cp-btn-default cp-right"><?php _e( 'Preview Certificate', 'cp' ); ?></button>
                </div>
            </div>
        </div>
    </div>
</script>