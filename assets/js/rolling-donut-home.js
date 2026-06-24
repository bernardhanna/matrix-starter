/* global Splide */
(function () {
  function updateSlideCounts(currentSlide) {
    document.querySelectorAll('.slide-count').forEach(function (el) {
      const span = el.querySelector('.start-count');
      if (span) {
        span.textContent = String(currentSlide);
      }
    });
  }

  function animateFeaturedImages(direction) {
    if (window.innerWidth < 1200) {
      return;
    }

    document.querySelectorAll('.featured-image').forEach(function (img) {
      img.classList.remove('animate-up', 'animate-down');
      if (direction === 'up') {
        img.classList.add('animate-up');
      } else if (direction === 'down') {
        img.classList.add('animate-down');
      }
      window.setTimeout(function () {
        img.classList.remove('animate-up', 'animate-down');
      }, 300);
    });
  }

  function positionFeaturedArrows(root) {
    const arrows = root.querySelector('.splide__arrows');
    if (!arrows) {
      return;
    }

    if (window.innerWidth >= 993) {
      arrows.style.cssText =
        'display:flex!important;position:absolute!important;right:6rem!important;left:auto!important;top:auto!important;bottom:2rem!important;width:auto!important;z-index:200!important;flex-direction:column-reverse!important;align-items:flex-end!important;justify-content:flex-end!important;pointer-events:none!important;transform:none!important;margin:0!important;padding:0!important;gap:.75rem!important;';
    } else {
      arrows.style.cssText = 'display:none!important;';
    }
  }

  function initFeaturedSlider() {
    const featured = document.getElementById('featured-slider');
    if (!featured) {
      return;
    }

    let initialLoad = true;

    const featuredSplide = new Splide('#featured-slider', {
      type: 'fade',
      perPage: 1,
      arrows: true,
      pagination: true,
    });

    const splideExtensions = window.splide && window.splide.Extensions;
    if (splideExtensions) {
      featuredSplide.mount(splideExtensions);
    } else {
      featuredSplide.mount();
    }

    positionFeaturedArrows(featured);
    featuredSplide.on('mounted', function () {
      positionFeaturedArrows(featured);
    });
    featuredSplide.on('updated', function () {
      positionFeaturedArrows(featured);
    });

    let arrowResizeTimer;
    window.addEventListener('resize', function () {
      window.clearTimeout(arrowResizeTimer);
      arrowResizeTimer = window.setTimeout(function () {
        positionFeaturedArrows(featured);
      }, 200);
    });

    featuredSplide.on('moved', function () {
      updateSlideCounts(featuredSplide.index + 1);
      if (!initialLoad) {
        animateFeaturedImages('down');
      }
    });

    window.setTimeout(function () {
      initialLoad = false;
    }, 5000);

    featured.querySelectorAll('.splide__arrows .splide__arrow').forEach(function (button) {
      button.addEventListener('click', function () {
        animateFeaturedImages('up');
      });
    });

    const thumb = document.getElementById('donut-thumb-slider');
    if (thumb) {
      const thumbSplide = new Splide('#donut-thumb-slider', {
        cover: false,
        isNavigation: true,
        focus: 'center',
        pagination: false,
        arrows: false,
        drag: false,
      }).mount();
      featuredSplide.sync(thumbSplide);
    }

    updateSlideCounts(1);
  }

  function initBestsellerSlider() {
    const bestseller = document.querySelector('.bestseller-splide');
    if (!bestseller) {
      return;
    }

    const bestsellerSplide = new Splide('.bestseller-splide', {
      type: 'slide',
      perPage: 3,
      pagination: false,
      gap: '1.5rem',
      arrows: true,
      breakpoints: {
        1084: { perPage: 2 },
        768: { perPage: 1 },
      },
    });

    const splideExtensions = window.splide && window.splide.Extensions;
    if (splideExtensions) {
      bestsellerSplide.mount(splideExtensions);
    } else {
      bestsellerSplide.mount();
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    initServicesSlick();

    if (typeof Splide === 'undefined') {
      return;
    }

    initFeaturedSlider();
    initBestsellerSlider();
  });

  function initServicesSlick() {
    if (typeof jQuery === 'undefined' || !jQuery.fn.slick) {
      return;
    }

    const $slider = jQuery('.services-slick');
    if (!$slider.length) {
      return;
    }

    const customPrevArrow =
      '<button class="splide__arrow splide__arrow--prev" type="button" aria-label="Go to previous slide">' +
      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" width="40" height="40" focusable="false">' +
      '<path d="m15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z"></path></svg></button>';
    const customNextArrow =
      '<button class="splide__arrow splide__arrow--next" type="button" aria-label="Go to next slide">' +
      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" width="40" height="40" focusable="false">' +
      '<path d="m15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z"></path></svg></button>';

    function debounce(fn, wait) {
      let timeout;
      return function () {
        const ctx = this;
        const args = arguments;
        window.clearTimeout(timeout);
        timeout = window.setTimeout(function () {
          fn.apply(ctx, args);
        }, wait);
      };
    }

    function toggleSlider() {
      if (window.innerWidth <= 1084) {
        if (!$slider.hasClass('slick-initialized')) {
          $slider.slick({
            dots: true,
            arrows: true,
            infinite: true,
            slidesToShow: 1,
            slidesToScroll: 1,
            adaptiveHeight: true,
            prevArrow: customPrevArrow,
            nextArrow: customNextArrow,
          });
        }
      } else if ($slider.hasClass('slick-initialized')) {
        $slider.slick('unslick');
      }
    }

    toggleSlider();
    jQuery(window).on('resize', debounce(toggleSlider, 200));
  }
})();
