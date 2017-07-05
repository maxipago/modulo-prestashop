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
class MaxipagoValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @var Maxipago _module;
     */

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $cart = $this->context->cart;
        $method = Tools::getValue('method');

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
            Tools::redirect('index.php?controller=order&step=1');

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer))
            Tools::redirect('index.php?controller=order&step=1');

        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'maxipago') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $currency = $this->context->currency;

        switch ($method) {
            case 'boleto':
                $displayName = $this->module->displayName . ' - ' . $this->module->l('Boleto Bancário');
                $boletoDiscount = floatval(preg_replace('/[^0-9.]/', '', str_replace(",", ".", Configuration::get('MAXIPAGO_BOLETO_DISCOUNT'))));
                if (floatval($boletoDiscount) > 0) {
                    $amountDiscount = (float)($total * ($boletoDiscount / 100));
                    $cartRule = $this->_createDiscount($cart->id_customer, $amountDiscount, 'Boleto');
                    $cart->addCartRule($cartRule->id);
                    $total = (float)$cart->getOrderTotal(true);
                }
                break;
            case 'card':
                $displayName = $this->module->displayName . ' - ' . $this->module->l('Cartão de Crédito');
                break;
            case 'tef':
                $displayName = $this->module->displayName . ' - ' . $this->module->l('TEF');
                $tefDiscount = floatval(preg_replace('/[^0-9.]/', '', str_replace(",", ".", Configuration::get('MAXIPAGO_TEF_DISCOUNT'))));
                if (floatval($tefDiscount) > 0) {
                    $amountDiscount = (float)($total * ($tefDiscount / 100));
                    $cartRule = $this->_createDiscount($cart->id_customer, $amountDiscount, 'TEF');
                    $cart->addCartRule($cartRule->id);
                    $total = (float)$cart->getOrderTotal(true);
                }
                break;
        }


        $this->module->validateOrder($cart->id, Configuration::get('MAXIPAGO_ORDER_CODE_PENDING'), $total, $this->module->displayName, NULL, NULL, NULL, false, $customer->secure_key);

        switch ($method) {
            case 'boleto':
                $this->_boletoMethod($total);
                break;
            case 'card':
                $this->_cardMethod($total);
                break;
            case 'tef':
                $response = $this->_tefMethod($total);
                break;
        }

        Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key);
    }

    /**
     * Send the payment method Boleto to maxiPago!
     */
    protected function _boletoMethod($totalOrder)
    {
        $cart = $this->context->cart;

        //Boleto
        $methodEnabled = Configuration::get('MAXIPAGO_BOLETO_ENABLED');
        if ($methodEnabled) {

            $isSandbox = Configuration::get('MAXIPAGO_SANDBOX');

            $dayToExpire = (int) Configuration::get('MAXIPAGO_BOLETO_DAYS_TO_EXPIRE');
            $instructions = Configuration::get('MAXIPAGO_BOLETO_INSTRUCTIONS');

            $date = new DateTime();
            $date->modify('+' . $dayToExpire . ' days');
            $expirationDate = $date->format('Y-m-d');

            $boletoBank = $isSandbox ? 12 : Configuration::get('MAXIPAGO_BOLETO_BANK');
            $ipAddress = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;

            $address = $this->_getAddress($cart);
            $customer = new Customer($cart->id_customer);

            $orderId = (string) $this->module->currentOrder;

            $data = array(
                'referenceNum' => $orderId, //Order ID
                'processorID' => $boletoBank, //Bank Number
                'ipAddress' => $ipAddress,
                'chargeTotal' => $totalOrder,
                'expirationDate' => $expirationDate,
                'number' => $orderId, //Our Number
                'instructions' => $instructions, //Instructions
                'bname' => $customer->firstname . ' ' . $customer->lastname,
                'baddress' => $address['address1'],
                'baddress2' => $address['address2'],
                'bcity' => $address['city'],
                'bstate' => $address['state'],
                'bpostalcode' => $address['postcode'],
                'bcountry' => $address['postcode'],
                'bemail' => $customer->email,
            );

            $this->module->getMaxipago()->boletoSale($data);
            //Log
            $this->module->log(htmlspecialchars($this->module->getMaxipago()->xmlRequest));
            $this->module->log(htmlspecialchars($this->module->getMaxipago()->xmlResponse));

            $response = $this->module->getMaxipago()->response;

            $boletoUrl = isset($response['boletoUrl']) ? $response['boletoUrl'] : null;
            if (!$boletoUrl) {
                $error = isset($response['errorMessage']) ? $response['errorMessage'] : null;
                die($this->module->l($error));
            }
            $this->_saveTransaction('boleto', $data, $response, $boletoUrl);
        }

    }

    /**
     * Send the payment method Credit Card to maxiPago!
     */
    protected function _cardMethod($totalOrder)
    {
        $cart = $this->context->cart;

        $methodEnabled = Configuration::get('MAXIPAGO_CC_ENABLED');

        if ($methodEnabled) {

            $id_order = (string) $this->module->currentOrder;

            $softDescriptor = Configuration::get('MAXIPAGO_SOFT_DESCRIPTOR');
            $processingType = Configuration::get('MAXIPAGO_CC_PROCESSING_TYPE'); //auth || sale

            $fraudCheck = (Configuration::get('MAXIPAGO_CC_FRAUD_CHECK')) ? 'Y' : 'N';
            $fraudCheck = $processingType != 'sale' ? $fraudCheck : 'N';

            $maxWithoutInterest = (int) Configuration::get('MAXIPAGO_CC_MAX_WITHOUT_INTEREST');
            $interestRate = Configuration::get('MAXIPAGO_CC_INTEREST_RATE');
            $hasInterest = 'N';

            $cpf = Tools::getValue('payment-card-cpf');
            $installments = Tools::getValue('payment-card-installments');
            $owner = Tools::getValue('payment-card-owner');
            $brand = Tools::getValue('payment-card-brand');
            $ipAddress = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;

            if ($interestRate && $installments > $maxWithoutInterest) {
                $hasInterest = 'Y';
                $totalOrder = $this->module->getTotalByInstallments($totalOrder, $installments, $interestRate);
            }

            $cardSaved = Tools::getValue('payment-card-saved');

            if ($cardSaved) {
                $cvvSaved = Tools::getValue('payment-card-cvv-saved');

                $sql = 'SELECT *
                        FROM ' . _DB_PREFIX_ . 'maxipago_cc_token
                        WHERE `id_customer` = \'' . pSQL($cart->id_customer) . '\'
                        AND `description` = \'' . $cardSaved . '\'; ';
                $maxipagoToken = Db::getInstance()->getRow($sql);

                $processorID =  Configuration::get('MAXIPAGO_' . strtoupper($maxipagoToken['brand']) . '_PROCESSOR');

                $data = array(
                    'customerId' => $maxipagoToken['id_customer_maxipago'],
                    'token' => $maxipagoToken['token'],
                    'cvvNumber' => $cvvSaved,
                    'referenceNum' => $id_order, //Order ID
                    'processorID' => $processorID, //Processor
                    'ipAddress' => $ipAddress,
                    'fraudCheck' => $fraudCheck,
                    'currencyCode' => $this->context->currency->iso_code,
                    'chargeTotal' => $totalOrder,
                    'numberOfInstallments' => $installments,
                    'chargeInterest' => $hasInterest
                );

            } else {

                $processorID =  Configuration::get('MAXIPAGO_' . $brand . '_PROCESSOR');

                $number = Tools::getValue('payment-card-number');
                $expMonth = Tools::getValue('payment-card-expiration-month');
                $expYear = Tools::getValue('payment-card-expiration-year');
                $cvv = Tools::getValue('payment-card-cvv');

                $saveCard = Tools::getValue('payment-card-save');

                $address = $this->_getAddress($cart);
                $customer = new Customer($cart->id_customer);

                $data = array(
                    'referenceNum' => $id_order, //Order ID
                    'processorID' => $processorID, //Processor
                    'ipAddress' => $ipAddress,
                    'fraudCheck' => $fraudCheck,
                    'number' => $number,
                    'expMonth' => $expMonth,
                    'expYear' => $expYear,
                    'cvvNumber' => $cvv,
                    'currencyCode' => $this->context->currency->iso_code,
                    'chargeTotal' => $totalOrder,
                    'numberOfInstallments' => $installments,
                    'chargeInterest' => $hasInterest
                );

                if ($saveCard) {

                    $sql = 'SELECT *
                        FROM ' . _DB_PREFIX_ . 'maxipago_cc_token
                        WHERE `id_customer` = \'' . pSQL($cart->id_customer) . '\'
                        ';
                    $mpCustomer = Db::getInstance()->getRow($sql);

                    if (!$mpCustomer) {
                        $customerData = array(
                            'customerIdExt' => $cart->id_customer,
                            'firstName' => $customer->firstname,
                            'lastName' => $customer->lastname
                        );
                        $this->module->getMaxipago()->addProfile($customerData);
                        $mpCustomerId = $this->module->getMaxipago()->getCustomerId();
                    } else {
                        $mpCustomerId = $mpCustomer['id_customer_maxipago'];
                    }

                    $address = $this->_getAddress($cart);

                    $date = new DateTime($expYear . '-' . $expMonth . '-01');
                    $date->modify('+1 month');
                    $endDate = $date->format('m/d/Y');

                    $ccData = array(
                        'customerId' => $mpCustomerId,
                        'creditCardNumber' => $number,
                        'expirationMonth' => $expMonth,
                        'expirationYear' => $expYear,
                        'billingName' => $customer->firstname . ' ' . $customer->lastname,
                        'billingAddress1' => $address['address1'],
                        'billingAddress2' => $address['address2'],
                        'billingCity' => $address['city'],
                        'billingState' => $address['state'],
                        'billingZip' => $address['postcode'],
                        'billingPhone' => $address['phone'],
                        'billingEmail' => $customer->email,
                        'onFileEndDate' => $endDate,
                        'onFilePermissions' => 'ongoing',
                    );
                    $this->module->getMaxipago()->addCreditCard($ccData);
                    $token = $this->module->getMaxipago()->getToken();
                    $this->_saveTransaction('save_card', $ccData, $this->module->getMaxipago()->response, null, false);

                    if ($token) {
                        $ccEnc = substr($number, 0, 6) . 'XXXXXX' . substr($number, -4, 4);
                        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'maxipago_cc_token` 
                            (`id_customer`, `id_customer_maxipago`, `brand`, `token`, `description`)
                        VALUES
                            ("' . $customer->id . '", "' . $mpCustomerId . '", "' . $brand . '", "' . $token . '", "' . $ccEnc . '" )
                        ';

                        if (!Db::getInstance()->Execute($sql)) {
                            die('maxiPago! Não foi possível salvar o cartão de crédito.');
                        }
                    }
                }

            }

            if (Configuration::get('MAXIPAGO_CC_PROCESSING_TYPE') == 'auth') {
                $this->module->getMaxipago()->creditCardAuth($data);

                //pending

            } else {
                $this->module->getMaxipago()->creditCardSale($data);
            }

            //Log
            $this->module->log(htmlspecialchars($this->module->getMaxipago()->xmlRequest));
            $this->module->log(htmlspecialchars($this->module->getMaxipago()->xmlResponse));

            $response = $this->module->getMaxipago()->response;
            $this->_saveTransaction('card', $data, $response);

        }

    }

    /**
     * Send the payment method TEF to maxiPago!
     */
    protected function _tefMethod($totalOrder)
    {
        $response = null;
        $methodEnabled = Configuration::get('MAXIPAGO_TEF_ENABLED');

        if ($methodEnabled) {
            $cart = $this->context->cart;

            $isSandbox = Configuration::get('MAXIPAGO_SANDBOX');

            $tefBank = $isSandbox ? 17 : Tools::getValue('payment-tef-bank');
            $cpf = Tools::getValue('payment-tef-cpf');
            $ipAddress = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;

            $address = $this->_getAddress($cart);
            $customer = new Customer($cart->id_customer);

            $data = array(
                'referenceNum' => (string) $this->module->currentOrder, //Order ID
                'processorID' => $tefBank, //Bank Number
                'ipAddress' => $ipAddress,
                'chargeTotal' => $totalOrder,
                'customerIdExt' => $cpf,
                'name' => $customer->firstname . ' ' . $customer->lastname,
                'address' => $address['address1'], //Address 1
                'address2' => $address['address2'], //Address 2
                'city' => $address['city'],
                'state' => $address['state'],
                'postalcode' => $address['postcode'],
                'country' => $address['country'],
                'parametersURL' => 'oid=' . $this->module->currentOrder
            );

            $this->module->getMaxipago()->onlineDebitSale($data);

            //Log
            $this->module->log(htmlspecialchars($this->module->getMaxipago()->xmlRequest));
            $this->module->log(htmlspecialchars($this->module->getMaxipago()->xmlResponse));

            $response = $this->module->getMaxipago()->response;

            $onlineDebitUrl = isset($response['onlineDebitUrl']) ? $response['onlineDebitUrl'] : null;
            if (!$onlineDebitUrl) {
                $error = isset($response['errorMessage']) ? $response['errorMessage'] : null;
                die($this->module->l($error));
            }
            $this->_saveTransaction('tef', $data, $response, $onlineDebitUrl);

        }

    }

    protected function _getAddress($cart)
    {
        $invoiceAddress = new Address((int) $cart->id_address_invoice);

        $address2 = $invoiceAddress->address2;
        if (property_exists($invoiceAddress, 'address3')) {
            $address2 .= ' ' . $invoiceAddress->address3;
        }
        if (property_exists($invoiceAddress, 'address4')) {
            $address2 .= ' ' . $invoiceAddress->address4;
        }

        $state = "";
        if (isset($address->id_state)) {
            $state = State::getNameById($invoiceAddress->id_state);
        }

        return array(
            'address1' => $invoiceAddress->address1,
            'address2' => $address2,
            'state' => $state,
            'city' => $invoiceAddress->city,
            'postcode' => $invoiceAddress->postcode,
            'country' => $invoiceAddress->country,
            'phone' => $invoiceAddress->phone,
        );
    }

    /**
     * Creates a cart rule to discount in Boleto an TEF payment methods
     * @param $id_customer
     * @param $amount
     * @param string $type
     * @return CartRule
     */
    protected function _createDiscount($id_customer, $amount, $type = 'Boleto')
    {
        $cartRule = new CartRule();
        $languages = Language::getLanguages();

        foreach ($languages as $key => $language) {
            $array[$language['id_lang']]= 'Desconto por pagamento à vista (' . $type . ')';
        }

        $cartRule->name = $array;
        $cartRule->description = $this->module->l('Desconto por pagamento à vista (' . $type . ')', 'validation');
        $cartRule->id_customer = $id_customer;
        $cartRule->active = 1;
        $cartRule->date_from = date('Y-m-d 00:00:00');
        $cartRule->date_to = date('Y-m-d h:i:s', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
        $cartRule->minimum_amount = 1;
        $cartRule->minimum_amount_currency = 1;
        $cartRule->quantity = 1;
        $cartRule->quantity_per_user = 1;
        $cartRule->reduction_tax = 1;
        $cartRule->reduction_amount = floatval($amount);
        $cartRule->add();

        return $cartRule;
    }

    /**
     * Save at the DB the data of the transaction and the Boleto URL when the payment is made with boleto
     *
     * @param $method
     * @param $request
     * @param $return
     * @param null $transactionUrl
     * @param boolean $hasOrder
     */
    protected function _saveTransaction($method, $request, $return, $transactionUrl = null, $hasOrder = true)
    {
        $onlineDebitUrl = null;
        $boletoUrl = null;

        if ($transactionUrl) {
            if ($method == 'tef') {
                $onlineDebitUrl = $transactionUrl;
            } else if ($method == 'boleto') {
                $boletoUrl = $transactionUrl;
            }
        }

        if (is_object($request) || is_array($request)) {

            if (isset($request['number'])) {
                $request['number'] = substr($request['number'], 0, 6) . 'XXXXXX' . substr($request['number'], -4, 4);
            }

            if (Tools::getValue('payment-card-brand')) {
                $request['brand'] = strtoupper(Tools::getValue('payment-card-brand'));
            }

            $request = json_encode($request);
        }

        $responseMessage = null;
        if (is_object($return) || is_array($return)) {
            $responseMessage = isset($return['responseMessage']) ? $return['responseMessage'] : null;
            $return = json_encode($return);
        }

        $id_order = $this->module->currentOrder;
        if (! $hasOrder) {
            $id_order = 0;
        }

        //Log only if debug is active
        $this->module->log($method);
        $this->module->log($request);
        $this->module->log($return);

        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'maxipago_transactions` 
                    (`id_order`, `boleto_url`, `online_debit_url`, `method`, `request`, `return`, `response_message`)
                VALUES
                    ("' . pSQL($id_order) . '", "' . pSQL($boletoUrl) . '",  "' . pSQL($onlineDebitUrl) . '", "' . pSQL($method) . '" ,"' . pSQL($request) . '", "' . pSQL($return) . '", "' . $responseMessage . '" )
                ';

        if (! Db::getInstance()->Execute($sql)) {
            die('maxiPago! Não foi possível salvar o cartão de crédito.');
        }
    }
}
