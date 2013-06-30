<?php

/*
  Plugin Name: CoursePress
  Plugin URI: http://premium.wpmudev.org/project/coursepress/
  Description: Create courses, write lessons, and add quizzes...
  Author: Marko Miljus (Incsub)
  Author URI: http://premium.wpmudev.org
  Version: 0.3
  TextDomain: cp
  Domain Path: /languages/
  WDP ID: XXX
  License: GNU General Public License (Version 2 - GPLv2)

  Copyright 2007-2013 Incsub (http://incsub.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
  the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('CoursePress')) {

    class CoursePress {

        var $version = '0.2';
        var $name = 'CoursePress';
        var $dir_name = 'coursepress';
        var $location = '';
        var $plugin_dir = '';
        var $plugin_url = '';

        function CoursePress() {
            $this->__construct();
        }

        function __construct() {

            //setup our variables
            $this->init_vars();

            // Load the common functions
            require_once('includes/functions.php');

            //install plugin
            register_activation_hook(__FILE__, array($this, 'install'));

            //Administration area
            if (is_admin()) {

                // Administration main class
                require_once( $this->plugin_dir . 'includes/classes/class.administration.php' );

                // Support for WPMU DEV Dashboard plugin
                include_once( $this->plugin_dir . 'includes/external/wpmudev-dash-notification.php' );

                // Course class
                require_once( $this->plugin_dir . 'includes/classes/class.course.php' );

                // Course search
                require_once( $this->plugin_dir . 'includes/classes/class.coursesearch.php' );

                // Contextual help
                require_once( $this->plugin_dir . 'includes/classes/class.help.php' );

                // Student class
                require_once( $this->plugin_dir . 'includes/classes/class.student.php' );

                // Search Students class
                require_once( $this->plugin_dir . 'includes/classes/class.studentsearch.php' );

                // Instructor class
                require_once( $this->plugin_dir . 'includes/classes/class.instructor.php' );

                // Search Instructor class
                require_once( $this->plugin_dir . 'includes/classes/class.instructorsearch.php' );
            }

            //Localize the plugin
            add_action('plugins_loaded', array(&$this, 'localization'), 9);

            //Register custom post types
            add_action('init', array(&$this, 'register_custom_posts'), 0);

            //Add plugin admin menu - Network
            add_action('network_admin_menu', array(&$this, 'add_admin_menu_network'));

            //Add plugin admin menu
            add_action('admin_menu', array(&$this, 'add_admin_menu'));

            //Custom header actions

            add_action('admin_enqueue_scripts', array(&$this, 'admin_header_actions'));
            add_action('load-coursepress_page_course_details', array(&$this, 'admin_coursepress_page_course_details'));
            add_action('load-toplevel_page_courses', array(&$this, 'admin_coursepress_page_courses'));

            // Load payment gateways
            $this->load_payment_gateways();

            //Load add-ons
            $this->load_addons();

            //update install script if necessary
            if (get_option('coursepress_version') != $this->version) {
                $this->install();
            }

            //add_action('admin_notices', array(&$this, 'dev_check_current_screen'));
        }

        function dev_check_current_screen() {
            if (!is_admin())
                return;

            global $current_screen;

            print_r($current_screen);
        }

        function install() {
            include_once( 'includes/install.php' );
            update_option('coursepress_version', $this->version);
            $this->add_user_roles_and_caps(); //This setting is saved to the database (in table wp_options, field wp_user_roles), so it might be better to run this on theme/plugin activation
        }

        function localization() {
            // Load up the localization file if we're using WordPress in a different language

            if ($this->location == 'mu-plugins') {
                load_muplugin_textdomain('cp', '/languages/');
            } else if ($this->location == 'subfolder-plugins') {
                load_plugin_textdomain('cp', false, '/' . $this->plugin_dir . '/languages/');
            } else if ($this->location == 'plugins') {
                load_plugin_textdomain('cp', false, '/languages/');
            }
        }

        function init_vars() {
            //setup proper directories
            if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . $this->dir_name . '/' . basename(__FILE__))) {
                $this->location = 'subfolder-plugins';
                $this->plugin_dir = WP_PLUGIN_DIR . '/' . $this->dir_name . '/';
                $this->plugin_url = plugins_url('/', __FILE__);
            } else if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . basename(__FILE__))) {
                $this->location = 'plugins';
                $this->plugin_dir = WP_PLUGIN_DIR . '/';
                $this->plugin_url = plugins_url('/', __FILE__);
            } else if (is_multisite() && defined('WPMU_PLUGIN_URL') && defined('WPMU_PLUGIN_DIR') && file_exists(WPMU_PLUGIN_DIR . '/' . basename(__FILE__))) {
                $this->location = 'mu-plugins';
                $this->plugin_dir = WPMU_PLUGIN_DIR;
                $this->plugin_url = WPMU_PLUGIN_URL;
            } else {
                wp_die(sprintf(__('There was an issue determining where %s is installed. Please reinstall it.', 'cp'), $this->name));
            }
        }

        //Load payment gateways
        function load_payment_gateways() {
            if (is_dir($this->plugin_dir . 'includes/gateways')) {
                if ($dh = opendir($this->plugin_dir . 'includes/gateways')) {
                    $mem_gateways = array();
                    while (( $gateway = readdir($dh) ) !== false)
                        if (substr($gateway, -4) == '.php')
                            $mem_gateways[] = $gateway;
                    closedir($dh);
                    sort($mem_gateways);

                    foreach ($mem_gateways as $mem_gateway)
                        include_once( $this->plugin_dir . 'includes/gateways/' . $mem_gateway );
                }
            }

            do_action('coursepress_gateways_loaded');
        }

        //Load plugin add-ons
        function load_addons() {
            if (is_dir($this->plugin_dir . 'includes/add-ons')) {
                if ($dh = opendir($this->plugin_dir . 'includes/add-ons')) {
                    $mem_addons = array();
                    while (( $addon = readdir($dh) ) !== false)
                        if (substr($addon, -4) == '.php')
                            $mem_addons[] = $addon;
                    closedir($dh);
                    sort($mem_addons);

                    foreach ($mem_addons as $mem_addon)
                        include_once( $this->plugin_dir . 'includes/add-ons/' . $mem_addon );
                }
            }

            do_action('coursepress_addons_loaded');
        }

        function add_admin_menu_network() {
            
        }

        //Add plugin admin menu items
        function add_admin_menu() {

            // Add the menu page
            add_menu_page(__('CoursePress', 'cp'), __('CoursePress', 'cp'), 'coursepress_dashboard_cap', 'courses', array(&$this, 'coursepress_courses_admin'), $this->plugin_url . 'images/coursepress-icon.png');

            do_action('coursepress_add_menu_items_up');

            // Add the sub menu items
            add_submenu_page('courses', __('Courses', 'cp'), __('Courses', 'cp'), 'coursepress_courses_cap', 'courses', array(&$this, 'coursepress_courses_admin'));
            do_action('coursepress_add_menu_items_after_courses');

            add_submenu_page('courses', __('New Course', 'cp'), __('New Course', 'cp'), 'coursepress_courses_cap', 'course_details', array(&$this, 'coursepress_course_details_admin'));
            do_action('coursepress_add_menu_items_after_new_courses');

            add_submenu_page('courses', __('Instructors', 'cp'), __('Instructors', 'cp'), 'coursepress_instructors_cap', 'instructors', array(&$this, 'coursepress_instructors_admin'));
            do_action('coursepress_add_menu_items_after_instructors');

            add_submenu_page('courses', __('Students', 'cp'), __('Students', 'cp'), 'coursepress_students_cap', 'students', array(&$this, 'coursepress_students_admin'));
            do_action('coursepress_add_menu_items_after_instructors');

            add_submenu_page('courses', __('Reports', 'cp'), __('Reports', 'cp'), 'coursepress_reports_cap', 'reports', array(&$this, 'coursepress_reports_admin'));
            do_action('coursepress_add_menu_items_after_instructors');

            add_submenu_page('courses', __('Settings', 'cp'), __('Settings', 'cp'), 'coursepress_settings_cap', 'settings', array(&$this, 'coursepress_settings_admin'));
            do_action('coursepress_add_menu_items_after_settings');

            do_action('coursepress_add_menu_items_down');
        }

        function register_custom_posts() {

            //Register Courses post type
            $args = array(
                'labels' => array('name' => __('Courses', 'cp'),
                    'singular_name' => __('Course', 'cp'),
                    'add_new' => __('Create New', 'cp'),
                    'add_new_item' => __('Create New Course', 'cp'),
                    'edit_item' => __('Edit Course', 'cp'),
                    'edit' => __('Edit', 'cp'),
                    'new_item' => __('New Course', 'cp'),
                    'view_item' => __('View Course', 'cp'),
                    'search_items' => __('Search Courses', 'cp'),
                    'not_found' => __('No Courses Found', 'cp'),
                    'not_found_in_trash' => __('No Courses found in Trash', 'cp'),
                    'view' => __('View Course', 'cp')
                ),
                'public' => false,
                'show_ui' => true,
                'publicly_queryable' => true,
                'capability_type' => 'post',
                //Add later rewrite for customizable slugs!!!
                'query_var' => true
            );

            register_post_type('course', $args);

            //Register Units post type
            $args = array(
                'labels' => array('name' => __('Units', 'cp'),
                    'singular_name' => __('Unit', 'cp'),
                    'add_new' => __('Create New', 'cp'),
                    'add_new_item' => __('Create New Unit', 'cp'),
                    'edit_item' => __('Edit Unit', 'cp'),
                    'edit' => __('Edit', 'cp'),
                    'new_item' => __('New Unit', 'cp'),
                    'view_item' => __('View Unit', 'cp'),
                    'search_items' => __('Search Units', 'cp'),
                    'not_found' => __('No Units Found', 'cp'),
                    'not_found_in_trash' => __('No Units found in Trash', 'cp'),
                    'view' => __('View Unit', 'cp')
                ),
                'public' => true,
                'show_ui' => false,
                'publicly_queryable' => true,
                'capability_type' => 'post',
                //Add later rewrite for customizable slugs!!!
                'query_var' => true
            );

            register_post_type('unit', $args);
        }

        //Add new roles and user capabilities
        function add_user_roles_and_caps() {
            global $user;

            add_role('instructor', 'Instructor');

            $role = get_role('instructor');
            $role->add_cap('read');
            $role->add_cap('coursepress_dashboard_cap');
            $role->add_cap('coursepress_courses_cap');
            $role->add_cap('coursepress_instructors_cap');
            $role->add_cap('coursepress_students_cap');
            $role->add_cap('coursepress_reports_cap');
            $role->add_cap('coursepress_settings_cap');

            add_role('student', 'Student');

            $role = get_role('student');
            $role->add_cap('read');
        }

        //Functions for handling admin menu pages

        function coursepress_courses_admin() {
            include_once($this->plugin_dir . 'includes/admin-pages/courses.php');
        }

        function coursepress_course_details_admin() {
            include_once($this->plugin_dir . 'includes/admin-pages/courses-details.php');
        }

        function coursepress_instructors_admin() {
            include_once($this->plugin_dir . 'includes/admin-pages/instructors.php');
        }

        function coursepress_students_admin() {
            include_once($this->plugin_dir . 'includes/admin-pages/students.php');
        }

        function coursepress_reports_admin() {
            include_once($this->plugin_dir . 'includes/admin-pages/reports.php');
        }

        function coursepress_settings_admin() {
            include_once($this->plugin_dir . 'includes/admin-pages/settings.php');
        }

        /* Functions for handling tab pages */

        function show_courses_details_overview() {
            include_once($this->plugin_dir . 'includes/admin-pages/courses-details-overview.php');
        }

        function show_courses_details_units() {
            include_once($this->plugin_dir . 'includes/admin-pages/courses-details-units.php');
        }

        function show_courses_details_students() {
            include_once($this->plugin_dir . 'includes/admin-pages/courses-details-students.php');
        }

        /* Custom header actions */

        function admin_header_actions() {
            wp_enqueue_style('admin_general', $this->plugin_url . 'css/admin_general.css');
            
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-ui', 'http://code.jquery.com/ui/1.10.3/jquery-ui.js', array('jquery'), '1.10.3'); //need to change this to built-in 
            
            wp_enqueue_script('courses_bulk', $this->plugin_url . 'js/coursepress-admin.js', array('jquery', 'jquery-ui'), false, false);
            wp_localize_script('courses_bulk', 'coursepress', array(
                'delete_instructor_alert' => __('Please confirm that you want to remove the instructor from this course?', 'cp'),
                'delete_course_alert' => __('Please confirm that you want to permanently delete the course?', 'cp')
            ));
        }

        function admin_coursepress_page_course_details() {
            wp_enqueue_style('jquery-ui-admin', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css'); //need to change this to built-in
            wp_enqueue_style('admin_coursepress_page_course_details', $this->plugin_url . 'css/admin_coursepress_page_course_details.css');
            wp_enqueue_style('jquery-ui-admin', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css'); //need to change this to built-in
            
          
        }

        function admin_coursepress_page_courses() {
            wp_enqueue_style('courses', $this->plugin_url . 'css/admin_coursepress_page_courses.css');
        }

    }

}

global $coursepress;
$coursepress = new CoursePress();
?>
