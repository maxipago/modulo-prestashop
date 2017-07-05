{*
** @author PrestaShop SA <contact@prestashop.com>
** @copyright  2007-2014 PrestaShop SA
**
*}
<br />
<div class="table_block">
    <fieldset>
        <legend><img src="{$module_dir}logo.png" alt="" /> {l s='Dados do Pagamento' mod='maxipago'}</legend>
        <table cellpadding="0" cellspacing="0" class="detail_step_by_step table table-bordered">

            {if $method == 'tef'}
                <tr>
                    <td>{l s='Method' mod='maxipago'}</td>
                    <td>{l s='maxiPago! - TEF' mod='maxipago'}</td>
                </tr>
                <tr>
                    <td>
                        {l s='Status:' mod='maxipago'}
                    </td>
                    <td>
                        {$status}
                    </td>
                </tr>
                {if $return->onlineDebitUrl && $response_message == 'PENDING'}
                    <tr>
                        <td>{l s='Link para pagamento' mod='maxipago'}</td>
                        <td>
                            <a href="{$return->onlineDebitUrl}" target="_blank">{l s='Clique aqui para finalizar seu pedido!' mod='maxipago'}</a>
                        </td>
                    </tr>
                {/if}
            {else}
                {if $method == 'card'}
                    <tr>
                        <td>{l s='Method' mod='maxipago'}</td>
                        <td>{l s='maxiPago! - Cartão de Crédito' mod='maxipago'}</td>
                    </tr>
                    <tr>
                        <td>
                            {l s='Status:' mod='maxipago'}
                        </td>
                        <td>
                            {$status}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {l s='Parcelas:' mod='maxipago'}
                        </td>
                        <td>
                            {$installments=$request->chargeTotal / $request->numberOfInstallments}
                            {$request->numberOfInstallments} x {l s='de' mod='maxipago'} {displayPrice price=$installments} - {l s='Total: ' mod='maxipago'} {displayPrice price=$request->chargeTotal}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {l s='Cartão:' mod='maxipago'}
                        </td>
                        <td>
                            {if $request->brand}
                                {$request->brand} -
                            {/if}
                            {$request->number}
                        </td>
                    </tr>

                    <tr>
                        <td>{l s='ID da transação' mod='maxipago'}</td>
                        <td>{$return->transactionID|escape:'htmlall':'UTF-8'}</td>
                    </tr>

                {elseif $method == 'boleto'}
                    <tr>
                        <td>{l s='Method' mod='maxipago'}</td>
                        <td>{l s='maxiPago! - Boleto Bancário' mod='maxipago'}</td>
                    </tr>
                    {if $return->boletoUrl}
                        <tr>
                            <td>{l s='Link do Boleto' mod='maxipago'}</td>
                            <td>
                                <a href="{$return->boletoUrl}" target="_blank">{l s='Boleto' mod='maxipago'}</a>
                            </td>
                        </tr>
                    {/if}
                {/if}
            {/if}

        </table>
    </fieldset>
</div>