<div class="wrap nosubsub">
    <div class="icon32" id="icon-users"><br></div>
    <h2><?php _e('Students', 'cp'); ?><a class="add-new-h2" href="admin.php?page=students&action=add"><?php _e('Add New', 'cp'); ?></a></h2>

    <form method="get" action="?page=<?php echo esc_attr($page); ?>" class="search-form">
        <p class="search-box">
            <input type='hidden' name='page' value='<?php echo esc_attr($page); ?>' />
            <label class="screen-reader-text"><?php _e('Search Students', 'cp'); ?>:</label>
            <input type="text" value="<?php echo esc_attr($s); ?>" name="s">
            <input type="submit" class="button" value="<?php _e('Search Students', 'cp'); ?>">
        </p>
    </form>

    <br class="clear" />

    <form method="get" action="?page=<?php echo esc_attr($page); ?>" id="posts-filter">
        
    </form>

</div>