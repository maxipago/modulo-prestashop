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

<link rel="stylesheet" type="text/css" href="{$this_path}assets/css/checkout.css"/>
{if $method == 'boleto' && !$boleto_url}
    <p class="warning">
        {l s='Houve um problema ao gerar seu pedido, entre em contato com nossa equipe para mais informações' mod='maxipago'}
        <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='nossa equipe' mod='maxipago'}</a>.
    </p>
{else}

    <p class="alert alert-success">{l s='Seu Pedido em %s está completo.' sprintf=$shop_name mod='maxipago'}</p>

    <div class="pull-left mp-left-space-2">
        <img src="{$this_path}assets/images/maxiPago.jpg" alt="maxiPago!" title="maxiPago!"/>
    </div>

    <div>
        <div class="mp-success-payment">
           <div class="mp-success-payment-inside-box">
                <div class="mp-row mp-col-12">

                    <div class="mp-col-1">
                        <div class="mp-icon-emission-success">
                            <span class="icon-check-circle-o"></span>
                        </div>
                    </div>

                    <div class="mp-col-11">
                        {if !isset($reference)}
                            {l s='Seu pedido #%d foi realizado com sucesso e enviamos um e-mail contendo todas informações sobre o pedido para você.' sprintf=$id_order mod='maxipago'}
                        {else}
                            {l s='Seu pedido %s foi realizado com sucesso e enviamos um e-mail contendo todas informações sobre o pedido para você.' sprintf=$reference mod='maxipago'}
                        {/if}
                    </div>
                </div>

               <div class="mp-col-12">
                   {if $method == 'boleto'}
                       {l s='O Boleto Bancário foi gerado com sucesso. Efetue o pagamento em qualquer banco conveniado, lotéricas, correios ou bankline. Fique atento à data de vencimento do boleto.' mod='maxipago'}

                        <div class="mp-alimp-center">
                            <button id="show_boleto" class="button btn btn-default standard-checkout button-medium" onclick="window.open('{$boleto_url}', '_blank');">
                                <div class="mp-success-payment-button-icon pull-left"><i class="icon-download"></i></div>
                                <div class="pull-left mp-button-with-icon">Visualizar Boleto</div>
                                <div class="clear"></div>
                            </button>
                        </div>
                    {elseif $method == 'card'}

                       {if $response->responseMessage == 'INVALID'}
                           <p>{l s='Infelizmente sua compra não foi processada' sprintf=$status mod='maxipago'}</p>
                       {else}
                            <p>{l s='Sua compra foi processada' mod='maxipago'}</p>
                       {/if}

                       <ul>
                            <li>
                                {l s='Status: %s' sprintf=$status mod='maxipago'}
                            </li>
                            <li>
                                {$installments=$request->chargeTotal / $request->numberOfInstallments}
                                {$request->numberOfInstallments} x {l s='de' mod='maxipago'} {displayPrice price=$installments} - {l s='Total: ' mod='maxipago'} {displayPrice price=$request->chargeTotal}
                            </li>
                            <li>
                                {l s='Cartão:' mod='maxipago'}
                                {if $request->brand}
                                    {$request->brand} -
                                {/if}
                                {$request->number}
                            </li>
                            <li>
                                {l s='ID Transação:' mod='maxipago'} {$response->transactionID}
                            </li>
                        </ul>
                    {elseif $method == 'tef'}
                        <p>
                            {l s='Transferência concluída, assim que o valor for confirmado em nossa conta, seu produto será despachado.' mod='maxipago'}
                        </p>
                    {/if}
               </div>

               {l s='Se tiver dúvidas, por favor, entre em contato com a nossa ' mod='maxipago'}
               <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='equipe de atendimento' mod='maxipago'}</a>.
            </div>
        </div>
    </div>

{/if}
