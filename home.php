<?php
/**
 * Posts index template (blog home).
 */

get_header();
?>
<main id="main-content" class="site-main w-full overflow-hidden">
<?php
get_template_part('template-parts/hero/subhero', null, matrix_blog_subhero_args());
get_template_part('template-parts/blog/index');
get_template_part('template-parts/flexi/newsletter_001');
?>
</main>
<?php
get_footer();
