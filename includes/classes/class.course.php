<?php
/**
 * This file defines the Course class.
 *
 * @copyright Incsub (http://incsub.com/)
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 (GPL-2.0)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301 USA
 *
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( !class_exists( 'Course' ) ) {

	/**
	 * This class defines the methods and properties of a Course in CoursePress.
	 *
	 * A Course object has all the required methods to manage the Course custom
	 * post type, and the surrounding meta data used to create courses in CoursePress.
	 *
	 * A course is typically also the parent post for a Unit[].
	 *
	 * If creating a Course object outside of CoursePress make sure that CoursePress
	 * has already loaded. Hooking 'plugins_loaded' should do the trick.
	 *
	 * @todo Make sure we need !class_exists as it should be require_once() anyway.
	 *
	 * @since 1.0.0
	 * @package CoursePress
	 */
	class Course extends CoursePress_Object {

		var $id		 = '';
		var $output	 = 'OBJECT';
		var $course	 = array();
		var $details;
		var $data	 = array();

		/**
		 * The Course constructor.
		 *
		 * The constructor makes sure that it uses the WordPress Object cache to make
		 * subsequent access less resource heavy.
		 *
		 * Note: The actual post gets loaded in Course::$details;
		 *
		 * @param string $id
		 * @param string $output
		 */
		function __construct( $id = '', $output = 'OBJECT' ) {
			$this->id		 = $id;
			$this->output	 = $output;

// Attempt to load from cache or create new cache object
			if ( !self::load( self::TYPE_COURSE, $this->id, $this->details ) ) {

// Get the course
				$this->details = get_post( $this->id, $this->output );

// Initialize the course
				$this->init_course( $this->details );

// Cache the course object
				self::cache( self::TYPE_COURSE, $this->id, $this->details );
			};

			/**
			 * Perform action after a course object is created.
			 *
			 * @since 1.2.1
			 */
			do_action( 'coursepress_course_init', $this );
		}

// PHP legacy constructor
		function Course( $id = '', $output = 'OBJECT' ) {
			$this->__construct( $id, $output );
		}

		/**
		 * Initialises a Course object.
		 *
		 * If there is no post title defined it will create a default. It also assigns additional
		 * metadata to the Course object.
		 *
		 * @param $course
		 */
		function init_course( &$course ) {
			if ( !empty( $course ) ) {
				if ( !isset( $course->post_title ) || $course->post_title == '' ) {
					$course->post_title = __( 'Untitled', 'cp' );
				}
				if ( $course->post_status == 'private' || $course->post_status == 'draft' ) {
					$course->post_status = 'unpublished';
				}

				$course->allow_course_discussion = get_post_meta( $this->id, 'allow_course_discussion', true );
				$course->class_size				 = get_post_meta( $this->id, 'class_size', true );
			}
		}

		/**
		 * Gets the actual WordPress post object for a Course.
		 *
		 * @return bool|null|WP_Post
		 */
		function get_course() {
			return !empty( $this->details ) ? $this->details : false;
		}

		/**
		 * Renders the course structure.
		 *
		 * Used in shortcodes on the front end to render the course hierarchy.
		 *
		 * @param string $try_title
		 * @param bool $show_try
		 * @param bool $hide_title
		 * @param bool $echo
		 */
		function course_structure_front( $try_title = '', $show_try = true, $hide_title = false, $echo = true ) {
			$show_unit		 = $this->details->show_unit_boxes;
			$preview_unit	 = $this->details->preview_unit_boxes;

			$show_page		 = $this->details->show_page_boxes;
			$preview_page	 = $this->details->preview_page_boxes;

			$units = $this->get_units();

			$content = '';

			if ( !$echo ) {
				ob_start();
			}


			echo $hide_title ? '' : '<label>' . $this->details->post_title . '</label>';
			?>

			<ul class="tree">
				<li>
					<ul>
						<?php
						foreach ( $units as $unit ) {
							$unit_class	 = new Unit( $unit->ID );
							$unit_pages	 = $unit_class->get_number_of_unit_pages();

//					$modules = Unit_Module::get_modules( $unit->ID );

							if ( isset( $show_unit[ $unit->ID ] ) && $show_unit[ $unit->ID ] == 'on' && $unit->post_status == 'publish' ) {
								?>

								<li>

									<label for="unit_<?php echo $unit->ID; ?>" class="course_structure_unit_label">
										<div class="tree-unit-left"><?php echo $unit->post_title; ?></div>
										<div class="tree-unit-right">

											<?php if ( $this->details->course_structure_time_display == 'on' ) { ?>
												<span><?php echo $unit_class->get_unit_time_estimation( $unit->ID ); ?></span>
											<?php } ?>

											<?php
											if ( isset( $preview_unit[ $unit->ID ] ) && $preview_unit[ $unit->ID ] == 'on' ) {
												?>
												<a href="<?php echo Unit::get_permalink( $unit->ID ); ?>?try" class="preview_option"><?php
													if ( $try_title == '' ) {
														_e( 'Try Now', 'cp' );
													} else {
														echo $try_title;
													};
													?></a>
											<?php } ?>
										</div>
									</label>

									<ul>
										<?php
										for ( $i = 1; $i <= $unit_pages; $i ++ ) {
											if ( isset( $show_page[ $unit->ID . '_' . $i ] ) && $show_page[ $unit->ID . '_' . $i ] == 'on' ) {
												?>

												<li class="course_structure_page_li">
													<?php
													$pages_num	 = 1;
													$page_title	 = $unit_class->get_unit_page_name( $i );
													?>

													<label for="page_<?php echo $unit->ID . '_' . $i; ?>">
														<div class="tree-page-left">
															<?php echo( isset( $page_title ) && $page_title !== '' ? $page_title : __( 'Untitled Page', 'cp' ) ); ?>
														</div>
														<div class="tree-page-right">
															<?php if ( $this->details->course_structure_time_display == 'on' ) { ?>
																<span><?php echo $unit_class->get_unit_page_time_estimation( $unit->ID, $i ); ?></span>
															<?php } ?>
															<?php
															if ( isset( $preview_page[ $unit->ID . '_' . $i ] ) && $preview_page[ $unit->ID . '_' . $i ] == 'on' ) {
																?>
																<a href="<?php echo Unit::get_permalink( $unit->ID ); ?>page/<?php echo $i; ?>?try" class="preview_option"><?php
																	if ( $try_title == '' ) {
																		_e( 'Try Now', 'cp' );
																	} else {
																		echo $try_title;
																	};
																	?></a>
															<?php } ?>

														</div>
													</label>

													<?php ?>
												</li>
												<?php
											}
										}//page visible
										?>

									</ul>
								</li>
								<?php
							}//unit visible

							if ( !$echo ) {
								trim( ob_get_clean() );
							}
						}
						?>

					</ul>
					<?php
				}

				function is_open_ended() {
					
				}

				static function get_course_featured_url( $course_id = false ) {
					if ( !$course_id ) {
						return false;
					}

					$course = new Course( $course_id );

					if ( $course->details->featured_url !== '' ) {
						return $course->details->featured_url;
					} else {
						return false;
					}

					unset( $course );
				}

				static function get_course_thumbnail( $course_id = false ) {
					if ( !$course_id ) {
						return false;
					}

					$thumb = get_post_thumbnail_id( $course_id );
					if ( $thumb !== '' ) {
						return $thumb;
					} else {
						self::get_course_featured_url( $course_id );
					}
				}

				static function has_course_video( $course_id = false ) {
					if ( !$course_id ) {
						return false;
					}

					$course_video = get_post_meta( $course_id, 'course_video_url', true );

					if ( $course_video ) {
						return true;
					} else {
						return false;
					}
				}

				static function get_course_id_by_marketpress_product_id( $marketpress_product_id ) {

					$args = array(
						'post_type'		 => 'course',
						'post_status'	 => 'any',
						'meta_key'		 => 'marketpress_product',
						'meta_value'	 => $marketpress_product_id,
						'posts_per_page' => 1,
						'fields'		 => 'ids',
					);

					$post = get_posts( $args );

					if ( $post ) {
						return (int) $post[ 0 ];
					} else {
						return false;
					}
				}

				static function get_course_id_by_name( $slug ) {

					$args = array(
						'name'			 => $slug,
						'post_type'		 => 'course',
						'post_status'	 => 'any',
						'posts_per_page' => 1,
						'fields'		 => 'ids',
					);

					$post = get_posts( $args );

					if ( $post ) {
						return (int) $post[ 0 ];
					} else {
						return false;
					}
				}

				/* function mp_product_id() {
				  $mp_product_id = get_post_meta($this->id, 'mp_product_id', true);
				  return get_post($mp_product_id) ? $mp_product_id : 0;
				  } */

				function mp_product_id( $course_id = false ) {
					$course_id	 = $course_id ? $course_id : $this->id;
					$args		 = array(
						'posts_per_page' => 1,
						'post_type'		 => 'product',
						'post_parent'	 => $course_id,
						'post_status'	 => 'publish',
						'fields'		 => 'ids',
					);

					$products = get_posts( $args );

					if ( isset( $products[ 0 ] ) ) {
						return (int) $products[ 0 ];
					} else {
						return false;
					}
				}

				function update_mp_product( $course_id = false ) {

					$course_id				 = $course_id ? $course_id : $this->id;
					$automatic_sku_number	 = 'CP-' . $course_id;

					if ( cp_use_woo() ) {
						$mp_product_id = CP_WooCommerce_Integration::woo_product_id( $course_id );
					} else {
						$mp_product_id = $this->mp_product_id( $course_id );
					}

					$post = array(
						'post_status'	 => 'publish',
						'post_title'	 => cp_filter_content( $this->details->post_title, true ),
						'post_type'		 => 'product',
						'post_parent'	 => $course_id
					);

					// Add or Update a product if its a paid course
					if ( isset( $_POST[ 'meta_paid_course' ] ) && 'on' == $_POST[ 'meta_paid_course' ] ) {

						if ( $mp_product_id ) {
							$post[ 'ID' ] = $mp_product_id; //If ID is set, wp_insert_post will do the UPDATE instead of insert
						}

						$post_id = wp_insert_post( $post );

						// Only works if the course actually has a thumbnail.
						set_post_thumbnail( $mp_product_id, get_post_thumbnail_id( $course_id ) );

						$automatic_sku = $_POST[ 'meta_auto_sku' ];

						if ( $automatic_sku == 'on' ) {
							$sku[ 0 ] = $automatic_sku_number;
						} else {
							$sku[ 0 ] = cp_filter_content( (!empty( $_POST[ 'mp_sku' ] ) ? $_POST[ 'mp_sku' ] : '' ), true );
						}

						if ( cp_use_woo() ) {
							update_post_meta( $this->id, 'woo_product_id', $post_id );
							update_post_meta( $this->id, 'woo_product', $post_id );

							$price		 = cp_filter_content( (!empty( $_POST[ 'mp_price' ] ) ? $_POST[ 'mp_price' ] : 0 ), true );
							$sale_price	 = cp_filter_content( (!empty( $_POST[ 'mp_sale_price' ] ) ? $_POST[ 'mp_sale_price' ] : 0 ), true );

							update_post_meta( $post_id, '_virtual', 'yes' );
							update_post_meta( $post_id, '_sold_individually', 'yes' );
							update_post_meta( $post_id, '_sku', $sku[ 0 ] );
							update_post_meta( $post_id, '_regular_price', $price );

							if ( !empty( $_POST[ 'mp_is_sale' ] ) ) {
								update_post_meta( $post_id, '_sale_price', $sale_price );
								update_post_meta( $post_id, '_price', $sale_price );
							}else{
								update_post_meta( $post_id, '_price', $price );
							}

							update_post_meta( $post_id, 'mp_is_sale', cp_filter_content( (!empty( $_POST[ 'mp_is_sale' ] ) ? $_POST[ 'mp_is_sale' ] : '' ), true ) );
							update_post_meta( $post_id, 'cp_course_id', $this->id );
						} else {
							update_post_meta( $this->id, 'mp_product_id', $post_id );
							update_post_meta( $this->id, 'marketpress_product', $post_id );

							$price		 = cp_filter_content( (!empty( $_POST[ 'mp_price' ] ) ? $_POST[ 'mp_price' ] : 0 ), true );
							$sale_price	 = cp_filter_content( (!empty( $_POST[ 'mp_sale_price' ] ) ? $_POST[ 'mp_sale_price' ] : 0 ), true );
							update_post_meta( $post_id, 'mp_sku', $sku );
							update_post_meta( $post_id, 'mp_var_name', serialize( array() ) );
							update_post_meta( $post_id, 'mp_price', $price );
							update_post_meta( $post_id, 'mp_sale_price', $sale_price );
							update_post_meta( $post_id, 'mp_is_sale', cp_filter_content( (!empty( $_POST[ 'mp_is_sale' ] ) ? $_POST[ 'mp_is_sale' ] : '' ), true ) );
							update_post_meta( $post_id, 'mp_file', get_permalink( $this->id ) );
							update_post_meta( $post_id, 'cp_course_id', $this->id );
						}
						// Remove product if its not a paid course (clean up MarketPress products)
					} elseif ( isset( $_POST[ 'meta_paid_course' ] ) && 'off' == $_POST[ 'meta_paid_course' ] ) {
						if ( $mp_product_id && 0 != $mp_product_id ) {
							if ( get_post_type( $mp_product_id ) == 'product' ) {
								wp_delete_post( $mp_product_id );
							}
							if ( cp_use_woo() ) {
								delete_post_meta( $this->id, 'woo_product_id' );
								delete_post_meta( $this->id, 'woo_product' );
							} else {
								delete_post_meta( $this->id, 'mp_product_id' );
								delete_post_meta( $this->id, 'marketpress_product' );
							}
						}
					}
				}

				function update_course() {
					global $user_id, $wpdb;

					$course = $this->get_course();

					$new_course = false;

					$post_status = empty( $this->data[ 'status' ] ) ? 'publish' : $this->data[ 'status' ];

					if ( $_POST[ 'course_name' ] != '' && $_POST[ 'course_name' ] != __( 'Untitled', 'cp' ) ) {
						if ( !empty( $course->post_status ) && $course->post_status != 'publish' ) {
							$post_status = 'private';
						}
					} else {
						$post_status = 'draft';
					}

					$post = array(
						'post_author'	 => !empty( $this->data[ 'uid' ] ) ? $this->data[ 'uid' ] : $user_id,
						// 'post_excerpt' => $_POST['course_excerpt'],
						// 'post_content' => $_POST['course_description'],
						'post_status'	 => $post_status,
						// 'post_title' => $_POST['course_name'],
						'post_type'		 => 'course',
					);

					// If the course already exsists, avoid accidentally wiping out important fields.
					if ( $course ) {
						$post[ 'post_excerpt' ]	 = cp_filter_content( empty( $_POST[ 'course_excerpt' ] ) ? $course->post_excerpt : $_POST[ 'course_excerpt' ]  );
						$post[ 'post_content' ]	 = cp_filter_content( empty( $_POST[ 'course_description' ] ) ? $course->post_content : $_POST[ 'course_description' ]  );
						$post[ 'post_title' ]	 = cp_filter_content( ( empty( $_POST[ 'course_name' ] ) ? $course->post_title : $_POST[ 'course_name' ] ), true );
						if ( !empty( $_POST[ 'course_name' ] ) ) {
							$post[ 'post_name' ] = wp_unique_post_slug( sanitize_title( $post[ 'post_title' ] ), $course->ID, 'publish', 'course', 0 );
						}
					} else {
						$new_course				 = true;
						$post[ 'post_excerpt' ]	 = cp_filter_content( $_POST[ 'course_excerpt' ] );
						if ( isset( $_POST[ 'course_description' ] ) ) {
							$post[ 'post_content' ] = cp_filter_content( $_POST[ 'course_description' ] );
						}
						$post[ 'post_title' ]	 = cp_filter_content( $_POST[ 'course_name' ], true );
						$post[ 'post_name' ]	 = wp_unique_post_slug( sanitize_title( $post[ 'post_title' ] ), 0, 'publish', 'course', 0 );
					}

					if ( isset( $_POST[ 'course_id' ] ) ) {
						$post[ 'ID' ] = $_POST[ 'course_id' ]; //If ID is set, wp_insert_post will do the UPDATE instead of insert
					}

					// Avoid ping backs
					$post[ 'ping_status' ] = 'closed';

					$post_id = wp_insert_post( apply_filters( 'coursepress_pre_insert_post', $post ) );

					$course_order_exists = get_post_meta( $post_id, 'course_order', true );

					if ( empty( $course_order_exists ) ) {
						update_post_meta( $post_id, 'course_order', 0 );
					}

					// Clear cached object because we updated
					self::kill( self::TYPE_COURSE, $post_id );
					self::kill_related( self::TYPE_COURSE, $post_id );

					//Update post meta
					if ( $post_id != 0 ) {
						foreach ( $_POST as $key => $value ) {
							if ( preg_match( "/meta_/i", $key ) ) {//every field name with prefix "meta_" will be saved as post meta automatically
								update_post_meta( $post_id, str_replace( 'meta_', '', $key ), cp_filter_content( $value ) );
							}

							if ( preg_match( "/mp_/i", $key ) ) {
								update_post_meta( $post_id, $key, cp_filter_content( $value ) );
							}

							if ( isset( $_POST[ 'meta_course_category' ] ) ) {
								if ( is_array( $_POST[ 'meta_course_category' ] ) ) {
									$sanitized_array = array();
									foreach ( $_POST[ 'meta_course_category' ] as $cat_id ) {
										$sanitized_array = (int) $cat_id;
									}
									wp_set_post_categories( $post_id, $sanitized_array );
								} else {
									$cat = array( (int) $_POST[ 'meta_course_category' ] );
									if ( $cat ) {
										wp_set_post_categories( $post_id, $cat );
									}
								}
							} // meta_course_category
							//Add featured image
							if ( ( 'meta_featured_url' == $key || '_thumbnail_id' == $key ) && ( isset( $_POST[ '_thumbnail_id' ] ) && is_numeric( $_POST[ '_thumbnail_id' ] ) || isset( $_POST[ 'meta_featured _url' ] ) && $_POST[ 'meta_featured_url' ] !== '' ) ) {

								$course_image_width	 = get_option( 'course_image_width', 235 );
								$course_image_height = get_option( 'course_image_height', 225 );

								$upload_dir_info = wp_upload_dir();

								$fl = trailingslashit( $upload_dir_info[ 'path' ] ) . basename( $_POST[ 'meta_featured_url' ] );

								$image = wp_get_image_editor( $fl ); // Return an implementation that extends <tt>WP_Image_Editor</tt>

								if ( !is_wp_error( $image ) ) {

									$image_size = $image->get_size();

									if ( ( $image_size[ 'width' ] < $course_image_width || $image_size[ 'height' ] < $course_image_height ) || ( $image_size[ 'width' ] == $course_image_width && $image_size[ 'height' ] == $course_image_height ) ) {
										update_post_meta( $post_id, '_thumbnail_id', cp_filter_content( $_POST[ 'meta_featured_url' ] ) );
									} else {
										$ext			 = pathinfo( $fl, PATHINFO_EXTENSION );
										$new_file_name	 = str_replace( '.' . $ext, '-' . $course_image_width . 'x' . $course_image_height . '.' . $ext, basename( $_POST[ 'meta_featured_url' ] ) );
										$new_file_path	 = str_replace( basename( $_POST[ 'meta_featured_url' ] ), $new_file_name, $_POST[ 'meta_featured_url' ] );
										update_post_meta( $post_id, '_thumbnail_id', cp_filter_content( $new_file_path ) );
									}
								} else {
									update_post_meta( $post_id, '_thumbnail_id', cp_filter_content( $_POST[ 'meta_featured_url' ], true ) );
								}
							} else {
								if ( isset( $_POST[ 'meta_featured_url' ] ) && $_POST[ 'meta_featured_url' ] == '' ) {
									update_post_meta( $post_id, '_thumbnail_id', '' );
								}
							}

							//Add instructors
							if ( 'instructor' == $key && isset( $_POST[ 'instructor' ] ) ) {

								//Get last instructor ID array in order to compare with posted one
								$old_post_meta = get_post_meta( $post_id, 'instructors', false );

								if ( serialize( array( $_POST[ 'instructor' ] ) ) !== serialize( $old_post_meta ) || 0 == $_POST[ 'instructor' ] ) {//If instructors IDs don't match
									delete_post_meta( $post_id, 'instructors' );
									cp_delete_user_meta_by_key( 'course_' . $post_id );
								}

								if ( 0 != $_POST[ 'instructor' ] ) {

									update_post_meta( $post_id, 'instructors', cp_filter_content( $_POST[ 'instructor' ] ) ); //Save instructors for the Course


									foreach ( $_POST[ 'instructor' ] as $instructor_id ) {
										$global_option = !is_multisite();
										update_user_option( $instructor_id, 'course_' . $post_id, $post_id, $global_option ); //Link courses and instructors ( in order to avoid custom tables ) for easy MySql queries ( get instructor stats, his courses, etc. )
									}
								} // only add meta if array is sent
							}
						}

						if ( isset( $_POST[ 'meta_paid_course' ] ) ) {
							$this->update_mp_product( $post_id );
						}

						if ( $new_course ) {

							/**
							 * Perform action after course has been created.
							 *
							 * @since 1.2.1
							 */
							do_action( 'coursepress_course_created', $post_id );
						} else {

							/**
							 * Perform action after course has been updated.
							 *
							 * @since 1.2.1
							 */
							do_action( 'coursepress_course_updated', $post_id );
						}

						return $post_id;
					}
				}

				function delete_course( $force_delete = true ) {

					$force_delete = apply_filters( 'coursepress_course_force_delete', $force_delete );

					/**
					 * Allow course deletion to be cancelled when filter returns true.
					 *
					 * @since 1.2.1
					 */
					if ( apply_filters( 'coursepress_course_cancel_delete', false, $this->id ) ) {

						/**
						 * Perform actions if the deletion was cancelled.
						 *
						 * @since 1.2.1
						 */
						do_action( 'coursepress_course_delete_cancelled', $this->id );

						return false;
					}

					// Get object before it gets destroyed
					$course = new Course( $this->id );

					// Clear cached object because we're deleting the object
					self::kill( self::TYPE_COURSE, $this->id );
					self::kill_related( self::TYPE_COURSE, $this->id );

					if ( get_post_type( $this->id ) == 'course' ) {
						wp_delete_post( $this->id, $force_delete ); //Whether to bypass trash and force deletion
					}
					/* Delete all usermeta associated to the course */
					cp_delete_user_meta_by_key( 'course_' . $this->id );
					cp_delete_user_meta_by_key( 'enrolled_course_date_' . $this->id );
					cp_delete_user_meta_by_key( 'enrolled_course_class_' . $this->id );
					cp_delete_user_meta_by_key( 'enrolled_course_group_' . $this->id );

					// Get list of units from cached object
					$course_units = Unit::get_units_from_course( $this->id, 'any' );

					//Delete course units
					foreach ( $course_units as $course_unit ) {
						$unit = new Unit( $course_unit );
						$unit->delete_unit( true );
					}

					//Delete course discussions
					$discussion = new Discussion();
					$discussion->delete_discussion( true, $this->id );

					//Delete course notification
					$notification = new Notification();
					$notification->delete_notification( true, $this->id );

					/**
					 * Perform actions after a course is deleted.
					 *
					 * @var $course  The course object if the ID or post_ title is needed.
					 *
					 * @since 1.2.1
					 */
					do_action( 'coursepress_course_deleted', $course );
				}

				function can_show_permalink() {
					$course = $this->get_course();
					if ( $course->post_status !== 'draft' ) {
						return true;
					} else {
						return false;
					}
				}

				static function get_course_instructors_ids( $course_id = false ) {
					if ( !$course_id ) {
						return false;
					}

					$instructors	 = get_post_meta( $course_id, 'instructors', true );
					$instructor_id_i = 0;
					if ( isset( $instructors ) && !empty( $instructors ) ) {
						foreach ( $instructors as $instructor_id ) {
							$instructors[ $instructor_id_i ] = (int) $instructor_id; //make sure all are numeric values (it wasn't always the case, like for '1')
							if ( $instructor_id == 0 ) {
								unset( $instructors[ $instructor_id_i ] ); //remove zeros and empty values
							}

							$instructor_id_i ++;
						}
					}

					//re-index array
					return !empty( $instructors ) ? $instructors : array();
				}

				static function get_course_students_ids( $course_id = false ) {
					if ( !$course_id ) {
						return false;
					}

					$meta_key = '';
					if ( is_multisite() ) {
						$meta_key = $wpdb->prefix . 'enrolled_course_class_' . $course_id;
					} else {
						$meta_key = 'enrolled_course_class_' . $course_id;
					}

					$args = array(
						/* 'role' => 'student', */
						'meta_key' => $meta_key,
					);

					$wp_user_search = new WP_User_Query( $args );

					$student_id_i = 0;
					if ( isset( $wp_user_search ) ) {
						foreach ( $wp_user_search->results as $student ) {
							$students[ $student_id_i ] = (int) $student->ID; //make sure all are numeric values (it wasn't always the case, like for '1')
							$student_id_i ++;
						}
					}

					//re-index array
					return array_values( $students );
				}

				static function get_course_instructors( $course_id = false ) {
					global $wpdb;
					if ( !$course_id ) {
						return false;
					}

					// Get instructor ID's to return
					$instructors = get_post_meta( $course_id, 'instructors', true );

					$args = array(
						'meta_key'		 => 'course_' . $course_id,
						'meta_value'	 => $course_id,
						'meta_compare'	 => '',
						'meta_query'	 => array(),
						// Only include instructors, not students
						'include'		 => $instructors,
						'orderby'		 => 'display_name',
						'order'			 => 'ASC',
						'offset'		 => '',
						'search'		 => '',
						'number'		 => '',
						'count_total'	 => false,
					);

					if ( is_multisite() ) {
						$args[ 'blog_id' ]	 = get_current_blog_id();
						$args[ 'meta_key' ]	 = $wpdb->prefix . 'course_' . $course_id;
					}

					return get_users( $args );
				}

				static function get_categories( $course_id = false ) {

					if ( !$course_id ) {
						return false;
					}

					$course_category = get_post_meta( $course_id, 'course_category', true );

					if ( !is_array( $course_category ) ) {
						$course_category = array( $course_category );
					}

					$args = array(
						'type'		 => 'link_category',
						'hide_empty' => 0,
						'include'	 => $course_category,
						'taxonomy'	 => array( 'course_category' ),
					);

					return get_categories( $args );
				}

				function change_status( $post_status ) {
					$post = array(
						'ID'			 => $this->id,
						'post_status'	 => $post_status,
					);
					// Update the post status
					wp_update_post( $post );

					// Clear cached object because we updated the object
					self::kill( self::TYPE_COURSE, $this->id );
					self::kill_related( self::TYPE_COURSE, $this->id );

					/**
					 * Perform actions when course status is changed.
					 *
					 * var $this->id  The course id
					 * var $post_status The new status
					 *
					 * @since 1.2.1
					 */
					do_action( 'coursepress_course_status_changed', $this->id, $post_status );
				}

				function get_units( $course_id = '', $status = 'any', $count = false ) {

					if ( $course_id == '' ) {
						$course_id = $this->id;
					}

					// Gets cached object array.
					$units = Unit::get_units_from_course( $course_id, $status, false );

					if ( $count ) {
						return count( $units );
					} else {
						return $units;
					}
				}

				function get_permalink( $course_id = '' ) {
					if ( $course_id == '' ) {
						$course_id = $this->id;
					}

					return get_permalink( $course_id );
				}

				function get_permalink_to_do( $course_id = '' ) {
					global $course_slug;
					global $units_slug;

					if ( $course_id == '' ) {
						$course_id = get_post_meta( $post_id, 'course_id', true );
					}

					$course	 = new Course( $course_id );
					$course	 = $course->get_course();

					$unit_permalink = home_url() . '/' . $course_slug . '/' . $course->post_name . '/' . $units_slug . '/' . $this->details->post_name . '/';

					return $unit_permalink;
				}

				function get_number_of_students( $course_id = '' ) {
					global $wpdb;

					if ( $course_id == '' ) {
						$course_id = $this->id;
					}

					$meta_key = '';
					if ( is_multisite() ) {
						$meta_key = $wpdb->prefix . 'enrolled_course_class_' . $course_id;
					} else {
						$meta_key = 'enrolled_course_class_' . $course_id;
					}

					$args = array(
						/* 'role' => 'student', */
						'meta_key' => $meta_key,
					);

					$wp_user_search = new WP_User_Query( $args );

					return count( $wp_user_search->get_results() );
				}

				function is_populated( $course_id = '' ) {
					if ( $course_id == '' ) {
						$course_id = $this->id;
					}

					$class_size = $this->get_course()->class_size;

					$number_of_enrolled_students = $this->get_number_of_students( $course_id );

					$is_limited = get_post_meta( $course_id, 'limit_class_size', true ) == 'on' ? true : false;

					if ( $is_limited ) {
						if ( $class_size == 0 ) {
							return false;
						} else {
							if ( $class_size > $number_of_enrolled_students ) {
								return false;
							} else {
								return true;
							}
						}
					} else {
						return false;
					}
				}

				function show_purchase_form( $product_id ) {
					echo do_shortcode( '[mp_product product_id="' . $product_id . '" title="true" content="full"]' );
					//echo do_shortcode( '[mp_product_meta product_id="' . $product_id . '"]' );
				}

				function is_user_purchased_course( $product_id, $user_id ) {
					global $mp;

					$args = array(
						'author'		 => $user_id,
						'post_type'		 => 'mp_order',
						'post_status'	 => apply_filters( 'cp_is_user_purchased_mp_order_status', 'order_paid' ),
						'posts_per_page' => '-1'
					);

					$purchases = get_posts( $args );

					foreach ( $purchases as $purchase ) {

						$purchase_records = $mp->get_order( $purchase->ID );

						if ( array_key_exists( $product_id, $purchase_records->mp_cart_info ) ) {
							return true;
						}

						return false;
					}
				}

				function duplicate( $course_id = '' ) {
					global $wpdb;

					if ( $course_id == '' ) {
						$course_id = $this->id;
					}

					/**
					 * Allow course duplication to be cancelled when filter returns true.
					 *
					 * @since 1.2.1
					 */
					if ( apply_filters( 'coursepress_course_cancel_duplicate', false, $course_id ) ) {

						/**
						 * Perform actions if the duplication was cancelled.
						 *
						 * @since 1.2.1
						 */
						do_action( 'coursepress_course_duplicate_cancelled', $course_id );

						return false;
					}

					/* Duplicate course and change some data */

					$new_course		 = $this->get_course();
					$old_course_id	 = $new_course->ID;
					unset( $new_course->ID );
					unset( $new_course->guid );

					$new_course->post_author = get_current_user_id();
					$new_course->post_status = 'private';
					$new_course->post_name	 = $new_course->post_name . '-copy';
					$new_course->post_title	 = $new_course->post_title . ' (copy)';

					$new_course_id = wp_insert_post( $new_course );

					/*
					 * Duplicate course post meta
					 */

					if ( !empty( $new_course_id ) ) {
						$post_metas = get_post_meta( $old_course_id );
						foreach ( $post_metas as $key => $meta_value ) {
							$value	 = array_pop( $meta_value );
							$value	 = maybe_unserialize( $value );
							update_post_meta( $new_course_id, $key, $value );
						}
					}

					delete_post_meta( $new_course_id, 'me ta_mp_pr oduct_id' );
					delete_post_meta( $new_course_id, 'mp_product_id' );
					delete_post_meta( $new_course_id, 'mp_sale_price' );
					delete_post_meta( $new_course_id, 'mp_price' );
					delete_post_meta( $new_course_id, 'mp_is_sale' );
					delete_post_meta( $new_course_id, 'mp_sku' );
					delete_post_meta( $new_course_id, 'auto_sku' );
					delete_post_meta( $new_course_id, 'paid_course' );
					delete_post_meta( $new_course_id, 'marketpress_product' );


					$units = $this->get_units( $old_course_id );

					foreach ( $units as $unit ) {
						$unt = new Unit( $unit->ID );
						$unt->duplicate( $unit->ID, $new_course_id );
					}

					do_action( 'coursepress_course_duplicated', $new_course_id );
				}

				public function enrollment_details() {

					$this->enroll_type			 = get_post_meta( $this->id, 'enroll_type', true );
					$this->course_start_date	 = get_post_meta( $this->id, 'course_start_date', true );
					$this->course_end_date		 = get_post_meta( $this->id, 'course_end_date', true );
					$this->enrollment_start_date = get_post_meta( $this->id, 'enrollment_start_date', true );
					$this->enrollment_end_date	 = get_post_meta( $this->id, 'enrollment_end_date', true );
					$this->open_ended_course	 = 'off' == get_post_meta( $this->id, 'open_ended_course', true ) ? false : true;
					$this->open_ended_enrollment = 'off' == get_post_meta( $this->id, 'open_ended_enrollment', true ) ? false : true;
					$this->prerequisite			 = get_post_meta( $this->id, 'prerequisite', true );

					$this->is_paid	 = get_post_meta( $this->id, 'paid_course', true );
					$this->is_paid	 = $this->is_paid && 'on' == $this->is_paid ? true : false;

					$this->course_started		 = strtotime( $this->course_start_date ) <= current_time( 'timestamp', 0 ) ? true : false;
					$this->enrollment_started	 = strtotime( $this->enrollment_start_date ) <= current_time( 'timestamp', 0 ) ? true : false;
					$this->course_expired		 = strtotime( $this->course_end_date ) < current_time( 'timestamp', 0 ) ? true : false;
					$this->enrollment_expired	 = strtotime( $this->enrollment_end_date ) < current_time( 'timestamp', 0 ) ? true : false;
					$this->full					 = $this->is_populated();
				}

				public static function get_allowed_pages( $course_id ) {

					$pages = array(
						'course_discussion'	 => get_post_meta( $course_id, 'allow_course_discussion', true ),
						'workbook'			 => get_post_meta( $course_id, 'allow_workbook_page', true ),
					);

					return $pages;
				}

				public static function get_course_time_estimation( $course_id, $status = 'any' ) {

					$course_time	 = '';
					$course_seconds	 = 0;
					$units			 = Unit::get_units_from_course( $course_id, $status, false );

					foreach ( $units as $unit ) {
						$unit_details	 = new Unit( $unit->ID );
						$unit_time		 = $unit_details->get_unit_time_estimation( $unit->ID );

						$min_sec = explode( ':', $unit_time );
						if ( isset( $min_sec[ 0 ] ) ) {
							$course_seconds += intval( $min_sec[ 0 ] ) * 60;
						}
						if ( isset( $min_sec[ 1 ] ) ) {
							$course_seconds += intval( substr( $min_sec[ 1 ], 0, 2 ) );
						}
					}
					$total_seconds	 = round( $course_seconds );
					$formatted_time	 = sprintf( '%02d:%02d:%02d', ($total_seconds / 3600 ), ($total_seconds / 60 % 60 ), $total_seconds % 60 );

					$course_time = apply_filters( 'coursepress_course_get_time_estimation', $formatted_time, $total_seconds, $course_id );

					return $course_time;
				}

			}

		} 

							

							

							

							

							

							

							

							

							

							

							

							

							

							

							

							

							 