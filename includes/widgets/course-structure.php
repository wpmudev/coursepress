<?php

class CP_Course_Structure extends WP_Widget {

    function CP_Course_Structure() {
        $widget_ops = array( 'classname' => 'cp_course_strucutre_widget', 'description' => __('Displays a selected course structure', 'cp') );
        $this->WP_Widget('CP_Course_Structure', __('Course Structure', 'cp'), $widget_ops);
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
        $instance['title'] = $new_instance['title'];
        $instance['course'] = $new_instance['course'];
        return $instance;
    }

    function widget( $args, $instance ) {
        extract($args, EXTR_SKIP);

        echo $before_widget;

        $course_id = $instance['course'];
        $course = new Course($course_id);

        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

        if ( !empty($title) ) {
            echo $before_title . $title . $after_title;
        }

        $course->course_structure_front('Free');
        ?>

        <?php
        echo $after_widget;
    }

}

add_action('widgets_init', create_function('', 'return register_widget("CP_Course_Structure");'));
?>