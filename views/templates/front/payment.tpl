{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author maxiPago!
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!-- v0.1.2 -->
<style type="text/css" media="all">
    div#center_column {
        width: {$width_center_column|escape};
    }

    div#left_column {
        display: none;
    }
</style>
<link rel="stylesheet" type="text/css" href="{$path_uri}assets/css/checkout.css"/>

{capture name=path}
    <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='maxipago'}">
        {l s='Checkout' mod='maxipago'}
    </a>
    <span class="navigation-pipe">{$navigationPipe}</span>
    {l s='maxiPago!' mod='maxipago'}
{/capture}

<h2>{l s='Finalizar Pagamento' mod='maxipago'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
    <p class="warning">{l s='Seu carrinho está vazio!' mod='maxipago'}</p>
{else}
    <p>
        <img src="{$path_uri}assets/images/maxiPago.jpg" alt="{l s='Boleto ou Cartão de Crédito' mod='maxipago'}" />
    </p>

    {if $sandbox}
        <div class="mp-warning" id="wc-messages-sandbox">
            {l s='O modo sandbox (testes) está ativo!' mod='maxipago'}
        </div>
    {/if}

    <div class="mp-alert mp-hide" id="wc-maxipago-messages"></div>

    <div class="panel-group" id="accordion">
        {if $boleto_option}
            <div class="panel panel-default mp-option" id="boleto-option">
                <div id="background-boleto">
                    <div class="mp-row-left panel-heading panel-maxipago mp-icons">
                        <div id="boleto-radio-button" class="mp-left">
                            <input type="radio" id="boleto_radio" name="boleto_radio" value="boleto"/>
                        </div>

                        <div class="mp-left payment-option-maxipago">
                            <label for="boleto_radio">
                                <img src="{$path_uri}assets/images/ico-boleto.png" alt="{l s='Boleto Bancário' mod='maxipago'}" />
                                {l s='Boleto Bancário' mod='maxipago'}
                            </label>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="mp-row-right">
                        <div>
                            <div class="mp-left mp-price-payment-info">
                                {if $boleto_discount > 0}
                                    <center>
                                        <span class="payment-old-price-maxipago">
                                            {displayPrice price=$total}
                                        </span>
                                        <br>

                                        <span class="payment-discount-maxipago">
                                            <b>{l s='Desconto de' mod='maxipago'} {$boleto_discount} %</b>
                                        </span>
                                    </center>
                                {/if}
                            </div>

                            <div class="mp-right mp-price-payment total-maxipago">
                                {if $boleto_discount > 0}
                                    {$total_boleto}
                                {else}
                                    {displayPrice price=$total}
                                {/if}
                            </div>

                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
                <div id="payment-boleto" class="panel-collapse" style="display:none;">
                    <div class="panel-body">
                        <form class="form-horizontal"
                              id="boleto-form"
                              name="boleto-form"
                              action="{$link->getModuleLink('maxipago', 'validation', [], true)|escape:'html'}"
                              method="post">

                            <input name="method" id="method_boleto" type="hidden" value="boleto"/>
                        </form>
                    </div>
                </div>
            </div>
        {/if}

        {if $card_option}
            <div id="card-option" class="panel panel-default mp-option">
                <div id="background-card">
                    <div class="mp-row-left panel-heading panel-maxipago mp-icons">
                        <div id="card-radio-button" class="mp-left">
                            <input type="radio" id="card_radio" name="card_radio" value="card"/>
                        </div>

                        <label class="mp-left payment-option-maxipago" for="card_radio">
                            <img src="{$path_uri}assets/images/ico-card.png" alt="{l s='Cartão de Crédito' mod='maxipago'}" />
                            {l s='Cartão de Crédito' mod='maxipago'}
                        </label>
                        <div class="clear"></div>
                    </div>
                    <div class="mp-row-right">
                        <div>
                            <div class="mp-left mp-price-payment-info">
                                {if !empty($installment)}
                                    <center>
                                        <span class="payment-installment-maxipago">
                                            {l s='Pague em até' mod='maxipago'}
                                        </span>
                                        <br>
                                        <span class="payment-discount-maxipago">
                                            <strong>{$installment.max_installments}x</strong>
                                        </span>
                                    </center>
                                {/if}
                            </div>
                            <div class="mp-right mp-price-payment total-maxipago">
                                {displayPrice price=$total}
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
                <div id="payment-card" class="panel-collapse" style="display:none;">
                    <div class="panel-body">

                        <form class="form-horizontal"
                              id="card-form"
                              action="{$link->getModuleLink('maxipago', 'validation', [], true)|escape:'html'}"
                              method="post">

                            <input name="method" id="method_card" type="hidden" value="card"/>

                            <div class="mp-form">
                                <div id="card-data">
                                    <div class="clear"></div>
                                    <div class="mp-section">
                                        <p><strong>{l s='Dados do Cartão' mod='maxipago'}</strong></p>

                                        {if $cc_can_save && !empty($saved_cards)}
                                            <div class="mp-row saved-cards">
                                                <div class="mp-col-12">
                                                    <label class="" for="payment-card-saved">
                                                        {l s='Usar Cartão Salvo' mod='maxipago'}
                                                    </label>
                                                </div>

                                                <div class="mp-col-8" id="select-card-saved">
                                                    <select name="payment-card-saved"
                                                            id="payment-card-saved"
                                                            class="form-control mp-form-select">
                                                        <option value="">{l s='Selecione' mod='maxipago'}</option>
                                                        {foreach $saved_cards as $card}
                                                            <option value="{$card.description}">{$card.brand|ucwords} - {$card.description}</option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                                <div class="mp-col-4 remove" style="display:none">
                                                    <span>
                                                        <input type="text" name="payment-card-cvv-saved"
                                                               id="payment-card-cvv-saved" value=""
                                                               class="mp-cvv-input"/>
                                                        <img src="{$path_uri}assets/images/cvv.png" alt="{l s='Código de Segurança' mod='maxipago'}" />
                                                    </span>
                                                    <span>
                                                        <a href="javascript:void(0)" id="remove-cc">{l s='Remover Cartão' mod='maxipago'}</a>
                                                    </span>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                        {/if}

                                        <div class="mp-row new-card">
                                            <label>
                                                {l s='Selecione a Bandeira do Cartão' mod='maxipago'}
                                            </label>
                                            <div>
                                                <div class="mp-card-brand-selector">

                                                    {foreach $cc_brands as $brand}
                                                        <div class="pull-left">
                                                            <label class="mp-card-brand">
                                                                <input id="{$brand|strtolower}"
                                                                       type="radio"
                                                                       name="payment-card-brand"
                                                                       id="payment-card-brand-{$brand|strtolower}"
                                                                       value="{$brand}"/>
                                                                <img src="{$path_uri}assets/images/brands/{$brand|strtolower}.png" alt="{$brand}" />
                                                            </label>
                                                        </div>
                                                    {/foreach}
                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mp-row new-card required">
                                            <div class="mp-col-12">
                                                <label>
                                                    {l s='Número do cartão:' mod='maxipago'}
                                                </label>
                                                <div>
                                                    <div class="mp-card-number-input-row">
                                                        <input type="text" name="payment-card-number"
                                                               id="payment-card-number" value=""
                                                               maxlength="16"
                                                               class="form-control mp-input-card-number"/>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                            <div class="clear"></div>

                                        </div>

                                        <div class="mp-row new-card required">
                                            <div class="mp-col-12">
                                                <label>
                                                    {l s='Nome no cartão:' mod='maxipago'}
                                                </label>
                                                <div>
                                                    <div class="mp-card-number-input-row">
                                                        <input type="text" name="payment-card-owner"
                                                               id="payment-card-owner" value=""
                                                               class="form-control"/>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mp-row new-card required">

                                            <div class="mp-col-8">
                                                <label>
                                                    {l s='Validade:' mod='maxipago'}
                                                </label>
                                                <div class="mp-card-expiration-row">
                                                    <div class="pull-left">
                                                        <select class="form-control mp-card-expiration-select"
                                                                name="payment-card-expiration-month"
                                                                id="payment-card-expiration-month">
                                                            <option value="">{l s='Mês' mod='maxipago'}</option>
                                                            <option value="01">01</option>
                                                            <option value="02">02</option>
                                                            <option value="03">03</option>
                                                            <option value="04">04</option>
                                                            <option value="05">05</option>
                                                            <option value="06">06</option>
                                                            <option value="07">07</option>
                                                            <option value="08">08</option>
                                                            <option value="09">09</option>
                                                            <option value="10">10</option>
                                                            <option value="11">11</option>
                                                            <option value="12">12</option>
                                                        </select>
                                                    </div>
                                                    <div class="mp-card-expiration-divisor pull-left">
                                                        /
                                                    </div>
                                                    <div class="pull-left">
                                                        <select class="form-control mp-card-expiration-select"
                                                                name="payment-card-expiration-year"
                                                                id="payment-card-expiration-year">
                                                            <option value="">{l s='Ano' mod='maxipago'}</option>
                                                            {foreach $years as $key => $year}
                                                                <option value="{$year}">{$year}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                    <div></div>
                                                    <div class="clear"></div>
                                                </div>
                                            </div>

                                            <div class="mp-col-4">
                                                <label>
                                                    {l s='Código de Segurança' mod='maxipago'}
                                                </label>
                                                <div>
                                                    <div class="pull-left mp-cvv-row">
                                                        <input type="text" name="payment-card-cvv"
                                                               id="payment-card-cvv" value=""
                                                               class="mp-cvv-input"/>
                                                        <img src="{$path_uri}assets/images/cvv.png" alt="{l s='Código de Segurança' mod='maxipago'}" />
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                        </div>

                                        {if $cc_can_save}
                                            <div class="mp-row new-card required">

                                                <div class="mp-col-8">
                                                    <div class="pull-left mp-save-row">
                                                        <input type="checkbox" name="payment-card-save" id="payment-card-save" value="1"/>
                                                    </div>
                                                    <label class="pull-left" for="payment-card-save">
                                                        {l s='Salvar Cartão de Crédito' mod='maxipago'}
                                                    </label>
                                                </div>
                                            </div>
                                        {/if}

                                        <div class="mp-row {if $billing_cpf } mp-hide {/if} required">
                                            <div>
                                                <label class="mp-label" for="payment-card-cpf">{l s='CPF' mod='maxipago'}: </label>
                                            </div>
                                            <div class="mp-col-12">
                                                <div>
                                                    <div class="mp-col-3 required">
                                                        <input type="text"
                                                               name="payment-card-cpf"
                                                               id="payment-card-cpf"
                                                               value="{$billing_cpf}"
                                                               class="form-control cpf-mask"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mp-row required">
                                            <div class="mp-col-12">
                                                <label class="" for="payment-card-installments">
                                                    {l s='Quantidade de Parcelas' mod='maxipago'}
                                                </label>
                                            </div>

                                            <div class="mp-col-12" id="select-card-installments">
                                                <select name="payment-card-installments"
                                                        id="payment-card-installments"
                                                        class="form-control mp-form-select">
                                                    <option value="">{l s='Selecione' mod='maxipago'}</option>

                                                    {foreach $installments as $installment}
                                                        <option value="{$installment.installments}">
                                                            {$installment.installments}x {l s='de' mod='maxipago'} {displayPrice price=$installment.installment_value}
                                                            {if !$installment.interest_rate}
                                                                ({l s='sem juros' mod='maxipago'})
                                                            {else}
                                                                ({$installment.interest_rate}% {l s='a.m.' mod='maxipago'} -
                                                                {l s='Total' mod='maxipago'}: {displayPrice price=$installment.total})
                                                            {/if}
                                                        </option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>

                    </div>
                </div>
                <script>
                    var urlAjaxMaxipago = '{$base_dir}modules/maxipago/maxipago-ajax.php';

                    {literal}
                    //remove
                    $('#remove-cc').click(function(){
                        var desc = $('#payment-card-saved').val();
                        if (desc) {
                            $.ajax({
                                url: urlAjaxMaxipago,
                                type: "POST",
                                cache: false,
                                headers: {"cache-control": "no-cache"},
                                data: {
                                    action: 'remove-cc',
                                    ident: desc
                                },
                                dataType: "json",
                                success: function(result) {
                                    if (result.success == true) {
                                        $('#payment-card-saved option[value="' + desc + '"]').remove();
                                        $('#payment-card-saved').val('').change();
                                    }
                                }
                            });
                        }
                    });
                    {/literal}
                </script>
            </div>

        {/if}

        {if $tef_option && !empty($tef_banks)}
            <div class="panel panel-default mp-option" id="tef-option">
                <div id="background-tef">
                    <div class="mp-row-left panel-heading panel-maxipago mp-icons">
                        <div id="tef-radio-button" class="mp-left">
                            <input type="radio" id="tef_radio" name="tef_radio" value="tef"/>
                        </div>
                        <div class="mp-left payment-option-maxipago">
                            <label for="tef_radio">
                                <img src="{$path_uri}assets/images/ico-tef.png" alt="{l s='Transferência Eletrônica' mod='maxipago'}" />
                                {l s='Transferência Eletrônica' mod='maxipago'}
                            </label>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="mp-row-right">
                        <div>
                            <div class="mp-left mp-price-payment-info">
                                {if $tef_discount > 0}
                                    <center>
                                        <span class="mp-price-payment payment-old-price-maxipago">
                                            {displayPrice price=$total}
                                        </span>
                                        <br>
                                            <span class="payment-discount-maxipago">
                                                <b>{l s='Desconto de' mod='maxipago'} {$tef_discount} %</b>
                                            </span>
                                    </center>
                                {/if}
                            </div>
                            <div class="mp-right mp-price-payment total-maxipago">
                                {if $tef_discount > 0}
                                    {displayPrice price=$total_tef}
                                {else}
                                    {displayPrice price=$total}
                                {/if}
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
                <div id="payment-tef" class="panel-collapse" style="display:none;">
                    <div class="panel-body">
                        <form class="form-horizontal"
                              id="tef-form"
                              name="tef-form"
                              action="{$link->getModuleLink('maxipago', 'validation', [], true)|escape:'html'}"
                              method="post">

                            <input name="method" id="method_tef" type="hidden" value="tef"/>

                            <div class="mp-form">
                                <div id="tef-data">
                                    <div class="clear"></div>
                                    <div class="mp-section">
                                        <div class="mp-row">
                                            <label>
                                                {l s='Selecione o Banco' mod='maxipago'}
                                            </label>
                                            <div>
                                                <div class="mp-card-brand-selector">

                                                    {foreach $tef_banks as $bankCode => $bank}
                                                        <div class="pull-left">
                                                            <label class="mp-card-brand">
                                                                <input id="bank-{$bankCode}"
                                                                       type="radio"
                                                                       name="payment-tef-bank"
                                                                       id="payment-tek-bank-{$bankCode}"
                                                                       value="{$bankCode}"/>
                                                                <img src="{$path_uri}assets/images/banks/{$bankCode}.png" alt="{$bank}" />
                                                            </label>
                                                        </div>
                                                    {/foreach}
                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="required mp-row {if $billing_cpf } mp-hide {/if}">
                                        <div class="mp-col-2">
                                            <label class="mp-label" for="payment-tef-cpf">{l s='CPF' mod='maxipago'}: </label>
                                        </div>
                                        <div class="mp-col-10">
                                            <div>
                                                <div class="mp-col-3 required">
                                                    <input type="text" name="payment-tef-cpf" id="cpf" value="{$billing_cpf}" class="form-control cpf-mask"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        {/if}


    </div>
    <div class="checkout-footer">
        <div class="pull-left">
            <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button-exclusive btn btn-default">
                <i class="icon-chevron-left"></i>
                {l s='Outros métodos de pagamento' mod='maxipago'}
            </a>
        </div>

        <div class="pull-right">
            <div id="button-boleto" class="mp-buttons" style="display:none;">
                <p>
                    <button type="button" class="button btn btn-default button-medium" rel="boleto-form">
                        <span>
                            {l s='Pagar com Boleto Bancário | ' mod='maxipago'}
                            {if $boleto_discount}
                                {$total_boleto}
                            {else}
                                {displayPrice price=$total}
                            {/if}

                            <i class="icon-chevron-right right"></i>
                        </span>
                    </button>
                </p>
            </div>

            <div id="button-card" class="mp-buttons" style="display:none;">
                <p>
                    <button type="button" class="button btn btn-default button-medium" rel="card-form">
                        <span>
                            {l s='Pagar com Cartão de Crédito | ' mod='maxipago'}
                            {displayPrice price=$total}
                            <i class="icon-chevron-right right"></i>
                        </span>
                    </button>
                </p>
            </div>

            <div id="button-tef" class="mp-buttons" style="display:none;">
                <p>
                    <button type="button" class="button btn btn-default button-medium" rel="tef-form">
                        <span>
                            {l s='Pagar com Transferência Eletrônica | ' mod='maxipago'}

                            {if $tef_discount}
                                {$total_tef}
                            {else}
                                {displayPrice price=$total}
                            {/if}

                            <i class="icon-chevron-right right"></i>
                        </span>
                    </button>
                </p>
            </div>
        </div>

        <div class="clear"></div>
    </div>
{/if}
