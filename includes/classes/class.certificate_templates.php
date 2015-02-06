<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'CP_Certificate_Templates' ) ) {

	class CP_Certificate_Templates {

		var $form_title = '';

		function __construct() {

		}

		function generate_certificate( $course_id = false, $user_id = false, $preview = false, $force_download = false ) {
			global $cp, $pdf;

			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

			if ( $course_id ) {
				$course = new Course( $course_id );
			}

			require_once( $tc->plugin_dir . 'includes/external/tcpdf/examples/tcpdf_include.php' );

			$template_id = get_post_meta( $course->details->ID, 'certificate_template', true );

			if ( $template_id ) {
				$metas = get_post_meta( $post_id ); //cp_get_post_meta_all
			}

			$margin_left  = $metas['document_template_left_margin'];
			$margin_top   = $metas['document_template_top_margin'];
			$margin_right = $metas['document_template_right_margin'];

			// create new PDF document

			// Use CoursePress_PDF which extends TCPDF
			require_once( CoursePress::instance()->plugin_dir . 'includes/classes/class.coursepress-pdf.php' );

			$pdf = new CoursePress_PDF( $metas['document_template_orientation'], PDF_UNIT, apply_filters( 'coursepress_additional_template_document_size_output', $metas['document_template_size'] ), true, apply_filters( 'coursepress_template_document_encoding', bloginfo( 'charset' ) ), false );

			$pdf->setPrintHeader( false );
			$pdf->setPrintFooter( false );
			$pdf->SetFont( $metas['document_font'], '', 14 );

			// set margins
			$pdf->SetMargins( $margin_left, $margin_top, $margin_right );

			// set auto page breaks
			$pdf->SetAutoPageBreak( false, PDF_MARGIN_BOTTOM );

			$pdf->AddPage();

			error_reporting( 0 ); //Don't show errors in the PDF 
			ob_clean(); //Clear any previous output 
			ob_start(); //Start new output buffer 

			if ( isset( $metas['document_template_background_image'] ) && $metas['document_template_background_image'] !== '' ) {
				$pdf->Image( $metas['document_template_background_image'], 0, 0, '', '', '', '', '', false, 300, '', false, false, 0 );
			}

			$col_1       = 'width: 100%;';
			$col_1_width = '100%';
			$col_2       = 'width: 49.2%; margin-right: 1%;';
			$col_2_width = '49.2%';
			$col_3       = 'width: 32.5%; margin-right: 1%;';
			$col_3_width = '32.5%';
			$col_4       = 'width: 24%; margin-right: 1%;';
			$col_5       = 'width: 19%; margin-right: 1%;';
			$col_6       = 'width: 15.66%; margin-right: 1%;';
			$col_7       = 'width: 13.25%; margin-right: 1%;';
			$col_8       = 'width: 11.43%; margin-right: 1%;';
			$col_9       = 'width: 10%; margin-right: 1%;';
			$col_10      = 'width: 8.94%; margin-right: 1%;';

			$rows = '<table>';

			for ( $i = 1; $i <= apply_filters( 'coursepress_template_template_row_number', 10 ); $i ++ ) {

				$rows .= '<tr>';
				$rows_elements = get_post_meta( $post_id, 'rows_' . $i, true );

				if ( isset( $rows_elements ) && $rows_elements !== '' ) {

					$element_class_names = explode( ',', $rows_elements );
					$rows_count          = count( $element_class_names );

					foreach ( $element_class_names as $element_class_name ) {

						if ( class_exists( $element_class_name ) ) {

							if ( isset( $post_id ) ) {
								$rows .= '<td ' . ( isset( $metas[ $element_class_name . '_cell_alignment' ] ) ? 'align="' . $metas[ $element_class_name . '_cell_alignment' ] . '"' : 'align="left"' ) . ' style="' . ${"col_" . $rows_count} . ( isset( $metas[ $element_class_name . '_cell_alignment' ] ) ? 'text-align:' . $metas[ $element_class_name . '_cell_alignment' ] . ';' : '' ) . ( isset( $metas[ $element_class_name . '_font_size' ] ) ? 'font-size:' . $metas[ $element_class_name . '_font_size' ] . ';' : '' ) . ( isset( $metas[ $element_class_name . '_font_color' ] ) ? 'color:' . $metas[ $element_class_name . '_font_color' ] . ';' : '' ) . '">';

								for ( $s = 1; $s <= ( $metas[ $element_class_name . '_top_padding' ] ); $s ++ ) {
									$rows .= '<br />';
								}

								$element = new $element_class_name( $post_id );
								$rows .= $element->template_content( $course_id, $user_id, $preview );

								for ( $s = 1; $s <= ( $metas[ $element_class_name . '_bottom_padding' ] ); $s ++ ) {
									$rows .= '<br />';
								}

								$rows .= '</td>';
							}
						}
					}
				}
				$rows .= '</tr>';
			}
			$rows .= '</table>';

			echo $rows;

			$page = ob_get_contents();
			ob_clean();
			$page = preg_replace( "/\s\s+/", '', $page ); //Strip excess whitespace
			$pdf->writeHTML( $page, true, 0, true, 0 ); //Write page
			$pdf->lastPage();
			$pdf->Output( ( isset( $course->details->post_name ) ? $course->details->post_name : __( 'Certificate', 'cp' ) ) . '.pdf', ( $force_download ? 'D' : 'I' ) );
			exit;
		}

		function add_new_template() {
			global $wpdb;

			if ( isset( $_POST['template_title'] ) ) {

				$post = array(
					'post_content' => '',
					'post_status'  => 'publish',
					'post_title'   => $_POST['template_title'],
					'post_type'    => 'certificates',
				);

				$post = apply_filters( 'coursepress_certificate_template_post', $post );

				if ( isset( $_POST['template_id'] ) ) {
					$post['ID'] = $_POST['template_id']; //If ID is set, wp_insert_post will do the UPDATE instead of insert
				}

				$post_id = wp_insert_post( $post );

				//Update post meta
				if ( $post_id != 0 ) {
					foreach ( $_POST as $key => $value ) {
						if ( preg_match( "/_post_meta/i", $key ) ) {//every field name with sufix "_post_meta" will be saved as post meta automatically
							update_post_meta( $post_id, str_replace( '_post_meta', '', $key ), $value );
							do_action( 'coursepress_template_post_metas' );
						}
					}
				}

				return $post_id;
			}
		}

		function get_template_col_fields() {

			$default_fields = array(
				array(
					'field_name'        => 'post_title',
					'field_title'       => __( 'Template Name', 'cp' ),
					'field_type'        => 'text',
					'field_description' => '',
					'post_field_type'   => 'post_title',
					'table_visibility'  => true,
				),
				array(
					'field_name'        => 'post_date',
					'field_title'       => __( 'Date', 'cp' ),
					'field_type'        => 'text',
					'field_description' => '',
					'post_field_type'   => 'post_date',
					'table_visibility'  => true,
				),
			);

			return apply_filters( 'coursepress_template_col_fields', $default_fields );
		}

		function get_columns() {
			$fields  = $this->get_template_col_fields();
			$results = cp_search_array( $fields, 'table_visibility', true );

			$columns = array();

			$columns['ID'] = __( 'ID', 'cp' );

			foreach ( $results as $result ) {
				$columns[ $result['field_name'] ] = $result['field_title'];
			}

			$columns['edit']   = __( 'Edit', 'cp' );
			$columns['delete'] = __( 'Delete', 'cp' );

			return $columns;
		}

	}

}
?>
