<?php
/**
 * Admin menu
 *
 * @since 2.0
 **/
class CoursePress_Admin_Controller_Menu {
	var $parent_slug 				= '';
	var $slug 						= '';
	protected $cap 					= 'manage_options'; // Default to admin cap
	var $description 				= '';
	var $with_editor 				= false;

	/**
	 * @var (bool)		A helper var to identify if current page is the page set for this menu.
	 **/
	var $is_page_loaded 			= false;

	var $scripts 					= array();
	var $css 						= array();
	/** @var (associative_array)	Use as container for localize text/settings. **/

	var $localize_array				= array();
	/** @var (associative_array)	Use to change the wp_editor settings. **/
	var $wp_editor_settings 		= array();

	public function __construct() {
		// Setup menu
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		// Set ajax callback
		add_action( 'wp_ajax_' . $this->slug, array( $this, 'ajax_request' ) );
		// Set assets
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	public function get_labels() {
		return array(
			'title' => '',
			'menu_title' => '',
		);
	}

	public function admin_menu() {
		$labels = $this->get_labels();

		if ( ! empty( $this->parent_slug ) ) {
			// It's a sub-menu
			$submenu = add_submenu_page( $this->parent_slug, $labels['title'], $labels['menu_title'], $this->cap, $this->slug, array( $this, 'render_page' ) );

			add_action( "load-{$submenu}", array( $this, 'before_page_load' ) );
			add_action( "load-{$submenu}", array( $this, 'process_form' ) );
		}
	}

	public function render_page() {
		$view_id = str_replace( 'coursepress_', '', $this->slug );
		$admin_path = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
		$view_file = $admin_path . $view_id . '.php';

		if ( is_readable( $view_file ) ) {
			require_once $view_file;
		}
	}

	public function before_page_load() {
		$this->is_page_loaded = true;
	}

	/**
	 * Receives form submission
	 *
	 * Must be overriden in a sub-class
	 **/
	public function process_form() {}
	public function ajax_request() {}

	public function is_valid_page() {
		return isset( $_REQUEST[ $this->slug ] ) && wp_verify_nonce( $_REQUEST[ $this->slug ], $this->slug );
	}

	public static function init_tiny_mce_listeners( $init_array ) {
		$page = get_current_screen()->id;

		if ( $page == $this->slug ) {
			$init_array['height'] = '360px';
			$init_array['relative_urls'] = false;
			$init_array['url_converter'] = false;
			$init_array['url_converter_scope'] = false;

			$init_array['setup'] = 'function( ed ) {
				ed.on( \'keyup\', function( args ) {
					CoursePress.Events.trigger(\'editor:keyup\',ed);
				} );
			}';
		}

		return $init_array;
	}

	/**
	 * Select or set CSS and JS file to include in the page.
	 *
	 * Must be overriden in a sub-class
	 **/
	public function get_assets() {
		$this->scripts['admin-ui'] = true;
		$this->scripts['core'] = true;
		$this->scripts['jquery-select2'] = true;
		$this->css['admin-ui'] = true;
	}

