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
$Revision: 51 $
$LastChangedDate: 2015-06-25 18:37:33 +0530 (Thu, 25 Jun 2015) $
$Id: helper.php 51 2015-06-25 13:07:33Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die;

if (!class_exists('VmConfig')) 
{
	$configFile = JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php';
	if(file_exists($configFile)) require($configFile);
}

/**
* VP One Page Checkout plugin helper class
* For VirtueMart 3
* 
* @since 3.0.0
*/
class VPOPCHelper 
{
	protected $input;
	protected $option;
	protected $view;
	protected $task;
	protected $layout;
	protected $tmpl;
	protected $lang;
	protected $checkoutTask;
	protected $type;
	protected $params;
	protected $data;
	
	protected static $instances = array();
	protected static $_originalQueue = array();
	protected static $_messageQueue = array();
	protected static $_renderedHTML = null;
	protected static $_klarnaEnabled = null;
	protected static $_scriptTexts = array();
	protected static $_scriptOptions = array();
	
	protected static $_loaded = false;
	
	/**
	* Construction method of the helper class
	* 
	* @param mixed (object/null) $params Plugin params JRegistry object
	* 
	* @return void
	*/
	public function __construct($params = null)
	{
		if(!class_exists('VmConfig'))
		{
			// VirtueMart is not installed in this site.
			return false;
		}
		
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		if(version_compare(JVERSION, '3.0.0', 'ge'))
		{
			jimport('cms.html.html');
			
			$this->input         = $app->input;
			$this->option        = strtolower($this->input->getCmd('option', ''));
			$this->view          = strtolower($this->input->getCmd('view', ''));
			$this->task          = strtolower($this->input->getCmd('task', ''));
			$this->task          = $this->input->post->getCmd('task', '') ?
			                       strtolower($this->input->post->getCmd('task', '')) :
			                       $this->task;
			$this->layout        = strtolower($this->input->getCmd('layout', ''));
			$this->tmpl          = strtolower($this->input->getCmd('tmpl', ''));
			$this->lang          = strtolower($this->input->get('lang', '', 'STRING'));
			$this->checkoutTask  = strtolower($this->input->get('ctask', '', 'STRING'));
		}
		else
		{
			$this->option        = strtolower(JRequest::getCmd('option', ''));
			$this->view          = strtolower(JRequest::getCmd('view', ''));
			$this->task          = strtolower(JRequest::getCmd('task', ''));
			$this->layout        = strtolower(JRequest::getCmd('layout', ''));
			$this->tmpl          = strtolower(JRequest::getCmd('tmpl', ''));
			$this->lang          = strtolower(JRequest::getVar('lang', '', 'STRING'));
			$this->checkoutTask  = strtolower(JRequest::getVar('ctask', '', 'STRING'));
		}

		$this->type    = strtolower($doc->getType());
		$this->params  = $params;
	}
	
	/**
	* Method to get an instance of the the VPOPCHelper class
	* 
	* @param mixed (object/null) $params Plugin params JRegistry object
	* 
	* @return object VPOPCHelper class object
	*/
	public static function getInstance($params = null)
	{
		$hash = !empty($params) ? md5(serialize($params)) : 0;
		
		if(!isset(self::$instances[$hash]))
		{
			self::$instances[$hash] = new VPOPCHelper($params);
		}
		
		return self::$instances[$hash];
	}
	
	/**
	* Method to check if we are in the VirtueMart cart page 
	* 
	* @return boolean Returns false if not cart page
	*/
	public function isCart()
	{
		$isCart = ($this->option == 'com_virtuemart' && $this->view == 'cart' && $this->type == 'html') ||
		          ($this->option == 'com_virtuemart' && $this->view == 'vmplg' && $this->task == 'pluginuserpaymentcancel' && $this->type == 'html') ||
		          ($this->option == 'com_virtuemart' && $this->view == 'pluginresponse' && $this->task == 'pluginuserpaymentcancel' && $this->type == 'html');

		if($isCart)
		{
			VmConfig::loadConfig();
			
			if (!class_exists('VirtueMartCart'))
			{
				require(JPATH_SITE . '/components/com_virtuemart/helpers/cart.php');
			}
			
			$cart = VirtueMartCart::getCart();
			$isCart = $isCart && ($this->layout != 'cart' && $cart->layout != 'cart');
			
			if($isCart && !self::$_loaded && class_exists('VmConfig'))
			{
				// Load required VirtueMart languages
				VmConfig::loadJLang('com_virtuemart', true);
				VmConfig::loadJLang('com_virtuemart_shoppers', true);
				
				// Load other required languages
				$language = JFactory::getLanguage();
				$language->load('lib_joomla');
				$language->load('com_users');
				$language->load('plg_system_vponepagecheckout', JPATH_ADMINISTRATOR);
				$language->load('plg_system_vponepagecheckout_override', JPATH_SITE);
				
				self::$_loaded = true;
			}
		}
		
		return $isCart;
	}
	
