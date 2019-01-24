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
$Revision: 7 $
$LastChangedDate: 2015-01-22 22:56:52 +0530 (Thu, 22 Jan 2015) $
$Id: default_shopperform.php 7 2015-01-22 17:26:52Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');
?>
<?php if($this->isAdminUser) : ?>
	<?php 
	$users = $this->getUserList();
	$app = JFactory::getApplication();
	$search = $app->getUserStateFromRequest('proopc.changeshoper.usersearch', 'usersearch', '', 'STRING');
	?>
	<div class="proopc-change-shopper-cont">
		<h3 class="proopc-change-shopper-title"><?php echo vmText::_ ('COM_VIRTUEMART_CART_CHANGE_SHOPPER'); ?></h3>
		<div class="proopc-change-shopper-inner">
			<?php if(!empty($this->vmAdminID) && $this->juser->id != $this->vmAdminID) : ?>
				<div class="proopc-active-shopper">
					<span><?php echo vmText::_('COM_VIRTUEMART_CART_ACTIVE_ADMIN') . ' ' . $this->adminUser->name; ?></span>
				</div>
			<?php endif; ?>
			<form action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart', false); ?>" method="post" id="form-usersearch" class="proopc-form-inline">
				<div class="proopc-field-group">
					<input type="text" name="usersearch" id="usersearch" value="<?php echo $search ?>" onchange="this.form.submit();" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>"/>
					<button type="submit" class="proopc-btn" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
					<button type="button" class="proopc-btn proopc-clear-filter" onclick="return ProOPC.resetForm(this.form, '#usersearch');" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
				</div>
				<input type="hidden" name="view" value="cart"/>
				<?php echo JHtml::_( 'form.token' ); ?>
			</form>
			<div class="clear"></div>
			<form action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart', false); ?>" method="post" class="proopc-form-inline">
				<div class="proopc-field-group">
					<?php echo JHtml::_('select.genericlist', $users, 'userID', 'class=""', 'id', 'displayedName', $this->cart->user->virtuemart_user_id, 'shopper_id'); ?>
					<button type="submit" class="proopc-btn" title="<?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?>"><?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?></button>
				</div>
				<input type="hidden" name="view" value="cart"/>
				<input type="hidden" name="task" value="changeShopper"/>
				<?php echo JHtml::_( 'form.token' ); ?>
			</form>
		</div>
	</div>
<?php endif; ?>
