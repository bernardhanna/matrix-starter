<?php
/**
 * PACE header — Figma 3:269 (desktop), 7:3577 (mobile)
 */

$theme_logo_id = get_theme_mod('custom_logo');
$acf_logo_id   = get_field('logo', 'option');
$logo_id       = $theme_logo_id ?: $acf_logo_id;

$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
$logo_alt = $logo_id ? (get_post_meta($logo_id, '_wp_attachment_image_alt', true) ?: get_bloginfo('name')) : get_bloginfo('name');

$nav_settings   = get_field('navigation_settings_start', 'option') ?: [];
$contact_button = $nav_settings['contact_button'] ?? null;
$has_header_cta = is_array($contact_button) && !empty($contact_button['url']) && !empty($contact_button['title']);

use Log1x\Navi\Navi;

$primary_navigation = Navi::make()->build('primary');

$nav_link_class = matrix_pace_btn_classes('ghost');
$cta_class      = matrix_pace_btn_classes('primary') . ' max-w-[200px]';
?>

<section
  id="site-nav"
  x-data="{
    isOpen: false,
    activeDropdown: null,
    dropdownCloseTimer: null,
    openDropdown(index) {
      if (this.dropdownCloseTimer) {
        clearTimeout(this.dropdownCloseTimer);
        this.dropdownCloseTimer = null;
      }
      this.activeDropdown = index;
    },
    scheduleCloseDropdown() {
      if (this.dropdownCloseTimer) {
        clearTimeout(this.dropdownCloseTimer);
      }
      this.dropdownCloseTimer = setTimeout(() => {
        this.activeDropdown = null;
        this.dropdownCloseTimer = null;
      }, 180);
    },
    toggleDropdown(index) {
      if (this.activeDropdown === index) {
        if (this.dropdownCloseTimer) {
          clearTimeout(this.dropdownCloseTimer);
          this.dropdownCloseTimer = null;
        }
        this.activeDropdown = null;
      } else {
        this.openDropdown(index);
      }
    },
    checkWindowSize() {
      if (window.innerWidth >= 1084) {
        this.isOpen = false;
        this.activeDropdown = null;
        if (this.dropdownCloseTimer) {
          clearTimeout(this.dropdownCloseTimer);
          this.dropdownCloseTimer = null;
        }
      }
    },
    setContentOffset() {
      this.$nextTick(() => {
        document.querySelectorAll('[data-nav-offset=true]').forEach((el) => {
          el.style.paddingTop = '';
          el.removeAttribute('data-nav-offset');
        });
        const navEl = this.$refs.siteNavInner;
        const target =
          document.querySelector('main > section:first-of-type') ||
          document.querySelector('main section:first-of-type');
        if (!navEl || !target || target.closest('[data-disable-nav-offset=true], .no-nav-offset')) {
          document.documentElement.style.removeProperty('--site-nav-offset');
          return;
        }
        const offsetPx = Math.ceil(navEl.getBoundingClientRect().height);
        if (offsetPx > 0) {
          target.style.paddingTop = `${offsetPx}px`;
          target.setAttribute('data-nav-offset', 'true');
          document.documentElement.style.setProperty('--site-nav-offset', `${offsetPx}px`);
        }
      });
    }
  }"
  x-init="
    checkWindowSize();
    setContentOffset();
    window.addEventListener('resize', () => { checkWindowSize(); setContentOffset(); });
    window.addEventListener('load', () => { setContentOffset(); setTimeout(() => setContentOffset(), 120); });
  "
  x-effect="isOpen ? document.body.style.overflow = 'hidden' : document.body.style.overflow = ''"
  class="sticky top-0 left-0 z-[1000] w-full border-b border-solid border-[#ecf0f4] bg-white pl-5 pr-2 font-montserrat lg:px-20"
