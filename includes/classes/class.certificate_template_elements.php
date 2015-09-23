<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'CP_Certificate_Template_Elements' ) ) {

	class CP_Certificate_Template_Elements {

		var $id = '';
		var $template_metas = '';
		var $element_title = '';
		var $element_name = '';

		function __construct( $id = '' ) {
			$this->id = $id;

			if ( $id !== '' ) {
				$this->template_metas = get_post_meta( $id );
			}

			$this->on_creation();
		}

		function on_creation() {

		}

		function admin_content() {
			echo $this->get_font_sizes();
			echo $this->get_font_style();
			echo $this->get_font_colors();
			echo $this->get_cell_alignment();
			echo $this->get_element_margins();
			do_action( 'coursepress_template_admin_content' );
		}

		function template_content() {

		}

		function save() {

		}

		function get_all_set_elements() {
			$set_elements = array();

			for ( $i = 1; $i <= apply_filters( 'coursepress_certificate_template_row_number', 15 ); $i ++ ) {
				$rows_elements = get_post_meta( $this->id, 'rows_' . $i, true );
				if ( isset( $rows_elements ) && $rows_elements !== '' ) {
					$element_class_names = explode( ',', $rows_elements );
					foreach ( $element_class_names as $element_class_name ) {
						$set_elements[] = $element_class_name;
					}
				}
			}

			return $set_elements;
		}

		function get_document_sizes() {
			$document_template_size = isset( $this->template_metas['document_template_size'][0] ) ? $this->template_metas['document_template_size'][0] : 'A4';
			?>
			<label><?php _e( 'Certificate Paper Size', 'cp' ); ?></label>
			<select name="document_template_size_post_meta">
				<option value="A4" <?php selected( $document_template_size, 'A4', true ); ?>><?php echo esc_attr_e( 'A4 (210 × 297)', 'cp' ); ?></option>
				<option value="A5" <?php selected( $document_template_size, 'A5', true ); ?>><?php echo esc_attr_e( 'A5 (148 × 210)', 'cp' ); ?></option>
				<option value="A6" <?php selected( $document_template_size, 'A6', true ); ?>><?php echo esc_attr_e( 'A6 (105 × 148)', 'cp' ); ?></option>
				<option value="A7" <?php selected( $document_template_size, 'A7', true ); ?>><?php echo esc_attr_e( 'A7 (74 × 105)', 'cp' ); ?></option>
				<option value="A8" <?php selected( $document_template_size, 'A8', true ); ?>><?php echo esc_attr_e( 'A8 (52 × 74)', 'cp' ); ?></option>
				<?php do_action( 'coursepress_additional_template_document_size' ); ?>
			</select>
		<?php
		}

		function get_document_orientation() {
			$document_template_orientation = isset( $this->template_metas['document_template_orientation'][0] ) ? $this->template_metas['document_template_orientation'][0] : 'P';
			?>
			<label><?php _e( 'Orientation', 'cp' ); ?></label>
			<select name="document_template_orientation_post_meta">
				<option value="P" <?php selected( $document_template_orientation, 'P', true ); ?>><?php echo esc_attr_e( 'Portrait', 'cp' ); ?></option>
				<option value="L" <?php selected( $document_template_orientation, 'L', true ); ?>><?php echo esc_attr_e( 'Landscape', 'cp' ); ?></option>
			</select>
		<?php
		}

		function get_document_margins( $top = 10, $right = 10, $left = 10 ) {
			$top_margin   = isset( $this->template_metas['document_template_top_margin'][0] ) ? $this->template_metas['document_template_top_margin'][0] : $top;
			$right_margin = isset( $this->template_metas['document_template_right_margin'][0] ) ? $this->template_metas['document_template_right_margin'][0] : $right;
			$left_margin  = isset( $this->template_metas['document_template_left_margin'][0] ) ? $this->template_metas['document_template_left_margin'][0] : $left;
			?>
			<label><?php _e( 'Document Margins', 'cp' ); ?></label>
			<?php _e( 'Top', 'cp' ); ?> <input class="template_margin" type="text" name="document_template_top_margin_post_meta" value="<?php echo esc_attr( $top_margin ); ?>" />
			<?php _e( 'Right', 'cp' ); ?> <input class="template_margin" type="text" name="document_template_right_margin_post_meta" value="<?php echo esc_attr( $right_margin ); ?>" />
			<?php _e( 'Left', 'cp' ); ?> <input class="template_margin" type="text" name="document_template_left_margin_post_meta" value="<?php echo esc_attr( $left_margin ); ?>" />
			</p>
			<?php
		}

		function get_full_background_image() {
			$template_background_image = ( isset( $this->template_metas['document_template_background_image'][0] ) && $this->template_metas['document_template_background_image'][0] !== '' ? $this->template_metas['document_template_background_image'][0] : '' );
			?>
			<label><?php _e( 'Certificate Background Image', 'cp' ); ?></label>
			<input class="file_url" type="text" size="36" name="document_template_background_image_post_meta" value="<?php echo esc_attr( $template_background_image ); ?>"/>
			<input class="file_url_button button-secondary" type="button" value="<?php esc_attr_e( 'Browse', 'cp' ); ?>"/>
		<?php
		}

		function get_cell_alignment() {
			$cell_alignment = isset( $this->template_metas[ $this->element_name . '_cell_alignment' ][0] ) ? $this->template_metas[ $this->element_name . '_cell_alignment' ][0] : 'left';
			?>
			<label><?php _e( 'Cell Alignment', 'cp' ); ?></label>
			<select name="<?php echo $this->element_name; ?>_cell_alignment_post_meta">
				<option value="left" <?php selected( $cell_alignment, 'left', true ); ?>><?php echo esc_attr_e( 'Left', 'cp' ); ?></option>
				<option value="right" <?php selected( $cell_alignment, 'right', true ); ?>><?php echo esc_attr_e( 'Right', 'cp' ); ?></option>
				<option value="center" <?php selected( $cell_alignment, 'center', true ); ?>><?php echo esc_attr_e( 'Center', 'cp' ); ?></option>
			</select>
		<?php
		}

		function get_element_margins() {
			$top_padding    = isset( $this->template_metas[ $this->element_name . '_top_padding' ][0] ) ? $this->template_metas[ $this->element_name . '_top_padding' ][0] : '0';
			$bottom_padding = isset( $this->template_metas[ $this->element_name . '_bottom_padding' ][0] ) ? $this->template_metas[ $this->element_name . '_bottom_padding' ][0] : '0';
			?>
			<label><?php _e( 'Element Break Lines', 'cp' ); ?></label>
			<?php _e( 'Top', 'cp' ); ?> <input class="template_element_padding" type="text" name="<?php echo $this->element_name; ?>_top_padding_post_meta" value="<?php echo esc_attr( $top_padding ); ?>" /><br />
			<?php _e( 'Bottom', 'cp' ); ?> <input class="template_element_padding" type="text" name="<?php echo $this->element_name; ?>_bottom_padding_post_meta" value="<?php echo esc_attr( $bottom_padding ); ?>" />
			</p>
			<?php
		}

		function get_font_style() {
			?>
			<label><?php _e( 'Font Style', 'cp' ); ?></label>

			<select name="<?php echo $this->element_name; ?>_font_style_post_meta">
				<?php
				$font_style = isset( $this->template_metas[ $this->element_name . '_font_style' ][0] ) ? $this->template_metas[ $this->element_name . '_font_style' ][0] : '';
				?>
				<option value="" <?php selected( $font_style, '', true ); ?>><?php echo _e( 'Regular', 'cp' ); ?></option>
				<option value="B" <?php selected( $font_style, 'B', true ); ?>><?php echo _e( 'Bold', 'cp' ); ?></option>
				<option value="BI" <?php selected( $font_style, 'BI', true ); ?>><?php echo _e( 'Bold + Italic', 'cp' ); ?></option>
				<option value="BU" <?php selected( $font_style, 'BU', true ); ?>><?php echo _e( 'Bold + Underline', 'cp' ); ?></option>
				<option value="BIU" <?php selected( $font_style, 'BIU', true ); ?>><?php echo _e( 'Bold + Underline + Italic', 'cp' ); ?></option>
				<option value="I" <?php selected( $font_style, 'I', true ); ?>><?php echo _e( 'Italic', 'cp' ); ?></option>
				<option value="IU" <?php selected( $font_style, 'IU', true ); ?>><?php echo _e( 'Italic + Underline', 'cp' ); ?></option>
				<option value="U" <?php selected( $font_style, 'U', true ); ?>><?php echo _e( 'Underline', 'cp' ); ?></option>
			</select>
		<?php
		}

		function get_colors( $label = 'Color', $field_name = 'color', $default_color = '#000000' ) {
			?>
			<label><?php echo $label; ?></label>
			<input type="text" class="cp-color-picker" name="<?php echo $this->element_name; ?>_<?php echo $field_name; ?>_post_meta" value="<?php echo esc_attr( isset( $this->template_metas[ $this->element_name . '_' . $field_name ] ) ? $this->template_metas[ $this->element_name . '_' . $field_name ] : $default_color ); ?>"/>
		<?php
		}

		function get_font_colors( $label = 'Font Color', $field_name = 'font_color', $default_color = '#000000' ) {
			$font_color = isset( $this->template_metas[ $this->element_name . '_' . $field_name ][0] ) ? $this->template_metas[ $this->element_name . '_' . $field_name ][0] : $default_color;
			?>
			<label><?php echo $label; ?></label>
			<input type="text" class="cp-color-picker" name="<?php echo $this->element_name; ?>_<?php echo $field_name; ?>_post_meta" value="<?php echo esc_attr( $font_color ); ?>"/>
		<?php
		}

		function tcpdf_get_fonts( $prefix = 'document', $default_font = 'helvetica' ) {
			?>
			<label><?php _e( 'Font', 'cp' ); ?></label>
			<select name="<?php echo $prefix; ?>_font_post_meta">
				<?php
				$document_font = isset( $this->template_metas[ $prefix . '_font' ][0] ) ? $this->template_metas[ $prefix . '_font' ][0] : $default_font;
				?>
				<option value='aealarabiya' <?php selected( $document_font, 'aealarabiya', true ); ?>><?php _e( 'Al Arabiya', 'cp' ); ?></option>
				<option value='aefurat' <?php selected( $document_font, 'aefurat', true ); ?>><?php _e( 'Furat', 'cp' ); ?></option>
				<option value='cid0cs' <?php selected( $document_font, 'cid0cs', true ); ?>><?php _e( 'Arial Unicode MS (Simplified Chinese)', 'cp' ); ?></option>
				<option value='cid0jp' <?php selected( $document_font, 'cid0jp', true ); ?>><?php _e( 'Arial Unicode MS (Japanese)', 'cp' ); ?></option>
				<option value='cid0kr' <?php selected( $document_font, 'cid0kr', true ); ?>><?php _e( 'Arial Unicode MS (Korean)', 'cp' ); ?></option>
				<option value='courier <?php selected( $document_font, 'courier', true ); ?>'><?php _e( 'Courier', 'cp' ); ?></option>
				<option value='dejavusans' <?php selected( $document_font, 'dejavusans', true ); ?>><?php _e( 'DejaVu Sans', 'cp' ); ?></option>
				<option value='dejavusanscondensed' <?php selected( $document_font, 'dejavusanscondensed', true ); ?>><?php _e( 'DejaVu Sans Condensed', 'cp' ); ?></option>
				<option value='dejavusansextralight' <?php selected( $document_font, 'dejavusansextralight', true ); ?>><?php _e( 'DejaVu Sans ExtraLight', 'cp' ); ?></option>
				<option value='dejavusansmono' <?php selected( $document_font, 'dejavusansmono', true ); ?>><?php _e( 'DejaVu Sans Mono', 'cp' ); ?></option>
				<option value='dejavuserif' <?php selected( $document_font, 'dejavuserif', true ); ?>><?php _e( 'DejaVu Serif', 'cp' ); ?></option>
				<option value='dejavuserifcondensed' <?php selected( $document_font, 'dejavuserifcondensed', true ); ?>><?php _e( 'DejaVu Serif Condensed', 'cp' ); ?></option>
				<option value='freemono' <?php selected( $document_font, 'freemono', true ); ?>><?php _e( 'FreeMono', 'cp' ); ?></option>
				<option value='freesans' <?php selected( $document_font, 'freesans', true ); ?>><?php _e( 'FreeSans', 'cp' ); ?></option>
				<option value='freeserif' <?php selected( $document_font, 'freeserif', true ); ?>><?php _e( 'FreeSerif', 'cp' ); ?></option>
				<option value='helvetica' <?php selected( $document_font, 'helvetica', true ); ?>><?php _e( 'Helvetica', 'cp' ); ?></option>
				<option value='hysmyeongjostdmedium' <?php selected( $document_font, 'hysmyeongjostdmedium', true ); ?>><?php _e( 'MyungJo Medium (Korean)', 'cp' ); ?></option>
				<option value='kozgopromedium' <?php selected( $document_font, 'kozgopromedium', true ); ?>><?php _e( 'Kozuka Gothic Pro (Japanese Sans-Serif)', 'cp' ); ?></option>
				<option value='kozminproregular' <?php selected( $document_font, 'kozminproregular', true ); ?>><?php _e( 'Kozuka Mincho Pro (Japanese Serif)', 'cp' ); ?></option>
				<option value='msungstdlight' <?php selected( $document_font, 'msungstdlight', true ); ?>><?php _e( 'MSung Light (Traditional Chinese)', 'cp' ); ?></option>
				<option value='pdfacourier' <?php selected( $document_font, 'pdfacourier', true ); ?>><?php _e( 'PDFA Courier', 'cp' ); ?></option>
				<option value='pdfahelvetica' <?php selected( $document_font, 'pdfahelvetica', true ); ?>><?php _e( 'PDFA Helvetica', 'cp' ); ?></option>
				<option value='pdfasymbol' <?php selected( $document_font, 'pdfasymbol', true ); ?>><?php _e( 'PDFA Symbol', 'cp' ); ?></option>
				<option value='pdfatimes' <?php selected( $document_font, 'pdfatimes', true ); ?>><?php _e( 'PDFA Times', 'cp' ); ?></option>
				<option value='pdfazapfdingbats' <?php selected( $document_font, 'pdfazapfdingbats', true ); ?>><?php _e( 'PDFA Zapfdingbats', 'cp' ); ?></option>
				<option value='stsongstdlight' <?php selected( $document_font, 'stsongstdlight', true ); ?>><?php _e( 'STSong Light (Simplified Chinese)', 'cp' ); ?></option>
				<option value='symbol' <?php selected( $document_font, 'symbol', true ); ?>><?php _e( 'Symbol', 'cp' ); ?></option>
				<option value='times' <?php selected( $document_font, 'times', true ); ?>><?php _e( 'Times-Roman', 'cp' ); ?></option>
				<option value='zapfdingbats' <?php selected( $document_font, 'zapfdingbats', true ); ?>><?php _e( 'ZapfDingbats', 'cp' ); ?></option>
				<?php do_action( 'coursepress_template_font' ); ?>
			</select>
		<?php
		}

		function get_font_sizes( $box_title = false, $default_font_size = false ) {
			$font_size = isset( $this->template_metas[ $this->element_name . '_font_size' ][0] ) ? $this->template_metas[ $this->element_name . '_font_size' ][0] : ( $default_font_size ? $default_font_size : 14 );
			?>
			<label><?php
				if ( $box_title ) {
					echo $box_title;
				} else {
					_e( 'Font Size', 'cp' );
				}
				?></label>
			<select name="<?php echo $this->element_name; ?>_font_size_post_meta">
				<?php
				for ( $i = 8; $i <= 100; $i ++ ) {
					?>
					<option value='<?php echo $i; ?>' <?php selected( $font_size, $i, true ); ?>><?php echo $i; ?> <?php _e( 'pt', 'cp' ); ?></option>
				<?php
				}
				?>
			</select>
		<?php
		}

	}

}

function cp_register_template_element( $class_name, $element_title ) {
	global $cp_template_elements;

	if ( ! is_array( $cp_template_elements ) ) {
		$cp_template_elements = array();
	}

	if ( class_exists( $class_name ) ) {
		$cp_template_elements[] = array( $class_name, $element_title );
	} else {
		return false;
	}
}

?>