<?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

if ( !class_exists( 'Course_Search' ) ) {

    class Course_Search {

        var $courses_per_page = 10;
        var $args = array( );

        function __construct( $search_term = '', $page_num = '' ) {
            $this->search_term = $search_term;
            $this->raw_page = ( '' == $page_num ) ? false : ( int ) $page_num;
            $this->page_num = ( int ) ( '' == $page_num ) ? 1 : $page_num;

            $args = array(
                's' => $this->search_term,
                'posts_per_page' => $this->courses_per_page,
                'offset' => ( $this->page_num - 1 ) * $this->courses_per_page,
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

        function Course( $search_term = '', $page_num = '' ) {
            $this->__construct( $search_term, $page_num );
        }

        function get_args( ) {
            return $this->args;
        }

        function get_results( ) {
            return get_posts( $this->args );
        }

        function get_count_of_all_courses( ) {
             $args = array(
                's' => $this->search_term,
                'posts_per_page' => -1,
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
             return count( get_posts( $args ) );
            //return wp_count_posts( 'course' )->publish + wp_count_posts( 'course' )->private;
        }

        function page_links( ) {
            $pagination = new CoursePress_Pagination( );
            $pagination->Items( $this->get_count_of_all_courses( ) );
            $pagination->limit( $this->courses_per_page );
            $pagination->parameterName = 'page_num';
            if ( $this->search_term != '' ) {
                $pagination->target( "admin.php?page=courses&s=".$this->search_term );
            } else {
                $pagination->target( "admin.php?page=courses" );
            }
            $pagination->currentPage( $this->page_num );
            $pagination->nextIcon( '&#9658;' );
            $pagination->prevIcon( '&#9668;' );
            $pagination->items_title = __( 'courses', 'cp' );
            $pagination->show( );
        }

    }

}
?>
