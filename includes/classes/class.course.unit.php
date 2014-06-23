<?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

if ( !class_exists( 'Unit' ) ) {

    class Unit {

        var $id = '';
        var $output = 'OBJECT';
        var $unit = array();
        var $details;
        var $course_id = '';

        function __construct( $id = '', $output = 'OBJECT' ) {
            $this->id = $id;
            $this->output = $output;
            $this->details = get_post( $this->id, $this->output );

            $this->course_id = $this->get_parent_course_id();
        }

        function Unit( $id = '', $output = 'OBJECT' ) {
            $this->__construct( $id, $output );
        }

        function get_unit() {

            $unit = get_post( $this->id, $this->output );

            if ( !empty( $unit ) ) {

                if ( $unit->post_title == '' ) {
                    $unit->post_title = __( 'Untitled', 'cp' );
                }

                if ( $unit->post_status == 'private' || $unit->post_status == 'draft' ) {
                    $unit->post_status = __( 'unpublished', 'cp' );
                }

                return $unit;
            } else {
                return false;
            }
        }

        function is_unit_available( $unit_id = '' ) {

            if ( $unit_id == '' ) {
                $unit_id = $this->id;
            }

            $unit_details = $this->get_unit( $unit_id );

            $current_date = ( date( 'Y-m-d', current_time( 'timestamp', 0 ) ) );

            /* Check if previous unit must be 100% completed */

            $forced_not_available = false;

            //if ( $unit_details->force_current_unit_completion == 'on' ) {
            $previous_unit = $this->get_previous_unit_from_the_same_course( $unit_id );

            if ( $previous_unit ) {
                if ( $previous_unit[0]->force_current_unit_completion == 'on' ) {
                    if ( do_shortcode( '[course_unit_details field="percent" unit_id="' . $previous_unit[0]->ID . '"]' ) < 100 ) {
                        $forced_not_available = true;
                    }
                }
            }

            if ( $forced_not_available ) {
                return false;
            }

            if ( $current_date < $unit_details->unit_availability || $forced_not_available ) {
                return false;
            }

            return true;
        }

        function get_previous_unit_from_the_same_course( $unit_id = '', $post_status = 'publish' ) {

            if ( $unit_id == '' ) {
                $unit_id = $this->id;
            }

            $current_unit_order = get_post_meta( $unit_id, 'unit_order', true );

            $args = array(
                'post_type' => 'unit',
                'post_status' => $post_status,
                'posts_per_page' => -1,
                'meta_key' => 'course_id',
                'meta_value' => $this->course_id,
                'meta_query' => array(
                    array(
                        'key' => 'unit_order',
                        'compare' => '=',
                        'value' => $current_unit_order - 1
                    ),
                )
            );

            $previous_unit = get_posts( $args );

            return $previous_unit;
        }

        function update_unit() {
            global $user_id, $last_inserted_unit_id;

            $post_status = 'private';
            
            if ( isset( $_POST['unit_id'] ) && $_POST['unit_id'] != 0 ) {

                $unit_id = ( isset( $_POST['unit_id'] ) ? $_POST['unit_id'] : $this->id );

                $unit = get_post( $unit_id, $this->output );

                if ( $_POST['unit_name'] !== '' && $_POST['unit_name'] !== __( 'Untitled', 'cp' ) /* && $_POST['unit_description'] !== '' */ ) {
                    if ( $unit->post_status !== 'publish' ) {
                        $post_status = 'private';
                    } else {
                        $post_status = 'publish';
                    }
                } else {
                    $post_status = 'draft';
                }
            }

            $post = array(
                'post_author' => $user_id,
                'post_content' => '',//$_POST['unit_description']
                'post_status' => $post_status, //$post_status
                'post_title' => $_POST['unit_name'],
                'post_type' => 'unit',
                'post_parent' => $_POST['course_id']
            );

            if ( isset( $_POST['unit_id'] ) ) {
                $post['ID'] = $_POST['unit_id']; //If ID is set, wp_insert_post will do the UPDATE instead of insert
            }

            $post_id = wp_insert_post( $post );

            $last_inserted_unit_id = $post_id;

            update_post_meta( $post_id, 'course_id', $_POST['course_id'] );

            update_post_meta( $post_id, 'unit_availability', $_POST['unit_availability'] );

            update_post_meta( $post_id, 'force_current_unit_completion', $_POST['force_current_unit_completion'] );

            if ( !get_post_meta( $post_id, 'unit_order', true ) ) {
                update_post_meta( $post_id, 'unit_order', $post_id );
            }

            return $post_id;
        }

        function delete_unit( $force_delete ) {
            $wpdb;
            wp_delete_post( $this->id, $force_delete ); //Whether to bypass trash and force deletion
            
            //Delete unit modules
            
            $args = array(
                'posts_per_page' => -1,
                'post_parent' => $this->id,
                'post_type' => 'module',
                'post_status' => 'any',
                );

            $units_modules = get_posts( $args );

            foreach ( $units_modules as $units_module ) {
                $module = new Unit_Module( $units_module->ID );
                $module->delete_module( true );
            }
        }

        function change_status( $post_status ) {
            $post = array(
                'ID' => $this->id,
                'post_status' => $post_status,
            );

            // Update the post status
            wp_update_post( $post );
        }

        function can_show_permalink() {
            $unit = $this->get_unit();
            if ( $unit->post_status !== 'draft' ) {
                return true;
            } else {
                return false;
            }
        }

        function get_permalink( $course_id = '' ) {
            global $course_slug;
            global $units_slug;

            if ( $course_id == '' ) {
                $course_id = get_post_meta( $this->id, 'course_id', true );
            }

            $course = new Course( $course_id );
            $course = $course->get_course();

            $unit_permalink = trailingslashit( site_url() . '/' . $course_slug . '/' . $course->post_name . '/' . $units_slug . '/' . $this->details->post_name );
            return $unit_permalink;
        }

        function get_unit_id_by_name( $slug ) {
            global $wpdb;
            $id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s", $slug ) );
            return $id;
        }

        function get_parent_course_id( $unit_id = '' ) {
            if ( $unit_id == '' ) {
                $unit_id = $this->id;
            }

            $course_id = get_post_meta( $unit_id, 'course_id', true );
            return $course_id;
        }

    }

}
?>
