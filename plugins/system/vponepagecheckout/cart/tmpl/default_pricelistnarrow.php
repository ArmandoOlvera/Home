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
$Revision: 32 $
$LastChangedDate: 2015-02-05 00:27:35 +0530 (Thu, 05 Feb 2015) $
$Id: default_pricelistnarrow.php 32 2015-02-04 18:57:35Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined ('_JEXEC') or die('Restricted access');
?>
<div class="inner-wrap">
	<table class="proopc-cart-summery" width="100%" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th class="col-name" align="left"><?php echo JText::_('COM_VIRTUEMART_CART_NAME') ?></th>
				<th class="col-qty" align="center"><?php echo JText::_('COM_VIRTUEMART_CART_QUANTITY') ?></th>
				<th class="col-total" align="right"><?php echo JText::_('COM_VIRTUEMART_CART_TOTAL') ?></th>			
			</tr>
		</thead>
		<?php
		$i = 1;
		foreach($this->cart->products as $pkey => $prow) : ?>
		<tbody class="proopc-cart-product" data-details="proopc-product-details<?php echo $i ?>">
			<tr valign="top" class="proopc-cart-entry<?php echo $i ?> proopc-p-list" >
				<td class="col-name">
					<?php 
						echo JHTML::link($prow->url, $prow->product_name);
						echo $this->customfieldsModel->CustomsFieldCartDisplay($prow); 
					?>
					<div class="proopc-p-price">
						<span><?php echo trim(JText::_('COM_VIRTUEMART_CART_PRICE')) ?>: </span>
						<?php if ($prow->prices['discountedPriceWithoutTax']) : ?>
							<span class="PricediscountedPriceWithoutTax nowrap"><?php echo $this->currencyDisplay->priceDisplay($prow->prices['discountedPriceWithoutTax']); ?></span>
						<?php else : ?>
							<span class="PricebasePriceVariant nowrap"><?php echo $this->currencyDisplay->priceDisplay($prow->prices['basePriceVariant']); ?></span>
						<?php endif; ?>
					</div>
					<?php if($this->params->get('show_sku', 1)) : ?>
						<div class="proopc-p-sku">
							<?php echo JText::_('COM_VIRTUEMART_CART_SKU') . ': '. $prow->product_sku; ?>
						</div>
					<?php endif; ?>
				</td>
				<td class="col-qty" align="center">
					<?php echo $prow->quantity ?>
				</td>
				<td class="col-total nowrap" colspan="1" align="right">
					<?php if (VmConfig::get('checkout_show_origprice', 1) && !empty($prow->prices['basePriceWithTax']) && $prow->prices['basePriceWithTax'] != $prow->prices['salesPrice']) : ?>
						<span class="line-through"><?php echo $this->currencyDisplay->createPriceDiv('basePriceWithTax', '', $prow->prices, true, false, $prow->quantity); ?></span><br/>
					<?php elseif(VmConfig::get ('checkout_show_origprice', 1) && empty($prow->prices['basePriceWithTax']) && $prow->prices['basePriceVariant'] != $prow->prices['salesPrice']) : ?>
						<span class="line-through"><?php echo $this->currencyDisplay->createPriceDiv('basePriceVariant', '', $prow->prices, true, false, $prow->quantity); ?></span><br/>
					<?php endif; ?>
					<?php echo $this->currencyDisplay->createPriceDiv('salesPrice', '', $prow->prices, false, false, $prow->quantity) ?>
				</td>
			</tr>
			<?php // Start - Mouse Over Details ?>
			<tr id="proopc-product-details<?php echo $i ?>" class="proopc-product-hover soft-hide">
				<td colspan="4">
					<div class="proopc_arrow_box">
					<table class="proopc-p-info-table">
						<tr>
							<?php if($prow->virtuemart_media_id && !empty($prow->images[0]) && VmConfig::get('oncheckout_show_images')) {  ?>
								<td colspan="2">
									<div class="proopc-product-image">
										<div class="p-info-inner">
											<?php echo $prow->images[0]->displayMediaThumb('class="img-reponsive"', false); ?>
										</div>
									</div>
									<div class="proopc-p-info">
										<div class="p-info-inner">
											<div class="proopc-product-name">
												<?php 
												echo JHTML::link($prow->url, $prow->product_name);
												echo $this->customfieldsModel->CustomsFieldCartDisplay($prow); 
												?>
											</div>
										</div>
									</div>
								</td>
							<?php } else { ?>
								<td colspan="2">
									<div class="proopc-p-info noimage">
										<div class="p-info-inner">
											<div class="proopc-product-name"><?php echo JHTML::link($prow->url, $prow->product_name) . $this->customfieldsModel->CustomsFieldCartDisplay($prow); ?></div>
										</div>
									</div>
								</td>
							<?php } ?>
						</tr>
						<tr class="add-padding">
							<td width="35%" class="proopc-qty-title"><?php echo JText::_('COM_VIRTUEMART_CART_QUANTITY') ?></td>
							<td width="65%">
								<?php
								if($prow->step_order_level)
									$step = $prow->step_order_level;
								else
									$step = 1;
								if($step == 0)
									$step=1;
								?>
								<div class="proopc-qty-update">
									<div class="proopc-input-append">
										<input type="text" onblur="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');" onclick="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');" onchange="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');" onsubmit="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');" title="<?php echo  vmText::_('COM_VIRTUEMART_CART_UPDATE') ?>" class="inputbox input-ultra-mini js-recalculate proopc-qty-input" size="1" maxlength="4" name="quantity[<?php echo $pkey; ?>]" value="<?php echo $prow->quantity ?>" data-quantity="<?php echo $prow->quantity ?>"/>
										<button class="proopc-btn<?php echo ($this->params->get('color', 1) == 2) ? ' proopc-btn-danger' : ''; ?>" name="updatecart.<?php echo $pkey ?>" title="<?php echo  JText::_('COM_VIRTUEMART_CART_UPDATE') ?>" onclick="return ProOPC.updateproductqty(this);"><i class="proopc-icon-refresh"></i></button>
									</div>	
								</div>	
								<span class="proopc-delete-product">
									<button class="remove_from_cart proopc-btn<?php echo ($this->params->get('color', 1) == 2) ? ' proopc-btn-danger' : ''; ?>" name="delete.<?php echo $pkey ?>" title="<?php echo JText::_('COM_VIRTUEMART_CART_DELETE') ?>" data-vpid="<?php echo $prow->cart_item_id  ?>" onclick="return ProOPC.deleteproduct(this);"><i class="proopc-icon-trash"></i></button>
								</span>
							</td>
						</tr>
						<tr class="add-padding add-padding-top">
							<td width="35%"><?php echo JText::_('COM_VIRTUEMART_CART_PRICE') ?></td>
							<td class="col-price nowrap" width="65%" align="right">
								<?php if ($prow->prices['discountedPriceWithoutTax']) : ?>
									<span class="PricediscountedPriceWithoutTax"><?php echo $this->currencyDisplay->priceDisplay($prow->prices['discountedPriceWithoutTax']); ?></span>
								<?php else : ?>
									<span class="PricebasePriceVariant"><?php echo $this->currencyDisplay->priceDisplay($prow->prices['basePriceVariant']); ?></span>
								<?php endif; ?>
							</td>
						</tr>
						<?php if(VmConfig::get('show_tax') && !empty($prow->prices['taxAmount'])) : ?>
							<tr class="add-padding">
								<td width="35%"><?php  echo JText::_('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT') ?></td>
								<td class="col-price nowrap" width="65%" align="right">
									<?php echo $this->currencyDisplay->createPriceDiv ('taxAmount', '', $prow->prices, false, false, $prow->quantity) ?>
								</td>
							</tr>
						<?php endif; ?>
						<?php if(!empty($prow->prices['discountAmount'])) : ?>
							<tr class="add-padding">
								<td width="35%"><?php echo JText::_('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT') ?></td>
								<td class="col-price nowrap" width="65%" align="right">
									<?php echo $this->currencyDisplay->createPriceDiv('discountAmount', '', $prow->prices, false, false, $prow->quantity) ?>
								</td>
							</tr>
						<?php endif; ?>
						<tr class="add-padding add-padding-bottom">
							<td width="35%"><?php echo JText::_('COM_VIRTUEMART_CART_TOTAL') ?></td>
							<td class="col-total-price nowrap" width="65%" align="right">
								<?php echo $this->currencyDisplay->createPriceDiv('salesPrice', '', $prow->prices, false, false, $prow->quantity) ?>
							</td>
						</tr>
					</table>
					</div>
				</td>
			</tr>
		</tbody>
		<?php 
		$i++;
		endforeach; ?>
		
		<tbody class="proopc-subtotal">
			<tr class="proopc-cart-sub-total">
				<td class="sub-headings" colspan="2" align="left">
					<?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_PRICES_TOTAL'); ?>
				</td>
				<td class="col-total" align="right">
					<?php echo $this->currencyDisplay->createPriceDiv('salesPrice','', $this->cart->pricesUnformatted,false); ?>
				</td>
			</tr>		
		</tbody>	
		
		<?php if (VmConfig::get('coupons_enable') && !empty($this->cart->cartData['couponCode'])) : ?>
			<tbody class="proopc-coupon-details">
				<tr class="cart-coupon-row">
					<td class="coupon-form-col" colspan="2" align="left">
						<?php 
						echo '<span>';
						echo $this->cart->cartData['couponCode'] ;
						echo $this->cart->cartData['couponDescr'] ? (' (' . $this->cart->cartData['couponDescr'] . ')' ): '';
						echo '</span>';
						if (VmConfig::get('show_tax') && !empty($this->cart->cartPrices['couponTax']))
						{
							echo '<div class="coupon-tax">';
							echo $this->currencyDisplay->createPriceDiv('couponTax', 'COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT', $this->cart->cartPrices['couponTax'], false);
							echo '</div>';
						}
						?>
					</td>
					<td class="col-total nowrap" align="right">
						<?php echo $this->currencyDisplay->createPriceDiv ('salesPriceCoupon', '', $this->cart->cartPrices['salesPriceCoupon'], false); ?>
					</td>
				</tr>
			</tbody>
		<?php endif; ?>

		<?php if(count($this->cart->cartData['DBTaxRulesBill']) || count($this->cart->cartData['taxRulesBill']) || count($this->cart->cartData['DATaxRulesBill'])) : ?>
			<tbody class="proopc-bill-taxrules">
				<?php foreach($this->cart->cartData['DBTaxRulesBill'] as $rule) : ?>
					<tr class="tax-per-bill dbtax-row">
						<td class="sub-headings" colspan="2">
							<div class="dbtax-cal-name"><?php echo $rule['calc_name'] ?></div>
						</td>
						<td class="col-total nowrap" align="right">
							<?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], false); ?> 
						</td>
					</tr>
				<?php endforeach; ?>
			
				<?php foreach($this->cart->cartData['taxRulesBill'] as $rule) : ?>
					<tr class="tax-per-bill tax-row">
						<td class="sub-headings" colspan="2">
							<div class="tax-cal-name"><?php echo $rule['calc_name'] ?></div>
						</td>
						<td class="col-total nowrap" align="right">
							<?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], false); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			
			<?php foreach($this->cart->cartData['DATaxRulesBill'] as $rule) : ?>
				<tr class="tax-per-bill datax-row">
					<td class="sub-headings" colspan="2">
						<div class="datax-cal-name"><?php echo $rule['calc_name'] ?></div>
					</td>
					<td class="col-total nowrap" align="right">
						<?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], false); ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		<?php endif; ?>
			
			<tbody class="poopc-shipment-table">
				<tr>
					<td class="shipping-heading" colspan="2" align="left">
						<?php echo $this->cart->cartData['shipmentName']; ?>
						<?php if(VmConfig::get('show_tax') && !empty($this->cart->cartPrices['shipmentTax'])) : ?>
							<div class="proopc-taxcomponent">
								<?php echo $this->currencyDisplay->createPriceDiv ('shipmentTax', 'COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT', $this->cart->cartPrices['shipmentTax'], false) ?>
							</div>
						<?php endif; ?>
					</td>
					<td class="col-total" align="right">
						<?php echo $this->currencyDisplay->createPriceDiv ('salesPriceShipment', '', $this->cart->cartPrices['salesPriceShipment'], false); ?>
					</td>
				</tr>
			</tbody>
			
			<tbody class="poopc-payment-table">
				<tr>		
					<td class=payment-heading" colspan="2" align="left">
						<?php echo $this->cart->cartData['paymentName']; ?>
						<?php if(VmConfig::get('show_tax') && !empty($this->cart->cartPrices['paymentTax'])) : ?>
							<div class="proopc-taxcomponent">
								<?php echo $this->currencyDisplay->createPriceDiv ('paymentTax', 'COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT', $this->cart->cartPrices['paymentTax'], false) ?>
							</div>
						<?php endif; ?>
					</td>
					<td class="col-total nowrap" align="right">
						<?php echo $this->currencyDisplay->createPriceDiv ('salesPricePayment', '', $this->cart->cartPrices['salesPricePayment'], false); ?>
					</td>
				</tr>
			</tbody>
			
			<tbody class="proopc-grand-total">
				<?php if (VmConfig::get('show_tax') && !empty($this->cart->cartPrices['billTaxAmount'])) : ?>
					<tr class="grand-total">
						<td class="sub-headings" colspan="2" align="left">
							<?php echo JText::_('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT') ?>
						</td>
						<td class="col-total nowrap" align="right">
							<?php echo $this->currencyDisplay->createPriceDiv ('billTaxAmount', '', $this->cart->cartPrices['billTaxAmount'], false) ?>
						</td>
					</tr>
				<?php endif; ?>
				<?php if(!empty($this->cart->cartPrices['billDiscountAmount'])) : ?>
					<tr class="grand-total">
						<td class="sub-headings" colspan="2" align="left">
							<?php echo JText::_('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT') ?>
						</td>
						<td class="col-total nowrap" align="right">
							<?php echo $this->currencyDisplay->createPriceDiv ('billDiscountAmount', '', $this->cart->cartPrices['billDiscountAmount'], false) ?>
						</td>
					</tr>
				<?php endif; ?>
				<tr class="grand-total">
					<td class="sub-headings" colspan="2" align="left">
						<?php echo JText::_('COM_VIRTUEMART_CART_TOTAL') ?>
					</td>
					<td class="col-total nowrap" align="right">
						<?php echo $this->currencyDisplay->createPriceDiv('billTotal','', $this->cart->pricesUnformatted['billTotal'],false); ?>
					</td>
				</tr>
			</tbody>
			
			<?php if ($this->totalInPaymentCurrency) : ?>
				<tbody class="proopc-grand-total-p-currency">
					<tr class="grand-total-p-currency">
						<td class="sub-headings" colspan="2" align="left">
							<?php echo JText::_('COM_VIRTUEMART_CART_TOTAL_PAYMENT') ?>
						</td>
						<td class="col-total nowrap" align="right">
							<span class="PricesalesPrice"><?php echo $this->totalInPaymentCurrency; ?></span>
						</td>
					</tr>
				</tbody>
			<?php endif; ?>
	</table>
</div>