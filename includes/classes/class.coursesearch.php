<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Course_Search')) {

    class Course_Search {

        var $courses_per_page = 25;
        var $args = array();

        function __construct($search_term = '', $page = '') {
            $this->search_term = $search_term;
            $this->raw_page = ( '' == $page ) ? false : (int) $page;
            $this->page = (int) ( '' == $page ) ? 1 : $page;

            $args = array(
                's' => $this->search_term,
                'posts_per_page' => $this->courses_per_page,
                'offset' => ( $this->page - 1 ) * $this->courses_per_page,
                'category' => '',
                'orderby' => 'post_date',
                'order' => 'DESC',
                'include' => '',
                'exclude' => '',
                'meta_key' => '',
                'meta_value' => '',
                'post_type' => 'course',
                'post_mime_type' => '',
                'post_parent' => '',
                'post_status' => 'any'
                );
           

            $this->args = $args;
        }

        function Course($search_term = '', $page = '') {
            $this->__construct($search_term, $page);
        }
        
        function get_args(){
            return $this->args;
        }
        
        function get_results(){
            return get_posts($this->args);
        }
        
        function page_links(){
            echo 'Pagination links goes here...';
        }

    }

}
?>
