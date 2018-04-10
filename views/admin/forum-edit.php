<?php
/**
 * @var string $page_title
 * @var int $course_id
 * @var array $menu_list
 */

		$toggle_input = coursepress_create_html( 'span', array( 'class' => 'cp-toggle-btn' ) );

		$config = array();
		$config['content'] = array(
			'title' => __( 'Topic', 'cp' ),
			'fields' => array(
				'post_title' => array(
					'type' => 'text',
					'value' => $post_title,
					'class' => 'large-text',
				),
				'post_content' => array(
					'id' => 'post_content',
					'type' => 'wp_editor',
					'value' => $post_content,
					'options' => array(
						'quicktags' => false,
					),
				),
			),
		);
		$config['related'] = array(
			'title' => __( 'Related Courses', 'cp' ),
			'fields' => array(
				'course_id' => array(
					'type' => 'select',
					'title' => __( 'Select Course', 'cp' ),
					'value' => $course_id,
					'class' => 'select2',
					'data' => array(
						'nonce' => wp_create_nonce( 'coursepress-course-search-nonce' ),
					),
					'field_options' => $courses,
				),
				'unit_id' => array(
					'type' => 'select',
					'title' => __( 'Select Unit', 'cp' ),
					'value' => $unit_id,
					'field_options' => $units,
					'data' => array(
						'nonce' => wp_create_nonce( 'coursepress_nonce' ),
						'all-value' => 'course',
						'all-label' => __( 'All Units', 'cp' ),
						'value' => $unit_id,
					),
				),
			),
		);
		$config['settings'] = array(
			'title' => __( 'Settings', 'cp' ),
			'fields' => array(
				'email_notification' => array(
					'type' => 'checkbox',
					'title' => $toggle_input . __( 'Enable email notification', 'cp' ),
					'value' => $email_notification,
				),
				'thread_comments_depth' => array(
					'type' => 'number',
					'title' => __( 'Threaded comments level', 'cp' ),
					'value' => $thread_comments_depth,
				),
				'thread_comments_depth' => array(
					'type' => 'number',
					'title' => __( 'Threaded comments level', 'cp' ),
					'value' => $thread_comments_depth,
					'class' => 'small-text',
				),
				'comments_per_page' => array(
					'type' => 'number',
					'title' => __( 'Number of comments per page', 'cp' ),
					'value' => $comments_per_page,
					'class' => 'small-text',
				),
				'comments_order' => array(
					'type' => 'radio',
					'title' => __( 'Comments order', 'cp' ),
					'value' => $comments_order,
					'field_options' => array(
						'newer' => __( 'Newer first', 'cp' ),
						'older' => __( 'Older first', 'cp' ),
					),
				),
			),

		);
?>
<form method="post" id="coursepress-forum-form" >
<div class="wrap coursepress-wrap coursepress-notifications" id="coursepress-notifications">
    <h1 class="wp-heading-inline"><?php $forum_id ? esc_html_e( 'Edit Forum', 'cp' ):esc_html_e( 'Create new', 'cp' ); ?></h1>
	<?php if ( !empty( $forum_created ) ) { ?>
		<div class="notice notice-success"><p>
			<?php
			$url = add_query_arg(
				array(
					'page' => $page,
					$id_name => $forum_created,
				),
				admin_url( 'admin.php' )
			);
			
			printf( __( 'Forum created. %sEdit forum%s', 'cp' ), '<a href="' . $url . '">', '</a>' );
			?>
		</p></div>
	<?php } ?>
    <div class="coursepress-page">
<?php
		$option_name = sprintf( 'coursepress_%s', basename( __FILE__, '.php' ) );
		$options = apply_filters( $option_name, $config );
		/**
		 * print options
		 */
foreach ( $options as $option_key => $option ) {
	$classes = 'box-inner-content';
	printf( '<div class="cp-box-content cp-box-%s">', esc_attr( $option_key ) );
	if ( ! empty( $option['title'] ) || ! empty( $option['description'] ) ) {
		echo '<div class="box-label-area">';
		if ( ! empty( $option['title'] ) ) {
			printf(
				'<h3 class="label">%s</h3>',
				$option['title']
			);
		}
		if ( isset( $option['description'] ) ) {
			printf( '<p class="description">%s</p>', $option['description'] );
		}
		echo '</div>';
	} else {
		$classes .= ' box-inner-full';
	}
	printf( '<div class="%s">', esc_attr( $classes ) );
	/**
	 * flex wrapper: semaphore
	 */
	$is_flex = false;
	foreach ( $option['fields'] as $key => $data ) {
		/**
				 * flex wrapper: open & close
				 */
		if ( isset( $data['flex'] ) && true === $data['flex'] ) {
			if ( ! $is_flex ) {
				echo '<div class="flex">';
			}
			$is_flex = true;
		} else if ( true === $is_flex ) {
			echo '</div>';
			$is_flex = false;
		}
		$class = isset( $data['wrapper_class'] )? $data['wrapper_class']:'';
		printf(
			'<div class="cp-box option option-%s option-%s %s">',
			esc_attr( sanitize_title( $key ) ),
			esc_attr( $data['type'] ),
			esc_attr( $class )
		);
		if ( isset( $data['label'] ) ) {
			printf( '<label class="label">%s</label>', $data['label'] );
		}
		$data['name'] = $key;
		lib3()->html->element( $data );
		echo '</div>';
	}
	/**
			 * flex wrapper: close
			 */
	if ( $is_flex ) {
		echo '</div>';
	}
	echo '</div>';
	echo '</div>';
}
?>
        <div class="cp-box-content">
            <div class="box-inner-content course-footer">
                <button type="submit" class="cp-btn cp-btn-active"><?php esc_html_e( 'Save', 'cp' ); ?></button>
            </div>
        </div>

    </div>
</div>
<?php wp_nonce_field( 'coursepress-update-forum-'.$forum_id ); ?>
<input type="hidden" name="forum_id" value="<?php echo esc_attr( $forum_id ); ?>" />
<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
</form>
