<?php
$columns = array(
    "ID" => __('Student ID', 'cp'),
    "user_firstname" => __('First Name', 'cp'),
    "user_lastname" => __('Surname', 'cp'),
    "group" => __('Group', 'cp'),
    "edit" => __('Edit', 'cp'),
    "delete" => __('Delete', 'cp'),
);

//$search_args['users_per_page'] = 1;
$search_args['meta_key'] = 'enrolled_course_group_'.$_GET['course_id'];
$search_args['meta_value'] = '';
$wp_user_search = new Student_Search($usersearch, $userspage, $search_args);
?>
<div id="students_accordion">
    
    <?php if($wp_user_search->get_results()) { ?>
    <div class="sidebar-name no-movecursor">
        <h3>Default</h3>
    </div>

    <div>
        <table cellspacing="0" class="widefat fixed">
            <thead>
                <tr>
                    <?php
                    foreach ($columns as $key => $col) {
                        ?>
                        <th style="" class="manage-column column-<?php echo $key; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
                        <?php
                    }
                    ?>
                </tr>
            </thead>


            <tbody>
                <?php
                $style = '';

                foreach ($wp_user_search->get_results() as $user) {

                    $user_object = new Student($user->ID);
                    $roles = $user_object->roles;
                    $role = array_shift($roles);

                    $style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
                    ?>
                    <tr id='user-<?php echo $user_object->ID; ?>' <?php echo $style; ?>>
                        
                        <td <?php echo $style; ?>><?php echo $user_object->ID; ?></td>
                        <td <?php echo $style; ?>><?php echo $user_object->first_name; ?></td>
                        <td <?php echo $style; ?>><?php echo $user_object->last_name; ?></td>
                        <td <?php echo $style; ?>>--Group--</td>
                        <td <?php echo $style; ?>><a href="">Edit</a></td>
                        <td <?php echo $style; ?>><a href="">Remove</a></td>
                                                
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <?php } ?>
    
    <div class="sidebar-name no-movecursor">
        <h3>Class 1</h3>
    </div>

    <div>
        <table cellspacing="0" class="widefat fixed">
            <thead>
                <tr>
                    <?php
                    foreach ($columns as $key => $col) {
                        ?>
                        <th style="" class="manage-column column-<?php echo $key; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
                        <?php
                    }
                    ?>
                </tr>
            </thead>


            <tbody>
                <?php
                $style = '';

                foreach ($wp_user_search->get_results() as $user) {

                    $user_object = new Student($user->ID);
                    $roles = $user_object->roles;
                    $role = array_shift($roles);

                    $style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
                    ?>
                    <tr id='user-<?php echo $user_object->ID; ?>' <?php echo $style; ?>>
                        
                        <td <?php echo $style; ?>><?php echo $user_object->ID; ?></td>
                        <td <?php echo $style; ?>><?php echo $user_object->first_name; ?></td>
                        <td <?php echo $style; ?>><?php echo $user_object->last_name; ?></td>
                        <td <?php echo $style; ?>>--Group--</td>
                        <td <?php echo $style; ?>><a href="">Edit</a></td>
                        <td <?php echo $style; ?>><a href="">Remove</a></td>
                                                
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    
    
    <div class="sidebar-name no-movecursor">
        <h3>Class 2</h3>
    </div>

    <div>
        <p>
            Sed non urna. Donec et ante. Phasellus eu ligula. Vestibulum sit amet
            purus. Vivamus hendrerit, dolor at aliquet laoreet, mauris turpis porttitor
            velit, faucibus interdum tellus libero ac justo. Vivamus non quam. In
            suscipit faucibus urna.
        </p>
    </div>

    <div class="sidebar-name no-movecursor">
        <h3>Class 3</h3>
    </div>

    <div>
        <p>
            Nam enim risus, molestie et, porta ac, aliquam ac, risus. Quisque lobortis.
            Phasellus pellentesque purus in massa. Aenean in pede. Phasellus ac libero
            ac tellus pellentesque semper. Sed ac felis. Sed commodo, magna quis
            lacinia ornare, quam ante aliquam nisi, eu iaculis leo purus venenatis dui.
        </p>
        <ul>
            <li>List item one</li>
            <li>List item two</li>
            <li>List item three</li>
        </ul>
    </div>

    <div class="sidebar-name no-movecursor">
        <h3>Class 4</h3>
    </div>

    <div>
        <p>
            Cras dictum. Pellentesque habitant morbi tristique senectus et netus
            et malesuada fames ac turpis egestas. Vestibulum ante ipsum primis in
            faucibus orci luctus et ultrices posuere cubilia Curae; Aenean lacinia
            mauris vel est.
        </p>
        <p>
            Suspendisse eu nisl. Nullam ut libero. Integer dignissim consequat lectus.
            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per
            inceptos himenaeos.
        </p>
    </div>
</div>