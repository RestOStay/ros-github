var CheckoutMain = Class.create();
CheckoutMain.prototype = {
    initialize: function(allForms) {
        this.allForms = allForms;
    },
    setLoadWaiting: function(flag) {
        $('uni-main-loader-checkout').style.height = $('opcheckout-wrapper-main').clientHeight + 'px';
        document.getElementById('uni-main-loader-checkout').style.display = ((flag) ? 'block' : 'none');
        document.getElementById('sub-loader').style.display = ((flag) ? 'block' : 'none');
    },
    save: function() {
        var validators = new Array();
        for (i = 0; i < this.allForms.length; i++) {
            validators[i] = new Validation(this.allForms[i]);
        }
        if (validators[0].validate() && validators[1].validate() && this.shippingMethodValidate() && this.paymentMethodValidate() && (validators[3] ? validators[3].validate() : true)) {
            reviewFinal.save();
        }
    },
    setShippingDifferent: function(divId, isDiff) {
        document.getElementById(divId).style.display = (isDiff ? 'none' : 'block');
    },
    setStepResponse: function(response) {
        if (response.update_section) {
            if ($('checkout-' + response.update_section.name + '-load')) {
                $('checkout-' + response.update_section.name + '-load').update(response.update_section.html);
            }
            return true;
        }
        return false;
    },
    loadReview: function() {

    },
    shippingMethodValidate: function() {
        if (!canShip()) {
            return true;
        }
        var methods = document.getElementsByName('shipping_method');
        if (methods.length == 0) {
            alert(Translator.translate('Your order can not be completed at this time as there is no shipping methods available for it. Please make neccessary changes in your shipping address.'));
            return false;
        }

        for (var i = 0; i < methods.length; i++) {
            if (methods[i].checked) {
                return true;
            }
        }
        alert(Translator.translate('Please specify shipping method.'));
        return false;
    },
    paymentMethodValidate: function() {
        var methods = document.getElementsByName('payment[method]');
        if (methods.length == 0) {
            alert(Translator.translate('Your order can not be completed at this time as there is no payment methods available for it.'));
            return false;
        }
        for (var i = 0; i < methods.length; i++) {
            if (methods[i].checked) {
                return true;
            }
        }
        alert(Translator.translate('Please specify payment method.'));
        return false;
    }
}
// billing
var Billing = Class.create();
Billing.prototype = {
    initialize: function(form, addressUrl, saveUrl) {
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function(event) {
                this.save();
                Event.stop(event);
            }.bind(this));
        }
        this.addressUrl = addressUrl;
        this.saveUrl = saveUrl;
        this.onAddressLoad = this.fillForm.bindAsEventListener(this);
        this.onSave = this.nextStep.bindAsEventListener(this);
        /*this.onComplete   = this.resetLoadWaiting.bindAsEventListener(this);*/
        this.onFail = this.ajaxFailed.bindAsEventListener(this);
    },
    setAddress: function(addressId) {
        if (addressId) {
            request = new Ajax.Request(
                    this.addressUrl + addressId,
                    {
                        method: 'get',
                        onSuccess: this.onAddressLoad,
                        onFailure: this.onFail
                    }
            );
        }
        else {
            this.fillForm(false);
        }
    },
    newAddress: function(isNew) {
        if (isNew) {
            this.resetSelectedAddress();
            Element.show('billing-imformation-fields');
//            arrElements = Form.getElements(this.form);
//            for (var elemIndex in arrElements) {
//                if (arrElements[elemIndex].id) {
//                    var fieldName = arrElements[elemIndex].id.replace(/^billing:/, '');
//                    if (fieldName != 'country_id'){
//                        arrElements[elemIndex].value = '';
//                    }
//                }
//            }
        } else {
            Element.hide('billing-imformation-fields');
        }
        billing.save();
    },
    resetSelectedAddress: function() {
        var selectElement = $('billing-address-select')
        if (selectElement) {
            selectElement.value = '';
        }
    },
    fillForm: function(transport) {
        var elementValues = {};
        if (transport && transport.responseText) {
            try {
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                elementValues = {};
            }
        }
        else {
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(this.form);
        for (var elemIndex in arrElements) {
            if (arrElements[elemIndex].id) {
                var fieldName = arrElements[elemIndex].id.replace(/^billing:/, '');
                arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
                if (fieldName == 'country_id' && billingForm) {
                    billingForm.elementChildLoad(arrElements[elemIndex]);
                }
            }
        }
    },
    setUseForShipping: function(flag) {
        $('shipping:same_as_billing').checked = flag;
    },
    save: function() {
        checkout.setLoadWaiting('billing');

        var request = new Ajax.Request(
                this.saveUrl,
                {
                    method: 'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: this.onFail,
                    parameters: Form.serialize(this.form)
                }
        );
    },
    resetLoadWaiting: function(transport) {
        checkout.setLoadWaiting(false);
    },
    /**
     This method recieves the AJAX response on success.
     There are 3 options: error, redirect or html with shipping options.
     */
    nextStep: function(transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.error) {
            if ((typeof response.message) == 'string') {
                alert(response.message);
            } else {
                if (window.billingRegionUpdater) {
                    billingRegionUpdater.update();
                }

                alert(response.message.join("\n"));
            }
            checkout.setLoadWaiting(false);
            return false;
        }
        if ((response.goto_section == 'shipping' || response.goto_section == 'shipping_method') && checkout.setStepResponse(response)) {
        }
        checkout.setLoadWaiting(false);
        if (response.goto_section == 'shipping') {
            if (!canShip()) {
                window.location = window.location;
            } else {
                shippingStep.save();
            }
        } else {
            payment.loadPaymentMethods();
            reviewStep.getReview();
        }
    },
    ajaxFailed: function() {
        alert('Ajax Failed!');
    }
}
//Shipping
var ShippingStep = Class.create();
ShippingStep.prototype = {
    initialize: function(shippingForm, saveUrl, methodsUrl) {
        this.shippingForm = shippingForm
        this.saveUrl = saveUrl;
        this.methodsUrl = methodsUrl;
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onFail = this.ajaxFailed.bindAsEventListener(this);
    },
    setSameAsBilling: function(flag) {
        if (flag) {
            this.syncWithBilling();
        }
    },
    syncWithBilling: function() {
        $('billing-address-select') && this.newAddress(!$('billing-address-select').value);
        if (!$('billing-address-select') || !$('billing-address-select').value) {
            arrElements = Form.getElements(this.shippingForm);
            for (var elemIndex in arrElements) {
                if (arrElements[elemIndex].id) {
                    var sourceField = $(arrElements[elemIndex].id.replace(/^shipping:/, 'billing:'));
                    if (sourceField) {
                        arrElements[elemIndex].value = sourceField.value;
                    }
                }
            }
            shippingRegionUpdater.update();
            $('shipping:region_id').value = $('billing:region_id').value;
            $('shipping:region').value = $('billing:region').value;
        } else {
            $('shipping-address-select').value = $('billing-address-select').value;
        }
    },
    save: function() {
        checkout.setLoadWaiting(true);
        var request = new Ajax.Request(
                this.saveUrl,
                {
                    method: 'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: this.onFail,
                    parameters: Form.serialize(this.shippingForm)
                }
        );
    },
    newAddress: function(isNew) {
        if (isNew) {
            this.resetSelectedAddress();
            Element.show('shipping-imformation-fields');
        } else {
            Element.hide('shipping-imformation-fields');
        }
        shipping.setSameAsBilling(false);

    },
    resetSelectedAddress: function() {
        var selectElement = $('shipping-address-select')
        if (selectElement) {
            selectElement.value = '';
        }
    },
    nextStep: function(transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.error) {
            if ((typeof response.message) == 'string') {
                alert(response.message);
            } else {
                if (window.shippingRegionUpdater) {
                    shippingRegionUpdater.update();
                }
                alert(response.message.join("\n"));
            }
            checkout.setLoadWaiting(false);
            return false;
        }
        if (checkout.setStepResponse(response)) {
        }
        checkout.setLoadWaiting(false);
        payment.loadPaymentMethods();
        reviewStep.getReview();
    },
    ajaxFailed: function() {
        alert('Ajax Failed!!');
    }
}

