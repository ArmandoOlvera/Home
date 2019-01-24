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
$Revision: 12 $
$LastChangedDate: 2015-01-23 21:26:59 +0530 (Fri, 23 Jan 2015) $
$Id: default_register.php 12 2015-01-23 15:56:59Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');
?>
<?php if(!empty($this->regFields['fields'])) : ?>
	<form id="UserRegistration" name="userForm" autocomplete="off">
		<?php foreach($this->regFields['fields'] as $name => $field) : ?>
			<div class="proopc-group">
				<div class="proopc-input-group-level">
					<label class="<?php echo $field['name'] ?> full-input" for="<?php echo $field['name'] ?>_field">
						<span><?php echo vmText::_($field['title']) ?></span>
					</label>
				</div>
				<div class="proopc-input proopc-input-append">
					<?php echo str_replace('vm-chzn-select', '', $field['formcode']); ?>
					<i class="status hover-tootip"></i>
					<?php if($field['name'] == 'password' && $this->params->get('live_validation', 1)) : ?>
						<div class="password-stregth">
							<?php echo JText::_('PLG_VPONEPAGECHECKOUT_PASSWORD_STRENGTH') ?>
							<span id="password-stregth"></span>
						</div>
						<div class="strength-meter"><div id="meter-status"></div></div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>	
		<div class="proops-login-inputs">	
			<div class="proopc-group">
				<div class="proopc-input proopc-input-prepend">
					<button class="proopc-btn<?php echo($this->params->get('color', 1) == 2) ? ' proopc-btn-danger' : ' proopc-btn-inverse'; ?>" type="submit" onclick="return ProOPC.registerCheckout();"><i id="proopc-register-process" class="proopc-button-process"></i><?php echo JText::_('COM_VIRTUEMART_REGISTER_AND_CHECKOUT') ?></button>
				</div>
			</div>
			<?php echo JHTML::_( 'form.token' ); ?>
		</div>
	</form>
<?php endif; ?>