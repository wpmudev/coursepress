<?php

class CP_Featured_Course extends WP_Widget {

    function CP_Featured_Course() {
        $widget_ops = array( 'classname' => 'cp_featured_widget', 'description' => __('Displays a selected course as featured', 'cp') );
        $this->WP_Widget('CP_Featured_Course', __('Featured Course', 'cp'), $widget_ops);
    }

    function form( $instance ) {
        $instance = wp_parse_args(( array ) $instance, array( 'title' => '' ));
        $title = $instance['title'];
        $button_title = $instance['button_title'];
        $selected_course = $instance['course'];
		$selected_type = $instance['type'];
		$selected_priority = $instance['priority'];

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
		
        <p><label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Featured Media', 'cp'); ?><br />
                <select name="<?php echo $this->get_field_name('type'); ?>" class="widefat" id="<?php echo $this->get_field_id('type'); ?>">
					<option value="default" <?php selected($selected_type, 'default', true); ?>><?php _e( 'Priority Mode (default)', 'cp' ); ?></option>
					<option value="video" <?php selected($selected_type, 'video', true); ?>><?php _e( 'Featured Video', 'cp' ); ?></option>
					<option value="image" <?php selected($selected_type, 'image', true); ?>><?php _e( 'List Image', 'cp' ); ?></option>					
					<!-- <option value="thumbnail" <?php // selected($selected_type, 'thumbnail', true); ?>><?php // _e( 'Thumbnail', 'cp' ); ?></option> -->
                </select>
            </label>
        </p>

        <p><label for="<?php echo $this->get_field_id('priority'); ?>"><?php _e('Priority Media', 'cp'); ?><br /><small><?php _e( 'Priority needs to be set above.', 'cp' ); ?></small>
                <select name="<?php echo $this->get_field_name('priority'); ?>" class="widefat" id="<?php echo $this->get_field_id('priority'); ?>">
					<option value="video" <?php selected($selected_priority, 'video', true); ?>><?php _e( 'Featured Video (image fallback)', 'cp' ); ?></option>
					<option value="image" <?php selected($selected_priority, 'image', true); ?>><?php _e( 'List Image (video fallback)', 'cp' ); ?></option>					
                </select>
            </label>
        </p>
		

        <p><label for="<?php echo $this->get_field_id('button_title'); ?>"><?php _e('Button Title', 'cp'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('button_title'); ?>" name="<?php echo $this->get_field_name('button_title'); ?>" type="text" value="<?php echo (!isset($button_title) ? __('Find out more') : esc_attr($button_title)); ?>" /></label></p>
        <?php
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['button_title'] = $new_instance['button_title'];
        $instance['course'] = $new_instance['course'];
		$instance['type'] = $new_instance['type']; 
		$instance['priority'] = $new_instance['priority'];
		
        return $instance;
    }

    function widget( $args, $instance ) {
        extract($args, EXTR_SKIP);

        echo $before_widget;

        $course_id = $instance['course'];
        $course = new Course($course_id);
		$selected_type = $instance['type'];
		$selected_priority = $instance['priority'];

        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

        if ( !empty($title) ) {
            echo $before_title . $title . $after_title;
        }
        ?>
		<div class=fcp_featured_widget cp_featured_widget-course-<?php echo $course_id; ?>">
	        <div class="cp_featured_widget_title"><?php echo $course->details->post_title; ?></div>
	        <?php
	        echo do_shortcode('[course_media type="' . $selected_type . '" priority="' . $selected_priority . '" course_id="' . $course_id . '"]');
	        ?>
	        <div class="cp_featured_widget_course_summary">
	        <?php echo do_shortcode('[course_summary course_id="' . $course_id . '" length="30"]'); ?>
	        </div>

	        <div class="cp_featured_widget_course_link">
	            <a href="<?php echo $course->get_permalink($course_id) ?>"><?php echo $instance['button_title']; ?></a>
	        </div>
		</div>
        <?php
        echo $after_widget;
    }

}

add_action('widgets_init', create_function('', 'return register_widget("CP_Featured_Course");'));
?>