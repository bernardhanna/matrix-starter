<?php
/**
 * Blog post grid (main loop).
 */
$heights = [
    1 => 'notebook:h-[707px]', 2 => 'notebook:h-[659px]', 3 => 'notebook:h-[707px]',
    4 => 'notebook:h-[625px]', 5 => 'notebook:h-[753px]', 6 => 'notebook:h-[625px]',
    7 => 'notebook:h-[753px]', 8 => 'notebook:h-[629px]', 9 => 'notebook:h-[753px]',
];
$image_heights = [
    1 => 'notebook:h-[519px]', 2 => 'notebook:h-[437px]', 3 => 'notebook:h-[425px]',
    4 => 'notebook:h-[437px]', 5 => 'notebook:h-[565px]', 6 => 'notebook:h-[437px]',
    7 => 'notebook:h-[565px]', 8 => 'notebook:h-[437px]', 9 => 'notebook:h-[565px]',
];
$margin_tops = [4 => 'notebook:mt-[-4rem]', 5 => 'notebook:mt-[-2rem]', 6 => 'notebook:mt-[-4rem]', 7 => 'notebook:mt-[-4rem]', 8 => 'notebook:mt-[-1.5rem]', 9 => 'notebook:mt-[-4rem]'];
$center_items = [4, 6, 8];

$categories = get_categories(['orderby' => 'name', 'order' => 'ASC', 'hide_empty' => true]);
$blog_url   = get_permalink((int) get_option('page_for_posts'));
?>
<div class="px-4 pb-16">
  <ul class="flex flex-row items-center justify-start gap-4 my-12 overflow-x-auto categories-filter flex-nowrap sm:justify-center lg:justify-center" id="post-filter">
    <li class="h-[56px] flex items-center justify-center rounded-113xl border-solid border-3 border-black-full bg-white text-sm-md-font py-4 px-8 font-reg420 hover:bg-yellow-primary shrink-0">
      <a href="<?php echo esc_url($blog_url); ?>"><?php esc_html_e('All', 'matrix-starter'); ?></a>
    </li>
    <?php foreach ($categories as $category) : ?>
    <li class="h-[56px] flex items-center justify-center rounded-113xl border-solid border-3 border-black-full bg-white text-sm-md-font py-4 px-8 font-reg420 hover:bg-yellow-primary shrink-0">
      <a href="<?php echo esc_url(get_category_link($category)); ?>"><?php echo esc_html($category->name); ?></a>
    </li>
    <?php endforeach; ?>
  </ul>

  <div class="grid grid-cols-1 gap-8 ml-auto mr-auto bg-white md:grid-cols-2 lg:grid-cols-3 max-w-max-1300">
    <?php
    if (have_posts()) :
        $counter = 1;
        while (have_posts()) :
            the_post();
            $item_height   = $heights[$counter] ?? 'notebook:h-[437px]';
            $image_height  = $image_heights[$counter] ?? 'notebook:h-auto';
            $item_center   = in_array($counter, $center_items, true) ? 'notebook:self-center' : '';
            $margin_top    = $margin_tops[$counter] ?? '';
            $thumb         = get_the_post_thumbnail_url(get_the_ID(), 'full');
            $word_count    = str_word_count(wp_strip_all_tags(get_the_content()));
            $reading_time  = max(1, (int) ceil($word_count / 250));
            ?>
    <a href="<?php the_permalink(); ?>" class="border-2 boxshadow-three border-solid border-black-full rounded-sm-10 p-4 block <?php echo esc_attr("$item_height $item_center $margin_top"); ?>">
      <?php if ($thumb) : ?>
      <img class="rounded-normal w-full <?php echo esc_attr($image_height); ?> object-cover" src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>" />
      <?php endif; ?>
      <h4 class="post-title text-black-full text-sm-md-font font-reg420 leading-[1.625rem] pt-4 pb-3 relative"><?php the_title(); ?></h4>
      <span class="flex items-center font-medium underline reading-time text-reg-font text-black-full"><?php echo (int) $reading_time; ?> <?php esc_html_e('min read', 'matrix-starter'); ?></span>
      <div class="flex flex-row flex-wrap items-center justify-between py-2">
        <span class="post-date text-reg-font light-grey font-regular"><?php echo esc_html(get_the_date('j, F Y')); ?></span>
      </div>
    </a>
            <?php
            $counter++;
        endwhile;
    else :
        ?>
    <p class="col-span-full text-center py-12"><?php esc_html_e('Sorry, no results were found.', 'matrix-starter'); ?></p>
        <?php
    endif;
    ?>
  </div>

  <nav class="flex justify-center mt-12" aria-label="<?php esc_attr_e('Blog pagination', 'matrix-starter'); ?>">
    <?php
    the_posts_pagination([
        'mid_size'  => 2,
        'prev_text' => '‹',
        'next_text' => '›',
    ]);
    ?>
  </nav>
</div>
