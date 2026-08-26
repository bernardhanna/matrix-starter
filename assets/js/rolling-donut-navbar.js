/* global matrixRdNav, jQuery, Alpine */
(function () {
  async function fetchCartData() {
    const base = matrixRdNav && matrixRdNav.ajaxUrl ? matrixRdNav.ajaxUrl : '/wp-admin/admin-ajax.php';
    const url = base + (base.indexOf('?') >= 0 ? '&' : '?') + 'action=get_cart_info&nocache=' + Date.now();

    try {
      const response = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
          'Cache-Control': 'no-store, no-cache, must-revalidate, max-age=0',
          Pragma: 'no-cache',
        },
      });
      const result = await response.json();
      if (result.success) {
        return result.data;
      }
      return { cart_count: 0, cart_total: '' };
    } catch (e) {
      return { cart_count: 0, cart_total: '' };
    }
  }

  window.matrixRdFetchCartData = fetchCartData;
  window.fetchCartData = fetchCartData;

  function getCartComponentData(el) {
    if (window.Alpine && typeof Alpine.$data === 'function') {
      return Alpine.$data(el);
    }
    if (el.__x && el.__x.$data) {
      return el.__x.$data;
    }
    return null;
  }

  function refreshAllCartWidgets() {
    document.querySelectorAll('.js-cart-header, .js-cart-header-mobile, [data-rd-cart]').forEach(function (el) {
      const data = getCartComponentData(el);
      if (data && typeof data.refreshCart === 'function') {
        data.refreshCart();
      }
    });
  }

  function getVisibleDesktopOrderCta() {
    // There are multiple .btn-menu nodes (nav-left/right + nav-combined). Only the
    // visible one has a real box — picking a hidden zero-width CTA produced
    // ~viewport-width padding and clipped the cart/utilities out of view.
    const candidates = document.querySelectorAll('#site-nav ul.nav-left .btn-menu, #site-nav ul.nav-right .btn-menu, #site-nav ul.nav-combined .btn-menu');

    for (let i = 0; i < candidates.length; i++) {
      const el = candidates[i];
      if (el.offsetWidth > 8 && el.offsetHeight > 8) {
        return el;
      }
    }

    return null;
  }

  function alignDesktopTopNav() {
    const topNav = document.querySelector('#site-nav .top-nav');
    const headerBar = document.querySelector('.rd-header-bar');
    const cart = document.querySelector('#site-nav .js-cart-header .cart-contents')
      || document.querySelector('#site-nav .js-cart-header');
    const cta = getVisibleDesktopOrderCta();

    if (!topNav) {
      return;
    }

    if (headerBar && headerBar.classList.contains('rd-header-bar--hidden')) {
      return;
    }

    if (window.innerWidth < 1211 || !cart || !cta) {
      topNav.style.setProperty('--rd-topnav-align-offset', '0px');
      return;
    }

    // Measure from zero padding — otherwise we only capture the residual gap
    // after the CSS fallback and make the overflow worse on wide viewports.
    topNav.style.setProperty('--rd-topnav-align-offset', '0px');
    void topNav.offsetWidth;

    const cartRect = cart.getBoundingClientRect();
    const ctaRect = cta.getBoundingClientRect();

    if (cartRect.width < 8 || ctaRect.width < 8) {
      return;
    }

    // Cap the nudge — a runaway value with overflow:hidden hides the cart row.
    const offset = Math.max(0, Math.min(48, Math.round(cartRect.right - ctaRect.right)));

    topNav.style.setProperty('--rd-topnav-align-offset', offset + 'px');
  }

  function scheduleTopNavAlignment() {
    alignDesktopTopNav();

    if (window.requestAnimationFrame) {
      window.requestAnimationFrame(function () {
        window.requestAnimationFrame(alignDesktopTopNav);
      });
    }

    window.setTimeout(alignDesktopTopNav, 120);
    window.setTimeout(alignDesktopTopNav, 400);
  }

  function initTopNavAlignmentWatchers() {
    const siteNav = document.getElementById('site-nav');
    const headerBar = document.querySelector('.rd-header-bar');

    if (siteNav && typeof ResizeObserver !== 'undefined') {
      const resizeObserver = new ResizeObserver(function () {
        scheduleTopNavAlignment();
      });
      resizeObserver.observe(siteNav);

      const cartHeader = siteNav.querySelector('.js-cart-header');
      if (cartHeader) {
        resizeObserver.observe(cartHeader);
      }
    }

    // Do not realign on every scroll — that shifts the utility top bar while sticky.
    // Pin/unpin and visibility changes already fire matrix_rd_header_layout_change.
    window.addEventListener('matrix_rd_header_layout_change', scheduleTopNavAlignment);

    if (headerBar && typeof MutationObserver !== 'undefined') {
      const classObserver = new MutationObserver(function () {
        scheduleTopNavAlignment();
      });
      classObserver.observe(headerBar, {
        attributes: true,
        attributeFilter: ['class', 'style'],
      });
    }
  }

  window.refreshAllCartHeaders = refreshAllCartWidgets;
  window.matrixRdAlignDesktopTopNav = alignDesktopTopNav;
  window.matrixRdScheduleDesktopTopNav = scheduleTopNavAlignment;

  window.matrixRdRefreshCartAndNotices = async function (prefetched) {
    if (window.matrixRdCartNotices && matrixRdCartNotices.feedbackMode === 'side_cart') {
      if (typeof window.matrixRdOpenSideCart === 'function') {
        window.matrixRdOpenSideCart(prefetched);
      }
      return;
    }

    refreshAllCartWidgets();
    if (typeof window.matrixRdShowCartNotices === 'function') {
      await window.matrixRdShowCartNotices();
    }
  };

  let didInit = false;

  function initNavbar() {
    if (didInit) {
      return;
    }
    didInit = true;

    const topbar = document.getElementById('topbar');
    const closeBtn = document.getElementById('topbar-close');
    if (topbar && closeBtn) {
      if (localStorage.getItem('topbarClosed') === 'true') {
        topbar.setAttribute('data-rd-topbar-hidden', '');
      }
      closeBtn.addEventListener('click', function () {
        topbar.setAttribute('data-rd-topbar-hidden', '');
        localStorage.setItem('topbarClosed', 'true');
      });
    }

    document.querySelectorAll('.hamburger').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (topbar) {
          topbar.classList.toggle('hidden');
        }
      });
    });

    window.addEventListener('pageshow', function () {
      refreshAllCartWidgets();
      scheduleTopNavAlignment();
    });

    window.addEventListener('load', function () {
      refreshAllCartWidgets();
      scheduleTopNavAlignment();
    });

    window.addEventListener('resize', scheduleTopNavAlignment, { passive: true });

    scheduleTopNavAlignment();
    initTopNavAlignmentWatchers();

    if (typeof jQuery !== 'undefined') {
      jQuery(document.body).on('added_to_cart removed_from_cart wc_fragment_refresh', function (event) {
        if (
          event.type === 'added_to_cart' &&
          window.matrixRdCartNotices &&
          matrixRdCartNotices.feedbackMode === 'side_cart'
        ) {
          return;
        }
        refreshAllCartWidgets();
        scheduleTopNavAlignment();
      });
      jQuery(document.body).trigger('wc_fragment_refresh');
    }

    window.addEventListener('matrix_rd_cart_updated', function () {
      window.matrixRdRefreshCartAndNotices();
      scheduleTopNavAlignment();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNavbar);
    document.addEventListener('DOMContentLoaded', scheduleTopNavAlignment);
  } else {
    initNavbar();
    scheduleTopNavAlignment();
  }

  window.addEventListener('load', scheduleTopNavAlignment);
})();
