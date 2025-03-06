<?php
$logo_id = get_field('logo', 'option') ?: get_theme_mod('custom_logo');
$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
$logo_alt = $logo_id ? get_post_meta($logo_id, '_wp_attachment_image_alt', true) : get_bloginfo('name');
$logo_position = get_field('logo_position', 'option');
$logo_position_class = ($logo_position === 'center') ? 'justify-center' : 'justify-start';

use Log1x\Navi\Navi;

$primary_navigation = Navi::make()->build('primary');
$secondary_navigation = Navi::make()->build('secondary');
?>
<section
  id="site-nav"
  x-data="{
    isOpen: false,
    activeDropdown: null,
    toggleDropdown(index) {
      this.activeDropdown = (this.activeDropdown === index ? null : index);
    },
    checkWindowSize() {
      if (window.innerWidth > 1084) {
        this.isOpen = false;
        this.activeDropdown = null;
      }
    }
  }"
  x-init="window.addEventListener('resize', () => checkWindowSize())"
  class="py-4 bg-white"
  x-effect="isOpen ? document.body.style.overflow = 'hidden' : document.body.style.overflow = ''">
  <nav class="flex justify-between items-center w-full mx-auto max-w-[1168px] px-5 lg:px-0">
    <a href="<?php echo esc_url(home_url('/')); ?>"
      class="flex <?php echo $logo_position_class; ?>">
      <?php if ($logo_url) : ?>
        <img src=" <?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($logo_alt); ?>" />
      <?php else : ?>
        <span><?php echo get_bloginfo('name'); ?></span>
      <?php endif; ?>
    </a>
    <?php if ($primary_navigation->isNotEmpty()) : ?>
      <ul id="primary-menu" class="items-center hidden leading-loose text-black gap-9 max-md:gap-6 lg:flex">
        <?php foreach ($primary_navigation->toArray() as $index => $item) : ?>
          <li class="relative group <?php echo esc_attr($item->classes); ?> <?php echo $item->active ? 'current-item' : ''; ?>">
            <a href="<?php echo esc_url($item->url); ?>" class="gap-2.5 self-stretch my-auto whitespace-nowrap text-[#1d2838] hover:text-[#025a70] text-base font-normal leading-normal flex items-center capitalize <?php echo $item->active ? 'active-item' : ''; ?>">
              <?php echo esc_html($item->label); ?>
              <?php if ($item->children) : ?>
                <span class="ml-[2px]">
                  <svg xmlns="http://www.w3.org/2000/svg" width="17" height="18" viewBox="0 0 17 18" fill="none">
                    <path d="M4.25 6.875L8.5 11.125L12.75 6.875" stroke="#1D2939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </span>
              <?php endif; ?>
            </a>

            <?php if ($item->children) : ?>
              <ul class="absolute left-0 hidden space-y-2 border-b-2 border-[#F68D2E] bg-white group-hover:block min-w-[200px] z-50">
                <?php foreach ($item->children as $child) : ?>
                  <li class="group <?php echo esc_attr($child->classes); ?> <?php echo $child->active ? 'current-item' : ''; ?> hover:bg-secondary">
                    <a href="<?php echo esc_url($child->url); ?>"
                      class="block px-4 py-2 text-sm font-normal leading-normal 
          text-[#1d2838] hover:text-white">
                      <?php echo esc_html($child->label); ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php get_template_part('template-parts/header/navbar/mobile'); ?>

    <?php if ($secondary_navigation->isNotEmpty()) : ?>
      <ul class="flex gap-4 px-4 text-black xl:gap-6">
        <?php foreach ($secondary_navigation->toArray() as $item) : ?>
          <li class="relative group <?php echo esc_attr($item->classes); ?>">
            <a href="<?php echo esc_url($item->url); ?>" class="text-[#1d2838] hover:text-[#025a70] text-base font-normal flex items-center">
              <?php echo esc_html($item->label); ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </nav>
</section>