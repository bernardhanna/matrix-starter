(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const section = document.querySelector('.our-story');
    if (!section) {
      return;
    }

    const DESKTOP_MIN = 1250;
    const prev = section.querySelector('.arrow.prev');
    const next = section.querySelector('.arrow.next');
    const desktopCards = section.querySelectorAll('.our-story-cards-desktop .cards li.story-item');
    const textContents = section.querySelectorAll('.text-content');
    const mobileRoot = document.getElementById('our-story-mobile');

    let currentIndex = 0;
    let mobileSplide = null;

    function isDesktop() {
      return window.innerWidth >= DESKTOP_MIN;
    }

    function updateDesktop() {
      if (!isDesktop() || !desktopCards.length) {
        return;
      }

      desktopCards.forEach(function (card, index) {
        card.classList.remove('current');
        if (textContents[index]) {
          textContents[index].style.display = 'none';
          textContents[index].classList.remove('active');
        }
      });

      desktopCards[currentIndex].classList.add('current');
      if (textContents[currentIndex]) {
        textContents[currentIndex].style.display = 'flex';
        textContents[currentIndex].classList.add('active');
      }

      if (prev) {
        prev.classList.toggle('disabled', currentIndex === 0);
      }
      if (next) {
        next.classList.toggle('disabled', currentIndex === desktopCards.length - 1);
      }
    }

    function initMobileSplide() {
      if (!mobileRoot || typeof window.Splide === 'undefined' || mobileSplide) {
        return;
      }

      mobileSplide = new Splide(mobileRoot, {
        type: 'slide',
        fixedWidth: '309px',
        gap: '20px',
        padding: { left: '0.5rem', right: '2.5rem' },
        arrows: false,
        pagination: false,
        drag: true,
        snap: true,
        flickPower: 500,
        speed: 600,
        trimSpace: false,
      });

      mobileSplide.mount();
    }

    function destroyMobileSplide() {
      if (!mobileSplide) {
        return;
      }

      mobileSplide.destroy(true);
      mobileSplide = null;
    }

    function syncMode() {
      if (isDesktop()) {
        destroyMobileSplide();
        updateDesktop();
      } else {
        initMobileSplide();
      }
    }

    if (prev) {
      prev.addEventListener('click', function (e) {
        e.preventDefault();
        if (!isDesktop()) {
          return;
        }
        currentIndex = Math.max(0, currentIndex - 1);
        updateDesktop();
      });
    }

    if (next) {
      next.addEventListener('click', function (e) {
        e.preventDefault();
        if (!isDesktop()) {
          return;
        }
        currentIndex = Math.min(desktopCards.length - 1, currentIndex + 1);
        updateDesktop();
      });
    }

    window.addEventListener('resize', syncMode);
    syncMode();
  });
})();
