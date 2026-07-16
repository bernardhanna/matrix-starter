/* global matrixRdCartNotices, jQuery */
(function () {
  function usesSideCart() {
    return !!(window.matrixRdCartNotices && matrixRdCartNotices.feedbackMode === 'side_cart');
  }

  function getAjaxUrl() {
    if (window.matrixRdCartNotices && matrixRdCartNotices.ajaxUrl) {
      return matrixRdCartNotices.ajaxUrl;
    }
    if (window.matrixRdNav && matrixRdNav.ajaxUrl) {
      return matrixRdNav.ajaxUrl;
    }
    return '/wp-admin/admin-ajax.php';
  }

  function appendNoticeHtml(html) {
    if (!html || typeof html !== 'string' || html.trim() === '') {
      return;
    }

    document.querySelectorAll('#custom-woocommerce-notice').forEach(function (el) {
      el.remove();
    });

    const wrapper = document.createElement('div');
    wrapper.className = 'woocommerce-notices-wrapper';
    wrapper.innerHTML = html;
    document.body.appendChild(wrapper);
    initNoticeOverlays(wrapper);
  }

  function initNoticeOverlays(root) {
    const scope = root || document;
    scope.querySelectorAll('#custom-woocommerce-notice').forEach(function (notice) {
      if (notice.dataset.rdNoticeInit === '1') {
        return;
      }
      notice.dataset.rdNoticeInit = '1';

      const closeButton = notice.querySelector('.close-notice-button');
      const dismiss = function () {
        notice.classList.add('opacity-0', 'transition-opacity', 'duration-500', 'ease-out');
        window.setTimeout(function () {
          notice.remove();
        }, 500);
      };

      window.setTimeout(dismiss, 5000);

      if (closeButton) {
        closeButton.addEventListener('click', function (event) {
          event.preventDefault();
          dismiss();
        });
      }
    });
  }

  async function fetchAndShowNotices() {
    if (usesSideCart()) {
      if (typeof window.matrixRdOpenSideCart === 'function') {
        window.matrixRdOpenSideCart();
      }
      return;
    }

    const base = getAjaxUrl();
    const url = base + (base.indexOf('?') >= 0 ? '&' : '?') + 'action=fetch_wc_notices&nocache=' + Date.now();

    try {
      const response = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
      });
      const result = await response.json();
      if (result.success && result.data) {
        appendNoticeHtml(result.data);
      }
    } catch (e) {
      // Notices are optional.
    }
  }

  window.matrixRdShowCartNotices = fetchAndShowNotices;
  window.matrixRdAppendCartNoticeHtml = appendNoticeHtml;

  window.matrixRdRefreshCartAndNotices = async function (prefetched) {
    if (usesSideCart()) {
      if (typeof window.matrixRdOpenSideCart === 'function') {
        window.matrixRdOpenSideCart(prefetched);
      }
      return;
    }

    if (typeof window.refreshAllCartHeaders === 'function') {
      window.refreshAllCartHeaders();
    }
    await fetchAndShowNotices();
  };

  function maybeFetchNoticesAfterRedirect() {
    try {
      if (usesSideCart()) {
        return;
      }

      if (document.querySelector('#custom-woocommerce-notice')) {
        return;
      }

      const params = new URLSearchParams(window.location.search);
      if (!params.has('rd_atc')) {
        return;
      }

      fetchAndShowNotices().finally(function () {
        params.delete('rd_atc');
        const query = params.toString();
        const nextUrl = window.location.pathname + (query ? '?' + query : '') + window.location.hash;
        window.history.replaceState({}, '', nextUrl);
      });
    } catch (e) {
      // Ignore URL parsing issues.
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    initNoticeOverlays(document);
    maybeFetchNoticesAfterRedirect();
  });

  if (typeof jQuery !== 'undefined') {
    jQuery(function ($) {
      $(document.body).on('added_to_cart', function () {
        fetchAndShowNotices();
      });
    });
  }
})();
