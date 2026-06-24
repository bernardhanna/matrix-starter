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

  window.refreshAllCartHeaders = refreshAllCartWidgets;

  window.matrixRdRefreshCartAndNotices = async function () {
    if (window.matrixRdCartNotices && matrixRdCartNotices.feedbackMode === 'side_cart') {
      if (typeof window.matrixRdOpenSideCart === 'function') {
        window.matrixRdOpenSideCart();
      }
      return;
    }

    refreshAllCartWidgets();
    if (typeof window.matrixRdShowCartNotices === 'function') {
      await window.matrixRdShowCartNotices();
    }
  };

  document.addEventListener('DOMContentLoaded', function () {
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
    });

    window.addEventListener('load', function () {
      refreshAllCartWidgets();
    });

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
      });
      jQuery(document.body).trigger('wc_fragment_refresh');
    }

    window.addEventListener('matrix_rd_cart_updated', function () {
      window.matrixRdRefreshCartAndNotices();
    });
  });
})();
