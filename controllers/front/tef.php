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

class MaxipagoTefModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        $this->display_header = false;
        $this->display_footer = false;

        parent::initContent();
        print_r($_REQUEST);
        /** @var Maxipago $module */
        $module = $this->module;

        $transaction = Tools::getValue('transaction', null);

        if ($successUrl) {
            if ($paid) {
                $redirUrl = Tools::getValue('successUrl');
                $status = Configuration::get('MAXIPAGO_ORDER_CODE_PAID');
            } else if($canceled) {
                $status = Configuration::get('MAXIPAGO_ORDER_CODE_CANCELED');
                $redirUrl = Tools::getValue('failUrl');
            }
            $module->updateOrderHistory($id_order, $status)

            $url = $successUrl . '&id_order=' . $id_order;
            Tools::redirect($url);
        }
    }

}
