/**
 * Native WOOSB bundle list — same multi-column layout + typography as rd-bb-summary.
 */
(function () {
    'use strict';

    function initBundleListLayout(root) {
        var scope = root || document;
        scope.querySelectorAll('.woocommerce div.product .woosb-products.woosb-products-layout-list').forEach(function (list) {
            var rows = list.querySelectorAll('.woosb-product:not(.woosb-product-hidden)');
            var count = rows.length;

            list.classList.remove('rd-bb-summary--cols-2', 'rd-bb-summary--cols-3');
            if (count > 12) {
                list.classList.add('rd-bb-summary--cols-3');
            } else if (count > 6) {
                list.classList.add('rd-bb-summary--cols-2');
            }

            list.querySelectorAll('.woosb-name').forEach(function (nameEl) {
                nameEl.classList.add('rd-bb-summary-line');
            });
        });
    }

    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    ready(function () {
        initBundleListLayout(document);
    });

    window.matrixRdInitBundleListLayout = initBundleListLayout;
})();
