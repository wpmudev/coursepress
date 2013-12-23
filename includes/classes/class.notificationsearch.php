<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Notification_Search')) {

    class Notification_Search {

        var $notifications_per_page = 10;
        var $args = array();

        function __construct($search_term = '', $page_num = '') {
            $this->search_term = $search_term;
            $this->raw_page = ( '' == $page_num ) ? false : (int) $page_num;
            $this->page_num = (int) ( '' == $page_num ) ? 1 : $page_num;

            $args = array(
                's' => $this->search_term,
                'posts_per_page' => $this->notifications_per_page,
                'offset' => ( $this->page_num - 1 ) * $this->notifications_per_page,
                'category' => '',
                'orderby' => 'post_date',
                'order' => 'DESC',
                'include' => '',
                'exclude' => '',
                'meta_key' => '',
                'meta_value' => '',
                'post_type' => 'notifications',
                'post_mime_type' => '',
                'post_parent' => '',
                'post_status' => 'any'
            );

            $this->args = $args;
        }

        function Course($search_term = '', $page_num = '') {
            $this->__construct($search_term, $page_num);
        }

        function get_args() {
            return $this->args;
        }

        function get_results() {
            return get_posts($this->args);
        }

        function get_count_of_all_notifications() {
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
                'post_type' => 'notifications',
                'post_mime_type' => '',
                'post_parent' => '',
                'post_status' => 'any'
            );
             return count(get_posts($args));
        }

        function page_links() {
            $pagination = new CoursePress_Pagination();
            $pagination->Items($this->get_count_of_all_notifications());
            $pagination->limit($this->notifications_per_page);
            $pagination->parameterName = 'page_num';
            if ($this->search_term != '') {
                $pagination->target("admin.php?page=notifications&s=".$this->search_term);
            } else {
                $pagination->target("admin.php?page=notifications");
            }
            $pagination->currentPage($this->page_num);
            $pagination->nextIcon('&#9658;');
            $pagination->prevIcon('&#9668;');
            $pagination->items_title = __('notifications', 'cp');
            $pagination->show();
        }

    }

}
?>
