<?php
if(!class_exists('Unit_Module')) {

	class Unit_Module {

		var $data;
		var $name = 'none';
		var $label = 'None Set';
		var $description = '';

		// The area of the rule - public, admin or core
		var $rulearea = 'public';

		//var $level_id = false;

		function __construct( $level_id = false ) {
			// Store the level this rule is for
			//$this->level_id = $level_id;

			$this->on_creation();
		}

		function Unit_Module( $tst ) {
			$this->__construct();
		}

		function admin_sidebar($data) {
			?>
			<li class='draggable-level' id='<?php echo $this->name; ?>' <?php if($data === true) echo "style='display:none;'"; ?>>
				<div class='action action-draggable'>
					<div class='action-top closed'>
					<a href="#available-actions" class="action-button hide-if-no-js"></a>
					<?php _e($this->label,'cp'); ?>
					</div>
					<div class='action-body closed'>
						<?php if(!empty($this->description)) { ?>
							<p>
								<?php _e($this->description, 'cp'); ?>
							</p>
						<?php } ?>
						<p>
							<a href='#addtopositive' class='action-to-positive' title='<?php _e('Add this rule to the positive area of the membership level.','membership'); ?>'><?php _e('Add to Positive rules','membership'); ?></a><a href='#addtonegative' class='action-to-negative' title='<?php _e('Add this rule to the negative area of the membership level.','membership'); ?>'><?php _e('Add to Negative rules','membership'); ?></a>
						</p>
					</div>
				</div>
			</li>
			<?php
		}

		function admin_main($data) {

		}

		// Operations
		function on_creation() {

		}

		function on_positive($data) {
			$this->data = $data;
		}

		function on_negative($data) {
			$this->data = $data;
		}

		// Getters and Setters
		function is_adminside() {
			if( in_array($this->rulearea, array('admin', 'core')) ) {
				return true;
			} else {
				return false;
			}
		}


	}

}
?>