>
  <nav
    x-ref="siteNavInner"
    class="mx-auto w-full max-w-[1280px]"
    role="navigation"
    aria-label="<?php echo esc_attr__('Main navigation', 'matrix-starter'); ?>"
  >

    <!-- Mobile: logo | CTA | hamburger (Figma 7:3577) -->
    <div class="grid grid-cols-[auto_1fr_auto] items-center gap-4  py-[14px] lg:hidden">
      <a
        href="<?php echo esc_url(home_url('/')); ?>"
        class="inline-flex z-50 shrink-0 items-center justify-start focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0a60a0] focus-visible:ring-offset-2"
        aria-label="<?php echo esc_attr(get_bloginfo('name')); ?> - <?php esc_attr_e('Go to homepage', 'matrix-starter'); ?>"
      >
        <?php if ($logo_url) : ?>
          <img
            src="<?php echo esc_url($logo_url); ?>"
            alt="<?php echo esc_attr($logo_alt); ?>"
            class="h-10 w-[76px] object-contain object-left"
            width="76"
            height="40"
          />
        <?php else : ?>
          <span class="text-lg font-bold text-[#003b65]"><?php echo esc_html(get_bloginfo('name')); ?></span>
        <?php endif; ?>
      </a>

      <div class="flex justify-end mr-2 min-w-0">
        <?php if ($has_header_cta) : ?>
          <a
            href="<?php echo esc_url($contact_button['url']); ?>"
            target="<?php echo esc_attr($contact_button['target'] ?? '_self'); ?>"
            <?php if (($contact_button['target'] ?? '') === '_blank') : ?>rel="noopener noreferrer"<?php endif; ?>
            class="<?php echo esc_attr($cta_class); ?>"
            aria-label="<?php echo esc_attr($contact_button['title']); ?>"
          >
            <?php echo esc_html($contact_button['title']); ?>
          </a>
        <?php endif; ?>
      </div>

      <div class="flex justify-end items-center w-10 shrink-0">
        <?php get_template_part('template-parts/header/navbar/mobile'); ?>
      </div>
    </div>

    <!-- Desktop: logo | nav | CTA (Figma 3:269) -->
    <div class="hidden lg:grid lg:grid-cols-[76px_minmax(0,1fr)_auto] lg:items-center lg:gap-8 lg:px-10 lg:py-[14px]">
      <a
        href="<?php echo esc_url(home_url('/')); ?>"
        class="col-start-1 inline-flex shrink-0 items-center justify-start focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0a60a0] focus-visible:ring-offset-2"
        aria-label="<?php echo esc_attr(get_bloginfo('name')); ?> - <?php esc_attr_e('Go to homepage', 'matrix-starter'); ?>"
      >
        <?php if ($logo_url) : ?>
          <img
            src="<?php echo esc_url($logo_url); ?>"
            alt="<?php echo esc_attr($logo_alt); ?>"
            class="h-10 w-[76px] object-contain object-left z-50"
            width="76"
            height="40"
          />
        <?php else : ?>
          <span class="text-lg font-bold text-[#003b65]"><?php echo esc_html(get_bloginfo('name')); ?></span>
        <?php endif; ?>
      </a>

      <?php if ($primary_navigation->isNotEmpty()) : ?>
        <ul
          id="primary-menu"
          class="col-start-2 flex flex-wrap items-center justify-center gap-[22px]"
          role="menubar"
        >
          <?php foreach ($primary_navigation->toArray() as $index => $item) : ?>
            <li
              class="relative <?php echo esc_attr($item->classes); ?> <?php echo $item->active ? 'current-item' : ''; ?>"
              role="none"
              <?php if ($item->children) : ?>
                @mouseenter="openDropdown(<?php echo (int) $index; ?>)"
                @mouseleave="scheduleCloseDropdown()"
              <?php endif; ?>
            >
              <div class="relative px-0.5 py-2">
                <?php if ($item->children) : ?>
                  <div class="flex gap-1.5 items-center">
                    <a
                      href="<?php echo esc_url($item->url); ?>"
                      class="<?php echo esc_attr($nav_link_class); ?>"
                      role="menuitem"
                      aria-haspopup="true"
                      aria-expanded="false"
                      x-bind:aria-expanded="activeDropdown === <?php echo (int) $index; ?> ? 'true' : 'false'"
                    >
                      <?php echo esc_html($item->label); ?>
                    </a>
                    <span
                      class="pointer-events-none shrink-0 text-[#003b65] opacity-70 transition-transform duration-200"
                      :class="activeDropdown === <?php echo (int) $index; ?> ? 'rotate-180 opacity-100' : ''"
                      aria-hidden="true"
                    >
                      <svg class="size-3.5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                  </div>
                <?php else : ?>
                  <a
                    href="<?php echo esc_url($item->url); ?>"
                    class="<?php echo esc_attr($nav_link_class); ?>"
                    role="menuitem"
                  >
                    <?php echo esc_html($item->label); ?>
                  </a>
                <?php endif; ?>

                <?php if ($item->active) : ?>
                  <span
                    class="pointer-events-none absolute bottom-0 left-0.5 right-3.5 h-0.5 rounded-[2px] bg-[#f4bd0b]"
                    aria-hidden="true"
                  ></span>
                <?php endif; ?>
              </div>

              <?php if ($item->children) : ?>
                <?php get_template_part('template-parts/header/navbar/dropdown', null, ['item' => $item, 'index' => $index]); ?>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <div class="flex col-start-3 justify-end shrink-0">
        <?php if ($has_header_cta) : ?>
          <a
            href="<?php echo esc_url($contact_button['url']); ?>"
            target="<?php echo esc_attr($contact_button['target'] ?? '_self'); ?>"
            <?php if (($contact_button['target'] ?? '') === '_blank') : ?>rel="noopener noreferrer"<?php endif; ?>
            class="<?php echo esc_attr($cta_class); ?>"
            aria-label="<?php echo esc_attr($contact_button['title']); ?>"
          >
            <?php echo esc_html($contact_button['title']); ?>
          </a>
        <?php endif; ?>
      </div>
    </div>

  </nav>
</section>
