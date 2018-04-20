<?php

class CoursePress_Widget_FeaturedCourse extends WP_Widget {

	public static function init() {
		add_action( 'widgets_init', array( 'CoursePress_Widget_FeaturedCourse', 'register' ) );
		add_action( 'wp_footer', array( 'CoursePress_Widget_FeaturedCourse', 'enqueue_featured_script' ) );
	}

	public static function register() {
		register_widget( 'CoursePress_Widget_FeaturedCourse' );
	}

	public function __construct() {
		$widget_ops = array(
			'classname' => 'cp_featured_widget',
			'description' => __( 'Displays a selected course as featured', 'coursepress' ),
		);

		parent::__construct( 'CP_Featured_Course', __( 'Featured Course', 'coursepress' ), $widget_ops );
	}
	
	public static function enqueue_featured_script() {

		$featured_js = CoursePress::$url . 'asset/js/coursepress-featured.js';
		wp_enqueue_script( 'coursepress-featured', $featured_js, array(
			'jquery'
		), CoursePress::$version );
	}

	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title' => '',
				'button_title' => '',
				'course' => '',
				'type' => '',
				'priority' => '',
			)
		);
		$title = $instance['title'];
		$button_title = $instance['button_title'];
		$selected_course = $instance['course'];
		$selected_type = $instance['type'];
		$selected_priority = $instance['priority'];

		$args = array(
			'posts_per_page' => - 1,
			'post_type' => 'course',
			'post_status' => 'publish',
		);

		$courses = get_posts( $args );
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'coursepress' ); ?>:
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"/></label>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'course' ); ?>"><?php _e( 'Course', 'coursepress' ); ?><br/>
			<select name="<?php echo $this->get_field_name( 'course' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'course' ); ?>">
				<?php
				foreach ( $courses as $course ) {
				?>
					<option value="<?php echo $course->ID; ?>" <?php selected( $selected_course, $course->ID, true ); ?>><?php echo $course->post_title; ?></option>
				<?php
				}
				?>
			</select>
		</label></p>

		<p><label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e( 'Featured Media', 'coursepress' ); ?><br/>
			<select name="<?php echo $this->get_field_name( 'type' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'type' ); ?>">
				<option value="default" <?php selected( $selected_type, 'default', true ); ?>><?php _e( 'Priority Mode (default)', 'coursepress' ); ?></option>
				<option value="video" <?php selected( $selected_type, 'video', true ); ?>><?php _e( 'Featured Video', 'coursepress' ); ?></option>
				<option value="image" <?php selected( $selected_type, 'image', true ); ?>><?php _e( 'List Image', 'coursepress' ); ?></option>
			</select>
		</label></p>

		<p><label for="<?php echo $this->get_field_id( 'priority' ); ?>"><?php _e( 'Priority Media', 'coursepress' ); ?><br/>
			<small><?php _e( 'Priority needs to be set above.', 'coursepress' ); ?></small>
			<select name="<?php echo $this->get_field_name( 'priority' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'priority' ); ?>">
				<option value="video" <?php selected( $selected_priority, 'video', true ); ?>><?php _e( 'Featured Video (image fallback)', 'coursepress' ); ?></option>
				<option value="image" <?php selected( $selected_priority, 'image', true ); ?>><?php _e( 'List Image (video fallback)', 'coursepress' ); ?></option>
			</select>
		</label></p>

		<p><label for="<?php echo $this->get_field_id( 'button_title' ); ?>"><?php _e( 'Button Title', 'coursepress' ); ?>:
			<input class="widefat" id="<?php echo $this->get_field_id( 'button_title' ); ?>" name="<?php echo $this->get_field_name( 'button_title' ); ?>" type="text" value="<?php echo( ! isset( $button_title ) ? __( 'Find out more', 'coursepress' ) : esc_attr( $button_title ) ); ?>"/></label>
		</p>
	<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Admin on single sites, Super admin on network
		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['title'] = $new_instance['title'];
			$instance['button_title'] = $new_instance['button_title'];
		} else {
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['button_title'] = strip_tags( $new_instance['button_title'] );
		}
		$instance['course'] = $new_instance['course'];
		$instance['type'] = $new_instance['type'];
		$instance['priority'] = $new_instance['priority'];

		return $instance;
	}

	public function widget( $args, $instance ) {
		global $wp_query;

		/**
		 * Set the course variable so that the needed js will be included.
		 **/
		$wp_query->query['course'] = true;

		extract( $args, EXTR_SKIP );

		echo $before_widget;

		$course_id = $instance['course'];

		$selected_type = isset( $instance['type'] ) ? $instance['type'] : 'image';
		$selected_priority = isset( $instance['priority'] ) ? $instance['priority'] : 'image';
		$button_label = isset( $instance['button_title'] ) && ! empty( $instance['button_title'] ) ? $instance['button_title'] : __( 'Details', 'coursepress' );

		$title = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		?>
		<div class="fcp_featured_widget cp_featured_widget-course-<?php echo $course_id; ?>">
			<?php echo do_shortcode( '[course_title course_id="'. $course_id . '"]' ); ?>
			<?php
			echo do_shortcode( '[course_media type="' . $selected_type . '" priority="' . $selected_priority . '" course_id="' . $course_id . '"]' );
			?>
			<div class="cp_featured_widget_course_summary">
				<?php echo do_shortcode( '[course_summary course_id="' . $course_id . '" length="30"]' ); ?>
			</div>

			<div class="cp_featured_widget_course_link">
				<button class="apply-button apply-button-details" data-link="<?php echo esc_url( get_permalink( $course_id ) ); ?>"><?php echo esc_html( $button_label ); ?></button>
			</div>
		</div>
		<?php
		echo $after_widget;
	}
}
