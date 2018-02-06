<?php

/**
 * Class CoursePress_FrontPage
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_FrontPage extends CoursePress_Utility {
	protected $page_now;

	public function __construct() {
		// Listen to query request to load CP template.
		add_filter( 'parse_query', array( $this, 'maybe_load_coursepress' ) );

		// Load CP assets
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_load_assets' ) );

		// Set coursepress class
		add_filter( 'body_class', array( $this, 'set_body_class' ) );

		// Listen to zipped object file request
		add_action( 'init', array( $this, 'maybe_load_zip' ) );

		add_action( 'after_setup_theme', array( $this, 'remove_cookies' ) );
		/**
		 * add forum class
		 */
		new CoursePress_Data_Forum();
	}

	function remove_cookies() {
		$cookies = array(
			'cp_incorrect_passcode',
			'cp_mismatch_password',
			'cp_profile_updated',
			'cp_step_error',
		);

		foreach ( $cookies as $cookie ) {
			coursepress_delete_cookie( $cookie );
		}
	}

	private function reset_wp( $wp, $course_name ) {
		$wp->is_home = false;
		$wp->is_singular = $wp->is_single = true;
		$wp->query_vars = wp_parse_args( array(
			'page' => '',
			'course' => $course_name,
			'post_type' => 'course',
			'name' => $course_name,
		), $wp->query_vars );
	}

	/**
	 * @param WP_Query $wp
	 *
	 * @return mixed
	 */
	function maybe_load_coursepress( $wp ) {
		global $CoursePress_VirtualPage, $CoursePress_Core, $_coursepress_vars;

		$core = $CoursePress_Core;
		$post_type = $wp->get( 'post_type' );
		$course_name = $wp->get( 'coursename' );
		$type = $wp->get( 'coursepress' );
		$cp = array();
		if ( ! empty( $course_name ) ) {
			$cp['course'] = $course_name;
			$cp['type']   = $type;

			if ( in_array( $type, array( 'unit', 'module', 'step', 'step-comment' ) ) ) {
				$cp['unit'] = $wp->get( 'unit' );

				if ( ( $module = $wp->get( 'module' ) ) ) {
					$cp['module'] = $module;
				}
				if ( ( $step = $wp->get( 'step' ) ) ) {
					$cp['step'] = $step;
				}
			} elseif ( 'forum' == $type ) {
				$cp['topic'] = $wp->get( 'topic' );
			}
			$this->reset_wp( $wp, $course_name );
		} elseif ( ! empty( $type ) ) {
			// Use for CP specific pages
			$cp['course'] = false;
			$cp['type'] = $type;
			$wp->is_home = false;
			$wp->is_singular = $wp->is_single = true;

		} elseif ( $core->course_post_type == $post_type && $wp->is_main_query() ) {
			if ( $wp->is_single || $wp->is_singular ) {
				$cp['type'] = 'single-course';
				$cp['course'] = $wp->get( 'name' );
			} else {
				$wp->set( 'post_type', 'course' );
				$wp->set( 'is_archive', 1 );
				$cp['type'] = 'archive-course';
			}
		}

		$_coursepress_vars = $cp;
		if ( ! empty( $cp ) ) {
			$this->__set( 'page_now', $cp['type'] );

			// Set CP Virtual Page
			$CoursePress_VirtualPage = new CoursePress_VirtualPage( $cp );
		}

		return $wp;
	}

	function maybe_load_assets() {
		if ( $this->__get( 'page_now' ) ) {
			$this->load_assets();
		}
	}

	protected function load_assets() {
		$css_deps = array( 'dashicons' );
		$deps = array( 'jquery', 'backbone', 'underscore' );
		$page_now = $this->__get( 'page_now' );

		if ( 'single-course' == $page_now
			|| in_array( $page_now, array( 'unit', 'module', 'step' ) ) ) {
			$this->set_external_css( 'coursepress-video-css', 'video-js.min.css' );

			$this->set_external_js( 'coursepress-video', 'video.min.js' );
			$this->set_external_js( 'coursepress-video-youtube', 'video-youtube.min.js' );
			$this->set_external_js( 'coursepress-videojs-vimeo', 'videojs-vimeo.min.js', '3.0.0' );
		}
		$this->set_external_js( 'circle-progress', 'circle-progress.min.js' );
		$this->set_external_css( 'fontawesome', 'font-awesome.min.css' );

		// Global CSS
		$this->set_css( 'coursepress-css', 'front.min.css', $css_deps );
		// Global JS
		$this->set_js( 'coursepress-front-js', 'coursepress-front.min.js', $deps );
		// Set localize vars
		$this->set_local_vars();
	}

	private function set_local_vars() {
		$localize_vars = array(
			'_wpnonce' => wp_create_nonce('coursepress_nonce'),
			'ajaxurl'  => admin_url('admin-ajax.php'),
			'text' => array(
				'attempts_consumed' => __('You can no longer play this media because you have run out of attempts.')
			)
		);

		/**
		 * Fire before setting coursepress localize variables.
		 *
		 * @since 2.0
		 * @param array $localize_vars
		 */
		$localize_vars = apply_filters( 'coursepress_localize_object', $localize_vars );

		wp_localize_script( 'coursepress-front-js', '_coursepress', $localize_vars );
	}

	/**
	 * Function to enqueue an external script.
	 *
	 * @param string      $id  Id for the file.
	 * @param string      $src Name of the external js file.
	 * @param bool|string $version Version of the file. Default to CoursePress version.
	 */
	private function set_external_js( $id, $src, $version = false ) {
		global $CoursePress;

		if ( false === $version ) {
			$version = $CoursePress->version;
		}
		$plugin_url = $CoursePress->plugin_url;
		wp_enqueue_script( $id, $plugin_url . 'assets/external/js/' . $src, false, $version, true ); // Load the footer.
	}

	private function set_external_css( $id, $src ) {
		global $CoursePress;

		$plugin_url = $CoursePress->plugin_url;
		wp_enqueue_style( $id, $plugin_url . 'assets/external/css/' . $src );
	}

	private function set_css( $id, $src, $deps = false ) {
		global $CoursePress;

		$plugin_url = $CoursePress->plugin_url;
		$version = $CoursePress->version;

		wp_enqueue_style( $id, $plugin_url . 'assets/css/' . $src, $deps, $version );
	}

	private function set_js( $id, $src, $deps = false ) {
		global $CoursePress;

		$plugin_url = $CoursePress->plugin_url;
		$version = $CoursePress->version;
		wp_enqueue_script( $id, $plugin_url . 'assets/js/' . $src, $deps, $version, true );
	}

	public function set_body_class( $classes ) {
		if ( ( $is_cp = $this->__get( 'page_now' ) ) ) {
			array_push( $classes, 'coursepress', 'coursepress-' . $is_cp );
		}
		/**
		 * add preview class
		 */
		if ( isset( $_GET['preview'] ) ) {
			$classes[] = 'cp-preview';
		}
		return $classes;
	}

	function maybe_load_zip() {
		if ( ! empty( $_REQUEST['oacpf'] ) ) {
			$module_id = $_REQUEST['oacpf'];

			if ( (int) $module_id > 0 ) {
				$doc = new CoursePress_Zipped( $module_id );
				exit;
			}
		}
	}
}
