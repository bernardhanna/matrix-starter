<?php
/**
 * All FAQs (faq CPT) — used on About and similar pages.
 *
 * @var bool $show_view_all Optional. Show "View all" CTA (default true).
 */
$show_view_all = ! isset($args['show_view_all']) || $args['show_view_all'];

$faqs = new WP_Query([
    'post_type'      => 'faq',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order title',
    'order'          => 'ASC',
]);

if (! $faqs->have_posts()) {
    return;
}

$context_id = get_queried_object_id() ?: (int) get_the_ID();
$faq_btn    = matrix_rd_faq_view_all_link(get_field('faq_button', $context_id), $context_id ?: null);
?>
<section class="w-full faq-section md:mt-8 md:mb-20">
  <div class="flex flex-col mx-auto lg:flex-row lg:max-w-max-1514 px-4">
    <div class="flex flex-col justify-between w-full px-4 pt-4 pb-8 content md:boxshadow-two md:rounded-form md:border-3 md:border-black-full md:bg-white md:px-12 md:pt-12 lg:w-full">
      <div class="top">
        <h4 class="pb-4 lg:text-lg-font text-lg-font font-reg420 lg:text-leading-10"><?php esc_html_e('FAQs', 'matrix-starter'); ?></h4>
        <?php
        $faq_posts = [];
        while ($faqs->have_posts()) {
            $faqs->the_post();
            $faq_posts[] = get_post();
        }
        wp_reset_postdata();
        get_template_part('template-parts/components/faq', 'accordion', ['faqs' => $faq_posts]);
        ?>
      </div>
      <?php if ($show_view_all && $faq_btn['url']) : ?>
      <div class="bottom flex justify-center w-full pt-6">
        <a href="<?php echo esc_url($faq_btn['url']); ?>" class="faq-button items-center border-radius-large flex justify-center h-[60px] w-full md:w-[368px] text-sm-md-font font-reg420 bg-black-full text-white hover:bg-yellow-primary hover:text-black-full"><?php echo esc_html($faq_btn['title'] ?: __('View all', 'matrix-starter')); ?></a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
