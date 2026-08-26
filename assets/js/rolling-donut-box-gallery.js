/**
 * Archive product card galleries (donut-box / merch).
 *
 * Delegated so it still works after the category filter replaces the grid HTML.
 */
(function () {
  const GALLERY_SELECTOR = '[data-rd-box-gallery]';
  const IMAGE_SELECTOR = '.gallery-image';

  function showImage(container, index) {
    const images = container.querySelectorAll(IMAGE_SELECTOR);
    if (!images.length) {
      return;
    }

    const next = ((index % images.length) + images.length) % images.length;
    container.dataset.rdGalleryIndex = String(next);

    images.forEach(function (img, i) {
      img.classList.toggle('opacity-100', i === next);
      img.classList.toggle('opacity-0', i !== next);
    });
  }

  function currentIndex(container) {
    const parsed = parseInt(container.dataset.rdGalleryIndex || '0', 10);
    return Number.isFinite(parsed) ? parsed : 0;
  }

  function step(container, delta) {
    showImage(container, currentIndex(container) + delta);
  }

  document.addEventListener('click', function (event) {
    const arrow = event.target.closest('.rd-box-gallery-arrow');
    if (!arrow) {
      return;
    }

    const container = arrow.closest(GALLERY_SELECTOR);
    if (!container) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();
    step(container, arrow.classList.contains('rd-box-gallery-arrow--prev') ? -1 : 1);
  });

  const swipe = { startX: 0, container: null };

  document.addEventListener(
    'touchstart',
    function (event) {
      const container = event.target.closest(GALLERY_SELECTOR);
      if (!container || event.changedTouches.length === 0) {
        return;
      }
      swipe.container = container;
      swipe.startX = event.changedTouches[0].screenX;
    },
    { passive: true }
  );

  document.addEventListener(
    'touchend',
    function (event) {
      if (!swipe.container || event.changedTouches.length === 0) {
        return;
      }

      const diff = swipe.startX - event.changedTouches[0].screenX;
      const container = swipe.container;
      swipe.container = null;

      if (Math.abs(diff) < 30) {
        return;
      }

      step(container, diff > 0 ? 1 : -1);
    },
    { passive: true }
  );
})();
