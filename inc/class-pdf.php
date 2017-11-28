<?php
/**
 * Class CoursePress_PDF
 *
 * @since 2.0
 */
class CoursePress_PDF extends CoursePress_External_TCPDF_TCPDF
{
    private $footer_text = '';

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

    public function Footer() {
        $the_font = apply_filters( 'coursepress_pdf_font', 'helvetica' );

        // Position at 15 mm from bottom
        $this->SetY( -15 );
        // Set font
        $this->SetFont( $the_font, '', 10 );
        // Page number
        $this->Cell( 0, 5, $this->footer_text, 0, false, 'L', 0, '', 0, false, 'T', 'M' );
        $this->Cell( 0, 5, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'T' );

        // $this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));
        $pageWidth = $this->getPageWidth();   // Get total page width, without margins
        $pageMargins = $this->getMargins();     // Get all margins as array

        $px2 = $pageWidth - $pageMargins['right'];
        $p1x = $pageMargins['left'];

        // $p1x = $this->getX();
        $p1y = $this->getY();
        $p2x = $px2;
        $p2y = $p1y;  // Use same y for a straight line
        $style = array();
        $this->Line( $p1x, $p1y, $p2x, $p2y, $style );

    }

    public function fonts() {

        // Saving system resources, we wont scan the font directory.
        $fonts = array(
            'cid0cs.php' => __( 'Arial Unicode MS (Simplified Chinese)', 'CP_TD' ),
            'cid0ct.php' => __( 'Arial Unicode MS (Chinese Traditional)', 'CP_TD' ),
            'cid0jp.php' => __( 'Arial Unicode MS (Japanese)', 'CP_TD' ),
            'cid0kr.php' => __( 'Arial Unicode MS (Korean)', 'CP_TD' ),
            'courier.php' => __( 'Courier', 'CP_TD' ),
            'courierb.php' => '',
            'courierbi.php' => '',
            'courieri.php' => '',
            'helvetica.php' => __( 'Helvetica', 'CP_TD' ),
            'helveticab.php' => '',
            'helveticabi.php' => '',
            'helveticai.php' => '',
            'symbol.php' => __( 'Symbol', 'CP_TD' ),
            'times.php' => __( 'Times-Roman', 'CP_TD' ),
            'timesb.php' => '',
            'timesbi.php' => '',
            'timesi.php' => '',
            'uni2cid_ac15.php' => __( 'Adobe-CNS1-5', 'CP_TD' ),
            'uni2cid_ag15.php' => __( 'Adobe-GB1-5', 'CP_TD' ),
            'uni2cid_aj16.php' => __( 'Adobe-Japan1-6', 'CP_TD' ),
            'uni2cid_ak12.php' => __( 'Adobe-Korea1-2', 'CP_TD' ),
            'zapfdingbats.php' => __( 'ZapfDingbats', 'CP_TD' ),
        );

        if ( defined( 'TCPDF_PLUGIN_ACTIVE' ) && TCPDF_PLUGIN_ACTIVE ) {
            $fonts = array_merge( $fonts, array(
                'aealarabiya.php' => __( 'Al Arabiya', 'CP_TD' ),
                'aefurat.php' => __( 'Furat', 'CP_TD' ),
                'dejavusans.php' => __( 'DejaVu Sans', 'CP_TD' ),
                'dejavusansb.php' => '',
                'dejavusansbi.php' => '',
                'dejavusanscondensed.php' => __( 'DejaVu Sans Condensed', 'CP_TD' ),
                'dejavusanscondensedb.php' => '',
                'dejavusanscondensedbi.php' => '',
                'dejavusanscondensedi.php' => '',
                'dejavusansextralight.php' => __( 'DejaVu Sans ExtraLight', 'CP_TD' ),
                'dejavusansi.php' => '',
                'dejavusansmono.php' => __( 'DejaVu Sans Mono', 'CP_TD' ),
                'dejavusansmonob.php' => '',
                'dejavusansmonobi.php' => '',
                'dejavusansmonoi.php' => '',
                'dejavuserif.php' => __( 'DejaVu Serif', 'CP_TD' ),
                'dejavuserifb.php' => '',
                'dejavuserifbi.php' => '',
                'dejavuserifcondensed.php' => __( 'DejaVu Serif Condensed', 'CP_TD' ),
                'dejavuserifcondensedb.php' => '',
                'dejavuserifcondensedbi.php' => '',
                'dejavuserifcondensedi.php' => '',
                'dejavuserifi.php' => '',
                'freemono.php' => __( 'Free Mono', 'CP_TD' ),
                'freemonob.php' => '',
                'freemonobi.php' => '',
                'freemonoi.php' => '',
                'freesans.php' => __( 'Free Sans', 'CP_TD' ),
                'freesansb.php' => '',
                'freesansbi.php' => '',
                'freesansi.php' => '',
                'freeserif.php' => __( 'Free Serif', 'CP_TD' ),
                'freeserifb.php' => '',
                'freeserifbi.php' => '',
                'freeserifi.php' => '',
                'hysmyeongjostdmedium.php' => __( 'MyungJo Medium (Korean)', 'CP_TD' ),
                'kozgopromedium.php' => __( 'Kozuka Gothic Pro (Japanese Sans-Serif)', 'CP_TD' ),
                'kozminproregular.php' => __( 'Kozuka Mincho Pro (Japanese Serif)', 'CP_TD' ),
                'msungstdlight.php' => __( 'MSung Light (Traditional Chinese)', 'CP_TD' ),
                'pdfacourier.php' => __( 'PDFA Courier', 'CP_TD' ),
                'pdfacourierb.php' => '',
                'pdfacourierbi.php' => '',
                'pdfacourieri.php' => '',
                'pdfahelvetica.php' => __( 'PDFA Helvetica', 'CP_TD' ),
                'pdfahelveticab.php' => '',
                'pdfahelveticabi.php' => '',
                'pdfahelveticai.php' => '',
                'pdfasymbol.php' => __( 'PDFA Symbol', 'CP_TD' ),
                'pdfatimes.php' => __( 'PDFA Times', 'CP_TD' ),
                'pdfatimesb.php' => '',
                'pdfatimesbi.php' => '',
                'pdfatimesi.php' => '',
                'pdfazapfdingbats.php' => __( 'PDFA ZapfDingbats', 'CP_TD' ),
                'robotolight.php' => __( 'Roboto Light', 'CP_TD' ),
                'robotolightitalic.php' => __( 'Robot Light Italic', 'CP_TD' ),
                'stsongstdlight.php' => __( 'STSong Light (Simplified Chinese)', 'CP_TD' ),
            ) );
        }

        // If you are hooking this, make sure you are using fonts for TCPDF and that they are located in relevant font path
        return apply_filters( 'coursepress_pdf_font_list', $fonts );

    }