	/**
	* Method to check if the the plugin is compatible to installed VirtueMart version
	* 
	* @return boolean Returns false if not compatible
	*/
	public function isCompatible()
	{
		$app = JFactory::getApplication();
		
		if(!class_exists('VmConfig'))
		{
			$file = JPath::clean(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
			if(file_exists($file)) require($file);
		}
		
		if(!defined('VM_VERSION'))
		{
			$app->enqueueMessage('It appears VirtueMart Component is not installed. VP One Page Checkout plugin is an extension of VirtueMart Component.', 'error');
			return;
		}
		
		if(VM_VERSION < 3)
		{
			$app->enqueueMessage('This package of VP One Page Checkout plugin is compatible to VirtueMart 3 and above. You can get VirtueMart ' . VM_REV . 
			                     ' compatible package of the plugin from http://www.virtueplanet.com', 'error');
			return false;
		}
		
		return true;
	}
	
	/**
	* Method to load static css and js files
	* 
	* @return void
	*/
	public function loadAssets()
	{
		// Ensure that none of our assets are loaded on order done page.
		if($this->task == 'confirm' || $this->layout == 'order_done')
		{
			return;
		}
		
		if(version_compare(JVERSION, '3.0.0', 'ge'))
		{
			JHtml::_('jquery.framework');
		}

		vmJsApi::jPrice();
		
		$app           = JFactory::getApplication();
		$doc           = JFactory::getDocument();
		$root          = JUri::root(true);
		$version       = $this->getVersion();
		$template      = $app->getTemplate(true);
		$needLoading   = ($this->params->get('load_jquery', 2) == 2 && strpos($template->template, 'vp_') === false) ||
		                 ($this->params->get('load_jquery', 2) == 1);
		$loadedScripts = $doc->_scripts;
		$doc->_scripts = array();
		$jQuery        = array('/jquery.min.js', '/jquery.js');
		$loaded        = array();
		$unsetjQuery   = false;

		if($needLoading && (JVM_VERSION == 2) && !VmConfig::get('jquery', 1))
		{
			$doc->addScript($root . '/plugins/system/vponepagecheckout/assets/js/jquery.min.js');
			$doc->addScript($root . '/plugins/system/vponepagecheckout/assets/js/jquery-noconflict.js');
			$doc->addScript($root . '/plugins/system/vponepagecheckout/assets/js/jquery-migrate.min.js');
			$unsetjQuery = true;
		}
		
		foreach($loadedScripts as $script => &$attribs)
		{
			$suffix = '';
			if(strpos($script, '?') !== false)
			{
				$segments = explode('?', $script);
				$script = $segments[0];
				$suffix = '?' . $segments[1];
			}
			
			if($unsetjQuery && $this->strposa($script, $jQuery) !== false)
			{
				$foundNeedle = $this->strposa($script, $jQuery, 0, true);
				
				if($this->strposa($script, $jQuery) == (strlen($script) - strlen($foundNeedle)))
				{
					continue;
				}
			}
			
			if(strpos($script, 'jquery.easing.min.js') !== false || strpos($script, 'jquery.easing.js') !== false || strpos($script, 'jquery.easing.1.3.min.js') !== false)
			{
				$loaded['easing'] = true;
			}
			elseif(strpos($script, 'components/com_virtuemart/assets/js/jquery-ui.min.js') !== false)
			{
				$loaded['easing'] = true;
				$loaded['jquery-ui'] = true;
			}
			elseif(strpos($script, 'fancybox/jquery.fancybox-1.3.4.pack.js') !== false)
			{
				$loaded['fancybox'] = true;
			}
			elseif(strpos($script, 'vmsite.js') !== false)
			{
				$loaded['vmsite'] = true;
			}
			elseif(strpos($script, 'vmprices.js') !== false)
			{
				$loaded['vmprices'] = true;
			}
			elseif(strpos($script, 'vmcreditcard.js') !== false)
			{
				$loaded['vmcreditcard'] = true;
			}
			elseif(strpos($script, 'jquery.hoverIntent.min.js') !== false || strpos($script, 'jquery.hoverIntent.minified.js') !== false || strpos($script, 'jquery.hoverIntent.js') !== false)
			{
				$loaded['hoverIntent'] = true;
			}
			
			$script = $script . $suffix;
			// Re-enqueue the script
			$doc->addScript($script, $attribs['mime'], $attribs['defer'], $attribs['async']);
		}
		
		if($this->hasKlarnaEnabled())
		{
			$klarnaAssetsPath = $root . '/plugins/vmpayment/klarna/klarna/assets';
			$doc->addStyleSheet($klarnaAssetsPath . '/css/style.css');
			$doc->addStyleSheet($klarnaAssetsPath . '/css/klarna.css');
			$doc->addScript(JURI::root() . 'plugins/vmpayment/klarna/klarna/assets/js/klarna_pp.js');
			$doc->addScript('https://static.klarna.com:444/external/js/klarnapart.js');
			$doc->addScript($klarnaAssetsPath . '/js/klarna_general.js');
			$doc->addScript('https://static.klarna.com/external/js/klarnaConsentNew.js');
		}
		
		if($this->params->get('load_jquery_plugins', 2) == 2 && strpos($template->template, 'vp_') === false) 
		{
			if(!isset($loaded['hoverIntent']))
			{
				$doc->addScript($this->getStaticFiles('jquery.hoverIntent.min.js', 'js'));
			}
			if(!isset($loaded['easing']))
			{
				$doc->addScript($this->getStaticFiles('jquery.easing.1.3.min.js', 'js'));
			}
		}
		elseif($this->params->get('load_jquery_plugins', 2) == 1) 
		{
			$doc->addScript($this->getStaticFiles('jquery.hoverIntent.minified.js', 'js'));
			$doc->addScript($this->getStaticFiles('jquery.easing.1.3.min.js', 'js'));
		}
		
		if($this->params->get('tos_fancybox', 1))
		{
			if(!isset($loaded['fancybox']))
			{
				$vmFancyJS = '/components/com_virtuemart/assets/js/fancybox/jquery.fancybox-1.3.4.pack.js';
				$vmFancyJSPath = JPath::clean(JPATH_SITE . $vmFancyJS);
				$vmFancyCSS = '/components/com_virtuemart/assets/css/jquery.fancybox-1.3.4.css';
				$vmFancyCSSPath = JPath::clean(JPATH_SITE . $vmFancyCSS);

				if(file_exists($vmFancyJSPath) && file_exists($vmFancyCSSPath))
				{
					$doc->addScript($root . $vmFancyJS);
					$doc->addStyleSheet($root . $vmFancyCSS);
				}
				else
				{
					$doc->addScript($this->getStaticFiles('jquery.fancybox-1.3.4.pack.js', 'js'));
					$doc->addStyleSheet($this->getStaticFiles('jquery.fancybox-1.3.4.css', 'css'));
				}
			}
		}
		else
		{
			$doc->addScript($this->getStaticFiles('bootmodal.js', 'js'));
		}
		
		if(!isset($loaded['vmsite']))
		{
			$doc->addScript($root . '/components/com_virtuemart/assets/js/vmsite.js');
		}
		
		if(!isset($loaded['vmprices']))
		{
			$doc->addScript($root . '/components/com_virtuemart/assets/js/vmprices.js');
		}
		
		if(!isset($loaded['vmcreditcard']))
		{
			$doc->addScript($root . '/components/com_virtuemart/assets/js/vmcreditcard.js');
		}
		
		$doc->addScript($this->getStaticFiles('spin.min.js', 'js'));
		$doc->addScript($this->getStaticFiles('plugin-min.js', 'js', $version));
		
		if($this->params->get('color', 1) == 2) 
		{
			$SPINNER_COLOR = '#FFF';
			$doc->addStyleSheet($this->getStaticFiles('dark-checkout.css', 'css', $version));
		}
		else
		{
			$SPINNER_COLOR = '#000';
			$doc->addStyleSheet($this->getStaticFiles('light-checkout.css', 'css', $version));
		}
		
		if($this->params->get('responsive', 1)) 
		{
			$doc->addStyleSheet($this->getStaticFiles('responsive-procheckout.css', 'css', $version));
		}
		
		if(!class_exists ('VirtueMartModelUserfields')) require(VMPATH_ADMIN . '/models/userfields.php');
		
		// Load all body scripts
		$CheckoutURI            = $root . '/index.php?option=com_virtuemart&view=cart';
		if(!empty($this->lang))
		{
			$CheckoutURI .= '&lang=' . substr (VmConfig::$vmlang, 0, 2);
		}
		$ASSETPATH              = $root . '/plugins/system/vponepagecheckout/assets/';
		$userFieldsModel        = VmModel::getModel('userfields');
		$VMCONFIGTOS            = ($userFieldsModel->getIfRequired ('agreed') && VmConfig::get ('oncheckout_show_legal_info', 1)) || VmConfig::get('agree_to_tos_onorder') ? 1 : 0;
		$BTASST                 = (int) $this->params->get('check_shipto_address', 1);
		$GROUPING               = (int) $this->params->get('check_shipto_address', 1);
		
		if(!class_exists ('vmVersion')) require(VMPATH_ADMIN . '/version.php');
		if(version_compare(vmVersion::$RELEASE, '3.0.6', '>='))
		{
			$AUTOSHIPMENT         = (int) VmConfig::get('set_automatic_shipment');
			$AUTOPAYMENT          = (int) VmConfig::get('set_automatic_payment');
		}
		else
		{
			$AUTOSHIPMENT         = (int) VmConfig::get('automatic_shipment');
			$AUTOPAYMENT          = (int) VmConfig::get('automatic_payment');
		}

		$AJAXVALIDATION         = (int) $this->params->get('ajax_validation', 0);
		$RELOAD                 = (int) $this->params->get('reload', 0);
		$TOSFANCY               = (int) $this->params->get('tos_fancybox', 1);
		$EDITPAYMENTURI         = JRoute::_('index.php?view=cart&task=editpayment', false);
		$STYLERADIOCHEBOX       = (int) $this->params->get('style_radio_checkbox', 1);
		$REMOVEUNNECESSARYLINKS = (int) $this->params->get('remove_unnecessary_links', 1);
		$RELOADPAYMENTS         = (int) $this->params->get('reload_payment_on_shipment_selection', 0);
		$RELOADALLFORCOUPON     = (int) $this->params->get('reload_all_on_apply_coupon', 0);
		$DISABLELIVEVALIDATION  = $this->params->get('live_validation', 0) ? 0 : 1;
		$user_params            = JComponentHelper::getParams('com_users');
		$PASSWORD_LENGTH        = (int) $user_params->get('minimum_length', 4);
		$PASSWORD_INTEGERS      = (int) $user_params->get('minimum_integers', 0);
		$PASSWORD_SYMBOLS       = (int) $user_params->get('minimum_symbols', 0);
		$PASSWORD_UPPERCASE     = (int) $user_params->get('minimum_uppercase', 0);
		
		// Add options for script
		self::scriptOption('URI', $CheckoutURI);
		self::scriptOption('ASSETPATH', $ASSETPATH);
		self::scriptOption('RELOAD', $RELOAD);
		self::scriptOption('BTASST', $BTASST);
		self::scriptOption('GROUPING', $GROUPING);
		self::scriptOption('VMCONFIGTOS', $VMCONFIGTOS);
		self::scriptOption('SPINNER_COLOR', $SPINNER_COLOR);
		self::scriptOption('AUTOSHIPMENT', $AUTOSHIPMENT);
		self::scriptOption('AUTOPAYMENT', $AUTOPAYMENT);
		self::scriptOption('AJAXVALIDATION', $AJAXVALIDATION);
		self::scriptOption('EDITPAYMENTURI', $EDITPAYMENTURI);
		self::scriptOption('TOSFANCY', $TOSFANCY);
		self::scriptOption('STYLERADIOCHEBOX', $STYLERADIOCHEBOX);
		self::scriptOption('REMOVEUNNECESSARYLINKS', $REMOVEUNNECESSARYLINKS);
		self::scriptOption('RELOADPAYMENTS', $RELOADPAYMENTS);
		self::scriptOption('RELOADALLFORCOUPON', $RELOADALLFORCOUPON);
		self::scriptOption('DISABLELIVEVALIDATION', $DISABLELIVEVALIDATION);
		self::scriptOption('PASSWORD_LENGTH', $PASSWORD_LENGTH);
		self::scriptOption('PASSWORD_INTEGERS', $PASSWORD_INTEGERS);
		self::scriptOption('PASSWORD_SYMBOLS', $PASSWORD_SYMBOLS);
		self::scriptOption('PASSWORD_UPPERCASE', $PASSWORD_UPPERCASE);
		
		// Load JText languages for JavaScript
		self::scriptText('JLIB_LOGIN_AUTHENTICATE');
		self::scriptText('COM_VIRTUEMART_CART_PLEASE_ACCEPT_TOS');
		self::scriptText('COM_VIRTUEMART_USER_FORM_MISSING_REQUIRED_JS');
		self::scriptText('COM_VIRTUEMART_CART_NO_SHIPMENT_SELECTED');
		self::scriptText('COM_VIRTUEMART_CART_NO_PAYMENT_SELECTED'); 
		self::scriptText('PLG_VPONEPAGECHECKOUT_REQUIRED_FIELD'); 
		self::scriptText('PLG_VPONEPAGECHECKOUT_REQUIRED_FIELDS_MISSING'); 
		self::scriptText('PLG_VPONEPAGECHECKOUT_WEAK'); 
		self::scriptText('PLG_VPONEPAGECHECKOUT_TOO_SHORT');
		self::scriptText('PLG_VPONEPAGECHECKOUT_GOOD'); 
		self::scriptText('PLG_VPONEPAGECHECKOUT_STRONG'); 
		self::scriptText('PLG_VPONEPAGECHECKOUT_INVALID');
		self::scriptText('PLG_VPONEPAGECHECKOUT_VALIDATED');
		self::scriptText('PLG_VPONEPAGECHECKOUT_EMAIL_INVALID');
		self::scriptText('COM_USERS_PROFILE_EMAIL1_MESSAGE');
		self::scriptText('PLG_VPONEPAGECHECKOUT_USERNAME_INVALID');
		self::scriptText('COM_USERS_PROFILE_USERNAME_MESSAGE');
		self::scriptText('PLG_VPONEPAGECHECKOUT_REGISTRATION_COMPLETED');
		self::scriptText('PLG_VPONEPAGECHECKOUT_EMAIL_SAVED');
		self::scriptText('PLG_VPONEPAGECHECKOUT_LOGIN_COMPLETED');
		self::scriptText('PLG_VPONEPAGECHECKOUT_SAVING_BILLING_ADDRESS');
		self::scriptText('PLG_VPONEPAGECHECKOUT_BILLING_ADDRESS_SAVED');
		self::scriptText('PLG_VPONEPAGECHECKOUT_SAVING_SHIPPING_ADDRESS');
		self::scriptText('PLG_VPONEPAGECHECKOUT_SHIPPING_ADDRESS_SAVED');
		self::scriptText('PLG_VPONEPAGECHECKOUT_SAVING_CREDIT_CARD');
		self::scriptText('PLG_VPONEPAGECHECKOUT_CREDIT_CARD_SAVED');
		self::scriptText('PLG_VPONEPAGECHECKOUT_VERIFYING_ORDER');
		self::scriptText('PLG_VPONEPAGECHECKOUT_PLACING_ORDER');
		self::scriptText('PLG_VPONEPAGECHECKOUT_PLEASE_WAIT');
		self::scriptText('PLG_VPONEPAGECHECKOUT_COUPON_EMPTY');
		self::scriptText('VMPAYMENT_PAYPAL_REDIRECT_MESSAGE');
		self::scriptText('COM_VIRTUEMART_REG_COMPLETE');
		self::scriptText('PLG_VPONEPAGECHECKOUT_REGISTRATION_NEED_LOGIN');
		
		// For password validation in Joomla! 3
		self::scriptText('COM_USERS_MSG_NOT_ENOUGH_INTEGERS_N');
		self::scriptText('COM_USERS_MSG_NOT_ENOUGH_INTEGERS_N_1');
		self::scriptText('COM_USERS_MSG_NOT_ENOUGH_SYMBOLS_N');
		self::scriptText('COM_USERS_MSG_NOT_ENOUGH_SYMBOLS_N_1');
		self::scriptText('COM_USERS_MSG_NOT_ENOUGH_UPPERCASE_LETTERS_N');
		self::scriptText('COM_USERS_MSG_NOT_ENOUGH_UPPERCASE_LETTERS_N_1');
		self::scriptText('COM_USERS_MSG_PASSWORD_TOO_LONG');
		self::scriptText('COM_USERS_MSG_PASSWORD_TOO_SHORT_N');
		self::scriptText('COM_USERS_MSG_SPACES_IN_PASSWORD');
	}
	
	/**
	* Method to add a script option/params to VPOPC
	* 
	* @param string                                      $name Name of the option
	* @param mixed (string/integer/boolean/object/array) $value Value of the option
	* 
	* @return void
	*/
	public static function scriptOption($name, $value)
	{
		$name = strtoupper($name);
		self::$_scriptOptions[$name] = $value;
	}
	
	/**
	* Method to add a script language to VPOPC
	* 
	* @param string $langTag Language Tag
	* 
	* @return void
	*/
	public static function scriptText($langTag)
	{
		$langTag = strtoupper($langTag);
		self::$_scriptTexts[$langTag] = JText::_($langTag);
	}
	
	/**
	* Method to load inline body scripts for VPOPC
	* 
	* @return void
	*/
	public static function loadVPOPCScripts()
	{
		if(empty(self::$_scriptTexts) && empty(self::$_scriptOptions))
		{
			return;
		}
		
		$doc = JFactory::getDocument();
		$tab = $doc->_getTab();
		$lineEnd = $doc->_getLineEnd();
		$strings = !empty(self::$_scriptTexts) ? json_encode(self::$_scriptTexts) : null;
		$options = !empty(self::$_scriptOptions) ? json_encode(self::$_scriptOptions) : null;
		
		$script  = $lineEnd;
		$script .= $tab . "(function() {" . $lineEnd;
		if(!empty($strings))
		{
			$script .= $tab . $tab . "var strings = " . $strings . ";" . $lineEnd;
		}
		if(!empty($options))
		{
			$script .= $tab . $tab . "var options = " . $options . ";" . $lineEnd;
		}
		$script .= $tab . $tab . "if(typeof VPOPC === 'undefined' || typeof VPOPC === undefined) {" . $lineEnd;
		$script .= $tab . $tab . $tab . 'VPOPC = {};' . $lineEnd;
		if(!empty($strings))
		{
			$script .= $tab . $tab . $tab . 'VPOPC.JText = {};' . $lineEnd;
			$script .= $tab . $tab . $tab . 'VPOPC.JText.strings = strings;' . $lineEnd;
		}
		if(!empty($options))
		{
			$script .= $tab . $tab . $tab . 'VPOPC.option = options;' . $lineEnd;
		}
		$script .= $tab . $tab . "} else {" . $lineEnd;
		if(!empty($strings))
		{
			$script .= $tab . $tab . $tab . "VPOPC.JText.load(strings);" . $lineEnd;
		}
		if(!empty($options))
		{
			$script .= $tab . $tab . $tab . 'VPOPC.loadOptions(options);' . $lineEnd;
		}
		$script .= $tab . $tab . "}" . $lineEnd;
		$script .= $tab . "})();";
		
		$doc->addScriptDeclaration($script);
		// Reset the list after load.
		self::$_scriptTexts = array();
		self::$_scriptOptions = array();
	}
	
	/**
	* Method to get the static file url 
	* 
	* @param string               $fileName Name of the file
	* @param string               $type     Type of the asset i.e. css or js.
	* @param mixed (string/float) $ver
	* 
	* @return string Full file URL
	*/
	public function getStaticFiles($fileName, $type = 'css', $ver = null)
	{
		$app          = JFactory::getApplication();
		$template     = $app->getTemplate(true);
		$type         = trim(strtolower($type));
		$corePath     = '/plugins/system/vponepagecheckout/assets/' . $type . '/';
		$templatePath = '/templates/' . $template->template . '/' . $type . '/plg_system_vponepagecheckout/';

		if(is_file(JPath::clean(JPATH_SITE . $templatePath . $fileName)))
		{
			$return = JUri::root(true) . $templatePath . $fileName;
		}
		else
		{
			$return = JUri::root(true) . $corePath . $fileName;
		}

		if(!empty($ver))
		{
			$return .= '?ver=' . trim($ver);
		}

		return $return;
	}
	
	/**
	* Method to get the installed version of the plugin
	* 
	* @return string Plugin version
	*/
	public function getVersion()
	{
		$xmlPath = JPath::clean(JPATH_SITE . '/plugins/system/vponepagecheckout/vponepagecheckout.xml');
		
		if(version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$xml = JFactory::getXML($xmlPath);
			$version = (string) $xml->version;
		}
		else
		{
			$parser = JFactory::getXMLParser('Simple');
			$parser->loadFile($xmlPath);
			$doc = $parser->document;
			$element = $doc->getElementByPath('version');
			$version = (string) $element->data();
		}
		
		return trim($version);
	}
	
	/**
	* Method to check if Klarna payment plugin is enabled
	* 
	* @return boolean Returns false if not enabled.
	*/
	public function hasKlarnaEnabled()
	{
		if(self::$_klarnaEnabled === null)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true)
			            ->select('COUNT(virtuemart_paymentmethod_id)')
			            ->from('#__virtuemart_paymentmethods')
			            ->where('published = 1')
			            ->where('payment_element = ' . $db->quote('klarna'));
			$db->setQuery($query);
			$count = $db->loadResult();
			
			self::$_klarnaEnabled = (!empty($count) && $count > 0);
		}
		return self::$_klarnaEnabled;
	}
	
	/**
	* Method to set SSL and non-SSL url redirections
	* 
	* @param string $stage Joomla sysytem plugin event when the funtion is being called.
	* 
	* @return void
	*/
	public function setSSLRules($stage = 'onAfterRoute')
	{
		VmConfig::loadConfig();
		$app           = JFactory::getApplication();
		$uri           = JFactory::getURI();
		$SSLEnabled    = VmConfig::get('useSSL', 0);
		$post          = JRequest::get('POST');
		$canRedirect   = empty($post) || ($stage == 'onAfterDispatch');
		
		if(!$SSLEnabled || !$canRedirect || $this->type != 'html')
		{
			return;
		}
		
		if($this->isCart() && !$uri->isSSL())
		{
			$uri->setScheme('https');
			$app->redirect($uri->toString());
			return $app->close();
		}
		elseif(!$this->isCart() && $uri->isSSL() && $this->params->get('disable_ssl', 1))
		{
			$uri->setScheme('http');
			$app->redirect($uri->toString());
			return $app->close();
		}
	}
	
	/**
	* Method to save the last visited internal page url outside cart to userstate.
	* 
	* @return void
	*/
	public function saveLastVisitedPage()
	{
		if(!$this->isCart() && $this->type == 'html')
		{
			$app = JFactory::getApplication();
			$uri = JFactory::getURI();
			$url = $uri->toString();
			$url = (!JUri::isInternal($url)) ? '' : $url;
			$app->setUserState('proopc.lastvisited.url', $url);
		}
	}
	
	/**
	* Method to hide Joomla! system messages
	* 
	* @return void
	*/
	public function hideSystemMessages()
	{
		$app = JFactory::getApplication();
		$messages = !empty(self::$_originalQueue) ? self::$_originalQueue : $app->getMessageQueue();
		
		if(empty($messages) || $this->type != 'html')
		{
			return;
		}
		
		// What we need to hide
		$exactMatch = array();
		$prefixMatch = array();
		$suffixMatch = array();
		$anyMatch = array();
		$prefixMatch[] = trim(vmText::sprintf('COM_VIRTUEMART_MISSING_VALUE_FOR_FIELD', ''));
		$prefixMatch[] = 'Missing value for';
		$prefixMatch[] = vmText::_('VMPAYMENT_AUTHORIZENET_CARD_NUMBER_INVALID');
		$exactMatch[] = vmText::_('COM_VIRTUEMART_USER_FORM_MISSING_REQUIRED_JS');
		$exactMatch[] = vmText::_('COM_VIRTUEMART_CHECKOUT_PLEASE_ENTER_ADDRESS');
		$exactMatch[] = vmText::_('COM_VIRTUEMART_CART_PLEASE_ACCEPT_TOS');
		$exactMatch[] = vmText::_('COM_VIRTUEMART_MISSING_TOS');
		$exactMatch[] = vmText::_('VMPAYMENT_AUTHORIZENET_CARD_NUMBER_INVALID');
		$exactMatch[] = vmText::_('VMPAYMENT_AUTHORIZENET_CARD_CVV_INVALID');
		$exactMatch[] = vmText::_('VMPAYMENT_AUTHORIZENET_CARD_EXPIRATION_DATE_INVALID');
		$exactMatch[] = 'Please accept the terms of service to confirm';
		$exactMatch[] = 'Please accept the Terms of Service to proceed';
		
		// Check if we have any custom hiding requests set by the user
		$custom_requests = $this->params->get('hide_custom_msg', '');
		$custom_requests = trim($custom_requests);
		if(!empty($custom_requests))
		{
			if(strpos($custom_requests, ',') !== false)
			{
				$custom_requests = explode(',', $custom_msgs);
			}
			$custom_requests = (array) $custom_requests;
			$custom_requests = array_filter($custom_requests);

			if(!empty($custom_requests))
			{
				foreach($custom_requests as $custom_request)
				{
					$custom_request = trim(strval($custom_request));
					$starPosition = strpos($custom_request, '*');
					if($starPosition !== false)
					{
						$length = strlen($custom_request);
						if(substr_count($custom_request, '*') === 2 && strpos($custom_request, '*') === 0 && (strpos($custom_request, '*', 1) === ($length - 1)))
						{
							$anyMatch[] = trim(str_replace('*', '', $custom_request));
						}
						elseif(strpos($custom_request, '*') === 0)
						{
							$suffixMatch[] = trim(str_replace('*', '', $custom_request));
						}
						elseif(strpos($custom_request, '*', 1) === ($length - 1))
						{
							$prefixMatch[] = trim(str_replace('*', '', $custom_request));
						}
						else
						{
							$exactMatch[] = $custom_request;
							$exactMatch[] = vmText::_(strtoupper(str_replace(' ', '', $custom_request)));
						}
					}
					else
					{
						$exactMatch[] = $custom_request;
						$exactMatch[] = vmText::_(strtoupper(str_replace(' ', '', $custom_request)));
					}
				}
			}
		}

		foreach($messages as $key => $message)
		{
			$msg = isset($message['message']) ? $message['message'] : '';
			$length = strlen($msg);
			
			if(!$length || in_array($msg, $exactMatch) || ($this->strposa($msg, $prefixMatch, 0) === 0) || ($this->strposa($msg, $anyMatch) !== false))
			{
				continue;
			}
			elseif($this->strposa($msg, $suffixMatch, 1) !== false)
			{
				$foundNeedle = $this->strposa($msg, $suffixMatch, 0, true);
				$foundNeedleLength = strlen($foundNeedle);
				if($this->strposa($msg, $suffixMatch) == ($length - $foundNeedleLength))
				{
					continue;
				}
			}
			
			// Enqueue other messages in the helper instance
			$this->enqueueMessage($message['message'], $message['type']);
		}
		
		$originalHTML = $this->getRenderedMessages();
		$newHTML = $this->renderMessages();
		
		if(is_string($originalHTML) && !empty($originalHTML) && is_string($newHTML))
		{
			$body = JResponse::getBody();
			$body = str_replace($originalHTML, $newHTML, $body);
			JResponse::setBody($body);
		}
	}
	
	/**
	* Method to enqueue a system message to our internal queue
	* 
	* @param string $msg  Message
	* @param string $type Type of the message
	* 
	* @return void
	*/
	public function enqueueMessage($msg, $type = 'message')
	{
		// Don't add empty messages.
		if (!strlen($msg))
		{
			return;
		}
		// Enqueue the message.
		self::$_messageQueue[] = array('message' => $msg, 'type' => strtolower($type));
	}
	
	/**
	* strpos() where needles is array
	* 
	* @param string  $haystack  Haystack to check
	* @param array   $needles   Array of needles
	* @param integer $offset    Offset in the haystack
	* @param boolean $getNeedle Default should be 'false'. If the a call finds a match then you can retreve the matched 
	*                                                      needle by calling it again setting this value 'true'.
	* 
	* @return mixed (boolean/integer/string) False if not found or integer value of found position or found needle (string)
	*/
	public function strposa($haystack, $needles, $offset=0, $getNeedle = false)
	{
		static $foundNeedles = array();
		$key = (string) $haystack . serialize($needles) . $offset;
		$foundNeedles[$key] = null;
		
		if($getNeedle)
		{
			$foundNeedle = isset($foundNeedles[$key]) ? $foundNeedles[$key] : '';
			return $foundNeedle;
		}
		
		if(isset($foundNeedles[$key]))
		{
			return $foundNeedles[$key];
		}
		
		foreach($needles as $needle)
		{
			$pos = strpos($haystack, $needle, $offset);
			if($pos !== false)
			{
				$foundNeedles[$key] = $needle;
				return $pos;
			}
		}
		return false;
	}
	
	/**
	* Method to get the originally rendered system message HTML
	* 
	* @param boolean $return True of you need return or false so it just gets saved in the instance
	* 
	* @return mixed (string/void)
	*/
	public function getRenderedMessages($return = true)
	{
		if(self::$_renderedHTML === null)
		{
			$doc = JFactory::getDocument();
			$renderer = $doc->loadRenderer('message');
			self::$_renderedHTML = $renderer->render(false);
		}
		
		if($return)
		{
			return self::$_renderedHTML;
		}
	}
	
	/**
	* Method to save original system message queue in own object
	* 
	* @return void
	*/
	public function saveOriginalMessages()
	{
		$app = JFactory::getApplication();
		self::$_originalQueue = $app->getMessageQueue();
	}
	
	public function renderMessages()
	{
		$msgList = $this->getMessages();
		
		$displayData = array(
			'msgList' => $msgList,
			'name' => null,
			'params' => array(),
			'content' => null
		);

		$app = JFactory::getApplication();
		$chromePath = JPATH_THEMES . '/' . $app->getTemplate() . '/html/message.php';

		if (file_exists($chromePath))
		{
			include_once $chromePath;
		}

		if (function_exists('renderMessage'))
		{
			if(version_compare(JVERSION, '3.0.0', 'ge'))
			{
				JLog::add('renderMessage() is deprecated. Override system message rendering with layouts instead.', JLog::WARNING, 'deprecated');
			}
			
			return renderMessage($msgList);
		}
		
		if(version_compare(JVERSION, '3.0.0', 'ge'))
		{
			return JLayoutHelper::render('joomla.system.message', $displayData);
		}
		else
		{
			$buffer .= "\n<div id=\"system-message-container\">";

			// If messages exist render them
			if (is_array($msgList))
			{
				$buffer .= "\n<dl id=\"system-message\">";
				foreach ($msgList as $type => $msgs)
				{
					if (count($msgs))
					{
						$buffer .= "\n<dt class=\"" . strtolower($type) . "\">" . JText::_($type) . "</dt>";
						$buffer .= "\n<dd class=\"" . strtolower($type) . " message\">";
						$buffer .= "\n\t<ul>";
						foreach ($msgs as $msg)
						{
							$buffer .= "\n\t\t<li>" . $msg . "</li>";
						}
						$buffer .= "\n\t</ul>";
						$buffer .= "\n</dd>";
					}
				}
				$buffer .= "\n</dl>";
			}

			$buffer .= "\n</div>";
			
			return $buffer;
		}
	}
	
	/**
	* Method to get the messages from internal queue for display
	* 
	* @return array List of messages
	*/
	private function getMessages()
	{
		// Initialise variables.
		$lists = array();
		// Get the message queue
		$messages = self::$_messageQueue;
		// Build the sorted message list
		if (is_array($messages) && !empty($messages))
		{
			foreach ($messages as $msg)
			{
				if (isset($msg['type']) && isset($msg['message']))
				{
					$lists[$msg['type']][] = $msg['message'];
				}
			}
		}
		return $lists;
	}
	
	/**
	* Handles VP One Page Checkout action onAfterRoute
	* Directly return JSON object closing application
	* 
	* @return mixed (void/boolean) If true then onAfterRoute action must return without any further processing.
	*/
	public function handleAfterRouteActions()
	{
		if(!$this->isCart())
		{
			return;
		}
		
		if(($this->checkoutTask == 'checkemail' || $this->checkoutTask == 'checkuser') && $this->params->get('ajax_validation', 0))
		{
			if(version_compare(JVERSION, '3.0.0', 'ge'))
			{
				$email     = $this->input->get('email', '', 'STRING');
				$username  = $this->input->get('username', '', 'STRING');
			}
			else
			{
				$email     = JRequest::getVar('email', '', 'STRING');
				$username  = JRequest::getVar('username', '', 'STRING');
			}
			
			$users     = $this->getUsers();
			
			if(empty($users))
			{
				$result = ($this->checkoutTask == 'checkemail') ? array('valid' => 1, 'email' => $email) : array('valid' => 1, 'username' => $username);
			}
			elseif($this->checkoutTask == 'checkemail')
			{
				$valid  = !empty($email) && !in_array($email, $users['email']);
				$result = array('valid' => intval($valid), 'email' => $email);
			}
			else
			{
				$valid  = !empty($username) && !in_array($username, $users['username']);
				$result = array('valid' => intval($valid), 'username' => $username);
			}
			
			$this->jsonReturn($result);
		}
		elseif($this->checkoutTask == 'cancheckout')
		{
			require dirname(__FILE__) . '/includes.php';
			
			$app = JFactory::getApplication();
			$cart = VirtueMartCart::getCart();
			if(empty($cart->cartProductsData) || VmConfig::get('use_as_catalog'))
			{
				$app->setUserState('proopc.cancheckout', false);
				$result = array('error' => 1, 'reload' => 1);
			}
			else
			{
				$app->setUserState('proopc.cancheckout', true);
				$result = array('error' => 0, 'reload' => 0);
			}
			$this->jsonReturn($result);
		}
		elseif($this->checkoutTask == 'goback')
		{
			$app = JFactory::getApplication();
			$app->setUserState('proopc.checkout.finalstage', false);
			$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart', false));
			return true;
		}
	}
	
	/**
	* Method to get plugin version directly as JSON object by an Ajax call
	* 
	* @return void
	*/
	public function getOPCPluginVersion()
	{
		JSession::checkToken('GET') or $this->jsonReturn(array('error' => 1, 'msg' => JText::_('JINVALID_TOKEN')));
		
		$version = $this->getVersion();
		$this->jsonReturn(array('error' => 0, 'msg' => '', 'version' => $version));
	}
	
	/**
	* Method to get the list of already registered usernames and emails
	* 
	* @return array Array list of users email and username
	*/
	private function getUsers()
	{
		$app               = JFactory::getApplication();
		$cache             = JFactory::getCache('plg_vponepagecheckout', 'output');
		$session           = JFactory::getSession();
		$session_lifetime  = $app->get('lifetime', 15);
		$usersCount        = $this->getUsersCount();
		$key               = 'registered_users.usersCount:' . $usersCount; 
		$cache->setCaching(1);
		// We will save the cache as per session lifetime.
		$cache->setLifeTime($session_lifetime);
		$users             = $cache->get($key);
		
		if(empty($users))
		{
			$db     = JFactory::getDbo();
			$query  = $db->getQuery(true)
			             ->select($db->quoteName('username'))
			             ->select($db->quoteName('email'))
			             ->from($db->quoteName('#__users'));

			$db->setQuery($query);
			$users = $db->loadObjectList();

			if(!empty($users))
			{
				$emails = array();
				$username = array();
				
				foreach($users as $user) 
				{
					if(!empty($user->email)) 
					{
						$email[] = $user->email;
					}
					if(!empty($user->username)) 
					{
						$username[] = $user->username;
					}
				}
				
				$users = array();
				$users['email'] = $email;
				$users['username'] = $username;
				$cache->store($users, $key);
			}
		}
		
		return $users;
	}
	
	/**
	* Method to get the total user count
	* 
	* @return integer Count of registered users
	*/
	private function getUsersCount()
	{
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true)
		             ->select('COUNT(*)')
		             ->from($db->quoteName('#__users'));

		if(version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$query->clear('order')->clear('limit');
		}

		$db->setQuery($query);
		return $db->loadResult();
	}
	
	/**
	* Method to print JSON object data with proper JSON header.
	* This method return direct values during ajax calls.
	* 
	* @param array $message Array of the messages to be printed/returned
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

		echo json_encode((array) $message);
		flush();
		$app->close();
	}
	
	/**
	* Method to clean/minify a CSS style string
	* 
	* @param  string $buffer CSS style string
	* 
	* @return string Cleaned CSS style string
	*/
	public function cleanCSS($buffer)
	{
		// Remove comments
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
		// Remove tabs, spaces, new lines, etc.
		$buffer = str_replace(array("\r\n","\r","\n","\t",'  ','    ','    '),'',$buffer);
		// Remove unnecessary spaces
		$buffer = str_replace('{ ', '{', $buffer);
		$buffer = str_replace(' }', '}', $buffer);
		$buffer = str_replace('; ', ';', $buffer);
		$buffer = str_replace(', ', ',', $buffer);
		$buffer = str_replace(' {', '{', $buffer);
		$buffer = str_replace('} ', '}', $buffer);
		$buffer = str_replace(': ', ':', $buffer);
		$buffer = str_replace(' ,', ',', $buffer);
		$buffer = str_replace(' ;', ';', $buffer);
		$buffer = str_replace(';}', '}', $buffer);
		return $buffer;
	}
	
}