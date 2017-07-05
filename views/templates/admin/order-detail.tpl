{*
** @author PrestaShop SA <contact@prestashop.com>
** @copyright  2007-2014 PrestaShop SA
**
*}
<br />
<div class="panel">
    <fieldset>
        <legend><img src="{$module_dir}logo.png" alt="" /> {l s='Dados do Pagamento' mod='maxipago'}</legend>
        <table cellpadding="0" cellspacing="0" class="table">

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

                {if $return->onlineDebitUrl}
                    <tr>
                        <td>{l s='Link para pagamento' mod='maxipago'}</td>
                        <td>
                            <a href="{$return->onlineDebitUrl}" target="_blank">{$return->onlineDebitUrl}</a>
                        </td>
                    </tr>
                {/if}

                {if $response_message == 'PENDING' || $response_message == 'PENDING CONFIRMATION'}
                    <tr>
                        <td>{l s='Ações' mod='maxipago'}</td>
                        <td>
                            <a href="{$update_url}">{l s='Atualizar Status do Pedido' mod='maxipago'}</a>
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
                            {$status} - ({$response_message})
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

                    {if $response_message == 'AUTHORIZED'}
                        <tr>
                            <td>{l s='Ações' mod='maxipago'}</td>
                            <td>
                                <a href="{$update_url}">{l s='Atualizar Status do Pedido' mod='maxipago'}</a>
                            </td>
                        </tr>
                    {/if}

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

                    {if $response_message == 'BOLETO ISSUED'  || $response_message == 'BOLETO VIEWED'}
                        <tr>
                            <td>{l s='Ações' mod='maxipago'}</td>
                            <td>
                                <a href="{$update_url}">{l s='Atualizar Status do Pedido' mod='maxipago'}</a>
                            </td>
                        </tr>
                    {/if}
                {/if}

                {if property_exists($return, 'orderID')}
                    <tr>
                        <td>{l s='ID da encomenda no maxiPago!' mod='maxipago'}</td>
                        <td>{$return->orderID|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/if}
            {/if}

        </table>

    </fieldset>

</div>