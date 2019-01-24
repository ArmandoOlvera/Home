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
$Revision: 45 $
$LastChangedDate: 2015-05-28 17:26:05 +0530 (Thu, 28 May 2015) $
$Id: default.php 45 2015-05-28 11:56:05Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined ('_JEXEC') or die('Restricted access');
// Creating shortcuts for the variables.
$user = $this->juser;
$BT_STATE_ID = !empty($this->cart->BT['virtuemart_state_id']) ? $this->cart->BT['virtuemart_state_id'] : 0;
$ST_STATE_ID = !empty($this->cart->ST['virtuemart_state_id']) ? $this->cart->ST['virtuemart_state_id'] : 0;
?>
<div class="payments-signin-button" ></div>
<?php if(empty($this->productsCount)) : ?>
	<div id="ProOPC" class="cart-view emptyCart-view proopc-row<?php echo ($this->params->get('color', 1) == 2) ? ' dark' : ''; ?>">
		<?php echo $this->loadTemplate ('module'); ?>
		<h1 class="cart-page-title"><?php echo JText::_('COM_VIRTUEMART_EMPTY_CART') ?></h1>
		<?php if(!empty($this->continue_link)) : ?>
			<div class="proopc-empty-continue-link">
				<a href="<?php echo $this->continue_link ?>" class="proopc-btn<?php echo ($this->params->get('color', 1) == 2) ? ' proopc-btn-danger' : ''; ?>"><?php echo vmText::_('COM_VIRTUEMART_CONTINUE_SHOPPING') ?></a>
			</div>
		<?php endif; ?>
	</div>
<?php elseif($this->finalStage) : ?>
	<div id="ProOPC" class="cart-view proopc-row<?php echo (($this->params->get('color', 1) == 2) ? ' dark' : '') ?>">
		<?php echo $this->loadTemplate('module') ?>
		<?php echo $this->loadTemplate('shopperform'); ?>
		<?php echo $this->loadTemplate('checkout') ?>
		<div id="formToken"><?php echo JHTML::_( 'form.token' ) ?></div>
		<input type="hidden" id="BTStateID" name="BTStateID" value="<?php echo $BT_STATE_ID ?>"/>
		<input type="hidden" id="STStateID" name="STStateID" value="<?php echo $ST_STATE_ID ?>"/>
	</div>