//Shipping Method
var ShippingMethodStep = Class.create();
ShippingMethodStep.prototype = {
    initialize: function(shippingMethodForm, saveUrl) {
        this.shippingMethodForm = shippingMethodForm
        this.saveUrl = saveUrl;
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onFail = this.ajaxFailed.bindAsEventListener(this);
    },
    save: function() {
        checkout.setLoadWaiting(true);
        var request = new Ajax.Request(
                this.saveUrl,
                {
                    method: 'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: this.onFail,
                    parameters: Form.serialize(this.shippingMethodForm)
                }
        );
    },
    nextStep: function(transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.redirect) {
            location.href = response.redirect;
            return;
        }
        if (response.error) {
            alert(response.message);
            return false;
        }
        checkout.setLoadWaiting(false);
        reviewStep.getReview();
    },
    ajaxFailed: function() {
        alert('Ajax Failed!!');
    }
}

// payment
var Payment = Class.create();
Payment.prototype = {
    initialize: function(form, saveUrl, actionUrl) {
        this.form = form;
        this.saveUrl = saveUrl;
        this.actionUrl = actionUrl;
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onLoad = this.setPaymentMethod.bindAsEventListener(this);
        this.onFail = this.ajaxFailed.bindAsEventListener(this);
    },
    init: function() {
        var elements = Form.getElements(this.form);
        var method = null;
        if (this.currentMethod && $('p_method_' + this.currentMethod)) {
            $('p_method_' + this.currentMethod).checked = true;
        }
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].name == 'payment[method]') {
                if (elements[i].checked) {
                    method = elements[i].value;
                }
            } else {
                elements[i].disabled = true;
            }
        }
        if (method)
            this.switchMethod(method);
    },
    switchMethod: function(method) {
        if (this.currentMethod && $('payment_form_' + this.currentMethod)) {
            var form = $('payment_form_' + this.currentMethod);
            form.style.display = 'none';
            var elements = form.select('input', 'select', 'textarea');
            for (var i = 0; i < elements.length; i++) {
                elements[i].disabled = true;
            }
        }
        if ($('payment_form_' + method)) {
            var form = $('payment_form_' + method);
            form.style.display = '';
            var elements = form.select('input', 'select', 'textarea');
            for (var i = 0; i < elements.length; i++)
                elements[i].disabled = false;
        }
        if (method)
            this.currentMethod = method;
    },
    validate: function() {
        var methods = document.getElementsByName('payment[method]');
        if (methods.length == 0) {
            alert(Translator.translate('Your order can not be completed at this time as there is no payment methods available for it.'));
            return false;
        }
        for (var i = 0; i < methods.length; i++) {
            if (methods[i].checked) {
                return true;
            }
        }
        alert(Translator.translate('Please specify payment method.'));
        return false;
    },
    save: function() {
        var validator = new Validation(this.form);
        if (this.validate() && validator.validate()) {
            var request = new Ajax.Request(
                    this.saveUrl,
                    {
                        method: 'post',
                        onComplete: this.onComplete,
                        onSuccess: this.onSave,
                        onFailure: this.onFail,
                        parameters: Form.serialize(this.form)
                    }
            );
        } else {
            alert('Payment Method validation fail.');
        }
    },
    nextStep: function(transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        /*
         * if there is an error in payment, need to show error message
         */
        if (response.error) {
            if (response.fields) {
                var fields = response.fields.split(',');
                for (var i = 0; i < fields.length; i++) {
                    var field = null;
                    if (field = $(fields[i])) {
                        Validation.ajaxError(field, response.error);
                    }
                }
                return;
            }
            alert(response.error);
            return;
        }

        if (checkout.setStepResponse(response)) {
        }
    },
    ajaxFailed: function() {
        alert('Ajax Failed!!');
    },
    loadPaymentMethods: function() {
        checkout.setLoadWaiting(true);
        var request = new Ajax.Request(
                this.actionUrl,
                {
                    method: 'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onLoad,
                    onFailure: this.onFail,
                    parameters: Form.serialize(this.form)
                }
        );
    },
    setPaymentMethod: function(transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.html) {
            $('opcheckout-payment-method').update(response.html);
            if (response.method) {
                this.currentMethod = response.method;
            }
            this.init();
            resetPaymentEvents();
            checkout.setLoadWaiting(false);
        }
    }
}

