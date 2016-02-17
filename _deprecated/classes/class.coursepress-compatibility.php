<?php

/**
 * @copyright Incsub ( http://incsub.com/ )
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 ( GPL-2.0 )
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
if ( ! class_exists( 'CoursePress_Compatibility' ) ) {

	/**
	 * CoursePress class for dealing with WordPress version compatibility
	 *
	 * @since 1.2.1
	 *
	 */
	class CoursePress_Compatibility {

		private $version;
		private $plugin_url;
		private $min_version = false;
		private $editor_options = array();

		function __construct() {
			$this->plugin_url = $GLOBALS['coursepress_url'];
			$this->version    = $GLOBALS['coursepress_version'];

			$this->editor_options = array(
				'quicktags' => false,
			);

			// Are we dealing with 3.9 and up?
			if ( self::is_3_9_up() ) {
				$this->min_version = 3.9;
			} else {
				$this->min_version = 3.8;
			}

			// Administration area
			if ( is_admin() ) {

				add_action( 'coursepress_editor_compatibility', array( &$this, 'coursepress_editor_compatibility' ) );

				// Fix some anomalies on unit builder page
				add_action( 'admin_init', array( &$this, 'alter_unit_builder_editor' ) );
			}
			add_action( 'coursepress_editor_compatibility', array( &$this, 'coursepress_editor_compatibility' ) );
			// Public area

			/**
			 * Admin header actions.
			 *
			 * Compatibility mode.
			 *
			 * @since 1.2.1
			 */
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_header_actions' ) );

			/**
			 * Admin footer scripts.
			 *
			 * Load specific scripts in the footer.
			 *
			 * @since 1.2.1
			 */
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_footer_scripts' ) );
		}

		/**
		 * Check for WordPress 3.9 and up
		 *
		 * @since 1.2.1
		 */
		public static function is_3_9_up() {
			global $wp_version;

			if ( 3.9 <= (double) $wp_version ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Alter editor for Unit pages.
		 *
		 * Because of the complexities of the unit builder we might need to suppress
		 * or alter some editor options.
		 *
		 * @since 1.2.1
		 */
		public function alter_unit_builder_editor() {
			if ( isset( $_GET['page'] ) && 'course_details' == $_GET['page'] && isset( $_GET['tab'] ) && 'units' == $_GET['tab'] ) {
				/*
				 * Multiple editors on the same page is causing conflicts with Visual/Text tab selection
				 * so we need to disabled it.  
				 */
				if ( $this->is_3_9_up() ) {
					$this->editor_options['quicktags'] = false;
				}
			}
		}

		/**
		 * Hook WordPress Editor filters and actions.
		 *
		 * @since 1.2.1
		 */
		public function coursepress_editor_compatibility() {

			// Version Specific Hooks

			switch ( $this->min_version ) {

				// Do 3.9+ specific hooks for the editor
				case 3.9:
					add_filter( 'coursepress_element_editor_args', array(
						&$this,
						'cp_element_editor_args_39plus'
					), 10, 3 );
					add_action( 'coursepress_editor_options', array( &$this, 'prepare_coursepress_editor_39plus' ) );
					break;

				// Do 3.8 specific hooks for the editor				
				case 3.8:
					// $this->editor_options['quicktags'] = true;
					add_filter( 'coursepress_element_editor_args', array(
						&$this,
						'cp_element_editor_args_38'
					), 10, 3 );
					add_filter( 'coursepress_format_tinymce_plugins', array(
						&$this,
						'cp_format_tinymce_plugins_38'
					), 10, 1 );
					add_action( 'coursepress_editor_options', array( &$this, 'prepare_coursepress_editor_38' ) );
					break;
			}

			// Default Hooks

			/**
			 * Apply some styles to the WordPress editor (AJAX).
			 *
			 * Keeps consistency across course setup and unit setup.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'mce_css', array( &$this, 'mce_editor_style' ) );

			/**
			 * Add keydown() event listener for WP Editor.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'tiny_mce_before_init', array( &$this, 'init_tiny_mce_listeners' ) );

			/**
			 * Listen to dynamic editor requests.
			 *
			 * Used on unit page in admin.
			 *
			 * @since 1.0.0
			 */
			add_action( 'wp_ajax_dynamic_wp_editor', array( &$this, 'dynamic_wp_editor' ) );
		}

		function admin_header_actions() {

			/* Adding menu icon font */
			if ( $this->min_version >= 3.8 ) {
				wp_register_style( 'cp-38', CoursePress::instance()->plugin_url . 'css/admin-icon.css' );
				wp_enqueue_style( 'cp-38' );
			}


			if ( isset( $_GET['page'] ) ) {
				$page = isset( $_GET['page'] );
			} else {
				$page = '';
			}

			if ( $page == 'courses' || $page == 'course_details' || $page == 'instructors' || $page == 'students' || $page == 'assessment' || $page == 'reports' || $page == 'settings' || ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'course_category' ) ) {
				add_filter( 'tiny_mce_before_init', array( &$this, 'cp_format_TinyMCE' ) );

				wp_enqueue_style( 'editor-buttons' );
			}
		}

		public function admin_footer_scripts( $hook ) {

			// Address issue specifically caused by WPEngine and relative URLs
			if ( 'coursepress-pro_page_course_details' == $hook ) {
				wp_enqueue_script( 'coursepress_fix_editor', $this->plugin_url . 'js/footer-editor-fixes.js', array( 'jquery' ), $this->version, true );
			}
		}

		/**
		 * Create a listener for TinyMCE change event
		 *
		 */
		function init_tiny_mce_listeners( $initArray ) {
			if ( is_admin() ) {
				$detect_pages = array(
					'coursepress_page_course_details',
					'coursepress-pro_page_course_details',
				);

				$page = get_current_screen()->id;
				$tab  = empty( $_GET['tab'] ) ? '' : $_GET['tab'];

				if ( in_array( $page, $detect_pages ) ) {

					$initArray['height']              = '360px';
					$initArray['relative_urls']       = false;
					$initArray['url_converter']       = false;
					$initArray['url_converter_scope'] = false;

					if ( 3.8 < $this->min_version ) {
						$initArray['setup'] = 'function( ed ) {
								ed.on( \'keydown\', function( args ) {
									cp_editor_key_down( ed, \'' . $page . '\', \'' . $tab . '\' );
								} );
						}';
					} else {
						$initArray['setup'] = 'function( ed ) {
								ed.onKeyDown.add(function(ed, evt) {
								  cp_editor_key_down( ed, \'' . $page . '\', \'' . $tab . '\' );
								});
						}';
					}
				}
			}

			return $initArray;
		}

		// CoursePress CSS styles for TinyMCE
		function mce_editor_style( $url ) {
			global $wp_version;
			// Only on these pages
			$detect_pages = array(
				'coursepress_page_course_details',
				'coursepress-pro_page_course_details',
			);

			$page = get_current_screen()->id;
			$tab  = empty( $_GET['tab'] ) ? '' : $_GET['tab'];

			if ( in_array( $page, $detect_pages ) ) {

				if ( ! empty( $url ) ) {
					$url .= ',';
				}

				$url .= CoursePress::instance()->plugin_url . 'css/editor_style_fix.css,';

				if ( 3.9 <= (double) $wp_version ) {

				} else {
					$url .= CoursePress::instance()->plugin_url . 'css/editor_style_fix_38.css,';
				}
			}

			return $url;
		}

		/* Retrieve wp_editor dynamically ( using in unit admin ) */

		function dynamic_wp_editor() {

			$editor_name    = ( isset( $_GET['module_name'] ) ? $_GET['module_name'] : '' ) . "_content[]";
			$editor_id      = ( ( isset( $_GET['rand_id'] ) ? $_GET['rand_id'] : rand( 1, 9999 ) ) );
			$editor_content = htmlspecialchars_decode( ( isset( $_GET['editor_content'] ) ? $_GET['editor_content'] : '' ) );

			$args = array(
				"textarea_name" => $editor_name,
				"textarea_rows" => 4,
				"teeny"         => true,
				"editor_class"  => 'cp-editor cp-dynamic-editor',
			);

			if ( $this->editor_options['quicktags'] ) {
				$args['quicktags'] = $this->editor_options['quicktags'];
			}

			// Filter $args before showing editor
			$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

			wp_editor( $editor_content, $editor_id, $args );

			exit;
		}

		function cp_format_TinyMCE( $in ) {
			$plugins = apply_filters( 'coursepress_format_tinymce_plugins', $this->get_plugins() );
			$plugins = implode( ',', $plugins );

			$in['menubar']  = false;
			$in['plugins']  = $plugins;
			$in['toolbar1'] = implode( ',', $this->get_buttons() );
			$in['toolbar2'] = '';
			$in['toolbar3'] = '';
			$in['toolbar4'] = '';

			return $in;
		}

		// TinyMCE 4.0
		function cp_element_editor_args_39plus( $args, $editor_name, $editor_id ) {
			$args['quicktags'] = $this->editor_options['quicktags'];

			return $args;
		}

		function prepare_coursepress_editor_39plus() {
			//array( 'inlinepopups', 'tabfocus', 'paste', 'media', 'fullscreen', 'wordpress', 'wpeditimage', 'wpgallery', 'wplink', 'wpdialogs', 'textcolor', 'hr' )
			wp_localize_script( 'courses_bulk', 'coursepress_editor', array(
				'plugins'   => apply_filters( 'coursepress_format_tinymce_plugins', $this->get_plugins() ),
				'toolbar'   => $this->get_buttons(),
				'theme'     => apply_filters( 'coursepress_editor_theme', 'modern' ), // filter it for themers
				'skin'      => apply_filters( 'coursepress_editor_skin', 'wp_theme' ), // filter it for themers
				'quicktags' => $this->editor_options['quicktags'], // are we using quicktags?
			) );
		}

		// TinyMCE 3.5.9
		function cp_element_editor_args_38( $args, $editor_name, $editor_id ) {
			$args['quicktags'] = $this->editor_options['quicktags'];

			// unset( $args[ "quicktags" ] );//it doesn't work in 3.8 for some reason - should peform further checks
			return $args;
		}

		function prepare_coursepress_editor_38() {
			wp_localize_script( 'courses_bulk', 'coursepress_editor', array(
				'plugins'   => apply_filters( 'coursepress_format_tinymce_plugins', $this->get_plugins() ),
				'toolbar'   => $this->get_buttons(),
				'theme'     => apply_filters( 'coursepress_editor_theme', 'advanced' ), // filter it for themers
				'skin'      => apply_filters( 'coursepress_editor_skin', 'wp_theme' ), // filter it for themers
				'quicktags' => false, // Always false for WP 3.8 dynamic editor
			) );
		}

		function get_plugins() {
			// WP default plugins
			// array( 'inlinepopups', 'tabfocus', 'paste', 'media', 'fullscreen', 'wordpress', 'wpeditimage', 'wpgallery', 'wplink', 'wpdialogs' );
			// WP teeny plugins
			// array('inlinepopups', 'fullscreen', 'wordpress', 'wplink', 'wpdialogs' )			
			$plugins = array(
				// 'inlinepopups',
				// 'tabfocus',
				// 'paste',
				// 'media',
				// 'fullscreen',
				// 'wordpress',
				// 'wpeditimage',
				// 'wpgallery',
				'wplink',
				// 'wpdialogs',
				'textcolor', // not in 3.8
				'hr' // not in 3.8
			);

			return $plugins;
		}

		function get_buttons() {
			$buttons = array(
				'bold',
				'italic',
				'underline',
				'blockquote',
				'hr',
				'strikethrough',
				'bullist',
				'numlist',
				'subscript',
				'superscript',
				'alignleft',
				'aligncenter',
				'alignright',
				'alignjustify',
				'outdent',
				'indent',
				'link',
				'unlink',
				'forecolor',
				'backcolor',
				'undo',
				'redo',
				'removeformat',
				'formatselect',
				'fontselect',
				'fontsizeselect'
			);

			return $buttons;
		}

		function cp_format_tinymce_plugins_38( $plugins ) {
			$not_allowed = array( 'textcolor', 'hr' );
			foreach ( $plugins as $key => $value ) {
				if ( in_array( $value, $not_allowed ) ) {
					unset( $plugins[ $key ] );
				}
			}

			return $plugins;
		}

	}

}

$coursepress_compatibility = new CoursePress_Compatibility();
