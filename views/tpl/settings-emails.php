<script type="text/template" id="coursepress-emails-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'Emails', 'cp' ); ?></h2>
	</div>
	<div class="cp-content">
        <?php
        $first = false;
        $fields = array(
            'from' => array(
                'type' => 'text',
                'label' => __( 'From name', 'cp' ),
                'value' => '',
                'flex' => true,
                'class' => 'large-text',
                'placeholder' => 'e.g. John Smith',
            ),
            'email' => array(
                'type' => 'text',
                'label' => __( 'From email', 'cp' ),
                'value' => '',
                'flex' => true,
                'class' => 'large-text',
                'placeholder' => 'e.g. john@example.net',
            ),
            'subject' => array(
                'type' => 'text',
                'label' => __( 'Subject', 'cp' ),
                'value' => '',
                'class' => 'large-text',
            ),
        );
        ?>

        <div class="cp-box-content cp-odd">
            <h3 class="label"><?php _e( 'Pick email to customize', 'cp' ); ?></h3>
            <ul class="cp-input-group cp-select-list">
                <?php foreach ( $sections as $section_id => $section ) : ?>
                    <?php if ( ! $first ) : $first = $section; endif; ?>
                    <li data-key="<?php echo $section_id; ?>"><?php echo $section['title']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="cp-box-content cp-email-fields-content">
            <h3 id="course-email-heading" class="cp-box-header"><?php echo $first['title']; ?></h3>
            <p id="course-email-desc"><?php echo $first['description']; ?></p>

            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" name="enabled" value="1" class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php _e( 'Enable', 'cp' ); ?></span>
                </label>
            </div>
        </div>
        <div class="cp-box-content">
            <?php
            foreach ( $fields as $key => $field ) :
                $field['name'] = $key;
                ?>
                <div class="option option-<?php echo $key; ?>">
                    <?php if ( ! empty( $field['label'] ) ) : ?>
                        <label class="label"><?php echo $field['label']; ?></label>
                    <?php endif; ?>

                    <?php lib3()->html->element( $field ); ?>
                </div>
            <?php endforeach; ?>

            <div class="option option-body">
                <p><label class="label"><?php _e( 'Email Body', 'cp' ); ?></label></p>

                <div class="cp-alert cp-alert-info">
                    <?php echo $first['content_help_text']; ?>
                </div>

                <div class="coursepress-email-content"></div>
            </div>
        </div>
	</div>
</script>
