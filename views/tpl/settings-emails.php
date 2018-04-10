<script type="text/template" id="coursepress-emails-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php esc_html_e( 'Emails', 'cp' ); ?></h2>
	</div>
	<div class="cp-content">
        <?php
        $first = false;
        $fields = array(
            'from' => array(
                'type' => 'text',
                'label' => esc_html__( 'From name', 'cp' ),
                'value' => '',
                'flex' => true,
                'class' => 'large-text',
                'placeholder' => 'e.g. John Smith',
            ),
            'email' => array(
                'type' => 'text',
                'label' => esc_html__( 'From email', 'cp' ),
                'value' => '',
                'flex' => true,
                'class' => 'large-text',
                'placeholder' => 'e.g. john@example.net',
            ),
            'subject' => array(
                'type' => 'text',
                'label' => esc_html__( 'Subject', 'cp' ),
                'value' => '',
                'class' => 'large-text',
            ),
        );
        ?>

        <div class="cp-box-content cp-odd">
            <h3 class="label"><?php esc_html_e( 'Pick email to customize', 'cp' ); ?></h3>
            <ul class="cp-input-group cp-select-list">
                <?php foreach ( $sections as $section_id => $section ) : ?>
                    <?php
					if ( ! $first ) {
						$first = $section;
					}
					?>
                    <li data-key="<?php echo esc_attr( $section_id ); ?>"><?php echo esc_html( $section['title'] ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="cp-box-content cp-email-fields-content">
            <h3 id="course-email-heading" class="cp-box-header"><?php echo esc_html( $first['title'] ); ?></h3>
            <p id="course-email-desc"><?php echo esc_html( $first['description'] ); ?></p>

            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" name="enabled" value="1" class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php esc_html_e( 'Enable', 'cp' ); ?></span>
                </label>
            </div>
        </div>
        <div class="cp-box-content">
            <?php
            foreach ( $fields as $key => $field ) :
                $field['name'] = $key;
                ?>
                <div class="option option-<?php echo esc_attr( $key ); ?>">
                    <?php if ( ! empty( $field['label'] ) ) : ?>
                        <label class="label"><?php echo esc_html( $field['label'] ); ?></label>
                    <?php endif; ?>

                    <?php lib3()->html->element( $field ); ?>
                </div>
            <?php endforeach; ?>

            <div class="option option-body">
                <p><label class="label"><?php esc_html_e( 'Email Body', 'cp' ); ?></label></p>

                <div class="cp-alert cp-alert-info">
                    <?php echo esc_html( $first['content_help_text'] ); ?>
                </div>

                <div class="coursepress-email-content"></div>
            </div>
        </div>
	</div>
</script>
