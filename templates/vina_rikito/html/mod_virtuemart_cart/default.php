<?php // no direct access
defined('_JEXEC') or die('Restricted access');

//dump ($cart,'mod cart');
// Ajax is displayed in vm_cart_products
// ALL THE DISPLAY IS Done by Ajax using "hiddencontainer" ?>

<!-- Virtuemart 2 Ajax Card -->
<div class="vmCartModule <?php echo $params->get('moduleclass_sfx'); ?>" id="vmCartModule">	
	<div class="top-cart-title">
		<div class="shopping_cart"><i class="fa fa-shopping-cart"></i>
			<div class="total"><?php echo $data->billTotal; ?></div>
		</div>
	</div>
	<div class="top-cart-content">
	<?php
	if ($show_product_list) {
		?>
		<div id="hiddencontainer" class="hiddencontainer" style="display: none;">
			<div class="vmcontainer">
				<div class="product_row">
					<span class="quantity"></span>&nbsp;x&nbsp;<span class="product_name"></span>

				<?php if ($show_price and $currencyDisplay->_priceConfig['salesPrice'][0]) { ?>
					<div class="subtotal_with_tax" style="float: right;"></div>
				<?php } ?>
					<div class="customProductData"></div><br>
				</div>
			</div>
		</div>
		<div class="vm_cart_products">
			<div class="vmcontainer">
			<?php
				foreach ($data->products as $product){
					?>
					<div class="product_row">
						<div class="product-details">
							<p class="product_name"><?php echo  $product['product_name'] ?></p>
							<?php if ($show_price and $currencyDisplay->_priceConfig['salesPrice'][0]) { ?>
							  <p class="subtotal_with_tax"><?php echo  $product['quantity'] ?>&nbsp;x&nbsp;<span class="price"><?php echo $product['subtotal_with_tax'] ?></span></p>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
	<div class="top-cart">
		<div class="total">
			<?php echo $data->billTotal; ?>
		</div>
		<?php echo  $data->cart_show; ?>
	</div>
</div>
<div style="clear:both;"></div>
<div class="payments_signin_button" ></div>
<noscript>
<?php echo vmText::_('MOD_VIRTUEMART_CART_AJAX_CART_PLZ_JAVASCRIPT') ?>
</noscript>
</div>