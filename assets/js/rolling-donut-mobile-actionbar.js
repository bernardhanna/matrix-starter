/**
 * Floating mobile action bar for non-box-builder product pages.
 *
 * The bar is a lightweight proxy: its buttons forward clicks to the real
 * Add-to-Basket / Buy Now buttons inside form.cart, so all native WooCommerce
 * behaviour (validation, variations, quantity, redirects) is preserved.
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    ready(function () {
        var bar = document.querySelector('.rd-mobile-actionbar');
        if (!bar) { return; }

        var form = document.querySelector('form.cart');
        var addReal = form ? form.querySelector('.single_add_to_cart_button') : null;
        var buyReal = form ? form.querySelector('.rd-buy-now-button') : null;

        var addBtn = bar.querySelector('.rd-mobile-actionbar-add');
        var buyBtn = bar.querySelector('.rd-mobile-actionbar-buy');

        // No usable form/buttons — remove the bar so it can't mislead.
        if (!form || !addReal) {
            bar.parentNode && bar.parentNode.removeChild(bar);
            return;
        }

        function proxy(real) {
            // Variation not chosen (button disabled): bring the form into view so
            // the customer can pick options instead of a dead tap.
            if (real.disabled || real.classList.contains('disabled')) {
                if (form.scrollIntoView) {
                    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }
            real.click();
        }

        if (addBtn) {
            addBtn.addEventListener('click', function () { proxy(addReal); });
        }

        if (buyBtn) {
            if (buyReal) {
                buyBtn.addEventListener('click', function () { proxy(buyReal); });
            } else {
                buyBtn.parentNode && buyBtn.parentNode.removeChild(buyBtn);
            }
        }

        // Once the real buttons scroll into view (customer reached the bottom),
        // slide the floating bar away so it never covers them.
        var anchor = buyReal || addReal;
        if (anchor && 'IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                bar.classList.toggle('rd-actionbar-hidden', entries[0].isIntersecting);
            }, { threshold: 0, rootMargin: '0px 0px -72px 0px' });
            observer.observe(anchor);
        }
    });
})();
