<?php // no direct access
defined ('_JEXEC') or die('Restricted access');
// add javascript for price and cart, need even for quantity buttons, so we need it almost anywhere
vmJsApi::jPrice();

// Get New Products
$db     = JFactory::getDBO();
$query  = "SELECT virtuemart_product_id FROM #__virtuemart_products ORDER BY virtuemart_product_id DESC LIMIT 0, 10";
$db->setQuery($query);
$newIds = $db->loadColumn();

$col = 1;
$pwidth = floor (100 / $products_per_row);
$pwidthdiv = null;
switch ($products_per_row) {
    case 1:
        $pwidthdiv = "span12";
        break;
    case 2:
        $pwidthdiv = "span6";
        break;
    case 3:
        $pwidthdiv = "span4";
        break;
    case 4:
        $pwidthdiv = "span3";
        break;
    case 6:
        $pwidthdiv = "span2";
        break;
}
if ($products_per_row > 1) {
	$float = "floatleft";
} else {
	$float = "center";
}
?>
<div class="vmgroup<?php echo $params->get ('moduleclass_sfx') ?>">

	<?php if ($headerText) { ?>
	<div class="vmheader"><?php echo $headerText ?></div>
	<?php
}
	if ($display_style == "div") {
		?>
		<div class="vmproduct <?php echo $params->get ('moduleclass_sfx'); ?> row-fluid productdetails">
			<?php foreach ($products as $product) { 
				// Show Label Sale Or New
				$basePriceWithTax = $currency->createPriceDiv('basePrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
				$salesPrice = $currency->createPriceDiv('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
				$discountAmount = $currency->createPriceDiv('discountAmount', '', $product->prices);
				
				$isSaleLabel = (!empty($product->prices['discountAmount'])) ? 1 : 0;
				
				$pid = $product->virtuemart_product_id;
				$isNewLabel = in_array($pid, $newIds);
			?>
			<div class="<?php echo $pwidthdiv ?> <?php echo $float ?>">
				<div class="item-inner">
					<div class="pull-left">
					<?php
					if (!empty($product->images[0])) {
						$image = $product->images[0]->displayMediaThumb ('class="featuredProductImage" border="0"', FALSE);
					} else {
						$image = '';
					}
					echo JHTML::_ ('link', JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id), $image, array('title' => $product->product_name));
					?>
					</div>
					<div class="text-block">
						<?php 
						$url = JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' .
							$product->virtuemart_category_id); ?>
						
						<h2 class="product-name"><a href="<?php echo $url ?>"><?php echo $product->product_name ?></a></h2>      
						<?php  
							$productRating = 1;
							$rating = shopFunctionsF::renderVmSubLayout('rating', array('showRating' => $productRating, 'product' => $product));
						?>
						<!-- Product Rating -->
						<?php if($productRating) : ?>
						<div class="ratings">
							<?php
							$ratingModel = VmModel::getModel('ratings');
							$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
							$rating = $ratingModel->getRatingByProduct($product->virtuemart_product_id);
							$reviews = $ratingModel->getReviewsByProduct($product->virtuemart_product_id);
							if(empty($rating->rating)) { ?>						
								<div class="ratingbox dummy" title="<?php echo vmText::_('COM_VIRTUEMART_UNRATED'); ?>" >
								</div>
							<?php } else {						
								$ratingwidth = $rating->rating * 14; ?>
								<div title=" <?php echo (vmText::_("COM_VIRTUEMART_RATING_TITLE") . round($rating->rating) . '/' . $maxrating) ?>" class="ratingbox" >
								  <div class="stars-orange" style="width:<?php echo $ratingwidth.'px'; ?>"></div>
								</div>
							<?php } ?> 
							<?php if(!empty($reviews)) {					
								$count_review = 0;
								foreach($reviews as $k=>$review) {
									$count_review ++;
								}										
							?>
								<span class="amount">
									<a href="<?php echo $product->link.$ItemidStr; ?>"><?php echo $count_review;?> <?php echo JText::_('VINA_REVIEW'); ?></a>
								</span>
							<?php } ?>
						</div>
						<?php endif; ?>
						
						<?php 
						if ($show_price) {
						?>
							<div class="price-box">
								<!-- Product Price -->
								<?php if($isSaleLabel!= 0) { ?>
									<div class="sale-price">
										<?php echo $basePriceWithTax ?>
										<?php echo $salesPrice ?>
									</div>
								<?php } else { ?>
								<div class="regular-price">
									<?php echo $salesPrice ?>
								</div>
								<?php } ?>
							</div>
						<?php } ?>
						
						<?php if ($show_addtocart) {
							echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product));
						}
						?>
					</div>
				</div>
			</div>
			<?php
			if ($col == $products_per_row && $products_per_row && $col < $totalProd) {
				echo "	</div><div class='row-fluid productdetails'>";
				$col = 1;
			} else {
				$col++;
			}
		} ?>
		</div>

		<?php
	} else {
		$last = count ($products) - 1;
		?>

		<ul class="vmproduct <?php echo $params->get ('moduleclass_sfx'); ?> row-fluid">
			<?php foreach ($products as $product) : 
				// Show Label Sale Or New
				$basePriceWithTax = $currency->createPriceDiv('basePrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
				$salesPrice = $currency->createPriceDiv('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
				$discountAmount = $currency->createPriceDiv('discountAmount', '', $product->prices);
				
				$isSaleLabel = (!empty($product->prices['discountAmount'])) ? 1 : 0;
				
				$pid = $product->virtuemart_product_id;
				$isNewLabel = in_array($pid, $newIds);
				
			?>
			<li class="<?php echo $pwidthdiv ?> <?php echo $float ?>">
				<div class="vm-product">
					<div class="item">
						<!-- Check Product Label -->
						<?php if($isSaleLabel != 0 && $isNewLabel==0) : ?>
							<div class="label-pro label-pro-sale"><?php echo JTEXT::_('VINA_VIRTUEMART_SALES'); ?></div>
						<?php endif; ?>
						<?php if($isNewLabel && $isSaleLabel != 0) : ?>
						<div class="label-pro label-pro-sale-new"><?php echo JTEXT::_('VINA_VIRTUEMART_NEW'); ?></div>
						<?php endif; ?>
						<?php if($isNewLabel && $isSaleLabel == 0) : ?>
						<div class="label-pro label-pro-new"><?php echo JTEXT::_('VINA_VIRTUEMART_NEW'); ?></div>
						<?php endif; ?>
						<div class="image-block">	
							<!-- Check Product Label -->
							<?php if($isSaleLabel == 1) : ?>
							<div class="label-pro label-percentage"><?php echo $discountAmount; ?></div>
							<?php endif; ?>	
							<!--<a title="<?php //echo $product->product_name ?>" href="<?php //echo $product->link.$ItemidStr; ?>">
								<?php //echo $product->images[0]->displayMediaThumb('class="browseProductImage"', false); ?>						
							</a> -->
							<?php
								if (!empty($product->images[0])) {
									$image = $product->images[0]->displayMediaThumb ('class="featuredProductImage" border="0"', FALSE);
								} else {
									$image = '';
								}
								echo JHTML::_ ('link', JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id), $image, array('title' => $product->product_name));
								echo '<div class="clear"></div>';
								$url = JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' .
									$product->virtuemart_category_id); 
							?>																	
						</div>
						<div class="box-des">
							<div class="actions actions-top">
								<ul class="add-to-links">
									<li>
										<!-- Add Wishlist Button -->
										<?php if(is_dir(JPATH_BASE . "/components/com_wishlist/")) :
										 $app = JFactory::getApplication();
										?>
										<?php require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); ?>
										<?php endif; ?>
									</li>
									<li class="jutooltip" title="<?php echo JText::_( 'VINA_VIEW_DETAIL' ); ?>"><a href="<?php echo $url ?>" title="<?php echo JText::_( 'VINA_VIEW_DETAIL' ); ?>" class="product-details"><?php echo JText::_( 'VINA_VIEW_DETAIL' ); ?></a></li>
								</ul>
								<?php 
									if ($show_addtocart) {
										echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product));
									}
								?>
							</div>
							<div class="text-block">
								<?php 
								$url = JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' .
									$product->virtuemart_category_id); ?>
								
								<h2 class="product-name"><a href="<?php echo $url ?>"><?php echo $product->product_name ?></a></h2>      
								<?php  
									$productRating = 1;
									$rating = shopFunctionsF::renderVmSubLayout('rating', array('showRating' => $productRating, 'product' => $product));
								?>
								<!-- Product Rating -->
								<?php if($productRating) : ?>
								<div class="product-rating">
									<?php
									$ratingModel = VmModel::getModel('ratings');
									$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
									$rating = $ratingModel->getRatingByProduct($product->virtuemart_product_id);
									$reviews = $ratingModel->getReviewsByProduct($product->virtuemart_product_id);
									if(empty($rating->rating)) { ?>						
										<div class="ratingbox dummy" title="<?php echo vmText::_('COM_VIRTUEMART_UNRATED'); ?>" >
										</div>
									<?php } else {						
										$ratingwidth = $rating->rating * 14; ?>
										<div title=" <?php echo (vmText::_("COM_VIRTUEMART_RATING_TITLE") . round($rating->rating) . '/' . $maxrating) ?>" class="ratingbox" >
										  <div class="stars-orange" style="width:<?php echo $ratingwidth.'px'; ?>"></div>
										</div>
									<?php } ?> 
									<?php if(!empty($reviews)) {					
										$count_review = 0;
										foreach($reviews as $k=>$review) {
											$count_review ++;
										}										
									?>
										<span class="amount">
											<a href="<?php echo $product->link.$ItemidStr; ?>"><?php echo $count_review;?> <?php echo JText::_('VINA_REVIEW'); ?></a>
										</span>
									<?php } ?>
								</div>
								<?php endif; ?>
								
								<!-- Product Price -->
								<?php 
								if ($show_price) {
								?>
									<div class="price-box">
										<!-- Product Price -->
										<?php if($isSaleLabel!= 0) { ?>
											<div class="sale-price">
												<?php echo $basePriceWithTax ?>
												<?php echo $salesPrice ?>
											</div>
										<?php } else { ?>
										<div class="regular-price">
											<?php echo $salesPrice ?>
										</div>
										<?php } ?>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</li>
			<?php
			if ($col == $products_per_row && $products_per_row && $last) {
				echo '
		</ul><div class="clear"></div>
		<ul  class="vmproduct ' . $params->get ('moduleclass_sfx') . ' row-fluid">';
				$col = 1;
			} else {
				$col++;
			}
			$last--;
		endforeach; ?>
		</ul>
		<div class="clear"></div>

		<?php
	}
	if ($footerText) : ?>
		<div class="vmfooter<?php echo $params->get ('moduleclass_sfx') ?>">
			<?php echo $footerText ?>
		</div>
		<?php endif; ?>
</div>