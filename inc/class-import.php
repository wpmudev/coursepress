<?php

/**
 * Class CoursePress_Import
 *
 * @since 3.0
 */
class CoursePress_Import extends CoursePress_Utility
{
    var $with_students = false;
    var $with_comments = false;
    var $replace = false;
    var $courses = array();
    var $course_imported_id = 0;
    var $unit_keys = array();

    public function __construct( $course_object, $options ) {
        //error_log(print_r($course_object,true));
        $this->setUp( $course_object );

        if ( ! empty( $options ) ) {
            $this->setUp( $options );
        }

        // Add course author as user
        if ( ! empty( $this->author ) ) {
            $author_id = $this->maybe_add_user($this->author);
            $this->course->post_author = $author_id;
        }

        // Import the course
        $this->import_course();

        // Import course units
        $this->import_course_units();

        // Import course meta
        $this->import_course_meta();

        // Import course instructors
        $this->import_course_instructors();

        // Import course facilitators
        $this->import_course_facilitators();

        // Import course students
        $this->import_course_students();
    }

    function maybe_add_user( $user_data ) {
        $add = true;

        if ( ! empty( $user_data->user_email ) && email_exists( $user_data->user_email ) ) {
            $add = false;
            $user = get_user_by( 'email', $user_data->user_email );
        }
        if ( ! empty( $user_data->user_login ) && username_exists( $user_data->user_login ) ) {
            $add = false;
            $user = get_user_by( 'login', $user_data->user_login );
        }

        if ( $add || empty( $user ) ) {
            // User doesn't exist, insert
            unset( $user_data->ID );
            $user_id = wp_insert_user( get_object_vars( $user_data ) );
            if ( ! is_wp_error( $user_id ) ) {
                return $user_id;
            }
        } else {
            return $user->ID;
        }

        return 0;
    }

    function import_course() {
        global $wpdb;

        // Remove course ID
        $this->course_imported_id = $this->course->ID;
        unset( $this->course->ID );
        $the_course = get_object_vars( $this->course );

        if ( $this->replace ) {
            // Find a course that has similar title
            $course_title = $this->course->post_title;
            $sql = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE `post_type`='course' AND `post_title`=%s", $course_title );
            $course_ids = $wpdb->get_results( $sql );

            if ( ! empty( $course_ids ) ) {
                foreach( $course_ids as $count => $course_id ) {
                    $course = get_post( $course_id );
                    $course = get_object_vars( $course );
                    $course = wp_parse_args( $the_course, $course );
                    $this->courses[] = wp_update_post( $course );

                    // Delete units of this course
                    $course_data = coursepress_get_course( $course_id );
                    $units = $course_data->get_units();

                    if ( ! empty( $units ) ) {
                        foreach ( $units as $unit ) {
                            $unit_id = $unit->__get( 'ID' );
                            coursepress_delete_unit( $unit_id );
                        }
                    }
                }
            }
        } else {
            $this->courses[] = wp_insert_post( $the_course );
        }
    }

    function import_course_units() {
        if ( ! empty( $this->units ) ) {
            foreach ( $this->units as $unit ) {
                $the_unit = get_object_vars( $unit->unit );

                if ( ! isset( $the_unit->ID ) ) {
                	continue;
                }

                // Remove ID
                $old_unit_id = $the_unit->ID;
                unset( $the_unit['ID'] );

                foreach ( $this->courses as $course_id ) {
                    $unit['post_parent'] = $course_id;
                }

                $unit_id = wp_insert_post( $the_unit );
                $this->unit_keys[ $old_unit_id ] = $unit_id;
            }
        }
    }

    function import_course_meta() {
        $meta = $this->to_array( $this->meta );

        $settings = $meta['course_settings'];

        if ( ! empty( $settings ) ) {
            $settings = $settings[0];
            unset( $meta['course_settings'] );
        }

        $course_structures = array(
            'structure_visible_units',
            'structure_preview_units',
            'structure_visible_pages',
            'structure_preview_pages',
            'structure_visible_module',
            'structure_preview_module',
        );

        if ( ! empty( $this->course_ids ) ) {
            foreach ( $this->course_ids as $course_id ) {
                // Update course structure
                foreach ( $course_structures as $structure ) {
                    if ( ! empty( $settings[ $structure ] ) ) {
                        foreach ( $settings[ $structure ] as $key => $value ) {
                            $new_key = str_replace( $this->course_imported_id, $course_id, $key );
                            unset( $settings[ $key ] );
                            $settings[ $new_key ] = $value;
                        }
                    }
                }

                coursepress_course_update_setting( $course_id, $settings );
                $this->insert_meta( $course_id, $meta );
            }
        }
    }

    function import_course_instructors() {
        if ( ! empty( $this->instructors ) ) {
            foreach ( $this->instructors as $instructor ) {
                $instructor_id = $this->maybe_add_user( $instructor );

                foreach ( $this->courses as $course_id ) {
                    coursepress_add_course_instructor( $instructor_id, $course_id );
                }
            }
        }
    }

    function import_course_facilitators() {
        if ( ! empty( $this->facilitators ) ) {
            foreach ( $this->facilitators as $facilitator ) {
                $facilitator_id = $this->maybe_add_user( $facilitator );

                foreach ( $this->courses as $course_id ) {
                    coursepress_add_course_facilitator( $facilitator_id, $course_id );
                }
            }
        }
    }

    /**
     * Helper function to insert post_meta
     *
     * @param (array|object) $metas			The metadata to insert.
     * @return void
     **/
    function insert_meta( $post_id, $metas = array() ) {
        $metas = $this->to_array( $metas );

        foreach ( $metas as  $key => $values ) {
            $values = array_map( 'maybe_unserialize', $values );

            if ( is_array( $values ) ) {
                foreach ( $values as $value ) {
                    $value = maybe_unserialize( $value );

                    add_post_meta( $post_id, $key, $value );
                }
            } else {
                add_post_meta( $post_id, $key, $values );
            }
        }
    }

    function import_course_students() {
        if ( $this->with_students && ! empty( $this->students ) ) {
            foreach ( $this->students as $student ) {
                $student_id = $this->maybe_add_user( $student );
                $progress = array();

                if ( ! empty( $student->progress ) ) {
                    $progress = $this->to_array( $student->progress );
                }

                foreach ( $this->courses as $course_id ) {
                    coursepress_add_student( $course_id, $student_id );
                }


            }
        }
    }

    function get_course() {
    	$course_id = array_pop( $this->courses );
    	$course = coursepress_get_course( $course_id );

    	return $course;
    }
}