	/**
	 * Sets CSS and JS assets needed for the page
	 **/
	public function assets() {
		if ( $this->is_page_loaded ) {
			$this->get_assets();

			$url = CoursePress::$url;
			$css_url = $url . 'asset/css/';
			$js_url = $url . 'asset/js/';
			$version = CoursePress::$version;
			$include_core = isset( $this->scripts['core'] );

			// Print styles
			$core_css = array(
				'select2' => $css_url . 'external/select2.min.css',
				'admin-ui' => $css_url . 'admin-ui.css',
			);

			if ( $include_core ) {
				// Chosen
				wp_enqueue_style( 'cp_chosen_css', $css_url . 'external/chosen.css' );
				// Font Awesome.
				wp_enqueue_style( 'fontawesome', $css_url . 'external/font-awesome.min.css' );

				// General admin css
				wp_enqueue_style( 'coursepress_admin_general', $css_url . 'admin-general.css', array(), $version );
				wp_enqueue_style( 'coursepress_admin_global', $css_url . 'admin-global.css', array( 'dashicons' ), $version );
			}

			// Print the css required for this page
			foreach ( $this->css as $css_id => $css_path ) {
				if ( isset( $core_css[ $css_id ] ) ) {
					wp_deregister_style( $css_id );
					wp_enqueue_style( $css_id, $core_css[ $css_id ] );
				} else {
					wp_enqueue_style( "coursepress_{$css_id}", $css_path, array(), $version );
				}
			}

			// Print scripts
			$dependencies = array( 'jquery', 'backbone', 'underscore' );

			$core_scripts = array(
				'jquery-select2' => $url . 'asset/js/external/select2.min.js',
				'admin-ui' => $url . 'asset/js/admin-ui.min.js',
			);

			if ( $include_core ) {
				// Load coursepress core scripts
				$course_dependencies = array(
					'jquery-ui-accordion',
					'jquery-effects-highlight',
					'jquery-effects-core',
					'jquery-ui-datepicker',
					'jquery-ui-spinner',
					'jquery-ui-droppable',
				);

				if ( isset( $this->scripts['jquery-select2'] ) ) {
					$course_dependencies[] = 'jquery-select2';
				}
				wp_enqueue_script( 'coursepress_object', $url . 'asset/js/coursepress.js', array( 'jquery', 'backbone', 'underscore' ), $version );
				wp_enqueue_script( 'chosen', $url . 'asset/js/external/chosen.jquery.min.js' );
				wp_enqueue_script( 'coursepress_course', $url . 'asset/js/coursepress-course.js', $course_dependencies, $version );
				wp_enqueue_script( 'coursepress_ui', $url . 'asset/js/coursepress-ui.js', false, $version );
				wp_enqueue_script( 'jquery-treegrid', $url . 'asset/js/external/jquery.treegrid.min.js' );
			}

			// Print the script required for this page
			foreach ( $this->scripts as $script_id => $script_path ) {
				if ( isset( $core_scripts[ $script_id ] ) ) {
					wp_deregister_script( $script_id );
					wp_enqueue_script( $script_id, $core_scripts[ $script_id ], array( 'jquery' ) );
				} else {
					wp_enqueue_script( "coursepress_{$script_id}", $script_path, false, $version );
				}
			}

			if ( $include_core ) {
				$this->localize_array = array_merge(
					array(
						'_ajax_url' => CoursePress_Helper_Utility::get_ajax_url(),
						'allowed_video_extensions' => wp_get_video_extensions(),
						'allowed_audio_extensions' => wp_get_audio_extensions(),
						'allowed_image_extensions' => CoursePress_Helper_Utility::get_image_extensions(),
						'allowed_extensions' => apply_filters( 'coursepress_custom_allowed_extensions', false ),
						'date_format' => get_option( 'date_format' ),
						'editor_visual' => __( 'Visual', 'cp' ),
						'editor_text' => _x( 'Text', 'Name for the Text editor tab (formerly HTML)', 'cp' ),
						'invalid_extension_message' => __( 'Extension of the file is not valid. Please use one of the following:', 'cp' ),
						'is_super_admin' => current_user_can( 'manage_options' ),
						'user_caps' => CoursePress_Data_Capabilities::get_user_capabilities(),
						'server_error' => __( 'An error occur while processing your request. Please try again later!', 'cp' ),
						'labels' => array(
							'user_dropdown_placeholder' => __( 'Enter username, first name and last name, or email', 'cp' ),
						),
					),
					$this->localize_array
				);

				if ( $this->with_editor ) {
					add_action( 'admin_footer', array( $this, 'prepare_editor' ), 1 );
				}

				wp_localize_script( 'coursepress_object', '_coursepress', $this->localize_array );

			}
		}
	}

	public function prepare_editor() {
		// Create a single wp-editor instance
		$this->wp_editor_settings = wp_parse_args(
			$this->wp_editor_settings,
			array(
					'textarea_name' => 'dummy_editor_name',
					'wpautop' => true,
				)
		);
		echo '<script type="text/template" id="cp-wp-editor">';
			wp_editor( 'dummy_editor_content', 'dummy_editor_id', $this->wp_editor_settings );
		echo '</script>';
	}
}
