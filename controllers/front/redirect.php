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

class Square_checkoutRedirectModuleFrontController extends ModuleFrontController
{
    /**
     * Do whatever you have to before redirecting the customer on the website of your payment processor.
     */
    public function postProcess()
    {
        if ( (!Configuration::get('PS_SSL_ENABLED') || empty($_SERVER['HTTPS']) || Tools::strtolower($_SERVER['HTTPS']) == 'off')) {
            return $this->displayError($this->module->l("SSL must be enabled in order to process the payment"));
        }

        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == $this->module->name) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            return $this->displayError($this->module->l("This payment method is not available."));
        }

        $cart = $this->context->cart;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        if (Tools::getValue('action') == 'error') {
            return $this->displayError('An error occurred while trying to redirect the customer');
        } else {

            if (Configuration::get('SQUARE_CHECKOUT_LIVE_MODE')){
                $host ='https://connect.squareup.com';
                $accessToken =Configuration::get('SQUARE_CHECKOUT_LIVE_ACCESS_TOKEN');
                $locationId = Configuration::get('SQUARE_CHECKOUT_LIVE_LOCATION');
            } else {
                $host ='https://connect.squareupsandbox.com';
                $accessToken =Configuration::get('SQUARE_CHECKOUT_SANDBOX_APP_TOKEN');
                $locationId = Configuration::get('SQUARE_CHECKOUT_SANDBOX_LOCATION');
            }

            $customer = new Customer($cart->id_customer);
            $amount = (float) $cart->getOrderTotal();
            $apiConfig = new \SquareConnect\Configuration();
            $apiConfig->setHost($host);
            $apiConfig->setAccessToken($accessToken);
            $currencyId = (int) $cart->id_currency;
            $currency = new Currency($currencyId);
            $currencyIsoCode = $currency->iso_code;
            $apiClient = new \SquareConnect\ApiClient($apiConfig);
            try { 
                $checkoutApi = new \SquareConnect\Api\CheckoutApi($apiClient);
                $requestBody = new \SquareConnect\Model\CreateCheckoutRequest(
                    [	
                      'idempotency_key' => uniqid(),
                      'order' => [
                        "line_items" => [
                        [
                          'name' => 'Order'.$cart->id,
                          'quantity' => '1',
                          'base_price_money' => [
                            'amount' => (int)($amount*100),
                            'currency' => (string)$currencyIsoCode
                          ]
                        ]]
                      ]
                    ]
                  );         
                $requestBody->setPrePopulateBuyerEmail((string)$customer->email);
                $requestBody->setRedirectUrl($this->context->link->getModuleLink('square_checkout', 'validation'));
                $response = $checkoutApi->createCheckout($locationId, $requestBody);
                $checkoutId = $response->getCheckout()->getId();
    
                $insert = array(
                    'checkout_id' => (string)$checkoutId,
                    'cart_id' => $cart->id,
                    );
                Db::getInstance()->insert('square_checkout', $insert);  
              } catch (Exception $e) {
                return $this->displayError($this->module->l("redirection error."));
              }
              Tools::redirect($response->getCheckout()->getCheckoutPageUrl());
              exit();
        }
    }

    protected function displayError($message, $description = false)
    {
        /*
         * Create the breadcrumb for your ModuleFrontController.
         */
        $this->context->smarty->assign('path', '
			<a href="' . $this->context->link->getPageLink('order', null, null, 'step=3') . '">' . $this->module->l('Payment') . '</a>
			<span class="navigation-pipe">&gt;</span>' . $this->module->l('Error'));

        /*
         * Set error message and description for the template.
         */
        array_push($this->errors, $this->module->l($message), $description);

        return $this->setTemplate('error.tpl');
    }
}
		