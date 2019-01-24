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
$Revision: 52 $
$LastChangedDate: 2015-06-26 17:29:39 +0530 (Fri, 26 Jun 2015) $
$Id: vponepagecheckout.php 52 2015-06-26 11:59:39Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VPOPCHelper'))
{
	// We need VP One Page Checkout helper
	require dirname(__FILE__) . '/cart/helper.php';
}

/**
* VP One Page Checkout system plugin class
* For VirtueMart 3. Comaptible to Joomla! 2.5 and Joomla! 3
* 
* @since 3.1
*/
class plgSystemVPOnePageCheckout extends JPlugin
{
	/**
	* Joomla! Plugin Standard Constructor
	* @param undefined $subject
	* @param undefined $params
	* 
	* @return void
	*/
	function __construct(&$subject, $params)
	{
		parent::__construct($subject, $params);
	}
	
	/**
	* After dispatch events
	* 
	* @return void
	*/
	public function onAfterDispatch()
	{
		$app = JFactory::getApplication();

		// If admin do nothing
		if($app->isAdmin()) 
		{
			return;
		}
		
		// Create a helper instance
		$helper = VPOPCHelper::getInstance($this->params);
		
		// Handle SSL redirections
		$helper->setSSLRules('onAfterDispatch');
	}
	
	/**
	* After route events
	* 
	* @return void
	*/
	public function onAfterRoute()
	{
		$app = JFactory::getApplication();

		// If admin do nothing
		if($app->isAdmin()) 
		{
			if($app->input->getCmd('ctask', '') == 'getplgversion')
			{
				// Create a helper instance
				$helper = VPOPCHelper::getInstance($this->params);
				
				// Return installed plugin version on admin request
				$helper->getOPCPluginVersion();
			}
			
			return;
		}
		
		// Create a helper instance
		$helper = VPOPCHelper::getInstance($this->params);
		
		// If it is VirtueMart Cart Page
		if($helper->isCart())
		{
			// If not compatible then we can not proceed
			if(!$helper->isCompatible())
			{
				return false;
			}
			
			if(!class_exists('VirtueMartViewCart'))
			{
				$viewPath = JPath::clean(dirname(__FILE__) . '/cart/cartview.html.php');
				require $viewPath;
			}
			else
			{
				$msg  = 'VP One Page Checkout plugin could not be loaded. ';
				$msg .= 'You are already using another third party VirtueMart Checkout system ';
				$msg .= 'in your site which does not allow the plugin to get loaded. ';
				$msg .= 'Please disable the same and try again.';
				$app->enqueueMessage($msg);
			}
			
			// Handle all after route actions
			$result = $helper->handleAfterRouteActions();
			if($result === true)
			{
				return;
			}
		}
		
		// Handle SSL redirections
		$helper->setSSLRules('onAfterRoute');
	}
	
	/**
	* Before head compile events
	* 
	* @return void
	*/
	public function onBeforeCompileHead()
	{
		$app = JFactory::getApplication();
		
		// If admin do nothing
		if($app->isAdmin())
		{
			return;
		}
		
		// Create a helper instance
		$helper = VPOPCHelper::getInstance($this->params);
		
		// If it is VirtueMart Cart Page
		if($helper->isCart())
		{
			// If not compatible then we can not proceed
			if(!$helper->isCompatible())
			{
				return false;
			}
			
			$helper->loadAssets();
			VPOPCHelper::loadVPOPCScripts();
		}
	}
	
	/**
	* Before render events
	* 
	* @return void
	*/	
	public function onBeforeRender()
	{
		$app = JFactory::getApplication();
		
		// If admin do nothing
		if($app->isAdmin())
		{
			return;
		}
		
		// Create a helper instance
		$helper = VPOPCHelper::getInstance($this->params);
		
		// Get the original system messages in the cart page.
		// Only if hide system message option enabled.
		if($helper->isCart() && $this->params->get('hide_system_msg', 1))
		{
			// If not compatible then we can not proceed
			if(!$helper->isCompatible())
			{
				return false;
			}
			
			// This will save the original messages and rendered html in helper instance
			$helper->getRenderedMessages(false);
			$helper->saveOriginalMessages();
		}
	}
	
	/**
	* After render events
	* 
	* @return void
	*/
	public function onAfterRender()
	{
		$app = JFactory::getApplication();
		
		// If admin do nothing
		if($app->isAdmin())
		{
			return;
		}
		
		// Create a helper instance
		$helper = VPOPCHelper::getInstance($this->params);
		
		// Hide system message it cart page.
		// Only if hide system message option enabled.
		if($helper->isCart() && $this->params->get('hide_system_msg', 1))
		{
			// If not compatible then we can not proceed
			if(!$helper->isCompatible())
			{
				return false;
			}
			
			// Hide necessary system messages
			$helper->hideSystemMessages();
		}
		
		// Save the last visited page url after render to avoid error pages
		$helper->saveLastVisitedPage();
	}
}