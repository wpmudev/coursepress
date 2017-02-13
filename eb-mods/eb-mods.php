<?php
if ( ! class_exists( 'CoursePress_Session' ) ) {

	class CoursePress_Session {

		private static $token = 'coursepress_';
		private static $add_time = '+1 hour';


		public static function session_start() {}

		/**
		 * IMPORTANT: Only works for logged in users.
		 *
		 * To use this for registration, create the user first and login immediately
		 *
		 * @param $key
		 * @param null $value
		 * @param bool $unset
		 * @param bool $duration
		 * @param bool $force_session
		 * @param bool $token_update
		 *
		 * @return bool|null|string
		 */
		public static function session( $key = true, $value = null, $unset = false, $duration = false, $force_session = false, $token_update = false ) {

			$session_value = null;

			// WordPress 4.0+ only
			$user_id = get_current_user_id();

			/** @var WP_Session_Tokens $session */
			$session     = WP_Session_Tokens::get_instance( $user_id );
			$token_parts = explode( '_', self::$token );
			$token_parts = (int) array_pop( $token_parts );
			self::$token = empty( $token_parts ) ? self::$token . $user_id : self::$token;

			if ( empty( $duration ) ) {
				// Default 1 hr
				$duration = strtotime( self::$add_time, time() );
			}

			$session_data = $session->get( self::$token );
			if ( empty( $session_data ) ) {
				$session_data = array(
					'expiration' => $duration,
				);
			}

			if ( null === $value && ! $unset ) {
				if ( is_array( $key ) ) {
					$session_value = self::_get_val( $session_data, $key );
				} else if( true !== $key ){
					$session_value = isset( $session_data[ $key ] ) ? $session_data[ $key ] : null;
				} else {
					$session_value = isset( $session_data ) ? $session_data : null;
				}
			} else {
				if ( ! $unset ) {
					if ( is_array( $key ) ) {
						self::_set_val( $session_data, $key, $value );
					} else {
						$session_data[ $key ] = $value;
					}
				} else {
					if ( is_array( $key ) ) {
						self::_unset_val( $session_data, $key );
					} else {
						unset( $session_data[ $key ] );
					}
				}
				$session->update( self::$token, $session_data );

				$session_value = $value;
			}

			if( ! $force_session && empty( $session_value ) && null === $value ) {
				$session_value = self::session( $key, $value, $unset, $duration, true );
				// Update token if we can
				self::session( $key, $session_value, $unset, $duration, true, true );
			}

			return $session_value;

		}

		public static function unset_session( $key ) {
			self::session( $key, null, true );
		}

		private static function _get_val( $arr, $index ) {
			$value = false;
			if ( is_array( $index ) ) {
				$key = array_shift( $index );
				if ( isset( $arr[ $key ] ) ) {
					$value = $arr[ $key ];
					if ( count( $index ) ) {
						$value = self::_get_val( $value, $index );
					}
				} else {
					return null;
				}
			}

			return $value;
		}

		private static function _set_val( &$data, $path, $value ) {
			$temp = &$data;
			foreach ( $path as $key ) {
				$temp = &$temp[ $key ];
			}
			$temp = $value;

			return $value;
		}

		private static function _unset_val( &$data, $path ) {
			$temp = &$data;
			$kill = $path[ count($path) - 1 ];
			foreach ( $path as $key ) {
				if ( $kill != $key ) {
					$temp = &$temp[ $key ];
				} else {
					unset( $temp[ $key ] );
				}
			}
		}

		public static function attempt_force_sessions() {
			// Activate Sessions by putting in a false var
			CoursePress_Session::session('coursepress_sessions_active', true);
		}

	}

}