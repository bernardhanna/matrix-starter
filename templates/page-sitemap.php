<?php
/*
Template Name: Sitemap Page
*/

get_header();
?>
<main id="main-content" class="w-full min-h-screen overflow-hidden site-main">
    <?php load_hero_templates(); ?>
        <section class="relative flex overflow-hidden">
            <div class="w-full mx-auto max-w-[1095px] max-md:px-5">
                <article class="relative wp_editor py-12">
                    <div class="entry-content">
                        <div class="grid w-full gap-10 md:grid-cols-2">
                            <div>
                                <h2 class="font-sans text-[30px] font-bold not-italic leading-[38px] text-[var(--Blue-SR-500,#00628F)]">Pages</h2>
                                <ul class="mt-4 space-y-2 list-disc pl-5">
                            <?php
                            $pages = get_pages();
                            foreach ($pages as $page) {
                                echo '<li><a href="' . esc_url(get_permalink($page->ID)) . '" class="text-[var(--Blue-SR-500,#00628F)]">' . esc_html($page->post_title) . '</a></li>';
                            }
                            ?>
                                </ul>
                            </div>
                            <div>
                                <h2 class="font-sans text-[30px] font-bold not-italic leading-[38px] text-[var(--Blue-SR-500,#00628F)]">Posts</h2>
                                <ul class="mt-4 space-y-2 list-disc pl-5">
                            <?php
                            $posts = get_posts(['numberposts' => -1, 'post_status' => 'publish']);
                            foreach ($posts as $post) {
                                echo '<li><a href="' . esc_url(get_permalink($post->ID)) . '" class="text-[var(--Blue-SR-500,#00628F)]">' . esc_html($post->post_title) . '</a></li>';
                            }
                            ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    <?php load_flexible_content_templates(); ?>
</main>
<?php get_footer(); ?>