    protected function getFontsList() {

        $fonts = $this->fonts();
        $font_path = apply_filters( 'coursepress_pdf_font_path', CP_TCPDF_FONTS::_getfontpath() );

        foreach ( $fonts as $font => $font_name ) {
            array_push( $this->fontlist, strtolower( trailingslashit( $font_path ) . $font ) );
        }

    }

    public function get_format_in_mm( $format ) {
        $dimension = CP_TCPDF_STATIC::getPageSizeFromFormat( $format );
        $dimension[0] = round( $dimension[0] / 72 * 25.5 );
        $dimension[1] = round( $dimension[1] / 72 * 25.5 );

        return $dimension;
    }

    public function get_format_in_px( $format, $dpi = 300 ) {
        $ppm = $dpi * 0.03937008;
        $dimensions = $this->get_format_in_mm( $format );
        $dimensions[0] = $dimensions[0] / $ppm;
        $dimensions[1] = $dimensions[1] / $ppm;

        return $dimensions;
    }

    public function cache_path( $subdirectory = false ) {
        $uploads_dir = wp_upload_dir();
        $cache_path = apply_filters( 'coursepress_pdf_cache_path', trailingslashit( $uploads_dir['basedir'] ) . 'pdf-cache/' );
        if ( ! empty ( $subdirectory ) ) {
            $cache_path .= $subdirectory;
            $this->is_cache_path_writable( $cache_path );
        }
        return $cache_path;
    }

    public function cache_url() {
        $uploads_dir = wp_upload_dir();
        $cache_url = apply_filters( 'coursepress_pdf_cache_url', trailingslashit( $uploads_dir['baseurl'] ) . 'pdf-cache/' );
        return $cache_url;
    }

    /**
     * Check pdf-cache directory.
     *
     * @since 2.0.0
     *
     * @return bool is writable or not?
     */
    public function is_cache_path_writable( $cache_path = null ) {
        if ( empty( $cache_path ) ) {
            $cache_path = $this->cache_path();
        }

        $is_writable = is_dir( $cache_path ) && is_writable( $cache_path );
        if ( ! $is_writable ) {
            // Attempt to write locally
            if ( mkdir( $cache_path, 0775, true ) ) {
                $is_writable = true;
            } else {
                // Unable to write? Let's try Filesystem API
                if ( ! function_exists( 'WP_Filesystem' ) ) {
                    $file_system = ABSPATH . 'wp-admin/includes/file.php';

                    if ( file_exists( $file_system ) && is_readable( $file_system ) ) {
                        require_once $file_system;
                    }
                }
                $wp_filesystem = WP_Filesystem();
                $is_writable = $wp_filesystem->mkdir( $cache_path, 0775 );
            }
        }

        return $is_writable;
    }

