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

/**
 * Helper class for rendering tooltips.
 *
 * @since 1.0.0
 *
 * @return object
 */
class CP_Helper_Tooltip {

	/**
	 * Method for outputting tooltips.
	 *
	 * @since 1.0.0
	 *
	 * @return void But does output HTML.
	 */
	public static function tooltip( $tip = '', $return = false ) {
		if ( empty( $tip ) ) {
			return;
		}

		if ( $return ) {
			ob_start();
		}
		?>
		<a class="help-icon" href="javascript:;"></a>
		<div class="tooltip">
			<div class="tooltip-before"></div>
			<div class="tooltip-button">&times;</div>
			<div class="tooltip-content">
				<?php echo $tip; ?>
			</div>
		</div>
		<?php
		if ( $return ) {
			return ob_get_clean();
		}
	}

}