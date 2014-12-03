<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'Unit' ) ) {

	class Unit extends CoursePress_Object {

		var $id			 = '';
		var $output		 = 'OBJECT';
		var $unit		 = array();
		var $details;
		var $course_id	 = '';
		var $status		 = array();

		function __construct( $id = '', $output = 'OBJECT' ) {
			$this->id		 = $id;
			$this->output	 = $output;

			// Attempt to load from cache or create new cache object
			if ( !self::load( self::TYPE_UNIT, $this->id, $this->details ) ) {

				// Get the course				
				$this->details = get_post( $this->id, $this->output );

				// Initialize the unit				
				$this->init_unit( $this->details );

				// Cache the unit object				
				self::cache( self::TYPE_UNIT, $this->id, $this->details );
				// cp_write_log( 'Unit[' . $this->id . ']: Saved to cache..');
			} else {
				// cp_write_log( 'Unit[' . $this->id . ']: Loaded from cache...');
			};

			// Will return cached value if it exists
			$this->course_id = $this->get_parent_course_id();

			/**
			 * Perform action after a Unit object is created.
			 *
			 * @since 1.2.2
			 */
			do_action( 'coursepress_unit_init', $this );
		}

		function Unit( $id = '', $output = 'OBJECT' ) {
			$this->__construct( $id, $output );
		}

		function init_unit( &$unit ) {
			if ( !empty( $unit ) ) {

				if ( $unit->post_title == '' ) {
					$unit->post_title = __( 'Untitled', 'cp' );
				}

				if ( $unit->post_status == 'private' || $unit->post_status == 'draft' ) {
					$unit->post_status = __( 'unpublished', 'cp' );
				}

				// Set parent ID
				$course_id		 = get_post_meta( $unit->ID, 'course_id', true );
				$unit->course_id = $course_id;

				$unit->current_unit_order = get_post_meta( $unit->ID, 'unit_order', true );
				// if ( !isset( $unit->details->post_name ) ) {
				// 	$unit->details->post_name = '';
				// }
			}
		}

		function get_unit() {
			return !empty( $this->details ) ? $this->details : false;
		}

		function is_unit_available( $unit_id = '' ) {

			$unit_details = false;
			if ( $unit_id == '' ) {
				$unit_details	 = $this->get_unit();
				$unit_id		 = $this->id;
			} else {
				$unit			 = new Unit( (int) $unit_id );
				$unit_details	 = $unit->get_unit();
			}

			/* Not filtering date format as it could cause conflicts.  Only filter date on display. */
			$current_date = ( date( 'Y-m-d', current_time( 'timestamp', 0 ) ) );

			/* Check if previous has conditions */
			$previous_unit_id							 = $this->get_previous_unit_from_the_same_course( $unit_id );
			$force_current_unit_completion				 = !empty( $previous_unit_id ) ? get_post_meta( $previous_unit_id, 'force_current_unit_completion', true ) : '';
			$force_current_unit_successful_completion	 = !empty( $previous_unit_id ) ? get_post_meta( $previous_unit_id, 'force_current_unit_successful_completion', true ) : '';

			$available = true;

			$completion = new Course_Completion( $this->course_id );
			$completion->init_student_status();

			$mandatory_done	 = $completion->unit_all_pages_viewed( $previous_unit_id ) && $completion->unit_all_mandatory_answered( $previous_unit_id );
			$unit_completed	 = 100 == $completion->unit_progress( $previous_unit_id );

			$this->status[ 'mandatory_required' ][ 'enabled' ]	 = !empty( $force_current_unit_completion ) && 'on' == $force_current_unit_completion;
			$this->status[ 'mandatory_required' ][ 'result' ]	 = $mandatory_done;

			$this->status[ 'completion_required' ][ 'enabled' ]	 = !empty( $force_current_unit_successful_completion ) && 'on' == $force_current_unit_successful_completion;
			$this->status[ 'completion_required' ][ 'result' ]	 = $unit_completed;

			$available	 = $this->status[ 'mandatory_required' ][ 'enabled' ] ? $this->status[ 'mandatory_required' ][ 'result' ] : $available;
			$available	 = $this->status[ 'completion_required' ][ 'enabled' ] ? $this->status[ 'completion_required' ][ 'result' ] : $available;

			$this->status[ 'date_restriction' ][ 'result' ] = $current_date >= $unit_details->unit_availability;

			if ( !$this->status[ 'date_restriction' ][ 'result' ] || !$available ) {
				$available = false;
			} else {
				$available = true;
			}

			/**
			 * Perform action if unit is available.
			 *
			 * @since 1.2.2
			 * */
			do_action( 'coursepress_unit_availble', $available, $unit_id );

			/**
			 * Return filtered value.
			 *
			 * Can be used by other plugins to filter unit availability.
			 *
			 * @since 1.2.2
			 * */
			$available = apply_filters( 'coursepress_filter_unit_availability', $available, $unit_id );

			return $available;
		}

		static function get_units_from_course( $course_id, $status = 'publish', $id_only = true ) {

			$args = array(
				'post_type'		 => 'unit',
				'post_status'	 => $status,
				'meta_key'		 => 'unit_order',
				'orderby'		 => 'meta_value_num',
				'order'			 => 'ASC',
				'posts_per_page' => '-1',
				'meta_query'	 => array(
					array(
						'key'		 => 'course_id',
						'compare'	 => 'IN',
						'value'		 => array( $course_id ),
					),
				),
			);

			$type = $id_only ? 'list' : 'object';

			$args[ 'fields' ] = $id_only ? 'ids' : '';

			$units = array();

			// Attempt to load from cache or create new cache object
			if ( !self::load( self::TYPE_UNIT_STATIC, $type . '-' . $status . '-' . $course_id, $units ) ) {

				// Clear it out just incase something did load
				$units = array();

				if ( $id_only ) {
					// Get the units	
					$units = get_posts( $args );
				} else {
					$posts = get_posts( $args );

					// Do it this way so that units initialize correctly and get cached
					foreach ( $posts as $post ) {

						$unit_object = new Unit( $post->ID );
						$units[]	 = $unit_object->details;
					}
				}

				// Cache the units list
				self::cache( self::TYPE_UNIT_STATIC, $type . '-' . $status . '-' . $course_id, $units );
				// cp_write_log( $type . '-' . $status . '-' . $course_id . ': Saved to cache..');
			} else {
				// cp_write_log( $type . '-' . $status . '-' . $course_id . ': Loaded from cache...');
			};

			return $units;
		}

		function get_previous_unit_from_the_same_course() {

			$units = self::get_units_from_course( $this->course_id );

			$position			 = 0;
			$previous_unit_id	 = 0;

			if ( $this->details->ID == $this->details->current_unit_order ) {
				$haystack = array();
				foreach ( $units as $unit_item ) {
					$haystack[] = (int) $unit_item;
				}
				$position = array_search( $this->details->ID, $haystack );
			} else {
				// Adjust the index to fit in array bounds.
				$position = $this->details->current_unit_order - 1;
			}

			// There is no previous unit...
			if ( 0 == $position ) {
				$previous_unit_id = $this->details->ID;
			} else {
				$previous_unit_id = (int) $units[ $position - 1 ];
			}

			return $this->details->ID != $previous_unit_id ? $previous_unit_id : false;
		}

		function get_unit_page_time_estimation( $unit_id, $page_num ) {

			$unit_pages	 = $this->get_number_of_unit_pages();
			$module		 = new Unit_Module();
			$modules	 = $module->get_modules( $unit_id );


			for ( $i = 1; $i <= $unit_pages; $i++ ) {
				$pages_num		 = 1;
				$total_minutes	 = 0;
				$total_seconds	 = 0;

				foreach ( $modules as $mod ) {
					$class_name		 = $mod->module_type;
					$time_estimation = $mod->time_estimation;

					if ( class_exists( $class_name ) ) {
						$module = new $class_name();

						if ( $module->name == 'page_break_module' ) {
							$pages_num++;
						} else {
							if ( $pages_num == $page_num ) {
								if ( isset( $time_estimation ) && $time_estimation !== '' ) {
									$estimatation = explode( ':', $time_estimation );
									if ( isset( $estimatation[ 0 ] ) ) {
										$total_minutes = $total_minutes + intval( $estimatation[ 0 ] );
									}
									if ( isset( $estimatation[ 1 ] ) ) {
										$total_seconds = $total_seconds + intval( $estimatation[ 1 ] );
									}
								}
							}
						}
					}
				}

				$total_seconds = $total_seconds + ($total_minutes * 60); //converted everything into minutes for easy conversion back to minutes and seconds

				$minutes = floor( $total_seconds / 60 );
				$seconds = $total_seconds % 60;

				if ( $minutes >= 1 || $seconds >= 1 ) {
					return apply_filters( 'coursepress_unit_time_estimation_minutes_and_seconds_format', ($minutes . ':' . ($seconds <= 9 ? '0' . $seconds : $seconds) . ' min' ) );
				} else {
					return apply_filters( 'coursepress_unit_time_estimation_na_format', __( 'N/A', 'cp' ) );
				}
			}
		}

		function get_unit_time_estimation( $unit_id ) {
			$module			 = new Unit_Module();
			$modules		 = $module->get_modules( $unit_id );
			$total_minutes	 = 0;
			$total_seconds	 = 0;

			foreach ( $modules as $mod ) {
				$time_estimation = $mod->time_estimation;
				if ( isset( $time_estimation ) && $time_estimation !== '' ) {
					$estimatation = explode( ':', $time_estimation );
					if ( isset( $estimatation[ 0 ] ) ) {
						$total_minutes = $total_minutes + intval( $estimatation[ 0 ] );
					}
					if ( isset( $estimatation[ 1 ] ) ) {
						$total_seconds = $total_seconds + intval( $estimatation[ 1 ] );
					}
				}
			}

			$total_seconds = $total_seconds + ($total_minutes * 60); //converted everything into minutes for easy conversion back to minutes and seconds

			$minutes = floor( $total_seconds / 60 );
			$seconds = $total_seconds % 60;

			if ( $minutes >= 1 || $seconds >= 1 ) {
				return apply_filters( 'coursepress_unit_time_estimation_minutes_and_seconds_format', ($minutes . ':' . ($seconds <= 9 ? '0' . $seconds : $seconds) . ' min' ) );
			} else {
				return apply_filters( 'coursepress_unit_time_estimation_na_format', __( 'N/A', 'cp' ) );
			}
		}

		function create_auto_draft( $course_id ) {
			global $user_id;

			$post = array(
				'post_author'	 => $user_id,
				'post_content'	 => '',
				'post_status'	 => 'auto-draft', //$post_status
				'post_title'	 => __( 'Untitled', 'cp' ),
				'post_type'		 => 'unit',
				'post_parent'	 => $course_id
			);

			$post_id = wp_insert_post( $post );

			// Clear cached object just in case
			self::kill( self::TYPE_UNIT, $post_id );
			self::kill( self::TYPE_UNIT_MODULES, $post_id );

			return $post_id;
		}

		function delete_all_elements_auto_drafts( $unit_id = false ) {
			global $wpdb;

			if ( !$unit_id ) {
				$unit_id = $this->id;
			}

			$unit_id = (int) $unit_id;

			$drafts = get_posts( array( 'post_type' => array( 'module', 'unit' ), 'post_status' => 'auto-draft', 'post_parent' => $unit_id, 'post_per_page' => -1 ) );

			if ( !empty( $drafts ) ) {
				foreach ( $drafts as $draft ) {
					// Clear possible cached objects because we're deleting them
					self::kill( self::TYPE_UNIT, $draft->ID );
					self::kill( self::TYPE_UNIT_MODULES, $draft->ID );
					wp_delete_post( $draft->ID, true );
				}
			}
		}

		function delete_all_unit_auto_drafts( $course_id = false ) {
			global $wpdb;

			if ( !$unit_id ) {
				$unit_id = $this->course_id;
			}

			$course_id = (int) $course_id;

			$drafts = get_posts( array( 'post_type' => array( 'module', 'unit' ), 'post_status' => 'auto-draft', 'post_parent' => $course_id, 'post_per_page' => -1 ) );

			if ( !empty( $drafts ) ) {
				foreach ( $drafts as $draft ) {
					// Clear possible cached objects because we're deleting them
					self::kill( self::TYPE_UNIT, $draft->ID );
					self::kill( self::TYPE_UNIT_MODULES, $draft->ID );
					wp_delete_post( $draft->ID, true );
				}
			}
		}

		function update_unit() {
			global $user_id, $last_inserted_unit_id;

			$post_status = 'private';

			if ( isset( $_POST[ 'unit_id' ] ) && $_POST[ 'unit_id' ] != 0 ) {

				$unit_id = ( isset( $_POST[ 'unit_id' ] ) ? $_POST[ 'unit_id' ] : $this->id );

				$unit = get_post( $unit_id, $this->output );

				if ( $_POST[ 'unit_name' ] !== '' && $_POST[ 'unit_name' ] !== __( 'Untitled', 'cp' ) /* && $_POST['unit_description'] !== '' */ ) {
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
				'post_author'	 => $user_id,
				'post_content'	 => '', //$_POST['unit_description']
				'post_status'	 => $post_status, //$post_status
				'post_title'	 => cp_filter_content( $_POST[ 'unit_name' ], true ),
				'post_type'		 => 'unit',
				'post_parent'	 => $_POST[ 'course_id' ]
			);

			$new_unit = true;
			if ( isset( $_POST[ 'unit_id' ] ) ) {
				$post[ 'ID' ]	 = $_POST[ 'unit_id' ]; //If ID is set, wp_insert_post will do the UPDATE instead of insert
				$new_unit		 = false;
			}

			$post_id = wp_insert_post( $post );

			// Clear cached object because we're updating the object
			self::kill( self::TYPE_UNIT, $post_id );
			self::kill( self::TYPE_UNIT_MODULES, $post_id );
			// Clear related caches
			$course_id = $this->course_id;
			self::kill_related( self::TYPE_COURSE, $course_id );

			$last_inserted_unit_id = $post_id;

			update_post_meta( $post_id, 'course_id', (int) $_POST[ 'course_id' ] );

			update_post_meta( $post_id, 'unit_availability', cp_filter_content( $_POST[ 'unit_availability' ] ) );

			update_post_meta( $post_id, 'force_current_unit_completion', cp_filter_content( $_POST[ 'force_current_unit_completion' ] ) );
			update_post_meta( $post_id, 'force_current_unit_successful_completion', cp_filter_content( $_POST[ 'force_current_unit_successful_completion' ] ) );

			update_post_meta( $post_id, 'page_title', cp_filter_content( $_POST[ 'page_title' ], true ) );

			update_post_meta( $post_id, 'show_page_title', cp_filter_content( $_POST[ 'show_page_title_field' ] ) );

			if ( !get_post_meta( $post_id, 'unit_order', true ) ) {
				update_post_meta( $post_id, 'unit_order', $post_id );
			}

			// $this->delete_all_elements_auto_drafts( $post_id );
			// $this->delete_all_unit_auto_drafts( $course_id );

			if ( $new_unit ) {
				do_action( 'coursepress_unit_created', $post_id );
			} else {
				do_action( 'coursepress_unit_updated', $post_id );
			}

			return $post_id;
		}

		function get_unit_page_name( $page_number ) {
			return !empty( $this->details->page_title ) ? $this->details->page_title[ (int) ($page_number - 1) ] : '';
		}

		function delete_unit( $force_delete ) {

			/**
			 * Allow Unit deletion to be cancelled when filter returns true.
			 *
			 * @since 1.2.2
			 */
			if ( apply_filters( 'coursepress_unit_cancel_delete', false, $this->id ) ) {

				/**
				 * Perform actions if the deletion was cancelled.
				 *
				 * @since 1.2.2
				 */
				do_action( 'coursepress_unit_delete_cancelled', $this->id );
				return false;
			}

			$the_unit = new Unit( $this->id );

			// Clear cached object because we're deleting the object.
			self::kill( self::TYPE_UNIT, $this->id );
			self::kill( self::TYPE_UNIT_MODULES, $this->id );
			// Clear related caches
			$course_id = $this->course_id;
			self::kill_related( self::TYPE_COURSE, $course_id );

			wp_delete_post( $this->id, $force_delete ); //Whether to bypass trash and force deletion
			//Delete unit modules

			$args = array(
				'posts_per_page' => -1,
				'post_parent'	 => $this->id,
				'post_type'		 => 'module',
				'post_status'	 => 'any',
			);

			$units_modules = get_posts( $args );

			foreach ( $units_modules as $units_module ) {
				$module = new Unit_Module( $units_module->ID );
				$module->delete_module( true );
			}

			/**
			 * Perform actions after a Unit is deleted.
			 *
			 * @var $course  The Unit object if the ID or post_title is needed.
			 *
			 * @since 1.2.1
			 */
			do_action( 'coursepress_unit_deleted', $the_unit );
		}

		function change_status( $post_status ) {
			$post = array(
				'ID'			 => $this->id,
				'post_status'	 => $post_status,
			);

			// Update the post status
			wp_update_post( $post );

			// Clear cached object because we've modified the object.
			self::kill( self::TYPE_UNIT, $this->id );
			self::kill( self::TYPE_UNIT_MODULES, $this->id );
			// Clear related caches
			$course_id = $this->course_id;
			self::kill_related( self::TYPE_COURSE, $course_id );

			/**
			 * Perform actions when Unit status is changed.
			 *
			 * var $this->id  The Unit id
			 * var $post_status The new status
			 *
			 * @since 1.2.1
			 */
			do_action( 'coursepress_unit_status_changed', $this->id, $post_status );
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

			if ( empty( $course_id ) ) {
				$course_id = get_post_meta( $this->id, 'course_id', true );
			}

			$course	 = new Course( $course_id );
			$course	 = $course->get_course();

			$unit_permalink = trailingslashit( home_url() . '/' ) . trailingslashit( $course_slug . '/' ) . trailingslashit( isset( $course->post_name ) ? $course->post_name : '' . '/'  ) . trailingslashit( $units_slug . '/' ) . trailingslashit( isset( $this->details->post_name ) ? $this->details->post_name : '' . '/'  );

			return $unit_permalink;
		}

		function get_unit_id_by_name( $slug ) {

			if ( !cp_can_see_unit_draft() ) {
				$post = get_posts(
				array(
					'post_type'			 => array( 'unit' ),
					'name'				 => $slug,
					'post_per_page'		 => 1,
					'post_status'		 => 'publish',
					'suppress_filters'	 => false,
				)
				);
			} else {
				$post_id = cp_get_id_by_post_name($slug);
				$post = get_post( $post_id );
			}

			$post	 = !empty( $post ) && is_array( $post ) ? array_pop( $post ) : $post;

			return !empty( $post ) ? $post->ID : false;
		}

		function get_parent_course_id( $unit_id = '' ) {

			if ( $unit_id == '' ) {

				// If its already loaded from cache, return that value.
				if ( isset( $this->details ) && isset( $this->details->course_id ) ) {
					return $this->details->course_id;
				}

				$unit_id = $this->id;
			}

			$course_id = get_post_meta( $unit_id, 'course_id', true );
			return $course_id;
		}

		function get_number_of_unit_pages( $unit_id = '' ) {
			if ( $unit_id == '' ) {
				$unit_id = $this->id;
			}

			$module	 = new Unit_Module();
			$modules = $module->get_modules( $unit_id );

			$pages_num = 1;

			foreach ( $modules as $mod ) {
				$class_name = $mod->module_type;

				if ( class_exists( $class_name ) ) {
					$module = new $class_name();

					if ( $module->name == 'page_break_module' ) {
						$pages_num++;
					}
				}
			}

			return $pages_num;
		}

		function get_unit_modules( $unit_id = '' ) {

			if ( $unit_id == '' ) {
				$unit_id = $this->id;
			}

			$args = array(
				'post_type'		 => 'module',
				'post_status'	 => 'any',
				'posts_per_page' => -1,
				'post_parent'	 => $unit_id,
				'meta_key'		 => 'module_order',
				'orderby'		 => 'meta_value_num',
				'order'			 => 'ASC',
			);

			$modules = get_posts( $args );

			return $modules;
		}

		function duplicate( $unit_id = '', $course_id = '' ) {
			global $wpdb;

			if ( $unit_id == '' ) {
				$unit_id = $this->id;
			}

			/**
			 * Allow Unit duplication to be cancelled when filter returns true.
			 *
			 * @since 1.2.2
			 */
			if ( apply_filters( 'coursepress_unit_cancel_duplicate', false, $unit_id ) ) {

				/**
				 * Perform actions if the duplication was cancelled.
				 *
				 * @since 1.2.2
				 */
				do_action( 'coursepress_unit_duplicate_cancelled', $unit_id );
				return false;
			}

			/* Duplicate course and change some data */

			$new_unit	 = $this->get_unit();
			$old_unit_id = $new_unit->ID;

			unset( $new_unit->ID );
			unset( $new_unit->guid );

			$new_unit->post_author	 = get_current_user_id();
			$new_unit->post_status	 = 'private';
			$new_unit->post_parent	 = $course_id;

			$new_unit_id = wp_insert_post( $new_unit );


			/*
			 * Duplicate unit post meta
			 */

			if ( !empty( $new_unit_id ) ) {
				$post_metas = get_post_meta( $old_unit_id );
				foreach ( $post_metas as $key => $meta_value ) {
					$value	 = array_pop( $meta_value );
					$value	 = maybe_unserialize( $value );
					update_post_meta( $new_unit_id, $key, $value );
				}
			}

			update_post_meta( $new_unit_id, 'course_id', $course_id );

			$unit_modules = $this->get_unit_modules( $old_unit_id );

			foreach ( $unit_modules as $unit_module ) {
				$module = new Unit_Module( $unit_module->ID );
				$module->duplicate( $unit_module->ID, $new_unit_id );
			}

			/**
			 * Perform action when the unit is duplicated.
			 *
			 * @since 1.2.2
			 */
			do_action( 'coursepress_unit_duplicated', $new_unit_id );
		}

	}

}
?>
