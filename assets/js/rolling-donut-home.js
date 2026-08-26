/* global Splide */
(function () {
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

  const featuredControlsHome = { parent: null, next: null };

  function placeFeaturedControls(splide) {
    const featured = document.getElementById('featured-slider');
    if (!featured) {
      return;
    }

    const controls = featured.querySelector('.featured-slider__controls');
    if (!controls) {
      return;
    }

    if (!featuredControlsHome.parent) {
      featuredControlsHome.parent = controls.parentElement;
      featuredControlsHome.next = controls.nextElementSibling;
    }

    featured.classList.toggle('featured-donuts-slider--controls-in-panel', true);

    const slide = splide.Components.Slides.getAt(splide.index);
    const slideEl = slide && slide.slide;
    const panel = slideEl && slideEl.querySelector('.featured-slide__panel');

    if (panel && controls.parentElement !== panel) {
      panel.appendChild(controls);
    }
  }

  function initFeaturedSlider() {
    const featured = document.getElementById('featured-slider');
    if (!featured) {
      return;
    }

    let initialLoad = true;
    const slideCount = featured.querySelectorAll('.splide__slide').length;
    const isMobileFeatured = function () {
      return window.innerWidth < 993;
    };

    const featuredSplide = new Splide('#featured-slider', {
      type: 'fade',
      perPage: 1,
      arrows: slideCount > 1,
      pagination: slideCount > 1 ? '#featured-slider-pagination' : false,
      rewind: slideCount > 1,
      drag: slideCount > 1,
      autoHeight: isMobileFeatured(),
    });

    const splideExtensions = window.splide && window.splide.Extensions;
    if (splideExtensions) {
      featuredSplide.mount(splideExtensions);
    } else {
      featuredSplide.mount();
    }

    placeFeaturedControls(featuredSplide);
    featuredSplide.refresh();

    featuredSplide.on('mounted', function () {
      placeFeaturedControls(featuredSplide);
      featuredSplide.refresh();
    });

    featuredSplide.on('moved', function () {
      placeFeaturedControls(featuredSplide);
      if (!initialLoad) {
        animateFeaturedImages('down');
      }
      if (isMobileFeatured()) {
        featuredSplide.refresh();
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

    let featuredControlsResizeTimer;
    window.addEventListener('resize', function () {
      window.clearTimeout(featuredControlsResizeTimer);
      featuredControlsResizeTimer = window.setTimeout(function () {
        featuredSplide.options = { autoHeight: isMobileFeatured() };
        placeFeaturedControls(featuredSplide);
        featuredSplide.refresh();
      }, 150);
    });
  }

  function initHeroCtaLinks() {
    document.querySelectorAll('.home-hero-slide__cta').forEach(function (cta) {
      if (cta.textContent.trim() === 'Build your custom box') {
        cta.href = '/product/personalised-midi-sourdough-donuts-box-of-20/';
      }
    });
  }

  function initHeroSlideBackgrounds() {
    const panels = document.querySelectorAll('.home-hero-slide__left[data-mobile-bg]');
    if (!panels.length) {
      return;
    }

    const apply = function () {
      const mobile = window.innerWidth < 1084;
      panels.forEach(function (panel) {
        const url = mobile ? panel.dataset.mobileBg : panel.dataset.desktopBg;
        if (url) {
          panel.style.backgroundImage = 'url("' + url + '")';
        }
      });
    };

    apply();
    window.addEventListener('resize', apply);
  }

  function replayHeroSlideMotion(splide) {
    const slide = splide.Components.Slides.getAt(splide.index);
    const slideEl = slide && slide.slide;
    if (!slideEl) {
      return;
    }

    const inner = slideEl.querySelector('.home-hero-slide');
    if (!inner) {
      return;
    }

    inner.classList.remove('is-animating');
    void inner.offsetWidth;
    inner.classList.add('is-animating');
  }

  function initHeroSlider() {
    const hero = document.getElementById('home-hero-slider');
    if (!hero) {
      return;
    }

    const slideCount = hero.querySelectorAll('.splide__slide').length;
    const isMobileHero = function () {
      return window.innerWidth < 1084;
    };

    const heroSplide = new Splide('#home-hero-slider', {
      type: 'fade',
      perPage: 1,
      arrows: slideCount > 1,
      pagination: slideCount > 1 ? '#home-hero-slider-pagination' : false,
      rewind: slideCount > 1,
      speed: 500,
      easing: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
      drag: slideCount > 1,
      autoplay: slideCount > 1,
      interval: 5000,
      pauseOnHover: false,
      pauseOnFocus: false,
      resetProgress: false,
      autoHeight: isMobileHero(),
    });

    const splideExtensions = window.splide && window.splide.Extensions;
    if (splideExtensions) {
      heroSplide.mount(splideExtensions);
    } else {
      heroSplide.mount();
    }

    placeHeroControls(heroSplide);
    if (isMobileHero()) {
      heroSplide.refresh();
    }

    heroSplide.on('mounted', function () {
      replayHeroSlideMotion(heroSplide);
      placeHeroControls(heroSplide);
      if (isMobileHero()) {
        heroSplide.refresh();
      }
    });

    heroSplide.on('move', function () {
      replayHeroSlideMotion(heroSplide);
    });

    heroSplide.on('moved', function () {
      placeHeroControls(heroSplide);
      if (isMobileHero()) {
        heroSplide.refresh();
      }
    });

    let heroControlsResizeTimer;
    window.addEventListener('resize', function () {
      window.clearTimeout(heroControlsResizeTimer);
      heroControlsResizeTimer = window.setTimeout(function () {
        heroSplide.options = { autoHeight: isMobileHero() };
        placeHeroControls(heroSplide);
        heroSplide.refresh();
      }, 150);
    });
  }

  const heroControlsHome = { parent: null, next: null };

  function placeHeroControls(splide) {
    const hero = document.getElementById('home-hero-slider');
    if (!hero) {
      return;
    }

    const controls = hero.querySelector('.home-hero-slider__controls');
    if (!controls) {
      return;
    }

    if (!heroControlsHome.parent) {
      heroControlsHome.parent = controls.parentElement;
      heroControlsHome.next = controls.nextElementSibling;
    }

    const desktop = window.innerWidth >= 1084;
    const layoutDefault = !!hero.closest('.home-hero--layout-1');
    const controlsInSlide = !desktop || layoutDefault;
    hero.classList.toggle('home-hero-slider--controls-in-slide', controlsInSlide);

    if (!controlsInSlide) {
      if (heroControlsHome.parent && controls.parentElement !== heroControlsHome.parent) {
        if (heroControlsHome.next && heroControlsHome.next.parentElement === heroControlsHome.parent) {
          heroControlsHome.parent.insertBefore(controls, heroControlsHome.next);
        } else {
          heroControlsHome.parent.appendChild(controls);
        }
      }
      return;
    }

    const slide = splide.Components.Slides.getAt(splide.index);
    const slideEl = slide && slide.slide;
    if (!slideEl) {
      return;
    }

    let target = null;
    // Prefer overlay so controls can span full panel width (arrows left, dots right).
    if (layoutDefault) {
      target = slideEl.querySelector('.home-hero-slide__overlay');
    }
    if (!target) {
      target =
        slideEl.querySelector('.home-hero-slide__overlay') ||
        slideEl.querySelector('.home-hero-slide__cta-wrap') ||
        slideEl.querySelector('.home-hero-slide__content');
    }

    if (target && controls.parentElement !== target) {
      target.appendChild(controls);
    }
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
    initHeroCtaLinks();

    if (typeof Splide === 'undefined') {
      return;
    }

    initFeaturedSlider();
    initHeroSlideBackgrounds();
    initHeroSlider();
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
