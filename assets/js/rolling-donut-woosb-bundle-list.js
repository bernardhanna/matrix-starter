/**
 * Native WOOSB bundle list — same multi-column layout + typography as rd-bb-summary.
 */
(function () {
    'use strict';

    function summarySortName(name) {
        return String(name || '').replace(/\s+[—–-]\s+(large|midi)\s*$/i, '').trim();
    }

    function sortBundleRowsAz(list) {
        // Box builder keeps the native WPC list as a hidden data layer. Reordering
        // those nodes would change picker/slot order; the visible summary is sorted
        // separately in rd-box-builder.js.
        if (document.getElementById('rd-bb-summary') && list.closest('.woosb-wrap')) {
            return;
        }

        var rows = Array.prototype.slice.call(list.querySelectorAll('.woosb-product:not(.woosb-product-hidden)'));
        rows.sort(function (a, b) {
            var aEl = a.querySelector('.rd-bb-summary-label, .woosb-name');
            var bEl = b.querySelector('.rd-bb-summary-label, .woosb-name');
            var an = summarySortName(aEl ? aEl.textContent : '');
            var bn = summarySortName(bEl ? bEl.textContent : '');
            return an.localeCompare(bn, undefined, { sensitivity: 'base', numeric: true });
        });
        rows.forEach(function (row) {
            list.appendChild(row);
        });
    }

    function initBundleListLayout(root) {
        var scope = root || document;
        scope.querySelectorAll('.woocommerce div.product .woosb-products.woosb-products-layout-list').forEach(function (list) {
            sortBundleRowsAz(list);

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
