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
$Revision: 39 $
$LastChangedDate: 2015-03-20 05:18:25 +0530 (Fri, 20 Mar 2015) $
$Id: default_confirm.php 39 2015-03-19 23:48:25Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined ('_JEXEC') or die('Restricted access');
?>
<div class="inner-wrap">
	<form method="post" id="checkoutForm" name="checkoutForm" action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart', $this->useXHTML, $this->useSSL); ?>">
		<?php echo $this->loadTemplate ('cartfields'); ?>
		<?php if(!VmConfig::get('use_as_catalog')) : ?>
			<div class="proopc-row proopc-checkout-box">
				<button type="button" class="proopc-btn <?php echo ($this->params->get('color', 1) == 2) ? ' proopc-btn-danger' : ' proopc-btn-info'; ?>" id="proopc-order-submit" onclick="return ProOPC.submitOrder();"><?php echo JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU') ?></button>
			</div>
		<?php endif; ?>
	</form>
</div>
<?php 
// We have intentionally kept important hidden input fields outside the checkout form.
// They will be moved within the form by JavaScript when the cart is verified.
?>
<div id="proopc-hidden-confirm">
	<input type="hidden" name="STsameAsBT" value="<?php echo $this->cart->STsameAsBT ?>"/>
	<input type="hidden" name="shipto" value="<?php echo $this->cart->selected_shipto ?>"/>
	<input type="hidden" name="order_language" value="<?php echo $this->order_language; ?>"/>
	<input type="hidden" name="task" value="confirm"/>
	<input type="hidden" name="option" value="com_virtuemart"/>
	<input type="hidden" name="view" value="cart"/>
</div>