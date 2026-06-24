<?php
$selectedFaqs = get_sub_field('selected_faqs');
    $faqButton = get_sub_field('faq_button');
?>
<?php if ($selectedFaqs) : ?>
<section class="faq-section w-full md:mt-8 md:mb-20 px-4">
    <div class="flex flex-col lg:flex-row lg:max-w-max-1300 mx-auto">
        <div class="content md:boxshadow-two md:rounded-form md:border-3 md:border-black-full md:border-solid flex flex-col justify-between md:bg-white w-full px-4 md:px-12 pt-4 md:pt-12 pb-8 lg:w-full">
            <h4 class="pt-10 pb-4 lg:pt-0 lg:p-0 lg:text-lg-font text-lg-font font-reg420 lg:text-leading-10">FAQs</h4>
            <?php
            $faq_posts = [];
            foreach ((array) $selectedFaqs as $faq) {
                $faq_post = matrix_rd_acf_post($faq);
                if ($faq_post) {
                    $faq_posts[] = $faq_post;
                }
            }
            get_template_part('template-parts/components/faq', 'accordion', ['faqs' => $faq_posts]);
            ?>
            <?php
            $faq_btn = matrix_rd_faq_view_all_link($faqButton);
            if ($faq_btn['url']) :
                ?>
<div class="bottom pt-6 flex justify-center w-full">
                    <a href="<?php echo esc_url($faq_btn['url']); ?>" class="faq-button items-center border-radius-large flex justify-center h-[60px] w-full md:w-[368px] text-sm-md-font font-reg420 bg-black-full text-white hover:bg-yellow-primary hover:text-black-full"><?php echo esc_html($faq_btn['title']); ?></a>
                </div>
            <?php endif; ?>
</div>
    </div>
</section>
<?php endif; ?>
