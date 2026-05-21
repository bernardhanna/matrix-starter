<?php
/**
 * Template Part: Breadcrumbs
 * Renders site breadcrumbs on all pages except front/home (enforced by caller).
 * Tailwind + Font Awesome chevrons, accessible markup.
 */

if ( defined('ABSPATH') === false ) {
    exit;
}

// Build items
$items = [];

// Home
$items[] = [
    'label'   => __('Home', 'matrix-starter'),
    'url'     => home_url('/'),
    'current' => false,
];

if ( is_singular() ) {
    global $post;
    if ( $post instanceof WP_Post ) {
        $post_type = get_post_type($post);

        // If custom post type, add archive (when available)
        if ( $post_type && $post_type !== 'page' && $post_type !== 'post' ) {
            $pto = get_post_type_object($post_type);
            if ( $pto && !empty($pto->has_archive) ) {
                $items[] = [
                    'label'   => $pto->labels->name,
                    'url'     => get_post_type_archive_link($post_type),
                    'current' => false,
                ];
            }
        }

        // Posts: add the first category
        if ( $post_type === 'post' ) {
            $cats = get_the_category($post->ID);
            if ( !empty($cats) && !is_wp_error($cats) ) {
                $cat = $cats[0];
                $items[] = [
                    'label'   => $cat->name,
                    'url'     => get_category_link($cat->term_id),
                    'current' => false,
                ];
            }
        }

        // Pages: include ancestors
        if ( $post_type === 'page' ) {
            $anc = get_post_ancestors($post->ID);
            $anc = array_reverse($anc);
            foreach ( $anc as $a_id ) {
                $items[] = [
                    'label'   => get_the_title($a_id),
                    'url'     => get_permalink($a_id),
                    'current' => false,
                ];
            }
        }

        // Current entry
        $items[] = [
            'label'   => get_the_title($post),
            'url'     => '',
            'current' => true,
        ];
    }

} elseif ( is_archive() ) {
    $items[] = [
        'label'   => get_the_archive_title(),
        'url'     => '',
        'current' => true,
    ];

} elseif ( is_search() ) {
    $items[] = [
        'label'   => sprintf( esc_html__('Search results for “%s”', 'matrix-starter'), get_search_query() ),
        'url'     => '',
        'current' => true,
    ];

} elseif ( is_404() ) {
    $items[] = [
        'label'   => esc_html__('404 Not Found', 'matrix-starter'),
        'url'     => '',
        'current' => true,
    ];
}
?>

<nav aria-label="<?php echo esc_attr__('Breadcrumb', 'matrix-starter'); ?>"
     class="flex flex-col justify-center items-start py-3 mt-[5rem] pl-11 bg-gray-50 max-md:px-5 max-w-container mx-auto">
  <ol class="flex overflow-hidden gap-2 justify-center items-center font-red-hat-text text-[12px] leading-[18px] text-gray-800" role="list">
    <?php
    $last = count($items) - 1;
    foreach ($items as $i => $it) :
        $is_last = ($i === $last);
        $current = !empty($it['current']);
        // Non-current (incl. Home) => medium; Current => normal
        $weight_class = $current ? 'font-normal' : 'font-medium';
        ?>
        <li class="self-stretch my-auto <?php echo esc_attr($weight_class); ?>"<?php echo $current ? ' aria-current="page"' : ''; ?>>
          <?php if (!$current && !empty($it['url'])): ?>
            <a href="<?php echo esc_url($it['url']); ?>"
               class="focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-600 hover:opacity-90 transition-colors duration-200">
              <?php echo esc_html($it['label']); ?>
            </a>
          <?php else: ?>
            <span class="inline-block"><?php echo esc_html($it['label']); ?></span>
          <?php endif; ?>
        </li>
        <?php if (!$is_last): ?>
          <li class="self-stretch my-auto" aria-hidden="true">
            <!-- Font Awesome chevron-right -->
            <i class="fas fa-chevron-right w-3 inline-block align-middle"></i>
          </li>
        <?php endif; ?>
    <?php endforeach; ?>
  </ol>
</nav>
