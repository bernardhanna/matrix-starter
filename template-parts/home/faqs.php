<?php
/**
 * Home FAQ accordion (selected FAQs from front page).
 */
$post_id = (int) get_option('page_on_front');
$faq_img = matrix_rd_acf_image(get_field('faq_image', $post_id));
$faq_btn = matrix_rd_faq_view_all_link(get_field('faq_button', $post_id), $post_id);
$rows    = matrix_rd_acf_repeater_rows('selected_faqs', ['faq'], $post_id);
$faqs    = [];

foreach ($rows as $row) {
    $faq_post = matrix_rd_acf_post($row['faq'] ?? null);
    if ($faq_post) {
        $faqs[] = $faq_post;
    }
}

if ($faqs === [] && $faq_img['url'] === '') {
    return;
}
?>
<section class="<?php echo esc_attr(matrix_rd_section_shell_classes('py-20 faq-section bg-grey-background max-md:bg-white')); ?>">
  <div class="<?php echo esc_attr(matrix_rd_section_inner_classes('flex flex-col items-start sm:flex-row')); ?>">
    <?php if ($faq_img['url']) : ?>
    <div class="block w-full pl-4 pr-4 mx-auto lg:mx-0 lg:p-0 lg:pr-0 lg:w-45">
      <img class="m-auto lg:m-0 object-cover max-h-max-473 rounded-xl rounded-[15px] border-3 border-black-full" src="<?php echo esc_url($faq_img['url']); ?>" alt="<?php echo esc_attr($faq_img['alt']); ?>" />
    </div>
    <?php endif; ?>
    <div class="flex flex-col justify-between w-full h-full px-4 py-4 content lg:bg-white lg:pl-8 lg:pr-10 lg:w-55">
      <div class="top">
        <h4 class="font-reg420 lg:pb-4 lg:pt-0 lg:p-0 lg:text-mob-xxl-font text-sm-md-font lg:text-leading-10"><?php echo esc_html((string) get_field('faq_title', $post_id)); ?></h4>
        <?php get_template_part('template-parts/components/faq', 'accordion', ['faqs' => $faqs, 'variant' => 'home']); ?>
      </div>
      <?php if ($faq_btn['url']) : ?>
      <div class="bottom pt-4 flex justify-center w-full">
        <a href="<?php echo esc_url($faq_btn['url']); ?>" class="faq-button items-center border-radius-large flex justify-center h-[60px] w-full md:w-[368px] text-sm-md-font font-reg420 bg-black-full text-white hover:bg-yellow-primary hover:text-black-full"><?php echo esc_html($faq_btn['title']); ?></a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
