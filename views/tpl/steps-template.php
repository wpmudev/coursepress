<script type="text/template" id="coursepress-step-tpl">
    <div class="icon-move cp-step-header">
        <span class="step-type">{{window._coursepress.steps[module_type]}}</span>
        <input type="text" name="post_title" value="{{post_title}}" />
        <div class="step-config">
            <button type="button"></button>
            <div class="cp-dropdown-menu">
                <ul>
                    <li class="menu-item-duplicate"><?php _e( 'Duplicate step', 'cp' ); ?></li>
                    <li class="menu-item-move"><?php _e( 'Move to different Module', 'cp' ); ?></li>
                    <li class="menu-item-delete"><?php _e( 'Delete step', 'cp' ); ?></li>
                </ul>
            </div>
        </div>

        <button type="button" class="step-toggle-button"></button>
    </div>
    <div class="cp-step-content cp-step-{{module_type}}"></div>
    <div class="cp-step-footer">
        <div class="cp-toggle-box">
            <label>
                <input type="checkbox" name="meta_show_title" value="1" class="cp-toggle-input" {{_.checked(true, show_title)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Show module title in unit view', 'cp' ); ?></span>
            </label>
            <a href="" class="cp-btn cp-btn-xs cp-bordered-btn cp-preview"><?php _e( 'Preview', 'cp' ); ?></a>
        </div>
    </div>
    <input type="hidden" data-cid="{{cid}}" name="menu_order" value="{{menu_order}}" />
</script>

<script type="text/template" id="coursepress-step-image">
    <div class="cp-box">
        <label class="label"><?php _e( 'Image Source', 'cp' ); ?></label>
        <input type="text" name="meta_image_url" data-thumbnail="{{meta_image_url_thumbnail_id}}" class="cp-add-image-input" value="{{meta_image_url}}" id="meta_image_url_{{menu_order}}" />
    </div>
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_show_media_caption" value="1" class="cp-toggle-input" {{_.checked(true, meta_show_media_caption)}} /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Show image caption', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box image-custom-caption {{meta_show_media_caption?'':'inactive'}}">
        <div class="cp-toggle-box">
            <label>
                <input type="radio" name="meta_caption_field[{{cid}}]" value="media" {{_.checked('media', meta_caption_field)}} class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Use media caption', 'cp' ); ?></span>
            </label>
        </div>
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="radio" name="meta_caption_field[{{cid}}]" value="custom" {{_.checked('custom', meta_caption_field)}} class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Use custom caption', 'cp' ); ?></span>
            </label>
        </div>
        <input type="text" class="widefat" name="meta_caption_custom_text" value="{{meta_caption_custom_text}}" {{_.disabled(false, 'custom' === meta_caption_field && meta_show_media_caption)}} placeholder="<?php _e( 'Type custom caption here', 'cp' ); ?>" />
    </div>
</script>

<script type="text/template" id="coursepress-step-video">
    <div class="cp-box">
        <label class="label"><?php _e( 'Video Source', 'cp' ); ?></label>
        <input type="text" class="widefat cp-add-video" name="meta_video_url" value="{{meta_video_url}}"  data-title="<?php _e( 'Select Video Source', 'cp' ); ?>" />
    </div>
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_video_loop" value="1" class="cp-toggle-input" {{_.checked(true, meta_video_loop)}} /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Restart the video when it ends', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_video_autoplay" value="1" class="cp-toggle-input" {{_.checked(true, meta_video_autoplay)}} /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Auto play video on page load', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_video_hide_controls" value="1" class="cp-toggle-input" {{_.checked(true, meta_video_hide_controls)}} /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Hide video control buttons', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_show_media_caption" value="1" class="cp-toggle-input" {{_.checked(true, meta_show_media_caption)}} /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Show video caption', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_hide_related_media" value="1" class="cp-toggle-input" {{_.checked(true, meta_hide_related_media)}} /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Hide related videos', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box">
        <div class="cp-toggle-box">
            <label>
                <input type="checkbox" name="meta_allow_retries" value="1" class="cp-toggle-input" {{_.checked(true, meta_allow_retries)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Allow retries', 'cp' ); ?></span>
            </label>
        </div>
        <div class="cp-box-grey {{meta_allow_retries?'':'inactive'}}">
            <label class="label"><?php _e( 'Number of allowed retries', 'cp' ); ?></label>
            <input type="text" name="meta_retry_attempts" value="{{meta_retry_attempts}}" />
        </div>
    </div>
</script>

<script type="text/template" id="coursepress-step-audio">
    <div class="cp-box">
        <label class="label"><?php _e( 'Audio Source', 'cp' ); ?></label>
        <input type="text" name="meta_audio_url" class="widefat cp-add-media-input cp-add-audio" data-thumbnail-id="{{meta_audio_url_thumbnail_id}}"  value="{{meta_audio_url}}" data-type="audio" data-placeholder="<?php _e( 'Add audio URL or browse audio', 'cp' ); ?>" data-title="<?php _e( 'Select Audio Source', 'cp' ); ?>" />
    </div>
    <div class="cp-flex">
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="checkbox" name="meta_loop" value="1" class="cp-toggle-input" {{_.checked(true, meta_loop)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Loop', 'cp' ); ?></span>
            </label>
        </div>
    </div>
    <div class="cp-flex">
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="checkbox" name="meta_autoplay" value="1" class="cp-toggle-input" {{_.checked(true, meta_autoplay)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Autoplay', 'cp' ); ?></span>
            </label>
        </div>
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="checkbox" name="meta_allow_retries" value="1" class="cp-toggle-input" {{_.checked(true, meta_allow_retries)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Allow retries', 'cp' ); ?></span>
            </label>
            <div class="cp-box-grey">
                <label class="label"><?php _e( 'Number of allowed retries', 'cp' ); ?></label>
                <input type="text" name="meta_retry_attempts" value="{{meta_retry_attempts}}" />
            </div>
        </div>
    </div>

</script>

<script type="text/template" id="coursepress-step-file-upload">
    <div class="cp-flex">
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="checkbox" name="meta_mandatory" value="1" class="cp-toggle-input" {{_.checked(true, meta_mandatory)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Required', 'cp' ); ?></span>
            </label>
        </div>
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="checkbox" name="meta_assessable" value="1" class="cp-toggle-input" {{_.checked(true, meta_assessable)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Assessable', 'cp' ); ?></span>
            </label>
        </div>
    </div>

    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_assessable" value="1" class="cp-toggle-input" {{_.checked(true, meta_allow_retries)}} /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Allow retries', 'cp' ); ?></span>
        </label>
    </div>

    <div class="cp-flex">
        <div class="cp-box">
            <div class="cp-box-grey">
                <label class="label"><?php _e( 'Number of allowed retries', 'cp' ); ?></label>
                <input type="text" name="meta_retry_attempts" value="{{meta_retry_attempts}}" />
            </div>
        </div>
        <div class="cp-box">
            <div class="cp-box-grey">
                <label class="label"><?php _e( 'Minimum Grade', 'cp' ); ?></label>
                <input type="text" name="meta_minimum_grade" value="{{meta_minimum_grade}}" />
            </div>
        </div>
    </div>

    <div class="cp-box">
        <label class="label"><?php _e( 'Allowed file format', 'cp' ); ?></label>
        <div class="cp-flex">
            <?php foreach ( $file_types as $type => $label ) : ?>
            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" name="meta_allowed_file_types" value="<?php echo $type; ?>" {{_.checked('<?php echo $type; ?>', meta_allowed_file_types)}} class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php echo $label ?></span>
                </label>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_show_content" value="1" class="cp-toggle-input" {{_.checked(true, meta_show_content)}} /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Show content', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box cp-step-description {{!!meta_show_content?'':'inactive'}}"></div>
</script>

<script type="text/template" id="coursepress-step-quiz">
    <div class="cp-content-box">
        <div class="cp-flex">
            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" name="meta_mandatory" value="1" class="cp-toggle-input" {{_.checked(true, meta_mandatory)}} /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php _e( 'Required', 'cp' ); ?></span>
                </label>
            </div>

            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" name="meta_assessable" value="1" class="cp-toggle-input" {{_.checked(true, meta_assessable)}} /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php _e( 'Assessable', 'cp' ); ?></span>
                </label>
            </div>

            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" name="meta_allow_retries" value="1" class="cp-toggle-input" {{_.checked(true, meta_allow_retries)}} /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php _e( 'Allow retries', 'cp' ); ?></span>
                </label>
            </div>
        </div>

        <div class="cp-flex">


            <div class="cp-box">
                <div class="cp-box-grey">
                    <label class="label"><?php _e( 'Number of allowed retries', 'cp' ); ?></label>
                    <input type="text" name="meta_retry_attempts" value="{{meta_retry_attempts}}" />
                </div>
            </div>

            <div class="cp-box">
                <div class="cp-box-grey">
                    <label class="label"><?php _e( 'Minimum Grade', 'cp' ); ?></label>
                    <input type="text" name="meta_minimum_grade" value="{{meta_minimum_grade}}" />
                </div>
            </div>
        </div>
    </div>

    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_show_content" value="1" class="cp-toggle-input" {{_.checked(true, meta_show_content)}} /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Show content', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box cp-step-description {{!!meta_show_content?'':'inactive'}}"></div>

    <div class="cp-content-box">
        <h3><?php _e( 'Quiz questions', 'cp' ); ?></h3>
        <p class="description"><?php _e( 'Add all the questions for your quiz below. You can have as few or as many questions as you want.', 'cp' ); ?></p>
        <select class="cp-question-type">
            <option value=""><?php _e( 'Add question', 'cp' ); ?></option>
            <?php foreach ( $questions as $type => $label ) : ?>
            <option value="<?php echo $type; ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>

        <div class="cp-questions-container">
            <p class="description no-content-info"><?php _e( 'There are currently no questions in this quiz.', 'cp' ); ?></p>
        </div>
    </div>
</script>

<script type="text/template" id="coursepress-question-tpl">
    <div class="cp-question-header">
        <span class="q-type">{{window._coursepress.questions[type]}}</span>
        <input type="text" name="title" value="{{title}}" />
        <span class="cp-btn cp-btn-trash"></span>
        <button type="button" class="question-toggle-button"></button>
    </div>
    <div class="cp-question-content">
        <p class="description"><?php _e( 'Add the question and multiple possible answers below, tick checkbox next to correct answers.', 'cp' ); ?></p>

        <div class="cp-box">
            <label class="label"><?php _e( 'Question text', 'cp' ); ?></label>
            <textarea class="widefat" name="question">{{question}}</textarea>
            <div class="question-answers"></div>
            <button type="button" class="cp-btn cp-btn-xs cp-btn-active"><?php _e( 'Add Answer', 'cp' ); ?></button>
            <input class="question-order" type="hidden" name="order" value="{{order}}" />
        </div>
    </div>
</script>

<script type="text/template" id="coursepress-question-answer">
    <label class="cp-checkbox cp-ignore-update-model">
        <input type="{{'multiple'===type?'checkbox':'radio'}}" name="checked[{{cid}}]" autocomplete="off" class="cp-checkbox-input coursepress-question-answer-checked" value="1" {{_.checked(true, checked)}} />
        <span class="cp-checkbox-icon"></span>
    </label>
    <input type="text" name="answers" value="{{answer}}" />
    <span class="cp-btn cp-btn-trash"></span>
</script>

<script type="text/template" id="coursepress-step-zipped">
    <div class="cp-box">
        <label class="label"><?php _e( 'Zipped website source', 'cp' ); ?></label>
        <input type="text" name="meta_zip_url" value="{{meta_zip_url}}" class="cp-add-media" data-type="" data-title="<?php _e( 'Browse source', 'cp' ); ?>" data-placeholder="<?php _e( 'Browse for a zip file', 'cp' ); ?>" />
    </div>
    <div class="cp-flex">
        <div class="cp-box">
            <label class="label"><?php _e( 'Zip website link text', 'cp' ); ?></label>
            <input type="text" name="meta_link_text" value="{{meta_link_text}}" />
        </div>
        <div class="cp-box">
            <label class="label"><?php _e( 'Primary file', 'cp' ); ?></label>
            <input type="text" name="meta_primary_file" value="{{meta_primary_file}}" placeholder="<?php _e( 'e.g. index.html', 'cp' ); ?>" />
        </div>
    </div>
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_show_content" value="1" class="cp-toggle-input" {{_.checked(true, meta_show_content)}} /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Show content', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box cp-step-description {{!!meta_show_content?'':'inactive'}}">THE CONTENT HERE</div>
</script>

<script type="text/template" id="coursepress-step-written">
    <div class="cp-content-box">
        <div class="cp-flex">
            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" name="meta_mandatory" value="1" class="cp-toggle-input" {{_.checked(true, meta_mandatory)}} /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php _e( 'Required', 'cp' ); ?></span>
                </label>
            </div>
            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" name="meta_assessable" value="1" class="cp-toggle-input" {{_.checked(true, meta_assessable)}} /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php _e( 'Assessable', 'cp' ); ?></span>
                </label>
            </div>
        </div>
        <div class="cp-flex">
            <div class="cp-box">
                <div class="cp-box-grey">
                    <label class="label"><?php _e( 'Number of allowed retries', 'cp' ); ?></label>
                    <input type="text" name="meta_retry_attempts" value="{{meta_retry_attempts}}" />
                </div>
            </div>
            <div class="cp-box">
                <div class="cp-box-grey">
                    <label class="label"><?php _e( 'Minimum Grade', 'cp' ); ?></label>
                    <input type="text" name="meta_minimum_grade" value="{{meta_minimum_grade}}" />
                </div>
            </div>
        </div>
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="checkbox" name="meta_show_content" value="1" class="cp-toggle-input" {{_.checked(true, meta_show_content)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Show content', 'cp' ); ?></span>
            </label>
        </div>
        <div class="cp-box cp-step-description {{!!meta_show_content?'':'inactive'}}"></div>
    </div>
    <div class="cp-content-box">
        <h3><?php _e( 'Written questions', 'cp' ); ?></h3>
        <p class="description"><?php _e( 'Add all the questions for your quiz below. You can have as few or as many questions as you want.', 'cp' ); ?></p>
        <button type="button" class="cp-btn cp-btn-xs cp-btn-active add-question"><?php _e( 'Add Question', 'cp' ); ?></button>

        <div class="cp-questions-container">
            <p class="description no-content-info"><?php _e( 'There are currently no questions in this quiz.', 'cp' ); ?></p>
        </div>
    </div>
</script>

<script type="text/template" id="coursepress-written-tpl">
    <div class="cp-question-header">
        <span class="q-type"><?php _e( 'Written', 'cp' ); ?></span>
        <input type="text" value="{{title}}" />
        <button type="button" class="question-toggle-button"></button>
    </div>
    <div class="cp-question-content">
        <p class="description"><?php _e( 'Add the question and multiple possible answers below, tick checkbox next to correct answers.', 'cp' ); ?></p>

        <div class="cp-box">
            <label class="label"><?php _e( 'Question text', 'cp' ); ?></label>
            <textarea class="widefat" name="question">{{question}}</textarea>
        </div>
        <div class="cp-box">
            <label class="label"><?php _e( 'Answer area placeholder text', 'cp' ); ?></label>
            <input type="text" name="meta_placeholder_text" class="widefat" value="{{placeholder_text}}" placeholder="<?php _e( 'Describe how question should be answer', 'cp' ); ?>" />
        </div>
        <div class="cp-box">
            <label class="label"><?php _e( 'Answer word limit', 'cp' ); ?></label>
            <input type="text" name="meta_word_limit" value="{{word_limit}}" />
            <p class="description"><?php _e( 'Set 0 for no word limit answer', 'cp' ); ?></p>
        </div>
    </div>
</script>

<script type="text/template" id="coursepress-step-discussion">
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_mandatory" value="1" class="cp-toggle-input" {{_.checked(true, meta_mandatory)}} /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Required', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_show_content" value="1" class="cp-toggle-input" {{_.checked(true, meta_show_content)}} /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Show content', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box cp-step-description {{!!meta_show_content?'':'inactive'}}">THE CONTENT HERE</div>
</script>

<script type="text/template" id="coursepress-step-download">
    <div class="cp-box">
        <label class="label"><?php _e( 'Download file path', 'cp' ); ?></label>
        <input type="text" name="meta_file_url" value="{{meta_file_url}}" class="cp-add-media" data-type="" data-title="<?php _e( 'Browse source', 'cp' ); ?>" data-placeholder="<?php _e( 'Add file URL or browse for file or download', 'cp' ); ?>" />
    </div>
    <div class="cp-box">
        <label class="label"><?php _e( 'Download link text', 'cp' ); ?></label>
        <input type="text" name="meta_link_text" class="widefat" value="{{meta_link_text}}" data-placeholder="<?php _e( 'Type link text here', 'cp' ); ?>" />
    </div>
    <div class="cp-box cp-toggle-box">
        <label>
            <input type="checkbox" name="meta_show_content" value="1" class="cp-toggle-input" {{_.checked(true, meta_show_content)}} /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Show content', 'cp' ); ?></span>
        </label>
    </div>
    <div class="cp-box cp-step-description {{!!meta_show_content?'':'inactive'}}">THE CONTENT HERE</div>
</script>
