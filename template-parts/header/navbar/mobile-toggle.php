<?php
/**
 * Mobile hamburger toggle.
 */
?>
<button
  type="button"
  class="hamburger relative z-[100] ml-2 mr-4 flex h-10 w-10 shrink-0 items-center justify-center overflow-visible lg:hidden"
  @click="open = !open"
  :aria-expanded="open.toString()"
  aria-controls="rd-mobile-menu"
  :aria-label="open ? '<?php echo esc_attr(__('Close menu', 'matrix-starter')); ?>' : '<?php echo esc_attr(__('Open menu', 'matrix-starter')); ?>'"
>
  <span class="relative z-[100] flex h-10 w-10 items-center justify-center overflow-visible">
    <template x-if="!open">
      <span class="hamburger_close block" aria-hidden="true"></span>
    </template>
    <template x-if="open">
      <span class="hamburger_open block" aria-hidden="true"></span>
    </template>
  </span>
</button>
