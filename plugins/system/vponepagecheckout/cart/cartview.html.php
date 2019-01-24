<?php
/*--------------------------------------------------------------------------------------------------------
# VP One Page Checkout - Joomla! System Plugin for VirtueMart 3
----------------------------------------------------------------------------------------------------------
# Copyright:     Copyright (C) 2012 - 2015 VirtuePlanet Services LLP. All Rights Reserved.
# License:       GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
# Author:        Abhishek Das
# Email:         info@virtueplanet.com
# Websites:      http://www.virtueplanet.com
----------------------------------------------------------------------------------------------------------
$Revision: 55 $
$LastChangedDate: 2015-09-15 02:10:01 +0530 (Tue, 15 Sep 2015) $
$Id: cartview.html.php 55 2015-09-14 20:40:01Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');

require dirname(__FILE__) . '/includes.php';

class VirtueMartViewCart extends VmView 
{
	protected $cart;
	protected $params;
	protected $app;
	protected $layoutName;
	protected $juser;
	protected $checkoutTask;
	protected $task;
	protected $finalStage;
	protected $currencyDisplay;
	protected $totalInPaymentCurrency;
	protected $productsCount;
	protected $btFields;
	protected $stFields;
	protected $regFields;
	protected $selectSTName;
	protected $customfieldsModel;
	protected $useSSL;
	protected $useXHTML;
	protected $order_language;
	protected $checkoutAdvertise;
	protected $checkout_task;
	protected $continue_link;
	protected $continue_link_html;
	protected $couponCode;
	protected $coupon_text;
	protected $shipment_not_found_text;
	protected $found_shipment_method;
	protected $shipments_shipment_rates;
	protected $payment_not_found_text;
	protected $paymentplugins_payments;
	protected $found_payment_method;
	protected $vmAdminID;
	protected $adminUser;
	protected $isAdminUser;
	protected $helper;
	protected $message_type;
	protected $time_start;
	protected $time_end;
	
	protected static $vendorCountryState = null;
	
	public function display($tpl = null) 
	{
		$time_start                     = microtime(true);
		$app                            = JFactory::getApplication();
		$this->app                      = $app;
		$input                          = $app->input;
		$pathway                        = $app->getPathway();
		$document                       = JFactory::getDocument();
		$plugin                         = JPluginHelper::getPlugin('system', 'vponepagecheckout');
		$params                         = new JRegistry($plugin->params);
		$this->params                   = $params;
		$juser                          = JFactory::getUser();
		$this->juser                    = $juser;
		$vmAdminID                      = JFactory::getSession()->get('vmAdminID');
		$this->vmAdminID                = $vmAdminID;
		$adminUser                      = JFactory::getUser($vmAdminID);
		$this->adminUser                = $adminUser;
		$checkoutTask                   = version_compare(JVERSION, '3.0.0', 'ge') ? 
		                                  $input->get('ctask', '', 'STRING') : 
		                                  JRequest::getVar('ctask', '', 'STRING');
		$checkoutTask                   = strtolower($checkoutTask);
		$this->checkoutTask             = $checkoutTask;
		$task                           = version_compare(JVERSION, '3.0.0', 'ge') ? 
		                                  $input->getCmd('task', '') : 
		                                  JRequest::getCmd('task', '');
		$task                           = strtolower($task);
		$this->task                     = $task;
		$this->cart                     = VirtueMartCart::getCart();
		$useSSL                         = VmConfig::get('useSSL', 0);
		$this->useSSL                   = $useSSL;
		$useXHTML                       = true;
		$this->useXHTML                 = $useXHTML;
		$this->time_start               = $time_start;
		$lang                           = JFactory::getLanguage();
		$order_language                 = $lang->getTag();
		$this->order_language           = $order_language;
		
		// Prepare cart vendor
		$this->cart->prepareVendor();

		// Set proper layout
		$layoutName                     = $this->getLayout();
		$disabledLayouts                = array('select_shipment', 'select_payment');
		if(in_array($layoutName, $disabledLayouts))
		{
			$layoutName = 'default';
			$this->setLayout($layoutName);
			if(version_compare(JVERSION, '3.0.0', 'ge'))
			{
				$input->set('task', 'procheckout');
			}
			else
			{
				JRequest::setVar('task', 'procheckout');
			}
		}
		elseif(empty($layoutName))
		{
			$layoutName                   = version_compare(JVERSION, '3.0.0', 'ge') ?
			                                $input->getCmd('layout', 'default') :
			                                JRequest::getCmd('layout', 'default');
		}
		
		$this->layoutName               = $layoutName;
		
		if($this->layoutName != 'order_done')
		{
			$currencyDisplay              = CurrencyDisplay::getInstance($this->cart->pricesCurrency);
			$this->currencyDisplay        = $currencyDisplay;
			$data                         = $this->getData();
			$totalInPaymentCurrency       = $this->getTotalInPaymentCurrency();
			$this->totalInPaymentCurrency = $totalInPaymentCurrency;
			$customfieldsModel            = VmModel::getModel('Customfields');
			$this->customfieldsModel      = $customfieldsModel;
		}

		// Set meta data for cart page
		$document->setMetaData('robots','NOINDEX, NOFOLLOW, NOARCHIVE, NOSNIPPET');

		// Create a helper instance
		if(!class_exists('VPOPCHelper'))
		{
			require dirname(__FILE__) . '/helper.php';
		}
		$helper = VPOPCHelper::getInstance($this->params);
		$this->helper = $helper;
		
		// New Layout Path
		$this->addTemplatePath(dirname(__FILE__) . '/tmpl/');
		
		// Add template layout override path
		if($templatePath = $this->getTemplatePath())
		{
			$this->addTemplatePath($templatePath);
		}

		// Add user details to cart if user logs in using other modules
		// We may not need it any longer with VM 3
		// $this->refreshCartUser();
		
		// Check for incorrect configuration
		if($this->params->get('only_guest', 0) && VmConfig::get('oncheckout_only_registered'))
		{
			$app->enqueueMessage('You have set <strong>Only registered users can checkout</strong> in VirtueMart Configuration therefore <strong>Hide Registration and Login Area</strong> can not be enabled. You need to allow guest checkout in VirtueMart Configuration.', 'error');
		}

		// Set the checkout stage
		if(($task == 'procheckout') || !$juser->guest || ($this->params->get('only_guest', 0) && !VmConfig::get('oncheckout_only_registered')))
		{
			$app->setUserState('proopc.checkout.finalstage', true);
		}
		$finalStage = $app->getUserState('proopc.checkout.finalstage', false) ? true : false;
		$this->finalStage = $finalStage;

		// Check if the user is VM Admin
		if($juser->authorise('core.admin', 'com_virtuemart') || $adminUser->authorise('core.admin', 'com_virtuemart') ||
		   $juser->authorise('vm.user', 'com_virtuemart') || $adminUser->authorise('vm.user', 'com_virtuemart'))
		{
			$this->isAdminUser = true;
		}
		else
		{
			$this->isAdminUser = false;
		}
		
		// Execute called checkout task
		switch($checkoutTask)
		{
			case 'getcartsummery' :
				$this->cart->prepareCartData();
				$this->checkPaymentMethodsConfigured();
				$this->checkShipmentMethodsConfigured();
				$this->lSelectCoupon();
				$html = $this->renderPlgLayout('default_pricelist');
				$result = array('cartsummery' => $html);
				$this->jsonReturn($result);
				break;
				
			case 'getcartlist' :
				$this->cart->prepareCartData();
				$this->checkPaymentMethodsConfigured();
				$this->checkShipmentMethodsConfigured();
				$html = $this->renderPlgLayout('default_cartlist');
				$messages = '';
				$message_type = '';
				if(JPluginHelper::isEnabled('vmcoupon', 'awocoupon'))
				{
					$messages = $this->getMessages();
					$message_type = $this->getMessageType();
					$savedMessage = $this->app->getUserState('proopc.savemessage', '');
					$this->app->setUserState('proopc.savemessage', '');
					if(empty($messages))
					{
						$messages = $savedMessage;
						$message_type = 'success';
					}
				}
				$result = array('cartlist' => $html, 'msg' => $messages, 'msg_type' => $message_type);
				$this->jsonReturn($result);
				break;
				
			case 'getshipmentpaymentcartlist' :
				// $this->cart->prepareCartData();
				// Get shipment options
				$this->lSelectShipment();
				$shipment_html = $this->renderPlgLayout('default_shipment');
				$shipment_script = array();
				$shipment_scripts = array();
				$dom = new DOMDocument;
				$dom_state = libxml_use_internal_errors(true);
				$dom->loadHTML($shipment_html);
				libxml_clear_errors();
				libxml_use_internal_errors($dom_state);
				$scripts = $dom->getElementsByTagName('script');
				if($scripts->length)
				{
					foreach($scripts as $script) 
					{
						if(!$script->getAttribute('src')) 
						{
							$tempScripts = str_replace('//-->','', str_replace('<!--', '', $script->textContent));
							if(strpos($tempScripts, 'jQuery(function ()') !== false) 
							{
								$tempScripts = str_replace('jQuery(function () {', '', $this->str_lreplace('});', '', $tempScripts));
							}
							$shipment_scripts[] = trim($tempScripts);
						}
						else 
						{
							$shipment_script[] = $script->getAttribute('src');
						}
					}
				}
				unset($dom, $scripts, $script);
				// Get payment options
				$this->lSelectPayment();
				$payment_html = $this->renderPlgLayout('default_payment');
				$payment_script = array();
				$payment_scripts = array();
				$dom = new DOMDocument;
				$dom_state = libxml_use_internal_errors(true);
				$dom->loadHTML($html);
				libxml_clear_errors();
				libxml_use_internal_errors($dom_state);
				$scripts = $dom->getElementsByTagName('script');
				if($scripts->length)
				{
					foreach ($scripts as $script) 
					{
						if(!$script->getAttribute('src')) 
						{
							$tempScripts = str_replace('//-->','', str_replace('<!--', '', $script->textContent));
							if(strpos($tempScripts, 'jQuery(function ()') !== false) 
							{
								$tempScripts = str_replace('jQuery(function () {', '', $this->str_lreplace('});', '',$tempScripts));
							} 
							$payment_scripts[] = trim($tempScripts);
						}
						else 
						{
							$payment_script[] = $script->getAttribute('src');
						}
					}
				}
				unset($dom, $scripts, $script);
				// Get cartlist table
				$this->lSelectCoupon();
				$cartlist_html = $this->renderPlgLayout('default_cartlist');
				// Get messages
				$messages = '';
				$message_type = '';
				if(JPluginHelper::isEnabled('vmcoupon', 'awocoupon'))
				{
					$messages = $this->getMessages();
					$message_type = $this->getMessageType();
					$savedMessage = $this->app->getUserState('proopc.savemessage', '');
					$this->app->setUserState('proopc.savemessage', '');
					if(empty($messages))
					{
						$messages = $savedMessage;
						$message_type = 'success';
					}
				}
				// Prepare full result
				$result = array(
										'shipments' => $shipment_html,
										'shipment_script' => $shipment_script,
										'shipment_scripts' => $shipment_scripts,
										'payments' => $payment_html,
										'payment_script' => $payment_script,
										'payment_scripts' => $payment_scripts,
										'cartlist'=> $cartlist_html,
										'msg' => $messages,
										'msg_type' => $message_type
									);
				$this->jsonReturn($result);
				break;
			
			case 'getpaymentscripts' :
			case 'getpaymentlist' :
				// $this->cart->prepareCartData();
				$this->lSelectPayment();
				$html = $this->renderPlgLayout('default_payment');
				$_script = array();
				$_scripts = array();
				$dom = new DOMDocument;
				$dom_state = libxml_use_internal_errors(true);
				$dom->loadHTML($html);
				libxml_clear_errors();
				libxml_use_internal_errors($dom_state);
				$scripts = $dom->getElementsByTagName('script');
				
				if($scripts->length)
				{
					foreach ($scripts as $script) 
					{
						if(!$script->getAttribute('src')) 
						{
							$tempScripts = str_replace('//-->','', str_replace('<!--', '', $script->textContent));
							if(strpos($tempScripts, 'jQuery(function ()') !== false) 
							{
								$tempScripts = str_replace('jQuery(function () {', '', $this->str_lreplace('});', '',$tempScripts));
							} 
							$_scripts[] = trim($tempScripts);
						}
						else 
						{
							$_script[] = $script->getAttribute('src');
						}
					}
				}
				unset($dom, $scripts, $script);
				$result = array('payments' => $html, 'payment_script' => $_script, 'payment_scripts' => $_scripts);
				$this->jsonReturn($result);
				break;
			
			case 'getshipmentscripts' :
				// $this->cart->prepareCartData();
				$this->lSelectShipment();
				$html = $this->renderPlgLayout('default_shipment');
				$_script = array();
				$_scripts = array();
				$dom = new DOMDocument;
				$dom_state = libxml_use_internal_errors(true);
				$dom->loadHTML($html);
				libxml_clear_errors();
				libxml_use_internal_errors($dom_state);
				$scripts = $dom->getElementsByTagName('script');
				if($scripts->length)
				{
					foreach($scripts as $script) 
					{
						if(!$script->getAttribute('src')) 
						{
							$tempScripts = str_replace('//-->','', str_replace('<!--', '', $script->textContent));
							if(strpos($tempScripts, 'jQuery(function ()') !== false) 
							{
								$tempScripts = str_replace('jQuery(function () {', '', $this->str_lreplace('});', '',$tempScripts));
							}
							$_scripts[] = trim($tempScripts);
						}
						else 
						{
							$_script[] = $script->getAttribute('src');
						}
					}
				}
				unset($dom, $scripts, $script);
				$return = array('shipments' => $html, 'shipment_script' => $_script, 'shipment_scripts' => $_scripts);
				$this->jsonReturn($return);
				break;
				
			case 'savebtaddress' :
				$this->prepareCartForUpdate();
				$hasError = false;
				if(isset($this->cart->tosAccepted))
				{
					$data['agreed'] = $this->cart->tosAccepted ? 1 : 0;
				}
				$this->cart->ST = $this->cart->STsameAsBT ? 0 : $this->cart->ST;
				$this->cart->saveAddressInCart($data, 'BT', true);
				//$this->cart->prepareCartData();
				$stage = isset($data['stage']) ? strval($data['stage']) : null;
				if($stage == 'final' && $this->juser->id)
				{
					$data['virtuemart_user_id'] = $this->juser->id;
					$data['agreed'] = 1;
					$data['address_type'] = 'BT';
					$user = VmModel::getModel('user');
					if(!$user->store($data)) 
					{
						$hasError = true;
					}
				}
				elseif($this->juser->id)
				{
					$data['virtuemart_user_id'] = $this->juser->id;
					$data['agreed'] = 1;
					$data['address_type'] = 'BT';
					$userinfo_id = $this->storePartUserinfo($data, 'BT');
				}
				$messages = ($stage == 'final') ? $this->getMessages(false) : $this->getMessages();
				$result = array('error' => intval($hasError), 'info' => $this->cart->BT, 'msg' => $messages);
				$this->jsonReturn($result);
				break;
				
			case 'savestaddress' :
				$this->prepareCartForUpdate();
				$error = false;
				if($this->juser->id > 0)
				{
					$data['shipto_address_type_name'] = isset($data['shipto_address_type_name']) ? $data['shipto_address_type_name'] : 'Shipping Address 1';
				}
				$data['shipto_virtuemart_userinfo_id'] = isset($data['shipto_virtuemart_userinfo_id']) ? (int) $data['shipto_virtuemart_userinfo_id'] : 0;
				$virtuemart_userinfo_id = $data['shipto_virtuemart_userinfo_id'];
				$stage = isset($data['stage']) ? strval($data['stage']) : '';
				if($stage == 'final' && $this->juser->id) 
				{
					$data['shipto_virtuemart_user_id'] = $this->juser->id;
					$data['address_type'] = 'ST';
					$user = VmModel::getModel('user');
					if(!$virtuemart_userinfo_id = $user->storeAddress($data)) 
					{
						$error = true;
					}
					$data['shipto_virtuemart_userinfo_id'] = (int) $virtuemart_userinfo_id;
				}
				elseif($this->juser->id && !empty($data['shipto_address_type_name']) && (!empty($data['shipto_zip']) || !empty($data['shipto_city']) || !empty($data['shipto_virtuemart_country_id']) || !empty($data['shipto_city'])))
				{
					$data['shipto_virtuemart_user_id'] = $this->juser->id;
					$data['address_type'] = 'ST';
					$virtuemart_userinfo_id = $this->storePartUserinfo($data, 'ST', 'shipto_');
					$data['shipto_virtuemart_userinfo_id'] = (int) $virtuemart_userinfo_id;
				}
				
				$this->cart->STsameAsBT = 0;
				$this->cart->saveAddressInCart($data, 'ST', true, 'shipto_');
				$this->cart->ST['virtuemart_userinfo_id'] = $virtuemart_userinfo_id;
				$this->cart->selected_shipto = $virtuemart_userinfo_id;
				
				$this->cart->setCartIntoSession(true);
				$messages = ($stage == 'final') ? $this->getMessages(false) : $this->getMessages();
				$result = array('error' => intval($error), 'info' => $this->cart->ST, 'userinfo_id' => $virtuemart_userinfo_id, 'msg' => $messages);
				$this->jsonReturn($result);
				break;
			
			case 'selectstaddress' :
				$this->prepareCartForUpdate();
				$this->cart->selected_shipto = isset($data['shipto_virtuemart_userinfo_id']) ? intval($data['shipto_virtuemart_userinfo_id']) : 0;
				$userModel = VmModel::getModel('user');
				$stData = $userModel->getUserAddressList($this->juser->id, 'ST', $this->cart->selected_shipto);
				if(isset($stData[0]) && is_object($stData[0]))
				{
					$stData = get_object_vars($stData[0]);
					$this->cart->saveAddressInCart($stData, 'ST', true, '');
				}
				else
				{
					$this->cart->selected_shipto = 0;
					$this->cart->ST = 0;
				}
				$this->cart->setCartIntoSession(true);
				$this->cart->prepareCartData();
				$this->prepareAddressFields();
				$editST = $this->renderPlgLayout('default_staddress');
				$selectedStateID = (!empty($this->cart->ST) && isset($this->cart->ST['virtuemart_state_id'])) ?
				                   $this->cart->ST['virtuemart_state_id'] : '';
				$result = array('editst' => $editST, 'stateid' => $selectedStateID);
				$this->jsonReturn($result);
				break;
		
			case 'btasst' :
				//$this->prepareCartForUpdate();
				$this->cart->STsameAsBT = 1;
				$this->cart->ST = 0;
				$this->cart->selected_shipto = 0;
				$this->cart->setCartIntoSession(true);
				$app->setUserState('proopc.btasst', 1);
				$result = array('STsameAsBT' => 1, 'error' => 0);
				$this->jsonReturn($result);
				break;
			
			case 'btnotasst' :
				//$this->prepareCartForUpdate();
				$this->cart->STsameAsBT = 0;
				//$data['address_type_name'] = 'ST';
				$this->cart->setCartIntoSession(true);
				$app->setUserState('proopc.btasst', 0);
				$result = array('STsameAsBT' => 0, 'error' => 0);
				$this->jsonReturn($result);
				break;
				
			case 'register' :
				$this->registerUser();
				break;
				
			case 'login' :
				$method = version_compare(JVERSION, '3.0.0', 'ge') ? 
				          $input->getMethod() : 
				          JRequest::getMethod();
				$method = strtolower($method);
				// Check Token
				JSession::checkToken($method) or $this->jsonReturn(array('error' => 1, 'msg' => JText::_('JINVALID_TOKEN')));
				$return = version_compare(JVERSION, '3.0.0', 'ge') ? 
				          base64_decode($input->$method->get('return', '', 'BASE64')) : 
				          base64_decode(JRequest::getVar('return', '', $method, 'BASE64'));
				if(!empty($return)) 
				{
					$return = (!JUri::isInternal($return)) ? '' : $return;
				}
				$options                  = array();
				$options['remember']      = version_compare(JVERSION, '3.0.0', 'ge') ? 
				                            $input->getBool('remember', false) :
				                            JRequest::getBool('remember', false);
				$options['return']        = $return;
				$options['silent']        = true;
				$credentials              = array();
				$credentials['username']  = version_compare(JVERSION, '3.0.0', 'ge') ? 
				                            $input->$method->get('username', '', 'USERNAME') :
				                            JRequest::getVar('username', '', $method, 'USERNAME');
				$credentials['password']  = version_compare(JVERSION, '3.0.0', 'ge') ? 
				                            $input->$method->get('password', '', 'RAW') :
				                            JRequest::getString('password', '', $method, JREQUEST_ALLOWRAW);
				// Retrieve actual username
				$credentials['username']  = $this->getLoginUsername($credentials['username']);
				if(empty($credentials['username']) || empty($credentials['password']))
				{
					$this->ajaxResponse(false);
					return;
				}
				
				if(version_compare(JVERSION, '3.0.0', 'ge'))
				{
					$credentials['secretkey'] = $input->$method->get('secretkey', '', 'RAW');
				}
				// Perform the login action
				$result = $app->login($credentials, $options);
				if(true === $result)
				{
					if ($options['remember'] == true)
					{
						$app->setUserState('rememberLogin', true);
					}
				}
				if(JFactory::getUser()->id > 0 && JPluginHelper::isEnabled('system', 'bonus')) 
				{
					VmbonusHelperFrontBonus::ParseCart();
				}
				// Arrange to return the result taking care of URL redirection
				$this->ajaxResponse($result);
				break;
		
			case 'setshipments' :
				$this->prepareCartForUpdate();
				$return = $this->setShipmentMethod(true);
				$error = $return ? 0 : 1;
				$result = array('error' => $error, 'msg' => $this->getMessages());
				$this->jsonReturn($result);
				break;
				
			case 'setpayment' :
				$this->prepareCartForUpdate();
				$saveCC = isset($data['savecc']) ? intval($data['savecc']) : 0;
				$payment_data = isset($data['payment_data']) ? intval($data['payment_data']) : 0;
				$app->setUserState('virtuemart.paypal.express.url', false);
				$return = $this->setPaymentMethod($saveCC);
				$error = $return ? 0 : 1;
				$messages = $this->getMessages();
				$paymentExpresssURL = $app->getUserState('virtuemart.paypal.express.url', false);
				if($payment_data && $saveCC) 
				{
					$this->cart->prepareCartData();
				}
				if($payment_data == 1 and !$saveCC) 
				{
					$result = array('error' => 0, 'msg' => $messages);
				} 
				else 
				{
					if($paymentExpresssURL) 
					{
						$result = array('error' => 0, 'msg' => $messages, 'redirect' => $paymentExpresssURL);
					}
					else
					{
						$result = array('error' => $error, 'msg' => $messages, 'redirect' => '' , 'test'=>json_encode($this->cart));
					}
				}
				$this->jsonReturn($result);
				break;
				
			case 'setdefaultsp' :
				$virtuemart_shipmentmethod_id = vRequest::getInt('virtuemart_shipmentmethod_id', 0);
				$virtuemart_paymentmethod_id = vRequest::getInt('virtuemart_paymentmethod_id', 0);
				if(!empty($virtuemart_shipmentmethod_id) || !empty($virtuemart_paymentmethod_id))
				{
					$this->prepareCartForUpdate();
				}
				if(!empty($virtuemart_shipmentmethod_id))
				{
					// First try to set the shipment method
					$return = $this->setShipmentMethod(true);
					$error = $return ? 0 : 1;
					$messages = $this->getMessages();
					if($return == false)
					{
						$result = array('error' => $error, 'msg' => $messages);
						$this->jsonReturn($result);
					}
					$this->cart->prepareCartData();
				}
				if(!empty($virtuemart_paymentmethod_id))
				{
					// Next set the payment method
					$saveCC = isset($data['savecc']) ? intval($data['savecc']) : 0;
					$payment_data = isset($data['payment_data']) ? intval($data['payment_data']) : 0;
					$app->setUserState('virtuemart.paypal.express.url', false);
					$return = $this->setPaymentMethod($saveCC);
					$error = $return ? 0 : 1;
					$messages = $this->getMessages();
					$paymentExpresssURL = $app->getUserState('virtuemart.paypal.express.url', false);
					if($payment_data && $saveCC) 
					{
						$this->cart->prepareCartData();
					}
					if($payment_data == 1 and !$saveCC) 
					{
						$result = array('error' => 0, 'msg' => $messages);
					} 
					else 
					{
						if($paymentExpresssURL) 
						{
							$result = array('error' => 0, 'msg' => $messages, 'redirect' => $paymentExpresssURL);
						}
						else
						{
							$result = array('error' => $error, 'msg' => $messages, 'redirect' => '');
						}
					}
					$this->jsonReturn($result);
				}
				$result = array('error' => 0, 'msg' => '', 'redirect' => 0);
				$this->jsonReturn($result);
				break;
				
			case 'deleteproduct' :
				$this->prepareCartForUpdate();
				$product_key = vRequest::getInt('id', 0);
				$return = $this->cart->removeProductCart($product_key);
				if($return)
				{
					if (JPluginHelper::isEnabled('system', 'bonus')) 
					{
						VmbonusHelperFrontBonus::ParseCart();
					}
					$this->cart->prepareCartData();
				}
				$productsCount = $this->getProductsCount();
				$result = array('pqty' => $productsCount, 'updated' => intval($return));
				$this->jsonReturn($result);
				break;
				
			case 'updateproduct' :
				$this->prepareCartForUpdate();
				$return = $this->cart->updateProductCart();
				$productsCount = $this->getProductsCount();
				if($return)
				{
					if (JPluginHelper::isEnabled('system', 'bonus')) 
					{
						VmbonusHelperFrontBonus::ParseCart();
					}
					$this->cart->prepareCartData();
					$result = array('error' => 0, 'msg' => '', 'pqty' => $productsCount);
				}
				else
				{
					$result = array('error' => 1, 'msg' => $this->getMessages(), 'pqty' => $productsCount);
				}
				$this->jsonReturn($result);
				break;
				
			case 'setcoupon' :
				$this->prepareCartForUpdate();
				$coupon_code = vRequest::getString('coupon_code');
				$return = $this->cart->setCouponCode($coupon_code);
				$warnings = array();
				$warnings['COM_VIRTUEMART_COUPON_CODE_EXPIRED'] = vmText::_('COM_VIRTUEMART_COUPON_CODE_EXPIRED');
				$warnings['COM_VIRTUEMART_COUPON_CODE_NOTYET'] = vmText::_('COM_VIRTUEMART_COUPON_CODE_NOTYET');
				$warnings['COM_VIRTUEMART_COUPON_CODE_TOOLOW'] = vmText::_('COM_VIRTUEMART_COUPON_CODE_TOOLOW');
				$error = 1;
				$wait = 0;
				if((($return == 'COM_VIRTUEMART_CART_COUPON_VALID') || (vmText::_($return) == vmText::_('COM_VIRTUEMART_CART_COUPON_VALID'))))
				{
					if(JPluginHelper::isEnabled('vmcoupon', 'awocoupon'))
					{
						$wait = 1;
						$this->app->setUserState('proopc.savemessage', $return);
					}
					$error = 0;
				}
				elseif(array_key_exists($return, $warnings) || in_array(vmText::_($return), $warnings))
				{
					$error = 2;
				}
				if (JPluginHelper::isEnabled('system', 'bonus')) 
				{
					VmbonusHelperFrontBonus::ParseCart();
				}
				$messages = $this->getMessages();
				if(!empty($messages))
				{
					$result = array('error' => 1, 'msg' => $messages, 'wait' => $wait);
				}
				elseif(strlen($return))
				{
					$result = array('error' => $error, 'msg' => vmText::_($return), 'wait' => $wait);
				}
				else
				{
					$result = array('error' => $error, 'msg' => 'Ooops.. something went wrong. Please try again.', 'wait' => $wait);
				}
				$this->jsonReturn($result);
				break;

			case 'savecartfields' :
				$this->prepareCartForUpdate();
				$this->cart->saveCartFieldsInCart();
				$messages = $this->getMessages();
				$hasError = empty($messages) ? 0 : 1;
				$result = array('error' => $hasError, 'msg' => $messages);
				$this->jsonReturn($result);
				
			case 'verifycheckout' :
				//$this->cart->prepareCartData();
				$this->prepareCartForUpdate();
				$this->cart->_inConfirm = false;
				$this->cart->_inCheckOut = true;
				$hasError = false;
				$verifyMsg = array();
				
				// This prevents that people checkout twice
				$this->cart->setCartIntoSession(false, true);
				
				// Check if cart has products in it
				if(count($this->cart->cartProductsData) === 0)
				{
					$result = array('error' => 1, 'msg' => vmText::_('COM_VIRTUEMART_CART_NO_PRODUCT'));
					$this->jsonReturn($result);
				}
				
				// Check for valid purchase value
				$invalidMessage = $this->checkPurchaseValue();
				if(!empty($invalidMessage) && is_string($invalidMessage))
				{
					$this->cart->_inCheckOut = false;
					$result = array('error' => 1, 'msg' => $invalidMessage);
					$this->jsonReturn($result);
				}
				
				// Check for valid user data
				$validUserDataBT = $this->validateUserData();
				//Important, we can have as result -1,false and true.
				if ($validUserDataBT !== true) 
				{
					$result = array('error' => 1, 'msg' => $this->getMessages(false));
					$this->jsonReturn($result);
				}
				
				// Check for valid user data in cart fields
				$validUserDataCart = $this->validateUserData('cartfields', $this->cart->cartfields, false);
				if($validUserDataCart !== true)
				{
					$this->cart->_inCheckOut = false;
					$this->cart->_blockConfirm = true;
					$result = array('error' => 1, 'msg' => $this->getMessages(false));
					$this->jsonReturn($result);
				} 
				else
				{
					// Atm a bit dirty. We store this information in the BT order_userinfo, so we merge it here, it gives also
					// the advantage, that plugins can easily deal with it.
					// This is same as done by core VirtueMart Component as of Ver 3.0.2
					$this->cart->BT = array_merge((array) $this->cart->BT, (array) $this->cart->cartfields);
				}
				
				if(!empty($this->cart->STsameAsBT) || (!$this->juser->guest && $this->cart->selected_shipto < 1))
				{
					$this->cart->STsameAsBT = 1;
					$this->cart->ST = $this->cart->BT;
					$this->cart->selected_shipto = 0;
				}
				else
				{
					if($this->cart->selected_shipto > 0)
					{
						$userModel = VmModel::getModel('user');
						$stData = $userModel->getUserAddressList($juser->id, 'ST', $this->cart->selected_shipto);
						if(isset($stData[0]) && is_object($stData[0]))
						{
							$stData = get_object_vars($stData[0]);
							if($this->validateUserData('ST', $stData) > 0)
							{
								$this->cart->STsameAsBT = 0;
								$this->cart->ST = $stData;
							}
						} 
						else
						{
							$this->cart->STsameAsBT = 1;
							$this->cart->selected_shipto = 0;
							$this->cart->ST = $this->cart->BT;
						}
					}
					// Only when there is an ST data, test if all necessary fields are filled
					$validUserDataST = $this->validateUserData('ST');
					if ($validUserDataST !== true)
					{
						$result = array('error' => 1, 'msg' => $this->getMessages(false));
						$this->jsonReturn($result);
					}
				}
				
				// Check if only registered users can checkout and if the user is registered
				if(VmConfig::get('oncheckout_only_registered',0) && (empty($juser->id) || $juser->guest))
				{
					$result = array('error' => 1, 'msg' => vmText::_('COM_VIRTUEMART_CART_ONLY_REGISTERED'));
					$this->jsonReturn($result);
				}
				
				// Check Coupon for errors
				if (!empty($this->cart->couponCode))
				{
					if(!in_array($this->cart->couponCode, $this->cart->_triesValidateCoupon))
					{
						$this->cart->_triesValidateCoupon[] = $this->cart->couponCode;
					}
					if(count($this->cart->_triesValidateCoupon) < 8)
					{
						$message = CouponHelper::ValidateCouponCode($this->couponCode, $this->cartPrices['salesPrice']);
					}
					else
					{
						$message = vmText::_('COM_VIRTUEMART_CART_COUPON_TOO_MANY_TRIES');
					}
					if(!empty($message))
					{
						$this->cart->couponCode = '';
						$this->cart->_inCheckOut = false;
						$this->cart->setCartIntoSession();
						$result = array('error' => 1, 'msg' => $message);
						$this->jsonReturn($result);
					}
				}
				
				// Verify with shipment plugins
				if (empty($this->cart->virtuemart_shipmentmethod_id) && !$params->get('disable_shipment', 0)) 
				{
					$hasError = true;
					$verifyMsg[] = vmText::_('COM_VIRTUEMART_CART_NO_SHIPMENT_SELECTED');
				} 
				else 
				{
					JPluginHelper::importPlugin('vmshipment');
					// Add a hook here for other shipment methods, checking the data of the choosed plugin
					$dispatcher = JDispatcher::getInstance();
					$retValues = $dispatcher->trigger('plgVmOnCheckoutCheckDataShipment', array($this->cart));
					foreach ($retValues as $retVal) 
					{
						if ($retVal === true) 
						{
							break; // Shipment plugin check succesfull.
						}
						elseif ($retVal === false) 
						{
							// Missing data, ask for it (again)
							$hasError = true;
							$verifyMsg[] = $this->getMessages(false);
						}
					}
				}
				// Verify with payment plugin
				if($this->cart->pricesUnformatted['salesPrice']>0.0)
				{
					if (empty($this->cart->virtuemart_paymentmethod_id)) 
					{
						$error = true;
						$verifyMsg[] = vmText::_('COM_VIRTUEMART_CART_NO_PAYMENT_SELECTED');
					}
					else 
					{
						JPluginHelper::importPlugin('vmpayment');
						// Add a hook here for other payment methods, checking the data of the choosed plugin
						$dispatcher = JDispatcher::getInstance();
						$retValues = $dispatcher->trigger('plgVmOnCheckoutCheckDataPayment', array($this->cart));
						foreach ($retValues as $retVal) 
						{
							if ($retVal === true) 
							{
								break; // Payment plugin check succesfull.
							} 
							elseif ($retVal === false) 
							{
								// Missing data, ask for it (again)
								$hasError = true;
								$verifyMsg[] = JText::_('COM_VIRTUEMART_CART_SETPAYMENT_PLUGIN_FAILED');
								$verifyMsg[] = $this->getMessages(false);
							}
						}
					}
				}
				
				if($hasError)
				{
					$messages = implode('<br/>', $verifyMsg);
					$result = array('error' => 1, 'msg' => $messages);
					$this->jsonReturn($result);
				}
				
				if($this->cart->_redirected)
				{
					$this->cart->_redirected = false;
				}
				else
				{
					$this->cart->_inCheckOut = false;
				}
				
				if($this->cart->_blockConfirm)
				{
					$this->cart->_dataValidated = false;
					$this->cart->_inCheckOut = false;
					$result = array('error' => 1, 'msg' => $this->getMessages(false));
				}
				else
				{
					// New hash check introduced since VM 3.0.9.4
					$this->cart->_dataValidated = method_exists($this->cart, 'getCartHash') ? $this->cart->getCartHash() : true;
					$this->cart->_inCheckOut = false;
					$result = array('error' => 0, 'msg' => $this->getMessages(false), 'cart' => $this->cart);
				}
				$this->cart->setCartIntoSession(true);
				$this->jsonReturn($result);
				break;
				
			case 'test' :
				$this->jsonReturn($this->cart);
				break;
			
			default :
				break;
		}
		
		// Layout specific actions
		switch($layoutName)
		{
			case 'order_done' :
				$this->lOrderDone();
				$pathway->addItem(vmText::_('COM_VIRTUEMART_CART_THANKYOU'));
				$document->setTitle(vmText::_('COM_VIRTUEMART_CART_THANKYOU'));
				break;
			
			default:
				if($app->getUserState('proopc.btasst', $params->get('check_shipto_address'))) 
				{
					$this->cart->STsameAsBT = 1;
					$this->cart->ST = 0;
				} 
				else 
				{
					$this->cart->STsameAsBT = 0;
				}
				
				//Prepare cart data
				$this->cart->prepareCartData();

				// Set default country
				$this->setDefaultCountry();

				// Prepare registration, billing address and shipping address form fields
				$this->prepareAddressFields();
				
				// Add JS for Ajax State/Region Field
				if(class_exists('VmJsApi') && method_exists('VmJsApi', 'JcountryStateList'))
				{
					$js = '';
					
					if(is_array($this->cart->BT) && !empty($this->cart->BT['virtuemart_country_id']) && isset($this->cart->BT['virtuemart_state_id']))
					{
						VmJsApi::JcountryStateList($this->cart->BT['virtuemart_state_id']);
						$js .= '
						$("#virtuemart_country_id").change(function() {
							if ($("#virtuemart_state_id optgroup").length > 0) {
								$("#virtuemart_state_id optgroup").remove();
								$("#virtuemart_country_id").vm2front("list", {
									dest: "#virtuemart_state_id",
									ids: "",
									prefiks: ""
								});
							}
						});';
					}
					
					if(is_array($this->cart->ST) && !empty($this->cart->ST['virtuemart_country_id']) && isset($this->cart->ST['virtuemart_state_id']))
					{
						VmJsApi::JcountryStateList($this->cart->ST['virtuemart_state_id'], 'shipto_');
						$js .= '
						$("#shipto_virtuemart_country_id").change(function() {
							if ($("#shipto_virtuemart_state_id optgroup").length > 0) {
								$("#shipto_virtuemart_state_id optgroup").remove();
								$("#shipto_virtuemart_country_id").vm2front("list", {
									dest: "#shipto_virtuemart_state_id",
									ids: "",
									prefiks: ""
								});
							}
						});';
					}
					
					if(!empty($js))
					{
						$js = 'jQuery(document).ready(function($) {' . $js . '});';
						vmJsApi::addJScript('onChangeVM2front', $js);
					}
				}
				
				// Prepare continue link
				$this->prepareOPCContinueLink();

				// Get User Fields for Cart
				$userFieldsModel = VmModel::getModel('userfields');
				$igonoreTypes = array('captcha' => true, 'delimiters' => true);
				$skipFields = array('delimiter_userinfo','user_is_vendor' ,'username','password', 'password2', 'agreed', 'address_type');
				$userFieldsCart = $userFieldsModel->getUserFields('cart', $igonoreTypes, $skipFields);
				$this->userFieldsCart = $userFieldsModel->getUserFieldsFilled($userFieldsCart, $this->cart->cartfields);

				// Get checkout advertisements
				$this->checkoutAdvertise = $this->getCheckoutAdvertise();
				
				// Get products count
				$this->productsCount = $this->getProductsCount();
				
				// Prepare shipment methods selection
				$this->lSelectShipment();
				
				// Prepare payment methods selection
				$this->lSelectPayment();
				
				// Prepare coupon field
				$this->lSelectCoupon();
				
				// Prepare cart for checkout
				if ($this->cart && !VmConfig::get('use_as_catalog', 0))
				{
					$this->cart->checkoutData(false);
				}
				
				// Set pathway, page title and checkout task
				if ($this->cart->getDataValidated()) 
				{
					$pathway->addItem(vmText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU'));
					$document->setTitle(vmText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU'));
					$this->checkout_task = 'confirm';
				} 
				else 
				{
					$pathway->addItem(JText::_('COM_VIRTUEMART_CART_OVERVIEW'));
					$document->setTitle(JText::_('COM_VIRTUEMART_CART_OVERVIEW'));
					$this->checkout_task = 'checkout';
				}
				break;
		}
		
		// Do we need to set the cart session here? It consumes some time.
		// $this->cart->setCartIntoSession(true);
		
		shopFunctionsF::setVmTemplate($this, 0, 0, $layoutName);
		parent::display($tpl);
	}

	/**
	* Method to get raw request data
	* 
	* @return array Post and get requests
	*/
	private function getData()
	{
		// We are using JRequest instead of JInput to retrieve raw data.
		$rawDataPost = JRequest::get('POST', 2);
		$rawDataGet = JRequest::get('GET', 2);
		
		// POST value gets priority over GET.
		return array_merge($rawDataGet, $rawDataPost);
	}
	
	/**
	* Method to get all messages in queue
	* 
	* @param boolean $ignoreAddress Ignore address missing related messages
	* 
	* @return string Messages
	*/
	private function getMessages($ignoreAddress = true)
	{
		$messages = '';
		$missingAddressPrefix = trim(vmText::sprintf('COM_VIRTUEMART_MISSING_VALUE_FOR_FIELD', ' '));
		$msgs = JFactory::getApplication()->getMessageQueue();
		
		if(!empty($msgs))
		{
			$messages = array();
			foreach($msgs as $msg) 
			{
				if($ignoreAddress && (strpos($msg['message'], $missingAddressPrefix) === 0))
				{
					continue;
				}
			
				$this->message_type = !empty($msg['type']) ? $msg['type'] : 'warning';
				$message = str_replace(array('<br/>', '<br />', '<BR/>'), array('||*||'), $msg['message']);
				
				if(strpos($message, '||*||') !== false)
				{
					$message = explode('||*||', $message);
					$messages = array_merge($messages, $message);
				}
				else
				{
					$messages[] = $msg['message'];
				}
			}
			
			if(!empty($messages))
			{
				$messages = array_map('trim', $messages);
				$messages = array_unique(array_filter($messages));
				$messages = implode('<br/>', $messages);
			}
			else
			{
				$messages = '';
			}
		}

		return $messages;
	}
	
	/**
	* Method to get the captured messages type
	* 
	* @return string Messages
	*/
	private function getMessageType()
	{
		if(empty($this->message_type))
		{
			return '';
		}
		
		$this->message_type = strtolower($this->message_type);
		
		if(!in_array($this->message_type, array('success', 'warning', 'error', 'info')))
		{
			$this->message_type = 'warning';
		}
		
		return $this->message_type;
	}
	
	/**
	* Method to prepare cart for update
	* 
	* @return void
	*/
	private function prepareCartForUpdate()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$this->cart->_fromCart = true;
		$this->cart->_redirected = false;
		if($input->getInt('cancel', 0))
		{
			$this->cart->_inConfirm = false;
		}
		if($this->cart->getInCheckOut())
		{
			$input->set('checkout', true);
		}
	}
	
	/**
	* Method to validate user data
	* 
	* @param  string              $type
	* @param  mixed (null/object) $obj  Data object
	* @param  boolean             $redirect Redirect true or false
	* 
	* @return boolean (true/false) Returns false in case of invalid data
	*/
	private function validateUserData($type = 'BT', $obj = null ,$redirect = false)
	{
		if($obj == null)
		{
			$obj = $this->cart->{$type};
		}
		$usersModel = VmModel::getModel('user');
		return $usersModel->validateUserData($obj, $type, $redirect);
	}
	
	/**
	* Method to set the selected shipment method in cart
	* 
	* @param  boolean $force Force set even if the same method is already set
	* 
	* @return boolean False if failed
	*/
	private function setShipmentMethod($force = false) 
	{
		$virtuemart_shipmentmethod_id = vRequest::getInt('virtuemart_shipmentmethod_id', $this->cart->virtuemart_shipmentmethod_id);
		$status = true;
		
		if(($this->cart->virtuemart_shipmentmethod_id != $virtuemart_shipmentmethod_id) || $force)
		{
			$this->cart->_dataValidated = false;
			$this->cart->virtuemart_shipmentmethod_id = $virtuemart_shipmentmethod_id;
			JPluginHelper::importPlugin('vmshipment');

			//Add a hook here for other payment methods, checking the data of the choosed plugin
			$dispatcher = JDispatcher::getInstance();
			$retValues = $dispatcher->trigger('plgVmOnSelectCheckShipment', array(&$this->cart));
			
			foreach ($retValues as $retVal) 
			{
				if ($retVal === true ) 
				{
					$status = true;
					break;
				} 
				elseif ($retVal === false)
				{
					$status = false;
				}
			}
			$this->cart->setCartIntoSession();
		}
		
		return $status;
	}
	
	/**
	* Method to set the selected payment method in cart
	* 
	* @param  boolean $force Force set even if the same method is already set
	* 
	* @return boolean False if failed
	*/	
	private function setPaymentMethod($force = false)
	{
		$virtuemart_paymentmethod_id = vRequest::getInt('virtuemart_paymentmethod_id', $this->cart->virtuemart_paymentmethod_id);
		$status = true;
		
		if($this->cart->virtuemart_paymentmethod_id != $virtuemart_paymentmethod_id || $force)
		{
			$this->cart->_dataValidated = false;
			$this->cart->virtuemart_paymentmethod_id = $virtuemart_paymentmethod_id;
			JPluginHelper::importPlugin('vmpayment');
			
			//Add a hook here for other payment methods, checking the data of the choosed plugin
			$msg = '';
			$dispatcher = JDispatcher::getInstance();
			$retValues = $dispatcher->trigger('plgVmOnSelectCheckPayment', array($this->cart, &$msg));
			
			foreach($retValues as $retVal)
			{
				if ($retVal === true ) 
				{
					$status = true;
					break;
				} 
				elseif ($retVal === false)
				{
					$status = false;
				}
			}
			
			$this->cart->setCartIntoSession();
		}
		
		return $status;
	}
	
	private function storePartUserinfo(&$data)
	{
		$userModel = VmModel::getModel('user');
		$user = $this->juser;
		$userinfo = $userModel->getTable('userinfos');
		$manager = ($user->authorise('core.admin','com_virtuemart') || $user->authorise('core.manage','com_virtuemart'));
		
		if($data['address_type'] == 'BT')
		{
			if(isset($data['virtuemart_userinfo_id']) && $data['virtuemart_userinfo_id'] != 0)
			{
				if(!$manager )
				{
					$userinfo->load($data['virtuemart_userinfo_id']);
					if($userinfo->virtuemart_user_id != $user->id)
					{
						vmError('Hacking attempt as admin?','Hacking attempt storeAddress');
						return false;
					}
				}
			}
			else
			{
				if(!$manager)
				{
					$userId = $user->id;
				}
				else
				{
					$userId = (int)$data['virtuemart_user_id'];
				}
				
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
				            ->select('virtuemart_userinfo_id')
				            ->from('#__virtuemart_userinfos')
				            ->where('virtuemart_user_id = ' . $userId)
				            ->where('address_type = ' . $db->quote('BT'));
				$db->setQuery($query);
				$total = $db->loadColumn();

				if (count($total) > 0)
				{
					$data['virtuemart_userinfo_id'] = (int)$total[0];
				}
				else
				{
					$data['virtuemart_userinfo_id'] = 0;
				}
				
				$userinfo->load($data['virtuemart_userinfo_id']);
			}
			
			$data = (array) $data;
			$userInfoData = $userModel->_prepareUserFields($data, 'BT', $userinfo);
			$userinfo->bindChecknStore($userInfoData);
		}
		// Check for fields with the the 'shipto_' prefix; that means a (new) shipto address.
		elseif($data['address_type'] == 'ST' || isset($data['shipto_address_type_name']))
		{
			$dataST = array();
			$_pattern = '/^shipto_/';

			foreach ($data as $_k => $_v)
			{
				if (preg_match($_pattern, $_k))
				{
					$_new = preg_replace($_pattern, '', $_k);
					$dataST[$_new] = $_v;
				}
			}
			
			if(isset($dataST['virtuemart_userinfo_id']) && $dataST['virtuemart_userinfo_id']!=0)
			{
				$dataST['virtuemart_userinfo_id'] = (int) $dataST['virtuemart_userinfo_id'];
				if(!$manager)
				{
					$userinfo->load($dataST['virtuemart_userinfo_id']);
					if($userinfo->virtuemart_user_id != $user->id)
					{
						vmError('Hacking attempt as admin?','Hacking attempt store address');
						return false;
					}
				}
			}

			if(empty($userinfo->virtuemart_user_id))
			{
				if(!$manager)
				{
					$dataST['virtuemart_user_id'] = $user->id;
				}
				else
				{
					if(isset($data['virtuemart_user_id']))
					{
						$dataST['virtuemart_user_id'] = (int)$data['virtuemart_user_id'];
					}
					else
					{
						//Disadvantage is that admins should not change the ST address in the FE (what should never happen anyway.)
						$dataST['virtuemart_user_id'] = $user->id;
					}
				}
			}

			$dataST = (array) $dataST;
			$dataST['address_type'] = 'ST';
			$userfielddata = $userModel->_prepareUserFields($dataST, 'ST', $userinfo);
			$userinfo->bindChecknStore($userfielddata);
			$this->cart->selected_shipto = $userinfo->virtuemart_userinfo_id;
		}
		
		return $userinfo->virtuemart_userinfo_id;
	}
	
	/**
	* Method to add user details to cart if the user logs in using other modules
	* 
	* @return void
	*/
	private function refreshCartUser()
	{
		$app = JFactory::getApplication();
		$wasGuest = $app->getUserState('proopc.guest.user', 0);
		
		if($this->juser->guest && !$wasGuest)
		{
			$app->setUserState('proopc.guest.user', 1);
		}
		elseif(!$this->juser->guest && $wasGuest)
		{
			$app->setUserState('proopc.guest.user', 0);
			$user_id = $this->juser->get('id');
			$user_BT = $this->getUserInfo('BT', $user_id);
			$update = false;
			
			if(!empty($user_BT)) 
			{
				$this->cart->saveAddressInCart($user_BT, 'BT', true);
				$update = true;
			}
			if($this->cart->STsameAsBT == 0) 
			{
				$user_ST = $this->getUserInfo('ST', $user_id);
				
				if(!empty($user_ST))
				{
					$this->cart->saveAddressInCart($user_ST, 'BT', true);
					$update = true;
				}
			}
			if($update)
			{
				$this->cart->prepareCartData();
			}
		}
	}

	/**
	* Method to prepare registration, billing address and shipping address fields
	* 
	* @return void
	*/
	private function prepareAddressFields()
	{
		$this->cart->prepareAddressFieldsInCart();
		$notNeeded = array('agreed');
		
		// Prepare User Registration and BT Address Fields
		$btFields = $this->cart->BTaddress;
		$regFields = $btFields;
		$standardRegFields = array('email', 'name', 'username', 'password', 'password2');
		$customRegFields = $this->getCustomRegFields();
		
		// Prepare reCaptcha Field
		$user_params = JComponentHelper::getParams('com_users');
		$captcha = $user_params->get('captcha', 0);
		$captchaFields = array();
		
		if(!empty($captcha) && $this->params->get('enable_recaptcha', 0))
		{
			JPluginHelper::importPlugin('captcha');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onInit', 'dynamic_recaptcha_1');
			$captchaFields['captcha'] = array (
																	'name' => 'captcha',
																	'value' => '',
																	'title' => 'COM_USERS_CAPTCHA_LABEL',
																	'type' => 'captcha',
																	'required' => 1,
																	'hidden' => 0,
																	'formcode' => '<div id="dynamic_recaptcha_1"></div>',
																	'description' => 'COM_USERS_CAPTCHA_DESC' 
																);
		}
		
		// Finalize all registration and BT address fields
		if(!empty($btFields['fields']) && is_array($btFields['fields']))
		{
			$regFields['fields'] = array();
			
			foreach($btFields['fields'] as $name => &$field)
			{
				if(in_array($name, $notNeeded) || ($field['type'] == 'delimiter'))
				{
					unset($btFields['fields'][$name]);
				}
				elseif(in_array($name, $standardRegFields))
				{
					if($name != 'email')
					{
						unset($btFields['fields'][$name]);
					}
					if($name == 'name' && $this->params->get('hide_name_field', 0))
					{
						continue;
					}
					elseif($name == 'username' && $this->params->get('auto_generate_username', 0))
					{
						continue;
					}
					$regFields['fields'][$name] = $field;
				}
				elseif(in_array($name, $customRegFields))
				{
					$regFields['fields'][$name] = $field;
				}
			}
			
			if(count($captchaFields))
			{
				$regFields['fields'] = array_merge($regFields['fields'], $captchaFields);
			}
			
			$this->regFields = $regFields;
			$this->btFields = $btFields;
		}
		
		// Prepare ST Address Fields
		$stFields = $this->cart->STaddress;
		if(!empty($stFields['fields']))
		{
			foreach($stFields['fields'] as $name => &$field)
			{
				if(in_array($name, $notNeeded) || ($field['type'] == 'delimiter'))
				{
					unset($stFields['fields'][$name]);
				}
			}
		}
		
		$this->stFields = $stFields;
		$this->selectSTName = $this->getUserSTList();
	}
	
	/**
	* Method to get custom registration fields set in the plugin parameter
	* 
	* @return array Custom registration fields name array
	*/
	private function getCustomRegFields()
	{
		$customRegFields = $this->params->get('custom_registration_fields', '');
		
		// Check for custom registration fields
		if(!empty($customRegFields)) 
		{
			if(is_string($customRegFields))
			{
				if(strpos($customRegFields, ',') !== false)
				{
					$customRegFields = explode(',', $customRegFields);
				}
				else
				{
					$customRegFields = array($customRegFields);
				}
				
				$customRegFields = array_map('trim', $customRegFields);
			}
		}
		else
		{
			$customRegFields = array();
		}
		
		return $customRegFields;
	}
	
	/**
	* Method to prepare the continue link
	* 
	* @return void
	*/
	private function prepareOPCContinueLink() 
	{
		$app               = JFactory::getApplication();
		$category_id       = (int) shopFunctionsF::getLastVisitedCategoryId();
		$last_visited_url  = $app->getUserState('proopc.lastvisited.url', '');
		$link              = !empty($last_visited_url) ? $last_visited_url : JUri::root(true);
		if(!empty($category_id)) 
		{
			$link = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $category_id, false);
		}
		$this->continue_link = $link;
		$this->continue_link_html = '<a href="' . $link . '" >' . vmText::_('COM_VIRTUEMART_CONTINUE_SHOPPING') . '</a>';
	}
	
	/**
	* Method to display the order done page
	* 
	* @return void
	*/
	public function lOrderDone()
	{
		$display_title = vRequest::getBool('display_title', true);
		$this->assignRef('display_title', $display_title);
		// Do not change this. It contains the payment form
		$this->html = vRequest::get('html', vmText::_('COM_VIRTUEMART_ORDER_PROCESSED'));
		// Shows Thank you page or error due payment plugins like paypal express
	}
	
	/**
	* Method to prepare coupon field display variables
	* 
	* @return void
	*/
	public function lSelectCoupon() 
	{
		$this->couponCode = isset($this->cart->couponCode) ? $this->cart->couponCode : '';
		$coupon_text = $this->cart->couponCode ?
		               vmText::_('COM_VIRTUEMART_COUPON_CODE_CHANGE') : 
		               vmText::_('COM_VIRTUEMART_COUPON_CODE_ENTER');
		$this->coupon_text = $coupon_text;
	}

	/**
	* Method to prepare the list of shipping methods for selection
	* 
	* @return void
	*/
	public function lSelectShipment() 
	{
		if($this->checkShipmentMethodsConfigured())
		{
			JPluginHelper::importPlugin('vmshipment');
			$dispatcher = JDispatcher::getInstance();
			$selectedShipment = empty($this->cart->virtuemart_shipmentmethod_id) ? 0 : $this->cart->virtuemart_shipmentmethod_id;
			$shipments_shipment_rates = array();
			
			// Trigger plgVmDisplayListFEShipment to get all available shipment methods
			$return = $dispatcher->trigger('plgVmDisplayListFEShipment', array($this->cart, $selectedShipment, &$shipments_shipment_rates));
			
			// Assign the values to our view object
			$this->found_shipment_method = (count($shipments_shipment_rates) > 0);
			$this->shipments_shipment_rates = $shipments_shipment_rates;
		}
		else
		{
			$this->found_shipment_method = false;
			$this->shipments_shipment_rates = array();
		}
		$this->shipment_not_found_text = vmText::_('COM_VIRTUEMART_CART_NO_SHIPPING_METHOD_PUBLIC');
	}

	/**
	* Method to prepare the list of payment methods for selection
	* 
	* @return void
	*/
	public function lSelectPayment() 
	{
		if($this->checkPaymentMethodsConfigured()) 
		{
			JPluginHelper::importPlugin('vmpayment');
			$dispatcher = JDispatcher::getInstance();
			$selectedPayment = empty($this->cart->virtuemart_paymentmethod_id) ? 0 : $this->cart->virtuemart_paymentmethod_id;
			$paymentplugins_payments = array();
			
			// Trigger plgVmDisplayListFEPayment to get all available payment methods
			$return = $dispatcher->trigger('plgVmDisplayListFEPayment', array($this->cart, $selectedPayment, &$paymentplugins_payments));
			
			// Assign the values to our view object
			$this->found_payment_method = (count($paymentplugins_payments) > 0);
			$this->paymentplugins_payments = $paymentplugins_payments;
		}
		else
		{
			// Assign the values to our view object
			$this->found_payment_method = false;
			$this->paymentplugins_payments = array();
		}
		
		$this->payment_not_found_text = vmText::sprintf('COM_VIRTUEMART_CART_NO_PAYMENT_METHOD_PUBLIC', JText::_('PLG_VPONEPAGECHECKOUT_FILLUP_ADDRESS_FIELDS'));
		
		if($this->found_payment_method)
		{
			$methods = $this->getPaymentMethods();

			foreach($methods as $key => &$method)
			{
				$params = $this->decodePluginParams($method->payment_params);
				$method->payment_params = $params;
			}
			
			$finds    = array(
                     '<table', '</table', 'border="0" cellspacing="0" cellpadding="2" width="100%"', 
                     'class="wrapper_paymentdetails"', '<tr valign="top"', '<tr valign="middle"',
                     '<tr>', '<tr', '</tr', '<td nowrap width="10%" align="right"', 
                     '<td>', '<td', '</td', '<br />', 'hasTip'
                    );
			$replaces = array(
                     '<div', '</div', 'class="proopc-creditcard-info"', 'class="wrapper_paymentdetails proopc-creditcard-info"',
                     '<div class="proopc-row"', '<div class="proopc-row"', '<div class="proopc-row">',
                     '<div class="proopc-row"', '</div', '<div class="creditcard-label"', 
                     '<div>', '<div', '</div', '', 'hover-tootip'
                    );
			
			//vpdump($methods);exit;
			
			foreach($this->paymentplugins_payments as &$payments)
			{
				if(is_array($payments))
				{
					foreach($payments as &$payment)
					{
						$payment = trim($payment);
						
						// Amazon Checkout returns blank HTML
						if(empty($payment))
						{
							continue;
						}
						
						// Considering the first input field is the payment as selection radio button
						// If it is not then we will have to use JavaScript to add add the onclick attribute to the correct element.
						$payment = substr_replace($payment, '<input onclick="return ProOPC.setpayment(this);"', strpos($payment, '<input'), strlen('<input'));

						foreach($methods as $key => &$method)
						{
							if(strpos($payment, 'id="payment_id_' . $method->virtuemart_paymentmethod_id . '"') !== false)
							{
								// Add payment method information
								$info = 'data-pmtype="' . $method->payment_element . '" data-paypalproduct="' . $method->payment_params->get('paypalproduct', 'false') . '"';
								$payment = str_replace('name="virtuemart_paymentmethod_id"', 'name="virtuemart_paymentmethod_id" ' . $info, $payment);
								
								// Convert table based layout to normal div based layout
								$payment = str_replace($finds, $replaces, $payment);
								
								// Add credit card area information
								if(strpos($payment, 'vmpayment_cardinfo') !== false)
								{
									if(strpos($payment, 'checked="checked"') !== false)
									{
										$payment = str_replace('vmpayment_cardinfo', 'vmpayment_cardinfo additional-payment-info ' . $method->payment_element . $method->payment_params->get('paypalproduct', '') . ' show', $payment);
									}
									else
									{
										$payment = str_replace('vmpayment_cardinfo', 'vmpayment_cardinfo additional-payment-info ' . $method->payment_element . $method->payment_params->get('paypalproduct', '') . ' hide', $payment);
									}
								}
								// We have added the required information for this payment
								// Unset the method for repetative check
								unset($methods[$key]);
								break;
							}
						}
					}
				}
			}
		}
	}

	/**
	*  Method to get all published payment methods
	* 
	* @return array List of payment methods
	*/
	private function getPaymentMethods()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
		            ->select('virtuemart_paymentmethod_id, payment_element, payment_params')
		            ->from('#__virtuemart_paymentmethods')
		            ->where('published = 1');
		$db->setQuery($query);
		$methods = $db->loadObjectList();
		if(empty($methods))
		{
			$methods = array();
		}
		return $methods;
	}

	/**
	* Method to decode VM Payment and Shipment params string to JRegistry object
	* 
	* @param string $params_string Encodes params string
	* 
	* @return object JRegistry object of params
	*/	
	private function decodePluginParams($params_string)
	{
		$params = array();
		
		if(!empty($params_string) && is_string($params_string))
		{
			if(strpos($params_string, '|') !== false)
			{
				$items = explode('|', $params_string);
				$items = array_filter($items);
				foreach($items as $key => $item)
				{
					list($name, $value) = explode('=', $item);
					if((strpos($value, '{') !== false && strpos($value, '}') !== false) || (strpos($value, '[') !== false && strpos($value, ']') !== false))
					{
						$value = json_decode($value);
					}
					else
					{
						$value = str_replace(array('"', '\''), array('', ''), $value);
					}
					$params[$name] = $value;
				}
			}
			else
			{
				list($name, $value) = explode('=', $params_string);
				if((strpos($value, '{') !== false && strpos($value, '}') !== false) || (strpos($value, '[') !== false && strpos($value, ']') !== false))
				{
					$value = json_decode($value);
				}
				else
				{
					$value = str_replace(array('"', '\''), array('', ''), $value);
				}
				$params[$name] = $value;
			}
		}
		
		$tmp = new JRegistry;
		$tmp->loadArray($params);
		
		return $tmp;
	}
	
	/**
	* Method to check if shipment method is configured for the store
	* 
	* @return boolean
	*/
	public function checkShipmentMethodsConfigured()
	{
		$app = JFactory::getApplication();
		$shipmentModel = VmModel::getModel('Shipmentmethod');
		$shipments = $shipmentModel->getShipments();
		
		if(empty($shipments))
		{
			$app->enqueueMessage(vmText::_('COM_VIRTUEMART_NO_SHIPPING_METHODS_CONFIGURED'));
			if($this->juser->authorise('core.admin','com_virtuemart') || $this->juser->authorise('core.manage','com_virtuemart') || VmConfig::isSuperVendor()) 
			{
				$link = JUri::root() . 'administrator/index.php?option=com_virtuemart&view=shipmentmethod';
				$text = vmText::sprintf('COM_VIRTUEMART_NO_SHIPPING_METHODS_CONFIGURED_LINK', '<a href="' . $link . '" rel="nofollow">' . $link . '</a>');
				$app->enqueueMessage($text);
			}
			
			$this->found_shipment_method = false;
			$this->cart->virtuemart_shipmentmethod_id = 0;
			return false;
		}
		return true;
	}
	
	/**
	* Method to check if payment method is configured for the store
	* 
	* @return boolean
	*/
	private function checkPaymentMethodsConfigured()
	{
		$app = JFactory::getApplication();
		$paymentModel = VmModel::getModel('Paymentmethod');
		$payments = $paymentModel->getPayments(true, false);
		
		if(empty($payments))
		{
			$app->enqueueMessage(vmText::_('COM_VIRTUEMART_NO_PAYMENT_METHODS_CONFIGURED'));
			if($this->juser->authorise('core.admin','com_virtuemart') || $this->juser->authorise('core.manage','com_virtuemart') || VmConfig::isSuperVendor()) 
			{
				$link = JUri::root() . 'administrator/index.php?option=com_virtuemart&view=paymentmethod';
				$text = vmText::sprintf('COM_VIRTUEMART_NO_PAYMENT_METHODS_CONFIGURED_LINK', '<a href="' . $link . '" rel="nofollow">' . $link . '</a>');
				$app->enqueueMessage($text);
			}
			
			$this->found_payment_method = false;
			$this->cart->virtuemart_paymentmethod_id = 0;
			return false;
		}
		return true;
	}
	
	/**
	* Method set default country and state in the cart
	* if enabled in the plugin settings.
	* 
	* @return void
	*/
	private function setDefaultCountry()
	{
		$default_country_id = (int) $this->params->get('default_country', -1);
		$default_state_id = 0;
		
		if(empty($default_country_id))
		{
			$vendor = $this->getVendorCountryState();
			$default_country_id = $vendor['country_id'];
			$default_state_id = $vendor['state_id'];
		}
		
		// If default country available set the same in the cart
		if($default_country_id > 0)
		{
			$update = false;
			
			if($this->cart->STsameAsBT == 0 && empty($this->cart->ST['virtuemart_country_id']))
			{
				$update = true;
				$data = array();
				$data['shipto_virtuemart_country_id'] = $default_country_id;
				if(!empty($default_state_id) && empty($this->cart->ST['virtuemart_state_id']))
				{
					$data['shipto_virtuemart_state_id'] = $default_state_id;
				}
				$this->cart->saveAddressInCart($data, 'ST', true, 'shipto_');
			}
			
			if(empty($this->cart->BT['virtuemart_country_id']))
			{
				$update = true;
				$data = array();
				$data['virtuemart_country_id'] = $default_country_id;
				if(!empty($default_state_id) && empty($this->cart->BT['virtuemart_state_id']))
				{
					$data['virtuemart_state_id'] = $default_state_id;
				}
				
				$this->cart->saveAddressInCart($data, 'BT', true);
				
				if($this->juser->id)
				{
					$data['virtuemart_user_id'] = $this->juser->id;
					$data['agreed'] = !empty($data['agreed']) ? $data['agreed'] : 1;
					$data['address_type'] = 'BT';
					$userinfo_id = $this->storePartUserinfo($data, 'BT');
				}
			}
			
			if($update)
			{
				$this->cart->prepareCartData();
			}
		}
	}

	/**
	* Method to get present cart vendors country id and state id
	* 
	* @return array Array containing country_id and state_id.
	*/
	private function getVendorCountryState()
	{
		if(self::$vendorCountryState === null)
		{
			$vendorCountryState = array();
			$vendor_id = (int) $this->cart->vendor->virtuemart_vendor_id;
			
			if(!empty($vendor_id))
			{
				$db = JFactory::getDBO();
				$query = $db->getQuery(true)
				            ->select('b.virtuemart_country_id, b.virtuemart_state_id')
				            ->from('`#__virtuemart_vmusers` AS a')
				            ->join('LEFT', '`#__virtuemart_userinfos` AS b ON a.virtuemart_user_id = b.virtuemart_user_id')
				            ->where('a.virtuemart_vendor_id	= ' . $vendor_id)
				            ->where('b.address_type	= ' . $db->quote('BT'));
				$db->setQuery($query);
				$vendor = $db->loadObject();
				
				if(!empty($vendor))
				{
					$vendorCountryState['country_id'] = (int) $vendor->virtuemart_country_id;
					$vendorCountryState['state_id'] = (int) $vendor->virtuemart_state_id;
				}
			}
			else
			{
				$vendorCountryState['country_id'] = 0;
				$vendorCountryState['state_id'] = 0;
			}
			
			self::$vendorCountryState = $vendorCountryState;
		}
		
		return self::$vendorCountryState;
	}

	/**
	* Method to get the cart total is payment currency
	* 
	* @return string Formated amount in the payment currency
	*/
	public function getTotalInPaymentCurrency()
	{
		if(empty($this->cart->virtuemart_paymentmethod_id))
		{
			return null;
		}

		if(!$this->cart->paymentCurrency || ($this->cart->paymentCurrency == $this->cart->pricesCurrency)) 
		{
			return null;
		}
		
		if(!isset($this->cart->cartPrices['billTotal']))
		{
			$this->prepareCartForUpdate();
			$this->cart->prepareCartData();
			$this->cart->setCartIntoSession(true);
			$this->cart->prepareCartData();
		}

		$paymentCurrency = CurrencyDisplay::getInstance($this->cart->paymentCurrency);
		$totalInPaymentCurrency = $paymentCurrency->priceDisplay($this->cart->cartPrices['billTotal'], $this->cart->paymentCurrency);
		$currencyDisplay = CurrencyDisplay::getInstance($this->cart->pricesCurrency);
		return $totalInPaymentCurrency;
	}

	/**
	* Method to get the checkout advertisements set by coupon, payment and shipment plugins
	* 
	* @return array Array of advertisement HTML
	*/
	public function getCheckoutAdvertise()
	{
		JPluginHelper::importPlugin('vmcoupon');
		JPluginHelper::importPlugin('vmpayment');
		JPluginHelper::importPlugin('vmshipment');
		$dispatcher = JDispatcher::getInstance();
		$checkoutAdvertises = array();
		$return = $dispatcher->trigger('plgVmOnCheckoutAdvertise', array($this->cart, &$checkoutAdvertises));
	
		if(!empty($checkoutAdvertises))
		{
			foreach($checkoutAdvertises as $key => &$checkoutAdvertise)
			{
				$raw = trim(preg_replace('/^\s+|\n|\r|\s+$/m', '', $checkoutAdvertise));
				if(strlen($raw) == 0)
				{
					unset($checkoutAdvertises[$key]);
				}
			}
		}

		return $checkoutAdvertises;
	}

	/**
	* Method to return the response from login requests
	* 
	* @param mixed (string/boolean) $message Login request return value
	* 
	* @return void
	*/
	private function ajaxResponse($message)
	{
		$obLevel = ob_get_level();
		if($obLevel)
		{
			while ($obLevel > 0)
			{
				ob_end_clean();
				$obLevel --;
			}
		}
		else
		{
			ob_clean();
		}
		echo $message;
		die;
	}
	
	/**
	* Method to return JSON object values with proper header
	* 
	* @param arry $message Array to be return as JSON object
	* 
	* @return void
	*/
	private function jsonReturn($message = array()) 
	{
		$app = JFactory::getApplication();
		$obLevel = ob_get_level();
		if($obLevel)
		{
			while ($obLevel > 0)
			{
				ob_end_clean();
				$obLevel --;
			}
		}
		else
		{
			ob_clean();
		}
		header('Content-type: application/text');
		header('Content-type: application/json');
		header('Cache-Control: public,max-age=1,must-revalidate');
		header('Expires: ' . gmdate('D, d M Y H:i:s', ($_SERVER['REQUEST_TIME'] + 1)) . ' GMT');
		header('Last-modified: ' . gmdate('D, d M Y H:i:s', $_SERVER['REQUEST_TIME']) . ' GMT');
		if(function_exists('header_remove')) 
		{
			header_remove('Pragma');
		}
		$this->time_end = microtime(true);
		$execution_time = ($this->time_end - $this->time_start);
		if($execution_time < 1)
		{
			$execution_time = number_format(($execution_time * 1000), 2, '.', ',') . ' ms';
		}
		else
		{
			$execution_time = number_format($execution_time, 6, '.', ',') . ' s';
		}
		$message = (array) $message;
		$message['execution_time'] = $execution_time;
		echo json_encode($message);
		flush();
		$app->close();
	}

	/**
	* Method to get the billing address of a user by Joomla user id
	* 
	* @param integer $userID Joomla user id
	* 
	* @return array 
	*/
	private function getUserInfo($type = 'BT', $user_id)
	{
		$type = strtoupper($type);
		$db = JFactory::getDBO();
		// Get userfields
		$query = $db->getQuery(true)
		            ->select('field.name')
		            ->from('`#__virtuemart_userfields` AS field')
		            ->where('field.type NOT IN (' . $db->quote('password') . ', ' . $db->quote('delimiter') . ')')
		            ->where('field.name NOT IN (' . $db->quote('username') . ', '.$db->quote('email') . ', ' . $db->quote('name') . ')')
		            ->where('field.readonly = 0')
		            ->where('field.published = 1');
		if($type == 'BT')
		{
			$query->where('field.registration = 1');
		}
		elseif($type == 'ST')
		{
			$query->where('field.shipment = 1');
		}
		$db->setQuery($query);
		$userFields = $db->loadColumn();
		
		if(empty($userFields))
		{
			return false;
		}
		
		foreach($userFields as $key => &$userField) 
		{
			if(version_compare(JVERSION, '3.0.0', 'ge'))
			{
				$userFields[$key] = $db->quoteName($userField);
			}
			else
			{
				$userFields[$key] = $db->nameQuote($userField);
			}
		}

		// The the user info
		$query->getQuery(true)
		      ->select($userFields)
		      ->from('`#__virtuemart_userinfos`')
		      ->where('virtuemart_user_id = ' . (int) $user_id)
		      ->where('address_type = ' . $db->Quote('BT'));
		$db->setQuery($query);
		$info = $db->loadAssoc();
		
		if(empty($info)) 
		{
			return false;
		}
		return $info;
	}

	/**
	* Method to get saved shipping address list of an user
	* 
	* @return string HTML dropdown select list
	*/
	private function getUserSTList() 
	{	
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
		            ->select('virtuemart_userinfo_id AS value, address_type_name AS text')
		            ->from('`#__virtuemart_userinfos`')
		            ->where('virtuemart_user_id = ' . (int) $this->juser->get('id'))
		            ->where('address_type = ' . $db->quote('ST'))
		            ->order('virtuemart_userinfo_id ASC');
		$db->setQuery($query);
		$addresses = $db->loadObjectList();

		if(empty($addresses))
		{
			return false;
		}
		
		$selectedAddress = isset($this->cart->selected_shipto) ? intval($this->cart->selected_shipto) : $addresses[0]->value;
		
		$options = array();
		$options[] = JHtml::_('select.option', (int) 0, '- ' . JText::_('JNEW') . ' -');
		
		foreach($addresses as $address)
		{
			$options[] = JHtml::_('select.option', (int) $address->value, $address->text);
		}
		
		$html = JHtml::_('select.genericlist', $options, 'proopc-select-st', 'onchange="return ProOPC.selectSTAddress(this);" class="proopc-select-st"', 'value', 'text', (int) $selectedAddress, 'proopc-select-st');
		return $html;
	}	
	
	/**
	* Method to check if we can checkout
	* Ajax method set 'proopc.cancheckout' in user state after verifying the cart state 
	* 
	* @return boolean
	*/
	public function canCheckout()
	{
		if($this->params->get('extra_security', 0))
		{
			return JFactory::getApplication()->getUserState('proopc.cancheckout', false);
		}
		return true;
	}
	
	/**
	* Method to register a new user in cart.
	* It directlt return the json values against regsitration request.
	* 
	* @return void
	*/
	private function registerUser()
	{
		// $method = version_compare(JVERSION, '3.0.0', 'ge') ? $this->input->getMethod() : JRequest::getMethod();
		$method = 'post';
		// Check Token
		JSession::checkToken($method) or $this->jsonReturn(array('error' => 1, 'msg' => JText::_('JINVALID_TOKEN')));

		// Check if ajax has set the registration form loaded.
		if(!$this->canCheckout())
		{
			$result = array('error' => 1, 'msg' => 'Please check for JavaScript errors.', 'reload' => 1);
			$this->jsonReturn($result);
		}
				
		// If cart is empty do not allow registration
		// If ajax checkout is enabled then this will 100% protect us from bots.
		if(empty($this->cart->cartProductsData))
		{
			$result = array('error' => 1, 'msg' => vmText::_('COM_VIRTUEMART_EMPTY_CART'), 'reload' => 1);
			$this->jsonReturn($result);
		}
		
		if(!$this->juser->guest || ($this->juser->id > 0))
		{
			$result = array('error' => 1, 'msg' => 'You are already logged into the system', 'reload' => 1);
			$this->jsonReturn($result);
		}

		// Get Joomla Users config.
		$usersConfig = JComponentHelper::getParams('com_users');
		$data        = $this->getData();
		
		// Check if user registration is not allowed.
		$allowUserRegistration = $usersConfig->get('allowUserRegistration', 1);
		if(empty($allowUserRegistration))
		{
			$result = array('error' => 1, 'msg' => vmText::_('COM_VIRTUEMART_ACCESS_FORBIDDEN'));
			$this->jsonReturn($result);
		}
		
		// Check for captcha
		$captcha = $usersConfig->get('captcha', 0);
		if(!empty($captcha) && $this->params->get('enable_recaptcha', 0))
		{
			JPluginHelper::importPlugin('captcha');
			$dispatcher = JDispatcher::getInstance();
			$response   = isset($data['recaptcha_response_field']) ? $data['recaptcha_response_field'] : '';
			$returns    = $dispatcher->trigger('onCheckAnswer', $response);
			
			if(!empty($returns))
			{
				foreach($returns as $return)
				{
					if($return === false)
					{
						$result = array('error' => 1, 'msg' => $dispatcher->getError());
						$this->jsonReturn($result);
					}
				}
			}
		}
		
		// Now we can proceed
		jimport('joomla.user.helper');
		
		$app              = JFactory::getApplication();
		$date             = JFactory::getDate();
		$customRegFields  = $this->getCustomRegFields();
		$userActivation   = $usersConfig->get('useractivation');
		$doUserActivation = ($userActivation == 1 || $userActivation == 2);
		$user             = new JUser();
		
		if(!empty($customRegFields))
		{
			JPluginHelper::importPlugin('vmuserfield');
			$dispatcher = JDispatcher::getInstance();
			$valid = true;
			$dispatcher->trigger('plgVmOnBeforeUserfieldDataSave', array(&$valid, $this->juser->id, &$data, $user));
			
			if($valid == false)
			{
				$result = array('error' => 1, 'msg' => $this->getMessages());
				$this->jsonReturn($result);
			}
		}
		
		$data['username']  = version_compare(JVERSION, '3.0.0', 'ge') ? 
		                     $app->input->$method->get('username', '', 'USERNAME') :
		                     JRequest::getVar('username', '', $method, 'USERNAME');
		$data['password']  = version_compare(JVERSION, '3.0.0', 'ge') ? 
		                     $app->input->$method->get('password', '', 'RAW') :
		                     JRequest::getString('password', '', $method, JREQUEST_ALLOWRAW);
		$data['password2'] = version_compare(JVERSION, '3.0.0', 'ge') ? 
		                     $app->input->$method->get('password2', '', 'RAW') :
		                     JRequest::getString('password2', '', $method, JREQUEST_ALLOWRAW);
		$data['email']     = vRequest::getEmail('email', '');
		$name              = vRequest::getWord('name', '');
		$data['name']      = str_replace(array('\'', '"', ',', '%', '*', '/', '\\', '?', '^', '`', '{','}' ,'|', '~'), array(''), $name);
		
		if(version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$data['secretkey'] = vRequest::get('secretkey', '');
		}
		
		if($this->params->get('auto_generate_username', 0) && empty($data['username']))
		{
			if(strpos($data['email'], '@') !== false)
			{
				$parts = explode('@', $data['email']);
				$username = $parts[0];
			}
			else
			{
				$username = $data['email'];
			}
			
			$username = str_replace(array(' ', '<', '>', '\\', '"', '\'', '%', ';', '(', ')', '&'), array(''), trim($username));
			if(strlen($username) < 3)
			{
				$username = $username . rand(1, 10);
			}
			
			if(!empty($username))
			{
				$db = JFactory::getDbo();

				// Escape the username token.
				$search = $db->quote($db->escape($username, true) . '%');

				$query = $db->getQuery(true)
				            ->select('username')
				            ->from('#__users')
				            ->where('username LIKE ' . $search);
				$db->setQuery($query);
				$existing = $db->loadColumn();
				
				if(in_array($username, $existing))
				{
					for($i=1;$i<=100;$i++)
					{
						$username = $username . rand(1, 1000);
						if(!in_array($username, $existing))
						{
							break;
						}
					}
				}
			}
			
			$data['username'] = $username;
		}
		
		if($this->params->get('hide_name_field', 0) && empty($data['name']))
		{
			if(!empty($data['username']))
			{
				$data['name'] = $data['username'];
				$data['name'] = ucfirst(str_replace(array('.', '_', '-'), array(' '), $data['name']));
			}
		}
		
		// Replace all '@' sign by hyphens.
		$data['name'] = str_replace('@', '-', $data['name']);
		
		// Remove special chars but preserves dots, hyphens and spaces.
		// Not removing special characters to support other non-latin languages.
		// $data['name'] = preg_replace('/[^A-Za-z0-9\. -]/', '', $data['name']);

		if($this->params->get('auto_password', 0) && empty($data['password']) && empty($data['password2']))
		{
			$data['password'] = JUserHelper::genRandomPassword();
			$data['password2'] = $data['password'];
		}

		// Bind user data
		if(!$user->bind($data))
		{
			$instanceError = $user->getError();
			if(!empty($instanceError))
			{
				// Enqueue instance error with all other errors enqueued previously.
				$app->enqueueMessage($instanceError, 'error');
			}
			$result = array('error' => 1, 'msg' => $this->getMessages());
			$this->jsonReturn($result);
		}
		
		// Get default use type
		$userType = $usersConfig->get('new_usertype') ? $usersConfig->get('new_usertype') : 2;
		
		// Set user type
		$user->set('usertype', $userType);
		$user->groups[] = $userType;
		
		// Set registration date
		$user->set('registerDate', $date->toSQL());

		// If user activation is turned on, we need to set the activation information
		if($doUserActivation)
		{
			$activation_key = JApplication::getHash(JUserHelper::genRandomPassword());
			$user->set('activation', $activation_key);
			$user->set('block', 1);
			// $user->set('lastvisitDate', '0000-00-00 00:00:00');
		}

		if(isset($data['language']))
		{
			$user->setParam('language', $data['language']);
		}

		// Save the JUser object
		if (!$user->save())
		{
			$instanceError = $user->getError();
			if(!empty($instanceError))
			{
				$app->enqueueMessage($instanceError, 'error');
			}
			$result = array('error' => 1, 'msg' => $this->getMessages());
			$this->jsonReturn($result);
		} 
		
		// Get the newly created user's id
		$user_id = $user->get('id');
		
		// We may need the following later if we try to save it in VirtueMart User Table
		$userModel = VmModel::getModel('user');
		$data['virtuemart_user_id'] = $user_id;
		$userModel->setId($user_id);

		$data['name']     = $user->get('name');
		$data['username'] = $user->get('username');
		$data['email']    = $user->get('email');
		$data['language'] = $user->get('language');
		$data['editor']   = $user->get('editor');

		if(isset($this->cart->tosAccepted)) 
		{
			$data['agreed'] = $this->cart->tosAccepted ? 1 : 0;
		}
		
		if(!empty($customRegFields))
		{
			// We do not have all required VirtueMart shopper fields data during registration.
			// So we will not consider the errors thrown.
			$return = $this->cart->saveAddressInCart($data, 'BT', true);
		}

		$user->userInfo = $data;
		$this->sendRegistrationEmail($user, $user->password_clear, $doUserActivation);
		
		if($doUserActivation)
		{
			$message = vmText::_('COM_VIRTUEMART_REG_COMPLETE_ACTIVATE');
		}
		else
		{
			$user->set('activation', '');
			$user->set('block', 0);
			$user->set('guest', 0);
			// Auto login the user so that he can continue with checkout
			$options                  = array();
			$options['remember']      = false;
			$options['return']        = '';
			$options['silent']        = true;
			$credentials              = array();
			$credentials['username']  = $user->get('username');
			$credentials['password']  = $user->password_clear;
			if(version_compare(JVERSION, '3.0.0', 'ge'))
			{
				$credentials['secretkey'] = '';
			}
			// Perform the login action
			$return = $app->login($credentials, $options);
			if(false === $return)
			{
				$message = JText::_('PLG_VPONEPAGECHECKOUT_REGISTRATION_NEED_LOGIN');
			}
			else
			{
				if($user_id)
				{
					$data['agreed'] = !empty($data['agreed']) ? $data['agreed'] : 1;
					$data['address_type'] = 'BT';
					$userinfo_id = $this->storePartUserinfo($data, 'BT');
				}
				
				$message = JText::_('PLG_VPONEPAGECHECKOUT_REGISTRATION_COMPLETED');
			}
		}

		// Check if the user is still logged in the system.
		// Otherwise we will request a stop in activity.
		$currentUser = JFactory::getUser();
		$stop = ($currentUser->get('id') == $user->get('id')) ? 0 : 1;
		
		$result = array('error' => 0, 'msg' => $message, 'stop' => $stop);
		$this->jsonReturn($result);
	}

	/**
	* Method to send registration mail using standard VirtueMart layout
	* 
	* @param object  $user              JUser object
	* @param string  $password          Clear password of the newly registered user
	* @param boolean $doUserActivation  User email activation required or not
	* 
	* @return void
	*/
	private function sendRegistrationEmail($user, $password, $doUserActivation)
	{
		if(!class_exists('shopFunctionsF'))
		{
			require(VMPATH_SITE . '/helpers/shopfunctionsf.php');
		}
		
		$vars = array();
		$vars['user'] = $user;
		$vars['doVendor'] = true;
		
		// Disallow control chars in the email
		$vars['password'] = preg_replace('/[\x00-\x1F\x7F]/', '', $password);
		
		// If you need to send an activation link
		if ($doUserActivation)
		{
			$vars['activationLink'] = 'index.php?option=com_users&task=registration.activate&token=' . $user->get('activation');
		}
		
		shopFunctionsF::renderMail('user', $user->get('email'), $vars);
	}
	
	private function getLoginUsername($field_value)
	{
		$field_value = strval($field_value);
		$email_as_username = (int) $this->params->get('email_as_username', 2);
		
		if(!empty($field_value) && $email_as_username)
		{
			if($email_as_username == 1 || preg_match('/@/', $field_value))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
				            ->select('username')
				            ->from('#__users')
				            ->where('email = ' . $db->quote($field_value));
				$db->setQuery($query);
				$username = $db->loadResult();
				
				if($email_as_username == 1)
				{
					return $username;
				}
				
				// For $email_as_username as 2 i.e. both
				if(!empty($username))
				{
					return $username;
				}
			}
		}
		
		return $field_value;
	}

	/**
	* Method to get Joomla Users List
	* 
	* @param  string $search Search user string
	* 
	* @return array Users data Object List
	*/
	public function getUserList($search = '')
	{
		$search = !empty($search) ? $search : vRequest::getUword('usersearch', '');
		$search = strval($search);
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
		            ->select('id, name, username')
		            ->from('#__users')
		            ->order('name');

		if(version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$query->clear('limit');
		}
		
		if(!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search) . '%');
			$query->where('name LIKE ' . $search . ' OR username LIKE ' . $search);
		}

		$db->setQuery($query);
		$users = $db->loadObjectList();
		
		foreach($users as &$user)
		{
			$user->displayedName = $user->name . ' (' . $user->username . ')';
		}
		
		if(!empty($search) && empty($users))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JGLOBAL_SELECT_NO_RESULTS_MATCH'));
		}
		
		return $users;
	}

	/**
	* Method to get Cart Modules
	* 
	* @return array Object list of modules
	*/	
	public function getCartModules()
	{
		$module_position = $this->params->get('module_position', 'cart-promo');
		
		if(empty($module_position))
		{
			return array();
		}
		
		if(version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$user = JFactory::getUser();
			$app = JFactory::getApplication();
			$doc = JFactory::getDocument();
			$renderer = $doc->loadRenderer('module');
			$modules = array();
			$params = array();
			$content = null;
			
			$frontediting = $app->get('frontediting', 1);
			$canEdit = $user->id && $frontediting && !($app->isAdmin() && $frontediting < 2) && $user->authorise('core.edit', 'com_modules');
			$menusEditing = ($frontediting == 2) && $user->authorise('core.edit', 'com_menus');

			foreach (JModuleHelper::getModules($module_position) as $mod)
			{
				$moduleHtml = $renderer->render($mod, $params, $content);
				$params = new JRegistry;
				$params->loadString($mod->params);
				$mod->params = $params;
				
				if ($app->isSite() && $canEdit && trim($moduleHtml) != '' && $user->authorise('core.edit', 'com_modules.module.' . $mod->id))
				{
					$displayData = array('moduleHtml' => &$moduleHtml, 'module' => $mod, 'position' => $module_position, 'menusediting' => $menusEditing);
					JLayoutHelper::render('joomla.edit.frontediting_modules', $displayData);
				}

				$mod->moduleHtml = $moduleHtml;
				$modules[] = $mod;
			}
		}
		else
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true)
			            ->select('a.*')
			            ->from('#__modules AS a')
			            ->where('a.published = 1')
			            ->where('a.position = ' . $db->quote($module_position))
			            ->order('a.ordering');
			$db->setQuery($query);
			$modules = $db->loadObjectList();
			
			if(!empty($modules))
			{
				foreach($modules as &$module)
				{
					if(JModuleHelper::isEnabled($module->module))
					{
						$params = new JRegistry;
						$params->loadString($module->params);
						$module->params = $params;
						$module->moduleHtml = JModuleHelper::renderModule($module, array('style' => 'no'));
					}
				}
			}
		}
		
		return $modules;
	}

	/**
	 * Method to check if a minimum purchase value for the order if set
	 * 
	 * @return string An error message when a minimum value was set that was not eached, null otherwise
	 */
	private function checkPurchaseValue()
	{
		$this->cart->prepareVendor();
		if ($this->cart->vendor->vendor_min_pov > 0)
		{
			$this->cart->getCartPrices();
			if ($this->cart->cartPrices['salesPrice'] < $this->cart->vendor->vendor_min_pov)
			{
				$currency = CurrencyDisplay::getInstance();
				return vmText::sprintf('COM_VIRTUEMART_CART_MIN_PURCHASE', $currency->priceDisplay($this->cart->vendor->vendor_min_pov));
			}
		}
		return null;
	}

	/**
	* Method to get total product count in cart
	* 
	* @return integer Product Count
	*/
	private function getProductsCount()
	{
		$productsCount = 0;
		if(!empty($this->cart->cartProductsData)) 
		{
			foreach($this->cart->cartProductsData as $product) 
			{
				$productsCount = $productsCount + (isset($product['quantity']) ? intval($product['quantity']) : 0);
			}
		}
		return $productsCount;
	}
	
	/**
	* Method to render VP OPC Plugin layouts
	* 
	* @param string $layoutName Name of the layout file
	* 
	* @return string Rendered HTML
	*/
	protected function renderPlgLayout($layoutName)
	{
		$layoutName = trim(strval($layoutName));
		$path = JPath::clean(dirname(__FILE__) . '/tmpl/' . $layoutName . '.php');
		
		if($templatePath = $this->getTemplatePath())
		{
			$layoutPath = JPath::clean($templatePath . '/' . $layoutName . '.php');
			
			if(is_file($layoutPath) && file_exists($layoutPath))
			{
				$path = $layoutPath;
			}
		}
		
		if(!file_exists($path) || !is_file($path))
		{
			JFactory::getApplication()->enqueueMessage('Layout file ' . $path . ' not found.', 'error');
			return '';
		}
		
		ob_start();
		require_once($path);
		$layout = ob_get_contents();
		ob_end_clean();	

		return $layout;
	}
	
	/**
	* Method to find template layout override path if exists
	* 
	* @return mixed (boolean/string) If does not exists it returns false. If exists then it returns directory path.
	*/
	protected function getTemplatePath()
	{
		$app = JFactory::getApplication();
		$template = $app->getTemplate(true);
		$templatePath = JPath::clean(JPATH_ROOT . '/templates/' . $template->template . '/html/plg_system_vponepagecheckout');

		if(!is_dir($templatePath))
		{
			return false;
		}

		return $templatePath;
	}
	
	/**
	* Internal method replace a string once
	* 
	* @param undefined $search
	* @param undefined $replace
	* @param undefined $subject
	* 
	* @return
	*/
	private function str_lreplace($search, $replace, $subject)
	{
		$pos = strrpos($subject, $search);
		if($pos !== false)
		{
			$subject = substr_replace($subject, $replace, $pos, strlen($search));
		}
		return $subject;
	}
} 