function verifyMaxipagoCC(ccNumber) {

    var eloRE = /^(636368|438935|504175|451416|(6362|5067|4576|4011)\d{2})\d{10}/;
    var visaRE = /^4\d{12,15}/;
    var masterRE = /^5[1-5]{1}\d{14}/;
    var amexRE = /^(34|37)\d{13}/;
    var discoveryRE = /^(6011|622\d{1}|(64|65)\d{2})\d{12}/;
    var hiperRE = /^(60\d{2}|3841)\d{9,15}/;
    var dinersRE = /^((30(1|5))|(36|38)\d{1})\d{11}/;

    try {
        document.getElementById('mpPaymentFlagVI').className = 'mpPaymentFlag mpPaymentFlagVI';
    } catch (err) {
        console.debug(err.message);
    }
    try {
        document.getElementById('mpPaymentFlagMC').className = 'mpPaymentFlag mpPaymentFlagMC';
    } catch (err) {
        console.debug(err.message);
    }
    try {
        document.getElementById('mpPaymentFlagDC').className = 'mpPaymentFlag mpPaymentFlagDC';
    } catch (err) {
        console.debug(err.message);
    }
    try {
        document.getElementById('mpPaymentFlagAM').className = 'mpPaymentFlag mpPaymentFlagAM';
    } catch (err) {
        console.debug(err.message);
    }
    try {
        document.getElementById('mpPaymentFlagELO').className = 'mpPaymentFlag mpPaymentFlagELO';
    } catch (err) {
        console.debug(err.message);
    }
    try {
        document.getElementById('mpPaymentFlagDI').className = 'mpPaymentFlag mpPaymentFlagDI';
    } catch (err) {
        console.debug(err.message);
    }
    try {
        document.getElementById('mpPaymentFlagHC').className = 'mpPaymentFlag mpPaymentFlagHC';
    } catch (err) {
        console.debug(err.message);
    }

    if (eloRE.test(ccNumber)) {
        document.getElementById('mpPaymentMethod').value = 'ELO';
        document.getElementById('mpPaymentFlagELO').className = 'mpPaymentFlag mpPaymentFlagELO mpPaymentFlagSelected';
    } else if (visaRE.test(ccNumber)) {
        document.getElementById('mpPaymentMethod').value = 'VI';
        document.getElementById('mpPaymentFlagVI').className = 'mpPaymentFlag mpPaymentFlagVI mpPaymentFlagSelected';
    } else if (masterRE.test(ccNumber)) {
        document.getElementById('mpPaymentMethod').value = 'MC';
        document.getElementById('mpPaymentFlagMC').className = 'mpPaymentFlag mpPaymentFlagMC mpPaymentFlagSelected';
    } else if (amexRE.test(ccNumber)) {
        document.getElementById('mpPaymentMethod').value = 'AM';
        document.getElementById('mpPaymentFlagAM').className = 'mpPaymentFlag mpPaymentFlagAM mpPaymentFlagSelected';
    } else if (discoveryRE.test(ccNumber)) {
        document.getElementById('mpPaymentMethod').value = 'DI';
        document.getElementById('mpPaymentFlagDI').className = 'mpPaymentFlag mpPaymentFlagDI mpPaymentFlagSelected';
    } else if (hiperRE.test(ccNumber)) {
        document.getElementById('mpPaymentMethod').value = 'HC';
        document.getElementById('mpPaymentFlagHC').className = 'mpPaymentFlagHC mpPaymentFlagSelected';
    } else if (dinersRE.test(ccNumber)) {
        document.getElementById('mpPaymentMethod').value = 'DC';
        document.getElementById('mpPaymentFlagDC').className = 'mpPaymentFlag mpPaymentFlagDC mpPaymentFlagSelected';
    }
}

