<?php

class CP_Course_Calendar extends WP_Widget {

	private $date_indicator = array();

	function __construct() {
		$widget_ops = array( 'classname'   => 'cp_course_calendar_widget',
		                     'description' => __( 'Displays the course calendar.', 'cp' )
		);

		parent::__construct( 'CP_Course_Calendar', __( 'Course Calendar', 'cp' ), $widget_ops );

		$this->date_indicator = array(
			'indicator_light_block' => __( 'Light theme - Block', 'cp' ),
			'indicator_light_line'  => __( 'Light theme - Line', 'cp' ),
			'indicator_dark_block'  => __( 'Dark theme - Block', 'cp' ),
			'indicator_dark_line'   => __( 'Dark theme - Line', 'cp' ),
			'indicator_none'        => __( 'Theme/Custom CSS', 'cp' ),
		);
	}

	function form( $instance ) {
		$instance           = wp_parse_args( ( array ) $instance, array( 'title' => '' ) );
		$title              = $instance['title'];
		$pre_text           = ! empty( $instance['pre_text'] ) ? $instance['pre_text'] : null;
		$next_text          = ! empty( $instance['next_text'] ) ? $instance['next_text'] : null;
		$selected_course    = ! empty( $instance['course'] ) ? $instance['course'] : "false";
		$selected_indicator = ! empty( $instance['indicator'] ) ? $instance['indicator'] : "indicator_light_block";

		$args = array(
			'posts_per_page' => - 1,
			'post_type'      => 'course',
			'post_status'    => 'publish'
		);

		$courses = get_posts( $args );
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'cp' ); ?>:
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"/></label>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'course' ); ?>"><?php _e( 'Course', 'cp' ); ?><br/>
				<select name="<?php echo $this->get_field_name( 'course' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'course' ); ?>">
					<option value="false" <?php selected( $selected_course, "false", true ); ?>><?php _e( '- current -', 'cp' ); ?></option>
					<?php
					foreach ( $courses as $course ) {
						?>
						<option value="<?php echo $course->ID; ?>" <?php selected( $selected_course, $course->ID, true ); ?>><?php echo $course->post_title; ?></option>
					<?php
					}
					?>
				</select>
			</label>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'pre_text' ); ?>"><?php _e( 'Previous Month Text:', 'cp' ); ?>:
				<input class="widefat" id="<?php echo $this->get_field_id( 'pre_text' ); ?>" name="<?php echo $this->get_field_name( 'pre_text' ); ?>" type="text" value="<?php echo( ! isset( $pre_text ) ? __( '« Previous', 'cp' ) : esc_attr( $pre_text ) ); ?>"/></label>
		</p>
		<p><label for="<?php echo $this->get_field_id( 'next_text' ); ?>"><?php _e( 'Next Month Text:', 'cp' ); ?>:
				<input class="widefat" id="<?php echo $this->get_field_id( 'next_text' ); ?>" name="<?php echo $this->get_field_name( 'next_text' ); ?>" type="text" value="<?php echo( ! isset( $next_text ) ? __( 'Next »', 'cp' ) : esc_attr( $next_text ) ); ?>"/></label>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'indicator' ); ?>"><?php _e( 'Dates indicators', 'cp' ); ?><br/>
				<select name="<?php echo $this->get_field_name( 'indicator' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'indicator' ); ?>">
					<?php
					foreach ( $this->date_indicator as $key => $value ) {
						?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected_indicator, $key, true ); ?>><?php echo $value; ?></option>
					<?php
					}
					?>
				</select>
			</label>
		</p>

	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		// Admin on single sites, Super admin on network
		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['title']     = $new_instance['title'];
			$instance['pre_text']  = $new_instance['pre_text'];
			$instance['next_text'] = $new_instance['next_text'];
		} else {
			$instance['title']     = strip_tags( $new_instance['title'] );
			$instance['pre_text']  = strip_tags( $new_instance['pre_text'] );
			$instance['next_text'] = strip_tags( $new_instance['next_text'] );
		}

		$instance['indicator'] = $new_instance['indicator'];
		$instance['course']    = $new_instance['course'];

		return $instance;
	}

	function widget( $args, $instance ) {
		global $post;
		extract( $args, EXTR_SKIP );
		
		$course_id = $instance['course'];

		if ( ( $post && ( 'course' == $post->post_type || 'unit' == $post->post_type ) && ! is_post_type_archive( 'course' ) ) || 'false' != $instance['course'] ) {

			echo $before_widget;

			$title = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );

			if ( ! empty( $title ) ) {
				echo $before_title . $title . $after_title;
			}

			if ( $course_id && "false" != $course_id ) {
				echo do_shortcode( '[course_calendar course_id="' . $course_id . '" date_indicator="' . $instance['indicator'] . '" pre="' . $instance['pre_text'] . '" next="' . $instance['next_text'] . '"]' );
			} else {
				echo do_shortcode( '[course_calendar date_indicator="' . $instance['indicator'] . '" pre="' . $instance['pre_text'] . '" next="' . $instance['next_text'] . '"]' );
			}

			echo $after_widget;
		}
	}

}

add_action( 'widgets_init', create_function( '', 'return register_widget("CP_Course_Calendar");' ) );
?>