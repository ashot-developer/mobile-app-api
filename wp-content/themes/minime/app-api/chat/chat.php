<?php 
require 'get-chats.php';

function chat_menu() {
    add_menu_page('ניהול התכתבויות', 'ניהול התכתבויות', 'manage_options', 'chats-page', 'chat_page_callback', 'dashicons-format-chat', 22);
}
add_action('admin_menu', 'chat_menu');

function chat_page_callback() {
    $chat_data = fetch_chat_data();
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $show_reported = isset($_GET['show_reported']) && $_GET['show_reported'] === 'true';

    // Filter based on search query
    if (!empty($search)) {
        $chat_data = array_filter($chat_data, function ($chat) use ($search) {
            return strpos(strtolower($chat['hostPhone']), strtolower($search)) !== false
                || strpos(strtolower($chat['guestPhone']), strtolower($search)) !== false
                || strpos(strtolower($chat['hostName']), strtolower($search)) !== false
                || strpos(strtolower($chat['guestName']), strtolower($search)) !== false;
        });
    }

    // Filter to show only reported users
    if ($show_reported) {
        $chat_data = array_filter($chat_data, function ($chat) {
            return !empty($chat['reportedById']);
        });
    }

    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $items_per_page = 7;
    $total_items = count($chat_data);
    $offset = ($current_page - 1) * $items_per_page;

    $chat_data_chunk = array_slice($chat_data, $offset, $items_per_page);
    ?>
<div class="wrap minime-table-wrap">
    <div id="chat-modal" class="modal chat-modal">
        <div class="middle">
            <div class="voldemort">
                
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
    </div>
    <h1>נתוני הצ׳אט</h1>
    <form method="get" class="minime-table-form">
        <input type="hidden" name="page" value="chats-page" />
        <div class="search-box">
            <div class="search_group minime-form-gr">
                <label for="chat-search-input">חפש לפי מספר הנייד של אחד ההורים</label>
                <input type="search" id="chat-search-input" name="s" value="<?php echo esc_attr($search); ?>" />
            </div>
            <input type="submit" id="search-submit" class="button" value="חיפוש" />
            <button type="submit" name="show_reported" value="<?php echo $show_reported ? 'false' : 'true'; ?>" class="button show__reported">
                <?php echo $show_reported ? 'הצג את כל הצ׳אטים' : 'ניהול התכתבויות'; ?>
            </button>
        </div>
    </form>
    <div class="minime__table">
    <table class="wp-list-table widefat striped minime-table">
        <thead>
            <tr>
                <th>מזהה צ׳אט</th>
                <th>מזהה ילד 1</th>
                <th>מזהה ילד 2</th>
                <th>ילד/ה 1</th>
                <th>ילד/ה 2</th>
                <th>מספר נייד של הורה 1</th>
                <th>מספר נייד של ההורה 2</th>
                <th> דווח עד ידי</th>
                <th>דווח על ידי מזהה</th>
                <th>זמן דיווח</th>
                <th>פעולה</th>
            </tr>
        </thead>
        <?php if(!empty($chat_data_chunk)) : ?>
        <tbody>
            <?php
                usort($chat_data_chunk, 'custom_date_compare');

                foreach ($chat_data_chunk as $chat): ?>
            <tr>
                <td><span><?php echo esc_html($chat['id']); ?></span></td>
                <td><span><?php echo esc_html($chat['hostId']); ?></span></td>
                <td><span><?php echo esc_html($chat['guestId']); ?></span></td>
                <td><span><?php echo esc_html($chat['hostName']); ?></span></td>
                <td><span><?php echo esc_html($chat['guestName']); ?></span></td>
                <td><span class="phone_nb"><?php echo esc_html($chat['hostPhone']); ?></span></td>
                <td><span class="phone_nb"><?php echo esc_html($chat['guestPhone']); ?></span></td>
                <td><span><?php echo $chat['reportedByName'] ?? "-" ?></span></td>
                <td><span><?php echo $chat['reportedById'] ?? "-" ?></span></td>
                <td><span><?php echo $chat['reportedAt'] ?? "-" ?></span></td>
                <td>
                    <span>
                        <a href="#chat-modal" data-modal="#chat-modal" data-host="<?= $chat['hostId'] ?>" data-id="<?= $chat['id'] ?>" type="submit" name="block_unblock_submit" class="button open-dialog">
                        הצג התכתבות
                        </a>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <?php else: ?>
            <tr>
                <td colspan="11">
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