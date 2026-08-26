<?php
/**
 * Shared breadcrumb navigation (interior pages + WooCommerce).
 *
 * @var array<int, array{0: string, 1: string}> $crumbs
 */

if (! defined('ABSPATH')) {
    exit;
}

$crumbs = $args['crumbs'] ?? [];
if (! is_array($crumbs) || $crumbs === []) {
    return;
}
?>
<nav class="rd-breadcrumbs breadcrumb-nav pt-4 relative z-50" aria-label="<?php esc_attr_e('Breadcrumb', 'matrix-starter'); ?>">
<?php foreach ($crumbs as $key => $crumb) :
    $label     = isset($crumb[0]) ? (string) $crumb[0] : '';
    $url       = isset($crumb[1]) ? (string) $crumb[1] : '';
    $is_last   = $key === array_key_last($crumbs);
    $item_class = mb_strlen($label) > 8 ? 'breadcrumb-ellipsis' : '';
    ?>
  <span class="breadcrumb-item font-laca text-sm-font <?php echo $is_last ? 'text-yellow-primary font-bolder' : 'text-white'; ?> <?php echo esc_attr($item_class); ?>">
    <?php if ($url !== '' && ! $is_last) : ?>
      <a class="text-white font-laca text-sm-font hover:underline" href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
    <?php else : ?>
      <?php echo esc_html($label); ?>
    <?php endif; ?>
  </span>
    <?php if (! $is_last) : ?>
  <span class="mx-2 text-white font-laca text-sm-font" aria-hidden="true">&gt;</span>
    <?php endif; ?>
<?php endforeach; ?>
</nav>
