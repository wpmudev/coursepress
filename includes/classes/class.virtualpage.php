<?php

if (!class_exists('CoursePress_Virtual_Page')) {

    class CoursePress_Virtual_Page {

        var $slug = null;
        var $title = null;
        var $content = null;
        var $author = null;
        var $date = null;
        var $type = null;

        function __construct($args) {
            if (!isset($args['slug']))
                throw new Exception('No slug given for the virtual page');

            $this->show_title = isset($args['show_title']) ? $args['show_title'] : true;
            $this->slug = $args['slug'];
            $this->title = isset($args['title']) ? $args['title'] : '';
            $this->content = isset($args['content']) ? $args['content'] : '';
            $this->author = isset($args['author']) ? $args['author'] : 1;
            $this->date = isset($args['date']) ? $args['date'] : current_time('mysql');
            $this->dategmt = isset($args['date']) ? $args['date'] : current_time('mysql', 1);
            $this->type = isset($args['type']) ? $args['type'] : 'page';
            //remove_action('template_redirect', 'redirect_canonical');
            add_filter('the_posts', array(&$this, 'virtualPage'));
            add_filter('the_title', array(&$this, 'hide_title'), 10, 2);
        }

        // filter to create virtual page content
        function virtualPage($posts) {
            global $wp, $wp_query, $wpdb;

            $old_post_slug_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s", $wp->request)); //check if slug already exists

            if ($old_post_slug_id == '') {

                $post = new stdClass;

                $post->ID = -1;
                $post->post_author = $this->author;
                $post->post_date = $this->date;
                $post->post_date_gmt = $this->dategmt;
                $post->post_content = $this->content;
                $post->post_title = $this->title;
                $post->post_excerpt = '';
                $post->post_status = 'publish';
                $post->comment_status = 'closed';
                $post->ping_status = 'closed';
                $post->post_password = '';
                $post->post_name = $this->slug;
                $post->to_ping = '';
                $post->pinged = '';
                $post->modified = $post->post_date;
                $post->modified_gmt = $post->post_date_gmt;
                $post->post_content_filtered = '';
                $post->post_parent = 0;
                $post->guid = get_home_url('/' . $this->slug);
                $post->menu_order = 0;
                $post->post_type = $this->type;
                $post->post_mime_type = '';
                $post->comment_count = -1;

                $posts = array($post);

                $wp_query->is_page = TRUE;
                $wp_query->is_singular = TRUE;
                $wp_query->is_home = FALSE;
                $wp_query->is_archive = FALSE;
                $wp_query->is_category = FALSE;
                unset($wp_query->query['error']);
                $wp_query->query_vars['error'] = '';
                $wp_query->is_404 = FALSE;
            } else {
                //Slug already exists
            }

            return ($posts);
        }

        function hide_title($title, $id) {
            if ($this->show_title) {
                return $title;
            } else {
                return '';
            }
        }

    }

}