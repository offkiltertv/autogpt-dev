<?php
/**
 * OKArcana_Creator — Creator relations surface.
 *
 * Provides:
 *   [oktv_footer_brand]      Reimagined footer brand messaging block.
 *   [oktv_why_creator]       "Why am I on OFFKILTER?" explainer for creators.
 *   [oktv_creator_optout]    Creator opt-out request form (works standalone
 *                            or is embedded inside [oktv_why_creator]).
 *
 * Opt-out submissions are stored as a private `ok_creator_optout` post type so
 * the operator can review and action them in wp-admin, and the site admin is
 * emailed on each submission. Ownership is asserted by the creator's YouTube
 * channel + email today; automated verification via Google (YouTube/Gmail)
 * sign-in is the documented next step — see docs/creator-optout.md.
 */

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Creator
{
    const OPTOUT_POST_TYPE = 'ok_creator_optout';
    const NONCE_ACTION     = 'okarcana_creator_optout';

    public static function init()
    {
        add_action('init', array(__CLASS__, 'register_optout_store'));

        add_shortcode('oktv_footer_brand',   array(__CLASS__, 'render_footer_brand'));
        add_shortcode('oktv_why_creator',    array(__CLASS__, 'render_why_creator'));
        add_shortcode('oktv_creator_optout', array(__CLASS__, 'render_optout_form'));

        add_action('admin_post_nopriv_' . self::NONCE_ACTION, array(__CLASS__, 'handle_optout'));
        add_action('admin_post_' . self::NONCE_ACTION, array(__CLASS__, 'handle_optout'));
    }

    // -------------------------------------------------------------------------
    // Storage — private CPT for opt-out requests (operator-reviewable).
    // -------------------------------------------------------------------------

    public static function register_optout_store()
    {
        register_post_type(self::OPTOUT_POST_TYPE, array(
            'labels' => array(
                'name'          => __('Opt-Out Requests', 'offkilter-arcana'),
                'singular_name' => __('Opt-Out Request', 'offkilter-arcana'),
                'edit_item'     => __('Review Opt-Out Request', 'offkilter-arcana'),
            ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_icon'           => 'dashicons-shield',
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'has_archive'         => false,
            'rewrite'             => false,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'supports'            => array('title', 'editor'),
        ));
    }

    // -------------------------------------------------------------------------
    // [oktv_footer_brand]
    // Premium product footer. Operator embeds in the Elementor footer template.
    // Renders a 3-column layout on desktop, 2-column on tablet, stacked on mobile.
    //
    // Attributes:
    //   tagline          string  "Where the signal finds you."
    //   line             string  "" (empty = platform default line)
    //   creator_href     string  "/why-am-i-here/"
    //   privacy_href     string  "" (empty = no Privacy link rendered)
    //   terms_href       string  "" (empty = no Terms link rendered)
    //   social_x         string  "" (URL for X/Twitter, empty = hidden)
    //   social_youtube   string  "" (URL for YouTube, empty = hidden)
    //   social_instagram string  "" (URL for Instagram, empty = hidden)
    //   show_version     int     0  (1 = display plugin version in bottom bar)
    //   wrapper_class    string  ""
    // -------------------------------------------------------------------------

    public static function render_footer_brand($atts)
    {
        $atts = shortcode_atts(array(
            'tagline'          => 'Where the signal finds you.',
            'line'             => '',
            'creator_href'     => '/why-am-i-here/',
            'privacy_href'     => '',
            'terms_href'       => '',
            'social_x'         => '',
            'social_youtube'   => '',
            'social_instagram' => '',
            'show_version'     => 0,
            'wrapper_class'    => '',
        ), $atts, 'oktv_footer_brand');

        $outer_class = 'ok-footer-brand';
        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        $year = (int) current_time('Y');

        $brand_line = $atts['line']
            ? esc_html($atts['line'])
            : esc_html('OFFKILTER is where short clips, intuitive readings, and fast Pulse observations surface from creators worth returning for.');

        $pillars = array(
            'Pulse'     => '/pulse/',
            'Signals'   => '/video-category/signals/',
            'Arcana'    => '/video-category/arcana/',
            'Creators'  => '/member-list/',
            'Community' => '/community/',
        );

        $socials = array();
        if ($atts['social_x']) {
            $socials['X']  = esc_url($atts['social_x']);
        }
        if ($atts['social_youtube']) {
            $socials['YT'] = esc_url($atts['social_youtube']);
        }
        if ($atts['social_instagram']) {
            $socials['IG'] = esc_url($atts['social_instagram']);
        }

        ob_start();
        ?>
        <footer class="<?php echo esc_attr($outer_class); ?>" role="contentinfo">
            <div class="ok-footer-brand__inner">

                <div class="ok-footer-brand__col ok-footer-brand__col--brand">
                    <p class="ok-footer-brand__mark">OFFKILTER</p>
                    <p class="ok-footer-brand__tagline"><?php echo esc_html($atts['tagline']); ?></p>
                    <p class="ok-footer-brand__line"><?php echo $brand_line; ?></p>

                    <?php if (!empty($socials)) : ?>
                        <div class="ok-footer-brand__social">
                            <?php foreach ($socials as $label => $href) : ?>
                                <a href="<?php echo $href; ?>"
                                   class="ok-footer-brand__social-link"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   aria-label="<?php printf(esc_attr__('OFFKILTER on %s', 'offkilter-arcana'), $label); ?>">
                                    <?php echo esc_html($label); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="ok-footer-brand__col ok-footer-brand__col--nav">
                    <p class="ok-footer-brand__col-heading"><?php esc_html_e('Discover', 'offkilter-arcana'); ?></p>
                    <nav class="ok-footer-brand__nav" aria-label="<?php esc_attr_e('Footer sections', 'offkilter-arcana'); ?>">
                        <?php foreach ($pillars as $label => $href) : ?>
                            <a href="<?php echo esc_url($href); ?>"><?php echo esc_html($label); ?></a>
                        <?php endforeach; ?>
                    </nav>
                </div>

                <div class="ok-footer-brand__col ok-footer-brand__col--meta">
                    <p class="ok-footer-brand__col-heading"><?php esc_html_e('Platform', 'offkilter-arcana'); ?></p>
                    <div class="ok-footer-brand__creator">
                        <a href="<?php echo esc_url($atts['creator_href']); ?>" class="ok-footer-brand__creator-link">
                            <?php esc_html_e('Creators: Why am I on OFFKILTER?', 'offkilter-arcana'); ?>
                        </a>
                    </div>
                </div>

            </div><!-- /.ok-footer-brand__inner -->

            <div class="ok-footer-brand__bottom">
                <p class="ok-footer-brand__legal">
                    &copy; <?php echo esc_html($year); ?> OFFKILTER.TV<?php
                    if ($atts['privacy_href']) {
                        printf(
                            ' &middot; <a href="%s">%s</a>',
                            esc_url($atts['privacy_href']),
                            esc_html__('Privacy', 'offkilter-arcana')
                        );
                    }
                    if ($atts['terms_href']) {
                        printf(
                            ' &middot; <a href="%s">%s</a>',
                            esc_url($atts['terms_href']),
                            esc_html__('Terms', 'offkilter-arcana')
                        );
                    }
                    ?>
                </p>
                <?php if (!empty($atts['show_version'])) : ?>
                    <p class="ok-footer-brand__version">v<?php echo esc_html(OKARCANA_VERSION); ?></p>
                <?php endif; ?>
            </div>
        </footer>
        <?php
        return (string) ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // [oktv_why_creator]
    // "Why am I on OFFKILTER?" explainer page body. Includes the opt-out form
    // unless show_form="0".
    //
    // Attributes:
    //   headline      string  "Why am I on OFFKILTER?"
    //   show_form     int     1
    //   wrapper_class string  ""
    // -------------------------------------------------------------------------

    public static function render_why_creator($atts)
    {
        $atts = shortcode_atts(array(
            'headline'      => 'Why am I on OFFKILTER?',
            'show_form'     => 1,
            'wrapper_class' => '',
        ), $atts, 'oktv_why_creator');

        $outer_class = 'ok-why-creator';
        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        ob_start();
        ?>
        <section class="<?php echo esc_attr($outer_class); ?>" role="region" aria-label="<?php echo esc_attr($atts['headline']); ?>">
            <h1 class="ok-why-creator__headline"><?php echo esc_html($atts['headline']); ?></h1>

            <p class="ok-why-creator__lede">
                Life has a peculiar way of informing and connecting us all.
            </p>

            <div class="ok-why-creator__body">
                <p>
                    Your work surfaced here because something in it resonated with the
                    spirit of Arcana — the intuitive, the searching, the quietly
                    remarkable. OFFKILTER gathers signals like yours so that the people
                    looking for them can actually find them.
                </p>
                <p>
                    We believe attention should follow meaning, not algorithms alone.
                    If a clip of yours was selected, it's because a human felt it
                    belonged in the conversation.
                </p>
                <p>
                    <strong>This is your work, and you decide.</strong> If you'd rather
                    not take part, you can opt out below. Verify that you're the creator
                    using your YouTube or Gmail account, take control of how your content
                    appears on OFFKILTER, and remove it entirely if you choose.
                </p>
            </div>

            <?php if (!empty($atts['show_form'])) : ?>
                <?php echo self::render_optout_form(array()); ?>
            <?php endif; ?>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // [oktv_creator_optout]
    // Creator opt-out request form.
    //
    // Attributes:
    //   title         string  "Opt out of OFFKILTER"
    //   wrapper_class string  ""
    // -------------------------------------------------------------------------

    public static function render_optout_form($atts)
    {
        $atts = shortcode_atts(array(
            'title'         => 'Opt out of OFFKILTER',
            'wrapper_class' => '',
        ), $atts, 'oktv_creator_optout');

        $outer_class = 'ok-optout';
        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        $status = isset($_GET['ok_optout']) ? sanitize_key(wp_unslash($_GET['ok_optout'])) : '';

        ob_start();
        ?>
        <div class="<?php echo esc_attr($outer_class); ?>" id="opt-out">
            <h2 class="ok-optout__title"><?php echo esc_html($atts['title']); ?></h2>

            <?php if ($status === 'received') : ?>
                <div class="ok-optout__notice ok-optout__notice--success" role="status">
                    <p>
                        <?php esc_html_e('Thank you — your request has been received. We\'ll verify ownership and follow up at the email you provided.', 'offkilter-arcana'); ?>
                    </p>
                </div>
            <?php elseif ($status === 'error') : ?>
                <div class="ok-optout__notice ok-optout__notice--error" role="alert">
                    <p>
                        <?php esc_html_e('Something didn\'t go through. Please check the required fields and try again.', 'offkilter-arcana'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <p class="ok-optout__intro">
                <?php esc_html_e('Tell us which channel is yours. Verifying with your YouTube or Gmail account confirms you own the content so we can act on your request.', 'offkilter-arcana'); ?>
            </p>

            <form class="ok-optout__form" method="post"
                  action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="<?php echo esc_attr(self::NONCE_ACTION); ?>">
                <?php wp_nonce_field(self::NONCE_ACTION); ?>

                <p class="ok-optout__field">
                    <label for="ok-optout-channel"><?php esc_html_e('YouTube channel URL or handle', 'offkilter-arcana'); ?> <span aria-hidden="true">*</span></label>
                    <input type="text" id="ok-optout-channel" name="ok_optout_channel"
                           required placeholder="https://youtube.com/@yourchannel">
                </p>

                <p class="ok-optout__field">
                    <label for="ok-optout-email"><?php esc_html_e('Email (YouTube/Gmail preferred)', 'offkilter-arcana'); ?> <span aria-hidden="true">*</span></label>
                    <input type="email" id="ok-optout-email" name="ok_optout_email"
                           required placeholder="you@gmail.com">
                </p>

                <p class="ok-optout__field">
                    <label for="ok-optout-name"><?php esc_html_e('Creator / channel name', 'offkilter-arcana'); ?></label>
                    <input type="text" id="ok-optout-name" name="ok_optout_name">
                </p>

                <p class="ok-optout__field">
                    <label for="ok-optout-message"><?php esc_html_e('Anything you\'d like us to know', 'offkilter-arcana'); ?></label>
                    <textarea id="ok-optout-message" name="ok_optout_message" rows="4"></textarea>
                </p>

                <p class="ok-optout__field ok-optout__field--check">
                    <label>
                        <input type="checkbox" name="ok_optout_confirm" value="1" required>
                        <?php esc_html_e('I am the owner of this channel and I request to opt out of OFFKILTER.', 'offkilter-arcana'); ?>
                    </label>
                </p>

                <?php // Honeypot — must stay empty. ?>
                <p class="ok-optout__hp" aria-hidden="true">
                    <label>Leave this field empty
                        <input type="text" name="ok_hp" tabindex="-1" autocomplete="off">
                    </label>
                </p>

                <p class="ok-optout__actions">
                    <button type="submit" class="ok-btn"><?php esc_html_e('Submit opt-out request', 'offkilter-arcana'); ?></button>
                </p>

                <p class="ok-optout__fineprint">
                    <?php esc_html_e('Automated verification with Google sign-in is coming soon. Until then, we confirm ownership manually using the details above.', 'offkilter-arcana'); ?>
                </p>
            </form>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // Form handler.
    // -------------------------------------------------------------------------

    public static function handle_optout()
    {
        $redirect = wp_get_referer();
        if (!$redirect) {
            $redirect = home_url('/');
        }

        // Nonce + honeypot.
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(wp_unslash($_POST['_wpnonce']), self::NONCE_ACTION)) {
            wp_safe_redirect(add_query_arg('ok_optout', 'error', $redirect));
            exit;
        }

        if (!empty($_POST['ok_hp'])) {
            // Bot: silently treat as success to avoid signaling the trap.
            wp_safe_redirect(add_query_arg('ok_optout', 'received', $redirect));
            exit;
        }

        $channel = isset($_POST['ok_optout_channel']) ? sanitize_text_field(wp_unslash($_POST['ok_optout_channel'])) : '';
        $email   = isset($_POST['ok_optout_email']) ? sanitize_email(wp_unslash($_POST['ok_optout_email'])) : '';
        $name    = isset($_POST['ok_optout_name']) ? sanitize_text_field(wp_unslash($_POST['ok_optout_name'])) : '';
        $message = isset($_POST['ok_optout_message']) ? sanitize_textarea_field(wp_unslash($_POST['ok_optout_message'])) : '';
        $confirm = !empty($_POST['ok_optout_confirm']);

        if ($channel === '' || !is_email($email) || !$confirm) {
            wp_safe_redirect(add_query_arg('ok_optout', 'error', $redirect));
            exit;
        }

        $title = $name !== '' ? $name : $channel;

        $body  = "Channel: {$channel}\n";
        $body .= "Email: {$email}\n";
        $body .= "Name: {$name}\n\n";
        $body .= "Message:\n{$message}\n";

        $post_id = wp_insert_post(array(
            'post_type'    => self::OPTOUT_POST_TYPE,
            'post_status'  => 'publish',
            'post_title'   => sprintf('Opt-out: %s', $title),
            'post_content' => $body,
        ), true);

        if (!is_wp_error($post_id) && $post_id) {
            update_post_meta($post_id, '_ok_optout_channel', $channel);
            update_post_meta($post_id, '_ok_optout_email', $email);
            update_post_meta($post_id, '_ok_optout_name', $name);
            update_post_meta($post_id, '_ok_optout_received', current_time('mysql'));
        }

        // Notify the operator.
        $admin_email = get_option('admin_email');
        if ($admin_email) {
            $subject = sprintf('[OFFKILTER] Creator opt-out request: %s', $title);
            wp_mail($admin_email, $subject, $body);
        }

        wp_safe_redirect(add_query_arg('ok_optout', 'received', $redirect) . '#opt-out');
        exit;
    }
}
