<?php
/*
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
*/

/**
 * @since 1.5.0
 */
class MaxipagoPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = true;

    const CC_BRANDS = array('VISA', 'MASTERCARD', 'AMEX', 'DINERS', 'ELO', 'DISCOVER', 'HIPERCARD');
    const TEF_BANKS = array('17' => 'Bradesco', '18' => 'Itaú');

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        $customer_fields = $this->context->customer->getFields();

        if (!$this->module->checkCurrency($cart))
            Tools::redirect('index.php?controller=order');

        $years = array();
        $actual_year = intval(date("Y"));
        $last_year = $actual_year + 15;
        for ($y = $actual_year; $y <= $last_year; $y++) {
            array_push($years, $y);
        }

        $this->context->controller->addJS($this->module->getPathUri() . '/assets/js/maxipago.js?version=' . $this->module->maxiPagoVersion);
        $this->context->controller->addJS($this->module->getPathUri() . '/assets/js/jquery.mask.min.js');

        $address_invoice = new Address((Integer)$cart->id_address_invoice);

        if (isset($customer_fields['document'])) {
            $cpf = $customer_fields['document'];
        } else if (isset($customer_fields['cpf'])) {
            $cpf = $customer_fields['cpf'];
        } else {
            $cpf = '';
        }

        $total_order = $cart->getOrderTotal(true);

        $boleto_discount = floatval(preg_replace('/[^0-9.]/', '', str_replace(",", ".", Configuration::get('MAXIPAGO_BOLETO_DISCOUNT'))));
        $boleto_discount = str_replace('.', ',', $boleto_discount);
        $total_boleto = (float)($total_order * (1 - ($boleto_discount / 100)));

        $tef_discount = floatval(preg_replace('/[^0-9.]/', '', str_replace(",", ".", Configuration::get('MAXIPAGO_TEF_DISCOUNT'))));
        $tef_discount = str_replace('.', ',', $tef_discount);
        $total_tef = (float)($total_order * (1 - ($tef_discount / 100)));


        if (isset($_SERVER['HTTPS'])) {
            $base_url_dir = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/';
        } else {
            $base_url_dir = Tools::getShopDomain(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/';
        }

        $currency = new Currency($cart->id_currency);

        $cc_brands = self::CC_BRANDS;
        for($i = 0; $i < count($cc_brands); $i++) {
            if (!Configuration::get('MAXIPAGO_' . $cc_brands[$i] . '_PROCESSOR')) {
                unset($cc_brands[$i]);
            }
        }

        $tef_banks = self::TEF_BANKS;
        $banks = explode(',', Configuration::get('MAXIPAGO_TEF_BANKS'));
        foreach (array_keys($tef_banks) as $bank) {
            if (!in_array($bank, $banks)) {
                unset($tef_banks[$bank]);
            }
        }

        $cpf = "";

        if (isset($customer_fields['document'])) {
            $cpf = $customer_fields['document'];
        } else if (isset($customer_fields['cpf'])) {
            $cpf = $customer_fields['cpf'];
        }

        //Saved Cards
        $sql = 'SELECT *
        FROM ' . _DB_PREFIX_ . 'maxipago_cc_token
        WHERE `id_customer` = \'' . pSQL($cart->id_customer) . '\'
                        ';
        $saved_cards = Db::getInstance()->executeS($sql);

        $canSave = Configuration::get('MAXIPAGO_CC_CAN_SAVE');
        if (Configuration::get('MAXIPAGO_CC_PROCESSING_TYPE') == 'auth') {
            $canSave = false;
        }

        $this->context->smarty->assign(array(
            'base_url_dir' => $base_url_dir,
            'nbProducts' => $cart->nbProducts(),
            'currencies' => $this->module->getCurrency((int)$cart->id_currency),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'billing_cpf' => $cpf,
            'path_uri' => $this->module->getPathUri(),
            'width_center_column' => '100%',
            'cc_can_save' => Configuration::get('MAXIPAGO_CC_CAN_SAVE'),
            'sandbox' => Configuration::get('MAXIPAGO_SANDBOX'),
            'processing_type' => Configuration::get('MAXIPAGO_CC_PROCESSING_TYPE'),
            'card_option' => Configuration::get('MAXIPAGO_CC_ENABLED'),
            'boleto_option' => Configuration::get('MAXIPAGO_BOLETO_ENABLED'),
            'tef_option' => Configuration::get('MAXIPAGO_TEF_ENABLED'),
            'boleto_discount' => $boleto_discount,
            'total_boleto' => Tools::displayPrice($total_boleto, $currency),
            'tef_discount' => $tef_discount,
            'total_tef' => Tools::displayPrice($total_tef, $currency),
            'cc_brands' => $cc_brands,
            'saved_cards' => $saved_cards,
            'tef_banks' => $tef_banks,
            'installment' => $this->getInstallment($cart->getOrderTotal(true, Cart::BOTH)),
            'installments' => $this->getInstallments($cart->getOrderTotal(true, Cart::BOTH)),
            'years' => $years
        ));

        $this->setTemplate('payment.tpl');

    }

    public function getInstallmentPrice($price, $installments, $interestRate)
    {
        return $this->module->getInstallmentPrice($price, $installments, $interestRate);
    }

    public function getInstallment($price = null)
    {
        return $this->module->getInstallment($price);

    }

    public function getInstallments($price = null)
    {
        return $this->module->getInstallments($price);
    }

}
