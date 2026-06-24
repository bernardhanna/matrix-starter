<?php
/**
 * FAQ accordion list (Alpine.js toggle — does not require Flowbite).
 *
 * @var array<int, WP_Post> $faqs FAQ posts to render.
 * @var string              $variant Optional. 'home' uses border-b-2 styling.
 */
$faqs = isset($args['faqs']) && is_array($args['faqs']) ? $args['faqs'] : [];
if ($faqs === []) {
    return;
}

$variant     = isset($args['variant']) && $args['variant'] === 'home' ? 'home' : 'default';
$item_border = $variant === 'home' ? 'border-b-2' : 'border-b';
$body_extra  = $variant === 'home'
    ? 'w-full pb-5 font-light leading-tight lg:pt-5 max-md:text-sm-font text-sm-font font-laca max-lg:w-11/12'
    : 'lg:pt-5 pb-5 leading-tight text-sm-font font-laca w-full max-lg:w-11/12';
?>
<div id="accordion-open">
  <?php foreach ($faqs as $index => $faq_post) : ?>
    <?php if (! $faq_post instanceof WP_Post) {
        continue;
    } ?>
  <div class="<?php echo esc_attr($item_border); ?> border-black-full lg:bg-white" x-data="{ open: false }">
    <div id="accordion-open-heading-<?php echo (int) $index; ?>">
      <button
        type="button"
        class="flex items-center justify-between w-full py-3 font-medium text-left"
        @click="open = !open"
        :aria-expanded="open"
        aria-controls="accordion-open-body-<?php echo (int) $index; ?>"
      >
        <span class="flex items-center font-medium text-black-full text-sm-font lg:text-reg-font"><?php echo esc_html($faq_post->post_title); ?></span>
        <div class="flex items-center justify-center w-6 h-6 border border-black-full rounded-full bg-yellow-primary" :class="{ 'rotate-180': open }">
          <span class="h-full text-black iconify text-md-font" data-icon="pajamas:chevron-down"></span>
        </div>
      </button>
    </div>
    <div
      id="accordion-open-body-<?php echo (int) $index; ?>"
      class="transition-all duration-500"
      x-show="open"
      x-cloak
      aria-labelledby="accordion-open-heading-<?php echo (int) $index; ?>"
    >
      <div class="<?php echo esc_attr($body_extra); ?>">
        <?php echo wp_kses_post(apply_filters('the_content', $faq_post->post_content)); ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
