/**
 * Variation validation for variable products (merch: t-shirts, mugs, etc.).
 *
 * WooCommerce only guards its own "Add to Basket" button when a variation has
 * not been chosen — the theme's "Buy Now" express button (and the floating
 * action-bar proxies) bypass that guard and submit the parent product, which
 * fails server-side and triggers a jarring reload. This script:
 *
 *   1. Mirrors WooCommerce's "selection needed" state onto the Buy Now button so
 *      it is visually disabled until a valid variation is selected.
 *   2. Intercepts Add to Basket / Buy Now clicks (inline and floating) while a
 *      variation is missing, shows a clear inline validation error listing the
 *      options still to choose, and scrolls the variation picker into view —
 *      instead of a dead tap, a browser alert, or a full-page reload.
 */
(function () {
    'use strict';

    var i18n = window.matrixRdVariation || {};
    var PREFIX = i18n.selectPrefix || 'Please select';
    var SUFFIX = i18n.selectSuffix || 'before adding to your basket.';
    var GENERIC = i18n.genericMessage || 'Please choose your product options before adding to your basket.';

    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    ready(function () {
        var form = document.querySelector('form.cart.variations_form');
        if (!form) {
            return;
        }

        var addBtn = form.querySelector('.single_add_to_cart_button');
        var buyBtn = form.querySelector('.rd-buy-now-button');
        var variations = form.querySelector('.variations');

        function selectionNeeded() {
            // WooCommerce's own button is the source of truth where present.
            if (addBtn && (addBtn.classList.contains('wc-variation-selection-needed') || addBtn.classList.contains('disabled'))) {
                return true;
            }
            // Fallback: a positive variation id means a concrete variation is set.
            var vid = form.querySelector('input[name="variation_id"]');
            if (vid && parseInt(vid.value, 10) > 0) {
                return false;
            }
            // Otherwise any empty attribute select means the choice is incomplete.
            var selects = form.querySelectorAll('.variations select');
            for (var i = 0; i < selects.length; i++) {
                if (!selects[i].value) {
                    return true;
                }
            }
            return false;
        }

        function missingLabels() {
            var labels = [];
            var selects = form.querySelectorAll('.variations select');
            selects.forEach(function (s) {
                if (s.value) {
                    return;
                }
                var label = s.id ? form.querySelector('label[for="' + (window.CSS && CSS.escape ? CSS.escape(s.id) : s.id) + '"]') : null;
                var text = label ? label.textContent.replace(/[\s:]+$/, '').trim() : '';
                labels.push(text || 'an option');
            });
            return labels;
        }

        function humanList(items) {
            if (items.length === 0) {
                return '';
            }
            if (items.length === 1) {
                return items[0];
            }
            return items.slice(0, -1).join(', ') + ' and ' + items[items.length - 1];
        }

        function noticeEl() {
            var el = form.querySelector('.rd-variation-notice');
            if (el) {
                return el;
            }
            el = document.createElement('div');
            el.className = 'rd-variation-notice';
            el.setAttribute('role', 'alert');
            el.setAttribute('aria-live', 'assertive');
            el.hidden = true;
            var anchor = form.querySelector('.woocommerce-variation-add-to-cart') || variations;
            if (anchor && anchor.parentNode) {
                anchor.parentNode.insertBefore(el, anchor);
            } else {
                form.insertBefore(el, form.firstChild);
            }
            return el;
        }

        function showNotice() {
            var labels = missingLabels();
            var el = noticeEl();
            el.textContent = labels.length
                ? (PREFIX + ' ' + humanList(labels) + ' ' + SUFFIX)
                : GENERIC;
            el.hidden = false;
        }

        function hideNotice() {
            var el = form.querySelector('.rd-variation-notice');
            if (el) {
                el.hidden = true;
            }
        }

        function scrollToVariations() {
            var target = variations || form;
            if (target && target.scrollIntoView) {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // Keep the Buy Now button's disabled state mirrored to WooCommerce's
        // Add to Basket button so it greys out and the floating proxy treats it
        // as disabled. Clear the notice automatically once a variation is valid.
        function syncState() {
            var needed = selectionNeeded();
            if (buyBtn) {
                buyBtn.classList.toggle('disabled', needed);
                buyBtn.classList.toggle('wc-variation-selection-needed', needed);
                buyBtn.setAttribute('aria-disabled', needed ? 'true' : 'false');
            }
            if (!needed) {
                hideNotice();
            }
        }

        if (addBtn && 'MutationObserver' in window) {
            new MutationObserver(syncState).observe(addBtn, { attributes: true, attributeFilter: ['class'] });
        }
        // WooCommerce variation events (jQuery custom events) as a second signal.
        if (window.jQuery) {
            window.jQuery(form).on('show_variation hide_variation reset_data found_variation woocommerce_variation_has_changed', syncState);
        }
        syncState();

        // Single capture-phase guard covering inline + floating Add / Buy buttons.
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest(
                '.rd-buy-now-button, .single_add_to_cart_button, .rd-mobile-actionbar-add, .rd-mobile-actionbar-buy'
            );
            if (!trigger) {
                return;
            }
            if (!selectionNeeded()) {
                return; // valid selection — let WooCommerce proceed normally.
            }
            e.preventDefault();
            e.stopImmediatePropagation();
            showNotice();
            scrollToVariations();
        }, true);

        // Safety net for any other submission path (e.g. Enter key).
        form.addEventListener('submit', function (e) {
            if (selectionNeeded()) {
                e.preventDefault();
                showNotice();
                scrollToVariations();
            }
        });
    });
})();
