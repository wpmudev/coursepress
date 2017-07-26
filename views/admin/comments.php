<div class="wrap coursepress-wrap">
	<h1 class="wp-heading-inline"><?php _e( 'Comments', 'cp' ); ?></h1>
    <div class="coursepress-page">
        <form method="get" class="cp-search-form" id="cp-search-form">
            <div class="cp-input-clear">
                <input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
                <input type="text" name="s" placeholder="<?php _e( 'Type here...', 'cp' ); ?>" value="<?php echo $search; ?>" />
                <button type="button" id="cp-search-clear" class="cp-btn-clear"><?php _e( 'Clear', 'cp' ); ?></button>
            </div>
            <button type="submit" class="cp-btn cp-btn-active"><?php _e( 'Search', 'cp' ); ?></button>
        </form>

        <?php if ( count( $statuses ) > 0 ) : ?>
            <ul class="subsubsub">
                <?php echo implode( '<li>|</li>', $statuses ); ?>
            </ul>
        <?php endif; ?>

        <table class="coursepress-table" cellspacing="0">
            <thead>
                <tr>
                    <?php foreach ( $columns as $column_id => $column_label ) { ?>
                        <th class="manage-column column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>" id="<?php echo $column_id; ?>">
                            <?php echo $column_label; ?>
                        </th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php
				$odd = 0;
				if ( ! empty( $items ) ) {
					foreach ( $items as $item ) {
						?>
                        <tr class="<?php echo ++$odd % 2 ? 'odd' : 'even'; ?>">
                            <?php foreach ( array_keys( $columns ) as $column_id ) { ?>
                                <td class="column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>">
                                    <?php
									switch ( $column_id ) {
										case 'author':
											printf(
												'%s <span>%s</span>',
												$item->user['avatar'],
												$item->user['userdata']->user_nicename
											);
										break;

										case 'comment':
											comment_text( $item->comment_ID );
										break;

										case 'in_response_to':
											printf(
												'<a href="%s">%s</a>',
												esc_url( $item->parent['link'] ),
												esc_html( $item->parent['title'] )
											);
										break;

										case 'added':
											printf(
												'<strong>%s</strong>%s',
												esc_html( $item->time ),
												esc_html( $item->date )
											);
										break;

										default :
											echo $column_id;
												/**
												 * Trigger to allow custom column value
												 *
												 * @since 3.0
												 * @param string $column_id
												 * @param CoursePress_Course object $item
												 */
												do_action( 'coursespress_commentslist_column', $column_id, $item );
												break;
									}
									?>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php
					}
				} else {
?>
                    <tr class="odd">
                        <td>
                            <?php _e( 'No comments found.', 'cp' ); ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php if ( ! empty( $pagination ) ) : ?>
            <div class="tablenav cp-admin-pagination">
                <?php $pagination->pagination( 'bottom' ); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
