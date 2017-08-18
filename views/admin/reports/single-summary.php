<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course CoursePress_Course
 */
?>
<tr>
    <td><?php
echo '<span class="user_login">';
echo $student->user_login;
echo '</span>';
echo ' ';
echo '<span class="display_name">(';
echo $student->display_name;
echo ')</span>';
?></td>
<td></td>
<td></td>
<td><?php 
if ( isset( $student->progress['completion']['progress'] ) ) {
    echo intval( $student->progress['completion']['progress'] );
    echo '%';
} else {
    echo '--';

}
?></td>
</tr>
