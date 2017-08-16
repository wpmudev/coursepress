<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course CoursePress_Course
 */
?>
<div class="wrap coursepress-wrap" id="coursepress-reports-list">
<h1 class="wp-heading-inline"><?php
_e( 'Report: ', 'cp' );
echo ' ';
echo $course->post_title;
?></h1>
    <div class="coursepress-page">
<?php 
											echo '<div class="cp-flex">';
											echo '<span class="gravatar">';
											echo get_avatar( $student->email, 30 );
											echo '</span>';
											echo ' ';
											echo '<span class="user_login">';
											echo $student->user_login;
											echo '</span>';
											echo ' ';
											echo '<span class="display_name">(';
											echo $student->display_name;
											echo ')</span>';
											echo '</div>';

?>
        <table class="coursepress-table">
<?php
foreach ( $units as $unit ) {
?>
					<tbody>
                    <tr style="font-weight:bold; font-size: 4mm; background-color: <?php esc_attr_e( $colors['unit_bg'] ); ?> '; color: ' . esc_attr( $colors['unit'] ) . ';">
                    <th colspan="3"><?php esc_html_e( $unit->post_title ); ?>'</th>
						</tr>
</tbody>
<?php
            }

?>
        </table>
    </div>
</div>
