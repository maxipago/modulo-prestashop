{*
* 2007-2011 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<script xmlns="http://www.w3.org/1999/html">
    if (!window.jQuery) {
        var script = document.createElement('script');
        script.type = "text/javascript";
        script.src = "{$module_dir|escape:'none'}assets/js/jquery.min.js";
        document.getElementsByTagName('head')[0].appendChild(script);
    }
</script>

<div class="panel-body">
    <div class="row admin-title-row">
        <div class="pull-left">
            <a target="_BLANK" href="http://www.maxipago.com/maxipago/">
                <img src="{$module_dir|escape:'none'}assets/images/maxiPago.jpg" alt="maxiPago!" title="maxiPago!"/>
            </a>
        </div>
    </div>

    {if $action == 'update'}
        <div class="bootstrap">
            <div class="module_confirmation conf confirm alert alert-success">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {l s='Pedidos Atualizados com Sucesso' mod='maxipago'}
            </div>
        </div>
    {/if}

    <div id="row sync-title">
        {l s='Atualize o status dos pedidos clicando em' mod='maxipago'}
        <a id="sync-maxipago" href="{$sync_url}" title="{l s='Sincronizar Pedidos' mod='maxipago'}">
            <i class="icon-refresh"></i>
            <span>{l s='Sincronizar Pedidos' mod='maxipago'}</span>
        </a>
        <br>
        <br>
        <p class="warning">
            {l s='Se quiser que a consulta seja feita automaticamente, pode-se criar uma cron no seu servidor com o seguinte comando:' mod='maxipago'}

            <pre>30 1 * * * wget -O /dev/null {$cron_url}</pre>
        </p>
    </div>

    <br>
    <br>

    <form action="{$action_post|escape:'none'}" method="POST" enctype="multipart/form-data" id="form-std-uk"
          class="form-horizontal">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#tab-general" data-toggle="tab">{l s='Configurações Gerais' mod='maxipago'}</a>
            </li>
            <li>
                <a href="#tab-cc" data-toggle="tab">{l s='Cartão de Crédito' mod='maxipago'}</a>
            </li>
            <li>
                <a href="#tab-boleto" data-toggle="tab">{l s='Boleto' mod='maxipago'}</a>
            </li>
            <li>
                <a href="#tab-tef" data-toggle="tab">{l s='Transferência Eletrônica' mod='maxipago'}</a>
            </li>
        </ul>

        <div class="tab-content">

            <div class="tab-pane active" id="tab-general">
                <div class="form-group">
                    <label class="col-sm-3 control-label">
                        {l s='Ambiente' mod='maxipago'}
                    </label>
                    <div class="col-sm-9">
                        <div class="pull-left admin-item-form">
                            <input type="radio" name="MAXIPAGO_SANDBOX" value="1" {if $MAXIPAGO_SANDBOX} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-left">
                            {l s='Testes' mod='maxipago'}
                        </div>
                        <div class="pull-left admin-item-form">
                            <input type="radio" name="MAXIPAGO_SANDBOX" value="0" {if !$MAXIPAGO_SANDBOX} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-right">
                            {l s='Produção' mod='maxipago'}
                        </div>
                    </div>
                </div>

                <div class="divisor"></div>

                <div class="form-group required">
                    <div>
                        <span><strong>{l s='Configurações Gerais' mod='maxipago'}</strong></span>
                    </div>
                    <label class="col-sm-3 control-label" for="MAXIPAGO_SELLER_KEY">
                        {l s='ID Loja' mod='maxipago'}
                    </label>
                    <div class="col-sm-9">
                        <input type="text" name="MAXIPAGO_SELLER_ID"
                               value="{$MAXIPAGO_SELLER_ID}" id="MAXIPAGO_SELLER_ID"
                               class="form-control"/>
                    </div>
                </div>

                <div class="form-group required">
                    <label class="col-sm-3 control-label" for="MAXIPAGO_SELLER_KEY">
                        {l s='Chave da Loja' mod='maxipago'}
                    </label>
                    <div class="col-sm-9">
                        <input type="text" name="MAXIPAGO_SELLER_KEY"
                               value="{$MAXIPAGO_SELLER_KEY}" id="MAXIPAGO_SELLER_KEY"
                               class="form-control"/>
                    </div>
                </div>

                <div class="form-group required">
                    <label class="col-sm-3 control-label" for="MAXIPAGO_SELLER_SECRET">
                        {l s='Chave Secreta' mod='maxipago'}
                    </label>
                    <div class="col-sm-9">
                        <input type="text" name="MAXIPAGO_SELLER_SECRET"
                               value="{$MAXIPAGO_SELLER_SECRET}" id="MAXIPAGO_SELLER_SECRET"
                               class="form-control"/>
                    </div>
                </div>

                <div class="divisor"></div>

                <div class="form-group admin-detail-config">
                    <div class="col-sm-3 control-label"></div>
                    <div class="col-sm-9 admin-detail-background">
                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_NOTIFICATION_UPDATE">
                                {l s='Atualizar status dos pedidos PrestaShop automaticamente' mod='maxipago'}
                            </label>
                            <div>
                                <div class="col-sm-10 admin.item-notification">
                                    <div class="pull-left admin-item-form">
                                        <input type="radio" name="MAXIPAGO_NOTIFICATION_UPDATE"
                                               value="1" {if $MAXIPAGO_NOTIFICATION_UPDATE} checked {/if}>
                                    </div>
                                    <div class="pull-left admin-item-left">
                                        {l s='Sim' mod='maxipago'}
                                    </div>
                                    <div class="pull-left admin-item-form">
                                        <input type="radio" name="MAXIPAGO_NOTIFICATION_UPDATE"
                                               value="0" {if !$MAXIPAGO_NOTIFICATION_UPDATE} checked {/if}>
                                    </div>
                                    <div class="pull-left admin-item-right">
                                        {l s='Não' mod='maxipago'}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_NOTIFICATION_UPDATE_MAIL">
                                {l s='Ao atualizar o status do pedido, deseja enviar e-mail automático da sua loja para notificar o cliente?' mod='maxipago'}
                            </label>
                            <div>
                                <div class="col-sm-10 admin.item-notification">
                                    <div class="pull-left admin-item-form">
                                        <input type="radio" name="MAXIPAGO_NOTIFICATION_UPDATE_MAIL"
                                               value="1" {if $MAXIPAGO_NOTIFICATION_UPDATE_MAIL} checked {/if}>
                                    </div>
                                    <div class="pull-left admin-item-left">
                                        {l s='Sim' mod='maxipago'}
                                    </div>
                                    <div class="pull-left admin-item-form">
                                        <input type="radio" name="MAXIPAGO_NOTIFICATION_UPDATE_MAIL"
                                               value="0" {if !$MAXIPAGO_NOTIFICATION_UPDATE_MAIL} checked {/if}>
                                    </div>
                                    <div class="pull-left admin-item-right">
                                        {l s='Não' mod='maxipago'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label" for="MAXIPAGO_STATUS">{l s='Status' mod='maxipago'}</label>
                    <div class="col-sm-9">
                        <div class="pull-left admin-item-form">
                            <input type="radio" name="MAXIPAGO_STATUS"
                                   value="1" {if $MAXIPAGO_STATUS} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-left">
                            {l s='Habilitado' mod='maxipago'}
                        </div>
                        <div class="pull-left admin-item-form">
                            <input type="radio" name="MAXIPAGO_STATUS"
                                   value="0" {if !$MAXIPAGO_STATUS} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-right">
                            {l s='Desabilitado' mod='maxipago'}
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label" for="MAXIPAGO_DEBUG">
                        {l s='Debug' mod='maxipago'}
                    </label>
                    <div class="col-sm-9">
                        <div class="pull-left admin-item-form">
                            <input type="radio" name="MAXIPAGO_DEBUG"
                                   value="1" {if $MAXIPAGO_DEBUG} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-left">
                            {l s='Sim' mod='maxipago'}
                        </div>
                        <div class="pull-left admin-item-form">
                            <input type="radio" name="MAXIPAGO_DEBUG"
                                   value="0" {if !$MAXIPAGO_DEBUG} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-right">
                            {l s='Não' mod='maxipago'}
                        </div>
                    </div>
                </div>

            </div>

            <div class="tab-pane" id="tab-cc">
                <div class="form-group required">
                    <h3>
                        <span><strong>{l s='Cartão de Crédito' mod='maxipago'}</strong></span>
                    </h3>

                    <label class="col-sm-3 control-label" for="MAXIPAGO_CC_ENABLED">
                        {l s='Habilitar Cartão de Crédito' mod='maxipago'}
                    </label>

                    <div class="col-sm-9">
                        <div class="pull-left admin-item-form">
                            <input type="radio" name="MAXIPAGO_CC_ENABLED"
                                   value="1" {if $MAXIPAGO_CC_ENABLED} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-left">
                            {l s='Sim' mod='maxipago'}
                        </div>
                        <div class="pull-left admin-item-form">
                            <input type="radio" name="MAXIPAGO_CC_ENABLED"
                                   value="0" {if !$MAXIPAGO_CC_ENABLED} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-right">
                            {l s='Não' mod='maxipago'}
                        </div>
                    </div>

                </div>

                <div class="form-group required">
                    <label class="col-sm-3 control-label" for="MAXIPAGO_SOFT_DESCRIPTOR">
                        {l s='Soft Descriptor (Nome na Fatura)' mod='maxipago'}
                    </label>
                    <div class="col-sm-9">
                        <input type="text"
                               name="MAXIPAGO_SOFT_DESCRIPTOR"
                               value="{$MAXIPAGO_SOFT_DESCRIPTOR}"
                               id="MAXIPAGO_SOFT_DESCRIPTOR"
                               class="form-control"
                                maxlength="20"/>
                        <p class="note">{l s='Apenas para adquirente Cielo. Não use caracteres especiais e use no máximo 20 caracteres.' mod='maxipago'}</p>
                    </div>
                </div>

                <div class="form-group required">
                    <label class="col-sm-3 control-label" for="MAXIPAGO_CC_MAX_INSTALLMENTS">
                        {l s='Quantidade máxima de parcelas' mod='maxipago'}
                    </label>
                    <div class="col-sm-9">
                        <select type="text" name="MAXIPAGO_CC_MAX_INSTALLMENTS" id="MAXIPAGO_CC_MAX_INSTALLMENTS"
                               class="form-control">
                            {for $i=1 to 12}
                                <option value="{$i}" {if $i eq $MAXIPAGO_CC_MAX_INSTALLMENTS} selected {/if}>{$i}</option>
                            {/for}
                        </select>
                    </div>
                </div>

                <div class="form-group required">
                    <label class="col-sm-3 control-label" for="MAXIPAGO_CC_MAX_WITHOUT_INTEREST">
                        {l s='Quantidade de parcelas sem juros' mod='maxipago'}
                    </label>
                    <div class="col-sm-9">
                        <select type="text" name="MAXIPAGO_CC_MAX_WITHOUT_INTEREST" id="MAXIPAGO_CC_MAX_WITHOUT_INTEREST"
                               class="form-control">
                            {for $i=1 to 12}
                                <option value="{$i}" {if $i eq $MAXIPAGO_CC_MAX_WITHOUT_INTEREST} selected {/if}>{$i}</option>
                            {/for}
                        </select>
                    </div>
                </div>

                <div class="form-group required">
                    <label class="col-sm-3 control-label" for="MAXIPAGO_CC_INTEREST_TYPE">
                        {l s='Tipo de Juros' mod='maxipago'}
                    </label>
                    <div class="col-sm-9">
                        <select type="text" name="MAXIPAGO_CC_INTEREST_TYPE" id="MAXIPAGO_CC_INTEREST_TYPE"
                               class="form-control">
                                <option value="simple" {if $MAXIPAGO_CC_INTEREST_TYPE eq 'simple'} selected {/if}>{l s='Simples' mod='maxipago'}</option>
                                <option value="compound" {if $MAXIPAGO_CC_INTEREST_TYPE eq 'compound'} selected {/if}>{l s='Composto' mod='maxipago'}</option>
                                <option value="price" {if $MAXIPAGO_CC_INTEREST_TYPE eq 'price'} selected {/if}>{l s='Price' mod='maxipago'}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group required">
                    <label class="col-sm-3 control-label" for="MAXIPAGO_CC_INTEREST_RATE">
                        {l s='Taxa de Juros (%)' mod='maxipago'}
                    </label>
                    <div class="col-sm-9">
                        <input type="text"
                               name="MAXIPAGO_CC_INTEREST_RATE"
                               value="{$MAXIPAGO_CC_INTEREST_RATE}"
                               id="MAXIPAGO_CC_INTEREST_RATE"
                               class="form-control"/>
                    </div>
                </div>

                <div class="form-group required">
                    <label class="col-sm-3 control-label" for="MAXIPAGO_CC_MINIMUM_PER_INSTALLMENTS">
                        {l s='Valor da parcela mínima' mod='maxipago'}
                    </label>
                    <div class="col-sm-9">
                        <input type="text"
                               name="MAXIPAGO_CC_MINIMUM_PER_INSTALLMENTS"
                               value="{$MAXIPAGO_CC_MINIMUM_PER_INSTALLMENTS}"
                               id="MAXIPAGO_CC_MINIMUM_PER_INSTALLMENTS"
                               class="form-control"/>
                    </div>
                </div>

                <div class="form-group required">
                    <label class="col-sm-3 control-label" for="MAXIPAGO_CC_PROCESSING_TYPE">
                        {l s='Tipo de Venda' mod='maxipago'}
                    </label>
                    <div class="col-sm-9">
                        <select id="MAXIPAGO_CC_PROCESSING_TYPE" name="MAXIPAGO_CC_PROCESSING_TYPE" class=" select">
                            {foreach from=$processing_types key=k item=v}
                                <option value="{$k}" {if $MAXIPAGO_CC_PROCESSING_TYPE eq $k} selected {/if}>
                                    {l s=$v mod='maxipago'}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                <div class="form-group row-cc-can-save">
                    <label class="col-sm-3 control-label" for="MAXIPAGO_CC_CAN_SAVE">
                        {l s='Permitir Salvar Cartão de Crédito' mod='maxipago'}
                    </label>

                    <div class="col-sm-9">
                        <div class="pull-left admin-item-form">
                            <input type="radio" name="MAXIPAGO_CC_CAN_SAVE"
                                   id="MAXIPAGO_CC_CAN_SAVE_1"
                                   value="1" {if $MAXIPAGO_CC_CAN_SAVE} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-left">
                            {l s='Sim' mod='maxipago'}
                        </div>
                        <div class="pull-left admin-item-form">
                            <input type="radio" name="MAXIPAGO_CC_CAN_SAVE"
                                   id="MAXIPAGO_CC_CAN_SAVE_0"
                                   value="0" {if !$MAXIPAGO_CC_CAN_SAVE} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-right">
                            {l s='Não' mod='maxipago'}
                        </div>
                    </div>

                </div>

                <div class="form-group required row-fraud-check">
                    <label class="col-sm-3 control-label" for="MAXIPAGO_CC_FRAUD_CHECK">
                        {l s='Verificação de Fraude' mod='maxipago'}
                    </label>

                    <div class="col-sm-9">
                        <div class="pull-left admin-item-form">
                            <input type="radio"
                                   name="MAXIPAGO_CC_FRAUD_CHECK"
                                   id="MAXIPAGO_CC_FRAUD_CHECK_1"
                                   value="1" {if $MAXIPAGO_CC_FRAUD_CHECK} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-left">
                            {l s='Sim' mod='maxipago'}
                        </div>
                        <div class="pull-left admin-item-form">
                            <input type="radio"
                                   name="MAXIPAGO_CC_FRAUD_CHECK"
                                   id="MAXIPAGO_CC_FRAUD_CHECK_0"
                                   value="0" {if !$MAXIPAGO_CC_FRAUD_CHECK} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-right">
                            {l s='Não' mod='maxipago'}
                        </div>
                    </div>

                </div>

                <div class="divisor"></div>

                <div class="form-group admin-detail-config">
                    <div class="col-sm-3 control-label"> {l s='Adquirentes' mod='maxipago'}</div>
                    <div class="col-sm-9 admin-detail-background">

                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_VISA_PROCESSOR">
                                {l s='Visa' mod='maxipago'}
                            </label>
                            <div>
                                <select name="MAXIPAGO_VISA_PROCESSOR" class="" id="MAXIPAGO_VISA_PROCESSOR" title="">
                                    {foreach from=$processors key=k item=v}
                                        <option value="{$k}" {if $MAXIPAGO_VISA_PROCESSOR eq $k} selected {/if}>
                                            {l s=$v mod='maxipago'}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>

                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_MASTERCARD_PROCESSOR">
                                {l s='MasterCard' mod='maxipago'}
                            </label>
                            <div>
                                <select name="MAXIPAGO_MASTERCARD_PROCESSOR" class="" id="MAXIPAGO_MASTERCARD_PROCESSOR" title="">
                                    {foreach from=$processors key=k item=v}
                                        <option value="{$k}" {if $MAXIPAGO_MASTERCARD_PROCESSOR eq $k} selected {/if}>
                                            {l s=$v mod='maxipago'}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>

                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_AMEX_PROCESSOR">
                                {l s='Amex (American Express)' mod='maxipago'}
                            </label>
                            <div>
                                <select name="MAXIPAGO_AMEX_PROCESSOR" class="" id="MAXIPAGO_AMEX_PROCESSOR" title="">
                                    {foreach from=$processors_amex key=k item=v}
                                        <option value="{$k}" {if $MAXIPAGO_AMEX_PROCESSOR eq $k} selected {/if}>
                                            {l s=$v mod='maxipago'}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>

                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_DINERS_PROCESSOR">
                                {l s='Diners Club' mod='maxipago'}
                            </label>
                            <div>
                                <select name="MAXIPAGO_DINERS_PROCESSOR" class="" id="MAXIPAGO_DINERS_PROCESSOR" title="">
                                    {foreach from=$processors_diners key=k item=v}
                                        <option value="{$k}" {if $MAXIPAGO_DINERS_PROCESSOR eq $k} selected {/if}>
                                            {l s=$v mod='maxipago'}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>

                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_ELO_PROCESSOR">
                                {l s='Elo' mod='maxipago'}
                            </label>
                            <div>
                                <select name="MAXIPAGO_ELO_PROCESSOR" class="" id="MAXIPAGO_ELO_PROCESSOR" title="">
                                    {foreach from=$processors_elo key=k item=v}
                                        <option value="{$k}" {if $MAXIPAGO_ELO_PROCESSOR eq $k} selected {/if}>
                                            {l s=$v mod='maxipago'}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>

                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_DISCOVER_PROCESSOR">
                                {l s='Discover' mod='maxipago'}
                            </label>
                            <div>
                                <select name="MAXIPAGO_DISCOVER_PROCESSOR" class="" id="MAXIPAGO_DISCOVER_PROCESSOR" title="">
                                    {foreach from=$processors_discover key=k item=v}
                                        <option value="{$k}" {if $MAXIPAGO_DISCOVER_PROCESSOR eq $k} selected {/if}>
                                            {l s=$v mod='maxipago'}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>

                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_HIPERCARD_PROCESSOR">
                                {l s='Hipercard' mod='maxipago'}
                            </label>
                            <div>
                                <select name="MAXIPAGO_HIPERCARD_PROCESSOR" class="" id="MAXIPAGO_HIPERCARD_PROCESSOR" title="">
                                    {foreach from=$processors_hipercard key=k item=v}
                                        <option value="{$k}" {if $MAXIPAGO_HIPERCARD_PROCESSOR eq $k} selected {/if}>
                                            {l s=$v mod='maxipago'}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="tab-pane" id="tab-boleto">
                <div class="form-group required">
                    <h3>
                        <span><strong>{l s='Boleto' mod='maxipago'}</strong></span>
                    </h3>
                    <label class="col-sm-3 control-label" for="MAXIPAGO_BOLETO_ENABLED">
                        {l s='Habilitar' mod='maxipago'}
                    </label>
                    <div class="col-sm-9">
                        <div class="pull-left admin-item-form">
                            <input type="radio" name="MAXIPAGO_BOLETO_ENABLED"
                                   value="1" {if $MAXIPAGO_BOLETO_ENABLED} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-left">
                            {l s='Sim' mod='maxipago'}
                        </div>
                        <div class="pull-left admin-item-form">
                            <input type="radio" name="MAXIPAGO_BOLETO_ENABLED"
                                   value="0" {if !$MAXIPAGO_BOLETO_ENABLED} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-right">
                            {l s='Não' mod='maxipago'}
                        </div>
                    </div>
                </div>

                <div class="form-group admin-detail-config">
                    <div class="col-sm-3 control-label">{l s='Configurações' mod='maxipago'}</div>
                    <div class="col-sm-9 admin-detail-background">
                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_BOLETO_DAYS_TO_EXPIRE">
                                {l s='Dias para vencimento do Boleto' mod='maxipago'}
                            </label>
                            <div>
                                <input type="text" name="MAXIPAGO_BOLETO_DAYS_TO_EXPIRE"
                                       value="{$MAXIPAGO_BOLETO_DAYS_TO_EXPIRE}"
                                       id="MAXIPAGO_BOLETO_DAYS_TO_EXPIRE" class="form-control"/>
                            </div>
                        </div>
                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_BOLETO_INSTRUCTIONS">
                                {l s='Instruções' mod='maxipago'}
                            </label>
                            <div>
                                <textarea name="MAXIPAGO_BOLETO_INSTRUCTIONS"id="MAXIPAGO_BOLETO_INSTRUCTIONS" class="form-control">{$MAXIPAGO_BOLETO_INSTRUCTIONS}</textarea>
                            </div>
                        </div>
                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_BOLETO_DISCOUNT">
                                {l s='Desconto para pagamento no boleto (%)' mod='maxipago'}</label>
                            <div>
                                <input type="text" name="MAXIPAGO_BOLETO_DISCOUNT"
                                       value="{$MAXIPAGO_BOLETO_DISCOUNT}"
                                       id="MAXIPAGO_BOLETO_DISCOUNT" class="form-control percent"/>
                            </div>
                        </div>

                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_BOLETO_BANK">
                                {l s='Banco' mod='maxipago'}
                            </label>
                            <div>
                                <select name="MAXIPAGO_BOLETO_BANK" class="" id="MAXIPAGO_BOLETO_BANK" title="">
                                    {foreach from=$banks key=k item=v}
                                        <option value="{$k}" {if $MAXIPAGO_BOLETO_BANK eq $k} selected {/if}>
                                            {l s=$v mod='maxipago'}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane" id="tab-tef">
                <div class="form-group required">
                    <h3 class="col-sm-12">
                        <span><strong>{l s='Transferência Eletrônica' mod='maxipago'}</strong></span>
                    </h3>

                    <label class="col-sm-3 control-label" for="MAXIPAGO_TEF_ENABLED">
                        {l s='Habilitar' mod='maxipago'}
                    </label>

                    <div class="col-sm-9">
                        <div class="pull-left admin-item-form">
                            <input type="radio" name="MAXIPAGO_TEF_ENABLED"
                                   value="1" {if $MAXIPAGO_TEF_ENABLED} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-left">
                            {l s='Sim' mod='maxipago'}
                        </div>
                        <div class="pull-left admin-item-form">
                            <input type="radio" name="MAXIPAGO_TEF_ENABLED"
                                   value="0" {if !$MAXIPAGO_TEF_ENABLED} checked {/if}>
                        </div>
                        <div class="pull-left admin-item-right">
                            {l s='Não' mod='maxipago'}
                        </div>
                    </div>

                </div>

                <div class="form-group admin-detail-config">
                    <div class="col-sm-3 control-label">{l s='Configurações' mod='maxipago'}</div>
                    <div class="col-sm-9 admin-detail-background">
                        <div class="form-group admin-detail-content">
                            <p class="warning">
                                {l s='É preciso configurar no maxiPago! as URLs de sucesso e falha para transferência eletrônica' mod='maxipago'}
                                <br>
                                {l s='A mesma URL deve ser configurada nos dois casos:' mod='maxipago'}
                                <br>
                                {$tef_url}
                            </p>
                        </div>
                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_TEF_BANKS">
                                {l s='Bancos' mod='maxipago'}
                            </label>
                            <div>
                                <select name="MAXIPAGO_TEF_BANKS[]" class="" multiple id="MAXIPAGO_TEF_BANKS" title="">
                                    {foreach $tef_banks as $k => $v}
                                        <option value="{$k}" {if $k|in_array:$MAXIPAGO_TEF_BANKS} selected {/if}>
                                            {l s=$v mod='maxipago'}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>

                        <div class="form-group admin-detail-content">
                            <label class="control-label" for="MAXIPAGO_TEF_DISCOUNT">
                                {l s='Desconto para pagamento com Transferência Eletrônica (%)' mod='maxipago'}</label>
                            <div>
                                <input type="text" name="MAXIPAGO_TEF_DISCOUNT"
                                       value="{$MAXIPAGO_TEF_DISCOUNT}"
                                       id="MAXIPAGO_TEF_DISCOUNT" class="form-control percent"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="divSalvar">
            <input type="submit" class="btn btn-success admin-btn" name='btnSubmit' value="{l s='Salvar Alterações' mod='maxipago'}"/>
        </div>

    </form>
</div>
<script>
    function mpCCCanSave() {
        if ($('select#MAXIPAGO_CC_PROCESSING_TYPE').val() == 'auth') {
            $('input#MAXIPAGO_CC_CAN_SAVE_0').click();
            $('div.row-cc-can-save').hide();
        } else {
            $('div.row-cc-can-save').show();
        }
    }
    function mpFraudCheck() {
        if ($('select#MAXIPAGO_CC_PROCESSING_TYPE').val() == 'sale') {
            $('input#MAXIPAGO_CC_FRAUD_CHECK_0').click();
            $('div.row-fraud-check').hide();
        } else {
            $('div.row-fraud-check').show();
        }
    }

    $('select#MAXIPAGO_CC_PROCESSING_TYPE').change(function(){
        mpCCCanSave();
        mpFraudCheck();
    });

    mpCCCanSave();
    mpFraudCheck();
</script>