    private function get_image_contents($url)
    {
        $image_path = str_replace(WP_CONTENT_URL, WP_CONTENT_DIR, $url);
        $image_contents = file_get_contents($image_path);
        return $image_contents ? '@' . $image_contents : false;
    }

    /**
     * Make the actual PDF
     *
     * @param $html
     * @param array $args
     *
     * 'title' - PDF Title
     * 'orientation' - [P]ortrait, [L]andscape, [A]utomatic
     * 'base_font' - See TCPDF
     * 'clickable' - True creates clickable links
     * 'style' - Additional Style tag
     * 'image' - Background image
     * 'uid' - Defined unique ID (generated if left empty)
     * 'filename' - Override auto filename
     * 'format' - See TCPDF (F - save file, I - standard output (preview), D - download )
     * 'header' - Header content - array( 'Header Title', 'Subtitle' )
     * 'footer' - Footer content
     *
     * @return array|string
     */
    public function make_pdf( $html, $args = array() ) {
        global $CoursePress;

        if ( ! isset( $args['title'] ) || empty( $args['title'] ) ) {
            $args['title'] = __( 'CoursePress Report', 'CP_TD' );
        }

        $the_font = apply_filters( 'coursepress_pdf_font', 'helvetica' );

        // If filtering, please make sure both path and url refer to the same location
        $cache_path = $this->cache_path();
        $furl_path = $this->cache_url();

        $page_orientation = isset( $args['orientation'] ) ? $args['orientation'] : PDF_PAGE_ORIENTATION;

        // Make directory for receipt cache
        if ( ! is_dir( $cache_path ) ) {
            mkdir( $cache_path, 0755, true );
            touch( trailingslashit( $cache_path ) . 'index.php' );
        }
        if ( ! is_writable( $cache_path ) ) {
            chmod( $cache_path, 0755 );
        }

        // Clean out old cache files
        foreach ( glob( $cache_path . '*.pdf' ) as $fname ) {
            $age = time() - filemtime( $fname );
            if ( ( $age > 12 * 60 * 60 ) && ( basename( $fname ) != 'index.php' ) ) { // Don't erase our blocking index.php file
                unlink( $fname ); // more than 12 hours old;
            }
        }
        // create new PDF document
        $pdf = new CoursePress_PDF( $page_orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

        // $dimension = $this->get_format_in_mm( PDF_PAGE_FORMAT );
        $dimension = $this->get_format_in_px( PDF_PAGE_FORMAT );
        if ( 'P' == $page_orientation ) {
            $temp = $dimension[0];
            $dimension[0] = $dimension[1];
            $dimension[1] = $temp;
        }

        // Note: If uncommenting below, please remove previous call.
        // Can use the following to change language symbols to appropriate standard, e.g. ISO-638-2 languages.
        // $pdf = new TCPDF( $page_orientation, PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-639-2', false );
        // set document information
        $pdf->SetCreator( $CoursePress->name );
        $pdf->SetTitle( $args['title'] );
        $pdf->SetKeywords( '' );

        if ( isset( $args['header'] ) && is_array( $args['header'] ) ) {

            $title = isset( $args['header']['title'] ) ? $args['header']['title'] : '';
            $subtitle = isset( $args['header']['subtitle'] ) ? $args['header']['subtitle'] : '';

            if ( empty( $subtitle ) ) {
                $pdf->SetHeaderMargin( PDF_MARGIN_HEADER );
            }

            // setHeaderData($ln='', $lw=0, $ht='', $hs='', $tc=array(0,0,0), $lc=array(0,0,0)
            $pdf->SetHeaderData( '', '', $title, $subtitle );
            $pdf->setHeaderFont( array( PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN ) );

        } else {
            // remove default header
            $pdf->setPrintHeader( false );
            // adjust margin
            $pdf->SetHeaderMargin( PDF_MARGIN_HEADER );
        }

        if ( isset( $args['footer'] ) ) {

            $pdf->footer_text = $args['footer'];
            $pdf->setFooterFont( array( PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA ) );
        } else {
            // remove default footer
            $pdf->setPrintFooter( false );
            // adjust margins
            $pdf->SetFooterMargin( PDF_MARGIN_FOOTER );
        }

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont( PDF_FONT_MONOSPACED );

        // set image scale factor
        $pdf->setImageScale( PDF_IMAGE_SCALE_RATIO );

        // set some language-dependent strings
        global $l;
        $pdf->setLanguageArray( $l );

        // ---------------------------------------------------------
        // set font
        if ( isset( $args['base_font'] ) ) {
            $pdf->SetFont( $args['base_font']['family'], '', $args['base_font']['size'] );
        } else {
            $pdf->SetFont( $the_font, '', 14 );
        }

        // add a page
        $pdf->AddPage();

        if ( isset( $args['clickable'] ) && ! empty( $args['clickable'] ) ) {
            $html = wpautop( $html );
        } else {
            $html = make_clickable( wpautop( $html ) );
        }

        if ( isset( $args['style'] ) ) {
            $html = $args['style'] . $html;
        }

        if (isset($args['image']) && !empty($args['image'])) {
            $image_contents = $this->get_image_contents($args['image']);

            if ($image_contents) {
                $pdf->SetMargins(0, 0, 0);
                $pdf->SetAutoPageBreak(false, 0);
                $pdf->Image($image_contents, 0, 0, 0, 0, '', '', '', true, 300, '', false, false, 0, false, false, true);
                $pdf->setPageMark();
            }
        }

        // set auto page breaks
        $set_auto_page_break = true;
        if ( isset( $args['page_break'] ) && $args['page_break'] ) {
            $set_auto_page_break = cp_is_true( $args['page_break'] );
        }
        if ( $set_auto_page_break ) {
            $pdf->SetAutoPageBreak( true, PDF_MARGIN_BOTTOM );
        } else {
            $pdf->SetAutoPageBreak( false );
        }

        /**
         * margins
         */
        if ( isset( $args['margins'] ) ) {
            $pdf->setPageMark();
            if ( isset( $args['margins']['right'] ) ) {
                $pdf->setRightMargin( $args['margins']['right'] );
            }
            if ( isset( $args['margins']['top'] ) ) {
                $pdf->setTopMargin( $args['margins']['top'] );
            }
            if ( isset( $args['margins']['left'] ) ) {
                $pdf->setLeftMargin( $args['margins']['left'] );
            }
        }

        /**
         * text color
         */
        if ( isset( $args['text_color'] ) ) {
            if ( is_array( $args['text_color']) && 2 < sizeof( $args['text_color'] ) ) {
                $pdf->SetTextColor( $args['text_color'][0], $args['text_color'][1], $args['text_color'][2]);
            }
        }

        /**
         * Logo
         */
	    if (!empty($args['logo']['file'])) {
		    $logo_image_contents = $this->get_image_contents($args['logo']['file']);
		    $args['logo'] = wp_parse_args($args['logo'], array(
			    'x' => 0,
			    'y' => 0,
			    'w' => 0
		    ));

		    if ($logo_image_contents) {
			    $pdf->Image(
				    $logo_image_contents,
				    $args['logo']['x'],
				    $args['logo']['y'],
				    $args['logo']['w']
			    );
		    }
	    }

        // output the HTML content
        $pdf->writeHTML( $html, true, false, true, false, '' );

        // ---------------------------------------------------------
        global $blog_id;
        $user_id = get_current_user_id();

        $sitename = sanitize_title( get_site_option( 'blogname' ) );

        $uid = isset( $args['uid'] ) ? "{$sitename}-{$blog_id}-{$user_id}" . $args['uid'] : uniqid( "{$sitename}-{$blog_id}-{$user_id}" );

        $file = isset( $args['filename'] ) ? $args['filename'] : $uid . '.pdf';

        $fname = $cache_path . $file;

        $furl = $furl_path . $file;

        if ( ! isset( $args['format'] ) || empty( $args['format'] ) ) {
            $args['format'] = 'F';
        }

        switch ( $args['format'] ) {
            case 'F': case 'FI':
            // Close and output PDF document
            $pdf->Output( $fname, $args['format'] );
            if ( isset( $args['url'] ) && ! empty( $args['url'] ) ) {
                if ( isset( $args['force_download'] ) && ! empty( $args['force_download'] ) ) {
                    coursepress_download_file( $furl );
                } else {
                    return $furl;
                }
            } else {
                $attachments[] = $fname;
            }

            return $attachments;
            break;
            case 'I' :
            case 'D' :
                $pdf->Output( $fname, $args['format'] );
                exit;
                break;
        }

    }
}
