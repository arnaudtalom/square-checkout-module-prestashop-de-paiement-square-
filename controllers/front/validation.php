<?php
/**
* 2007-2019 PrestaShop
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
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Square_checkoutvalidationModuleFrontController extends ModuleFrontController
{
    /**
     * This class should be use by your Instant Payment
     * Notification system to validate the order remotely
     */

    public function postProcess() 
    {  
        if ( (!Configuration::get('PS_SSL_ENABLED') || empty($_SERVER['HTTPS']) || Tools::strtolower($_SERVER['HTTPS']) == 'off')) {
            $this->displayError($this->module->l('SSL must be enabled in order to process the payment'));
        }

        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == $this->module->name) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            $this->displayError($this->l('This payment method is not available.'));
        }

        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $currencyId = (int) $cart->id_currency;
        $authorized = false;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        if (Configuration::get('SQUARE_CHECKOUT_LIVE_MODE')) {
            $host ='https://connect.squareup.com';
            $accessToken =Configuration::get('SQUARE_CHECKOUT_LIVE_ACCESS_TOKEN');
            $locationId = Configuration::get('SQUARE_CHECKOUT_LIVE_LOCATION');
        } else {
            $host ='https://connect.squareupsandbox.com';
            $accessToken = Configuration::get('SQUARE_CHECKOUT_SANDBOX_APP_TOKEN');
            $locationId = Configuration::get('SQUARE_CHECKOUT_SANDBOX_LOCATION');
        }

            $this->payByCheckOut($host, $accessToken, $locationId);

    }

    protected function displayError($message)
    {
        $backlink = $this->context->link->getPageLink('order', null, null, 'step=1');
        $this->context->smarty->assign('message', $message);
        $this->context->smarty->assign('backlink', $backlink);
        return $this->setTemplate('error.tpl');
    }

    protected function validateCartCheckOut($CheckoutId) 
    {
        $checkOutRow =  Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'square_checkout WHERE checkout_id = "'.pSQL($CheckoutId) .'"');
        if (!empty($checkOutRow)){
            return $checkOutRow['cart_id'];
        } else {
            return -1;
        }
    }

    protected function payByCheckOut($host, $accessToken, $locationId) 
    {
        if (!empty(Tools::getValue('checkoutId')) && !empty(Tools::getValue('transactionId'))) {
           if ($this->checkTransaction($host, $accessToken, $locationId,  Tools::getValue('transactionId'))) {
             $cart = new Cart($this->validateCartCheckOut(Tools::getValue('checkoutId')));
             if (!empty($cart->id)) {
                 $this->saveOder($cart, Tools::getValue('transactionId'));
             } else {
                 $this->displayError($this->module->l('Payment failure'));
             }
           } else {
               var_dump($this->checkTransaction($host, $accessToken, $locationId,  Tools::getValue('transactionId')));
                $this->displayError($this->module->l('Payment failure'));
           }
        } else {
            $this->displayError($this->module->l('Payment failure'));
        }
    }

    protected function saveOder($cart, $transactionId) 
    {
        $paymentStatus = Configuration::get('PS_OS_PAYMENT');
        $customer = new Customer($cart->id_customer);
        $message = null;
        $this->module->validateOrder($cart->id, $paymentStatus, $cart->getOrderTotal(), 
            $this->module->displayName, $message,
            array(
                    'transaction_id' => $transactionId), 
                     $cart->id_currency, false, 
                     $customer->secure_key
                );
        Tools::redirect('index.php?controller=order-confirmation&id_cart='.
            $cart->id.'&id_module='
            .$this->module->id.'&id_order='.
            $this->module->currentOrder.
            '&key='.$customer->secure_key
        );    
    }

    protected function checkTransaction($host, $accessToken, $locationId,  $transactionId)
    {
        $apiConfig = new \SquareConnect\Configuration();
        $apiConfig->setHost($host);
        $apiConfig->setAccessToken($accessToken);
        $apiClient = new \SquareConnect\ApiClient($apiConfig);
        $transactionApi = new \SquareConnect\Api\TransactionsApi($apiClient);
        try {
            $result = $transactionApi->retrieveTransaction($locationId, $transactionId);
            var_dump("transaction id");
            var_dump($result->getTransaction()->getId());
            if (!empty($result->getTransaction()->getId())) {
                return true;
            } else {
                return false;
            }

        } catch (Exception $e) {
            return false;
        }
    }

}