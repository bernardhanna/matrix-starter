<?php
/**
 * Desktop menu list (left / right / combined variants).
 */
$nav_items          = $args['nav_items'] ?? [];
$nav_ul_class       = $args['nav_ul_class'] ?? '';
$nav_mark_last_cta  = ! empty($args['nav_mark_last_cta']);
$nav_max_items      = array_key_exists('nav_max_items', $args) ? $args['nav_max_items'] : 4;
$nav_tabindex_start = (int) ($args['nav_tabindex_start'] ?? 0);

if (empty($nav_items)) {
    return;
}

$visible    = $nav_max_items === null ? $nav_items : array_slice($nav_items, 0, (int) $nav_max_items);
$last_index = count($visible) - 1;
?>
<ul class="<?php echo esc_attr($nav_ul_class); ?>" role="menubar">
  <?php foreach ($visible as $index => $nav_item) : ?>
    <?php
    get_template_part('template-parts/header/navbar/menu-item', null, [
        'nav_item'          => $nav_item,
        'nav_is_last_cta'   => $nav_mark_last_cta && ($index === $last_index),
        'nav_tabindex'      => $nav_tabindex_start ? $nav_tabindex_start + $index : 0,
        'nav_item_li_class' => $args['nav_item_li_class'] ?? 'group relative overflow-visible',
    ]);
    ?>
  <?php endforeach; ?>
</ul>
