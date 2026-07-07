<?php
/**
 * Front page — hero + flexi blocks (no breadcrumbs / empty content wrapper).
 *
 * @package Matrix_Starter
 */

get_header();
?>
<main id="main-content" class="site-main w-full overflow-hidden">
    <?php
    if (function_exists('matrix_rd_load_home_sections')) {
        matrix_rd_load_home_sections();
    } else {
        load_hero_templates();
        load_flexible_content_templates();
    }
    ?>
</main>
<?php
get_footer();
