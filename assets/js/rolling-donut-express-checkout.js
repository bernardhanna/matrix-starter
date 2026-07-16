(function ($) {
    'use strict';

    var config = window.matrixRdExpressCheckout || {};
    var pickupMethodId = config.pickupMethodId || 'local_pickup_plus';
    var wizardSteps = ['method', 'schedule', 'details', 'pay'];
    var currentWizardStep = 0;
    var wizardReady = false;
    var checkoutValidationArmed = false;
    // null = follow the responsive default; true/false = explicit user choice.
    var orderSummaryUserState = null;
    // Last pickup location the customer genuinely chose. Local Pickup Plus can
    // blank its <select> during an order-review refresh before its own
    // persistence lands, so we remember the choice and restore it (see
    // restorePickupSelection). The guard stops a restore from re-entering itself.
    var rememberedPickup = '';
    var pickupRestoreGuard = false;
    var pickupUserSelected = false;
    // Brief window after opening the pickup dropdown where we ignore selections.
    // Mobile Select2 can treat the open tap as a select of the first option.
    var pickupOpenGuardUntil = 0;
    var pickupLoadingPollId = null;
    var pickupFieldSnapshotHtml = '';
    var pickupLocationsReady = false;
    var pickupWarmupTimer = null;
    // Phase 1 auto-advance: the Method and Date steps are single-choice, so once
    // a valid choice is made we move the customer to the next step automatically.
    // The Continue buttons stay as a manual fallback. Can be disabled by the
    // server via `autoAdvance: false`.
    var autoAdvanceEnabled = config.autoAdvance !== false;

    function isDesktopOrderSummary() {
        return window.matchMedia('(min-width: 1024px)').matches;
    }

    function syncMobileOrderSummaryPlacement() {
        var $grid = $('.rd-express-checkout');
        var $summary = $('.rd-express-checkout__summary');
        var $payBody = $('#rd-checkout-step-pay .rd-checkout-step__body');
        var isMobile = window.matchMedia('(max-width: 1023px)').matches;
        var onPayStep = getWizardStepIndex('pay') === currentWizardStep;

        if (!$grid.length || !$summary.length || !$payBody.length) {
            return;
        }

        if (isMobile && onPayStep) {
            if (!$summary.hasClass('rd-order-summary--mobile-inline')) {
                $payBody.prepend($summary);
                $summary.addClass('rd-order-summary--mobile-inline');
            }

            return;
        }

        if ($summary.hasClass('rd-order-summary--mobile-inline')) {
            $grid.append($summary);
            $summary.removeClass('rd-order-summary--mobile-inline');
        }
    }

    function rememberOrderSummaryOpenState() {
        if (isDesktopOrderSummary()) {
            return;
        }

        var $summary = $('.rd-order-summary');

        if ($summary.length && !$summary.hasClass('rd-order-summary--collapsed')) {
            orderSummaryUserState = true;
        }
    }

    function applyOrderSummaryState() {
        var $summary = $('.rd-order-summary');

        if (!$summary.length) {
            return;
        }

        var $bar = $summary.find('.rd-order-summary__bar').first();

        syncMobileOrderSummaryPlacement();

        if (isDesktopOrderSummary()) {
            $summary.removeClass('rd-order-summary--collapsed');
            $bar.attr('aria-expanded', 'true');
            return;
        }

        var shouldOpen = orderSummaryUserState === true;

        $summary.toggleClass('rd-order-summary--collapsed', !shouldOpen);
        $bar.attr('aria-expanded', shouldOpen ? 'true' : 'false');
    }

    function clearCheckoutBlockUi() {
        var $form = $('form.checkout');
        var $payment = $('#payment.woocommerce-checkout-payment');
        var $reviewTable = $('.woocommerce-checkout-review-order-table');
        var $orderReview = $('#order_review');

        if ($form.length) {
            $form.unblock();
            $form.removeClass('processing');
            $form.find('> .blockUI.blockOverlay').remove();
        }

        if ($payment.length) {
            $payment.unblock();
            $payment.find('.blockUI.blockOverlay').remove();
        }

        if ($reviewTable.length) {
            $reviewTable.unblock();
            $reviewTable.find('.blockUI.blockOverlay').remove();
        }

        if ($orderReview.length) {
            $orderReview.unblock();
            $orderReview.find('.blockUI.blockOverlay').remove();
        }
    }

    function closePickupLocationDropdowns() {
        $('#rd-checkout-step-method select.pickup-location-lookup.select2-hidden-accessible').each(function () {
            var $select = $(this);

            if (!$select.data('select2')) {
                return;
            }

            try {
                $select.select2('close');
            } catch (err) {
                // Select2 may throw if the picker is mid-init.
            }
        });
    }

    function getChosenShippingMethod() {
        var $checked = $('input.shipping_method:checked');
        if ($checked.length) {
            return $checked.val() || '';
        }

        return $('input.shipping_method').first().val() || '';
    }

    function isPickupMethod(methodId) {
        if (!methodId) {
            return false;
        }

        return methodId.indexOf('local_pickup') !== -1 || methodId === pickupMethodId;
    }

    function getSelectedPickupWrap() {
        var $li = $('#rd-checkout-step-method ul.woocommerce-shipping-methods > li')
            .filter(function () {
                var $radio = $(this).find('input.shipping_method').first();
                return $radio.is(':checked') && isPickupMethod($radio.val());
            })
            .first();

        if (!$li.length) {
            return $();
        }

        var $wrap = $li.children('.rd-shipping-option-pickup');
        return $wrap.length ? $wrap : $li;
    }

    function isPickupLocationSelectReady($select) {
        if (!$select.length) {
            return false;
        }

        if (!pickupSelectHasLocations($select)) {
            return false;
        }

        if (!$select.hasClass('select2-hidden-accessible')) {
            return false;
        }

        var $container = $select.nextAll('.select2-container').first();
        var $rendered = $container.find('.select2-selection__rendered');

        if (!$container.length || !$rendered.length) {
            return false;
        }

        // Do not use offsetWidth while the loading state hides the field — that
        // created a catch-22 where the spinner never cleared.
        if ($select.closest('.rd-pickup-loading').length) {
            return true;
        }

        return $container[0].offsetWidth > 20 && $.trim($rendered.text()) !== '';
    }

    function isPickupFieldPopulated($select) {
        return pickupSelectHasLocations($select);
    }

    function markPickupLocationsReady($context) {
        var $li = $context && $context.length
            ? ($context.is('li') ? $context : $context.closest('li'))
            : getPickupOptionLi();
        var $select = $li.find('select.pickup-location-lookup').first();

        if (isPickupFieldPopulated($select)) {
            pickupLocationsReady = true;
        }
    }

    function resetPickupLocationsReady() {
        pickupLocationsReady = false;
    }

    function sanitizePickupSnapshotHtml(html) {
        if (!html) {
            return '';
        }

        var $temp = $('<div></div>').html(html);

        $temp.find('.select2-container').remove();
        $temp.find('select.pickup-location-lookup').each(function () {
            var $select = $(this);

            $select.removeClass('select2-hidden-accessible');
            $select.removeAttr('data-select2-id aria-hidden tabindex');
        });

        return $temp.html();
    }

    function destroyPickupSelect2($select) {
        if (!$select || !$select.length || !$.fn.select2) {
            return;
        }

        if ($select.hasClass('select2-hidden-accessible')) {
            try {
                $select.select2('destroy');
            } catch (error) {
                $select.removeClass('select2-hidden-accessible');
            }
        }

        $select.nextAll('.select2-container').remove();
    }

    function pickupSelectHasLocations($select) {
        if (!$select || !$select.length) {
            return false;
        }

        var hasLocations = false;

        $select.find('option').each(function () {
            if (isValidPickupSelection($(this).val())) {
                hasLocations = true;
                return false;
            }
        });

        return hasLocations;
    }

    function consolidatePickupLocationFields($li) {
        if (!$li || !$li.length) {
            return;
        }

        decorateShippingOptions();

        var $wraps = $li.children('.rd-shipping-option-pickup');

        if ($wraps.length > 1) {
            var $primary = $wraps.first();

            $wraps.slice(1).each(function () {
                $(this).children().appendTo($primary);
                $(this).remove();
            });
        }

        $li.children('.pickup-location-field, .pickup-location-lookup-field, .wc-local-pickup-plus-pickup-details').appendTo(
            $li.children('.rd-shipping-option-pickup').first()
        );

        var $wrap = $li.children('.rd-shipping-option-pickup').first();

        if (!$wrap.length) {
            return;
        }

        var $selects = $wrap.find('select.pickup-location-lookup');
        var $keeper = $selects.filter(function () {
            return pickupSelectHasLocations($(this));
        }).first();

        if (!$keeper.length) {
            $keeper = $selects.first();
        }

        $selects.each(function () {
            var $select = $(this);

            if ($select.is($keeper)) {
                return;
            }

            destroyPickupSelect2($select);
            $select.closest('.pickup-location-field, .pickup-location-lookup-field').remove();
        });

        if ($keeper.length) {
            $keeper.nextAll('.select2-container').slice(1).remove();
        }

        $wrap.find('.rd-pickup-location-loading').not(':first').remove();
    }

    function getPickupWrapForLi($li, createIfMissing) {
        if (!$li || !$li.length) {
            return $();
        }

        decorateShippingOptions();

        var $wrap = $li.children('.rd-shipping-option-pickup');

        if (!$wrap.length && createIfMissing) {
            $li.append('<div class="rd-shipping-option-pickup rd-pickup-pending"></div>');
            $wrap = $li.children('.rd-shipping-option-pickup');
        }

        return $wrap.first();
    }

    function getPickupWrapContext($context) {
        if ($context && $context.length) {
            if ($context.is('.rd-shipping-option-pickup')) {
                return $context;
            }

            if ($context.is('li')) {
                return getPickupWrapForLi($context, false);
            }

            var $wrap = $context.find('.rd-shipping-option-pickup').first();

            if ($wrap.length) {
                return $wrap;
            }
        }

        return getSelectedPickupWrap();
    }

    function isPickupContextActive($context) {
        if ($context && $context.length) {
            var $li = $context.is('li') ? $context : $context.closest('li');
            var $radio = $li.find('input.shipping_method').first();

            if ($radio.length && isPickupMethod($radio.val())) {
                return $radio.is(':checked') || $li.hasClass('rd-pickup-pending') || $li.find('.rd-pickup-pending').length > 0;
            }
        }

        return isPickupMethod(getChosenShippingMethod());
    }

    function getPickupOptionLi() {
        var $li = $();

        $('#rd-checkout-step-method input.shipping_method').each(function () {
            if (isPickupMethod($(this).val())) {
                $li = $(this).closest('li');
                return false;
            }
        });

        return $li;
    }

    function cachePickupFieldSnapshot() {
        var $wrap = getPickupOptionLi().children('.rd-shipping-option-pickup');

        if (!$wrap.length) {
            return;
        }

        var $select = $wrap.find('select.pickup-location-lookup').first();

        if (isPickupLocationSelectReady($select)) {
            pickupFieldSnapshotHtml = sanitizePickupSnapshotHtml($wrap.html());
        }
    }

    function restorePickupSnapshotIfNeeded($li) {
        if (!pickupFieldSnapshotHtml || !$li || !$li.length) {
            return;
        }

        consolidatePickupLocationFields($li);

        var $select = $li.find('select.pickup-location-lookup').first();

        if ($select.length && (isPickupLocationSelectReady($select) || pickupSelectHasLocations($select))) {
            return;
        }

        var $wrap = getPickupWrapForLi($li, true);
        var sanitized = sanitizePickupSnapshotHtml(pickupFieldSnapshotHtml);

        if (!sanitized) {
            return;
        }

        destroyPickupSelect2($wrap.find('select.pickup-location-lookup'));
        $wrap.html(sanitized);
        consolidatePickupLocationFields($li);
    }

    function warmupPickupLocationFields() {
        var $li = getPickupOptionLi();

        if (!$li.length) {
            return;
        }

        decorateShippingOptions();
        $li.addClass('rd-pickup-warmup');
        consolidatePickupLocationFields($li);

        if (!$li.find('select.pickup-location-lookup').length) {
            restorePickupSnapshotIfNeeded($li);
        }

        prepareVisiblePickupSelects($li);
        applyPickupAddresses();
        consolidatePickupLocationFields($li);
        $li.removeClass('rd-pickup-warmup');
        markPickupLocationsReady($li);
        cachePickupFieldSnapshot();
    }

    function showPickupLocationOptimistically($li) {
        if (!$li || !$li.length) {
            return;
        }

        $('#rd-checkout-step-method .rd-pickup-pending')
            .not($li.find('.rd-pickup-pending'))
            .removeClass('rd-pickup-pending');

        var $wrap = getPickupWrapForLi($li, false);

        if ($wrap.length) {
            $wrap.addClass('rd-pickup-pending');
        } else {
            $li.addClass('rd-pickup-pending');
        }

        consolidatePickupLocationFields($li);

        if (!$li.find('select.pickup-location-lookup').length) {
            restorePickupSnapshotIfNeeded($li);
        }

        $li.find('.pickup-location-field').addClass('rd-pickup-pending');
        prepareVisiblePickupSelects($li);

        if (pickupLocationsReady) {
            setPickupLocationLoading(false, $li);
        } else {
            syncPickupLocationLoading($li);
        }
    }

    function stopPickupLoadingPoll() {
        if (pickupLoadingPollId) {
            window.clearInterval(pickupLoadingPollId);
            pickupLoadingPollId = null;
        }
    }

    function setPickupLocationLoading(loading, $context) {
        var $wrap = getPickupWrapContext($context);

        if (!$wrap.length && loading) {
            var $li = $context && $context.is('li') ? $context : ($context && $context.length ? $context.closest('li') : $());

            if (!$li.length && isPickupMethod(getChosenShippingMethod())) {
                $li = $('#rd-checkout-step-method input.shipping_method:checked').closest('li');
            }

            if ($li.length) {
                $wrap = getPickupWrapForLi($li, true);
            }
        }

        if (!$wrap.length) {
            if (!loading) {
                stopPickupLoadingPoll();
            }

            return;
        }

        if (!loading) {
            stopPickupLoadingPoll();
            $wrap.removeClass('rd-pickup-loading');
            $wrap.find('.rd-pickup-location-loading').remove();
            return;
        }

        $wrap.addClass('rd-pickup-loading');

        if (!$wrap.find('.rd-pickup-location-loading').length) {
            var message = getWizardMessages().pickupLoading || 'Please wait… loading collection locations.';

            $wrap.prepend(
                '<div class="rd-pickup-location-loading" role="status" aria-live="polite">' +
                    '<span class="rd-pickup-location-loading__spinner" aria-hidden="true"></span>' +
                    '<span class="rd-pickup-location-loading__text"></span>' +
                '</div>'
            );
            $wrap.find('.rd-pickup-location-loading__text').text(message);
        }
    }

    function syncPickupLocationLoading($context) {
        if (!isPickupContextActive($context)) {
            setPickupLocationLoading(false, $context);
            return;
        }

        var $wrap = getPickupWrapContext($context);
        var $li = $context && $context.is('li') ? $context : $wrap.closest('li');

        if ($li.length) {
            consolidatePickupLocationFields($li);
        }

        $wrap = getPickupWrapContext($context);
        var $select = $wrap.find('select.pickup-location-lookup').first();

        if (isPickupLocationSelectReady($select) || isPickupFieldPopulated($select) || pickupLocationsReady) {
            markPickupLocationsReady($context);
            prepareVisiblePickupSelects($li.length ? $li : null);
            setPickupLocationLoading(false, $context);
            return;
        }

        setPickupLocationLoading(true, $context);

        if (pickupLoadingPollId) {
            return;
        }

        var attempts = 0;

        pickupLoadingPollId = window.setInterval(function () {
            attempts += 1;
            $wrap = getPickupWrapContext($context);
            $select = $wrap.find('select.pickup-location-lookup').first();

            if (isPickupLocationSelectReady($select) || isPickupFieldPopulated($select) || attempts > 100) {
                if (isPickupFieldPopulated($select)) {
                    markPickupLocationsReady($context);
                }
                setPickupLocationLoading(false, $context);
            }
        }, 100);
    }

    function schedulePickupWarmup() {
        if (pickupLocationsReady) {
            return;
        }

        warmupPickupLocationFields();

        if (pickupWarmupTimer) {
            window.clearTimeout(pickupWarmupTimer);
        }

        pickupWarmupTimer = window.setTimeout(function () {
            pickupWarmupTimer = null;

            if (!pickupLocationsReady) {
                warmupPickupLocationFields();
            }
        }, 400);
    }

    function decorateShippingOptions() {
        $('#rd-checkout-step-method ul.woocommerce-shipping-methods').each(function () {
            $(this)
                .find('> li')
                .each(function (index) {
                    var $item = $(this);
                    $item.addClass('shipping-option-' + index);

                    if (!$item.find('> .shipping-method-wrapper').length) {
                        $item.children('input.shipping_method, label').wrapAll('<div class="shipping-method-wrapper"></div>');
                    }

                    var $pickupFields = $item.children(
                        '.pickup-location-field, .pickup-location-lookup-field, .wc-local-pickup-plus-pickup-details'
                    );

                    if ($pickupFields.length && !$item.find('> .rd-shipping-option-pickup').length) {
                        $pickupFields.wrapAll('<div class="rd-shipping-option-pickup"></div>');
                    }
                });
        });
    }

    function syncPickupVisibility() {
        var methodId = getChosenShippingMethod();
        var isPickup = isPickupMethod(methodId);

        $('#rd-checkout-step-method ul.woocommerce-shipping-methods > li').each(function () {
            var $li = $(this);
            var $radio = $li.find('input.shipping_method').first();
            var $pickupWrap = $li.children('.rd-shipping-option-pickup');
            var $pickupFields = $pickupWrap.length
                ? $pickupWrap
                : $li.find('.pickup-location-field, .pickup-location-lookup-field, .wc-local-pickup-plus-pickup-details');

            if (!$pickupFields.length) {
                return;
            }

            var isSelectedPickup = isPickup && $radio.is(':checked');
            // Drive visibility with a class and let CSS own `display` (keyed on
            // the checked radio). Clearing any inline display avoids the
            // delivery -> collection race where a stale inline `display:none`
            // (set while delivery was selected, or written by the Local Pickup
            // Plus plugin during its AJAX re-render) could survive and keep the
            // location picker hidden.
            $pickupFields.css('display', '').toggleClass('rd-pickup-visible', isSelectedPickup);

            if (isSelectedPickup) {
                $pickupFields.removeClass('rd-pickup-pending');
                consolidatePickupLocationFields($li);
                refreshPickupSelect2($li);
                window.setTimeout(function () {
                    prepareVisiblePickupSelects($li);
                    consolidatePickupLocationFields($li);
                    cachePickupFieldSnapshot();
                    syncPickupLocationLoading($li);
                }, 0);
            }
        });

        $('#rd-checkout-step-method .rd-pickup-pending').not('.rd-pickup-visible').removeClass('rd-pickup-pending');

        if (!isPickup) {
            resetPickupLocationsReady();
            setPickupLocationLoading(false);
            $('#rd-checkout-step-method .rd-pickup-pending').removeClass('rd-pickup-pending');
            $('#rd-checkout-step-method li.rd-pickup-pending').removeClass('rd-pickup-pending');
        } else {
            syncPickupLocationLoading($('#rd-checkout-step-method input.shipping_method:checked').closest('li'));
        }
    }

    // Select2 measures 0 width when it is initialised inside a hidden field, so
    // a picker that was created while delivery was selected can render as an
    // invisible/zero-width box once collection is chosen. When the field is
    // revealed, clear the stale baked inline width so Select2 recomputes against
    // the now-visible, full-width field.
    function refreshPickupSelect2($scope) {
        if (!$.fn.select2) {
            return;
        }

        $scope.find('select.pickup-location-lookup.select2-hidden-accessible').each(function () {
            var $select = $(this);
            var $container = $select.nextAll('.select2-container').first();

            if ($container.length && (!$container[0].offsetWidth || $container[0].style.width)) {
                $container.css('width', '');
                $select.trigger('change.select2');
            }
        });
    }

    // True when the picker's Select2/selectWoo is already anchored to its field.
    function pickupDropdownAnchored($select, $wrap) {
        var instance = $select.data('select2');
        var options = instance && instance.options ? instance.options.options : null;
        return !!(options && options.dropdownParent && options.dropdownParent[0] === $wrap[0]);
    }

    // Local Pickup Plus initialises its location <select> as a selectWoo (Select2
    // fork) with the default dropdownParent (<body>). A body-appended menu is
    // positioned against the viewport, so when the field sits low on screen it
    // flips the list *above* the input and can look detached from it. Re-init the
    // picker anchored to its field wrapper instead — preserving LPP's own options,
    // whose templateResult/templateSelection render the address sub-lines — so the
    // menu always opens directly beneath the input at full field width.
    function anchorPickupSelect($select) {
        var $wrap = $select.closest('.pickup-location-field');
        var instance = $select.data('select2');

        if (!$wrap.length || !instance || !instance.options || !instance.options.options) {
            return;
        }

        normalizePickupSelectOptions($select);

        if (pickupDropdownAnchored($select, $wrap)) {
            syncPickupSelectDisplay($select);
            return;
        }

        var value = pickupUserSelected ? $select.val() : '';
        var existingTemplateResult = instance.options.options.templateResult;
        var existingTemplateSelection = instance.options.options.templateSelection;
        var reinit = $.extend({}, instance.options.options, {
            dropdownParent: $wrap,
            width: '100%',
            allowClear: false,
            placeholder: getPickupPlaceholderText(),
            templateResult: function (data, container) {
                if (!data.id && data.id !== 0) {
                    return null;
                }

                if (typeof existingTemplateResult === 'function') {
                    return existingTemplateResult(data, container);
                }

                return data.text;
            },
            templateSelection: function (data, container) {
                if (!data.id && data.id !== 0) {
                    return getPickupPlaceholderText();
                }

                if (typeof existingTemplateSelection === 'function') {
                    return existingTemplateSelection(data, container);
                }

                return data.text;
            },
        });

        $wrap.css('position', 'relative');
        $select.select2('destroy');
        $select.select2(reinit);

        if (value && pickupUserSelected) {
            $select.val(value).trigger('change.select2');
        } else {
            clearPickupSelectValue($select);
        }

        syncPickupSelectDisplay($select);
    }

    function getPickupPlaceholderText() {
        return getWizardMessages().selectPickupPlaceholder || 'Select a collection location';
    }

    function getActivePickupSelect() {
        var $li = $('#rd-checkout-step-method input.shipping_method:checked').closest('li');

        if (!$li.length) {
            $li = getPickupOptionLi();
        }

        if ($li.length) {
            consolidatePickupLocationFields($li);
            return $li.find('select.pickup-location-lookup').first();
        }

        return $('#rd-checkout-step-method select.pickup-location-lookup').first();
    }

    function recordPickupUserSelection(value, $select) {
        if (!isValidPickupSelection(value)) {
            return false;
        }

        rememberedPickup = value;
        pickupUserSelected = true;
        clearStepErrors('method');

        if ($select && $select.length) {
            syncPickupSelectDisplay($select);
        }

        return true;
    }

    function getResolvedPickupValue($select) {
        $select = $select && $select.length ? $select : getActivePickupSelect();

        if (!$select.length || !pickupUserSelected) {
            return '';
        }

        if (isValidPickupSelection(rememberedPickup)) {
            return rememberedPickup;
        }

        var current = $select.val();

        return isValidPickupSelection(current) ? current : '';
    }

    function isValidPickupSelection(value) {
        return !!(value && String(value) !== '0');
    }

    function isGenuinePickupSelectEvent(event) {
        return !!(event && event.params && event.params.originalEvent);
    }

    function isEmptyPickupOption($option) {
        var value = $.trim(String($option.attr('value') || ''));
        var text = $.trim($option.text() || '');

        return (!value || value === '0') && !text;
    }

    function normalizePickupSelectOptions($select) {
        if (!$select || !$select.length) {
            return;
        }

        var placeholderText = getPickupPlaceholderText();
        var selectedValue = isValidPickupSelection($select.val()) ? String($select.val()) : '';
        var locationOptions = [];

        $select.find('option').each(function () {
            var $option = $(this);
            var value = $.trim(String($option.attr('value') || ''));

            if (isEmptyPickupOption($option)) {
                return;
            }

            if ($option.attr('data-placeholder') === 'true' || $option.attr('data-placeholder') === true) {
                return;
            }

            if (!isValidPickupSelection(value)) {
                return;
            }

            locationOptions.push(this);
        });

        $select.empty();
        $select.append(
            $('<option>', {
                value: '',
                'data-placeholder': 'true',
                text: placeholderText,
            })
        );

        if (locationOptions.length) {
            $select.append(locationOptions);
        }

        if (selectedValue && pickupUserSelected && $select.find('option[value="' + selectedValue + '"]').length) {
            $select.val(selectedValue);
        } else {
            $select.val('');
        }
    }

    function ensurePickupPlaceholderOption($select) {
        normalizePickupSelectOptions($select);
    }

    function ensurePickupSelectPlaceholder($select) {
        if (!$select || !$select.length) {
            return;
        }

        normalizePickupSelectOptions($select);

        if ($select.hasClass('select2-hidden-accessible')) {
            var instance = $select.data('select2');
            var options = instance && instance.options ? instance.options.options : null;

            if (options) {
                options.placeholder = getPickupPlaceholderText();
                options.allowClear = false;
            }
        }
    }

    function syncPickupSelectDisplay($select) {
        if (!$select || !$select.length || !$select.hasClass('select2-hidden-accessible')) {
            return;
        }

        var value = $select.val();
        var $rendered = $select.nextAll('.select2-container').first().find('.select2-selection__rendered');

        if (!$rendered.length) {
            return;
        }

        if (!isValidPickupSelection(value)) {
            $rendered.text(getPickupPlaceholderText());
            $rendered.addClass('select2-selection__placeholder');
        } else {
            $rendered.removeClass('select2-selection__placeholder');
        }
    }

    function clearPickupSelectValue($select) {
        ensurePickupPlaceholderOption($select);
        $select.val('');

        if ($select.hasClass('select2-hidden-accessible')) {
            $select.val(null).trigger('change.select2');
            syncPickupSelectDisplay($select);
        }

        $select.trigger('change');
    }

    // Anchor the dropdown and clear any LPP/Select2 default before the customer opens it.
    function prepareVisiblePickupSelects($scope) {
        var $root = $scope && $scope.length ? $scope : $('#rd-checkout-step-method');

        if ($scope && $scope.is('li')) {
            consolidatePickupLocationFields($scope);
        }

        $root.find('select.pickup-location-lookup').each(function () {
            var $select = $(this);

            ensurePickupSelectPlaceholder($select);

            if ($select.hasClass('select2-hidden-accessible')) {
                anchorPickupSelect($select);
            }

            if (!pickupUserSelected && isValidPickupSelection($select.val())) {
                pickupRestoreGuard = true;
                clearPickupSelectValue($select);
                window.setTimeout(function () {
                    pickupRestoreGuard = false;
                }, 150);
            }
        });

        syncPickupLocationLoading($scope && $scope.is('li') ? $scope : null);
    }

    function clearPickupLocationSelection() {
        var $select = getActivePickupSelect();

        if (!$select.length) {
            return;
        }

        pickupRestoreGuard = true;
        clearPickupSelectValue($select);
        rememberedPickup = '';
        pickupUserSelected = false;

        window.setTimeout(function () {
            pickupRestoreGuard = false;
        }, 150);
    }

    // LPP and Select2 can pre-select the first/default location when collection is
    // chosen. Until the customer explicitly picks from the dropdown, keep the
    // field empty so validation and auto-advance do not skip the Method step.
    function enforcePickupRequiresUserChoice() {
        if (!isPickupMethod(getChosenShippingMethod())) {
            return;
        }

        var $select = getActivePickupSelect();

        if (!$select.length) {
            return;
        }

        if (pickupUserSelected && rememberedPickup) {
            if ($select.val() !== rememberedPickup && $select.find('option[value="' + rememberedPickup + '"]').length) {
                restorePickupSelection();
            }
            return;
        }

        if (isValidPickupSelection($select.val())) {
            clearPickupLocationSelection();
        }
    }

    // Put back a pickup location that an order-review refresh blanked out, and
    // re-fire LPP's events so the server stores it again. LPP only persists the
    // choice through its select2:select flow; a refresh that beats that flow
    // re-renders the <select> back to its empty placeholder, which made the
    // wizard wrongly report "Choose a pickup location". The guard prevents the
    // change/select2:select we dispatch here from recursing back into a restore.
    function restorePickupSelection() {
        if (!rememberedPickup || pickupRestoreGuard) {
            return;
        }

        var $select = getActivePickupSelect();

        if (!$select.length || $select.val()) {
            return;
        }

        if (!$select.find('option[value="' + rememberedPickup + '"]').length) {
            return;
        }

        pickupRestoreGuard = true;
        pickupUserSelected = true;
        $select.val(rememberedPickup);

        if ($select.hasClass('select2-hidden-accessible')) {
            $select.trigger('change.select2');
        }

        $select.trigger('change').trigger({ type: 'select2:select' });
        window.setTimeout(function () {
            pickupRestoreGuard = false;
        }, 1500);
    }

    // LPP rebuilds the location field on every order-review refresh, which can
    // drop the chosen value. Re-apply it, deferred so we run after LPP's own
    // refresh handlers have re-created the field. (Dropdown anchoring is handled
    // lazily at open time — see the select2:opening handler — because LPP may
    // re-init its picker after us, undoing an eager re-anchor.)
    function refreshPickupUi() {
        window.setTimeout(function () {
            if (pickupUserSelected && rememberedPickup) {
                restorePickupSelection();
            } else {
                enforcePickupRequiresUserChoice();
            }
        }, 0);
    }

    function ensureBillingToggle() {
        var $form = $('form.checkout.rd-express-checkout-form');
        var $toggle = $('#rd-bill-different-address');

        if (!$toggle.length) {
            $toggle = $(
                '<p id="rd-bill-different-address" class="form-row rd-bill-different-row">' +
                    '<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">' +
                    '<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="rd-bill-different-address-checkbox" name="rd_bill_different_address" value="1" />' +
                    '<span>Billing address is different from delivery address?</span></label></p>'
            );
            $('#customer_details').append($toggle);
        }

        $toggle.toggle($form.hasClass('rd-fulfilment-delivery'));
    }

    function ensureDeliveryLayout() {
        var $form = $('form.checkout.rd-express-checkout-form');
        var isDelivery = $form.hasClass('rd-fulfilment-delivery');
        var $details = $('#customer_details');
        var $shippingBlock = $('.rd-checkout-shipping-block');
        var $shippingFields = $shippingBlock.find('.woocommerce-shipping-fields');
        var $shippingAddress = $shippingFields.find('.shipping_address');
        var $billingBlock = $('.rd-checkout-billing-block');
        var $billingWrapper = $billingBlock.find('.woocommerce-billing-fields__field-wrapper');
        var $phone = $('#billing_phone_field');
        var $email = $('#billing_email_field');
        // Move the field's wrapper row (.single-field-wrapper) rather than the
        // bare <p>, otherwise we leave an empty wrapper behind (which still has
        // padding-bottom, creating a phantom gap) and the moved <p> loses the
        // consistent row spacing. Fall back to the <p> if it isn't wrapped.
        var $phoneRow = $phone.closest('.single-field-wrapper').length ? $phone.closest('.single-field-wrapper') : $phone;
        var $emailRow = $email.closest('.single-field-wrapper').length ? $email.closest('.single-field-wrapper') : $email;
        var $accountFields = $billingBlock.find('.woocommerce-account-fields');
        var $toggle = $('#rd-bill-different-address');
        var $billingHeading = $billingBlock.find('.woocommerce-billing-fields > h3');

        if (!isDelivery) {
            $('#rd-delivery-address-heading, #rd-delivery-contact').remove();

            if ($phone.length && $billingWrapper.length) {
                $billingWrapper.append($phoneRow);
            }

            if ($email.length && $billingWrapper.length) {
                $billingWrapper.append($emailRow);
            }

            if ($accountFields.length && $billingBlock.length) {
                $billingBlock.find('.woocommerce-billing-fields').after($accountFields);
            }

            var $additionalFields = $shippingBlock.find('.woocommerce-additional-fields');
            if ($additionalFields.length) {
                $shippingFields.after($additionalFields);
            }

            if ($toggle.length && $details.length) {
                $details.append($toggle);
            }

            if ($billingBlock.length && $shippingBlock.length) {
                $shippingBlock.after($billingBlock);
            }

            $billingHeading.text('Billing details');
            return;
        }

        if (!$('#rd-delivery-address-heading').length) {
            $shippingFields.prepend(
                '<h3 id="rd-delivery-address-heading" class="rd-delivery-address-heading text-black-full text-md-font font-reg420">Delivery address</h3>'
            );
        }

        var $contact = $('#rd-delivery-contact');
        if (!$contact.length) {
            $contact = $('<div id="rd-delivery-contact" class="rd-delivery-contact"></div>');
            $shippingAddress.after($contact);
        }

        $contact.empty();

        if ($phone.length) {
            $contact.append($phoneRow);
        }

        if ($email.length) {
            $contact.append($emailRow);
        }

        if ($accountFields.length && !$form.hasClass('rd-show-billing')) {
            $contact.append($accountFields);
        } else if ($accountFields.length) {
            $billingBlock.find('.woocommerce-billing-fields').after($accountFields);
        }

        var $additionalFields = $shippingBlock.find('.woocommerce-additional-fields');
        if ($additionalFields.length) {
            $contact.after($additionalFields);
        }

        var $layoutAnchor = $additionalFields.length ? $additionalFields : $contact;

        if ($toggle.length && $layoutAnchor.length) {
            $layoutAnchor.after($toggle);
        } else if ($toggle.length && $details.length) {
            $details.append($toggle);
        }

        if ($billingBlock.length && $toggle.length) {
            $toggle.after($billingBlock);
        } else if ($billingBlock.length && $shippingBlock.length) {
            $shippingBlock.after($billingBlock);
        }

        $billingHeading.text('Billing address');
        $('#ship-to-different-address').hide();
        $('#shipping_phone_field, #shipping_email_field').closest('.single-field-wrapper').remove();
        $('#shipping_phone_field, #shipping_email_field').remove();
    }

    function ensureDeliveryShipFlag() {
        var $form = $('form.checkout.rd-express-checkout-form');
        var $flag = $('#rd-ship-to-different-flag');

        if (!$flag.length) {
            $flag = $('<input type="hidden" name="ship_to_different_address" id="rd-ship-to-different-flag" value="1" />');
            $form.append($flag);
        }

        if ($form.hasClass('rd-fulfilment-delivery')) {
            $flag.prop('disabled', false).val('1');
            $('#ship-to-different-address-checkbox').prop('disabled', true);
        } else {
            $flag.prop('disabled', true);
            $('#ship-to-different-address-checkbox').prop('disabled', false);
        }
    }

    function applyFulfilmentMode() {
        var methodId = getChosenShippingMethod();
        var isPickup = isPickupMethod(methodId);
        var $form = $('form.checkout.rd-express-checkout-form');
        var $billingBlock = $('.rd-checkout-billing-block');
        var $shippingBlock = $('.rd-checkout-shipping-block');
        var $shippingAddress = $('.shipping_address');
        var $shipDifferentRow = $('#ship-to-different-address');
        var $billDifferentCheckbox = $('#rd-bill-different-address-checkbox');

        $form.toggleClass('rd-fulfilment-collection', isPickup);
        $form.toggleClass('rd-fulfilment-delivery', !isPickup);

        if (isPickup) {
            $form.removeClass('rd-show-billing');
            $shippingBlock.hide();
            $billingBlock.show();
            $shippingAddress.hide();
            $shipDifferentRow.hide();
            $billDifferentCheckbox.prop('checked', false);
        } else {
            $shippingBlock.show();
            $shippingAddress.show();
            $shipDifferentRow.hide();
            $billingBlock.show();
            $form.toggleClass('rd-show-billing', $billDifferentCheckbox.is(':checked'));
        }

        ensureBillingToggle();
        ensureDeliveryLayout();
        ensureDeliveryShipFlag();
        decorateShippingOptions();
        syncPickupVisibility();
        updateFulfilmentLabels();

        if (isDesktopOrderSummary()) {
            applyOrderSummaryState();
        }
    }

    // Swap any element carrying both data-rd-label-delivery and
    // data-rd-label-collection to the wording that matches the chosen method,
    // e.g. the step 1 "Continue to ... date" button and the step 2
    // "Choose your ... date" heading. Server-rendered defaults already match the
    // session method, so this only needs to react when the customer switches.
    function updateFulfilmentLabels() {
        var isPickup = isPickupMethod(getChosenShippingMethod());

        $('[data-rd-label-delivery][data-rd-label-collection]').each(function () {
            var $el = $(this);
            var text = isPickup
                ? $el.attr('data-rd-label-collection')
                : $el.attr('data-rd-label-delivery');

            if (text) {
                $el.text(text);
            }
        });
    }

    var syncingBillingFromShipping = false;

    function syncBillingFromShippingForStripe() {
        var $form = $('form.checkout.rd-express-checkout-form');

        if (syncingBillingFromShipping || !$form.hasClass('rd-fulfilment-delivery') || $form.hasClass('rd-show-billing')) {
            return;
        }

        syncingBillingFromShipping = true;

        if (!$('#billing_country').val()) {
            $('#billing_country').val($('#shipping_country').val() || 'IE');
        }

        if (!$('#shipping_country').val()) {
            $('#shipping_country').val('IE');
        }

        var map = {
            first_name: 'first_name',
            last_name: 'last_name',
            company: 'company',
            address_1: 'address_1',
            address_2: 'address_2',
            city: 'city',
            state: 'state',
            postcode: 'postcode',
            country: 'country',
            phone: 'phone',
            email: 'email',
        };

        $.each(map, function (shippingKey, billingKey) {
            var $shipping = $('#shipping_' + shippingKey);
            var $billing = $('#billing_' + billingKey);
            var shippingVal = $shipping.val() || '';

            if (!$billing.length || !$shipping.length) {
                return;
            }

            if ($billing.val() !== shippingVal) {
                $billing.val(shippingVal);
            }
        });

        syncCustomEircodeToPostcodes();

        syncingBillingFromShipping = false;
    }

    var DEFAULT_SEED_EIRCODE = 'D01 F5P2';

    function ensureDefaultCountries() {
        $('#billing_country, #shipping_country').each(function () {
            var $country = $(this);

            if (!$country.val()) {
                $country.val('IE').trigger('change');
            }
        });
    }

    function normalizeIrishCountyValue(raw) {
        var value = String(raw || '').trim();
        if (!value) {
            return value;
        }

        value = value.replace(/^co\.?\s*/i, '').replace(/^county\s+/i, '').trim();
        if (/^dublin\b/i.test(value)) {
            return 'Dublin';
        }

        return value;
    }

    function normalizeIrishCountyField($field) {
        if (!$field || !$field.length) {
            return;
        }

        var normalized = normalizeIrishCountyValue($field.val());
        if (normalized && $field.val() !== normalized) {
            $field.val(normalized).trigger('change');
        }
    }

    function isEircodeUserTouched() {
        var $customEircode = $('#custom_shipping_eircode');
        return $customEircode.length && $customEircode.data('rd-user-touched') === true;
    }

    function syncCustomEircodeToPostcodes() {
        var $customEircode = $('#custom_shipping_eircode');
        var $shippingPostcode = $('#shipping_postcode');
        var $billingPostcode = $('#billing_postcode');
        var $form = $('form.checkout.rd-express-checkout-form');

        if (!$customEircode.length) {
            return;
        }

        var customEl = $customEircode[0];
        var customFocused = customEl && document.activeElement === customEl;
        var customVal = String($customEircode.val() || '').trim();
        var shippingVal = String($shippingPostcode.val() || '').trim();
        var billingVal = $billingPostcode.length ? String($billingPostcode.val() || '').trim() : '';

        if (shippingVal === DEFAULT_SEED_EIRCODE && !customVal) {
            shippingVal = '';
        }

        if (customVal) {
            if ($shippingPostcode.length && shippingVal !== customVal) {
                $shippingPostcode.val(customVal);
            }
            if (
                $billingPostcode.length &&
                billingVal !== customVal &&
                $form.hasClass('rd-fulfilment-delivery') &&
                !$form.hasClass('rd-show-billing')
            ) {
                $billingPostcode.val(customVal);
            }
            return;
        }

        // User cleared or is editing the visible field — never overwrite it from hidden
        // postcode inputs (autofill often fills both, which made delete appear broken).
        if (customFocused || isEircodeUserTouched()) {
            if ($shippingPostcode.length && shippingVal && shippingVal !== DEFAULT_SEED_EIRCODE) {
                $shippingPostcode.val('');
            }
            if (
                $billingPostcode.length &&
                billingVal &&
                $form.hasClass('rd-fulfilment-delivery') &&
                !$form.hasClass('rd-show-billing')
            ) {
                $billingPostcode.val('');
            }
            return;
        }

        if (shippingVal && shippingVal !== DEFAULT_SEED_EIRCODE) {
            $customEircode.val(shippingVal);
            if (
                $billingPostcode.length &&
                !billingVal &&
                $form.hasClass('rd-fulfilment-delivery') &&
                !$form.hasClass('rd-show-billing')
            ) {
                $billingPostcode.val(shippingVal);
            }
            return;
        }

        if (billingVal) {
            $customEircode.val(billingVal);
            if ($shippingPostcode.length) {
                $shippingPostcode.val(billingVal);
            }
        }
    }

    function placeCaretAtEndIfAutofilledAtStart(input) {
        if (!input || !input.value) {
            return;
        }

        var start = input.selectionStart;
        var end = input.selectionEnd;

        if (start !== 0 || end !== 0 || start !== end) {
            return;
        }

        window.requestAnimationFrame(function () {
            var length = input.value.length;
            if (input.setSelectionRange) {
                input.setSelectionRange(length, length);
            }
        });
    }

    function syncCheckoutAddressAutofill() {
        normalizeIrishCountyField($('#shipping_state'));
        normalizeIrishCountyField($('#billing_state'));
        syncCustomEircodeToPostcodes();
    }

    function getWizardMessages() {
        return config.wizardMessages || {};
    }

    function dedupeMessages(messages) {
        var seen = {};
        var unique = [];

        messages.forEach(function (message) {
            if (!message || seen[message]) {
                return;
            }

            seen[message] = true;
            unique.push(message);
        });

        return unique;
    }

    function getFieldLabel($row) {
        var label = $.trim(
            $row
                .find('label')
                .first()
                .clone()
                .children()
                .remove()
                .end()
                .text()
        );

        return label.replace(/\*+$/, '').replace(/\s+/g, ' ').trim() || 'This field';
    }

    function getStepElement(slug) {
        return $('[data-rd-step="' + slug + '"]');
    }

    function getStepErrorsBox(slug) {
        return getStepElement(slug).find('.rd-checkout-step__errors').first();
    }

    function clearStepErrors(slug) {
        if (slug) {
            getStepErrorsBox(slug).empty().prop('hidden', true);
            getStepElement(slug).removeClass('rd-checkout-step--has-error');
            syncAriaInvalidStates(getStepElement(slug));

            if (slug === 'method') {
                $('#rd-checkout-step-method .rd-express-shipping-body').removeClass('rd-field-error');
            }

            return;
        }

        $('.rd-checkout-step__errors').empty().prop('hidden', true);
        $('.rd-checkout-step').removeClass('rd-checkout-step--has-error');
        $('#rd-checkout-step-method .rd-express-shipping-body').removeClass('rd-field-error');
        syncAriaInvalidStates($('form.checkout'));
    }

    function syncAriaInvalidStates($scope) {
        if (!$scope || !$scope.length) {
            return;
        }

        $scope.find('input, select, textarea').each(function () {
            var $input = $(this);
            var $row = $input.closest('.woocommerce-invalid');

            if ($row.length && !$input.is('[type="hidden"]')) {
                $input.attr('aria-invalid', 'true');
            } else {
                $input.removeAttr('aria-invalid');
            }
        });
    }

    function getGenericErrorPhrases() {
        var messages = getWizardMessages();

        return [
            messages.errorsTitle,
            messages.fixErrors,
            'Please fix the following to continue',
            'Please fix the errors below to continue',
        ].filter(Boolean);
    }

    function filterSpecificMessages(messages) {
        var generic = getGenericErrorPhrases();

        return dedupeMessages(
            (messages || []).filter(function (message) {
                return message && generic.indexOf(message) === -1;
            })
        );
    }

    function armCheckoutValidation() {
        checkoutValidationArmed = true;
    }

    function showStepErrors(slug, messages, options) {
        options = options || {};
        var list = filterSpecificMessages(messages);

        if (!list.length || !slug) {
            return;
        }

        if (!checkoutValidationArmed && !options.fromServer) {
            return;
        }

        var $box = getStepErrorsBox(slug);
        var $step = getStepElement(slug);
        var title = getWizardMessages().errorsTitle || "What's missing";
        var items = list
            .map(function (message) {
                return '<li>' + $('<div>').text(message).html() + '</li>';
            })
            .join('');

        $box
            .html(
                '<p class="rd-checkout-step__errors-title">' +
                    $('<div>').text(title).html() +
                    '</p><ul class="rd-checkout-step__errors-list">' +
                    items +
                    '</ul>'
            )
            .prop('hidden', false);
        $step.addClass('rd-checkout-step--has-error');
        syncAriaInvalidStates($step);

        if (options.scroll !== false) {
            window.setTimeout(function () {
                scrollToStepErrors(slug);
                focusFirstInvalidInStep(slug);
            }, 80);
        }
    }

    function focusFirstInvalidInStep(slug) {
        var $step = getStepElement(slug);
        var $input = $step
            .find('.woocommerce-invalid input, .woocommerce-invalid select, .woocommerce-invalid textarea')
            .not('[type="hidden"]')
            .filter(':enabled')
            .first();

        if (!$input.length && slug === 'method') {
            $input = $('input.shipping_method').first();
        }

        if (!$input.length && slug === 'schedule') {
            $input = $('#jckwds-delivery-date').first();
        }

        if ($input.length) {
            $input.trigger('focus');
        }
    }

    function scrollToStepErrors(slug) {
        var $box = getStepErrorsBox(slug);
        var $step = getStepElement(slug);
        var $invalid = $step.find('.woocommerce-invalid, .rd-field-error').first();
        var $target = $box.length && !$box.prop('hidden') ? $box : $invalid.length ? $invalid : $step;

        if (!$target.length) {
            return;
        }

        $('html, body').animate({ scrollTop: $target.offset().top - 96 }, 320);
        $box.addClass('rd-checkout-step__errors--pulse');
        window.setTimeout(function () {
            $box.removeClass('rd-checkout-step__errors--pulse');
        }, 900);
    }

    function shouldValidateCheckoutRow($row) {
        var $form = $('form.checkout.rd-express-checkout-form');

        if ($row.closest('.rd-checkout-shipping-block').length && $form.hasClass('rd-fulfilment-collection')) {
            return false;
        }

        if (
            $row.closest('.rd-checkout-billing-block').length &&
            $form.hasClass('rd-fulfilment-delivery') &&
            !$form.hasClass('rd-show-billing')
        ) {
            return false;
        }

        if ($row.closest('.hideText').length && $row.closest('.hideText').css('display') === 'none') {
            return false;
        }

        return true;
    }

    function clearMethodFieldHighlights() {
        $('#rd-checkout-step-method .rd-express-shipping-body').removeClass('rd-field-error');
        $('#rd-checkout-step-method .pickup-location-lookup')
            .closest('.form-row, .pickup-location-field, .pickup-location-lookup-field')
            .removeClass('woocommerce-invalid woocommerce-invalid-required-field');
        $('#jckwds-delivery-date-wrapper, .jckwds-delivery-date, #jckwds-delivery-date_field').removeClass(
            'woocommerce-invalid woocommerce-invalid-required-field'
        );
    }

    function resolveStepFromElement($el) {
        if (!$el || !$el.length) {
            return null;
        }

        var $container = $el.closest('[data-rd-step]');

        if ($container.length) {
            return $container.data('rd-step');
        }

        if ($el.closest('#payment').length) {
            return 'pay';
        }

        if ($el.closest('#customer_details, #rd-checkout-step-details').length) {
            return 'details';
        }

        if ($el.closest('#rd-checkout-step-schedule').length) {
            return 'schedule';
        }

        if ($el.closest('#rd-checkout-step-method').length) {
            return 'method';
        }

        var token = ($el.attr('id') || $el.attr('name') || '').toLowerCase();

        if (token.indexOf('jckwds') !== -1 || token.indexOf('wds') !== -1) {
            return 'schedule';
        }

        if (token.indexOf('shipping_method') !== -1 || token.indexOf('pickup') !== -1) {
            return 'method';
        }

        if (token === 'terms' || token.indexOf('payment_method') !== -1) {
            return 'pay';
        }

        if (
            token.indexOf('billing_') !== -1 ||
            token.indexOf('shipping_') !== -1 ||
            token.indexOf('custom_shipping') !== -1
        ) {
            return 'details';
        }

        return null;
    }

    function resolveStepFromMessage(message) {
        var text = (message || '').toLowerCase();

        if (
            text.indexOf('shipping method') !== -1 ||
            text.indexOf('pickup') !== -1 ||
            text.indexOf('collection location') !== -1
        ) {
            return 'method';
        }

        if (
            text.indexOf('delivery date') !== -1 ||
            text.indexOf('collection date') !== -1 ||
            text.indexOf('date is required') !== -1 ||
            text.indexOf('time slot') !== -1 ||
            text.indexOf('no dates') !== -1
        ) {
            return 'schedule';
        }

        if (text.indexOf('terms') !== -1 || text.indexOf('payment') !== -1) {
            return 'pay';
        }

        if (
            text.indexOf('billing') !== -1 ||
            text.indexOf('shipping') !== -1 ||
            text.indexOf('address') !== -1 ||
            text.indexOf('eircode') !== -1 ||
            text.indexOf('email') !== -1 ||
            text.indexOf('phone') !== -1
        ) {
            return 'details';
        }

        return null;
    }

    function collectCheckoutErrorMessages() {
        var messages = [];

        $('.woocommerce-NoticeGroup-checkout .woocommerce-error li').each(function () {
            var text = $.trim($(this).text());

            if (text) {
                messages.push(text);
            }
        });

        if (!messages.length) {
            $('.woocommerce-notices-wrapper .woocommerce-error li, form.checkout > .woocommerce-error li').each(function () {
                var text = $.trim($(this).text());

                if (text && messages.indexOf(text) === -1) {
                    messages.push(text);
                }
            });
        }

        return dedupeMessages(messages);
    }

    function focusCheckoutStep(slug) {
        var index = getWizardStepIndex(slug);

        if (index === -1) {
            return;
        }

        goToWizardStep(index, { force: true });
    }

    function collectMethodStepErrors(applyHighlights) {
        var messages = getWizardMessages();
        var errors = [];

        if (applyHighlights) {
            clearMethodFieldHighlights();
        }

        if (!$('input.shipping_method:checked').length) {
            if (applyHighlights) {
                $('#rd-checkout-step-method .rd-express-shipping-body').addClass('rd-field-error');
            }

            errors.push(messages.selectMethod || 'Choose delivery or collection.');
        }

        if (isPickupMethod(getChosenShippingMethod())) {
            var $pickup = getActivePickupSelect();
            var pickupVal = getResolvedPickupValue($pickup);

            if (!$pickup.length) {
                if (applyHighlights) {
                    $('#rd-checkout-step-method .rd-express-shipping-body').addClass('rd-field-error');
                }

                errors.push(messages.selectPickup || 'Choose a pickup location.');
                return errors;
            }

            if (!pickupVal && rememberedPickup) {
                restorePickupSelection();
                pickupVal = getResolvedPickupValue($pickup);
            }

            if (!pickupVal) {
                if (applyHighlights) {
                    $pickup
                        .closest('.form-row, .pickup-location-field, .pickup-location-lookup-field')
                        .addClass('woocommerce-invalid woocommerce-invalid-required-field');
                }

                errors.push(messages.selectPickup || 'Choose a pickup location.');
            }
        }

        return errors;
    }

    function collectScheduleStepErrors(applyHighlights) {
        var messages = getWizardMessages();
        var errors = [];

        if (!hasScheduleDatesAvailable()) {
            errors.push(
                messages.noScheduleDates ||
                    'No dates are available for this method. Try collection or change your delivery method.'
            );
        }

        var $date = $('#jckwds-delivery-date');

        if ($date.length && $date.prop('required') && !$date.val()) {
            if (applyHighlights) {
                $('#jckwds-delivery-date-wrapper, .jckwds-delivery-date, #jckwds-delivery-date_field').addClass(
                    'woocommerce-invalid woocommerce-invalid-required-field'
                );
            }

            errors.push(messages.selectDate || 'Choose a delivery or collection date.');
        }

        return errors;
    }

    function collectDetailsStepErrors(applyHighlights) {
        var $scope = $('#customer_details');
        var messages = getWizardMessages();
        var errors = [];

        if (applyHighlights) {
            $scope.find('.woocommerce-invalid').removeClass(
                'woocommerce-invalid woocommerce-invalid-required-field woocommerce-invalid-email woocommerce-invalid-phone'
            );
        }

        $scope.find('.validate-required').each(function () {
            var $row = $(this);

            if (!shouldValidateCheckoutRow($row)) {
                return;
            }

            var $input = $row.find('input.input-text, select, textarea').not('[type="hidden"]');

            if (!$input.length) {
                $input = $row.find('input:checkbox');
            }

            if (!$input.length) {
                return;
            }

            var isEmpty = $input.is(':checkbox') ? !$input.is(':checked') : !$.trim($input.val() || '');

            if (isEmpty) {
                if (applyHighlights) {
                    $row.addClass('woocommerce-invalid woocommerce-invalid-required-field');
                }

                errors.push((messages.fieldRequired || '%s is required.').replace('%s', getFieldLabel($row)));
            }
        });

        if (applyHighlights) {
            $scope.find('.input-text, select, textarea').each(function () {
                var $row = $(this).closest('.form-row, .validate-required');

                if ($row.length && shouldValidateCheckoutRow($row)) {
                    $(this).trigger('validate');
                }
            });
        }

        $scope.find('.woocommerce-invalid-email').each(function () {
            if (shouldValidateCheckoutRow($(this))) {
                errors.push(messages.invalidEmail || 'Enter a valid email address.');
            }
        });

        return dedupeMessages(errors);
    }

    function collectPaymentStepErrors(applyHighlights) {
        var messages = getWizardMessages();
        var errors = [];
        var $terms = $('#terms');

        if (applyHighlights) {
            $('#terms_field, #payment .form-row.validate-required').removeClass('woocommerce-invalid');
        }

        if ($terms.length && !$terms.is(':checked')) {
            if (applyHighlights) {
                $terms.closest('.form-row').addClass('woocommerce-invalid');
            }

            errors.push(messages.acceptTerms || 'Tick the box to accept the terms and conditions.');
        }

        return errors;
    }

    function getStepErrorsForSlug(slug, applyHighlights) {
        if (slug === 'method') {
            return collectMethodStepErrors(applyHighlights);
        }

        if (slug === 'schedule') {
            return collectScheduleStepErrors(applyHighlights);
        }

        if (slug === 'details') {
            return collectDetailsStepErrors(applyHighlights);
        }

        if (slug === 'pay') {
            return collectPaymentStepErrors(applyHighlights);
        }

        return [];
    }

    function findFirstStepWithErrors(applyHighlights) {
        var i;
        var slug;
        var errors;

        for (i = 0; i < wizardSteps.length; i++) {
            slug = wizardSteps[i];
            errors = getStepErrorsForSlug(slug, applyHighlights);

            if (errors.length) {
                return { slug: slug, errors: errors };
            }
        }

        return null;
    }

    function routeCheckoutErrors() {
        armCheckoutValidation();

        var messages = filterSpecificMessages(collectCheckoutErrorMessages());
        var $firstInvalid = $('form.checkout .woocommerce-invalid').first();
        var targetStep = resolveStepFromElement($firstInvalid);
        var stepMatch;
        var i;

        if (!targetStep && messages.length) {
            targetStep = resolveStepFromMessage(messages[0]);
        }

        if (!targetStep) {
            for (i = 0; i < wizardSteps.length; i++) {
                stepMatch = getStepErrorsForSlug(wizardSteps[i], true);

                if (stepMatch.length) {
                    targetStep = wizardSteps[i];
                    messages = dedupeMessages(messages.concat(stepMatch));
                    break;
                }
            }
        }

        if (!targetStep) {
            stepMatch = findFirstStepWithErrors(true);

            if (stepMatch) {
                targetStep = stepMatch.slug;
                messages = dedupeMessages(messages.concat(stepMatch.errors));
            }
        } else if (!messages.length) {
            messages = getStepErrorsForSlug(targetStep, true);
        } else {
            messages = dedupeMessages(messages.concat(getStepErrorsForSlug(targetStep, true)));
        }

        messages = filterSpecificMessages(messages);

        if (!messages.length || !targetStep) {
            return;
        }

        clearStepErrors();
        showStepErrors(targetStep, messages, { fromServer: true });
        focusCheckoutStep(targetStep);

        $('.woocommerce-NoticeGroup-checkout').hide();
        $('.woocommerce-notices-wrapper:has(.woocommerce-error)').hide();
    }

    function validateDetailsStep() {
        var errors = collectDetailsStepErrors(true);

        if (errors.length) {
            showStepErrors('details', errors);
            return false;
        }

        clearStepErrors('details');
        return true;
    }

    function validatePaymentStep() {
        var errors = collectPaymentStepErrors(true);

        if (errors.length) {
            showStepErrors('pay', errors);
            return false;
        }

        clearStepErrors('pay');
        return true;
    }

    function hasScheduleDatesAvailable() {
        var $fields = $('#jckwds-fields, .iconic-wds-fields').first();

        if (!$fields.length) {
            return true;
        }

        return !$fields.hasClass('iconic-wds-fields--has-error');
    }

    function updateScheduleUnavailableState() {
        var $panel = $('#rd-schedule-unavailable');
        var $fields = $('#jckwds-fields, .iconic-wds-fields').first();
        var unavailable = $fields.length && $fields.hasClass('iconic-wds-fields--has-error');

        if ($panel.length) {
            $panel.prop('hidden', !unavailable);
        }

        $('#rd-checkout-step-schedule .rd-checkout-step__continue').prop('disabled', unavailable);
    }

    function selectCollectionShipping() {
        var $pickup = $('input.shipping_method').filter(function () {
            return isPickupMethod($(this).val());
        }).first();

        if (!$pickup.length) {
            goToWizardStep(0, { force: true });
            return;
        }

        if (!$pickup.is(':checked')) {
            $pickup.prop('checked', true).trigger('change');
        }

        goToWizardStep(0, { force: true });
    }

    var deliveryDefaultApplied = false;

    function ensureDefaultDeliveryShipping() {
        if (deliveryDefaultApplied) {
            return;
        }

        deliveryDefaultApplied = true;

        var $checked = $('input.shipping_method:checked');

        if ($checked.length && !isPickupMethod($checked.val())) {
            return;
        }

        var $delivery = $('input.shipping_method').filter(function () {
            return !isPickupMethod($(this).val());
        }).first();

        if (!$delivery.length) {
            return;
        }

        if (!$delivery.is(':checked')) {
            $delivery.prop('checked', true).trigger('change');
        }
    }

    function syncMobilePayBarTotal() {
        var $amount = $('.rd-express-checkout__summary .order-total .amount').first();

        if (!$amount.length) {
            $amount = $('#order_review .order-total .amount').first();
        }

        if ($amount.length) {
            var amountHtml = $amount.html();
            var payLabel = config.mobilePayLabel || 'Place Order';

            $('.rd-mobile-pay-bar__amount').html(amountHtml);
            $('.rd-mobile-pay-bar__button').text(payLabel);
        }
    }

    function updateMobilePayBarVisibility() {
        var $bar = $('#rd-mobile-pay-bar');
        var isMobile = window.matchMedia('(max-width: 1023px)').matches;
        var showFromDetails = currentWizardStep >= 2;

        if (!$bar.length) {
            return;
        }

        $bar.prop('hidden', !(isMobile && showFromDetails));
        $('body').toggleClass('rd-has-mobile-pay-bar', isMobile && showFromDetails);
    }

    function handleMobilePayBarClick() {
        if (!goToPayment()) {
            return;
        }

        if (!validatePaymentStep()) {
            return;
        }

        var $placeOrder = $('#place_order');

        if ($placeOrder.length && !$placeOrder.prop('disabled')) {
            $placeOrder.trigger('click');
        }
    }

    function updateScheduleDateLabel() {
        var isPickup = isPickupMethod(getChosenShippingMethod());
        var dateLabel = isPickup ? 'Collection Date' : 'Delivery Date';
        var $label = $('label[for="jckwds-delivery-date"], .jckwds-delivery-date > label, #jckwds-delivery-date-wrapper label').first();

        if (window.jckwds && typeof window.jckwds.update_checkout_field_labels === 'function') {
            window.jckwds.update_checkout_field_labels({ date: dateLabel });
        }

        if (!$label.length) {
            return;
        }

        var $required = $label.find('.required').detach();
        $label.text(dateLabel + '\u00a0');

        if ($required.length) {
            $label.append($required);
        }

        $('#jckwds-delivery-date').attr('aria-label', dateLabel);
    }

    function getWizardStepIndex(slug) {
        return wizardSteps.indexOf(slug);
    }

    function getMethodSummaryText() {
        var $checked = $('input.shipping_method:checked');
        var summary = '';

        if ($checked.length) {
            var $label = $checked.closest('li').find('label').first();
            summary = $.trim($label.text().replace(/\s+/g, ' '));
        }

        if (isPickupMethod(getChosenShippingMethod()) && pickupUserSelected) {
            var $pickup = getActivePickupSelect();
            var pickupText = '';

            if ($pickup.length) {
                pickupText = $pickup.find('option:selected').text() || '';
                pickupText = $.trim(pickupText.replace(/\s+/g, ' '));
            }

            if (pickupText && pickupText.toLowerCase().indexOf('select') === -1) {
                summary = summary ? summary + ' · ' + pickupText : pickupText;
            }
        }

        return summary;
    }

    function getScheduleSummaryText() {
        var date = $.trim($('#jckwds-delivery-date').val() || '');
        var time = getScheduleTimeSummaryText();
        var parts = [];

        if (date) {
            parts.push(date);
        }

        if (time) {
            parts.push(time);
        }

        return parts.join(' · ');
    }

    function getPickupLocationSummaryText() {
        if (!isPickupMethod(getChosenShippingMethod()) || !pickupUserSelected) {
            return '';
        }

        var $pickup = getActivePickupSelect();

        if (!$pickup.length) {
            return '';
        }

        var $option = $pickup.find('option:selected');
        var name = $.trim(String($option.data('name') || ''));

        if (!name) {
            name = $.trim($option.text() || '');
        }

        if (!name || /select|search/i.test(name)) {
            return '';
        }

        return name.replace(/\s+/g, ' ');
    }

    function stripRateLabelSuffix(label) {
        var cleaned = String(label || '').replace(/\s*\([^)]*\)\s*/g, ' ').replace(/\s+/g, ' ').trim();

        return cleaned || String(label || '').trim();
    }

    function getDeliveryEircodeSummaryText() {
        if (isPickupMethod(getChosenShippingMethod())) {
            return '';
        }

        var eircode = $.trim($('#custom_shipping_eircode').val() || '');

        if (!eircode) {
            eircode = $.trim($('#shipping_postcode').val() || '');
        }

        if (!eircode) {
            eircode = $.trim($('#billing_postcode').val() || '');
        }

        if (!eircode || eircode === DEFAULT_SEED_EIRCODE) {
            return '';
        }

        return eircode.replace(/\s+/g, ' ');
    }

    function formatDeliveryMethodSummaryText(rateLabel) {
        var baseLabel = stripRateLabelSuffix(rateLabel);
        var eircode = getDeliveryEircodeSummaryText();

        if (eircode) {
            return baseLabel + ' (' + eircode + ')';
        }

        return baseLabel;
    }

    function getFulfilmentLocationSummaryText() {
        if (!isPickupMethod(getChosenShippingMethod())) {
            return '';
        }

        return getPickupLocationSummaryText();
    }

    function updateFulfilmentMethodSummary() {
        var $method = $('.rd-fulfilment-summary__method');

        if (!$method.length) {
            return;
        }

        if (isPickupMethod(getChosenShippingMethod())) {
            $method.text('Collection');
            return;
        }

        var $checked = $('input.shipping_method:checked');

        if (!$checked.length) {
            return;
        }

        var $label = $checked.closest('li').find('label').first();
        var summary = formatDeliveryMethodSummaryText($.trim($label.text().replace(/\s+/g, ' ')));

        if (summary) {
            $method.text(summary);
        }
    }

    function getScheduleTimeSummaryText() {
        var $timeSelect = $('#jckwds-delivery-time');

        if (!$timeSelect.length) {
            return '';
        }

        var time = $.trim($timeSelect.find('option:selected').text() || '');

        if (!time || /choose|select/i.test(time)) {
            return '';
        }

        return time;
    }

    function updateFulfilmentScheduleSummary() {
        var $location = $('.rd-fulfilment-summary__location');
        var $schedule = $('.rd-fulfilment-summary__schedule');

        updateFulfilmentMethodSummary();

        if ($location.length) {
            var detailText = getFulfilmentLocationSummaryText();

            if (detailText) {
                $location.text(detailText).prop('hidden', false);
            } else {
                $location.text('').prop('hidden', true);
            }
        }

        if (!$schedule.length) {
            return;
        }

        var summary = getScheduleSummaryText();

        if (summary) {
            $schedule.text(summary).prop('hidden', false);
        } else {
            $schedule.text('').prop('hidden', true);
        }
    }

    function getDetailsSummaryText() {
        var isPickup = isPickupMethod(getChosenShippingMethod());
        var firstName = isPickup ? $('#billing_first_name').val() : $('#shipping_first_name').val();
        var city = isPickup ? $('#billing_city').val() : $('#shipping_city').val();
        var parts = [];

        firstName = $.trim(firstName || '');
        city = $.trim(city || '');

        if (firstName) {
            parts.push(firstName);
        }

        if (city) {
            parts.push(city);
        }

        return parts.join(', ');
    }

    function updateWizardSummaries() {
        $('[data-rd-summary="method"]').text(getMethodSummaryText());
        $('[data-rd-summary="schedule"]').text(getScheduleSummaryText());
        $('[data-rd-summary="details"]').text(getDetailsSummaryText());
        updateFulfilmentScheduleSummary();
    }

    function refreshWizardStepStates() {
        var $form = $('form.checkout.rd-express-checkout-form');

        if (!$form.length) {
            return;
        }

        wizardSteps.forEach(function (slug, index) {
            var $step = $('[data-rd-step="' + slug + '"]');
            var isActive = index === currentWizardStep;
            var isComplete = index < currentWizardStep;
            var isUpcoming = index > currentWizardStep;

            $step.toggleClass('rd-checkout-step--active', isActive);
            $step.toggleClass('rd-checkout-step--collapsed', isComplete);
            $step.toggleClass('rd-checkout-step--upcoming', isUpcoming);
            $step.find('.rd-checkout-step__body').prop('hidden', !isActive);
            $step.find('.rd-checkout-step__change').prop('hidden', !isComplete);
        });

        $('.rd-checkout-progress__item').each(function () {
            var $item = $(this);
            var slug = $item.data('rd-progress');
            var index = getWizardStepIndex(slug);
            var $trigger = $item.find('.rd-checkout-progress__trigger');

            if (index === -1) {
                return;
            }

            $item.toggleClass('rd-checkout-progress__item--active', index === currentWizardStep);
            $item.toggleClass('rd-checkout-progress__item--complete', index < currentWizardStep);

            if ($trigger.length) {
                var canNavigate = index <= currentWizardStep;
                $trigger.prop('disabled', !canNavigate);
                $trigger.attr('aria-current', index === currentWizardStep ? 'step' : null);
            }
        });

        updateWizardSummaries();
        updateScheduleUnavailableState();
        updateMobilePayBarVisibility();

        if (wizardSteps[currentWizardStep] === 'pay') {
            ensureStripeExpressCheckoutVisible();
        }
    }

    function goToWizardStep(index, options) {
        options = options || {};

        if (index < 0 || index >= wizardSteps.length) {
            return;
        }

        if (!options.force && index > currentWizardStep + 1) {
            return;
        }

        currentWizardStep = index;

        if (wizardSteps[index] !== 'method') {
            closePickupLocationDropdowns();
        }

        if (wizardSteps[index] !== 'pay') {
            stripeExpressCheckoutPayStepRefreshed = false;
        }

        refreshWizardStepStates();
        syncMobileOrderSummaryPlacement();
        applyOrderSummaryState();

        if (wizardSteps[index] === 'pay') {
            clearCheckoutBlockUi();
        }

        if (options.scroll !== false) {
            var $target = $('[data-rd-step="' + wizardSteps[index] + '"]');

            if ($target.length) {
                $('html, body').animate({ scrollTop: $target.offset().top - 24 }, 250);
            }
        }

        if (options.focus !== false) {
            window.setTimeout(function () {
                var $heading = $('[data-rd-step="' + wizardSteps[index] + '"]')
                    .find('.rd-checkout-step__title')
                    .first();

                if ($heading.length) {
                    $heading.attr('tabindex', '-1').trigger('focus');
                }
            }, options.scroll === false ? 0 : 280);
        }
    }

    // Auto-advance a single-choice step (Method = index 0, Schedule = index 1)
    // once it validates. Deliberately silent: it never applies error highlights,
    // so an as-yet-incomplete step (collection chosen but no location picked, a
    // date not yet selected, or no dates available) is simply a no-op and the
    // customer keeps using the step until it's satisfied. Only advances forward
    // and only from the step the customer is currently on, so a programmatic
    // refresh or a tweak to an already-completed step can't yank them forward.
    function maybeAutoAdvanceStep(index) {
        if (!autoAdvanceEnabled) {
            return;
        }

        // Only the single-choice steps auto-advance; Details is left manual.
        if (index !== 0 && index !== 1) {
            return;
        }

        if (currentWizardStep !== index) {
            return;
        }

        var slug = wizardSteps[index];

        if (getStepErrorsForSlug(slug, false).length) {
            return;
        }

        clearStepErrors(slug);
        goToWizardStep(index + 1);
    }

    function scrollToPayment() {
        var $step = getStepElement('pay');

        if ($step.length) {
            $('html, body').animate({ scrollTop: $step.offset().top - 96 }, 320);
        }
    }

    function goToPayment(options) {
        options = options || {};
        var i;
        var payIndex = getWizardStepIndex('pay');

        armCheckoutValidation();

        for (i = 0; i < payIndex; i++) {
            if (!validateWizardStep(i)) {
                goToWizardStep(i, { force: true });
                return false;
            }
        }

        goToWizardStep(payIndex, { force: true, scroll: options.scroll !== false });

        ensureStripeExpressCheckoutVisible();

        if (options.scroll !== false) {
            window.setTimeout(focusPaymentCardField, 360);
        }

        return true;
    }

    // Move focus into the Stripe card field once the customer reaches payment.
    // Stripe mounts the card inputs inside a cross-origin iframe, so focusing the
    // iframe element hands focus to the Payment Element, which places the caret in
    // its first field (card number). The iframe can still be (re)initialising just
    // after an updated_checkout refresh, so retry until it's present.
    function focusPaymentCardField() {
        var $payment = $('#payment');

        if (!$payment.length) {
            return;
        }

        ensureStripePaymentVisible();

        var $cardRadio = $payment.find('input[name="payment_method"]').filter(':visible').first();
        if ($cardRadio.length && !$cardRadio.is(':checked')) {
            $cardRadio.prop('checked', true).trigger('click');
        }

        var attempts = 0;
        (function tryFocus() {
            var iframe = $payment.find('iframe').filter(function () {
                return /__privateStripeFrame/.test(this.name || '');
            }).get(0) || $payment.find('iframe').get(0);

            if (iframe) {
                try {
                    // preventScroll so focusing doesn't fight the scroll-to-payment
                    // animation (ignored gracefully by older browsers).
                    iframe.focus({ preventScroll: true });
                } catch (e) {
                    try {
                        iframe.focus();
                    } catch (err) {}
                }
                return;
            }

            if (attempts++ < 10) {
                window.setTimeout(tryFocus, 150);
            }
        })();
    }

    function validateWizardStep(index) {
        var slug = wizardSteps[index];
        var stepErrors = getStepErrorsForSlug(slug, true);

        armCheckoutValidation();

        if (stepErrors.length) {
            showStepErrors(slug, stepErrors);
            return false;
        }

        clearStepErrors(slug);
        return true;
    }

    function initCheckoutWizard() {
        if (wizardReady || !wizardSteps.length || !$('[data-rd-step]').length) {
            return;
        }

        wizardReady = true;
        currentWizardStep = 0;
        refreshWizardStepStates();
    }

    function bindCheckoutWizardEvents() {
        $(document).on(
            'click',
            '.rd-checkout-step__continue, .rd-checkout-progress__trigger[data-rd-progress="pay"], .rd-mobile-pay-bar__button, #place_order',
            function () {
                armCheckoutValidation();
            }
        );

        $(document).on('click', '.rd-checkout-step__continue:not(.rd-checkout-step__continue--pay)', function (event) {
            event.preventDefault();

            var $step = $(this).closest('[data-rd-step]');
            var slug = $step.data('rd-step');
            var index = getWizardStepIndex(slug);

            if (index === -1 || !validateWizardStep(index)) {
                return;
            }

            goToWizardStep(index + 1);
        });

        $(document).on('click', '.rd-checkout-step__continue--pay', function (event) {
            event.preventDefault();
            goToPayment();
        });

        $(document).on('click', '.rd-checkout-step__change, .rd-checkout-step__back', function (event) {
            event.preventDefault();

            var slug = $(this).data('rd-goto');

            if (!slug) {
                return;
            }

            goToWizardStep(getWizardStepIndex(slug), { force: true });
        });

        $(document).on('click', '.rd-checkout-progress__trigger:not(:disabled)', function (event) {
            event.preventDefault();

            var slug = $(this).data('rd-progress');
            var index = getWizardStepIndex(slug);

            if (index === -1 || index > currentWizardStep) {
                return;
            }

            if (slug === 'pay') {
                goToPayment();
                return;
            }

            goToWizardStep(index, { force: true });
        });

        $(document).on(
            'change input',
            '#rd-checkout-step-method input, #rd-checkout-step-method select, #rd-checkout-step-schedule input, #customer_details input, #customer_details select, #custom_shipping_eircode',
            function () {
                var $step = $(this).closest('[data-rd-step]');

                if ($step.length) {
                    clearStepErrors($step.data('rd-step'));
                }

                updateWizardSummaries();
            }
        );

        $(document).on('change', '#terms', function () {
            clearStepErrors('pay');
        });

        $(document).on('click', '.rd-schedule-unavailable__try-collection', function (event) {
            event.preventDefault();
            selectCollectionShipping();
        });

        $(document).on('click', '.rd-schedule-unavailable__change-method', function (event) {
            event.preventDefault();
            goToWizardStep(0, { force: true });
        });

        $(document).on('click', '.rd-mobile-pay-bar__button', function (event) {
            event.preventDefault();
            handleMobilePayBarClick();
        });

        $(document).on('click', '#place_order', function (event) {
            if (!goToPayment({ scroll: false })) {
                event.preventDefault();
                return false;
            }

            if (!validatePaymentStep()) {
                event.preventDefault();
                scrollToPayment();
                return false;
            }
        });
    }

    function ensureStripePaymentVisible() {
        var $payment = $('#payment.woocommerce-checkout-payment');

        if (!$payment.length) {
            return;
        }

        $payment.show();
        $payment.find('.payment_box.payment_method_stripe').show();
        $payment.find('.wc-stripe-upe-element, .wc-upe-form').show();
    }

    var stripeExpressCheckoutRefreshQueued = false;
    var stripeExpressCheckoutPayStepRefreshed = false;

    // Apple Pay / Google Pay mount inside a hidden wizard step until the customer
    // reaches Payment. Refresh checkout once when that step opens so Stripe can
    // measure the container and render the wallet buttons.
    function ensureStripeExpressCheckoutVisible() {
        var payIndex = getWizardStepIndex('pay');

        if (payIndex === -1 || currentWizardStep !== payIndex) {
            return;
        }

        var $ece = $('#wc-stripe-express-checkout-element');
        var $separator = $('#wc-stripe-express-checkout-button-separator');
        var $payBody = $('#rd-checkout-step-pay .rd-checkout-step__body');

        if ($ece.length && $payBody.length && !$ece.closest('#rd-checkout-step-pay').length) {
            $ece.prependTo($payBody);
            if ($separator.length) {
                $separator.insertAfter($ece);
            }
        }

        if (stripeExpressCheckoutRefreshQueued || stripeExpressCheckoutPayStepRefreshed) {
            return;
        }

        stripeExpressCheckoutRefreshQueued = true;
        stripeExpressCheckoutPayStepRefreshed = true;

        window.setTimeout(function () {
            stripeExpressCheckoutRefreshQueued = false;
            $(document.body).trigger('update_checkout');
        }, 120);
    }

    function refreshExpressCheckout() {
        decorateShippingOptions();
        applyFulfilmentMode();
        ensureDefaultCountries();
        updateScheduleDateLabel();
        syncBillingFromShippingForStripe();
        syncCheckoutAddressAutofill();
        ensureStripePaymentVisible();
        ensureStripeExpressCheckoutVisible();
        updateScheduleUnavailableState();
        syncMobilePayBarTotal();
        refreshWizardStepStates();
        schedulePickupWarmup();
    }

    // Show each pickup location's address beneath its name in the dropdown.
    // Local Pickup Plus renders a second <small> line from each option's
    // data-address; we enrich it with "street, city, postcode" from the
    // localised address map so the dropdown matches the legacy display.
    function applyPickupAddresses() {
        var addresses = config.pickupAddresses || {};

        $('#rd-checkout-step-method select.pickup-location-lookup option').each(function () {
            var $option = $(this);
            var value = $option.attr('value');

            if (isEmptyPickupOption($option) || $option.attr('data-placeholder') === 'true') {
                return;
            }

            if (!value || !addresses[value]) {
                return;
            }

            $option.attr('data-address', addresses[value]);
            $option.data('address', addresses[value]);

            if (!$option.data('name')) {
                $option.data('name', $.trim($option.text()));
            }
        });
    }

    bindCheckoutWizardEvents();

    $(document.body).on('update_checkout', function () {
        var $li = $('#rd-checkout-step-method .rd-pickup-pending').closest('li');

        if (!$li.length && isPickupMethod(getChosenShippingMethod())) {
            $li = $('#rd-checkout-step-method input.shipping_method:checked').closest('li');
        }

        if (!$li.length || !isPickupMethod($li.find('input.shipping_method').first().val())) {
            return;
        }

        var $select = $li.find('select.pickup-location-lookup').first();

        if (pickupLocationsReady || isPickupFieldPopulated($select)) {
            return;
        }

        getPickupWrapForLi($li, false).addClass('rd-pickup-pending');
        setPickupLocationLoading(true, $li);
    });

    $(document.body).on('updated_checkout', function () {
        clearCheckoutBlockUi();
        window.setTimeout(clearCheckoutBlockUi, 0);
        refreshExpressCheckout();
        updateScheduleDateLabel();
        syncMobilePayBarTotal();
        applyPickupAddresses();
        refreshPickupUi();
        window.setTimeout(function () {
            var $li = $('#rd-checkout-step-method input.shipping_method:checked').closest('li');

            if (!$li.length) {
                $li = getPickupOptionLi();
            }

            if ($li.length) {
                consolidatePickupLocationFields($li);
            }

            syncPickupLocationLoading($li.length ? $li : null);
        }, 0);
        updateFulfilmentScheduleSummary();

        if (typeof window.rdRefreshBoxContentsScroll === 'function') {
            window.setTimeout(window.rdRefreshBoxContentsScroll, 0);
        }
    });

    // Remember every genuine pickup-location choice so a later order-review
    // refresh that blanks the field can't lose it (see restorePickupSelection).
    $(document).on('select2:select', '#rd-checkout-step-method select.pickup-location-lookup', function (event) {
        if (pickupRestoreGuard) {
            return;
        }

        var $select = $(this);

        if ($select[0] !== getActivePickupSelect()[0]) {
            return;
        }

        var value = $select.val();

        if (!isValidPickupSelection(value)) {
            return;
        }

        // Accept deliberate user picks (mouse/keyboard). Ignore LPP auto-selects
        // that fire without an originating DOM event.
        if (isGenuinePickupSelectEvent(event)) {
            recordPickupUserSelection(value, $select);
        }
    });

    $(document).on('change', '#rd-checkout-step-method select.pickup-location-lookup', function (event) {
        if (pickupRestoreGuard || (event && event.isTrigger)) {
            return;
        }

        var $select = $(this);

        if ($select[0] !== getActivePickupSelect()[0]) {
            return;
        }

        recordPickupUserSelection($select.val(), $select);
    });

    // Auto-advance off the Method step when a pickup location is chosen. Bound to
    // the genuine user-selection event (select2:select) only — never the generic
    // `change` — so programmatic restores/order-review refreshes can't push the
    // customer forward. Guarded against restores for belt-and-braces.
    $(document).on('select2:select', '#rd-checkout-step-method select.pickup-location-lookup', function (event) {
        if (
            pickupRestoreGuard ||
            !isGenuinePickupSelectEvent(event) ||
            !isValidPickupSelection($(this).val())
        ) {
            return;
        }

        window.setTimeout(function () {
            maybeAutoAdvanceStep(0);
        }, 0);
    });

    // Mobile Select2 can commit the first option from the same tap that opens the
    // menu. Block only that spurious open-tap selection — not deliberate option
    // clicks (mouseup/click), which must always be allowed through.
    $(document).on('select2:selecting', '#rd-checkout-step-method select.pickup-location-lookup', function (event) {
        if (pickupRestoreGuard || pickupUserSelected) {
            return;
        }

        if (!pickupOpenGuardUntil || Date.now() >= pickupOpenGuardUntil) {
            return;
        }

        var original = event.params && event.params.originalEvent;

        if (!original || original.type === 'mouseup' || original.type === 'click') {
            return;
        }

        event.preventDefault();
    });

    // Guarantee the menu is anchored at the instant it opens, regardless of when
    // LPP last (re-)initialised its picker. If it's still body-parented we cancel
    // this open and, on the next tick (so selectWoo's in-progress open() unwinds
    // first — destroying mid-open throws a null "query" error), re-init it
    // anchored to the field wrapper and re-open. The result drops directly beneath
    // the input instead of flipping above it.
    $(document).on('select2:opening', '#rd-checkout-step-method select.pickup-location-lookup', function (event) {
        var $select = $(this);
        var $wrap = $select.closest('.pickup-location-field');

        pickupOpenGuardUntil = Date.now() + 200;

        if (!pickupUserSelected && isValidPickupSelection($select.val())) {
            pickupRestoreGuard = true;
            clearPickupSelectValue($select);
            window.setTimeout(function () {
                pickupRestoreGuard = false;
            }, 150);
        }

        if (!$wrap.length || pickupDropdownAnchored($select, $wrap)) {
            return;
        }

        event.preventDefault();

        window.setTimeout(function () {
            anchorPickupSelect($select);
            $select.select2('open');
        }, 0);
    });

    // Mobile accordion only — desktop order details are always expanded.
    $(document).on('click', '.rd-order-summary__bar', function (event) {
        if (isDesktopOrderSummary()) {
            return;
        }

        var $summary = $(this).closest('.rd-order-summary');

        $summary.toggleClass('rd-order-summary--collapsed');

        var isOpen = !$summary.hasClass('rd-order-summary--collapsed');

        orderSummaryUserState = isOpen;
        $(this).attr('aria-expanded', isOpen ? 'true' : 'false');
    });

    $(document.body).on('update_checkout', rememberOrderSummaryOpenState);

    // Re-open after WooCommerce swaps the order review fragment (deferred so we
    // run after LPP/pickup handlers in the same updated_checkout turn).
    $(document.body).on('updated_checkout', function () {
        window.setTimeout(applyOrderSummaryState, 0);
    });

    $(document.body).on('checkout_error', function () {
        window.setTimeout(routeCheckoutErrors, 0);
    });

    $(document.body).on('wc_local_pickup_plus_ready wc_local_pickup_plus_after_locations_html', function () {
        decorateShippingOptions();
        syncPickupVisibility();
        updateWizardSummaries();
        applyPickupAddresses();
        refreshPickupUi();
        window.setTimeout(function () {
            prepareVisiblePickupSelects();
            enforcePickupRequiresUserChoice();
            cachePickupFieldSnapshot();

            var $li = $('#rd-checkout-step-method input.shipping_method:checked').closest('li');

            if (!$li.length) {
                $li = getPickupOptionLi();
            }

            if ($li.length) {
                consolidatePickupLocationFields($li);
                markPickupLocationsReady($li);
            }

            syncPickupLocationLoading($li.length ? $li : null);
        }, 0);
    });
    $(document).on(
        'change blur',
        '#customer_details input, #customer_details select, #custom_shipping_eircode, #billing_state, #shipping_state, #billing_postcode, #shipping_postcode',
        function () {
            syncBillingFromShippingForStripe();
            syncCheckoutAddressAutofill();
        }
    );
    $(document).on('input change', '#custom_shipping_eircode', function () {
        $(this).data('rd-user-touched', true);
        syncCustomEircodeToPostcodes();
        updateFulfilmentScheduleSummary();
    });
    $(document).on(
        'focus',
        '#custom_shipping_eircode, #customer_details input.input-text',
        function () {
            placeCaretAtEndIfAutofilledAtStart(this);
        }
    );
    $(document).on('change', 'input.shipping_method', applyFulfilmentMode);
    $(document).on(
        'mousedown',
        '#rd-checkout-step-method .rd-shipping-option input.shipping_method, #rd-checkout-step-method .rd-shipping-option label',
        function () {
            var $radio = $(this).is('input.shipping_method')
                ? $(this)
                : $('#' + ($(this).attr('for') || ''));

            if (!$radio.length || !isPickupMethod($radio.val())) {
                return;
            }

            showPickupLocationOptimistically($radio.closest('li'));
        }
    );
    // Auto-advance off the Method step once a choice is made. Delivery validates
    // immediately, so it jumps to the Date step; collection needs a pickup
    // location first, so it's a silent no-op here until that's chosen (handled by
    // the pickup-location handler below). Deferred so applyFulfilmentMode and any
    // Local Pickup Plus DOM work settle before we evaluate validity.
    $(document).on('change', 'input.shipping_method', function () {
        var methodId = $(this).val();

        resetPickupLocationsReady();

        if (!isPickupMethod(methodId)) {
            setPickupLocationLoading(false);
        }

        window.setTimeout(function () {
            if (isPickupMethod(methodId)) {
                enforcePickupRequiresUserChoice();
                return;
            }

            maybeAutoAdvanceStep(0);
        }, 0);
    });
    $(document).on('change', '#rd-bill-different-address-checkbox', function () {
        var $form = $('form.checkout.rd-express-checkout-form');
        if (!$form.hasClass('rd-fulfilment-delivery')) {
            return;
        }

        var showBilling = $(this).is(':checked');
        $form.toggleClass('rd-show-billing', showBilling);
        ensureDeliveryLayout();

        if (!showBilling) {
            syncBillingFromShippingForStripe();
        }
    });
    function openScheduleDatePicker() {
        var $date = $('#jckwds-delivery-date');

        if (!$date.length || $date.is(':disabled') || !$date.is(':visible')) {
            return;
        }

        $date.trigger('focus');

        if ($date.hasClass('hasDatepicker') && typeof $date.datepicker === 'function') {
            $date.datepicker('show');
        }
    }

    // Iconic binds the datepicker to the input only; theme pill styling and blockUI
    // overlays can swallow clicks on the placeholder / label / wrapper area.
    $(document).on('click', '#rd-checkout-step-schedule #jckwds-delivery-date-wrapper, #rd-checkout-step-schedule #jckwds-delivery-date_field, #rd-checkout-step-schedule label[for="jckwds-delivery-date"]', function (event) {
        if ($(event.target).closest('#jckwds-delivery-time, #jckwds_timeslot_field').length) {
            return;
        }

        event.preventDefault();
        openScheduleDatePicker();
    });

    // Auto-advance off the Date step once a date is chosen. The Iconic datepicker
    // sets #jckwds-delivery-date and fires change; deferred so its value (and any
    // updated_checkout refresh it kicks off) settles before we validate. When no
    // dates are available the schedule step reports an error, so this stays a
    // silent no-op and the customer sees the "no dates" panel instead.
    $(document).on('change', '#jckwds-delivery-date', function () {
        window.setTimeout(function () {
            maybeAutoAdvanceStep(1);
            updateFulfilmentScheduleSummary();
        }, 0);
    });

    $(document).on('change', '#jckwds-delivery-time', function () {
        window.setTimeout(updateFulfilmentScheduleSummary, 0);
    });

    function handleCheckoutResize() {
        updateMobilePayBarVisibility();
        syncMobileOrderSummaryPlacement();
        applyOrderSummaryState();
    }

    $(window).on('resize', handleCheckoutResize);

    $(window).on('load', function () {
        initCheckoutWizard();
        ensureDefaultDeliveryShipping();
        ensureDefaultCountries();
        refreshExpressCheckout();

        if (filterSpecificMessages(collectCheckoutErrorMessages()).length) {
            routeCheckoutErrors();
        }
    });
    $(function () {
        initCheckoutWizard();
        ensureDefaultDeliveryShipping();
        ensureDefaultCountries();
        refreshExpressCheckout();
        applyPickupAddresses();
        refreshPickupUi();
        applyOrderSummaryState();
    });
})(jQuery);
