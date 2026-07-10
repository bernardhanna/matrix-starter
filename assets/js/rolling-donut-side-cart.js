/* global matrixRdSideCart, jQuery */
(function () {
  function getConfig() {
    return window.matrixRdSideCart || {};
  }

  function getRoot() {
    return document.querySelector('[data-rd-side-cart]');
  }

  function getInner() {
    const root = getRoot();
    return root ? root.querySelector('[data-rd-side-cart-inner]') : null;
  }

  function isOpen() {
    const root = getRoot();
    return !!(root && root.classList.contains('is-open'));
  }

  function setBodyLock(open) {
    document.body.classList.toggle('rd-side-cart-open', open);
  }

  function getCartComponentData(el) {
    if (window.Alpine && typeof Alpine.$data === 'function') {
      return Alpine.$data(el);
    }
    if (el.__x && el.__x.$data) {
      return el.__x.$data;
    }
    return null;
  }

  function applyCartHeaderData(data) {
    if (!data || data.cart_count === undefined) {
      return;
    }

    const count = parseInt(data.cart_count, 10) || 0;
    const total = count > 0 ? (data.cart_total || '') : '';

    document.querySelectorAll('.js-cart-header, .js-cart-header-mobile, [data-rd-cart]').forEach(function (el) {
      const componentData = getCartComponentData(el);
      if (!componentData) {
        return;
      }
      componentData.cartCount = count;
      if ('cartTotal' in componentData) {
        componentData.cartTotal = total;
      }
    });
  }

  function setLoadingState(loading) {
    const inner = getInner();
    if (inner) {
      inner.classList.toggle('is-loading', loading);
    }
  }

  function openSideCart() {
    const root = getRoot();
    if (!root) {
      return;
    }
    root.classList.add('is-open');
    root.setAttribute('aria-hidden', 'false');
    setBodyLock(true);

    const panel = root.querySelector('.rd-side-cart__panel');
    if (panel) {
      panel.focus();
    }
  }

  function closeSideCart() {
    const root = getRoot();
    if (!root) {
      return;
    }
    root.classList.remove('is-open');
    root.setAttribute('aria-hidden', 'true');
    setBodyLock(false);
  }

  async function refreshSideCartContent() {
    const inner = getInner();
    const cfg = getConfig();
    if (!inner || !cfg.ajaxUrl) {
      return null;
    }

    const url = cfg.ajaxUrl + (cfg.ajaxUrl.indexOf('?') >= 0 ? '&' : '?') + 'action=fetch_side_cart&nocache=' + Date.now();

    try {
      const response = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
      });
      const result = await response.json();
      if (!result.success || !result.data) {
        return null;
      }

      inner.innerHTML = result.data.html || '';
      applyCartHeaderData(result.data);

      return result.data;
    } catch (e) {
      return null;
    }
  }

  async function openSideCartWithRefresh() {
    openSideCart();
    setLoadingState(true);

    try {
      return await refreshSideCartContent();
    } finally {
      setLoadingState(false);
    }
  }

  function removeCartItem(cartItemKey) {
    if (!cartItemKey) {
      return Promise.resolve();
    }

    if (typeof jQuery !== 'undefined' && window.wc_cart_fragments_params && window.wc_cart_fragments_params.wc_ajax_url) {
      const url = window.wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'remove_from_cart');
      return jQuery.post(url, { cart_item_key: cartItemKey }).promise();
    }

    return refreshSideCartContent();
  }

  function clearWcCartFragmentStorage() {
    if (typeof window.wc_cart_fragments_params === 'undefined') {
      return;
    }

    try {
      var ajaxUrl = window.wc_cart_fragments_params.ajax_url || '';
      sessionStorage.removeItem('wc_fragments_' + ajaxUrl);
      sessionStorage.removeItem('wc_cart_hash_' + ajaxUrl);
    } catch (e) {
      // Ignore storage errors (private mode, etc.).
    }
  }

  function syncCartChrome(data) {
    if (!data) {
      return;
    }

    applyCartHeaderData(data);

    if (typeof window.refreshAllCartHeaders === 'function') {
      window.refreshAllCartHeaders();
    }

    window.dispatchEvent(new CustomEvent('matrix_rd_cart_updated'));

    if (typeof jQuery !== 'undefined') {
      jQuery(document.body).trigger('wc_fragment_refresh');
      jQuery(document.body).trigger('removed_from_cart');
    }
  }

  function parseAjaxJson(response) {
    return response.text().then(function (text) {
      var trimmed = (text || '').trim();
      if (trimmed === '' || trimmed === '-1' || trimmed === '0') {
        return null;
      }

      try {
        return JSON.parse(trimmed);
      } catch (e) {
        return null;
      }
    });
  }

  async function clearCart(retryOnNonceFailure) {
    const inner = getInner();
    const cfg = getConfig();
    if (!inner || !cfg.ajaxUrl || !cfg.clearNonce) {
      return null;
    }

    const body = new URLSearchParams();
    body.append('action', 'clear_side_cart');
    body.append('nonce', cfg.clearNonce);

    try {
      const response = await fetch(cfg.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: body.toString(),
      });
      const result = await parseAjaxJson(response);

      if (!result) {
        return null;
      }

      if (!result.success) {
        if (
          !retryOnNonceFailure &&
          result.data &&
          result.data.clearNonce &&
          window.matrixRdSideCart
        ) {
          window.matrixRdSideCart.clearNonce = result.data.clearNonce;
          return clearCart(true);
        }

        var errorMessage =
          (result.data && result.data.message) ||
          (cfg.i18n && cfg.i18n.clearFailed) ||
          'Could not clear your cart. Please refresh the page and try again.';
        window.alert(errorMessage);
        return null;
      }

      if (!result.data) {
        return null;
      }

      if (result.data.clearNonce && window.matrixRdSideCart) {
        window.matrixRdSideCart.clearNonce = result.data.clearNonce;
      }

      inner.innerHTML = result.data.html || '';
      clearWcCartFragmentStorage();
      syncCartChrome(result.data);

      return result.data;
    } catch (e) {
      return null;
    }
  }

  function bindEvents() {
    document.addEventListener('click', function (event) {
      const closeTarget = event.target.closest('[data-rd-side-cart-close]');
      if (closeTarget && getRoot()) {
        event.preventDefault();
        closeSideCart();
        return;
      }

      const removeBtn = event.target.closest('[data-rd-side-cart-remove]');
      if (removeBtn) {
        event.preventDefault();
        const key = removeBtn.getAttribute('data-cart-item-key');
        removeCartItem(key).then(function () {
          setLoadingState(true);
          refreshSideCartContent().finally(function () {
            setLoadingState(false);
          });
        });
        return;
      }

      const clearBtn = event.target.closest('[data-rd-side-cart-clear]');
      if (clearBtn) {
        event.preventDefault();
        const cfg = getConfig();
        const confirmMsg = (cfg.i18n && cfg.i18n.clearConfirm) || 'Remove all items from your cart?';
        if (!window.confirm(confirmMsg)) {
          return;
        }
        setLoadingState(true);
        clearCart().finally(function () {
          setLoadingState(false);
        });
        return;
      }

      const cartTrigger = event.target.closest('[data-rd-side-cart-trigger]');
      if (cartTrigger && getRoot() && document.body.classList.contains('rd-cart-feedback-side')) {
        event.preventDefault();
        if (isOpen()) {
          closeSideCart();
        } else {
          openSideCartWithRefresh();
        }
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && isOpen()) {
        closeSideCart();
      }
    });
  }

  function maybeOpenAfterRedirect() {
    try {
      const params = new URLSearchParams(window.location.search);
      if (!params.has('rd_atc') && !params.has('rd_side_cart')) {
        return;
      }

      openSideCart();

      const cleanUrl = function () {
        params.delete('rd_atc');
        params.delete('rd_side_cart');
        const query = params.toString();
        const nextUrl = window.location.pathname + (query ? '?' + query : '') + window.location.hash;
        window.history.replaceState({}, '', nextUrl);
      };

      // After a form POST redirect the footer markup is already up to date.
      if (params.has('rd_side_cart')) {
        cleanUrl();
        return;
      }

      setLoadingState(true);
      refreshSideCartContent().finally(function () {
        setLoadingState(false);
        cleanUrl();
      });
    } catch (e) {
      // Ignore.
    }
  }

  window.matrixRdOpenSideCart = openSideCartWithRefresh;
  window.matrixRdCloseSideCart = closeSideCart;
  window.matrixRdRefreshSideCart = refreshSideCartContent;

  document.addEventListener('DOMContentLoaded', function () {
    bindEvents();
    maybeOpenAfterRedirect();
  });

  if (typeof jQuery !== 'undefined') {
    jQuery(function ($) {
      $(document.body).on('added_to_cart removed_from_cart', function () {
        if (isOpen()) {
          refreshSideCartContent();
        }
      });
    });
  }
})();