<?php else : ?>
	<div id="ProOPC" class="cart-view proopc-row<?php echo ($this->params->get('color', 1) == 2) ? ' dark' : ''; ?>">	
		<?php echo $this->loadTemplate('module'); ?>
		<?php echo $this->loadTemplate('shopperform'); ?>
		<div class="proopc-row">
			<h1 class="cart-page-title"><?php echo JText::_ ('COM_VIRTUEMART_CART_TITLE'); ?>&nbsp;<span class="septa">/</span>&nbsp;<span><?php echo vmText::sprintf('COM_VIRTUEMART_CART_X_PRODUCTS', '<span id="proopc-cart-totalqty">' . $this->productsCount . '</span>'); ?></span></h1> 
		</div>
		<div class="proopc-row">
			<?php if(!empty($this->continue_link)) : ?>
				<div class="proopc-continue-link">
					<a href="<?php echo $this->continue_link ?>"><?php echo vmText::_('COM_VIRTUEMART_CONTINUE_SHOPPING') ?></a>
				</div>
			<?php endif; ?>
		</div>
		<input type="hidden" id="proopc-cart-summery" name="proopc-cart-summery" value="1" />
		<form id="proopc-carttable-form">
			<div id="proopc-pricelist" class="first-page">
					<?php echo $this->loadTemplate('pricelist'); ?>
			</div>
			<input type="hidden" name="ctask" value="updateproduct"/>
		</form>
		<?php // This is our system message container. Need to keep it empty. ?>
		<div id="proopc-system-message"></div>
		<?php if($user->guest) : ?>
			<div class="proopc-register-login">
				<div class="proopc-register">
					<?php if(VmConfig::get('oncheckout_show_register') == 0 and VmConfig::get('oncheckout_only_registered') == 0) { ?>
						<h3><?php echo vmText::_('COM_VIRTUEMART_CHECKOUT_AS_GUEST')?></h3>
					<?php } else if(VmConfig::get('oncheckout_only_registered')) { ?>
						<h3><?php echo vmText::_('COM_VIRTUEMART_CART_ONLY_REGISTERED')?></h3>
					<?php } else { ?>
						<h3><?php echo JText::_('PLG_VPONEPAGECHECKOUT_CHECKOUT_AS_GUEST_REGISTER')?></h3>	
					<?php } ?>
					<div class="proopc-inner">
						<?php if(!VmConfig::get('oncheckout_show_register') && !VmConfig::get('oncheckout_only_registered')) { ?>
							<h4 class="proopc-subtitle"><?php echo vmText::_('COM_VIRTUEMART_ENTER_A_VALID_EMAIL_ADDRESS')?></h4>
							<div class="proopc-guest-form">
								<div class="proopc-inner">
									<?php echo $this->loadTemplate('guest'); ?>
								</div>
							</div>
						<?php } elseif(VmConfig::get('oncheckout_only_registered')) { ?>
							<h4 class="proopc-subtitle"><?php echo JText::_('PLG_VPONEPAGECHECKOUT_REGISTER_CONVINIENCE')?></h4>
							<div class="proopc-reg-form show">
								<div class="proopc-inner">
									<?php echo $this->loadTemplate ('register'); ?>
								</div>
							</div>
						<?php } else { ?>
							<h4 class="proopc-subtitle"><?php echo JText::_('PLG_VPONEPAGECHECKOUT_REGISTER_CONVINIENCE')?></h4>			
							<label class="proopc-switch">
								<input type="radio" name="proopc-method" value="guest" onchange="ProOPC.opcmethod();"<?php echo $this->params->get('registration_by_default', 0) ? '' : ' checked="checked"'; ?> autocomplete="off"/> 
								<?php echo vmText::_('COM_VIRTUEMART_CHECKOUT_AS_GUEST') ?>
							</label>
							<div class="proopc-guest-form<?php echo $this->params->get('registration_by_default', 0) ? ' soft-hide' : ''; ?>">
								<div class="proopc-inner with-switch">
									<?php echo $this->loadTemplate ('guest'); ?>
								</div>
							</div>
							<label class="proopc-switch">
								<input type="radio" name="proopc-method" value="register" onchange="ProOPC.opcmethod();" <?php echo $this->params->get('registration_by_default', 0) ? 'checked' : ''; ?> autocomplete="off"/>
								<?php echo vmText::_('COM_VIRTUEMART_REGISTER') ?>
							</label>
							<div class="proopc-reg-form<?php echo $this->params->get('registration_by_default', 0) ? '' : ' soft-hide'; ?>">
								<div class="proopc-inner with-switch">
									<?php echo $this->loadTemplate ('register'); ?>
								</div>
							</div>
							<div class="proopc-reg-advantages<?php echo $this->params->get('registration_by_default', 0) ? ' soft-hide' : ''; ?>">
								<?php if(trim($this->params->get('registration_message', '')) == '') { 
									echo JText::_('PLG_VPONEPAGECHECKOUT_DEFAULT_REGISTRATION_ADVANTAGE_MSG');
								} else {
									echo trim($this->params->get('registration_message', ''));
								} ?>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="proopc-login">
					<h3><?php echo JText::_('PLG_VPONEPAGECHECKOUT_LOGIN_AND_CHECKOUT') ?></h3>
					<div class="proopc-inner">
						<?php echo $this->loadTemplate ('login'); ?>
					</div>
				</div>
			</div>
			<?php endif; ?>
	</div>
<?php endif; ?>