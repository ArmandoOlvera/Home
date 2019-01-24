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
$Revision: 19 $
$LastChangedDate: 2015-01-24 19:28:10 +0530 (Sat, 24 Jan 2015) $
$Id: default_logout.php 19 2015-01-24 13:58:10Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');
$return = JRoute::_('index.php?option=com_virtuemart&view=cart', false);
?>
<?php if(!$this->juser->guest) : ?>
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="logout" id="form-logout">
		<div class="proopc-loggedin-user"><?php echo vmText::sprintf('COM_VIRTUEMART_WELCOME_USER', $this->juser->name); ?> <b class="caret"></b></div>
		<div class="proopc-logout-cont hide">
			<div class="proopc_arrow_box">
				<div class="proopc-arrow-inner">
					<button type="submit" class="proopc-btn<?php echo ($this->params->get('color', 1) == 2) ? ' proopc-btn-danger' : ''; ?>"><?php echo JText::_( 'JLOGOUT'); ?></button>
				</div>
			</div>
		</div>
		<input type="hidden" name="option" value="com_users"/>
		<input type="hidden" name="task" value="user.logout"/>
		<input type="hidden" name="return" value="<?php echo base64_encode($return) ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
<?php endif; ?>