//Review
var ReviewStep = Class.create();
ReviewStep.prototype = {
    initialize: function(saveUrl) {
        this.saveUrl = saveUrl;
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onFail = this.ajaxFailed.bindAsEventListener(this);
    },
    getReview: function() {
        checkout.setLoadWaiting(true);
        var request = new Ajax.Request(
                this.saveUrl,
                {
                    method: 'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: this.onFail
                }
        );
    },
    nextStep: function(transport) {
        $('one-step-checkout-review').update(transport.responseText);
        checkout.setLoadWaiting(false);
    },
    ajaxFailed: function() {
        alert('Ajax Failed!!');
    },
    isSuccess: false
}

var ReviewFinal = Class.create();
ReviewFinal.prototype = {
    initialize: function(saveUrl, successUrl, agreementsForm) {
        this.saveUrl = saveUrl;
        this.successUrl = successUrl;
        this.agreementsForm = agreementsForm;
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onFail = this.ajaxFailed.bindAsEventListener(this);
    },
    save: function() {
        checkout.setLoadWaiting(true);
        var element = $('opcheckout_order_comment');
        if (!element == null) {
            $('hidden_opcheckout_order_comment').value = $('opcheckout_order_comment').value;
        }
        var params = '';
        for (i = 0; i < checkout.allForms.length; i++) {
            params = params + (params != '' ? '&' : '') + Form.serialize(checkout.allForms[i]);
        }

        params.save = true;
        var request = new Ajax.Request(
                this.saveUrl,
                {
                    method: 'post',
                    parameters: params,
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: this.onFail
                });
    },
    nextStep: function(transport) {
        //alert('Review Final:'+transport.responseText);
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
            if (response.redirect) {
                location.href = response.redirect;
                return;
            }
            if (response.success) {
                this.isSuccess = true;
                window.location = this.successUrl;
            }
            else {
                if (!canShip() && response.is_virtual == 0) {
                    window.location = window.location;
                } else {
                    var msg = response.error_messages;
                    if (typeof(msg) == 'object') {
                        msg = msg.join("\n");
                    }
                    alert(msg);
                }
            }
        }
        checkout.setLoadWaiting(false);
    },
    ajaxFailed: function() {
        alert('Ajax Failed!!');
        $('opcheckout-place-order-button').onclick = 'checkout.save()';
    },
    isSuccess: false
}

