/* global Splide */
/**
 * Wedding/kudos testimonial carousel (#testimonial-slider).
 * Mirrors the legacy Splide config: looping, ~2.5 cards per view on desktop,
 * draggable, custom chevron arrows, autoplay when the extension is present.
 */
(function () {
  function initTestimonials() {
    var el = document.getElementById('testimonial-slider');
    if (!el || typeof Splide === 'undefined' || el.classList.contains('is-initialized')) {
      return;
    }

    var slides = el.querySelectorAll('.splide__slide');
    var multiple = slides.length > 1;

    var slider = new Splide('#testimonial-slider', {
      type: multiple ? 'loop' : 'slide',
      perPage: 2.5,
      perMove: 1,
      gap: '2rem',
      arrows: multiple,
      pagination: false,
      drag: multiple,
      focus: 0,
      trimSpace: false,
      arrowPath:
        'm15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z',
      breakpoints: {
        1280: { perPage: 2 },
        1023: { perPage: 1.4, gap: '1.5rem' },
        768: { perPage: 1.15, gap: '1rem' },
        480: { perPage: 1, gap: '1rem' },
      },
    });

    var extensions = window.splide && window.splide.Extensions;
    if (multiple && extensions) {
      slider.mount(extensions);
    } else {
      slider.mount();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTestimonials);
  } else {
    initTestimonials();
  }
})();
