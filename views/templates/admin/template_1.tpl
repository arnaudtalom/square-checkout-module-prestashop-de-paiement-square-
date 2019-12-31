{*
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
*}

<div class="panel">
	<div class="row square_checkout-header">
		<img src="{$module_dir|escape:'html':'UTF-8'}views/img/logo.jpg" class="col-xs-6 col-md-4 text-center" id="payment-logo" />
		<div class="col-xs-6 col-md-4 text-center">
			<h4>{l s='Online payment processing' mod='square_checkout'}</h4>
			<h4>{l s='Fast - Secure - Reliable' mod='square_checkout'}</h4>
		</div>
	</div>

	<hr />
	
	<div class="square_checkout-content">
		<div class="row">
			<div class="col-md-6">
				<h5>{l s='My payment module square checkout' mod='square_checkout'}</h5>
				<dl>
					<dt>&middot; {l s='You can accept payments in your shop using Square online payment gateway. As a major payment provider, Square is available in the USA®, the UK®, Canada®, Australia®, and Japan® and accepts each country’s currency.' mod='square_checkout'}</dt>
				</dl>
			</div>
			
			<div class="col-md-6">
				<h5>{l s='FREE My Payment Module Glocal Gateway (Value of 400$)' mod='square_checkout'}</h5>
				<ul>
					<li>{l s='Simple, secure and reliable solution to process online payments' mod='square_checkout'}</li>
					<li>{l s='Reccuring billing' mod='square_checkout'}</li>
					<li>{l s='24/7/365 customer support' mod='square_checkout'}</li>
					
				</ul>
				<br />
				<em class="text-muted small">
					* {l s='New merchant account required and subject to credit card approval.' mod='square_checkout'}
					{l s='The free My Payment Module Global Gateway will be accessed through log in information provided via email within 48 hours.' mod='square_checkout'}
					{l s='Monthly fees for My Payment Module Global Gateway will apply.' mod='square_checkout'}
				</em>
			</div>
		</div>

		<hr />
		
		<div class="row">
			<div class="col-md-12">
				<h4>{l s='Accept payments in the United States using all major credit cards' mod='square_checkout'}</h4>
				
				<div class="row">
					<img src="{$module_dir|escape:'html':'UTF-8'}views/img/template_1_cards.png" class="col-md-6" id="payment-logo" />
				</div>
			</div>
		</div>
	</div>
</div>
