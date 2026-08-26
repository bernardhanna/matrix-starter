<?php
/**
 * Back to top control (optional ACF theme option).
 */
?>
<div id="scrollToTopButton" class="fixed bottom-4 right-4 hidden opacity-0 transition-opacity lg:z-50">
  <button
    type="button"
    class="scroll-btn group flex h-12 w-12 items-center justify-center rounded-full border border-solid border-black-full bg-white transition-colors duration-300 hover:bg-yellow-primary focus:outline-none"
    onclick="scrollToTop()"
    aria-label="<?php esc_attr_e('Back to top', 'matrix-starter'); ?>"
  >
    <span class="sr-only"><?php esc_html_e('Back to top', 'matrix-starter'); ?></span>
    <svg width="49" height="48" viewBox="0 0 49 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <circle cx="24.5003" cy="23.9489" r="22.9489" transform="rotate(-180 24.5003 23.9489)" stroke="black" stroke-width="2"></circle>
      <path fill-rule="evenodd" clip-rule="evenodd" d="M32.5547 26.7215C32.8187 26.9855 32.8187 27.4135 32.5547 27.6776L31.6078 28.6244C31.344 28.8882 30.9164 28.8885 30.6523 28.6249L24.9373 22.9224L19.2224 28.6249C18.9583 28.8885 18.5306 28.8882 18.2668 28.6244L17.3199 27.6776C17.0559 27.4135 17.0559 26.9855 17.3199 26.7215L24.9373 19.1041L32.5547 26.7215Z" fill="black"></path>
    </svg>
  </button>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var scrollToTopButton = document.getElementById('scrollToTopButton');
    if (!scrollToTopButton) {
      return;
    }

    window.scrollToTop = function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    window.addEventListener('scroll', function () {
      if (window.scrollY > 300) {
        scrollToTopButton.classList.remove('hidden');
        scrollToTopButton.classList.add('opacity-100');
      } else {
        scrollToTopButton.classList.add('hidden');
        scrollToTopButton.classList.remove('opacity-100');
      }
    }, { passive: true });
  });
</script>
