<?php
/**
 * PACE desktop mega menu — Figma-aligned (navy / amber / Montserrat).
 *
 * @package Matrix_Starter
 */

$item  = $args['item'] ?? null;
$index = (int) ($args['index'] ?? 0);

if (!$item) {
    return;
}

$item_children = !empty($item->children) && is_iterable($item->children)
    ? array_values($item->children)
    : [];

$default_tier3_index = null;
foreach ($item_children as $child_index => $child) {
    if (!empty($child->children) && is_iterable($child->children) && count($child->children) > 0) {
        $default_tier3_index = (int) $child_index;
        break;
    }
}

$default_tier3_json = $default_tier3_index === null ? 'null' : (string) $default_tier3_index;

$chevron_svg = '<svg class="size-4 shrink-0" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

$pace_link_class = 'group/link flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#f4bd0b] focus-visible:ring-offset-2';
$pace_leaf_class = 'block rounded-xl px-4 py-3 transition-colors duration-200 hover:bg-[#fffae8] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0a60a0] focus-visible:ring-offset-2';
?>

<div
    x-show="activeDropdown === <?php echo (int) $index; ?>"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-1"
    class="pace-mega-menu fixed left-0 top-[var(--site-nav-offset,72px)] z-40 w-full"
    @mouseenter="openDropdown(<?php echo (int) $index; ?>)"
    @mouseleave="scheduleCloseDropdown()"
    @click.away="activeDropdown = null"
    @keydown.escape.window="activeDropdown = null"
    role="region"
    aria-label="<?php echo esc_attr($item->label); ?> submenu"
