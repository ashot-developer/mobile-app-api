<?php
require 'get-users.php';

function users_menu() {
    add_menu_page('משתמשים', 'משתמשים', 'manage_options', 'users-page', 'users_page_callback', 'dashicons-admin-users', 20);
}
add_action('admin_menu', 'users_menu');

function custom_date_compare($a, $b) {
    $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'lastLoginAt';
    $order = isset($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC']) ? strtoupper($_GET['order']) : 'DESC';

    if (array_key_exists($orderby, $a) && array_key_exists($orderby, $b)) {
        $date_a = format_date($a[$orderby]['_seconds']);
        $date_b = format_date($b[$orderby]['_seconds']);

        if ($date_a == $date_b) {
            return 0;
        }

        if ($order === 'ASC') {
            return strtotime($date_a) > strtotime($date_b) ? 1 : -1;
        } else {
            return strtotime($date_a) < strtotime($date_b) ? 1 : -1;
        }
    }

    return 0;
}

function users_page_callback() {

        $gender = [
            'boy' => 'ילד',
            'girl' => 'ילדה'
        ];

        $api_data = fetch_api_data();
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        if (!empty($search)) {
            $api_data = array_filter($api_data, function ($user) use ($search) {
                return strpos(strtolower($user['childName']), strtolower($search)) !== false
                    || strpos(strtolower($user['parentPhone']), strtolower($search)) !== false;
            });
        }
    
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $items_per_page = 7;
        $total_items = count($api_data);
        $offset = ($current_page - 1) * $items_per_page;

        $api_data_chunk = array_slice($api_data, $offset, $items_per_page);
        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'lastLoginAt';
        $order = isset($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC']) ? strtoupper($_GET['order']) : 'DESC';
    ?>
    <div class="wrap minime-table-wrap">
    <div id="terms-modal" class="modal">
        <div class="terms-res">
        </div>
        <div class="dot-spinner">
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
        </div>
    </div>
    <div id="passport-modal" class="modal" style="text-align: center;">
        <img width="300px" height="500px" src="<?php echo 'data:image/png;base64,' . esc_html($api_data_chunk[0]['passportBase64']); ?>" alt="">
    </div>
        <h1>משתמשים</h1>
        <?php if(isset($_GET['success'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?= $_GET['success']; ?></p>
            </div>
        <?php endif; ?>
        <form method="get" class="minime-table-form">
            <input type="hidden" name="page" value="users-page" />
            <div class="search-box">
                <div class="search_group minime-form-gr">
                    <label for="user-search-input">חיפוש לפי שם הילד/ה או מספר נייד של ההורה</label>
                    <input type="search" id="user-search-input" name="s" value="<?php echo esc_attr($search); ?>" />
                </div>
                <input type="submit" id="search-submit" class="button" value="חיפוש" />
            </div>
        </form>
        <div class="minime__table">
            <table class="wp-list-table widefat striped minime-table">
                <thead>
                    <tr>
                        <th>מזזה ילד/ה</th>
                        <th>אווטר</th>
                        <th>שם הילד/ה</th>
                        <th>מזהה ההורה</th>
                        <th>של ההורה</th>
                        <th>נייד ההורה</th>
                        <th>תעודה מזהה</th>
                        <th>תאריך לידה</th>
                        <th>מין</th>
                        <th>תנאי שימוש</th>
                        <th>מיקום אחרון</th>
                        <th>
                            <a href="<?php echo esc_url(add_query_arg(['orderby' => 'lastLoginAt', 'order' => ($order === 'ASC' ? 'DESC' : 'ASC')])); ?>">
                                תאריך התחברות אחרון     
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo esc_url(add_query_arg(['orderby' => 'registerAt', 'order' => ($order === 'ASC' ? 'DESC' : 'ASC')])); ?>">
                                תאריך רישום 
                            </a>
                        </th>
                        <th>פעולה</th>
                        <!-- Add more columns as needed -->
                    </tr>
                </thead>
                <?php if(!empty($api_data_chunk)) : ?>
                <tbody>
                    <?php
                    usort($api_data_chunk, 'custom_date_compare');

                    foreach ($api_data_chunk as $user): ?>
                        <tr class="<?php echo $user['isBlocked'] ? 'blocked' : ''; ?>">
                            <td><span><?php echo esc_html($user['childId']); ?></span></td>
                            <td class="child__avatar">
                                <span><img src="<?php echo 'data:image/png;base64,' . esc_html($user['avatarBase64']); ?>" alt=""></span>
                            </td>
                            <td><span><?php echo esc_html($user['childName']); ?></span></td>
                            <td><span><?php echo esc_html($user['parentId']); ?></span></td>
                            <td><span><?php echo isset($user['parentName']) ? esc_html($user['parentName']) : '-'; ?></span></td>
                            <td><span class="phone_nb"><?php echo esc_html($user['parentPhone']); ?></span></td>
                            <td>
                                <a href="#passport-modal" data-modal="#passport-modal"><img class="passport" src="<?php echo 'data:image/png;base64,' . esc_html($user['passportBase64']); ?>" alt=""></a>
                            </td>
                            <td><span><?php echo format_date(strtotime($user['birthday'])); ?></span></td>
                            <td><span><?php echo $gender[$user['gender']]; ?></span></td>
                            <td><a href="#terms-modal" data-id="<?php echo esc_html($user['parentId']); ?>" data-modal="#terms-modal"><span><?= $user['isAgreedToTerms'] ? 'אישר' : 'לא ודאי';?></span></a></td>
                            <td><span><?php echo implode(', ', $user['coords']) ?></span></td>

                            <td><span><?php echo format_date($user['lastLoginAt']['_seconds']) ?></span></td>
                            <td><span><?php echo format_date($user['registerAt']['_seconds']); ?></span></td>
                            <td>
                                <span>
                                <form method="post" action="<?= get_stylesheet_directory_uri() ?>/app-api/users/block-user.php">
                                    <input type="hidden" name="user_id" value="<?php echo esc_attr($user['parentId']); ?>">
                                    <input type="hidden" name="user_blocked" value='<?php echo esc_attr($user['isBlocked']); ?>'>
                                    <button type="submit" class="button">
                                        <?php echo $user['isBlocked'] ? 'בטל חסימה' : 'חסום משתמש'; ?>
                                    </button>
                                </form>
                                </span>
                            </td>
                            <!-- Add more columns as needed -->
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php else: ?>
                    <tr>
                        <td colspan="14">
                            <div class="empty_table">
                            אין נתונים
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
        <?php
        // Pagination
        $total_pages = ceil($total_items / $items_per_page);

        if ($total_pages > 1) {
            echo '<div class="tablenav minime-pagination">';
            echo paginate_links(array(
                'base'      => add_query_arg('paged', '%#%'),
                'format'    => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total'     => $total_pages,
                'current'   => $current_page,
            ));
            echo '</div>';
        }
        ?>
    </div>
    <?php
}