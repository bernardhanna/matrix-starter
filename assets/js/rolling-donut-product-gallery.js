/**
 * Single product gallery (Splide main + thumbnails) — legacy app.js behaviour.
 */
document.addEventListener('DOMContentLoaded', function () {
  var mainEl = document.getElementById('main-slider');
  if (!mainEl || typeof Splide === 'undefined') {
    return;
  }

  var mainSlides = mainEl.querySelectorAll('.splide__slide');
  var mainSplide = new Splide('#main-slider', {
    type: 'loop',
    perPage: 1,
    pagination: false,
    arrows: mainSlides.length > 1,
  });
  mainSplide.mount();

  if (mainSlides.length <= 1) {
    document.querySelectorAll('#main-slider .splide__arrows').forEach(function (el) {
      el.classList.add('hidden');
    });
  }

  var thumbEl = document.getElementById('thumbnail-slider');
  if (!thumbEl) {
    return;
  }

  var thumbSlides = thumbEl.querySelectorAll('.splide__slide');
  if (thumbSlides.length > 1) {
    var thumbSplide = new Splide('#thumbnail-slider', {
      isNavigation: true,
      pagination: false,
      perPage: 3,
      arrows: false,
    });
    thumbSplide.mount();
    mainSplide.sync(thumbSplide);
  }

  document.querySelectorAll('#thumbnail-slider .splide__slide a').forEach(function (anchor) {
    anchor.addEventListener('click', function (event) {
      event.preventDefault();
      var slide = anchor.closest('.splide__slide');
      var list = anchor.closest('.splide__list');
      if (!slide || !list) {
        return;
      }
      var index = Array.from(list.children).indexOf(slide);
      if (index >= 0) {
        mainSplide.go(index);
      }
    });
  });
});
