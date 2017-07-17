<?php
/**
 * @var array $students
 */
?>
<script type="text/template" id="coursepress-students-tpl">
    <table class="coursepress-table">
        <thead>
            <tr>
                <th class="column-student"><?php _e( 'Student', 'cp' ); ?></th>
                <th class="column-certified"><?php _e( 'Certified', 'cp' ); ?></th>
                <th class="column-withdraw"><?php _e( 'Withdraw', 'cp' ); ?></th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    <?php print_r( $students ); ?>
</script>