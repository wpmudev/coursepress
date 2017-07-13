<script type="text/template" id="coursepress-step-tpl">
    <div class="cp-step-header">
        <span class="step-type">{{type}}</span>
        <input type="text" name="post_title" value="{{post_title}}" />
        <button type="button" class="step-toggle-button"></button>
    </div>
    <div class="cp-step-content"></div>
    <div class="cp-step-footer">
        <div class="cp-toggle-box">
            <label>
                <input type="checkbox" name="meta_show_title" value="1" class="cp-toggle-input" {{_.checked(true, show_title)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Show module title in unit view', 'cp' ); ?></span>
            </label>
            <a href="" class="cp-btn cp-btn-xs cp-bordered-btn cp-preview"><?php _e( 'Preview', 'cp' ); ?></a>
        </div>
    </div>
</script>

<script type="text/template" id="coursepress-step-image">
    <div class="cp-box">
        <label class="label"><?php _e( 'Image Source', 'cp' ); ?></label>
        <input type="text" name="meta_image_url" class="cp-add-image-input" id="meta_image_url_{{menu_order}}" />
    </div>
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_show_media_caption" value="1" class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Show image caption', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box image-custom-caption inactive">
        <div class="cp-toggle-box">
            <label>
                <input type="checkbox" name="meta_caption_field" value="media" class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Show media caption', 'cp' ); ?></span>
            </label>
        </div>
        <input type="text" class="widefat" name="meta_caption_custom_text" disabled="disabled" placeholder="<?php _e( 'Type custom caption here', 'cp' ); ?>" />
    </div>
</script>

<script type="text/template" id="coursepress-step-video">
    <div class="cp-flex">
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="checkbox" name="meta_mandatory" value="1" class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Required', 'cp' ); ?></span>
            </label>
        </div>
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="checkbox" name="meta_allow_retries" value="1" class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Allow retries', 'cp' ); ?></span>
            </label>
            <div class="cp-box-grey">
                <label class="label"><?php _e( 'Number of allowed retries', 'cp' ); ?></label>
                <input type="text" name="meta_retry_attempts" />
            </div>
        </div>
    </div>
    <div class="cp-box">
        <label class="label"><?php _e( 'Video Source', 'cp' ); ?></label>
        <input type="text" name="meta_video_url" class="widefat" />
    </div>

    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_video_loop" value="1" class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Restart the video when it ends', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_video_autoplay" value="1" class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Auto play video on page load', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_video_hide_controls" value="1" class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Hide video control buttons', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_show_media_caption" value="1" class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Show video caption', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_hide_related_media" value="1" class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Hide related videos', 'cp' ); ?></span>
        </label>
    </div>
</script>

<script type="text/template" id="coursepress-step-audio">
    <div class="cp-flex">
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="checkbox" name="meta_mandatory" value="1" class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Required', 'cp' ); ?></span>
            </label>
        </div>
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="checkbox" name="meta_allow_retries" value="1" class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Allow retries', 'cp' ); ?></span>
            </label>
            <div class="cp-box-grey">
                <label class="label"><?php _e( 'Number of allowed retries', 'cp' ); ?></label>
                <input type="text" name="meta_retry_attempts" />
            </div>
        </div>
    </div>
    <div class="cp-box">
        <label class="label"><?php _e( 'Audio Source', 'cp' ); ?></label>
        <input type="text" name="meta_audio_url" class="widefat" />
    </div>
</script>