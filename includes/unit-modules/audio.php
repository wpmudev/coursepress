<?php

class audio_module extends Unit_Module {

	var $order = 4;
	var $name = 'audio_module';
	var $label = 'Audio';
	var $description = '';
	const FRONT_SAVE = false;
	var $response_type = '';

	function __construct() {
		$this->on_create();
	}

	function audio_module() {
		$this->__construct();
	}

	public static function front_main( $data ) {
		$data->name = __CLASS__;
		?>
		<div class="<?php echo $data->name; ?> front-single-module<?php echo( audio_module::FRONT_SAVE == true ? '-save' : '' ); ?>">
			<?php if ( $data->post_title != '' && parent::display_title_on_front( $data ) ) { ?>
				<h2 class="module_title"><?php echo $data->post_title; ?></h2>
			<?php } ?>

			<?php if ( $data->audio_url != '' ) { ?>
				<div class="audio_player">
					<?php
					$attr = array(
						'src'      => $data->audio_url,
						'loop'     => ( checked( $data->loop, 'Yes', false ) ? 'on' : '' ),
						'autoplay' => ( checked( $data->autoplay, 'Yes', false ) ? 'on' : '' ),
					);
					echo wp_audio_shortcode( $attr );
					?>
				</div>
			<?php } ?>
		</div>
	<?php
	}

	function admin_main( $data ) {
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_media();
		wp_enqueue_script( 'media-upload' );

		$supported_audio_extensions = implode( ",", wp_get_audio_extensions() );

		if ( ! empty( $data ) ) {

			if ( ! isset( $data->autoplay ) or empty( $data->autoplay ) ) {
				$data->autoplay = 'No';
			}

			if ( ! isset( $data->loop ) or empty( $data->loop ) ) {
				$data->loop = 'No';
			}
		}
		?>

		<div class="<?php if ( empty( $data ) ) { ?>draggable-<?php } ?>module-holder-<?php echo $this->name; ?> module-holder-title" <?php if (empty( $data )) { ?>style="display:none;"<?php } ?>>

			<h3 class="module-title sidebar-name <?php echo( ! empty( $data->active_module ) ? 'is_active_module' : '' ); ?>" data-panel="<?php echo( ! empty( $data->panel ) ? $data->panel : '' ); ?>" data-id="<?php echo( ! empty( $data->ID ) ? $data->ID : '' ); ?>">
				<span class="h3-label">
					<span class="h3-label-left"><?php echo( isset( $data->post_title ) && $data->post_title !== '' ? $data->post_title : __( 'Untitled', 'cp' ) ); ?></span>
					<span class="h3-label-right"><?php echo $this->label; ?></span>
					<?php
					parent::get_module_move_link();
					?>
				</span>
			</h3>

			<div class="module-content">

				<input type="hidden" name="<?php echo $this->name; ?>_module_page[]" class="module_page" value="<?php echo( isset( $data->module_page ) ? $data->module_page : '' ); ?>"/>
				<input type="hidden" name="<?php echo $this->name; ?>_module_order[]" class="module_order" value="<?php echo( isset( $data->module_order ) ? $data->module_order : 999 ); ?>"/>
				<input type="hidden" name="module_type[]" value="<?php echo $this->name; ?>"/>
				<input type="hidden" name="<?php echo $this->name; ?>_id[]" class="unit_element_id" value="<?php echo esc_attr( isset( $data->ID ) ? $data->ID : '' ); ?>"/>

				<input type="hidden" class="element_id" value="<?php echo esc_attr( isset( $data->ID ) ? $data->ID : '' ); ?>"/>

				<label class="bold-label"><?php
					_e( 'Element Title', 'cp' );
					$this->time_estimation( $data );
					?></label>
				<?php echo $this->element_title_description(); ?>
				<input type="text" class="element_title" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr( isset( $data->post_title ) ? $data->post_title : '' ); ?>"/>

				<?php echo $this->show_title_on_front_element( $data ); ?>

				<div class="audio_url_holder">
					<label><?php echo sprintf( __( 'Put a URL or Browse for an audio file. Supported audio extensions ( %s )', 'cp' ), $supported_audio_extensions ); ?>
						<input class="audio_url" type="text" size="36" name="<?php echo $this->name; ?>_audio_url[]" value="<?php echo esc_attr( ( isset( $data->audio_url ) ? $data->audio_url : '' ) ); ?>"/>
						<input class="audio_url_button" type="button" value="<?php _e( 'Browse', 'cp' ); ?>"/>

						<div class="invalid_extension_message"><?php echo sprintf( __( 'Extension of the file is not valid. Please use one of the following: %s', 'cp' ), $supported_audio_extensions ); ?></div>
					</label>
				</div>

				<div class="audio_additional_controls">
					<label><?php _e( 'Play in a loop', 'cp' ); ?></label>
					<?php
					$data_loop     = ( isset( $data->loop ) ? $data->loop : 'No' );
					$data_autoplay = ( isset( $data->autoplay ) ? $data->autoplay : 'No' );
					?>
					<input type="radio" name="<?php echo $this->name . '_loop[' . ( isset( $data->module_order ) ? $data->module_order : 999 ) . ']'; ?>" value="Yes" <?php checked( $data_loop, 'Yes', true ); ?>/> <?php _e( 'Yes', 'cp' ); ?>
					<br/><br/>
					<input type="radio" name="<?php echo $this->name . '_loop[' . ( isset( $data->module_order ) ? $data->module_order : 999 ) . ']'; ?>" value="No" <?php checked( $data_loop, 'No', true ); ?>/> <?php _e( 'No', 'cp' ); ?>
					<br/><br/>

					<label><?php _e( 'Autoplay', 'cp' ); ?></label>
					<input type="radio" name="<?php echo $this->name . '_autoplay[' . ( isset( $data->module_order ) ? $data->module_order : 999 ) . ']'; ?>" value="Yes" <?php checked( $data_autoplay, 'Yes', true ); ?>/> <?php _e( 'Yes', 'cp' ); ?>
					<br/><br/>
					<input type="radio" name="<?php echo $this->name . '_autoplay[' . ( isset( $data->module_order ) ? $data->module_order : 999 ) . ']'; ?>" value="No" <?php checked( $data_autoplay, 'No', true ); ?>/> <?php _e( 'No', 'cp' ); ?>
					<br/><br/>
				</div>

				<div class="editor_in_place" style="display: none;">
					<?php
					$editor_name    = $this->name . "_content[]";
					$editor_id      = ( esc_attr( isset( $data->ID ) ? 'editor_' . $data->ID : rand( 1, 9999 ) ) );
					$editor_content = htmlspecialchars_decode( ( isset( $data->post_content ) ? $data->post_content : '' ) );

					$args = array(
						"textarea_name" => $editor_name,
						"textarea_rows" => 5,
						"quicktags"     => true,
						"teeny"         => false,
						"editor_class"  => 'cp-editor cp-unit-element',
					);

					$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

					wp_editor( $editor_content, $editor_id, $args );
					?>

				</div>

				<?php
				parent::get_module_delete_link();
				?>

			</div>

		</div>

	<?php
	}