function OpcheckoutDialog(uni_popup_id, uni_link_id, close_id, close_callback, open_callback) {
    this.popup_id = uni_popup_id;
    this.popup = $(uni_popup_id);
    this.link_id = uni_link_id;
    this.link = $(uni_link_id);
    this.close_link_search = close_id;
    if (typeof close_callback != 'undefined') {
        this.close_callback = close_callback;
    } else {
        this.close_callback = false;
    }
    if (typeof open_callback != 'undefined') {
        this.open_callback = open_callback;
    } else {
        this.open_callback = false;
    }
    var opcheckout_popup = this;
    if (this.link != null) {
        this.link.observe('click', function(e) {
            e.preventDefault();
            opcheckout_popup.show();
            $('opcheckout_username').focus();
        });
    }
    var close_link = $$(this.close_link_search);
    if (close_link.length > 0) {
        close_link.each(function(item, index) {
            item.observe('click', function(e) {
                e.preventDefault();
                opcheckout_popup.hide();
            });
        });
    }
    this.overlay = $('opcheckout_login_popup');
    this.overlay.observe('click', function(e) {
        opcheckout_popup.hide();
    });
}

OpcheckoutDialog.prototype.show_overlay = function() {
    this.overlay.show();
    //var dimensions = document.viewport.getDimensions();
    var offset = document.viewport.getScrollOffsets();
    var dimensions_new = {
        height: (document.body.clientHeight + offset.top) + 'px',
        width: (document.body.clientWidth) + 'px'
    }
    this.overlay.setStyle(dimensions_new);
    /* IE Hack */
    var IE6 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf('MSIE') + 5)) == 6;
    if (IE6) {
        $$('select').invoke('hide');
    }
}

OpcheckoutDialog.prototype.show = function() {
    this.show_overlay();
    this.popup.show();
    this.center();
    if (this.open_callback) {
        this.open_callback();
    }
}

OpcheckoutDialog.prototype.hide = function() {
    this.overlay.hide();
    /* IE Hack */
    var IE6 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf('MSIE') + 5)) == 6;
    if (IE6) {
        $$('select').invoke('show');
    }
    this.popup.hide();
    if (this.close_callback) {
        this.close_callback();
    }
}

OpcheckoutDialog.prototype.center = function() {
    //var dimensions = document.viewport.getDimensions();
    var popup_width = this.popup.getWidth();
    var popup_height = this.popup.getHeight();
    var offset = document.viewport.getScrollOffsets();
    var width = document.body.clientWidth;
    var left = (width / 2) - (popup_width / 2);
    var height = document.body.clientHeight;
    var top = (height / 3) - (popup_height / 2) + offset.top;
    this.popup.setStyle({
        top: top + 'px',
        left: left + 'px'
    });
}
if (!String.prototype.trim) {
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/g, '');
    };
}
function togglePopupContent(first_link, second_link) {
    var firstlink = $(first_link);
    var secondlink = $(second_link);
    if (firstlink != null) {
        firstlink.observe('click', function() {
            gotoLogin();
        });
    }
    if (secondlink != null) {
        secondlink.observe('click', function() {
            gotoForgotPassword();
        });
    }
}
function gotoLogin() {
    $('opcheckout-forgotpassword-content').hide();
    $('opcheckout-login-content').show();
    $('opcheckout_username').focus();
}
function gotoForgotPassword() {
    $('opcheckout-login-content').hide();
    $('opcheckout-forgotpassword-content').show();
    $('opcheckout_forgotpassword_email').focus();
}

function canShip() {
    return parseInt($('opcheckout-canShip').value) ? true : false;
}
function resetPaymentEvents() {
    function toggleToolTip(event) {
        if ($('payment-tool-tip')) {
            $('payment-tool-tip').setStyle({
                //left: (Event.pointerX(event)+100)+'px'
                top: (Event.pointerY(event) - 320) + 'px'
            })
            $('payment-tool-tip').toggle();
        }
        Event.stop(event);
    }
    if ($('payment-tool-tip-close')) {
        Event.observe($('payment-tool-tip-close'), 'click', toggleToolTip);
    }
    $$('.cvv-what-is-this').each(function(element) {
        Event.observe(element, 'click', toggleToolTip);
    });
}