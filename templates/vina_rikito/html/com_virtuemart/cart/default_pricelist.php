<fieldset class="vm-fieldset-pricelist">
<table
	class="cart-summary" style="width: 100%;">
	<thead>
		<tr class="first last">
			<th class="tb-image" style="width: 15%">&nbsp;</th>
			<th class="tb-name" style="width: 40%"><span class="nobr"><?php echo vmText::_ ('COM_VIRTUEMART_CART_NAME') ?></span></th>
			<th class="tb-sku" style="width: 10%"><?php echo vmText::_ ('COM_VIRTUEMART_CART_SKU') ?></th>
			<th class="tb-price" style="width: 10%"><span class="nobr"><?php echo vmText::_ ('COM_VIRTUEMART_CART_PRICE') ?></span></th>
			<th class="tb-quantity" style="width: 10%"><?php echo vmText::_ ('COM_VIRTUEMART_CART_QUANTITY') ?></th>
			<th class="tb-subtotal" style="width: 10%"><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOTAL') ?></th>
			<th class="tb-delete" style="width: 5%">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
<?php
$i = 1;

foreach ($this->cart->products as $pkey => $prow) { ?>
	<tr class="sectiontableentry<?php echo $i ?>">
		<td class="tb-image2">
			<?php if ($prow->virtuemart_media_id) { ?>
			<span class="cart-images">
				<?php
				if (!empty($prow->images[0])) {
					echo $prow->images[0]->displayMediaThumb ('', FALSE);
				}
				?>	
			</span>
			<?php } ?>
		</td>
		<td class="tb-name2">
			<div class="product-name">
				<?php echo JHtml::link ($prow->url, $prow->product_name);
					echo $this->customfieldsModel->CustomsFieldCartDisplay ($prow);
				?>
			</div>
		</td>
		<td class="tb-sku2">
			<?php  echo $prow->product_sku ?>
		</td>
		<td class="tb-price2">
			<?php
			if (VmConfig::get ('checkout_show_origprice', 1) && !empty($prow->prices['basePriceWithTax']) && $prow->prices['basePriceWithTax'] != $prow->prices['salesPrice']) {
				//echo '<span class="line-through">' . $this->currencyDisplay->createPriceDiv ('basePriceWithTax', '', $prow->prices, TRUE, FALSE, $prow->quantity) . '</span>';
			}
			elseif (VmConfig::get ('checkout_show_origprice', 1) && empty($prow->prices['basePriceWithTax']) && $prow->prices['basePriceVariant'] != $prow->prices['salesPrice']) {
				echo '<span class="line-through">' . $this->currencyDisplay->createPriceDiv ('basePriceVariant', '', $prow->prices, TRUE, FALSE, $prow->quantity) . '</span>';
			}
			echo $this->currencyDisplay->createPriceDiv ('salesPrice', '', $prow->prices, FALSE, FALSE, 1) ?>
		</td>
		<!-- inclusive price starts here -->
		<td class="tb-quantity2">
						<?php

			if ($prow->step_order_level)
				$step=$prow->step_order_level;
			else
				$step=1;
			if($step==0)
				$step=1;
			?>
		   <input type="text"
				   onblur="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');"
				   onclick="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');"
				   onchange="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');"
				   onsubmit="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');"
				   title="<?php echo  vmText::_('COM_VIRTUEMART_CART_UPDATE') ?>" class="quantity-input js-recalculate" size="3" maxlength="4" name="quantity[<?php echo $pkey; ?>]" value="<?php echo $prow->quantity ?>" />

			<button type="submit" class="vmicon vm2-add_quantity_cart" name="updatecart.<?php echo $pkey ?>" title="<?php echo  vmText::_ ('COM_VIRTUEMART_CART_UPDATE') ?>"></button>
		</td>

		<!--Sub total starts here -->
		<td class="tb-subtotal2">
			<?php
			if (VmConfig::get ('checkout_show_origprice', 1) && !empty($prow->prices['basePriceWithTax']) && $prow->prices['basePriceWithTax'] != $prow->prices['salesPrice']) {
				//echo '<span class="line-through">' . $this->currencyDisplay->createPriceDiv ('basePriceWithTax', '', $prow->prices, TRUE, FALSE, $prow->quantity) . '</span>';
			}
			elseif (VmConfig::get ('checkout_show_origprice', 1) && empty($prow->prices['basePriceWithTax']) && $prow->prices['basePriceVariant'] != $prow->prices['salesPrice']) {
				echo '<span class="line-through">' . $this->currencyDisplay->createPriceDiv ('basePriceVariant', '', $prow->prices, TRUE, FALSE, $prow->quantity) . '</span>';
			}
			echo $this->currencyDisplay->createPriceDiv ('salesPrice', '', $prow->prices, FALSE, FALSE, $prow->quantity) ?>
		</td>
		<td class="tb-delete2 last">
			<button type="submit" class="vmicon vm2-remove_from_cart" name="delete.<?php echo $pkey ?>" title="<?php echo vmText::_ ('COM_VIRTUEMART_CART_DELETE') ?>" ></button>
		</td>
	</tr>
	<?php
	$i = ($i==1) ? 2 : 1;
} ?>

