<?php

class CoursePress_Widget_LatestCourse extends WP_Widget {

	public static function init() {
		add_action( 'widgets_init', array( 'CoursePress_Widget_LatestCourse', 'register' ) );
	}

	public static function register() {
		register_widget( 'CoursePress_Widget_LatestCourse' );
	}

	public function __construct() {
		$widget_ops = array(
			'classname' => 'cp_latest_courses_widget',
			'description' => __( 'Displays latest courses', 'coursepress' ),
		);

		parent::__construct( 'CP_Latest_Courses', __( 'Latest Courses', 'coursepress' ), $widget_ops );
	}

	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'limit' => '', 'button_title' => '' ) );

		$title = $instance['title'];
		$limit = $instance['limit'];
		$button_title = $instance['button_title'];
		?>

		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'coursepress' ); ?>:
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"/></label>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Number of Courses', 'coursepress' ); ?>:<br/>
			<select name="<?php echo $this->get_field_name( 'limit' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>">
			<?php
			for ( $i = 1; $i <= 30; $i ++ ) {
			?>
				<option value="<?php echo $i; ?>" <?php selected( $limit, $i, true ); ?>><?php echo $i; ?></option>
			<?php
			}
			?>
			</select>
		</label></p>

		<p><label for="<?php echo $this->get_field_id( 'button_title' ); ?>"><?php _e( 'Button Title', 'coursepress' ); ?>:
			<input class="widefat" id="<?php echo $this->get_field_id( 'button_title' ); ?>" name="<?php echo $this->get_field_name( 'button_title' ); ?>" type="text" value="<?php echo( ! isset( $button_title ) ? __( 'See All Courses', 'coursepress' ) : esc_attr( $button_title ) ); ?>"/></label>
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
		$instance['limit'] = $new_instance['limit'];

		return $instance;
	}

	public function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );

		echo $before_widget;

		$title = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		?>

		<?php
		// Course List
		echo do_shortcode( '[course_list class="cp_featured_widget_course_list" title_link="yes" limit="' . $instance['limit'] . '" order="DESC" show="" show_button="no" show_divider="no" title_tag="div"]' );
		?>

		<div class="cp_featured_widget_course_link">
			<a href="<?php echo esc_url( home_url( '/' ) . CoursePress_Core::get_slug( 'course' ) ); ?>"><?php echo esc_html( $instance['button_title'] ); ?></a>
		</div>

		<?php
		echo $after_widget;
	}
}
