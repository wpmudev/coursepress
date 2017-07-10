<script type="text/template" id="coursepress-course-completion-tpl">
    <div class="cp-box-heading">
        <h2 class="box-heading-title"><?php _e( 'Course Completion', 'cp' ); ?></h2>
    </div>

    <div class="cp-box-content cp-odd">
        <h3 class="label"><?php _e( 'Pick completion page to customize', 'cp' ); ?></h3>
        <ul class="cp-input-group cp-select-list">
            <li class="active" data-page="pre_completion"><?php _e( 'Pre Completion', 'cp' ); ?></li>
            <li data-page="course_completion"><?php _e( 'Successful Completion', 'cp' ); ?></li>
            <li data-page="course_failed"><?php _e( 'Failed', 'cp' ); ?></li>
        </ul>
    </div>

    <div class="cp-box-content">
        <h3 id="completion-title" class="cp-box-header"><?php echo $completion_pages['pre_completion']['title']; ?></h3>
        <p id="completion-description" class="description"><?php echo $completion_pages['pre_completion']['description']; ?></p>
    </div>

    <div class="cp-box-content">
        <div class="cp-box">
            <label class="label" for="page-completion-title"><?php _e( 'Page Title', 'cp' ); ?></label>
            <input type="text" id="page-completion-title" name="page-completion-title" class="widefat" value="{{pre_completion_title}}" />
        </div>
        <div class="cp-box">
            <label class="label" for="page-completion-content"><?php _e( 'Content', 'cp' ); ?></label>
            <div class="cp-alert cp-alert-info">
                <?php echo $completion_pages['tokens']; ?>
            </div>
            <?php coursepress_visual_editor( '{{pre_completion_content}}', 'page-completion-content', array( 'media_buttons' => false, 'name' => 'pre_completion_content' ) ); ?>
        </div>
    </div>

    <div class="cp-box-content">
        <div class="box-label-area">
            <label class="label"><?php _e( 'Custom Certificate', 'cp' ); ?></label>
        </div>

        <div class="box-inner-content">
            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" name="meta_basic_certificate" value="1" class="cp-toggle-input" autocomplete="off" {{_.checked(true,basic_certificate)}} /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php _e( 'Enable custom certificate for this course', 'cp' ); ?></span>
                </label>
                <p class="description"><?php _e( 'Creates custom certificate for this course that overrides default certificate settings.', 'cp' ); ?></p>
            </div>
        </div>
    </div>
    <div id="custom-certificate-setting" class="{{!basic_certificate?'hidden':''}}">
        <div class="cp-box-content">
            <h3 class="label"><?php _e( 'Certificate Content', 'cp' ); ?></h3>
            <?php coursepress_teeny_editor( '{{basic_certificate_layout}}', 'basic-certificate-layout', array( 'name' => 'meta_basic_certificate_layout' ) ); ?>
        </div>
        <div class="cp-box-content">
            <div class="box-label-area">
                <h3 class="label"><?php _e( 'Background Image', 'cp' ); ?></h3>
            </div>
            <div class="box-inner-content">
                <input type="text" class="cp-add-image-input" name="meta_certificate_background" value="{{certificate_background}}" data-thumbnail="20" data-size="medium" data-title="<?php _e( 'Select Background Image', 'cp' ); ?>" />
            </div>
        </div>
        <div class="cp-box-content">
            <div class="box-label-area">
                <h3 class="label"><?php _e( 'Content Margin', 'cp' ); ?></h3>
            </div>
            <div class="box-inner-content">
                <div class="cp-flex">
                    <div class="cp-pad-right">
                        <h3 class="label"><?php _e( 'Top', 'cp' ); ?></h3>
                        <input type="number" name="meta_margin.top" value="{{cert_margin.top}}" />
                    </div>
                    <div class="cp-pad-right">
                        <h3 class="label"><?php _e( 'Left', 'cp' ); ?></h3>
                        <input type="number" name="meta_margin.left" value="{{cert_margin.left}}" />
                    </div>
                    <div class="cp-pad-right">
                        <h3 class="label"><?php _e( 'Right', 'cp' ); ?></h3>
                        <input type="number" name="meta_margin.right" value="{{cert_margin.right}}" />
                    </div>
                </div>
            </div>
        </div>
        <div class="cp-box-content">
            <div class="box-label-area">
                <label class="label"><?php _e( 'Page Orientation', 'cp' ); ?></label>
            </div>
            <div class="box-inner-content">
                <select class="widefat" name="meta_page_orientation">
                    <option value="landscape" {{_.selected('L', page_orientation)}}><?php _e( 'Landscape', 'cp' ); ?></option>
                    <option value="portrait" {{_.selected('P', page_orientation)}}><?php _e( 'Portrait', 'cp' ); ?></option>
                </select>
            </div>
        </div>
        <div class="cp-box-content">
            <button type="button" class="cp-btn cp-btn-default cp-right"><?php _e( 'Preview Certificate', 'cp' ); ?></button>
        </div>
    </div>
</script>