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


include_once( CoursePress::instance()->plugin_dir . 'includes/external/tcpdf/config/lang/eng.php' );
require_once( CoursePress::instance()->plugin_dir . 'includes/external/tcpdf/tcpdf.php' );

/**
 * Class to Override TCPF.
 *
 * @since 1.2.1
 *
 * @return object
 */
class CoursePress_PDF extends TCPDF {

	protected function getFontsList() {

		// Saving system resources, we wont scan the font directory.
		$fonts = array(
			'courier.php',
			'courierb.php',
			'courierbi.php',
			'courieri.php',
			'helvetica.php',
			'helveticab.php',
			'helveticabi.php',
			'helveticai.php',
			'pdfacourierb.php',
			'pdfacourierbi.php',
			'pdfacourieri.php',
			'pdfahelvetica.php',
			'pdfahelveticab.php',
			'pdfahelveticabi.php',
			'pdfahelveticai.php',
			'pdfasymbol.php',
			'pdfatimes.php',
			'pdfatimesb.php',
			'pdfatimesbi.php',
			'pdfatimesi.php',
			'pdfazapfdingbats.php',
			'symbol.php',
			'times.php',
			'timesb.php',
			'timesbi.php',
			'timesi.php',
			'zapfdingbats.php',
		);

		foreach( $fonts as $font ) {
			array_push( $this->fontlist, strtolower( $this->_getfontpath() . $font ) );
		}

	}

}