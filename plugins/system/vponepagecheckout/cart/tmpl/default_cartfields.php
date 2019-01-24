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
$Id: default_cartfields.php 5 2015-01-21 20:53:43Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');
$hiddenFields = '';
?>
<?php if(!empty($this->userFieldsCart['fields'])) : ?>
	<?php foreach($this->userFieldsCart['fields'] as $field) : ?>
		<?php if($field['hidden']) :
			$hiddenFields .= $field['formcode'] . "\n";
		else : ?>
			<?php if($field['name'] == 'customer_note' || $field['type'] == 'textarea') : ?>
				<div class="customer-comment-group">
					<label for="<?php echo $field['name'] ?>_field" class="comment"><?php echo $field['title'] ?></label>
					<?php $field['formcode'] = str_replace('rows="1"', 'rows="3"', $field['formcode']); ?>
					<?php echo strpos($field['formcode'], 'class="') ? 
					str_replace('class="', 'class="customer-comment proopc-customer-comment ', $field['formcode']) : 
					str_replace('<textarea', '<textarea class="customer-comment proopc-customer-comment"', $field['formcode']); ?>
				</div>
			<?php elseif($field['name'] == 'tos') : ?>
				<div class="cart-tos-group">
					<?php
					$this->cart->prepareVendor();
					$tos = (is_array($this->cart->BT) && !empty($this->cart->BT['tos'])) ? $this->cart->BT['tos'] : 0;
					?>
					<?php if(VmConfig::get('oncheckout_show_legal_info', 1)) : ?>
						<?php if($this->params->get('tos_fancybox', 0)) : ?>
							<label for="tos" class="checkbox prooopc-tos-label proopc-row">
								<?php echo VmHtml::checkbox ($field['name'], $tos, 1, 0, 'class="terms-of-service"'); ?>
								<div class="terms-of-service-cont">
									<a href="#proopc-tos-fancy" class="terms-of-service" data-tos="fancybox"><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOS_READ_AND_ACCEPTED'); ?></a>
								</div>
							</label>
							<div class="soft-hide">
								<div id="proopc-tos-fancy" class="fancy-tos-container">
									<div class="fancy-tos-head">
										<button type="button" class="fancy-close"><span aria-hidden="true">&times;</span></button>
										<h3 class="fancy-tos-title"><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOS'); ?></h3>
									</div>
									<div class="fancy-tos-body">
										<p><?php echo $this->cart->vendor->vendor_terms_of_service; ?></p>
									</div>
								</div>
							</div>
						<?php else : ?>
							<label for="tos" class="checkbox prooopc-tos-label proopc-row">
								<?php echo VmHtml::checkbox ($field['name'], $tos, 1, 0, 'class="terms-of-service"'); ?>
								<div class="terms-of-service-cont">
									<a href="#proopc-tos-fancy" class="terms-of-service" data-toggle="bootmodal"><?php echo JText::_ ('COM_VIRTUEMART_CART_TOS_READ_AND_ACCEPTED'); ?></a>
								</div>
							</label>
							<div class="bootmodal fade" id="proopc-tos-fancy" tabindex="-1" role="dialog" aria-labelledby="tosLabel" aria-hidden="true">
								<div class="bootmodal-header">
									<button type="button" class="close" data-dismiss="bootmodal" aria-hidden="true">&times;</button>
									<h3 id="tosLabel"><?php echo JText::_ ('COM_VIRTUEMART_CART_TOS'); ?></h3>
								</div>
								<div class="bootmodal-body">
									<p><?php echo $this->cart->vendor->vendor_terms_of_service; ?></p>
								</div>
							</div>
						<?php endif; ?>
					<?php else : ?>
						<label for="tos" class="checkbox prooopc-tos-label proopc-row">
							<?php echo VmHtml::checkbox ($field['name'], $tos, 1, 0, 'class="terms-of-service"'); ?> <?php echo $field['title'] ?>
						</label>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<fieldset class="vm-fieldset-<?php echo str_replace('_', '-', $field['name']) ?>">
					<div class="form-group">
						<label for="<?php echo $field['name'] ?>_field"><?php echo $field['title'] ?></label>
						<?php echo $field['formcode'] ?>
					</div>
				</fieldset>
			<?php endif; ?>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>

<?php echo $hiddenFields; ?>