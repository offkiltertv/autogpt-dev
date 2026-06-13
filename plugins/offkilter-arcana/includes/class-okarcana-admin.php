<?php

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Admin
{
    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'register_menu'));
        add_action('admin_post_okarcana_import_manual', array(__CLASS__, 'handle_import_manual'));
        add_action('admin_post_okarcana_import_csv', array(__CLASS__, 'handle_import_csv'));
        add_action('admin_post_okarcana_import_takeout', array(__CLASS__, 'handle_import_takeout'));
        add_action('admin_post_okarcana_run_scheduler', array(__CLASS__, 'handle_run_scheduler'));
    }

    public static function register_menu()
    {
        add_menu_page(
            __('Arcana', 'offkilter-arcana'),
            __('Arcana', 'offkilter-arcana'),
            'manage_options',
            'okarcana',
            array(__CLASS__, 'render_admin_page'),
            'dashicons-art',
            56
        );
    }

    public static function render_admin_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'offkilter-arcana'));
        }

        global $wpdb;
        $table = OKArcana_DB::table_name();
        $rows = $wpdb->get_results("SELECT id, post_id, source_name, youtube_id, queue_state, imported_at, scheduled_for, published_at FROM {$table} ORDER BY id DESC LIMIT 50", ARRAY_A);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Arcana', 'offkilter-arcana'); ?></h1>

            <?php if (!empty($_GET['okarcana_msg'])) : ?>
                <div class="notice notice-success"><p><?php echo esc_html(wp_unslash($_GET['okarcana_msg'])); ?></p></div>
            <?php endif; ?>

            <h2><?php esc_html_e('Manual YouTube URL Import', 'offkilter-arcana'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('okarcana_import_manual'); ?>
                <input type="hidden" name="action" value="okarcana_import_manual" />
                <p><label><?php esc_html_e('Source Name (Playlist / Collection)', 'offkilter-arcana'); ?></label><br>
                    <input type="text" name="source_name" class="regular-text" value="Manual Import"></p>
                <p><label><?php esc_html_e('YouTube URLs (one per line)', 'offkilter-arcana'); ?></label><br>
                    <textarea name="urls" rows="8" cols="90"></textarea></p>
                <p><button class="button button-primary" type="submit"><?php esc_html_e('Import URLs', 'offkilter-arcana'); ?></button></p>
            </form>

            <hr>
            <h2><?php esc_html_e('CSV Import', 'offkilter-arcana'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                <?php wp_nonce_field('okarcana_import_csv'); ?>
                <input type="hidden" name="action" value="okarcana_import_csv" />
                <p><input type="file" name="csv_file" accept=".csv" required></p>
                <p><button class="button" type="submit"><?php esc_html_e('Import CSV', 'offkilter-arcana'); ?></button></p>
            </form>

            <hr>
            <h2><?php esc_html_e('Google Takeout Import', 'offkilter-arcana'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                <?php wp_nonce_field('okarcana_import_takeout'); ?>
                <input type="hidden" name="action" value="okarcana_import_takeout" />
                <p><input type="file" name="takeout_file" accept=".html,.json,.txt" required></p>
                <p><button class="button" type="submit"><?php esc_html_e('Import Takeout File', 'offkilter-arcana'); ?></button></p>
            </form>

            <hr>
            <h2><?php esc_html_e('Publishing Queue', 'offkilter-arcana'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:1rem;">
                <?php wp_nonce_field('okarcana_run_scheduler'); ?>
                <input type="hidden" name="action" value="okarcana_run_scheduler" />
                <button class="button button-secondary" type="submit"><?php esc_html_e('Run Scheduler Now', 'offkilter-arcana'); ?></button>
            </form>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Post</th>
                        <th>Source</th>
                        <th>YouTube ID</th>
                        <th>State</th>
                        <th>Imported</th>
                        <th>Scheduled</th>
                        <th>Published</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($rows)) : foreach ($rows as $row) : ?>
                    <tr>
                        <td><?php echo (int) $row['id']; ?></td>
                        <td>
                            <?php
                            $edit = !empty($row['post_id']) ? get_edit_post_link((int) $row['post_id']) : '';
                            if ($edit) {
                                echo '<a href="' . esc_url($edit) . '">#' . (int) $row['post_id'] . '</a>';
                            } else {
                                echo '&mdash;';
                            }
                            ?>
                        </td>
                        <td><?php echo esc_html($row['source_name']); ?></td>
                        <td><?php echo esc_html($row['youtube_id']); ?></td>
                        <td><?php echo esc_html($row['queue_state']); ?></td>
                        <td><?php echo esc_html($row['imported_at']); ?></td>
                        <td><?php echo esc_html($row['scheduled_for']); ?></td>
                        <td><?php echo esc_html($row['published_at']); ?></td>
                    </tr>
                <?php endforeach; else : ?>
                    <tr><td colspan="8">No queue records yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public static function handle_import_manual()
    {
        self::assert_admin_nonce('okarcana_import_manual');

        $source_name = isset($_POST['source_name']) ? sanitize_text_field(wp_unslash($_POST['source_name'])) : 'Manual Import';
        $urls = isset($_POST['urls']) ? wp_unslash($_POST['urls']) : '';

        $res = OKArcana_Importer::import_manual_urls($urls, $source_name, 'manual');
        self::redirect_with_result($res);
    }

    public static function handle_import_csv()
    {
        self::assert_admin_nonce('okarcana_import_csv');

        if (empty($_FILES['csv_file']['tmp_name'])) {
            self::redirect_with_message('No CSV file uploaded.');
        }

        $res = OKArcana_Importer::import_csv_file($_FILES['csv_file']['tmp_name']);
        self::redirect_with_result($res);
    }

    public static function handle_import_takeout()
    {
        self::assert_admin_nonce('okarcana_import_takeout');

        if (empty($_FILES['takeout_file']['tmp_name'])) {
            self::redirect_with_message('No Takeout file uploaded.');
        }

        $res = OKArcana_Importer::import_takeout_file($_FILES['takeout_file']['tmp_name']);
        self::redirect_with_result($res);
    }

    public static function handle_run_scheduler()
    {
        self::assert_admin_nonce('okarcana_run_scheduler');
        OKArcana_Scheduler::run_scheduler();
        self::redirect_with_message('Scheduler executed.');
    }

    private static function assert_admin_nonce($action)
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'offkilter-arcana'));
        }

        check_admin_referer($action);
    }

    private static function redirect_with_result($result)
    {
        $msg = sprintf(
            'Import complete: created=%d, skipped=%d, errors=%d',
            (int) $result['created'],
            (int) $result['skipped'],
            isset($result['errors']) ? count((array) $result['errors']) : 0
        );

        self::redirect_with_message($msg);
    }

    private static function redirect_with_message($message)
    {
        $url = add_query_arg(array('page' => 'okarcana', 'okarcana_msg' => rawurlencode($message)), admin_url('admin.php'));
        wp_safe_redirect($url);
        exit;
    }
}
