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

var ccSaveSelected = '0';
function selectCCSaved(obj, cc_type, maxipago_cc_token) {
    var entity = obj.id.replace('ccEntity', '');

    document.getElementById('mpEntityId').value = maxipago_cc_token;
    document.getElementById('mpCCType').value = cc_type;
    document.getElementById('mpPaymentMethod').value = cc_type;

    obj.className = 'mpPaymentMethod mpPaymentCCSave mpPaymentCCSaveSelected';
    console.log('ccSaveSelected: ' + ccSaveSelected);
    console.log('entity: ' + entity);
    if (ccSaveSelected != '0' && entity != ccSaveSelected) {
        document.getElementById('ccEntity' + ccSaveSelected).className = 'mpPaymentMethod mpPaymentCCSave';
    }
    ccSaveSelected = entity;
}

function addNewCardMaxiPago(code) {
    clearCreditCard(code);
    document.getElementById('selectCreditCardMp').style.display = 'none';
    document.getElementById('newCreditCardMp').style.display = 'block';
    document.getElementById('displayCcInfo').style.display = 'block';
}

function selectCardMaxiPago(code) {
    clearCreditCard(code);
    document.getElementById('selectCreditCardMp').style.display = 'block';
    document.getElementById('newCreditCardMp').style.display = 'none';
    document.getElementById('displayCcInfo').style.display = 'none';
}

function clearCreditCard(code) {
    document.getElementById('mpPaymentFlagVI').className = 'mpPaymentFlag mpPaymentFlagVI'
    document.getElementById('mpPaymentFlagMC').className = 'mpPaymentFlag mpPaymentFlagMC'
    document.getElementById('mpPaymentFlagDC').className = 'mpPaymentFlag mpPaymentFlagDC'
    document.getElementById('mpPaymentFlagAM').className = 'mpPaymentFlag mpPaymentFlagAM'
    document.getElementById('mpPaymentFlagELO').className = 'mpPaymentFlag mpPaymentFlagELO'
    document.getElementById('mpPaymentFlagDI').className = 'mpPaymentFlag mpPaymentFlagDI'
    document.getElementById('mpPaymentFlagHC').className = 'mpPaymentFlag mpPaymentFlagHC'
    document.getElementById('mpEntityId').value = '';
    document.getElementById('mpCCType').value = '';
    document.getElementById('mpPaymentMethod').value = '';
    document.getElementById(code + '_cc_owner').value = '';
    document.getElementById(code + '_cc_number').value = '';
    document.getElementById(code + '_expiration').value = '0';
    document.getElementById(code + '_expiration_yr').value = '0';
    document.getElementById(code + '_cc_cid').value = '';
    if (ccSaveSelected != '0') {
        document.getElementById('ccEntity' + ccSaveSelected).className = 'mpPaymentMethod mpPaymentCCSave';
        ccSaveSelected = '0';
    }
}

var errorMessage;
var id_charge = 0;
var active = 0;

var maxipago = {
    load: function() {
        var self = this;
        $('.cpf-mask').mask('000.000.000-00', {
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
           jQuery('#boleto-form').submit();
        });

        jQuery('.checkout-footer #button-card button').click(function(){
           jQuery('#card-form').submit();
        });

        jQuery('.checkout-footer #button-tef button').click(function(){
           jQuery('#tef-form').submit();
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
        errorMessage = '';
        if (!(this.verifyCPF($('#input-payment-card-cpf').val()))) {
            errorMessage = 'O CPF digitado é inválido.';
        } else if ($('input[name=input-payment-card-brand]:checked', '#payment-card-form').val()=="") {
            errorMessage = 'Selecione a bandeira do cartão de crédito.';
        } else if ($('#input-payment-card-installments').val()=="") {
            errorMessage = 'Selecione a quantidade de parcelas que deseja.';
        } else if ($('#input-payment-card-number').val()=="") {
            errorMessage = 'Digite o número do cartão de crédito.';
        } else if ($('#input-payment-card-cvv').val()=="") {
            errorMessage = 'Digite o código de segurança do cartão de crédito.';
        } else if ($('#input-payment-card-expiration-month').val()=="" || $('#input-payment-card-expiration-year').val()=="") {
            errorMessage = 'Digite os dados de validade do cartão de crédito.';
        }

        if (errorMessage!='') {
            this.showError(errorMessage);
            $('#mp-pay-card-button').prop("disabled",false);
            return false;
        } else {
            return true;
        }
    },

    showError: function(message) {
        if (!$('#wc-maxipago-messages').is(":visible")) {
            $('#wc-maxipago-messages').slideDown();
        }
        this.scrollToTop();
        jQuery("#wc-maxipago-messages").html(message)
    },

    hideError: function() {
        $('#wc-maxipago-messages').slideUp();
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
        $('#payment-card-saved').change(function(){
            var desc = $(this).val();
            if (desc) {
                $('.saved-cards .remove').show();
                $('#card-data .new-card').hide();
            } else {
                $('.saved-cards .remove').hide();
                $('#card-data .new-card').show();
            }
        });
    },

    scrollToTop: function() {
        $("html, body").animate({ scrollTop: $("#wc-maxipago-messages").offset().top-80 }, "slow");
    }

}

jQuery(document).ready(function($){
    maxipago.load();
});