<!--Begin of SubTotal, Tax, Shipment, Coupon Discount and Total listing -->
<?php if (VmConfig::get ('show_tax')) {
	$colspan = 3;
} else {
	$colspan = 2;
} ?>
<tr class="tb-total">
	<td colspan="7" class="total-title">
		<div class="vm-continue-shopping">
			<?php // Continue Shopping Button
			if (!empty($this->continue_link_html)) {
				echo $this->continue_link_html;
			} ?>
		</div>
		<div class="title"><?php echo vmText::_ ('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_PRICES_TOTAL'); ?></div>
		<div class="total" >
			<?php if (VmConfig::get ('show_tax')) { ?>
				<?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('taxAmount', '', $this->cart->cartPrices, FALSE) . "</span>" ?>
			<?php } ?>
			<?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('discountAmount', '', $this->cart->cartPrices, FALSE) . "</span>" ?>
			<?php echo $this->currencyDisplay->createPriceDiv ('salesPrice', '', $this->cart->cartPrices, FALSE) ?>
			
		</div>
	</td>
</tr>
</tbody>
<tfoot>
<?php
if (VmConfig::get ('coupons_enable')) {
	?>
<tr class="sectiontableentry2">
<td class="couponcode" colspan="7">
	<div class="tb-tfoot">
		<?php if (!empty($this->layoutName) && $this->layoutName == 'default') {
		echo $this->loadTemplate ('coupon');
	}
		?>

		<?php if (!empty($this->cart->cartData['couponCode'])) { ?>
		<?php
		echo $this->cart->cartData['couponCode'];
		echo $this->cart->cartData['couponDescr'] ? (' (' . $this->cart->cartData['couponDescr'] . ')') : '';
		?>
	</div>
</td>
<?php if (VmConfig::get ('show_tax')) { ?>
<td><?php echo $this->currencyDisplay->createPriceDiv ('couponTax', '', $this->cart->cartPrices['couponTax'], FALSE); ?> </td>
<?php } ?>
<td> </td>
<td><?php echo $this->currencyDisplay->createPriceDiv ('salesPriceCoupon', '', $this->cart->cartPrices['salesPriceCoupon'], FALSE); ?> </td>
<?php } else { ?>
	<?php
}

	?>
</tr>
	<?php } ?>
<?php
foreach ($this->cart->cartData['DBTaxRulesBill'] as $rule) {
	?>
<tr class="sectiontableentry<?php echo $i ?>">
	<td colspan="4"><?php echo $rule['calc_name'] ?> </td>

	<?php if (VmConfig::get ('show_tax')) { ?>
	<td></td>
	<?php } ?>
	<td><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?></td>
	<td><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
</tr>
	<?php
	if ($i) {
		$i = 1;
	} else {
		$i = 0;
	}
} ?>

<?php

foreach ($this->cart->cartData['taxRulesBill'] as $rule) {
	?>
<tr class="sectiontableentry<?php echo $i ?>">
	<td colspan="4"><?php echo $rule['calc_name'] ?> </td>
	<?php if (VmConfig::get ('show_tax')) { ?>
	<td><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
	<?php } ?>
	<td><?php ?> </td>
	<td><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
</tr>
	<?php
	if ($i) {
		$i = 1;
	} else {
		$i = 0;
	}
}

