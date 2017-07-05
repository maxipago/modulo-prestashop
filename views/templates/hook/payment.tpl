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
<script src="{$this_path}assets/js/jquery.maskedinput.js"></script>
<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a href="{$link->getModuleLink('maxipago', 'payment')|escape:'html'}"
               title="{$description}"
               style="background-image: url({$this_path}assets/images/maxiPago-logo.jpg);
                       background-repeat: no-repeat;
                       background-position: 15px 20px;
                       padding-left: 180px;">
                {$description}
            </a>
        </p>
    </div>
</div>
