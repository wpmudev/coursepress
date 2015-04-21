<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'CP_Basic_Certificate' ) ) {

	class CP_Basic_Certificate {

		private static $default_css = ".basic_certificate{\n}\n\n.basic_certificate .first_name {\n}\n\n.basic_certificate .last_name {\n}\n\n.basic_certificate .course_name {\n}\n\n.basic_certificate .completion_date {\n}\n\n.basic_certificate .certificate_number {\n}\n\n.basic_certificate .unit_list {\n}\n\n";

		public static function init_settings() {
			add_filter( 'coursepress_settings_new_menus', array( 'CP_Basic_Certificate', 'add_settings_item' ) );
			add_action( 'coursepress_settings_menu_basic_certificate', array( 'CP_Basic_Certificate', 'render_settings') );
			//add_action( 'coursepress_email_settings', array( 'CP_Basic_Certificate', 'render_email_settings') );
			//add_action( 'coursepress_update_settings', array( 'CP_Basic_Certificate', 'process_email_settings'), 10, 2 );
		}

		public static function init_front() {
			add_action( 'coursepress_pre_parse_action', array( 'CP_Basic_Certificate', 'redirect_to_pdf' ) );
		}

		public static function redirect_to_pdf() {
			if( isset( $_GET['action'] ) && 'view_certificate' == $_GET['action'] && isset( $_GET['course_id'] ) ) {
				$course_id = (int) $_GET['course_id'];
				if( $course_id > 0 ) {
					$certificate = CP_Basic_Certificate::make_pdf( get_current_user_id(), $course_id, true );
					wp_redirect( $certificate );
					exit;
				}
			}
		}

		public static function add_settings_item( $menus ) {

			$insert_before = 'shortcodes';
			$inserted = false;
			$new_menu = array();

			foreach( $menus as $key => $value ) {
				if( $key != $insert_before ) {
					$new_menu[ $key ] = $value;
				} else {
					if( ! $inserted) {
						$new_menu['basic_certificate'] = __( 'Basic Certificate', 'cp' );
						$inserted = true;
					}
					$new_menu[ $key ] = $value;
				}
			}

			if( ! $inserted) {
				$new_menu['basic_certificate'] = __( 'Basic Certificate', 'cp' );
			}

			return $new_menu;

		}

		public static function render_settings() {
			if( ! CoursePress_Capabilities::is_pro() ) {
				return;
			}
			self::process_submit(); ?>

			<div id="poststuff" class="metabox-holder m-settings email-settings cp-wrap" xmlns="http://www.w3.org/1999/html">
			<form action='' method='post'>

				<input type='hidden' name='action' value='update_basic_certificate'/>

				<?php
				wp_nonce_field( 'update_basic_certificate' );
				?>

				<div class="postbox">
					<h3 class="hndle" style='cursor:auto;'><span><?php _e( 'Certificate Options', 'cp' ); ?></span></h3>
					<div class="inside">
						<table class="form-table">
							<tbody>
							<tr valign="top">
								<th scope="row">
									<?php _e( 'Enable Basic Certificate', 'cp' ); ?>
									<a class="help-icon" href="javascript:;"></a>
										<div class="tooltip">
											<div class="tooltip-before"></div>
											<div class="tooltip-button">&times;</div>
											<div class="tooltip-content">
												<?php _e( 'Adds a "Certificate" link to completed courses on the student dashboard.', 'cp' ); ?>
											</div>
										</div>
								</th>
								<td>
									<input type='checkbox' value="1" name='cert_field_basic_certificate_enabled' <?php echo( checked( self::option( 'basic_certificate_enabled' ) ) ); ?> />
								</td>
							</tr>
							<!--<tr valign="top">-->
								<!--<th scope="row">--><?php //_e( 'Email certificate when course is completed.', 'cp' ); ?><!--</th>-->
								<!--<td>-->
									<!--<input type='checkbox' value="1" name='cert_field_auto_email' --><?php //echo( checked( self::option( 'auto_email' ) ) ); ?><!-- />-->
								<!--</td>-->
							<!--</tr>-->

							</tbody>
						</table>
					</div>
				</div>

				<div class="postbox">
					<h3 class="hndle" style='cursor:auto;'><span><?php _e( 'Certificate Layout', 'cp' ); ?></span></h3>

					<div class="inside">
						<p class="description"><?php _e( 'Use the editor below to create the layout of your certificate.', 'cp' ); ?></p>
						<p class="description"><?php _e( 'These codes will be replaced with actual data: FIRST_NAME, LAST_NAME, COURSE_NAME, COMPLETION_DATE, CERTIFICATE_NUMBER, UNIT_LIST', 'cp' ); ?></p>
						<table class="form-table">
							<tbody id="items">
								<tr>
									<td>
										<?php

										$editor_name    = "cert_field_certificate_content";
										$editor_id      = "cert_field_certificate_content";
										$editor_content = stripslashes( self::certificate_content() );

										$args = array(
											"textarea_name" => $editor_name,
											"textarea_rows" => 10,
											'wpautop'       => true,
											'quicktags'     => true
										);
										// Filter $args before showing editor
										//$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );
										wp_editor( $editor_content, $editor_id, $args );
										?>
									</td>
								</tr>
								<tr>
									<td>

										<strong><?php esc_html_e( 'Background Image', 'cp' ); ?></strong>
										<a class="help-icon" href="javascript:;"></a>
										<div class="tooltip">
											<div class="tooltip-before"></div>
											<div class="tooltip-button">&times;</div>
											<div class="tooltip-content">
												<?php echo sprintf( __( 'The image will be resized to fit the full page. For best results use 1:1414 as the ratio for image dimensions.<br /><br /><strong>Examples:</strong><br />595x842px (Portrait 72dpi)<br />1754x1240px (Landscape 150dpi)<br />2480x3508px (Portrait 300dpi).', 'cp' ) ) ; ?>
											</div>
										</div><br />
										<?php
										$supported_image_extensions = implode( ", ", cp_wp_get_image_extensions() );
										?>
										<div class="certificate_background_image_holder">
											<input class="image_url certificate_background_url" type="text" size="36" name="cert_field_background_url" value="<?php
											echo esc_attr( self::option( 'background_url' ) );
											?>" placeholder="<?php _e( 'Add Image URL or Browse for Image', 'cp' ); ?>"/>
											<input class="certificate_background_button button-secondary" type="button" value="<?php _e( 'Browse', 'cp' ); ?>"/>
											<div class="invalid_extension_message"><?php echo sprintf( __( 'Extension of the file is not valid. Please use one of the following: %s', 'cp' ), $supported_image_extensions ); ?></div>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<p>
											<strong><?php esc_html_e( 'Content Padding', 'cp' ); ?></strong>
											<span class="description"><?php esc_html_e( 'Can be any CSS units. E.g. "0.2em"', 'cp'); ?></span>
										</p>

										<span><?php esc_html_e( 'Top', 'cp' ) ?></span><input type="text" size="6" style="width: 80px;" class="padding_top" name="cert_field_padding_top" value="<?php echo esc_html( self::option('padding_top') ); ?>" />
										<span><?php esc_html_e( 'Bottom', 'cp' ) ?></span><input type="text" size="6" style="width: 80px;" class="padding_bottom" name="cert_field_padding_bottom" value="<?php echo esc_html( self::option('padding_bottom') ); ?>" />
										<span><?php esc_html_e( 'Left', 'cp' ) ?></span><input type="text" size="6" style="width: 80px;" class="padding_left" name="cert_field_padding_left" value="<?php echo esc_html( self::option('padding_left') ); ?>" />
										<span><?php esc_html_e( 'Right', 'cp' ) ?></span><input type="text" size="6" style="width: 80px;" class="padding_right" name="cert_field_padding_right" value="<?php echo esc_html( self::option('padding_right') ); ?>" />
									</td>
								</tr>
								<tr>
									<td>
										<strong><?php esc_html_e( 'Page Orientation', 'cp' ); ?></strong><br />
										<?php $selected_type = self::option( 'orientation' ); ?>
										<select name="cert_field_orientation" style="width: max-width: 200px;" id="cert_field_orientation">
											<option value="L" <?php selected( $selected_type, 'L', true ); ?>><?php _e( 'Landscape', 'cp' ); ?></option>
											<option value="P" <?php selected( $selected_type, 'P', true ); ?>><?php _e( 'Portrait', 'cp' ); ?></option>
										</select>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<!--/inside-->

				</div>
				<!--/postbox-->

				<!-- Maybe render CSS, it is limited with TCPDF -->
				<?php // self::render_css_setting(); ?>

				<p class="save-shanges">
					<?php submit_button( __( 'Save Changes', 'cp' ) ); ?>
				</p>

			</form>
		</div>

		<?php
		}

		private static function render_css_setting() {
			?>
			<div class="postbox">
				<h3 class="hndle" style='cursor:auto;'><span><?php _e( 'CSS', 'cp' ); ?></span></h3>
				<div class="inside">
					<p class="description"><?php _e( 'You can specify individual CSS rules for each of the certificate fields.<br />The follow CSS classes are available: .first_name, .last_name, .course_name, .completion_date, .certificate_number, .unit_list.<br />They are all wrapped in the .basic_certificate DIV tag.', 'cp' ); ?></p>
					<table class="form-table">
						<tbody id="items">
						<tr>
							<td>
								<?php
								$editor_content = self::certificate_styles();
								?>

								<textarea name="cert_field_styles" style="width: 100%; height: 200px"><?php echo $editor_content; ?></textarea>

							</td>
						</tr>

						</tbody>
					</table>
				</div>
				<!--/inside-->
			</div>
			<!--/postbox-->
		<?php
		}

		public static function render_email_settings() {
		?>
			<div class="postbox">
				<h3 class="hndle" style='cursor:auto;'><span><?php _e( 'Basic Certificate E-mail', 'cp' ); ?></span></h3>

				<div class="inside">
					<p class="description"><?php _e( 'E-mail to send certificate to student upon course completion. (if enabled)', 'cp' ); ?></p>
					<table class="form-table">
						<tbody id="items">
						<tr>
							<th><?php _e( 'From Name', 'cp' ); ?></th>
							<td>
								<input type="text" name="cert_field_from_name" value="<?php echo esc_attr( self::option( 'from_name' ) ); ?>"/>
							</td>
						</tr>

						<tr>
							<th><?php _e( 'From E-mail', 'cp' ); ?></th>
							<td>
								<input type="text" name="cert_field_from_email" value="<?php echo esc_attr( self::option( 'from_email' ) ); ?>"/>
							</td>
						</tr>

						<tr>
							<th><?php _e( 'E-mail Subject', 'cp' ); ?></th>
							<td>
								<input type="text" name="cert_field_email_subject" value="<?php echo esc_attr( self::option( 'email_subject' ) ); ?>"/>
							</td>
						</tr>

						<tr>
							<th><?php _e( 'E-mail Content', 'cp' ); ?></th>
							<td>
								<p class="description"><?php _e( 'These codes will be replaced with actual data: FIRST_NAME, LAST_NAME, COMPLETION_DATE, CERTIFICATE_NUMBER, UNIT_LIST, COURSE_NAME, COURSE_EXCERPT, COURSE_ADDRESS, WEBSITE_ADDRESS, WEBSITE_NAME', 'cp' ); ?></p>
								<?php
								$editor_name    = "cert_field_email_content";
								$editor_id      = "cert_field_email_content";
								$editor_content = stripslashes( self::option( 'email_content' ) );

								$args = array( "textarea_name" => $editor_name, "textarea_rows" => 10, 'wpautop' => true );
								 //Filter $args before showing editor
								//$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );
								wp_editor( $editor_content, $editor_id, $args );
								?>
							</td>
						</tr>

						</tbody>
					</table>
				</div>
				<!--/inside-->
			</div>
			<!--/postbox-->
		<?php
		}

		public static function process_submit( $verified = false, $vars = array() ) {

			if( ( 'update_basic_certificate' == $_POST['action'] && isset( $_POST['_wpnonce'] ) && current_user_can( 'manage_options' ) ) || $verified ) {

				if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'update_basic_certificate' ) || $verified ) {

					if( ! $verified ) {
						$vars = $_POST;
					}

					// Update all fields...
					foreach( $vars as $key => $value ) {
						if( preg_match( '/^cert_field_/', $key ) ) {
							$option = str_replace( 'cert_field_', '', $key );
							self::option( $option, $value );
						}
					}

					// and make sure these checkboxes are updated too...
					if( ! $verified ) {
						$check_fields = array(
							'cert_field_basic_certificate_enabled',
							'cert_field_auto_email',
						);
					}
					$post_array = array_keys( $vars );

					foreach( $check_fields as $field ) {
						if( ! in_array( $field, $post_array ) ) {
							$option = str_replace( 'cert_field_', '', $field );
							self::option( $option, 0 );
						}
					}

				}

			}

		}

		//public static function process_email_settings( $tab, $vars ) {
		//	if( 'email' == $tab ) {
		//		self::process_submit( true, $vars );
		//	}
		//}

		// Will be used if enabling CSS
		public static function certificate_styles() {
			$content = esc_textarea( stripslashes( self::option( 'styles' ) ) );
			$content = empty( $content ) ? self::$default_css : $content;
			return $content;
		}

		public static function certificate_content() {
			return self::option( 'certificate_content' );
		}

		public static function make_pdf( $student, $course, $url = false ) {

			// Use CoursePress_PDF which extends TCPDF
			require_once( CoursePress::instance()->plugin_dir . 'includes/classes/class.coursepress-pdf.php' );

			// Get the objects from IDs if passed as IDs.
			if( ! is_object( $course ) && 0 != (int) $course ) {
				$course = new Course( $course );
			}
			if( ! is_object( $student ) && 0 != (int) $student ) {
				$student = new Student( $student );
			}

			$course_completed_details = self::_get_fields( $student, $course );

			$certificate_title = sprintf( __('Certificate %s', 'cp' ), $course_completed_details['certificate_number'] );

			// Get the styles and replace fields if they exist
			$top = self::option( 'padding_top' );
			$bototm = self::option( 'padding_bottom' );
			$left = self::option( 'padding_left' );
			$right = self::option( 'padding_right' );
			$style = sprintf( ".basic_certificate{ padding: %s %s %s %s; }", $top, $right, $bototm, $left ); // has to be this order for CSS shortcut
			$style .= self::certificate_styles();
			$style = ! empty( $style ) ? '<style>' . $style . '</style>' : $style;

			$html = '<table class="basic_certificate"><tr><td>' . stripslashes( self::certificate_content() ) . '</td></tr></table>';

			$html = self::_replace_fields( $html, $student, $course, true, $course_completed_details );

			// create new PDF document
			$pdf = new CoursePress_PDF( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );
			return $pdf->make_pdf( $html, array(
					'title' => $certificate_title,
					'style' => $style,
					'image' => self::option( 'background_url' ),
					'orientation' => self::option( 'orientation' ),
					'uid' => $course_completed_details['certificate_number'],
					'url' => $url,
				)
			);
		}

		// Gets or sets the certificate settings
		public static function option( $key, $value = null, $default = null ) {
			$options = get_option( 'coursepress_basic_certificate' );
			if( empty( $options ) ) {
				$options = self::_default_options();
			}

			if( null === $value ) {

				return isset( $options[ $key ] ) ? $options[ $key ] : $default;

			} else {

				$options[ $key ] = $value;
				update_option( 'coursepress_basic_certificate', $options );

			}

		}

		private static function _default_options() {
			$options = array(
				'certificate_content' => self::_default_certificate_content(),
				'basic_certificate_enabled' => 1,
				'auto_email' => 0,
				'background_url' => '',
				'padding_top' => 0,
				'padding_right' => 0,
				'padding_bottom' => 0,
				'padding_left' => 0,
				'styles' => '',
				'orientation' => 'L',
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'email_subject' => self::_default_email_subject(),
				'email_content' => self::_default_email_content(),
			);

			return $options;
		}

		private static function _default_certificate_content() {
			// Certificate Content
			$fields = array(
				'FIRST_NAME',
				'LAST_NAME',
				'COURSE_NAME',
				'COMPLETION_DATE',
				'CERTIFICATE_NUMBER',
				'UNIT_LIST',
			);

			$default_certification_content = sprintf( __( '%1$s %2$s
				has successfully completed the course

				%3$s

				Date: %4$s
				Certificate no.: %5$s', 'cp' ), $fields[0], $fields[1], $fields[2], $fields[3], $fields[4] );
			return $default_certification_content;
		}

		private static function _default_email_subject() {
			return sprintf( __( '[%s] Congratulations. You passed your course.', 'cp' ), get_option( 'blogname' ) );
		}

		private static function _default_email_content() {

			$default_instructor_invitation_email = sprintf( __(
				'Hi %1$s,

				Congratulations! You have completed the course: %2$s

				Please find attached your certificate of completion.'
				, 'cp' ), 'FIRST_NAME', 'COURSE_NAME'
			);

			return get_option( 'instructor_invitation_email', $default_instructor_invitation_email );
		}

		private static function _get_fields( $student, $course ) {

			// Note ID and id is inconsistent
			$course_completed = Student_Completion::is_course_complete( $student->ID, $course->id );
			$course_completed_details = get_user_option( '_course_' . $course->id . '_completed', $student->ID );

			if( empty( $course ) || empty( $student ) || ! $course_completed ) {
				return false;
			}

			$units = $course->get_units();

			$show_units = false;
			$unit_list = '<ul class="unit_list">';
			foreach( $units as $unit ) {
				$show_units = true;
				$unit_list .= '<li>' . sanitize_text_field( $unit->post_title ) . '</li>';
			}
			$unit_list .= '</ul>';
			if( ! $show_units ) {
				$unit_list = '';
			}

			$fields = array(
				'first_name' => $student->first_name,
				'last_name' => $student->last_name,
				'course_name' => $course->details->post_title,
				'completion_date' => date_i18n( get_option( 'date_format '), $course_completed_details['date_completed'] ),
				'certificate_number' => $course_completed_details['certificate_number'],
				'unit_list' => $unit_list,
			);

			return $fields;

		}

		private static function _replace_fields( $content, $student, $course, $html = true, $fields = false ) {

			if( ! $fields ) {
				$fields = self::_get_fields( $student, $course );
			}

			if( empty( $fields ) ) {
				return false;
			}

			foreach( $fields as $key => $value ) {
				if( $html ) {
					$str_value = '<span class="' . $key . '">' . $value . '</span>';
				} else {
					$str_value = $value;
				}
				$content = str_replace( strtoupper( $key ), $str_value, $content );
			}

			return $content;
		}

		public static function get_certificate_link( $student_id, $course_id, $link_title, $pre = '', $post = '', $show_link = false ) {

			if( ! $show_link ) {
				$show_link = CP_Basic_Certificate::option( 'basic_certificate_enabled' );
				$show_link = ! empty( $show_link ) ? true : false;
				$show_link = CoursePress_Capabilities::is_pro() ? $show_link : false;
			}

			if( $show_link ) {
				if ( Student_Completion::is_course_complete( $student_id, $course_id ) ) {
					$certificate_permalink = esc_url( add_query_arg( array( 'action' => 'view_certificate', 'course_id' => $course_id ), get_permalink( $course_id ) ) );
					return esc_html( $pre ) . '<a target="_blank" href="' . esc_url( $certificate_permalink ) . '">' . esc_html( $link_title ) . '</a>' . esc_html( $post );
				}
			}
			return '';
		}


	}

}