var maxipago = {
    load: function() {
        var self = this;
        jQuery('.cpf-mask').mask('000.000.000-00', {
            onComplete: function(val, e, field, options) {
                if (!self.verifyCPF(val)) {
                    self.showError('CPF inválido. Digite novamente.');
                } else {
                    self.hideError();
                }
            },
            placeholder: "___.___.___-__"
        });

        jQuery('#input-payment-card-number').mask('0000000000000000000');
        jQuery('#input-payment-card-cvv').mask('99900');

        jQuery('input#card_radio').click(function(){
            self.showCard();
        });
        jQuery('input#boleto_radio').click(function(){
            self.showBoleto();
        });
        jQuery('input#tef_radio').click(function(){
            self.showTef();
        });

        jQuery('.checkout-footer #button-boleto button').click(function(){
            if (self.validateTicketFields()) {
                jQuery('#boleto-form').submit();
            }
        });

        jQuery('.checkout-footer #button-card button').click(function(){
            if (self.validateCardFields()) {
                jQuery('#card-form').submit();
            }
        });

        jQuery('.checkout-footer #button-tef button').click(function(){
            if (self.validateEftFields()) {
                jQuery('#tef-form').submit();
            }
        });

        //Clear Fields
        jQuery('input[name="payment-card-brand"]').click(function(){
            self.clearError('.mp-card-brand-selector');
        });

        jQuery('#payment-card-number').focus(function(){
            self.clearError('#payment-card-number');
        });

        jQuery('#payment-card-owner').focus(function(){
            self.clearError('#payment-card-owner');
        });

        jQuery('#payment-card-expiration-month').focus(function(){
            self.clearError('#payment-card-expiration-month');
            self.clearError('#uniform-payment-card-expiration-month');
        });

        jQuery('#payment-card-expiration-year').focus(function(){
            self.clearError('#payment-card-expiration-year');
            self.clearError('#uniform-payment-card-expiration-year');
        });

        jQuery('#payment-card-cvv').focus(function(){
            self.clearError('#payment-card-cvv');
        });

        jQuery('#payment-card-cvv-saved').focus(function(){
            self.clearError('#payment-card-cvv-saved');
        });

        jQuery('#payment-card-cpf').focus(function(){
            self.clearError('#payment-card-cpf');
        });

        jQuery('#payment-card-installments').focus(function(){
            self.clearError('#payment-card-installments');
            self.clearError('#uniform-payment-card-installments');
        });

        jQuery('#payment-boleto-cpf').focus(function(){
            self.clearError('#payment-boleto-cpf');
        });

        jQuery('input[name="payment-tef-bank"]').click(function(){
            self.clearError('.mp-eft-bank-selector');
        });

        jQuery('#payment-tef-cpf').focus(function(){
            self.clearError('#payment-tef-cpf');
        });

        self.changeSavedCard();

    },

    showBoleto: function() {
        jQuery('#payment-boleto').slideDown();
        jQuery('#payment-card').slideUp();
        jQuery('#payment-tef').slideUp();
        jQuery('input#card_radio').prop('checked', false);
        jQuery('input#card_radio').closest('span').removeClass('checked');
        jQuery('input#tef_radio').prop('checked', false);
        jQuery('input#tef_radio').closest('span').removeClass('checked');

        jQuery('.checkout-footer .mp-buttons').hide();
        jQuery('.checkout-footer #button-boleto').show();
    },

    showCard: function() {
        jQuery('#payment-card').slideDown();
        jQuery('#payment-boleto').slideUp();
        jQuery('#payment-tef').slideUp();
        jQuery('input#boleto_radio').prop('checked', false);
        jQuery('input#boleto_radio').closest('span').removeClass('checked');
        jQuery('input#tef_radio').prop('checked', false);
        jQuery('input#tef_radio').closest('span').removeClass('checked');

        jQuery('.checkout-footer .mp-buttons').hide();
        jQuery('.checkout-footer #button-card').show();
    },

    showTef: function() {
        jQuery('#payment-tef').slideDown();
        jQuery('#payment-card').slideUp();
        jQuery('#payment-boleto').slideUp();
        jQuery('input#card_radio').prop('checked', false);
        jQuery('input#card_radio').closest('span').removeClass('checked');
        jQuery('input#boleto_radio').prop('checked', false);
        jQuery('input#boleto_radio').closest('span').removeClass('checked');

        jQuery('.checkout-footer .mp-buttons').hide();
        jQuery('.checkout-footer #button-tef').show();
    },

    validateCardFields: function() {

        var self = this;
        var validated = true;

        //New card
        if (jQuery('#payment-card-saved').val() == '') {

            if (jQuery('input[name="payment-card-brand"]:checked').length == 0) {
                self.showError('.mp-card-brand-selector');
                validated = false;
            }

            if (jQuery('#payment-card-number').val() == "") {
                self.showError('#payment-card-number');
                validated = false;
            }

            if (jQuery('#payment-card-owner').val() == "") {
                self.showError('#payment-card-owner');
                validated = false;
            }

            if (jQuery('#payment-card-expiration-month').val() == "") {
                self.showError('#payment-card-expiration-month');
                self.showError('#uniform-payment-card-expiration-month');
                validated = false;
            }

            if (jQuery('#payment-card-expiration-year').val() == "") {
                self.showError('#payment-card-expiration-year');
                self.showError('#uniform-payment-card-expiration-year');
                validated = false;
            }

            if (jQuery('#payment-card-cvv').val() == "") {
                self.showError('#payment-card-cvv');
                validated = false;
            }
        } else {
            //savedCard
            if (jQuery('#payment-card-cvv-saved').val() == "") {
                self.showError('#payment-card-cvv-saved');
                validated = false;
            }

        }

        if (!(this.verifyCPF(jQuery('#payment-card-cpf').val()))) {
            self.showError('#payment-card-cpf');
            validated = false;
        }

        if (jQuery('#payment-card-installments').val() == "") {
            self.showError('#payment-card-installments');
            self.showError('#uniform-payment-card-installments');
            validated = false;
        }

        return validated;
    },

    validateEftFields: function() {
        var self = this;
        var validated = true;

        if (jQuery('input[name="payment-tef-bank"]:checked').length == 0) {
            self.showError('.mp-eft-bank-selector');
            validated = false;
        }

        if (!(this.verifyCPF(jQuery('#payment-tef-cpf').val()))) {
            self.showError('#payment-tef-cpf');
            validated = false;
        }

        return validated;

    },

    validateTicketFields: function() {
        var self = this;
        var validated = true;

        if (!(this.verifyCPF(jQuery('#payment-boleto-cpf').val()))) {
            self.showError('#payment-boleto-cpf');
            validated = false;
        }

        return validated;

    },

    showError: function(selector) {
        jQuery(selector).css('border', '1px solid #f00');
    },

    clearError: function(selector) {
        jQuery(selector).css('border', '1px solid #d6d4d4');
    },

    verifyCPF: function(cpf) {
        cpf = cpf.replace(/[^\d]+/g,'');

        if(cpf == '' || cpf.length != 11)
            return false;

        var resto;
        var soma = 0;

        if (
            cpf == "00000000000"
            || cpf == "11111111111"
            || cpf == "22222222222"
            || cpf == "33333333333"
            || cpf == "44444444444"
            || cpf == "55555555555"
            || cpf == "66666666666"
            || cpf == "77777777777"
            || cpf == "88888888888"
            || cpf == "99999999999"
            || cpf == "12345678909"
        ) {
            return false;
        }

        for (i=1; i<=9; i++)
            soma = soma + parseInt(cpf.substring(i-1, i)) * (11 - i);

        resto = (soma * 10) % 11;

        if ((resto == 10) || (resto == 11))
            resto = 0;

        if (resto != parseInt(cpf.substring(9, 10)) )
            return false;

        soma = 0;
        for (i = 1; i <= 10; i++)
            soma = soma + parseInt(cpf.substring(i-1, i)) * (12 - i);

        resto = (soma * 10) % 11;

        if ((resto == 10) || (resto == 11))
            resto = 0;
        if (resto != parseInt(cpf.substring(10, 11) ) )
            return false;

        return true;
    },

    changeSavedCard: function() {
        jQuery('#payment-card-saved').change(function(){
            var desc = jQuery(this).val();
            if (desc) {
                jQuery('.saved-cards .remove').show();
                jQuery('#card-data .new-card').hide();
            } else {
                jQuery('.saved-cards .remove').hide();
                jQuery('#card-data .new-card').show();
            }
        });
    }

}

jQuery(document).ready(function($){
    maxipago.load();
});