	function on_create() {
		$this->order       = apply_filters( 'coursepress_' . $this->name . '_order', $this->order );
		$this->description = __( 'Add audio files with player to the unit', 'cp' );
		$this->label       = __( 'Audio', 'cp' );
		$this->save_module_data();
		parent::additional_module_actions();
	}

	function save_module_data() {
		global $wpdb, $last_inserted_unit_id, $save_elements;

		if ( isset( $_POST['module_type'] ) && ( $save_elements == true ) ) {

			foreach ( array_keys( $_POST['module_type'] ) as $module_type => $module_value ) {

				if ( $module_value == $this->name ) {
					$data                       = new stdClass();
					$data->ID                   = '';
					$data->unit_id              = '';
					$data->title                = '';
					$data->excerpt              = '';
					$data->content              = '';
					$data->metas                = array();
					$data->metas['module_type'] = $this->name;
					$data->post_type            = 'module';

					if ( isset( $_POST[ $this->name . '_id' ] ) ) {

						foreach ( $_POST[ $this->name . '_id' ] as $key => $value ) {

							//cp_write_log($key);
							// cp_write_log($_POST[$this->name . '_autoplay'][$_POST[$this->name . '_module_order']]);

							$data->ID      = $_POST[ $this->name . '_id' ][ $key ];
							$data->unit_id = ( ( isset( $_POST['unit_id'] ) and ( isset( $_POST['unit'] ) && $_POST['unit'] != '' ) ) ? $_POST['unit_id'] : $last_inserted_unit_id );
							$data->title   = $_POST[ $this->name . '_title' ][ $key ];
							//$data->content = $_POST[$this->name . '_content'][$key];
							$data->metas['module_page']     = $_POST[ $this->name . '_module_page' ][ $key ];
							$data->metas['module_order']    = $_POST[ $this->name . '_module_order' ][ $key ];
							$data->metas['audio_url']       = $_POST[ $this->name . '_audio_url' ][ $key ];
							$data->metas['autoplay']        = $_POST[ $this->name . '_autoplay' ][ $data->metas['module_order'] ];
							$data->metas['loop']            = $_POST[ $this->name . '_loop' ][ $data->metas['module_order'] ];
							$data->metas['time_estimation'] = $_POST[ $this->name . '_time_estimation' ][ $key ];

							// if ( isset($_POST[$this->name . '_show_title_on_front'][$key]) ) {
							//     $data->metas['show_title_on_front'] = $_POST[$this->name . '_show_title_on_front'][$key];
							// } else {
							//     $data->metas['show_title_on_front'] = 'no';
							// }

							$data->metas['show_title_on_front'] = $_POST[ $this->name . '_show_title_field' ][ $key ];

							parent::update_module( $data );
						}
					}
				}
			}
		}
	}

}

cp_register_module( 'audio_module', 'audio_module', 'output' );
?>