foreach ($this->cart->cartData['DATaxRulesBill'] as $rule) {
	?>
<tr class="sectiontableentry<?php echo $i ?>">
	<td colspan="4"><?php echo   $rule['calc_name'] ?> </td>

	<?php if (VmConfig::get ('show_tax')) { ?>
	<td ></td>

	<?php } ?>
	<td><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?>  </td>
	<td><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
</tr>
	<?php
	if ($i) {
		$i = 1;
	} else {
		$i = 0;
	}
} ?>
<?php if ( 	VmConfig::get('oncheckout_opc',true) or
	!VmConfig::get('oncheckout_show_steps',false) or
	(!VmConfig::get('oncheckout_opc',true) and VmConfig::get('oncheckout_show_steps',false) and
		!empty($this->cart->virtuemart_shipmentmethod_id) )
) { ?>
<tr class="sectiontableentry1">
	<?php if (!$this->cart->automaticSelectedShipment) { ?>
		<td class="shipment" colspan="7">
			<?php
				echo $this->cart->cartData['shipmentName'].'<br/>';

		if (!empty($this->layoutName) and $this->layoutName == 'default') {
			if (VmConfig::get('oncheckout_opc', 0)) {
				$previouslayout = $this->setLayout('select');
				echo $this->loadTemplate('shipment');
				$this->setLayout($previouslayout);
			} else {
				echo JHtml::_('link', JRoute::_('index.php?option=com_virtuemart&view=cart&task=edit_shipment', $this->useXHTML, $this->useSSL), $this->select_shipment_text, 'class=""');
			}
		} else {
			echo vmText::_ ('COM_VIRTUEMART_CART_SHIPPING');
		}
	} else {
	?>
	<td colspan="7">
		<?php echo $this->cart->cartData['shipmentName']; ?>
	</td>
	<?php } ?>

	<?php if (VmConfig::get ('show_tax')) { ?>
	<td><?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('shipmentTax', '', $this->cart->cartPrices['shipmentTax'], FALSE) . "</span>"; ?> </td>
	<?php } ?>
	<td><?php if($this->cart->cartPrices['salesPriceShipment'] < 0) echo $this->currencyDisplay->createPriceDiv ('salesPriceShipment', '', $this->cart->cartPrices['salesPriceShipment'], FALSE); ?></td>
	<td><?php echo $this->currencyDisplay->createPriceDiv ('salesPriceShipment', '', $this->cart->cartPrices['salesPriceShipment'], FALSE); ?> </td>
</tr>
<?php } ?>
<?php if ($this->cart->pricesUnformatted['salesPrice']>0.0 and
	( 	VmConfig::get('oncheckout_opc',true) or
		!VmConfig::get('oncheckout_show_steps',false) or
		( (!VmConfig::get('oncheckout_opc',true) and VmConfig::get('oncheckout_show_steps',false) ) and !empty($this->cart->virtuemart_paymentmethod_id))
	)
) { ?>
<tr class="sectiontableentry1">
	<?php if (!$this->cart->automaticSelectedPayment) { ?>
		<td class="payment" colspan="7">
			<?php
				echo $this->cart->cartData['paymentName'].'<br/>';

		if (!empty($this->layoutName) && $this->layoutName == 'default') {
			if (VmConfig::get('oncheckout_opc', 0)) {
				$previouslayout = $this->setLayout('select');
				echo $this->loadTemplate('payment');
				$this->setLayout($previouslayout);
			} else {
				echo JHtml::_('link', JRoute::_('index.php?option=com_virtuemart&view=cart&task=editpayment', $this->useXHTML, $this->useSSL), $this->select_payment_text, 'class=""');
			}
		} else {
		echo vmText::_ ('COM_VIRTUEMART_CART_PAYMENT');
	} ?> </td>
	<?php } else { ?>
	<td colspan="7"><?php echo $this->cart->cartData['paymentName']; ?> </td>
	<?php } ?>
	<?php if (VmConfig::get ('show_tax')) { ?>
	<td ><?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('paymentTax', '', $this->cart->cartPrices['paymentTax'], FALSE) . "</span>"; ?> </td>
	<?php } ?>
	<td><?php if($this->cart->cartPrices['salesPriceShipment'] < 0) echo $this->currencyDisplay->createPriceDiv ('salesPricePayment', '', $this->cart->cartPrices['salesPricePayment'], FALSE); ?></td>
	<td><?php  echo $this->currencyDisplay->createPriceDiv ('salesPricePayment', '', $this->cart->cartPrices['salesPricePayment'], FALSE); ?> </td>
</tr>
<?php  } ?>
<tr>
	<td colspan="3">&nbsp;</td>
	<td colspan="4">
		<hr/>
	</td>
</tr>
<tr class="checkout-price">
	<td colspan="7">
		<div class="checkout-button-top"> 
		<?php
			echo $this->checkout_link_html;
		?>
		</div>
	</td>
</tr>

<?php
if ($this->totalInPaymentCurrency) {
?>

<tr class="sectiontableentry2">
	<td colspan="4"><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOTAL_PAYMENT') ?>:</td>

	<?php if (VmConfig::get ('show_tax')) { ?>
	<td ></td>
	<?php } ?>
	<td></td>
	<td><strong><?php echo $this->totalInPaymentCurrency;   ?></strong></td>
</tr>
	<?php
}
?>
</tfoot>
</table>
</fieldset>
