<?php
/**
 * Selected FAQs accordion (Contact page and similar).
 */
$post_id = get_the_ID() ?: get_queried_object_id();
if (! $post_id) {
    return;
}

$faq_title = (string) (get_field('faq_title', $post_id) ?: get_post_meta($post_id, 'faq_title', true) ?: __('FAQs', 'matrix-starter'));
$faq_btn   = matrix_rd_faq_view_all_link(get_field('faq_button', $post_id) ?: get_post_meta($post_id, 'faq_button', true), $post_id);

$faqs = [];
foreach (matrix_rd_acf_repeater_rows('selected_faqs', ['faq'], $post_id) as $row) {
    $faq_post = matrix_rd_acf_post($row['faq'] ?? null);
    if ($faq_post) {
        $faqs[] = $faq_post;
    }
}

if ($faqs === []) {
    return;
}
?>
<section class="w-full faq-section md:mt-8 md:mb-20">
  <div class="flex flex-col mx-auto px-4 lg:flex-row lg:max-w-max-1514">
    <div class="flex flex-col justify-between w-full px-4 pt-4 pb-8 content md:boxshadow-two md:rounded-form md:border-3 md:border-black-full md:border-solid md:bg-white md:px-12 md:pt-12 lg:w-full">
      <div class="top">
        <h4 class="pb-4 lg:pt-0 lg:p-0 lg:text-lg-font text-lg-font font-reg420 lg:text-leading-10"><?php echo esc_html($faq_title); ?></h4>
        <?php get_template_part('template-parts/components/faq', 'accordion', ['faqs' => $faqs]); ?>
      </div>
      <?php if ($faq_btn['url']) : ?>
      <div class="bottom flex justify-center w-full pt-6">
        <a href="<?php echo esc_url($faq_btn['url']); ?>" class="faq-button items-center border-radius-large flex justify-center h-[60px] w-full md:w-[368px] text-sm-md-font font-reg420 bg-black-full text-white hover:bg-yellow-primary hover:text-black-full"><?php echo esc_html($faq_btn['title'] ?: __('View all', 'matrix-starter')); ?></a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
