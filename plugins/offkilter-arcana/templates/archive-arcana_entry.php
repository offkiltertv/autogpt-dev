<?php
/**
 * Arcana archive template — routed via OKArcana_Post_Types::route_archive().
 *
 * Renders the flagship Arcana destination inside the active theme's
 * header/footer so `/arcana/` is a first-class, branded landing rather than
 * the theme's generic CPT archive. Mirrors templates/archive-pulse_item.php.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="main" class="ok-arcana-archive-main">
    <div class="ok-arcana-archive-wrap">
        <?php echo do_shortcode('[oktv_arcana_destination spotlight_ids="8255,8096,8093"]'); ?>
    </div>
</main>
<?php
get_footer();
