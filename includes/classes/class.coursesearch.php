<?php

if ( !defined('ABSPATH') )
    exit; // Exit if accessed directly

if ( !class_exists('Course_Search') ) {

    class Course_Search {

        var $courses_per_page = 10;
        var $args = array();
        var $is_light = false;
        var $post_type = 'course';

        function __construct( $search_term = '', $page_num = '' ) {
            if ( $this->is_light ) {
                $page_num = 1;
                $this->courses_per_page = 2;
            }
            $this->search_term = $search_term;
            $this->raw_page = ( '' == $page_num ) ? false : ( int ) $page_num;
            $this->page_num = ( int ) ( '' == $page_num ) ? 1 : $page_num;

            $args = array(
                //'s' => $this->search_term,
                'posts_per_page' => $this->courses_per_page,
                'offset' => ( $this->page_num - 1 ) * $this->courses_per_page,
                'orderby' => 'post_date',
                'order' => 'DESC',
                'post_type' => $this->post_type,
                'post_status' => 'any'
            );

            $this->args = $args;
        }

        function Course( $search_term = '', $page_num = '' ) {
            $this->__construct($search_term, $page_num);
        }

        function get_args() {
            return $this->args;
        }

        function get_results( $count = false ) {
            global $wpdb;
            $offset = ($this->page_num - 1 ) * $this->courses_per_page;
            if ( $this->search_term !== '' ) {
                $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = %s AND (post_title LIKE %s OR post_content LIKE %s) ORDER BY post_date DESC LIMIT %d OFFSET %d", $this->post_type, '%' . $this->search_term . '%', '%' . $this->search_term . '%', $this->courses_per_page, $offset), OBJECT);
                if ( $count ) {
                    return count($results);
                } else {
                    return $results;
                }
            } else {
                return get_posts($this->args);
            }
        }

        function get_count_of_all_courses() {
            $args = array(
                'posts_per_page' => -1,
                'category' => '',
                'orderby' => 'post_date',
                'order' => 'DESC',
                'post_type' => $this->post_type,
                'post_status' => 'any'
            );

            return count(get_posts($args, OBJECT));
        }

        function page_links() {
            $pagination = new CoursePress_Pagination();
            $pagination->Items($this->get_count_of_all_courses());
            $pagination->limit($this->courses_per_page);
            $pagination->parameterName = 'page_num';
            if ( $this->search_term != '' ) {
                $pagination->target("admin.php?page=courses&s=" . $this->search_term);
            } else {
                $pagination->target("admin.php?page=courses");
            }
            $pagination->currentPage($this->page_num);
            $pagination->nextIcon('&#9658;');
            $pagination->prevIcon('&#9668;');
            $pagination->items_title = __('courses', 'cp');
            $pagination->show();
        }

    }

}
?>
