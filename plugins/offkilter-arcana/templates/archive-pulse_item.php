<?php
/**
 * Pulse archive template — routed via OKArcana_Pulse::route_archive().
 *
 * Renders the Pulse destination inside the active theme's header/footer so `/pulse/`
 * is a first-class, branded landing rather than the theme's generic CPT archive.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="main" class="ok-pulse-archive-main">
    <div class="ok-pulse-archive-wrap">
        <?php echo do_shortcode('[oktv_pulse_destination]'); ?>
    </div>
</main>
<?php
get_footer();