>
    <div
        class="border-t border-[#ecf0f4] bg-[#003b65]/10 backdrop-blur-[2px]"
        aria-hidden="true"
    ></div>

    <div class="relative w-full bg-white pb-6 pt-3 shadow-[0_20px_50px_rgba(0,59,101,0.14)]">
        <div class="mx-auto w-full max-w-[1280px] px-5 lg:px-10">
            <div
                class="overflow-hidden rounded-2xl border border-[#ecf0f4] bg-white"
                x-data="{ activeTier3Index: <?php echo esc_attr($default_tier3_json); ?> }"
                @mouseleave="activeTier3Index = <?php echo esc_attr($default_tier3_json); ?>"
                role="navigation"
                aria-label="<?php echo esc_attr($item->label); ?> menu"
            >
                <div class="grid min-h-[300px] max-h-[min(70vh,520px)] lg:grid-cols-[minmax(260px,300px)_minmax(0,1fr)]">
                    <!-- L2 rail -->
                    <div class="flex flex-col border-b border-[#ecf0f4] bg-[#f8fafc] lg:border-b-0 lg:border-r">
                        <div class="border-b border-[#ecf0f4] bg-[#003b65] px-5 py-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#f4bd0b]">
                                <?php esc_html_e('Explore', 'matrix-starter'); ?>
                            </p>
                            <p class="mt-1 text-[18px] font-bold leading-[1.2] text-white">
                                <?php echo esc_html($item->label); ?>
                            </p>
                            <a
                                href="<?php echo esc_url($item->url); ?>"
                                class="mt-3 inline-flex items-center gap-1.5 text-[13px] font-semibold text-[#f4bd0b] transition hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#003b65]"
                            >
                                <?php esc_html_e('View section', 'matrix-starter'); ?>
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>

                        <?php if ($item_children) : ?>
                            <ul class="flex-1 space-y-1 overflow-y-auto p-3" role="list">
                                <?php foreach ($item_children as $child_index => $child) : ?>
                                    <?php
                                    $child_children = !empty($child->children) && is_iterable($child->children)
                                        ? array_values($child->children)
                                        : [];
                                    $has_tier3      = $child_children !== [];
                                    $child_id       = function_exists('mytheme_menu_item_id')
                                        ? mytheme_menu_item_id($child)
                                        : 0;
                                    $child_desc     = $child_id && function_exists('mytheme_menu_item_desc')
                                        ? mytheme_menu_item_desc($child, $child_id)
                                        : '';
                                    ?>
                                    <li>
                                        <?php if ($has_tier3) : ?>
                                            <button
                                                type="button"
                                                class="<?php echo esc_attr($pace_link_class); ?> w-full border-0 bg-transparent"
                                                :class="activeTier3Index === <?php echo (int) $child_index; ?> ? 'bg-[#fffae8] ring-1 ring-inset ring-[#f4bd0b]/40' : 'hover:bg-white'"
                                                aria-label="<?php echo esc_attr($child->label); ?>"
                                                :aria-expanded="activeTier3Index === <?php echo (int) $child_index; ?> ? 'true' : 'false'"
                                                aria-controls="pace-tier3-<?php echo (int) $index; ?>-<?php echo (int) $child_index; ?>"
                                                @mouseenter="activeTier3Index = <?php echo (int) $child_index; ?>"
                                                @focus="activeTier3Index = <?php echo (int) $child_index; ?>"
                                            >
                                                <span class="flex min-w-0 flex-1 flex-col text-left">
                                                    <span
                                                        class="text-[14px] font-semibold leading-[21px] transition-colors"
                                                        :class="activeTier3Index === <?php echo (int) $child_index; ?> ? 'text-[#003b65]' : 'text-[#003b65]'"
                                                    >
                                                        <?php echo esc_html($child->label); ?>
                                                    </span>
                                                    <?php if ($child_desc !== '') : ?>
                                                        <span class="mt-0.5 line-clamp-2 font-comfortaa text-[12px] leading-[18px] text-[#5c6b78]">
                                                            <?php echo esc_html($child_desc); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </span>
                                                <span
                                                    class="text-[#0a60a0] transition-transform duration-200"
                                                    :class="activeTier3Index === <?php echo (int) $child_index; ?> ? 'translate-x-0.5 text-[#003b65]' : ''"
                                                ><?php echo $chevron_svg; ?></span>
                                            </button>
                                        <?php else : ?>
                                            <a
                                                href="<?php echo esc_url($child->url); ?>"
                                                class="<?php echo esc_attr($pace_link_class); ?> hover:bg-white"
                                                <?php if (!empty($child->target)) : ?>target="<?php echo esc_attr($child->target); ?>"<?php endif; ?>
                                                @mouseenter="activeTier3Index = null"
                                            >
                                                <span class="flex min-w-0 flex-1 flex-col">
                                                    <span class="text-[14px] font-semibold leading-[21px] text-[#003b65]">
                                                        <?php echo esc_html($child->label); ?>
                                                    </span>
                                                    <?php if ($child_desc !== '') : ?>
                                                        <span class="mt-0.5 line-clamp-2 font-comfortaa text-[12px] leading-[18px] text-[#5c6b78]">
                                                            <?php echo esc_html($child_desc); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </span>
                                            </a>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <!-- L3 panel -->
                    <div class="relative min-h-[240px] overflow-hidden bg-white lg:min-h-0">
                        <!-- Default / no L3 selection -->
                        <div
                            x-show="activeTier3Index === null"
                            x-cloak
                            class="flex h-full min-h-[240px] flex-col justify-between bg-gradient-to-br from-[#003b65] via-[#003b65] to-[#0a60a0] p-8 lg:min-h-[300px]"
                        >
                            <div class="pointer-events-none absolute -right-8 bottom-0 h-32 w-48 rounded-tl-[80px] bg-[#f4bd0b]/25" aria-hidden="true"></div>
                            <div class="relative z-[1]">
                                <p class="text-[13px] font-semibold uppercase tracking-[0.14em] text-[#f4bd0b]">
                                    <?php echo esc_html($item->label); ?>
                                </p>
                                <p class="mt-3 max-w-[400px] font-comfortaa text-[16px] leading-[26px] text-white/85">
                                    <?php esc_html_e('Choose a topic on the left to see related pages and resources.', 'matrix-starter'); ?>
                                </p>
                            </div>
                            <a
                                href="<?php echo esc_url($item->url); ?>"
                                class="<?php echo esc_attr(matrix_pace_btn_classes('primary')); ?> relative z-[1] gap-2"
                            >
                                <?php
                                printf(
                                    /* translators: %s: menu section label */
                                    esc_html__('Browse all %s', 'matrix-starter'),
                                    esc_html($item->label)
                                );
                                ?>
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>

                        <?php foreach ($item_children as $child_index => $child) : ?>
                            <?php
                            $tier3_children = !empty($child->children) && is_iterable($child->children)
                                ? array_values($child->children)
                                : [];
                            if ($tier3_children === []) {
                                continue;
                            }
                            ?>
                            <div
                                id="pace-tier3-<?php echo (int) $index; ?>-<?php echo (int) $child_index; ?>"
                                x-show="activeTier3Index === <?php echo (int) $child_index; ?>"
                                x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                class="absolute inset-0 flex flex-col overflow-y-auto"
                                @mouseenter="activeTier3Index = <?php echo (int) $child_index; ?>"
                            >
                                <header class="sticky top-0 z-[2] border-b border-[#ecf0f4] bg-white/95 px-6 py-4 backdrop-blur-sm">
                                    <div class="flex flex-wrap items-end justify-between gap-3">
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#0a60a0]">
                                                <?php echo esc_html($child->label); ?>
                                            </p>
                                            <p class="mt-1 font-comfortaa text-[13px] text-[#5c6b78]">
                                                <?php
                                                printf(
                                                    /* translators: %d: number of links */
                                                    esc_html(_n('%d link', '%d links', count($tier3_children), 'matrix-starter')),
                                                    count($tier3_children)
                                                );
                                                ?>
                                            </p>
                                        </div>
                                        <a
                                            href="<?php echo esc_url($child->url); ?>"
                                            class="text-[13px] font-semibold text-[#0a60a0] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0a60a0]"
                                            <?php if (!empty($child->target)) : ?>target="<?php echo esc_attr($child->target); ?>"<?php endif; ?>
                                        >
                                            <?php esc_html_e('View all →', 'matrix-starter'); ?>
                                        </a>
                                    </div>
                                </header>

                                <ul
                                    class="grid flex-1 gap-2 p-4 sm:grid-cols-2 lg:p-6"
                                    role="list"
                                >
                                    <?php foreach ($tier3_children as $grandchild) : ?>
                                        <?php
                                        $gc_id   = function_exists('mytheme_menu_item_id')
                                            ? mytheme_menu_item_id($grandchild)
                                            : 0;
                                        $gc_desc = $gc_id && function_exists('mytheme_menu_item_desc')
                                            ? mytheme_menu_item_desc($grandchild, $gc_id)
                                            : '';
                                        $gc_icon = $gc_id && function_exists('mytheme_menu_item_icon')
                                            ? mytheme_menu_item_icon($gc_id)
                                            : null;
                                        ?>
                                        <li>
                                            <a
                                                href="<?php echo esc_url($grandchild->url); ?>"
                                                class="<?php echo esc_attr($pace_leaf_class); ?> flex h-full gap-3 bg-[#fafbfc] hover:bg-[#fffae8]"
                                                <?php if (!empty($grandchild->target)) : ?>target="<?php echo esc_attr($grandchild->target); ?>"<?php endif; ?>
                                            >
                                                <?php if (is_array($gc_icon) && !empty($gc_icon['url'])) : ?>
                                                    <span class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white ring-1 ring-[#ecf0f4]">
                                                        <img
                                                            src="<?php echo esc_url($gc_icon['url']); ?>"
                                                            alt=""
                                                            class="size-7 object-contain"
                                                            width="28"
                                                            height="28"
                                                            loading="lazy"
                                                            decoding="async"
                                                        />
                                                    </span>
                                                <?php else : ?>
                                                    <span
                                                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[#003b65] text-[11px] font-bold uppercase tracking-wide text-[#f4bd0b]"
                                                        aria-hidden="true"
                                                    >
                                                        <?php echo esc_html(mb_substr(trim($grandchild->label), 0, 2)); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <span class="min-w-0 flex-1">
                                                    <span class="block text-[14px] font-semibold leading-[21px] text-[#003b65] group-hover/link:text-[#0a60a0]">
                                                        <?php echo esc_html($grandchild->label); ?>
                                                    </span>
                                                    <?php if ($gc_desc !== '') : ?>
                                                        <span class="mt-1 block font-comfortaa text-[12px] leading-[18px] text-[#5c6b78] line-clamp-2">
                                                            <?php echo esc_html($gc_desc); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
