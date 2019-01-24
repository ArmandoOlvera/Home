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
$Revision: 38 $
$LastChangedDate: 2015-03-04 18:17:34 +0530 (Wed, 04 Mar 2015) $
$Id: default_login.php 38 2015-03-04 12:47:34Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');
$return = JRoute::_('index.php?option=com_virtuemart&view=cart', false);
$twofactormethods = array();
if(version_compare(JVERSION, '3.0.0', 'ge'))
{
	require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';
	$twofactormethods = UsersHelper::getTwoFactorMethods();
}
$email_as_username = (int) $this->params->get('email_as_username', 2);
?>
<?php if($this->juser->guest) : ?>
	<h4 class="proopc-subtitle"><?php echo JText::_('PLG_VPONEPAGECHECKOUT_ASK_FOR_LOGIN'); ?></h4>
	<form name="proopc-login" id="UserLogin" autocomplete="off">
		<div class="proopc-group">
			<div class="proopc-input-group-level">
				<?php if($email_as_username == 1) : ?>
					<label class="full-input" for="proopc-username"><?php echo vmText::_('COM_VIRTUEMART_EMAIL'); ?></label>
				<?php elseif($email_as_username == 2) : ?>
					<label class="full-input" for="proopc-username"><?php echo vmText::_('COM_VIRTUEMART_USERNAME'); ?> / <?php echo vmText::_('COM_VIRTUEMART_EMAIL'); ?></label>
				<?php else : ?>
					<label class="full-input" for="proopc-username"><?php echo vmText::_('COM_VIRTUEMART_USERNAME'); ?></label>
				<?php endif; ?>
			</div>
			<div class="proopc-input proopc-input-append">
				<input type="text" id="proopc-username" name="username" class="inputbox input-medium" size="18" />	
				<i class="status hover-tootip"></i>
			</div>
		</div>
		<div class="proopc-group">
			<div class="proopc-input-group-level">
				<label class="full-input" for="proopc-passwd"><?php echo JText::_('COM_VIRTUEMART_PASSWORD'); ?></label>
			</div>
			<div class="proopc-input proopc-input-append">
				<input id="proopc-passwd" type="password" name="password" class="inputbox input-medium" size="18" />
				<i class="status hover-tootip"></i>
			</div>
		</div>
		<?php if (count($twofactormethods) > 1): ?>
		<div id="form-login-secretkey" class="proopc-group">
			<div class="proopc-input-group-level">
				<label class="full-input" for="proopc-secretkey"><?php echo JText::_('JGLOBAL_SECRETKEY') ?></label>
			</div>
			<div class="proopc-input proopc-input-append">
				<input id="proopc-secretkey" autocomplete="off" type="text" name="secretkey" class="inputbox input-medium" size="18" />
			</div>
		</div>
		<?php endif; ?>
		<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
			<div class="proopc-group">
				<div class="proopc-input proopc-input-append">
					<label for="proopc-remember" class="proopc-checkbox inline">
						<input type="checkbox" id="proopc-remember" name="remember" class="inputbox" value="yes" alt="<?php echo JText::_('JGLOBAL_REMEMBER_ME') ?>"/>
						<?php echo JText::_('JGLOBAL_REMEMBER_ME') ?>
					</label>
				</div>
			</div>
		<?php endif; ?>
		<div class="proops-login-inputs">
			<div class="proopc-group">
				<div class="proopc-input proopc-input-prepend">
					<button type="submit" class="proopc-btn<?php echo ($this->params->get('color', 1) == 2) ? ' proopc-btn-danger' : ' proopc-btn-inverse'; ?>" onclick="return ProOPC.loginAjax()"><i id="proopc-login-process" class="proopc-button-process"></i><?php echo JText::_('PLG_VPONEPAGECHECKOUT_LOGIN_AND_CHECKOUT') ?></button>
				</div>
			</div>
			<input type="hidden" name="ctask" value=""/>
			<input type="hidden" name="return" id="proopc-return"	value="<?php echo base64_encode($return); ?>"/>
			<?php echo JHtml::_('form.token');?>
		</div>
		<div class="proops-login-inputs">
			<div class="proopc-group">
				<div class="proopc-input">
					<ul class="proopc-ul">
						<li><a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>"><?php echo JText::_('COM_VIRTUEMART_ORDER_FORGOT_YOUR_USERNAME'); ?></a></li>
						<li><a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>"><?php echo JText::_('COM_VIRTUEMART_ORDER_FORGOT_YOUR_PASSWORD'); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
	</form>
<?php endif; ?>