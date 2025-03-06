<?php
// Import Navi if not already imported
use Log1x\Navi\Navi;

// Get navigation objects if not already set
if (!isset($primary_navigation)) {
  $primary_navigation = Navi::make()->build('primary');
}
if (!isset($secondary_navigation)) {
  $secondary_navigation = Navi::make()->build('secondary');
}

$enable_hamburger = get_field('enable_hamburger', 'option');
$hamburger_style = get_field('hamburger_style', 'option');
$mobile_menu_effect = get_field('mobile_menu_effect', 'option') ?: 'slide_up';
$mobile_menu_width = get_field('mobile_menu_width', 'option') ?: 100;
$mobile_menu_bg = get_field('mobile_menu_background', 'option') ?: '#FFFFFF';
$sticky_menu = get_field('sticky_menu', 'option'); // Sticky menu toggle

// Map effects to transition classes
$effect_classes = [
  'slide_up'    => 'translate-y-full',
  'slide_left'  => '-translate-x-full',
  'slide_right' => 'translate-x-full',
  'fullscreen'  => 'translate-y-full',
];
$transition_class = $effect_classes[$mobile_menu_effect] ?? 'translate-y-full';

// Define additional styles for non-fullscreen menus
$menu_width_style = $mobile_menu_effect !== 'fullscreen'
  ? "width: {$mobile_menu_width}%; left: 0;"
  : "width: 100%;";

// Validate the hamburger style to prevent invalid classes
$valid_styles = [
  'hamburger--3dx',
  'hamburger--3dx-r',
  'hamburger--3dy',
  'hamburger--3dy-r',
  'hamburger--3dxy',
  'hamburger--3dxy-r',
  'hamburger--arrow',
  'hamburger--arrow-r',
  'hamburger--arrowalt',
  'hamburger--arrowalt-r',
  'hamburger--arrowturn',
  'hamburger--arrowturn-r',
  'hamburger--boring',
  'hamburger--collapse',
  'hamburger--collapse-r',
  'hamburger--elastic',
  'hamburger--elastic-r',
  'hamburger--emphatic',
  'hamburger--emphatic-r',
  'hamburger--minus',
  'hamburger--slider',
  'hamburger--slider-r',
  'hamburger--spin',
  'hamburger--spin-r',
  'hamburger--spring',
  'hamburger--spring-r',
  'hamburger--stand',
  'hamburger--stand-r',
  'hamburger--squeeze',
  'hamburger--vortex',
  'hamburger--vortex-r',
];

if (!in_array($hamburger_style, $valid_styles)) {
  $hamburger_style = 'hamburger--spin'; // Fallback to default style
}

?>

<?php if ($enable_hamburger): ?>
  <button
    :class="{ 'is-active z-50 bg-transparent hover:bg-transparent flex items-center justify-center ': isOpen }"
    class="hamburger <?php echo esc_attr($hamburger_style); ?> lg:hidden"
    type="button"
    aria-label="Menu"
    aria-expanded="false"
    @click="isOpen = !isOpen">
    <span class="hamburger-box">
      <span class="hamburger-inner"></span>
    </span>
  </button>
<?php endif; ?>

<?php if ($enable_hamburger && $primary_navigation->isNotEmpty()): ?>
  <div
    x-show="isOpen"
    :class="{ '<?php echo esc_attr($transition_class); ?>': !isOpen, 'translate-x-0 translate-y-0': isOpen }"
    class="absolute top-0 left-0 z-40 h-screen <?php echo esc_attr($transition_class); ?> bg-white transition-transform duration-500 ease-out"
    style="background-color: <?php echo esc_attr($mobile_menu_bg); ?>; <?php echo esc_attr($menu_width_style); ?>"
    x-transition:enter="transition ease-out duration-500"
    x-transition:leave="transition ease-in duration-300"
    @click.away="isOpen = false">
    <nav class="flex flex-col items-center justify-center h-full px-8">
      <ul class="relative flex flex-col justify-center w-full h-full mx-auto space-y-8 text-center stretch">
        <?php foreach ($primary_navigation->toArray() as $index => $item) : ?>
          <li class="relative mb-4 border-b border-[#CCDEE2] pb-6 <?php echo esc_attr($item->classes); ?> <?php echo $item->active ? 'current-item' : ''; ?>">
            <div class="flex items-center justify-between">
              <!-- Top-Level Link -->
              <a
                href="<?php echo esc_url($item->url); ?>"
                class="text-lg font-normal leading-7 text-secondary-800 ">
                <?php echo esc_html($item->label); ?>
              </a>

              <!-- If the item has children, show a toggle button -->
              <?php if ($item->children) : ?>
                <button
                  type=" button"
                  class="ml-4"
                  @click.stop="toggleDropdown(<?php echo $index; ?>)"
                  aria-label="Toggle sub-menu">
                  <svg xmlns="http://www.w3.org/2000/svg" width="17" height="18" viewBox="0 0 17 18" fill="none">
                    <path d="M4.25 6.875L8.5 11.125L12.75 6.875" stroke="#1D2939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>
              <?php endif; ?>
            </div>

            <!-- Child Submenu -->
            <?php if ($item->children) : ?>
              <ul
                x-show="activeDropdown === <?php echo $index; ?>"
                x-transition
                style="display: none;"
                class="flex flex-col items-start self-stretch gap-8 p-6 px-8  text-lg transition-all duration-300 rounded-lg text-gray-70 bg-[#E6EEF1]">
                <?php foreach ($item->children as $child) : ?>
                  <li class="text-left">
                    <a href="<?php echo esc_url($child->url); ?>" class="block py-2">
                      <?php echo esc_html($child->label); ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>
  </div>
<?php endif; ?>