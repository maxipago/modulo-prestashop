<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @author    maxiPago
 * @copyright Copyright (c) maxiPago [http://www.maxipago.com.br]
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

include_once dirname(__FILE__) . '/lib/maxiPago.php';

if (!defined('_PS_VERSION_'))
    exit;

class Maxipago extends PaymentModule
{

    public $maxiPagoVersion = '0.2.0';
    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;
    public $mp_url;
    protected $_html = '';
    protected $_postErrors = array();
    protected $_maxiPago;

    /**
     * Order states to create when enable the module
     */
    protected static $order_states = array(
        'new' => array(
            'name' => 'Nova Encomenda',
            'send_email' => false,
            'color' => '#0B8FBB',
            'template' => '',
            'hidden' => false,
            'delivery' => false,
            'logable' => false,
            'invoice' => false,
            'unremovable' => false,
            'shipped' => false,
            'paid' => false
        ),
        'pending' => array(
            'name' => 'Aguardando Pagamento',
            'send_email' => true,
            'color' => '#054DA4',
            'template' => '',
            'hidden' => false,
            'delivery' => false,
            'logable' => false,
            'invoice' => false,
            'unremovable' => false,
            'shipped' => false,
            'paid' => false
        ),
        'paid' => array(
            'name' => 'Pagamento Confirmado',
            'send_email' => true,
            'color' => '#469616',
            'template' => 'payment',
            'hidden' => false,
            'delivery' => false,
            'logable' => true,
            'invoice' => true,
            'unremovable' => false,
            'shipped' => false,
            'paid' => true
        ),
        'refunded' => array(
            'name' => 'Pagamento devolvido',
            'send_email' => true,
            'color' => '#AA921B',
            'template' => 'refund',
            'hidden' => false,
            'delivery' => false,
            'logable' => false,
            'invoice' => false,
            'unremovable' => false,
            'shipped' => false,
            'paid' => false
        ),
        'canceled' => array(
            'name' => 'Pedido Cancelado',
            'send_email' => true,
            'color' => '#D71B2D',
            'template' => 'order_canceled',
            'hidden' => false,
            'delivery' => false,
            'logable' => false,
            'invoice' => false,
            'unremovable' => false,
            'shipped' => false,
            'paid' => false
        )
    );

    protected $_responseCodes = array(
        '0' => 'Pagamento Aprovado',
        '1' => 'Pagamento Reprovado',
        '2' => 'Pagamento Reprovado',
        '5' => 'Pagamento em análise',
        '1022' => 'Ocorreu um erro com a finalizadora, entre em contato com nossa equipe',
        '1024' => 'Erros, dados enviados inválidos, entre em contato com nossa equipe',
        '1025' => 'Erro nas credenciais de envio, entre em contato com nossa equipe',
        '2048' => 'Erro interno, entre em contato com nossa equipe',
        '4097' => 'Erro de tempo de execução, entre em contato com nossa equipe'
    );

    protected $_transactionStates = array(
        '1' => 'In Progress',
        '3' => 'Captured',
        '6' => 'Authorized',
        '7' => 'Declined',
        '9' => 'Voided',
        '10' => 'Paid',
        '22' => 'Boleto Issued',
        '34' => 'Boleto Viewed',
        '35' => 'Boleto Underpaid',
        '36' => 'Boleto Overpaid',

        '4' => 'Pending Capture',
        '5' => 'Pending Authorization',
        '8' => 'Reversed',
        '11' => 'Pending Confirmation',
        '12' => 'Pending Review (check with Support)',
        '13' => 'Pending Reversion',
        '14' => 'Pending Capture (retrial)',
        '16' => 'Pending Reversal',
        '18' => 'Pending Void',
        '19' => 'Pending Void (retrial)',
        '29' => 'Pending Authentication',
        '30' => 'Authenticated',
        '31' => 'Pending Reversal (retrial)',
        '32' => 'Authentication in progress',
        '33' => 'Submitted Authentication',
        '38' => 'File submission pending Reversal',
        '44' => 'Fraud Approved',
        '45' => 'Fraud Declined',
        '46' => 'Fraud Review'
    );

    public function __construct()
    {
        $this->name = 'maxipago';
        $this->tab = 'payments_gateways';
        $this->version = '0.1.0';
        $this->author = 'maxiPago!';
        $this->controllers = array('payment', 'validation');
        $this->is_eu_compatible = 1;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->link = new Link();

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('maxiPago!');
        $this->description = $this->l('Receba pagamentos com maxiPago! usando Cartão de Crédito e Boleto Bancário!');
        $this->confirmUninstall = $this->l('Tem certeza que deseja remover o maxiPago!?');
        $merchantId = Configuration::get('MAXIPAGO_SELLER_ID');
        $sellerKey = Configuration::get('MAXIPAGO_SELLER_KEY');

        $this->_maxiPago = new maxiPagoPayment();
        if ($merchantId && $sellerKey) {
            $environment = (Configuration::get('MAXIPAGO_SANDBOX')) ? 'TEST' : 'LIVE';
            $this->_maxiPago->setCredentials($merchantId, $sellerKey);
            $this->_maxiPago->setEnvironment($environment);
        }
    }

    /**
     * @return maxiPagoPayment
     */
    public function getMaxipago()
    {
        return $this->_maxiPago;
    }

    /**
     * Check if the state exist before create another one.
     *
     * @param integer $id_order_state State ID
     * @return boolean availability
     */
    public static function orderStateAvailable($id_order_state)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT `id_order_state` AS ok
			FROM `' . _DB_PREFIX_ . 'order_state`
			WHERE `id_order_state` = ' . (int)$id_order_state);
        return $result['ok'];
    }

    /**
     * Installation method
     * @return boolean
     */
    public function install()
    {
        if (
            !parent::install() 
            || !$this->registerHook('payment') 
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('postUpdateOrderStatus')
            || !$this->registerHook('adminOrder')
            || !$this->registerHook('displayOrderDetail')
        ) {
            return false;
        }

        if (!$this->_generateOrderStatus()) {
            return false;
        }

        if (!$this->_createTables()) {
            return false;
        }
        
        $this->_setDefaultValues();

        return true;
    }

    /**
     * Generate order statuses on installing the module
     * @return bool
     */
    protected function _generateOrderStatus()
    {
        $image = _PS_ROOT_DIR_ . '/modules/maxipago/logo.gif';

        foreach (self::$order_states as $key => $state) {

            $order_state = new OrderState();
            $order_state->module_name = 'maxipago';
            $order_state->send_email = $state['send_email'];
            $order_state->color = $state['color'];
            $order_state->hidden = $state['hidden'];
            $order_state->delivery = $state['delivery'];
            $order_state->logable = $state['logable'];
            $order_state->invoice = $state['invoice'];

            if (version_compare(_PS_VERSION_, '1.5', '>')) {
                $order_state->unremovable = $state['unremovable'];
                $order_state->shipped = $state['shipped'];
                $order_state->paid = $state['paid'];
            }

            $order_state->name = array();
            $order_state->template = array();
            $continue = false;

            foreach (Language::getLanguages(false) as $language) {

                $list_states = $this->_findOrderStates($language['id_lang']);

                $continue = $this->_checkIfOrderStatusExists(
                    $language['id_lang'],
                    $state['name'],
                    $list_states
                );

                if ($continue) {
                    $order_state->name[(int)$language['id_lang']] = $state['name'];
                    $order_state->template[$language['id_lang']] = $state['template'];
                }

                if ($key == 'new') {
                    $this->_copyMailTo($state['template'], $language['iso_code'], 'html');
                    $this->_copyMailTo($state['template'], $language['iso_code'], 'txt');
                }

            }

            if ($continue) {
                if ($order_state->add()) {
                    $file = _PS_ROOT_DIR_ . '/img/os/' . (int)$order_state->id . '.gif';
                    copy($image, $file);
                }
            }
            Configuration::updateValue('MAXIPAGO_ORDER_CODE_' . strtoupper($key), $this->_returnIdOrderState($state['name']));
        }

        return true;
    }

    /**
     * Create tables to save maciPago transactions
     * @return bool
     */
    protected function _createTables()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'maxipago_cc_token` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `id_customer` INT(10) UNSIGNED NOT NULL ,
              `id_customer_maxipago` INT(10) UNSIGNED NOT NULL ,
              `brand` VARCHAR(255) NOT NULL, 
              `token` VARCHAR(255) NOT NULL ,
              `description` VARCHAR(255) NOT NULL ,
              PRIMARY KEY  (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ .
            ' DEFAULT CHARSET=utf8';
        if (! Db::getInstance()->Execute($sql)) {
            return false;
        }

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'maxipago_transactions` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `id_order` INT(10) UNSIGNED NOT NULL ,
              `boleto_url` VARCHAR(255) NULL ,
              `online_debit_url` VARCHAR(255) NULL,
              `method` VARCHAR(255) NOT NULL, 
              `request` TEXT NOT NULL ,
              `return` TEXT NOT NULL ,
              `response_message` VARCHAR(255) NOT NULL,
              `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY  (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ .
            ' DEFAULT CHARSET=utf8';

        if (! Db::getInstance()->Execute($sql)) {
            return false;
        }
        return true;
    }

    /**
     * Set the DEFAULT values of configs
     */
    protected function _setDefaultValues()
    {
        $softDescriptor = strtoupper(Configuration::get('PS_SHOP_NAME'));
        Configuration::updateValue('MAXIPAGO_SANDBOX', "1");
        Configuration::updateValue('MAXIPAGO_CHECKOUT_TYPE', "0");

        Configuration::updateValue('MAXIPAGO_CC_ENABLED', "0");
        Configuration::updateValue('MAXIPAGO_VISA_PROCESSOR', "1");
        Configuration::updateValue('MAXIPAGO_SOFT_DESCRIPTOR', substr($softDescriptor, 0, 15));
        Configuration::updateValue('MAXIPAGO_CC_CAN_SAVE', 0);
        Configuration::updateValue('MAXIPAGO_MASTERCARD_PROCESSOR', "1");
        Configuration::updateValue('MAXIPAGO_AMEX_PROCESSOR', "1");
        Configuration::updateValue('MAXIPAGO_DINERS_PROCESSOR', "1");
        Configuration::updateValue('MAXIPAGO_ELO_PROCESSOR', "1");
        Configuration::updateValue('MAXIPAGO_DISCOVER_PROCESSOR', "1");
        Configuration::updateValue('MAXIPAGO_HIPERCARD_PROCESSOR', "1");

        Configuration::updateValue('MAXIPAGO_BOLETO_ENABLED', "0");
        Configuration::updateValue('MAXIPAGO_BOLETO_BANK', 0);
        Configuration::updateValue('MAXIPAGO_BOLETO_DAYS_TO_EXPIRE', "5");
        Configuration::updateValue('MAXIPAGO_BOLETO_INSTRUCTIONS', "");
        Configuration::updateValue('MAXIPAGO_BOLETO_DISCOUNT', "0");

        Configuration::updateValue('MAXIPAGO_TEF_ENABLED', "0");
        Configuration::updateValue('MAXIPAGO_TEF_BANKS', '');
        Configuration::updateValue('MAXIPAGO_TEF_DISCOUNT', '');

        Configuration::updateValue('MAXIPAGO_NOTIFICATION_UPDATE', "1");
        Configuration::updateValue('MAXIPAGO_STATUS', "0");
        Configuration::updateValue('MAXIPAGO_DEBUG', "0");
    }

    protected function _findOrderStates($lang_id)
    {
        $sql = 'SELECT DISTINCT osl.`id_lang`, osl.`name`
            FROM `' . _DB_PREFIX_ . 'order_state` os
            INNER JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state`)
            WHERE osl.`id_lang` = ' . "$lang_id" . ' AND osl.`name` in (
                "Novo Pedido", 
                "Aguardando Pagamento",
                "Pagamento Confirmado", 
                "Pagamento Devolvido",
                "Pedido Cancelado"
            ) AND os.`id_order_state` <> 6';

        return (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
    }

    protected function _checkIfOrderStatusExists($id_lang, $status_name, $list_states)
    {
        if (Tools::isEmpty($list_states) or empty($list_states) or !isset($list_states)) {
            return true;
        }

        $save = true;
        foreach ($list_states as $state) {

            if ($state['id_lang'] == $id_lang && $state['name'] == $status_name) {
                $save = false;
                break;
            }
        }

        return $save;
    }

    protected function _copyMailTo($name, $lang, $ext)
    {
        $template = _PS_MAIL_DIR_ . $lang . '/' . $name . '.' . $ext;
        if (!file_exists($template)) {
            $templateToCopy = _PS_ROOT_DIR_ . '/modules/maxipago/mails/' . $name . '.' . $ext;
            copy($templateToCopy, $template);
        }
    }

    protected function _returnIdOrderState($state_name)
    {
        $isDeleted = version_compare(_PS_VERSION_, '1.5', '<') ? '' : 'WHERE deleted = 0';
        $sql = 'SELECT DISTINCT os.`id_order_state`
            FROM `' . _DB_PREFIX_ . 'order_state` os
            INNER JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl
            ON (os.`id_order_state` = osl.`id_order_state` AND osl.`name` = \'' .
            pSQL($state_name) . '\')' . $isDeleted;

        $id_order_state = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));

        return isset($id_order_state[0]) ? $id_order_state[0]['id_order_state'] : false;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('MAXIPAGO_SANDBOX') //is test
            || !Configuration::deleteByName('MAXIPAGO_SELLER_ID') // Seller ID
            || !Configuration::deleteByName('MAXIPAGO_SELLER_KEY') // Seller Key
            || !Configuration::deleteByName('MAXIPAGO_SELLER_SECRET') // Seller Secret

            || !Configuration::deleteByName('MAXIPAGO_CC_ENABLED') // If Credit Card is avaiable
            || !Configuration::deleteByName('MAXIPAGO_CC_INTEREST')
            || !Configuration::deleteByName('MAXIPAGO_CC_MAX_INSTALLMENTS')
            || !Configuration::deleteByName('MAXIPAGO_CC_MAX_WITHOUT_INTEREST')
            || !Configuration::deleteByName('MAXIPAGO_CC_ENABLED')
            || !Configuration::deleteByName('MAXIPAGO_CC_CAN_SAVE')
            || !Configuration::deleteByName('MAXIPAGO_CC_PROCESSING_TYPE')
            || !Configuration::deleteByName('MAXIPAGO_CC_FRAUD_CHECK')
            || !Configuration::deleteByName('MAXIPAGO_SOFT_DESCRIPTOR') // SOFT Descriptor
            || !Configuration::deleteByName('MAXIPAGO_VISA_PROCESSOR') // Adquirente
            || !Configuration::deleteByName('MAXIPAGO_MASTERCARD_PROCESSOR') // Adquirente
            || !Configuration::deleteByName('MAXIPAGO_AMEX_PROCESSOR') // Adquirente
            || !Configuration::deleteByName('MAXIPAGO_DINERS_PROCESSOR') // Adquirente
            || !Configuration::deleteByName('MAXIPAGO_ELO_PROCESSOR') // Adquirente
            || !Configuration::deleteByName('MAXIPAGO_DISCOVER_PROCESSOR') // Adquirente
            || !Configuration::deleteByName('MAXIPAGO_HIPERCARD_PROCESSOR') // Adquirente

            || !Configuration::deleteByName('MAXIPAGO_BOLETO_ENABLED') // If boleto is avaiable
            || !Configuration::deleteByName('MAXIPAGO_BOLETO_DAYS_TO_EXPIRE') // Days to expire Boleto
            || !Configuration::deleteByName('MAXIPAGO_BOLETO_INSTRUCTIONS') // Instructions Boleto
            || !Configuration::deleteByName('MAXIPAGO_BOLETO_DISCOUNT') // Boleto discount
            || !Configuration::deleteByName('MAXIPAGO_BOLETO_BANK') // Boleto bank

            || !Configuration::deleteByName('MAXIPAGO_TEF_ENABLED') // TEF
            || !Configuration::deleteByName('MAXIPAGO_TEF_BANKS') // TEF Bank
            || !Configuration::deleteByName('MAXIPAGO_TEF_DISCOUNT')

            || !Configuration::deleteByName('MAXIPAGO_NOTIFICATION_UPDATE') // Update by notification
            || !Configuration::deleteByName('MAXIPAGO_NOTIFICATION_UPDATE_MAIL') // Update customer when status changed
            || !Configuration::deleteByName('MAXIPAGO_STATUS')
            || !Configuration::deleteByName('MAXIPAGO_DEBUG')

            || !parent::uninstall()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Method that search the update status of an order
     * @param $sId
     * @param null $order_id
     */
    public function updateOrders($sId, $id_order = null)
    {
        if (trim($sId) && $sId == Configuration::get('MAXIPAGO_SELLER_ID')) {

            $searchStatues = array(
                '"BOLETO ISSUED"',
                '"BOLETO VIEWED"',
                '"PENDING"',
                '"PENDING CONFIRMATION"',
                '"AUTHORIZED"'
            );

            $date = new DateTime('-15 DAYS'); // first argument uses strtotime parsing
            $fromDate = $date->format('Y-m-d 00:00:00');

            $sql = 'SELECT *
                    FROM ' . _DB_PREFIX_ . 'maxipago_transactions
                    WHERE `created_at` > "' . pSQL($fromDate) . '" 
                    AND `response_message` IN (' . implode(',', $searchStatues). ')
                    ';
            if ($id_order) {
                $sql .= 'AND `id_order` = "' . pSQL($id_order) . '"';
                $id_order_ps = $id_order;
            }

            if ($transactions = Db::getInstance()->executeS($sql)) {

                foreach($transactions as $transaction) {

                    $return = json_decode($transaction['return']);

                    $search = array(
                        'orderID' => $return->orderID
                    );

                    $this->getMaxipago()->pullReport($search);
                    $response = $this->getMaxipago()->getReportResult();
                    $this->log($this->getMaxipago()->response);

                    $state = isset($response[0]['transactionState']) ? $response[0]['transactionState'] : null;

                    $id_order_ps = $transaction['id_order'];
                    if ($state && $id_order_ps) {
                        if ($state == '10' || $state == '3' || $state == '44') {
                            $this->updateOrderHistory($id_order_ps, Configuration::get('MAXIPAGO_ORDER_CODE_PAID'));
                        } else if ($state == '45' || $state == '7' || $state == '9') {
                            $this->updateOrderHistory($id_order_ps, Configuration::get('MAXIPAGO_ORDER_CODE_CANCELED'));
                        }
                        $this->_updateTransactionState($id_order_ps, $return, $response);
                    }

                }

            }

        }

    }

    public function updateOrderHistory($id_order, $status)
    {
        if (Configuration::get('MAXIPAGO_NOTIFICATION_UPDATE_MAIL')) {
            $mail = true;
        } else {
            $mail = false;
        }
        /** @var OrderHistoryCore $history */
        $history = new OrderHistory();
        $history->id_order = (int)$id_order;
        $history->changeIdOrderState((int)$status, (int)$id_order, true);

        if ($mail) {
            $history->addWithemail(true, array());
        }

    }

    /**
     * Content for admin
     */
    public function getContent()
    {
        $this->context->controller->addCss($this->_path . 'assets/css/admin.css', 'all');
        $this->context->controller->addJs($this->_path . 'assets/js/jquery.mask.min.js');

        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors))
                $this->_postProcess();
            else
                foreach ($this->_postErrors as $err)
                    $this->_html .= $this->displayError($err);
        } else
            $this->_html .= '<br />';

        $this->_html .= $this->_displayInfos();
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    /**
     * Validate Form admin
     */
    protected function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $MAXIPAGO_SELLER_ID = Tools::getValue('MAXIPAGO_SELLER_ID');
            $MAXIPAGO_SELLER_KEY = Tools::getValue('MAXIPAGO_SELLER_KEY');
            $MAXIPAGO_SELLER_SECRET = Tools::getValue('MAXIPAGO_SELLER_SECRET');

            if (!$MAXIPAGO_SELLER_ID || !$MAXIPAGO_SELLER_KEY || !$MAXIPAGO_SELLER_SECRET) {
                $this->_postErrors[] = $this->l('É preciso colocar as informações da loja para ativar o módulo!');
            }

        }
    }

    /**
     * Submit form admin
     */
    protected function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {

            Configuration::updateValue('MAXIPAGO_SANDBOX', Tools::getValue('MAXIPAGO_SANDBOX'));
            Configuration::updateValue('MAXIPAGO_SELLER_ID', Tools::getValue('MAXIPAGO_SELLER_ID'));
            Configuration::updateValue('MAXIPAGO_SELLER_KEY', Tools::getValue('MAXIPAGO_SELLER_KEY'));
            Configuration::updateValue('MAXIPAGO_SELLER_SECRET', Tools::getValue('MAXIPAGO_SELLER_SECRET'));

            Configuration::updateValue('MAXIPAGO_CC_ENABLED', Tools::getValue('MAXIPAGO_CC_ENABLED'));
            Configuration::updateValue('MAXIPAGO_CC_CAN_SAVE', Tools::getValue('MAXIPAGO_CC_CAN_SAVE'));
            Configuration::updateValue('MAXIPAGO_SOFT_DESCRIPTOR', Tools::getValue('MAXIPAGO_SOFT_DESCRIPTOR'));
            Configuration::updateValue('MAXIPAGO_CC_MAX_INSTALLMENTS', Tools::getValue('MAXIPAGO_CC_MAX_INSTALLMENTS'));
            Configuration::updateValue('MAXIPAGO_CC_MAX_WITHOUT_INTEREST', Tools::getValue('MAXIPAGO_CC_MAX_WITHOUT_INTEREST'));
            Configuration::updateValue('MAXIPAGO_CC_INTEREST_TYPE', Tools::getValue('MAXIPAGO_CC_INTEREST_TYPE'));
            Configuration::updateValue('MAXIPAGO_CC_INTEREST_RATE', Tools::getValue('MAXIPAGO_CC_INTEREST_RATE'));
            Configuration::updateValue('MAXIPAGO_CC_MINIMUM_PER_INSTALLMENTS', Tools::getValue('MAXIPAGO_CC_MINIMUM_PER_INSTALLMENTS'));
            Configuration::updateValue('MAXIPAGO_CC_PROCESSING_TYPE', Tools::getValue('MAXIPAGO_CC_PROCESSING_TYPE'));
            Configuration::updateValue('MAXIPAGO_CC_FRAUD_CHECK', Tools::getValue('MAXIPAGO_CC_FRAUD_CHECK'));
            Configuration::updateValue('MAXIPAGO_VISA_PROCESSOR', Tools::getValue('MAXIPAGO_VISA_PROCESSOR'));
            Configuration::updateValue('MAXIPAGO_MASTERCARD_PROCESSOR', Tools::getValue('MAXIPAGO_MASTERCARD_PROCESSOR'));
            Configuration::updateValue('MAXIPAGO_AMEX_PROCESSOR', Tools::getValue('MAXIPAGO_AMEX_PROCESSOR'));
            Configuration::updateValue('MAXIPAGO_DINERS_PROCESSOR', Tools::getValue('MAXIPAGO_DINERS_PROCESSOR'));
            Configuration::updateValue('MAXIPAGO_ELO_PROCESSOR', Tools::getValue('MAXIPAGO_ELO_PROCESSOR'));
            Configuration::updateValue('MAXIPAGO_DISCOVER_PROCESSOR', Tools::getValue('MAXIPAGO_DISCOVER_PROCESSOR'));
            Configuration::updateValue('MAXIPAGO_HIPERCARD_PROCESSOR', Tools::getValue('MAXIPAGO_HIPERCARD_PROCESSOR'));

            Configuration::updateValue('MAXIPAGO_BOLETO_ENABLED', Tools::getValue('MAXIPAGO_BOLETO_ENABLED'));
            Configuration::updateValue('MAXIPAGO_BOLETO_DAYS_TO_EXPIRE', Tools::getValue('MAXIPAGO_BOLETO_DAYS_TO_EXPIRE'));
            Configuration::updateValue('MAXIPAGO_BOLETO_INSTRUCTIONS', Tools::getValue('MAXIPAGO_BOLETO_INSTRUCTIONS'));
            Configuration::updateValue('MAXIPAGO_BOLETO_DISCOUNT', Tools::getValue('MAXIPAGO_BOLETO_DISCOUNT'));
            Configuration::updateValue('MAXIPAGO_BOLETO_BANK', Tools::getValue('MAXIPAGO_BOLETO_BANK'));

            Configuration::updateValue('MAXIPAGO_TEF_ENABLED', Tools::getValue('MAXIPAGO_TEF_ENABLED'));
            Configuration::updateValue('MAXIPAGO_TEF_BANKS', implode(',', Tools::getValue('MAXIPAGO_TEF_BANKS')));
            Configuration::updateValue('MAXIPAGO_TEF_DISCOUNT', Tools::getValue('MAXIPAGO_TEF_DISCOUNT'));

            Configuration::updateValue('MAXIPAGO_NOTIFICATION_UPDATE', Tools::getValue('MAXIPAGO_NOTIFICATION_UPDATE'));
            Configuration::updateValue('MAXIPAGO_NOTIFICATION_UPDATE_MAIL', Tools::getValue('MAXIPAGO_NOTIFICATION_UPDATE_MAIL'));
            Configuration::updateValue('MAXIPAGO_STATUS', Tools::getValue('MAXIPAGO_STATUS'));
            Configuration::updateValue('MAXIPAGO_DEBUG', Tools::getValue('MAXIPAGO_DEBUG'));
        }
        $this->_html .= $this->displayConfirmation($this->l('Configurações Atualizadas'));
    }

    /**
     * Display infos admin
     * @return mixed
     */
    protected function _displayInfos()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    /**
     * Render form admin
     * @return mixed
     */
    public function renderForm()
    {
        if (
            (
                Configuration::get('MAXIPAGO_CC_ENABLED') == "1"
                || Configuration::get('MAXIPAGO_CC_ENABLED') == "1"
            )
            && Configuration::get('MAXIPAGO_STATUS') == "1"
            && Configuration::get('MAXIPAGO_SELLER_ID') != ""
            && Configuration::get('MAXIPAGO_SELLER_KEY') != ""
            && Configuration::get('MAXIPAGO_SELLER_SECRET') != ""
        ) {
            $active = "yes";
        } else {
            $active = "no";
        }

        $syncUrl = $this->link->getModuleLink('maxipago', 'notification', array('sId' => Configuration::get('MAXIPAGO_SELLER_ID'), 'redir' => 1) , Configuration::get('PS_SSL_ENABLED'), null, null, false);

        $cronUrl = $this->link->getModuleLink('maxipago', 'notification', array('sId' => Configuration::get('MAXIPAGO_SELLER_ID')) , Configuration::get('PS_SSL_ENABLED'), null, null, false);

        $tefUrl = $this->link->getModuleLink('maxipago', 'tef', array() , Configuration::get('PS_SSL_ENABLED'), null, null, false);

        $this->context->smarty->assign('module_dir', _PS_MODULE_DIR_ . 'maxipago/');
        $this->context->smarty->assign('action_post', Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']));

        $this->context->smarty->assign('MAXIPAGO_SANDBOX', Configuration::get('MAXIPAGO_SANDBOX'));
        $this->context->smarty->assign('MAXIPAGO_SELLER_ID', Configuration::get('MAXIPAGO_SELLER_ID'));
        $this->context->smarty->assign('MAXIPAGO_SELLER_KEY', Configuration::get('MAXIPAGO_SELLER_KEY'));
        $this->context->smarty->assign('MAXIPAGO_SELLER_SECRET', Configuration::get('MAXIPAGO_SELLER_SECRET'));

        $this->context->smarty->assign('MAXIPAGO_CC_ENABLED', Configuration::get('MAXIPAGO_CC_ENABLED'));
        $this->context->smarty->assign('MAXIPAGO_SOFT_DESCRIPTOR', Configuration::get('MAXIPAGO_SOFT_DESCRIPTOR'));
        $this->context->smarty->assign('MAXIPAGO_CC_CAN_SAVE', Configuration::get('MAXIPAGO_CC_CAN_SAVE'));
        $this->context->smarty->assign('MAXIPAGO_CC_INTEREST', Configuration::get('MAXIPAGO_CC_INTEREST'));
        $this->context->smarty->assign('MAXIPAGO_CC_MAX_INSTALLMENTS', Configuration::get('MAXIPAGO_CC_MAX_INSTALLMENTS'));
        $this->context->smarty->assign('MAXIPAGO_CC_MAX_WITHOUT_INTEREST', Configuration::get('MAXIPAGO_CC_MAX_WITHOUT_INTEREST'));
        $this->context->smarty->assign('MAXIPAGO_CC_INTEREST_TYPE', Configuration::get('MAXIPAGO_CC_INTEREST_TYPE'));
        $this->context->smarty->assign('MAXIPAGO_CC_INTEREST_RATE', Configuration::get('MAXIPAGO_CC_INTEREST_RATE'));
        $this->context->smarty->assign('MAXIPAGO_CC_MINIMUM_PER_INSTALLMENTS', Configuration::get('MAXIPAGO_CC_MINIMUM_PER_INSTALLMENTS'));
        $this->context->smarty->assign('MAXIPAGO_CC_PROCESSING_TYPE', Configuration::get('MAXIPAGO_CC_PROCESSING_TYPE'));
        $this->context->smarty->assign('MAXIPAGO_CC_FRAUD_CHECK', Configuration::get('MAXIPAGO_CC_FRAUD_CHECK'));
        $this->context->smarty->assign('MAXIPAGO_VISA_PROCESSOR', Configuration::get('MAXIPAGO_VISA_PROCESSOR'));
        $this->context->smarty->assign('MAXIPAGO_MASTERCARD_PROCESSOR', Configuration::get('MAXIPAGO_MASTERCARD_PROCESSOR'));
        $this->context->smarty->assign('MAXIPAGO_AMEX_PROCESSOR', Configuration::get('MAXIPAGO_AMEX_PROCESSOR'));
        $this->context->smarty->assign('MAXIPAGO_DINERS_PROCESSOR', Configuration::get('MAXIPAGO_DINERS_PROCESSOR'));
        $this->context->smarty->assign('MAXIPAGO_ELO_PROCESSOR', Configuration::get('MAXIPAGO_ELO_PROCESSOR'));
        $this->context->smarty->assign('MAXIPAGO_DISCOVER_PROCESSOR', Configuration::get('MAXIPAGO_DISCOVER_PROCESSOR'));
        $this->context->smarty->assign('MAXIPAGO_HIPERCARD_PROCESSOR', Configuration::get('MAXIPAGO_HIPERCARD_PROCESSOR'));

        $this->context->smarty->assign('MAXIPAGO_BOLETO_ENABLED', Configuration::get('MAXIPAGO_BOLETO_ENABLED'));
        $this->context->smarty->assign('MAXIPAGO_BOLETO_DAYS_TO_EXPIRE', Configuration::get('MAXIPAGO_BOLETO_DAYS_TO_EXPIRE'));
        $this->context->smarty->assign('MAXIPAGO_BOLETO_INSTRUCTIONS', Configuration::get('MAXIPAGO_BOLETO_INSTRUCTIONS'));
        $this->context->smarty->assign('MAXIPAGO_BOLETO_DISCOUNT', Configuration::get('MAXIPAGO_BOLETO_DISCOUNT'));
        $this->context->smarty->assign('MAXIPAGO_BOLETO_BANK', Configuration::get('MAXIPAGO_BOLETO_BANK'));

        $this->context->smarty->assign('MAXIPAGO_TEF_ENABLED', Configuration::get('MAXIPAGO_TEF_ENABLED'));
        $this->context->smarty->assign('MAXIPAGO_TEF_BANKS', Configuration::get('MAXIPAGO_TEF_BANKS') ? explode(',', Configuration::get('MAXIPAGO_TEF_BANKS')) : array());
        $this->context->smarty->assign('MAXIPAGO_TEF_DISCOUNT', Configuration::get('MAXIPAGO_TEF_DISCOUNT'));

        $this->context->smarty->assign('MAXIPAGO_NOTIFICATION_UPDATE', Configuration::get('MAXIPAGO_NOTIFICATION_UPDATE'));
        $this->context->smarty->assign('MAXIPAGO_NOTIFICATION_UPDATE_MAIL', Configuration::get('MAXIPAGO_NOTIFICATION_UPDATE_MAIL'));
        $this->context->smarty->assign('MAXIPAGO_STATUS', Configuration::get('MAXIPAGO_STATUS'));
        $this->context->smarty->assign('MAXIPAGO_DEBUG', Configuration::get('MAXIPAGO_DEBUG'));
        $this->context->smarty->assign('MAXIPAGO_ACTIVE', $active);

        $this->context->smarty->assign('processors', $this->_processors('all'));
        $this->context->smarty->assign('processors_amex', $this->_processors('amex'));
        $this->context->smarty->assign('processors_diners', $this->_processors('diners'));
        $this->context->smarty->assign('processors_elo', $this->_processors('elo'));
        $this->context->smarty->assign('processors_discover', $this->_processors('discover'));
        $this->context->smarty->assign('processors_hipercard', $this->_processors('hipercard'));
        $this->context->smarty->assign('processing_types', $this->_processingTypes());

        $this->context->smarty->assign('banks', $this->_banks());
        $this->context->smarty->assign('tef_banks', $this->_banks('tef'));

        $this->context->smarty->assign('sync_url', $syncUrl);
        $this->context->smarty->assign('cron_url', $cronUrl);
        $this->context->smarty->assign('tef_url', $tefUrl);
        $this->context->smarty->assign('action', Tools::getValue('act'));

        return $this->display(__PS_BASE_URI__ . 'modules/maxipago', 'views/templates/admin/settings.tpl');
    }

    /**
     * Method that show the maxiPago! payment method
     * @param $params
     * @return bool
     */
    public function hookPayment($params)
    {
        $ccEnabled = Configuration::get('MAXIPAGO_CC_ENABLED');
        $boletoEnabled = Configuration::get('MAXIPAGO_BOLETO_ENABLED');
        $tefEnabled = Configuration::get('MAXIPAGO_TEF_ENABLED');

        if ($ccEnabled || $boletoEnabled || $tefEnabled) {
            $description = array();
            if ($ccEnabled) {
                array_push($description, $this->l('Cartão de Crédito'));
            }
            if ($boletoEnabled) {
                array_push($description, $this->l('Boleto Bancário'));
            }
            if ($tefEnabled) {
                array_push($description, $this->l('Transferência Eletrônica'));
            }
            $description = $this->_naturalJoin($description);

            $this->smarty->assign(array(
                'checkout_type' => 'default',
                'this_path' => $this->_path,
                'description' => $this->l('Pague com') . ' ' . $description
            ));

            return $this->display(__FILE__, 'payment.tpl');
        }
        return false;
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module))
            foreach ($currencies_module as $currency_module)
                if ($currency_order->id == $currency_module['id_currency'])
                    return true;

        return false;
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active)
            return;

        $boletoUrl = null;
        $onlineDebitUrl = null;
        $method = null;
        $request = null;
        $response = null;
        $status = null;

        $id_order = Tools::getValue('id_order', null);
        $sql = 'SELECT *
                    FROM ' . _DB_PREFIX_ . 'maxipago_transactions
                    WHERE `id_order` = \'' . pSQL($id_order) . '\';';

        if ($transaction = Db::getInstance()->getRow($sql)) {
            $boletoUrl = $transaction['boleto_url'];
            $onlineDebitUrl = $transaction['online_debit_url'];
            $method = $transaction['method'];

            $request = json_decode($transaction['request']);
            $response = json_decode($transaction['return']);

            $responseCode = $response->responseCode;
            $responseMessage = $response->responseMessage;
            $status = $this->_getOrderStatus($responseCode, $responseMessage);

            if ($method == 'card') {
                if ($responseCode == '0') {
                    if ($responseMessage != 'AUTHORIZED') {
                        $orderStatus = Configuration::get('MAXIPAGO_ORDER_CODE_PAID');
                        $this->updateOrderHistory($id_order, $orderStatus);
                    }
                } else if ($responseCode == '1' || $responseCode == '2') {
                    $this->updateOrderHistory($id_order, Configuration::get('MAXIPAGO_ORDER_CODE_CANCELED'));
                }

            }

        }

        $this->smarty->assign(
            array(
                'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
                'owner' => $this->owner,
                'this_path' => Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/'),
                'id_order' => Tools::getValue('id_order'),
                'request' => $request,
                'response' => $response,
                'status' => $status,
                'boleto_url' => $boletoUrl,
                'online_debit_url' => $onlineDebitUrl,
                'method' => $method
            )
        );

        if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference)) {
            $this->smarty->assign('reference', $params['objOrder']->reference);
        }

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    /**
     * Hook to show data in the admin order
     * @param $params
     */
    public function hookAdminOrder($params)
    {
        $sql = 'SELECT * 
                FROM ' . _DB_PREFIX_ . 'maxipago_transactions
                WHERE `id_order` = \'' . pSQL($_GET['id_order']) . '\'
                ORDER BY `id` DESC
                ; ';
        $orderData = Db::getInstance()->getRow($sql);

        if ($orderData) {

            $urlParams = array(
                'sId' => Configuration::get('MAXIPAGO_SELLER_ID'),
                'id_order' => $_GET['id_order'],
                'redir' => 1
            );
            $updateUrl = $this->link->getModuleLink('maxipago', 'notification', $urlParams , Configuration::get('PS_SSL_ENABLED'), null, null, false);

            $request = json_decode($orderData['request']);
            $return = json_decode($orderData['return']);

            $boletoUrl = $orderData['boleto_url'];
            $onlineDebitUrl = $orderData['online_debit_url'];
            $method = $orderData['method'];

            $status = $this->_getOrderStatus($return->responseCode, $orderData['response_message']);

            $this->context->smarty->assign(
                array(
                    'method' => $orderData['method'],
                    'status' => $status,
                    'boleto_url' => $boletoUrl,
                    'online_debit_url' => $onlineDebitUrl,
                    'request' => $request,
                    'update_url' => $updateUrl,
                    'return' => $return,
                    'response_message' => $orderData['response_message'],
                )
            );

            return $this->display(__FILE__, 'views/templates/admin/order-detail.tpl');
        }
    }

    /**
     * Hook to show data in the admin order
     * @param $params
     */
    public function hookDisplayOrderDetail($params)
    {
        $sql = 'SELECT * 
                FROM ' . _DB_PREFIX_ . 'maxipago_transactions
                WHERE `id_order` = \'' . pSQL($_GET['id_order']) . '\'; ';
        $orderData = Db::getInstance()->getRow($sql);

        if ($orderData) {

            $urlParams = array(
                'sId' => Configuration::get('MAXIPAGO_SELLER_ID'),
                'order_id' => $_GET['id_order']
            );
            $updateUrl = $this->link->getModuleLink('maxipago', 'notification', array() , Configuration::get('PS_SSL_ENABLED'), null, null, false);

            $request = json_decode($orderData['request']);
            $return = json_decode($orderData['return']);

            $boletoUrl = $orderData['boleto_url'];
            $onlineDebitUrl = $orderData['online_debit_url'];
            $method = $orderData['method'];

            $status = $this->_getOrderStatus($return->responseCode, $orderData['response_message']);

            $this->context->smarty->assign(
                array(
                    'method' => $orderData['method'],
                    'status' => $status,
                    'boleto_url' => $boletoUrl,
                    'online_debit_url' => $onlineDebitUrl,
                    'request' => $request,
                    'return' => $return,
                    'response_message' => $orderData['response_message']
                )
            );

            return $this->display(__FILE__, 'views/templates/front/order-detail.tpl');
        }
    }

    /**
     * Hook to show data in the admin order
     * @param $params
     */
    public function hookPostUpdateOrderStatus($params)
    {
        $id_order = $params['id_order'];
        $sql = 'SELECT *
                    FROM ' . _DB_PREFIX_ . 'maxipago_transactions
                    WHERE `id_order` = "' . pSQL($id_order) . '"
                    AND `method` = "card";';

        if ($transaction = Db::getInstance()->getRow($sql)) {

            $method = $transaction['method'];

            if ($method == 'card') {
                $newOrderStatus = $params['newOrderStatus'];

                $request = json_decode($transaction['request']);
                $response = json_decode($transaction['return']);

                $configCancelled = Configuration::get('MAXIPAGO_ORDER_CODE_CANCELED');
                $configRefunded = Configuration::get('MAXIPAGO_ORDER_CODE_REFUNDED');
                $configPaid = Configuration::get('MAXIPAGO_ORDER_CODE_PAID');

                if (
                    $newOrderStatus->id == $configCancelled
                    || $newOrderStatus->id == $configRefunded
                    || $newOrderStatus->template == 'order_canceled'
                    || $newOrderStatus->template == 'refund'
                ) {
                    $data = array(
                        'orderID' => $response->orderID,
                        'referenceNum' => $response->referenceNum,
                        'chargeTotal' => $request->chargeTotal,
                    );
                    $this->getMaxipago()->creditCardRefund($data);
                    $this->_updateTransactionState($id_order);
                } else {
                    if (
                        $newOrderStatus->id == $configPaid
                        || $newOrderStatus->template == 'payment'
                    ) {
                        $data = array(
                            'orderID' => $response->orderID,
                            'referenceNum' => $response->referenceNum,
                            'chargeTotal' => $request->chargeTotal,
                        );
                        $this->getMaxipago()->creditCardCapture($data);
                        $this->_updateTransactionState($id_order);
                    }
                }
            }
        }

    }

    public function ajaxCall()
    {
        $data = array('success' => false);

        if (Context::getContext()->customer->id) {
            $id_customer = Context::getContext()->customer->id;
        } else {
            $id_customer = false;
        }

        if ($id_customer) {
            $description = Tools::getValue('ident', null);
            $sql = 'SELECT *
                    FROM ' . _DB_PREFIX_ . 'maxipago_cc_token
                    WHERE `id_customer` = \'' . pSQL($id_customer) . '\'
                    AND `description` = \'' . $description . '\'; ';

            if ($ccSaved = Db::getInstance()->getRow($sql)) {
                if ($this->_deleteCC($ccSaved)) {
                    $data = array('success' => true);
                }
            }
        }
        return json_encode($data);

    }

    /**
     * Calculate the installments price for maxiPago!
     * @param $price
     * @param $installments
     * @param $interestRate
     * @return float
     */
    public function getInstallmentPrice($price, $installments, $interestRate)
    {
        $price = (float) $price;
        if ($interestRate) {
            $interestRate = (float)(str_replace(',', '.', $interestRate)) / 100;
            $type = Configuration::get('MAXIPAGO_CC_INTEREST_TYPE');
            $valorParcela = 0;
            switch ($type) {
                case 'price':
                    $value = round($price * (($interestRate * pow((1 + $interestRate), $installments)) / (pow((1 + $interestRate), $installments) - 1)), 2);
                    break;
                case 'compound':
                    //M = C * (1 + i)^n
                    $value = ($price * pow(1 + $interestRate, $installments)) / $installments;
                    break;
                case 'simple':
                    //M = C * ( 1 + ( i * n ) )
                    $value = ($price * (1 + ($installments * $interestRate))) / $installments;
            }
        } else {
            if ($installments)
                $value = $price / $installments;
        }
        return $value;
    }

    /**
     * Calculate the total of the order based on interest rate and installmentes
     * @param $price
     * @param $installments
     * @param $interestRate
     * @return float
     */
    public function getTotalByInstallments($price, $installments, $interestRate)
    {
        $installmentPrice = $this->getInstallmentPrice($price, $installments, $interestRate);
        return $installmentPrice * $installments;
    }

    /**
     * Get MAX installments for a price
     * @param null $price
     * @return array|bool
     */
    public function getInstallment($price = null)
    {
        $price = (float) $price;

        $maxInstallments = Configuration::get('MAXIPAGO_CC_MAX_INSTALLMENTS');
        $installmentsWithoutInterest = Configuration::get('MAXIPAGO_CC_MAX_WITHOUT_INTEREST');
        $minimumPerInstallment = Configuration::get('MAXIPAGO_CC_MINIMUM_PER_INSTALLMENTS');

        $minimumPerInstallment = (float)$minimumPerInstallment;

        if ($minimumPerInstallment > 0) {
            if ($minimumPerInstallment > $price / 2)
                return false;

            while ($maxInstallments > ($price / $minimumPerInstallment))
                $maxInstallments--;

            while ($installmentsWithoutInterest > ($price / $minimumPerInstallment))
                $installmentsWithoutInterest--;
        }

        $interestRate = str_replace(',', '.', Configuration::get('MAXIPAGO_CC_INTEREST_RATE'));
        $interestRate = ($maxInstallments <= $installmentsWithoutInterest) ? '' : $interestRate;

        $installmentValue = $this->getInstallmentPrice($price, $maxInstallments, $interestRate);
        $totalWithoutInterest = $installmentValue;

        if ($installmentsWithoutInterest)
            $totalWithoutInterest = $price / $installmentsWithoutInterest;

        $total = $installmentValue * $maxInstallments;

        return array(
            'total' => $total,
            'installments_without_interest' => $installmentsWithoutInterest,
            'total_without_interest' => $totalWithoutInterest,
            'max_installments' => $maxInstallments,
            'installment_value' => $installmentValue,
            'interest_rate' => $interestRate,
        );
    }

    /**
     * Get ALL POSSIBLE instalments for a price
     * @param null $price
     * @return array
     */
    public function getInstallments($price = null)
    {
        $price = (float) $price;

        $maxInstallments = Configuration::get('MAXIPAGO_CC_MAX_INSTALLMENTS');
        $installmentsWithoutInterest = Configuration::get('MAXIPAGO_CC_MAX_WITHOUT_INTEREST');
        $minimumPerInstallment = Configuration::get('MAXIPAGO_CC_MINIMUM_PER_INSTALLMENTS');
        $interestRate = str_replace(',', '.', Configuration::get('MAXIPAGO_CC_INTEREST_RATE'));

        if ($minimumPerInstallment > 0) {
            while ($maxInstallments > ($price / $minimumPerInstallment)) $maxInstallments--;
        }
        $installments = array();
        if ($price > 0) {
            $maxInstallments = ($maxInstallments == 0) ? 1 : $maxInstallments;
            for ($i = 1; $i <= $maxInstallments; $i++) {
                $interestRateInstallment = ($i <= $installmentsWithoutInterest) ? '' : $interestRate;
                $value = ($i <= $installmentsWithoutInterest) ? ($price / $i) : $this->getInstallmentPrice($price, $i, $interestRate);
                $total = $value * $i;

                $installments[] = array(
                    'total' => $total,
                    'installments' => $i,
                    'installment_value' => $value,
                    'interest_rate' => $interestRateInstallment
                );
            }
        }
        return $installments;
    }

    public function log($message, $severity = 1)
    {
        $debug = Configuration::get('MAXIPAGO_DEBUG');
        if ($debug) {
            if (is_array($message) || is_object($message)) {
                $message = json_encode($message);
            }
            Logger::addLog($message, $severity, null, get_class($this), 1, true);
        }
    }

    protected function _naturalJoin(array $list, $conjunction = 'ou')
    {
        $last = array_pop($list);
        if ($list) {
            return implode(', ', $list) . ' ' . $conjunction . ' ' . $last;
        }
        return $last;
    }

    protected function _processors($type = 'all')
    {
        $processors = array(
            '1' => 'Simulador de Teste',
            '2' => 'Redecard',
            '3' => 'GetNet',
            '4' => 'Cielo',
            '6' => 'Elavon'
        );
        $types = array(
            'all' => array('1', '2', '3', '4', '6'),
            'amex' => array('1', '4'),
            'diners' => array('1', '2', '4', '6'),
            'elo' => array('1', '3', '4'),
            'discover' => array('1', '2', '4', '6'),
            'hipercard' => array('1', '2'),
        );

        $processorKeys = array_keys($processors);
        foreach ($processors as $typeId => $typeName) {
            if (!in_array($typeId, $types[$type])) {
                unset($processors[$typeId]);
            }
        }

        $processors = array('' => 'Desabilitado') + $processors;
        return $processors;
    }

    protected function _processingTypes()
    {
        return array(
            'auth' => 'Autorização (Somente Autorizar)',
            'sale' => 'Venda Direta (Autorizar a Capturar)'
        );
    }

    protected function _banks($type = 'boleto')
    {
        $banks = array(
            'boleto' => array(
                '' => 'Selecione o Banco',
                '13' => 'Banco do Brasil',
                '12' => 'Bradesco',
                '16' => 'Caixa Econômica Federal',
                '14' => 'HSBC',
                '11' => 'Itaú',
                '15' => 'Santander'
            ),
            'tef' => array(
                '17' => 'Bradesco',
                '18' => 'Itaú'
            )
        );

        return isset($banks[$type]) ? $banks[$type] : array();
    }

    /**
     * @param $id_order
     * @param array $return
     * @param array $response
     */
    protected function _updateTransactionState($id_order, $return = array(), $response = array())
    {
        if (empty($return) ) {
            $sql = 'SELECT *
                        FROM ' . _DB_PREFIX_ . 'maxipago_transactions
                        WHERE `id_order` = "' . pSQL($id_order) . '" 
                        ';

            if ($transaction = Db::getInstance()->getRow($sql)) {

                $return = json_decode($transaction['return']);

                $search = array(
                    'orderID' => $return->orderID
                );

                $this->getMaxipago()->pullReport($search);
                $this->log($this->getMaxipago()->response);
                $response = $this->getMaxipago()->getReportResult();

            }
        }

        if (! empty($response) ) {
            $responseCode = isset($response[0]['responseCode']) ? $response[0]['responseCode'] : $return->responseCode;
            if (! property_exists($return, 'originalResponseCode')) {
                $return->originalResponseCode = $return->responseCode;
            }
            $return->responseCode = $responseCode;

            if (! property_exists($return, 'originalResponseMessage')) {
                $return->originalResponseMessage = $return->responseMessage;
            }
            $state = isset($response[0]['transactionState']) ? $response[0]['transactionState'] : null;
            $responseMessage = (array_key_exists($state, $this->_transactionStates)) ? $this->_transactionStates[$state] : $return->responseMessage;
            $return->responseMessage = $responseMessage;
            $return->transactionState = $state;
            $transaction['response_message'] = $responseMessage;

            $sql = 'UPDATE ' . _DB_PREFIX_ . 'maxipago_transactions 
                        SET `response_message` = \'' . pSQL(strtoupper($responseMessage)) . '\',
                        `return` = \'' . pSQL(json_encode($return)) . '\'
                        WHERE `id_order` = "' . pSQL($id_order) . '" 
                        ';

            if (! Db::getInstance()->Execute($sql)) {
                $this->log($this->l('Erro ao atualizar status'));
            }
        }
    }

    protected function _getOrderStatus($responseCode, $responseMessage)
    {
        $defaultStatus = $this->l('Aguardando Confirmação de Pagamento');

        if ($responseMessage == 'PENDING' || $responseMessage == 'PENDING CONFIRMATION') {
            $status = $defaultStatus;
        } else {
            if ($responseCode == 0 && $responseMessage == 'AUTHORIZED') {
                $status = $this->l('Pagamento Autorizado');
            } else {
                $status = isset($this->_responseCodes[$responseCode])
                    ? $this->l($this->_responseCodes[$responseCode])
                    : $defaultStatus;
            }
        }

        return $status;
    }

    /**
     * Remove the Credit Card frm maxiPago! Account and remove from the store Account
     *
     * @param $ccSaved
     * @return bool
     */
    protected function _deleteCC($ccSaved)
    {
        $data = array(
            'command' => 'delete-card-onfile',
            'customerId' => $ccSaved['id_customer'],
            'customerId' => $ccSaved['token']
        );

        $this->_maxiPago->deleteCreditCard($data);
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'maxipago_cc_token` WHERE `id` = \''. $ccSaved['id'] . '\';';
        if (! Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute($sql)) {
            return false;
        }

        return true;
    }

}
