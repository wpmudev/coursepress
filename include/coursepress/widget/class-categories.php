<?php

class CoursePress_Widget_Categories extends WP_Widget {
	public static function init() {
		add_action( 'widgets_init', array( 'CoursePress_Widget_Categories', 'register' ) );
	}

	public static function register() {
		register_widget( 'CoursePress_Widget_Categories' );
	}

	public function __construct() {
		$widget_ops = array(
			'classname' => 'cp_course_categories',
			'description' => __( 'A list or dropdown of course categories.', 'coursepress' ),
		);

		parent::__construct( 'CP_Widget_Categories', __( 'Course Categories', 'coursepress' ), $widget_ops );

	}

	public function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = esc_attr( $instance['title'] );
		$count = isset( $instance['count'] ) ? (bool) $instance['count'] : false;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'coursepress' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
		</p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>"<?php checked( $count ); ?> />
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Show course counts', 'coursepress' ); ?></label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['count'] = ! empty( $new_instance['count'] ) ? 1 : 0;

		return $instance;
	}

	public function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Course Categories', 'coursepress' ) : $instance['title'], $instance, $this->id_base );
		$show_course_count = isset( $instance['count'] ) ? true : false;

		echo $before_widget;

		if ( $title && ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		?>
		<ul>
		<?php
			$taxonomies = array( 'course_category' );

			$args = array(
				'orderby' => 'name',
				'order' => 'ASC',
				'hide_empty' => true,
				'fields' => 'all',
				'hierarchical' => true,
			);
			$terms = get_terms( $taxonomies, apply_filters( 'cp_course_categories_args', $args ) );

			foreach ( $terms as $term ) {
				$permalink = get_term_link( $term, 'course_category' );
				?>
				<li>
					<a href="<?php echo esc_url( $permalink ); ?>"><?php echo $term->name; ?></a>
					<?php echo $show_course_count ? '('. $term->count . ')' : ''; ?>
				</li>
			<?php
			}
			?>
		</ul>
		<?php
		echo $after_widget;
	}
}
