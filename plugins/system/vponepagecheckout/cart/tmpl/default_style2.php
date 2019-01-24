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
$Revision: 29 $
$LastChangedDate: 2015-01-26 13:55:08 +0530 (Mon, 26 Jan 2015) $
$Id: default_style2.php 29 2015-01-26 08:25:08Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined ('_JEXEC') or die('Restricted access');
$checkout_step = 1;
?>
<div id="proopc-system-message"></div>
<div class="proopc-finalpage">
	<div class="proopc-row">
		<h1 class="cart-page-title"><?php echo JText::_ ('COM_VIRTUEMART_CART_TITLE'); ?>&nbsp;<span class="septa">/</span>&nbsp;<span><?php echo JText::sprintf('COM_VIRTUEMART_CART_X_PRODUCTS', '<span id="proopc-cart-totalqty">' . $this->productsCount . '</span>'); ?></span></h1> 
	</div>
	<div class="proopc-row">
		<div class="proopc-login-message-cont">
			<?php if($this->juser->guest && !$this->params->get('only_guest', 0)) : ?>
				<a href="#goback" data-vpopc="redirect" data-vphref="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart&ctask=goback', $this->useXHTML, $this->useSSL) ?>" class="proopc-goback-link"><?php echo JText::_('JLOGIN') . '/' . JText::_('JREGISTER') ?></a>
			<?php elseif(!$this->juser->guest) : ?>
				<?php echo $this->loadTemplate('logout'); ?>
			<?php endif; ?>
		</div>
		<?php if(!empty($this->continue_link)) : ?>
			<div class="proopc-continue-link">
				<a href="<?php echo $this->continue_link ?>"><?php echo vmText::_('COM_VIRTUEMART_CONTINUE_SHOPPING') ?></a>
			</div>
		<?php endif; ?>
	</div>
	<form id="proopc-carttable-form">
		<div id="proopc-pricelist">
			<?php echo $this->loadTemplate('cartlist'); ?>
		</div>
		<input type="hidden" name="ctask" value="updateproduct"/>
	</form>
	<div class="proopc-column3">
		<div class="proopc-bt-address">
			<h3 class="proopc-process-title"><?php echo $this->params->get('oncheckout_show_steps', 1) ? '<div class="proopc-step">'.($checkout_step++).'</div>' : '' ?><?php echo JText::_('COM_VIRTUEMART_USER_FORM_BILLTO_LBL') ?></h3>
			<?php echo $this->loadTemplate ('btaddress'); ?>
		</div>
	</div>
	<div class="proopc-column3">
		<div class="proopc-st-address">
			<h3 class="proopc-process-title"><?php echo $this->params->get('oncheckout_show_steps', 1) ? '<div class="proopc-step">'.($checkout_step++).'</div>' : '' ?><?php echo JText::_('COM_VIRTUEMART_USER_FORM_SHIPTO_LBL') ?></h3>
			<?php echo $this->loadTemplate ('staddress'); ?>
		</div>	
		<div class="proopc-shipments">
			<h3 class="proopc-process-title"><?php echo $this->params->get('oncheckout_show_steps', 1) ? '<div class="proopc-step">'.($checkout_step++).'</div>' : '' ?><?php echo JText::_('COM_VIRTUEMART_CART_SHIPPING')?></h3>
			<div id="proopc-shipments">
				<?php echo $this->loadTemplate ('shipment'); ?>
			</div>
		</div>
		<div class="proopc-payments">
			<h3 class="proopc-process-title"><?php echo $this->params->get('oncheckout_show_steps', 1) ? '<div class="proopc-step">'.($checkout_step++).'</div>' : '' ?><?php echo JText::_('COM_VIRTUEMART_CART_PAYMENT')?></h3>
			<div id="proopc-payments">
				<?php echo $this->loadTemplate ('payment'); ?>
			</div>
		</div>		
	</div>	
	<div class="proopc-column3 last">
		<?php if (VmConfig::get('coupons_enable')) { ?>
		<div class="proopc-coupon no-top-margin">
			<h3 class="proopc-process-title"><?php echo $this->params->get('oncheckout_show_steps', 1) ? '<div class="proopc-step">'.($checkout_step++).'</div>' : '' ?><?php echo JText::_('COM_VIRTUEMART_COUPON_DISCOUNT')?></h3>
			<div id="proopc-coupon">
				<?php if(!empty($this->layoutName) && $this->layoutName=='default') {
				    echo $this->loadTemplate('coupon');
			    } ?>
			</div>
		</div>
		<?php 
		$confirm_class = '';
		} else { 
		$confirm_class = 'no-top-margin';
		}?>		
		<div class="proopc-confirm-order <?php echo $confirm_class ?>">
			<h3 class="proopc-process-title"><?php echo $this->params->get('oncheckout_show_steps', 1) ? '<div class="proopc-step">'.($checkout_step++).'</div>' : '' ?><?php echo JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU')?></h3>
			<div id="proopc-confirm-order">
				<?php echo $this->loadTemplate ('confirm'); ?>
			</div>
			<?php echo $this->loadTemplate ('advertisement'); ?>
		</div>
	</div>
</div>