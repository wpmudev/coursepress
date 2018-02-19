<?php

class CoursePress_Data_Helper_UI {

	/**
	 * Displays a password strength meter and includes the necessary assets.
	 */
	public static function password_strength_meter() {

		global $CoursePress_Core;

		if ( ! $CoursePress_Core->is_password_strength_meter_enabled() ) {
			return;
		}

		wp_enqueue_script( 'password-strength-meter' ); ?>
		<p class="password-strength-meter-container">
			<span class="password-strength-meter"></span>
			<input type="hidden" name="password_strength_level" value="3" />
		</p>
		<?php
	}
}
