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
$Revision: 5 $
$LastChangedDate: 2015-01-22 02:23:43 +0530 (Thu, 22 Jan 2015) $
$Id: default_guest.php 5 2015-01-21 20:53:43Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');
$emailField = !empty($this->regFields['fields']) && isset($this->regFields['fields']['email']) ?
              $this->regFields['fields']['email'] : null;
?>
<form method="post" id="GuestUser" autocomplete="off">
	<?php if(!empty($emailField) && is_array($emailField)) : ?>
		<div class="proopc-group">
			<div class="proopc-input-group-level">
				<label class="<?php echo $emailField['name'] ?> full-input" for="<?php echo $emailField['name'] ?>_field">
					<span><?php echo vmText::_($emailField['title']) ?></span>
				</label>
			</div>
			<div class="proopc-input proopc-input-append">
				<?php echo $emailField['formcode'] ?>
				<i class="status hover-tootip"></i>
			</div>
		</div>
	<?php endif; ?>
	<div class="proops-login-inputs">	
		<div class="proopc-group">
			<div class="proopc-input proopc-input-prepend">
				<button class="proopc-btn<?php echo ($this->params->get('color', 1) == 2) ? ' proopc-btn-danger' : ' proopc-btn-inverse'; ?>" type="submit" onclick="return ProOPC.guestcheckout();"><i id="proopc-guest-process" class="proopc-button-process"></i><?php echo vmText::_('COM_VIRTUEMART_CHECKOUT_AS_GUEST') ?></button>
			</div>
		</div>
		<input type="hidden" name="ctask" value="savebtaddress" />
		<?php echo JHTML::_('form.token'); ?>
	</div>	
</form>