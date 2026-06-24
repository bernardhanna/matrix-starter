<?php
/**
 * 404 Not Found — Rolling Donut layout.
 */
status_header(404);
nocache_headers();

get_header();
get_template_part('template-parts/404/404');
get_footer();
