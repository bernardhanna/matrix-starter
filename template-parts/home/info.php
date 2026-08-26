<?php
/**
 * Home events + gift cards section.
 */
$post_id = (int) get_option('page_on_front');
$bg_url  = get_template_directory_uri() . '/assets/images/home/white-bg-donuts.png';

$event_image  = matrix_rd_acf_image(get_field('event_image', $post_id), '', 'large');
$event_button = matrix_rd_acf_link(get_field('event_button', $post_id));
$gift_image   = matrix_rd_acf_image(get_field('giftcard_image', $post_id), '', 'large');
$gift_button  = matrix_rd_acf_link(get_field('giftcard_button', $post_id));
?>
<section class="<?php echo esc_attr(matrix_rd_section_shell_classes('bg-white bg-cover bg-no-repeat bg-top')); ?>" style="background-image:url('<?php echo esc_url($bg_url); ?>')">
  <div class="py-16 lg:py-28">
    <div class="<?php echo esc_attr(matrix_rd_section_inner_classes('flex flex-col md:flex-row')); ?>">
      <div class="md:w-1/2 text-left">
        <?php if ($event_image['url']) : ?>
        <img class="h-[325px] lg:max-w-max-95 lg:max-h-[32rem] lg:h-full lg:w-full object-cover border-4 border-black-full lg:border-none rounded-20px w-auto mx-auto shadow-small lg:shadow-none" src="<?php echo esc_url($event_image['url']); ?>" alt="<?php echo esc_attr($event_image['alt']); ?>" loading="lazy" decoding="async" />
        <?php endif; ?>
      </div>
      <div class="content md:w-1/2 lg:flex lg:flex-col lg:pl-8 xxl:pr-37">
        <h4 class="text-lg-font mt-6 font-reg420 pb-5 leading-3xl"><?php echo esc_html((string) get_field('event_heading', $post_id)); ?></h4>
        <div class="text-reg-font text-black-font leading-none lg:w-5/6"><?php echo wp_kses_post((string) get_field('event_text', $post_id)); ?></div>
        <?php if ($event_button['url']) : ?>
        <a class="btn mt-16 w-full max-w-[318px] text-white text-sm-md-font lg:text-md-font font-420 bg-black-full border-radius-large py-4 font-reg420 hover:bg-yellow-primary hover:text-black-full lg:w-full" href="<?php echo esc_url($event_button['url']); ?>"><?php echo esc_html($event_button['title']); ?></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="pt-8 lg:pb-24 lg:pt-0">
    <div class="<?php echo esc_attr(matrix_rd_section_inner_classes('flex flex-col md:flex-row-reverse lg:px-4')); ?>">
      <div class="md:w-1/2">
        <?php if ($gift_image['url']) : ?>
        <img class="mx-auto w-full max-lg:max-w-max-358 max-w-[30rem]" src="<?php echo esc_url($gift_image['url']); ?>" alt="<?php echo esc_attr($gift_image['alt']); ?>" loading="lazy" decoding="async" />
        <?php endif; ?>
      </div>
      <div class="content md:w-1/2 lg:flex lg:flex-col lg:justify-center lg:pl-0 xxl:pr-44">
        <h4 class="text-lg-font mt-6 font-reg420 pb-5 leading-3xl"><?php echo esc_html((string) get_field('giftcard_heading', $post_id)); ?></h4>
        <div class="text-reg-font text-black-font leading-none"><?php echo wp_kses_post((string) get_field('giftcard_text', $post_id)); ?></div>
        <?php if ($gift_button['url']) : ?>
        <a class="btn mt-16 w-full max-w-[346px] text-white text-sm-md-font lg:text-md-font font-420 bg-black-full border-radius-large py-4 font-reg420 hover:bg-yellow-primary hover:text-black-full lg:w-full" href="<?php echo esc_url($gift_button['url']); ?>"><?php echo esc_html($gift_button['title']); ?></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
