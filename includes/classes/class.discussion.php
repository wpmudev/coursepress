<?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

if ( !class_exists( 'Discussion' ) ) {

    class Discussion {

        var $id = '';
        var $output = 'OBJECT';
        var $discussion = array( );
        var $details;

        function __construct( $id = '', $output = 'OBJECT' ) {
            $this->id = $id;
            $this->output = $output;
            $this->details = get_post( $this->id, $this->output );
        }

        function Discussion( $id = '', $output = 'OBJECT' ) {
            $this->__construct( $id, $output );
        }

        function get_discussion( ) {

            $discussion = get_post( $this->id, $this->output );

            if ( !empty( $discussion ) ) {
                return $discussion;
            } else {
                return new stdClass( );
            }
        }

        function get_unit_name( ) {
            if( !isset( $this->details->unit_id ) || $this->details->unit_id == '' ) {
                return __( 'General', 'coursepress' );
            }else{
                $unit_obj = new Unit( $this->details->unit_id );
                $unit = $unit_obj->get_unit( );
                return $unit->post_title;
            }
        }
        
        function get_discussion_id_by_name( $slug ) {

            $args = array(
                'name' => $slug,
                'post_type' => 'discussion',
                'post_status' => 'any',
                'posts_per_page' => 1
            );

            $post = get_posts( $args );

            if ( $post ) {
                return $post[0]->ID;
            } else {
                return false;
            }
        }

        function update_discussion( $discussion_title = '', $discussion_description = '', $course_id = '', $unit_id = '' ) {
            global $user_id, $wpdb;

            $discussion = get_post( $this->id, $this->output );

            $post_status = 'publish';

            $post = array(
                'post_author' => $user_id,
                'post_content' => ( $discussion_description == '' ? $_POST['discussion_description'] : $discussion_description ),
                'post_status' => $post_status,
                'post_title' => ( $discussion_title == '' ? $_POST['discussion_name'] : $discussion_title ),
                'post_type' => 'discussions',
            );

            if ( isset( $_POST['discussion_id'] ) ) {
                $post['ID'] = $_POST['discussion_id']; //If ID is set, wp_insert_post will do the UPDATE instead of insert
            }

            $post_id = wp_insert_post( $post );
            
            //Update post meta
            if ( $post_id != 0 ) {
                
                if( $unit_id == '' ) {
                    $unit_id = $_POST['units_dropdown'];
                }
                
                update_post_meta( $post_id, 'course_id', $course_id );
                update_post_meta( $post_id, 'unit_id', $unit_id );
                
                foreach ( $_POST as $key => $value ) {
                    if ( preg_match( "/meta_/i", $key ) ) {//every field name with prefix "meta_" will be saved as post meta automatically
                        update_post_meta( $post_id, str_replace( 'meta_', '', $key ), $value );
                    }
                }
            }

            return $post_id;
        }

        function delete_discussion( $force_delete = true ) {
            $wpdb;
            wp_delete_post( $this->id, $force_delete ); //Whether to bypass trash and force deletion
        }

        function change_status( $post_status ) {
            $post = array(
                'ID' => $this->id,
                'post_status' => $post_status,
            );

            // Update the post status
            wp_update_post( $post );
        }

    }

}
?>
