<?php

class CP_Course_Structure extends WP_Widget {

    function __construct() {
        $widget_ops = array( 'classname' => 'cp_course_strucutre_widget', 'description' => __('Displays a selected course structure', 'cp') );
        parent::__construct('CP_Course_Structure', __('Course Structure', 'cp'), $widget_ops);
    }

    function form( $instance ) {
        $instance = wp_parse_args(( array ) $instance, array( 'title' => '' ));
        $title = $instance['title'];
        $selected_course = $instance['course'];

        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'course',
            'post_status' => 'publish'
        );

        $courses = get_posts($args);
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'cp'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>

        <p><label for="<?php echo $this->get_field_id('course'); ?>"><?php _e('Course', 'cp'); ?><br />
                <select name="<?php echo $this->get_field_name('course'); ?>" class="widefat" id="<?php echo $this->get_field_id('course'); ?>">
						<option value="false" <?php selected($selected_course, "false", true); ?>><?php _e( '- current -', 'cp' ); ?></option>
                    <?php
                    foreach ( $courses as $course ) {
                        ?>
                        <option value="<?php echo $course->ID; ?>" <?php selected($selected_course, $course->ID, true); ?>><?php echo $course->post_title; ?></option>
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
	        $instance['title'] = $new_instance['title'];
		} else {
	        $instance['title'] = strip_tags( $new_instance['title'] );
		}
        $instance['course'] = $new_instance['course'];
        return $instance;
    }

    function widget( $args, $instance ) {
		global $post;
        extract($args, EXTR_SKIP);

		$course_id = false;
		if( 'false' == $instance['course'] ){
			if ( $post && 'course' == $post->post_type ) {
				$course_id = $post->ID;
			} else {
				$parent_id = do_shortcode('[get_parent_course_id]');
				$course_id = 0 != $parent_id ? $parent_id : $course_id;
			}
		} else {
			$course_id = $instance['course'];
		}
        
		if ( ( $post && ( 'course' == $post->post_type || 'unit' == $post->post_type ) && ! is_post_type_archive( 'course' ) ) || 'false' != $instance['course'] ){

	        echo $before_widget;
			
	        $course = new Course($course_id);

	        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

	        if ( !empty($title) ) {
	            echo $before_title . $title . $after_title;
	        }
			
			echo '<div class="course_structure_widget">';
	        // $course->course_structure_front( __('Free', 'cp') );
			echo do_shortcode('[course_structure course_id="' . $course_id . '" label="" show_title="no" show_divider="no"]');
			// Strange bug.
			echo '</div>&nbsp;';
			
	        echo $after_widget;
		}
    }

}

add_action('widgets_init', create_function('', 'return register_widget("CP_Course_Structure");'));
?>