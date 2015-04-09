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

	// short side then long side
	protected $dimensions = array(
		'A0' => array( 841, 1189 ),
		'A1' => array( 594, 841 ),
		'A2' => array( 420, 594 ),
		'A3' => array( 297, 420 ),
		'A4' => array( 210, 297 ),
		'A5' => array( 148, 210 ),
		'A6' => array( 105, 148 ),
		'A7' => array( 74, 105 ),
		'A8' => array( 52, 74 ),
	);

	protected function getFontsList() {

		// Saving system resources, we wont scan the font directory.
		$fonts = array(
			'aealarabiya.php',
			'aefurat.php',
			'cid0cs.php',
			'cid0ct.php',
			'cid0jp.php',
			'cid0kr.php',
			'courier.php',
			'courierb.php',
			'courierbi.php',
			'courieri.php',
			'dejavusans.php',
			'dejavusansb.php',
			'dejavusansbi.php',
			'dejavusanscondensed.php',
			'dejavusanscondensedb.php',
			'dejavusanscondensedbi.php',
			'dejavusanscondensedi.php',
			'dejavusansextralight.php',
			'dejavusansi.php',
			'dejavusansmono.php',
			'dejavusansmonob.php',
			'dejavusansmonobi.php',
			'dejavusansmonoi.php',
			'dejavuserif.php',
			'dejavuserifb.php',
			'dejavuserifbi.php',
			'dejavuserifcondensed.php',
			'dejavuserifcondensedb.php',
			'dejavuserifcondensedbi.php',
			'dejavuserifcondensedi.php',
			'dejavuserifi.php',
			'freemono.php',
			'freemonob.php',
			'freemonobi.php',
			'freemonoi.php',
			'freesans.php',
			'freesansb.php',
			'freesansbi.php',
			'freesansi.php',
			'freeserif.php',
			'freeserifb.php',
			'freeserifbi.php',
			'freeserifi.php',
			'helvetica.php',
			'helveticab.php',
			'helveticabi.php',
			'helveticai.php',
			'hysmyeongjostdmedium.php',
			'kozgopromedium.php',
			'kozminproregular.php',
			'msungstdlight.php',
			'pdfacourier.php',
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
			'stsongstdlight.php',
			'symbol.php',
			'times.php',
			'timesb.php',
			'timesbi.php',
			'timesi.php',
			'uni2cid_ac15.php',
			'uni2cid_ag15.php',
			'uni2cid_aj16.php',
			'uni2cid_ak12.php',
			'zapfdingbats.php',
		);

		foreach ( $fonts as $font ) {
			array_push( $this->fontlist, strtolower( $this->_getfontpath() . $font ) );
		}

	}

	protected function get_format_in_mm( $format ) {
		$dimension = $this->getPageSizeFromFormat( $format );
		$dimension[0] = Math.round( $dimension[0] / 72 * 25.5 );
		$dimension[1] = Math.round( $dimension[1] / 72 * 25.5 );
		return $dimension;
	}

	protected function get_format_in_px( $format, $dpi = 300 ) {
		$ppm = $dpi * 0.03937008;
		$dimensions = $this->get_format_in_mm( $format );
		$dimensions[0] = $dimensions[0] / $ppm;
		$dimensions[1] = $dimensions[1] / $ppm;
	}

	function make_pdf( $html, $args = array() ) {

		if( ! isset( $args['title'] ) || empty( $args['title'] ) ) {
			$args['title'] = __( 'CoursePress Report', 'cp' );
		}

		$page_orientation = isset( $args['orientation'] ) ? $args['orientation'] : PDF_PAGE_ORIENTATION;

		//Make directory for receipt cache
		if ( ! is_dir( K_PATH_CACHE ) ) {
			mkdir( K_PATH_CACHE, 0755, true );
		}
		if ( ! is_writable( K_PATH_CACHE ) ) {
			chmod( K_PATH_CACHE, 0755 );
		}

		//Clean out old cache files
		foreach ( glob( K_PATH_CACHE . '*.pdf' ) as $fname ) {
			$age = time() - filemtime( $fname );
			if ( ( $age > 12 * 60 * 60 ) && ( basename( $fname ) != 'index.php' ) ) { //Don't erase our blocking index.php file
				unlink( $fname ); // more than 12 hours old;
			}
		}

		// create new PDF document
		$pdf = new TCPDF( $page_orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

		//$dimension = $this->get_format_in_mm( PDF_PAGE_FORMAT );
		$dimension = $this->get_format_in_px( PDF_PAGE_FORMAT );
		if( 'P' == $page_orientation ) {
			$temp = $dimension[0];
			$dimension[0] = $dimension[1];
			$dimension[1] = $temp;
		}

		// Note: If uncommenting below, please remove previous call.
		// Can use the following to change language symbols to appropriate standard, e.g. ISO-638-2 languages.
		// $pdf = new TCPDF( $page_orientation, PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-639-2', false );

		// set document information
		$pdf->SetCreator( CoursePress::instance()->name );
		$pdf->SetTitle( $args['title'] );
		$pdf->SetKeywords( '' );

		// remove default header/footer
		$pdf->setPrintHeader( false );
		$pdf->setPrintFooter( false );

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont( PDF_FONT_MONOSPACED );

		//set margins
		$pdf->SetHeaderMargin( PDF_MARGIN_HEADER );
		$pdf->SetFooterMargin( PDF_MARGIN_FOOTER );

		//set image scale factor
		$pdf->setImageScale( PDF_IMAGE_SCALE_RATIO );

		//set some language-dependent strings
		global $l;
		$pdf->setLanguageArray( $l );

		// ---------------------------------------------------------

		// set font
		if( isset( $args['base_font'] ) ) {
			$pdf->SetFont( $args['base_font']['family'], '', $args['base_font']['size'] );
		} else {
			$pdf->SetFont( 'helvetica', '', 14 );
		}

		// add a page
		$pdf->AddPage();

		if( isset( $args['clickable'] ) && ! empty( $args['clickable'] ) ) {
			$html = wpautop( $html );
		} else {
			$html = make_clickable( wpautop( $html ) );
		}

		if( isset( $args['style'] ) ) {
			$html = $args['style'] . $html;
		}

		if( isset( $args['image'] ) && ! empty( $args['image'] ) ) {
			$pdf->SetMargins( 0, 0, 0 );
			$pdf->SetAutoPageBreak( false, 0 );
			$pdf->Image( $args['image'], 0, 0, 0, 0, '', '', '', true, 300, '', false, false, 0, false, false, true );
			$pdf->setPageMark();
		}

		$pdf->SetMargins( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT );

		//set auto page breaks
		$pdf->SetAutoPageBreak( true, PDF_MARGIN_BOTTOM );

		// output the HTML content
		$pdf->writeHTML( $html, true, false, true, false, '' );

		// ---------------------------------------------------------

		global $blog_id;
		$user_id = get_current_user_id();

		$sitename = sanitize_title( get_site_option( 'blogname' ) );

		$uid = isset( $args['uid'] ) ? "{$sitename}-{$blog_id}-{$user_id}" . $args['uid'] : uniqid( "{$sitename}-{$blog_id}-{$user_id}" );

		$fname = K_PATH_CACHE . "{$uid}.pdf";

		$furl = CoursePress::instance()->plugin_url . 'includes/external/tcpdf/cache/' . $uid . '.pdf';

		if( ! isset( $args['format'] ) || empty( $args['format'] ) ) {
			$args['format'] = 'F';
		}

		switch( $args['format'] ) {
			case 'F':
				//Close and output PDF document
				$pdf->Output( $fname, 'F' );
				if( isset( $args['url'] ) && ! empty( $args['url'] ) ) {
					return $furl;
				} else {
					$attachments[] = $fname;
				}

				return $attachments;
				break;
		}

	}

}
