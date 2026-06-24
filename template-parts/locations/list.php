<?php
/**
 * Location cards list (CPT: location) — legacy locations/location.blade.php.
 */
$query = new WP_Query([
    'post_type'      => 'location',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order title',
    'order'          => 'ASC',
]);
?>
<style>.leaflet-tile-container { filter: saturate(0.8) !important; }</style>
<section
  class="relative max-mobile:-top-20 p-0 pt-4 mx-auto tablet-sm:max-w-max-1341 mobile:px-4"
  x-data="{ windowWidth: window.innerWidth }"
  x-init="window.addEventListener('resize', () => windowWidth = window.innerWidth)"
>
  <?php if ($query->have_posts()) : ?>
    <?php while ($query->have_posts()) : ?>
      <?php
      $query->the_post();
      $post_id    = get_the_ID();
      $address    = trim((string) get_field('address', $post_id));
      $phone      = trim((string) get_field('phone_number', $post_id));
      $directions = matrix_rd_acf_url_or_link(get_field('get_directions_link', $post_id));
      $collection = matrix_rd_acf_url_or_link(
          get_field('order_for_collection_link', $post_id),
          __('Order for Collection', 'matrix-starter')
      );
      $hours      = matrix_rd_acf_repeater_rows('opening_hours', ['day', 'times'], $post_id);
      $thumb      = get_the_post_thumbnail_url($post_id, 'full');
      ?>
  <div class="relative flex flex-col pt-10 mb-4 bg-white location-item mobile:pt-0 mobile:border-black mobile:border-4 mobile:border-solid mobile:rounded-md-32 mobile:flex-row tablet-sm:pt-6 tablet-sm:pb-3">
    <div class="flex flex-col mobile:p-4 lg:p-0 tablet-sm:mr-auto tablet-sm:ml-auto lg:w-30">
      <h4 class="hidden w-full pt-3 pb-8 leading-normal max-mobile:block text-font-28 tablet-sm:text-md-font font-reg420 text-black-full"><?php the_title(); ?></h4>
      <?php if ($thumb) : ?>
      <img class="max-mobile:border-black-full max-mobile:border-4 tablet-sm:pr-2 rounded-one object-cover w-full h-[270px] md:h-auto tablet-sm:h-[381px]" src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>" />
      <?php endif; ?>
    </div>
    <div class="relative flex flex-col w-full pr-4 mobile:w-3/5 lg:w-2/3 lg:-top-4 lg:right-2">
      <div class="flex flex-col pt-10 mobile:pt-0">
        <h4 class="w-full pt-3 leading-normal max-mobile:order-1 max-mobile:hidden text-font-28 tablet-sm:text-md-font font-reg420 text-black-full"><?php the_title(); ?></h4>
        <div class="flex flex-col w-full pt-0 max-mobile:order-2 tablet-sm:pt-5 max-mobile:flex-wrap tablet-sm:justify-between notebook:flex-row tablet-sm:flex-row">
          <?php if ($address !== '') : ?>
          <span class="flex items-start font-medium tablet-sm:items-center font-laca text-reg-font tablet-sm:text-base-font laptop:text-sm-md-font text-black-primary">
            <?php get_template_part('template-parts/locations/icons', null, ['icon' => 'pin']); ?>
            <?php echo esc_html($address); ?>
          </span>
          <?php endif; ?>
          <?php if ($phone !== '') : ?>
          <a href="<?php echo esc_attr('tel:' . $phone); ?>" class="flex items-center font-medium font-laca text-reg-font tablet-sm:text-base-font laptop:text-sm-md-font text-black-primary">
            <?php get_template_part('template-parts/locations/icons', null, ['icon' => 'phone']); ?>
            <?php echo esc_html($phone); ?>
          </a>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($hours !== []) : ?>
      <div x-data="{ openPanel: null }" class="bg-white max-mobile:order-4 border-2 border-solid border-black rounded-sm-12 boxshadow mt-[10px] pt-2 pb-2 pl-0 mobile:pr-4">
        <div class="flex flex-col" id="accordion-collapse-<?php echo (int) $post_id; ?>" x-data="{ isOpen: window.innerWidth >= 1024 }" x-init="window.addEventListener('resize', () => { isOpen = window.innerWidth >= 993 })">
          <span class="text-reg-font tablet-sm:text-md-font" id="accordion-collapse-heading-<?php echo (int) $post_id; ?>">
            <button @click="isOpen = !isOpen" type="button" class="flex items-center justify-between w-full pl-5 pr-5 font-medium text-left text-gray-500 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-800 dark:border-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 tablet-sm:py-4">
              <span class="relative text-black-primary text-sm-md-font font-reg420"><?php esc_html_e('Opening Hours', 'matrix-starter'); ?></span>
              <?php get_template_part('template-parts/locations/icons', null, ['icon' => 'accordion-up']); ?>
              <?php get_template_part('template-parts/locations/icons', null, ['icon' => 'accordion-down']); ?>
            </button>
          </span>
          <div x-show="isOpen" id="accordion-collapse-body-<?php echo (int) $post_id; ?>" aria-labelledby="accordion-collapse-heading-<?php echo (int) $post_id; ?>">
            <div class="pt-1 pl-5 pr-5 dark:bg-gray-900">
              <?php foreach ($hours as $i => $row) :
                  $bg    = ($i % 2 === 0) ? 'bg-grey-background' : 'bg-white';
                  $times = (string) ($row['times'] ?? '');
                  $closed = strtolower($times) === 'closed';
                  ?>
              <div class="flex items-center justify-between p-2 <?php echo esc_attr($bg); ?> rounded-sm-8">
                <span class="font-medium text-base-font text-black-full"><?php echo esc_html((string) ($row['day'] ?? '')); ?></span>
                <span class="<?php echo $closed ? 'font-bold text-red-critical text-base-font' : 'text-grey-font font-regular'; ?>"><?php echo esc_html($times); ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="flex flex-col justify-between mt-4 max-mobile:order-3 tablet-sm:flex-row max-w-max-691">
        <?php if ($directions['url'] !== '') : ?>
        <div x-data="{ isHovered: false }">
          <a
            target="_blank"
            rel="noopener noreferrer"
            class="max-tablet-sm:mb-4 w-full tablet-sm:w-[340px] h-[48px] tablet-sm:h-[56px] justify-center rounded-btn-72 bg-black-full hover:bg-yellow-primary flex items-center border-black border-solid border-2 text-base-font text-white hover:text-black-full font-medium btn-icon-fill"
            href="<?php echo esc_url($directions['url']); ?>"
            @mouseenter="isHovered = true"
            @mouseleave="isHovered = false"
          >
            <span><?php esc_html_e('Get Directions', 'matrix-starter'); ?></span>
            <?php get_template_part('template-parts/locations/icons', null, ['icon' => 'directions']); ?>
          </a>
        </div>
        <?php endif; ?>
        <?php if ($collection['url'] !== '') : ?>
        <a
          class="max-tablet-sm:mb-4 w-full tablet-sm:w-[340px] h-[48px] tablet-sm:h-[56px] flex justify-center rounded-btn-72 bg-yellow-primary hover:bg-white border-black border-solid border-3 items-center"
          href="<?php echo esc_url($collection['url']); ?>"
          target="<?php echo esc_attr($collection['target'] ?: '_self'); ?>"
        >
          <span class="font-medium text-black-full text-base-font"><?php esc_html_e('Order for Collection', 'matrix-starter'); ?></span>
          <?php get_template_part('template-parts/locations/icons', null, ['icon' => 'collection']); ?>
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
    <?php endwhile; ?>
    <?php wp_reset_postdata(); ?>
  <?php endif; ?>
</section>
