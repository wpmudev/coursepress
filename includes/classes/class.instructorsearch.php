<?php

if (!class_exists('Instructor_Search')) {

    class Instructor_Search extends WP_User_Query {

        var $users_per_page = 25;
        var $search_errors = false;

        function __construct($search_term = '', $page = '') {

            $this->search_term = $search_term;
            $this->raw_page = ( '' == $page ) ? false : (int) $page;
            $this->page = (int) ( '' == $page ) ? 1 : $page;

            $args = array('search' => $this->search_term,
                'number' => $this->users_per_page,
                'offset' => ( $this->page - 1 ) * $this->users_per_page,
                'fields' => 'all'
            );

            $this->query_vars = wp_parse_args($args, array(
                'blog_id' => $GLOBALS['blog_id'],
                'role' => 'instructor',
                'meta_key' => '',
                'meta_value' => '',
                'meta_compare' => '',
                'include' => array(),
                'exclude' => array(),
                'search' => '',
                'search_columns' => array(),
                'orderby' => 'login',
                'order' => 'ASC',
                'offset' => '',
                'number' => '',
                'count_total' => true,
                'fields' => 'all',
                'who' => ''
                    ));

            $this->prepare_query();
            $this->query();
            $this->do_paging();
        }

        function Instructor_Search($search_term = '', $page = '') {
            $this->__construct($search_term, $page);
        }

        function do_paging() {

            $this->total_users_for_query = $this->get_total();

            if ($this->total_users_for_query > $this->users_per_page) { // pagination required
                $args = array();
                if (!empty($this->search_term)) {
                    $args['s'] = urlencode($this->search_term);
                }

                if (!empty($this->role)) {
                    $args['role'] = urlencode($this->role);
                }

                $this->paging_text = paginate_links(array(
                    'total' => ceil($this->total_users_for_query / $this->users_per_page),
                    'current' => $this->page,
                    'base' => 'admin.php?page=students&%_%',
                    'format' => 'userspage=%#%',
                    'add_args' => $args
                        ));
                if ($this->paging_text) {
                    $this->paging_text = sprintf('<span class="displaying-num">' . __('Displaying %s&#8211;%s of %s', 'cp') . '</span>%s', number_format_i18n(( $this->page - 1 ) * $this->users_per_page + 1), number_format_i18n(min($this->page * $this->users_per_page, $this->total_users_for_query)), number_format_i18n($this->total_users_for_query), $this->paging_text
                    );
                }
            }
        }

        function page_links(){
            echo 'pagination goes here';
        }
       
        function prepare_query() {
            global $wpdb;

            $qv = & $this->query_vars;

            if (is_array($qv['fields'])) {
                $qv['fields'] = array_unique($qv['fields']);

                $this->query_fields = array();
                foreach ($qv['fields'] as $field)
                    $this->query_fields[] = $wpdb->users . '.' . esc_sql($field);
                $this->query_fields = implode(',', $this->query_fields);
            } elseif ('all' == $qv['fields']) {
                $this->query_fields = "$wpdb->users.*";
            } else {
                $this->query_fields = "$wpdb->users.ID";
            }

            if ($qv['count_total'])
                $this->query_fields = 'SQL_CALC_FOUND_ROWS ' . $this->query_fields;

            $this->query_from = "FROM $wpdb->users";
            $this->query_where = "WHERE 1=1";

            // sorting
            if (in_array($qv['orderby'], array('nicename', 'email', 'url', 'registered'))) {
                $orderby = 'user_' . $qv['orderby'];
            } elseif (in_array($qv['orderby'], array('user_nicename', 'user_email', 'user_url', 'user_registered'))) {
                $orderby = $qv['orderby'];
            } elseif ('name' == $qv['orderby'] || 'display_name' == $qv['orderby']) {
                $orderby = 'display_name';
            } elseif ('post_count' == $qv['orderby']) {

                $where = get_posts_by_author_sql('post');
                $this->query_from .= " LEFT OUTER JOIN (
					SELECT post_author, COUNT(*) as post_count
					FROM $wpdb->posts
					$where
					GROUP BY post_author
				) p ON ({$wpdb->users}.ID = p.post_author)
				";
                $orderby = 'post_count';
            } elseif ('ID' == $qv['orderby'] || 'id' == $qv['orderby']) {
                $orderby = 'ID';
            } else {
                $orderby = 'user_login';
            }

            $qv['order'] = strtoupper($qv['order']);
            if ('ASC' == $qv['order'])
                $order = 'ASC';
            else
                $order = 'DESC';
            $this->query_orderby = "ORDER BY $orderby $order";

            // limit
            if ($qv['number']) {
                if ($qv['offset'])
                    $this->query_limit = $wpdb->prepare("LIMIT %d, %d", $qv['offset'], $qv['number']);
                else
                    $this->query_limit = $wpdb->prepare("LIMIT %d", $qv['number']);
            }

            $search = trim($qv['search']);
            if ($search) {
                $leading_wild = ( ltrim($search, '*') != $search );
                $trailing_wild = ( rtrim($search, '*') != $search );
                if ($leading_wild && $trailing_wild)
                    $wild = 'both';
                elseif ($leading_wild)
                    $wild = 'leading';
                elseif ($trailing_wild)
                    $wild = 'trailing';
                else
                    $wild = false;
                if ($wild)
                    $search = trim($search, '*');

                $search_columns = array();
                if ($qv['search_columns'])
                    $search_columns = array_intersect($qv['search_columns'], array('ID', 'user_login', 'user_email', 'user_url', 'user_nicename'));
                if (!$search_columns) {
                    if (false !== strpos($search, '@'))
                        $search_columns = array('user_email');
                    elseif (is_numeric($search))
                        $search_columns = array('user_login', 'ID');
                    elseif (preg_match('|^https?://|', $search) && !wp_is_large_network('users'))
                        $search_columns = array('user_url');
                    else
                        $search_columns = array('user_login', 'user_nicename');
                }

                $this->query_where .= $this->get_search_sql($search, $search_columns, $wild);
            }

            $blog_id = absint($qv['blog_id']);

            if ('authors' == $qv['who'] && $blog_id) {
                $qv['meta_key'] = $wpdb->get_blog_prefix($blog_id) . 'user_level';
                $qv['meta_value'] = 0;
                $qv['meta_compare'] = '!=';
                $qv['blog_id'] = $blog_id = 0; // Prevent extra meta query
            }

            $role = trim($qv['role']);

            if ($blog_id && ( $role || is_multisite() )) {
                $cap_meta_query = array();
                $cap_meta_query['key'] = $wpdb->get_blog_prefix($blog_id) . 'capabilities';

                if ($role) {
                    $cap_meta_query['value'] = '"' . $role . '"';
                    $cap_meta_query['compare'] = 'like';
                }

                $qv['meta_query'][] = $cap_meta_query;
            }

            $meta_query = new WP_Meta_Query();
            $meta_query->parse_query_vars($qv);

            if (!empty($meta_query->queries)) {
                $clauses = $meta_query->get_sql('user', $wpdb->users, 'ID', $this);
                $this->query_from .= $clauses['join'];
                $this->query_where .= $clauses['where'];

                if ('OR' == $meta_query->relation)
                    $this->query_fields = 'DISTINCT ' . $this->query_fields;
            }

            if (!empty($qv['include'])) {
                $ids = implode(',', wp_parse_id_list($qv['include']));
                $this->query_where .= " AND $wpdb->users.ID IN ($ids)";
            } elseif (!empty($qv['exclude'])) {
                $ids = implode(',', wp_parse_id_list($qv['exclude']));
                $this->query_where .= " AND $wpdb->users.ID NOT IN ($ids)";
            }

            do_action_ref_array('pre_user_query', array(&$this));
        }

    }

}
?>