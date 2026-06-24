(function ($) {
    'use strict';

    var config = window.matrixRdExpressCheckout || {};
    var pickupMethodId = config.pickupMethodId || 'local_pickup_plus';
    var wizardSteps = ['method', 'schedule', 'details'];
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
    // Phase 1 auto-advance: the Method and Date steps are single-choice, so once
    // a valid choice is made we move the customer to the next step automatically.
    // The Continue buttons stay as a manual fallback. Can be disabled by the
    // server via `autoAdvance: false`.
    var autoAdvanceEnabled = config.autoAdvance !== false;

    function orderSummaryDefaultOpen() {
        // Collapsed by default on all devices; users expand to see line items.
        return false;
    }

    function applyOrderSummaryState() {
        var $summary = $('.rd-order-summary');

        if (!$summary.length) {
            return;
        }

        var shouldOpen = orderSummaryUserState === null ? orderSummaryDefaultOpen() : orderSummaryUserState;
        $summary.prop('open', shouldOpen);
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
                refreshPickupSelect2($li);
            }
        });
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

        if (pickupDropdownAnchored($select, $wrap)) {
            return;
        }

        var value = $select.val();
        var reinit = $.extend({}, instance.options.options, { dropdownParent: $wrap, width: '100%' });

        $wrap.css('position', 'relative');
        $select.select2('destroy');
        $select.select2(reinit);

        if (value) {
            $select.val(value).trigger('change.select2');
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

        var $select = $('#rd-checkout-step-method select.pickup-location-lookup');

        if (!$select.length || $select.val()) {
            return;
        }

        if (!$select.find('option[value="' + rememberedPickup + '"]').length) {
            return;
        }

        pickupRestoreGuard = true;
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
        window.setTimeout(restorePickupSelection, 0);
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

            if ($shipping.length && $billing.length && $billing.val() !== shippingVal) {
                $billing.val(shippingVal);
            }
        });

        syncCustomEircodeToPostcodes();

        syncingBillingFromShipping = false;
    }

    function syncCustomEircodeToPostcodes() {
        var $customEircode = $('#custom_shipping_eircode');

        if (!$customEircode.length || !$customEircode.val()) {
            return;
        }

        $('#billing_postcode, #shipping_postcode').val($customEircode.val());
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
        if (slug === 'pay') {
            return $('#payment .rd-checkout-step__errors');
        }

        return getStepElement(slug).find('.rd-checkout-step__errors');
    }

    function clearStepErrors(slug) {
        if (slug) {
            getStepErrorsBox(slug).empty().prop('hidden', true);

            if (slug === 'pay') {
                $('#payment').removeClass('rd-checkout-step--has-error');
            } else {
                getStepElement(slug).removeClass('rd-checkout-step--has-error');
            }

            return;
        }

        $('.rd-checkout-step__errors').empty().prop('hidden', true);
        $('.rd-checkout-step').removeClass('rd-checkout-step--has-error');
        $('#rd-checkout-step-method .rd-express-shipping-body').removeClass('rd-field-error');
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
        var $step = slug === 'pay' ? $('#payment') : getStepElement(slug);
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

        if (options.scroll !== false) {
            window.setTimeout(function () {
                scrollToStepErrors(slug);
                focusFirstInvalidInStep(slug);
            }, 80);
        }
    }

    function focusFirstInvalidInStep(slug) {
        var $step = slug === 'pay' ? $('#payment') : getStepElement(slug);
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
        var $step = slug === 'pay' ? $('#payment') : getStepElement(slug);
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
        if (slug === 'pay') {
            scrollToPayment();
            return;
        }

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
            var $pickup = $('#rd-checkout-step-method select.pickup-location-lookup');
            var pickupVal = $pickup.length ? $pickup.val() : '';

            // A still-in-flight order-review refresh can momentarily blank the
            // <select>. Fall back to (and re-apply) the customer's remembered
            // choice so the wizard doesn't block them over a transient reset —
            // LPP reads the posted value at submit, so a populated field is valid.
            if (!pickupVal && rememberedPickup) {
                restorePickupSelection();
                pickupVal = $pickup.val() || rememberedPickup;
            }

            if ($pickup.length && !pickupVal) {
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

        errors = getStepErrorsForSlug('pay', applyHighlights);

        if (errors.length) {
            return { slug: 'pay', errors: errors };
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

    function syncMobilePayBarTotal() {
        var $amount = $('.rd-express-checkout__summary .order-total .amount').first();

        if (!$amount.length) {
            $amount = $('#order_review .order-total .amount').first();
        }

        if ($amount.length) {
            var amountHtml = $amount.html();
            var payLabel = config.mobilePayLabel || 'Pay';

            $('.rd-mobile-pay-bar__amount').html(amountHtml);
            $('.rd-mobile-pay-bar__button').text(payLabel + ' \u00b7 ' + $.trim($amount.text()));
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

        if (isPickupMethod(getChosenShippingMethod())) {
            var $pickup = $('#rd-checkout-step-method select.pickup-location-lookup');
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
        return $.trim($('#jckwds-delivery-date').val() || '');
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

            if (slug === 'pay') {
                $item.toggleClass('rd-checkout-progress__item--active', currentWizardStep >= wizardSteps.length - 1);
                $item.toggleClass('rd-checkout-progress__item--complete', currentWizardStep >= wizardSteps.length - 1);
                return;
            }

            var index = getWizardStepIndex(slug);

            if (index === -1) {
                return;
            }

            $item.toggleClass('rd-checkout-progress__item--active', index === currentWizardStep);
            $item.toggleClass('rd-checkout-progress__item--complete', index < currentWizardStep);
        });

        updateWizardSummaries();
        updateScheduleUnavailableState();
        updateMobilePayBarVisibility();
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
        refreshWizardStepStates();

        if (options.scroll !== false) {
            var $target = $('[data-rd-step="' + wizardSteps[index] + '"]');

            if ($target.length) {
                $('html, body').animate({ scrollTop: $target.offset().top - 24 }, 250);
            }
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
        var $payment = $('#payment.woocommerce-checkout-payment, #payment').first();

        if ($payment.length) {
            $('html, body').animate({ scrollTop: $payment.offset().top - 96 }, 320);
            return;
        }

        var $summary = $('.rd-express-checkout__summary');

        if ($summary.length) {
            $('html, body').animate({ scrollTop: $summary.offset().top - 96 }, 320);
        }
    }

    function goToPayment(options) {
        options = options || {};
        var i;

        armCheckoutValidation();

        for (i = 0; i < wizardSteps.length; i++) {
            if (!validateWizardStep(i)) {
                goToWizardStep(i, { force: true });
                return false;
            }
        }

        // Advance past the last wizard step so the final step (Your details)
        // renders as completed/collapsed instead of staying active and open.
        // The payment area lives outside the wizard steps, so it stays visible.
        currentWizardStep = wizardSteps.length;
        refreshWizardStepStates();

        if (options.scroll !== false) {
            scrollToPayment();
            // Activate the card field right away so the customer can start typing
            // as soon as they land on payment. Wait for the scroll animation to
            // settle first. Skipped on the final place-order/mobile-pay path
            // (scroll === false) so submitting doesn't steal focus.
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
            '.rd-checkout-step__continue, .rd-checkout-progress__item--pay, .rd-mobile-pay-bar__button, #place_order',
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

        $(document).on('click', '.rd-checkout-progress__item:not(.rd-checkout-progress__item--pay)', function (event) {
            event.preventDefault();

            var slug = $(this).data('rd-progress');
            var index = getWizardStepIndex(slug);

            if (index === -1 || index > currentWizardStep) {
                return;
            }

            goToWizardStep(index, { force: true });
        });

        $(document).on('click', '.rd-checkout-progress__item--pay', function (event) {
            event.preventDefault();
            goToPayment();
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

    function refreshExpressCheckout() {
        decorateShippingOptions();
        applyFulfilmentMode();
        updateScheduleDateLabel();
        syncBillingFromShippingForStripe();
        syncCustomEircodeToPostcodes();
        ensureStripePaymentVisible();
        updateScheduleUnavailableState();
        syncMobilePayBarTotal();
        refreshWizardStepStates();
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

    $(document.body).on('updated_checkout', function () {
        refreshExpressCheckout();
        updateScheduleDateLabel();
        syncMobilePayBarTotal();
        applyPickupAddresses();
        refreshPickupUi();
        // The order review table is re-rendered on every AJAX refresh, which
        // resets the <details> accordion. Re-apply the user's chosen state.
        applyOrderSummaryState();
    });

    // Remember every genuine pickup-location choice so a later order-review
    // refresh that blanks the field can't lose it (see restorePickupSelection).
    $(document).on('select2:select change', '#rd-checkout-step-method select.pickup-location-lookup', function () {
        var value = $(this).val();
        if (value) {
            rememberedPickup = value;
        }
    });

    // Auto-advance off the Method step when a pickup location is chosen. Bound to
    // the genuine user-selection event (select2:select) only — never the generic
    // `change` — so programmatic restores/order-review refreshes can't push the
    // customer forward. Guarded against restores for belt-and-braces.
    $(document).on('select2:select', '#rd-checkout-step-method select.pickup-location-lookup', function () {
        if (pickupRestoreGuard || !$(this).val()) {
            return;
        }

        window.setTimeout(function () {
            maybeAutoAdvanceStep(0);
        }, 0);
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

        if (!$wrap.length || pickupDropdownAnchored($select, $wrap)) {
            return;
        }

        event.preventDefault();

        window.setTimeout(function () {
            anchorPickupSelect($select);
            $select.select2('open');
        }, 0);
    });

    // Record genuine user toggles so AJAX refreshes don't override their choice.
    $(document).on('click', '.rd-order-summary__bar', function () {
        var $summary = $(this).closest('.rd-order-summary');
        window.setTimeout(function () {
            orderSummaryUserState = $summary.prop('open');
        }, 0);
    });

    applyOrderSummaryState();

    $(document.body).on('checkout_error', function () {
        window.setTimeout(routeCheckoutErrors, 0);
    });

    $(document.body).on('wc_local_pickup_plus_ready wc_local_pickup_plus_after_locations_html', function () {
        decorateShippingOptions();
        syncPickupVisibility();
        updateWizardSummaries();
        applyPickupAddresses();
        refreshPickupUi();
    });
    $(document).on(
        'change input',
        '#customer_details input, #customer_details select, #custom_shipping_eircode',
        function () {
            syncBillingFromShippingForStripe();
            syncCustomEircodeToPostcodes();
        }
    );
    $(document).on('change', 'input.shipping_method', applyFulfilmentMode);
    // Auto-advance off the Method step once a choice is made. Delivery validates
    // immediately, so it jumps to the Date step; collection needs a pickup
    // location first, so it's a silent no-op here until that's chosen (handled by
    // the pickup-location handler below). Deferred so applyFulfilmentMode and any
    // Local Pickup Plus DOM work settle before we evaluate validity.
    $(document).on('change', 'input.shipping_method', function () {
        window.setTimeout(function () {
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
    // Auto-advance off the Date step once a date is chosen. The Iconic datepicker
    // sets #jckwds-delivery-date and fires change; deferred so its value (and any
    // updated_checkout refresh it kicks off) settles before we validate. When no
    // dates are available the schedule step reports an error, so this stays a
    // silent no-op and the customer sees the "no dates" panel instead.
    $(document).on('change', '#jckwds-delivery-date', function () {
        window.setTimeout(function () {
            maybeAutoAdvanceStep(1);
        }, 0);
    });

    $(window).on('resize', updateMobilePayBarVisibility);

    $(window).on('load', function () {
        initCheckoutWizard();
        refreshExpressCheckout();

        if (filterSpecificMessages(collectCheckoutErrorMessages()).length) {
            routeCheckoutErrors();
        }
    });
    $(function () {
        initCheckoutWizard();
        refreshExpressCheckout();
        applyPickupAddresses();
        refreshPickupUi();
    });
})(jQuery);
