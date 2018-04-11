<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course CoursePress_Course
 */
?>
<tr>
    <td>
		<?php
		echo '<span class="user_login">';
		echo $student->user_login;
		echo '</span>';
		echo ' ';
		echo '<span class="display_name">(';
		echo $student->display_name;
		echo ')</span>';
		?>
	</td>
    <td class="column-answered"><?php echo esc_html( $student->course_answered ); ?></td>
    <td class="column-average"><?php echo esc_html( $student->average ); ?>%</td>
    <td class="column-average">
		<?php
			if ( isset( $student->progress['completion']['progress'] ) ) {
				echo intval( $student->progress['completion']['progress'] );
				echo '%';
			} else {
				echo '--';
			}
		?>
	</td>
</tr>
