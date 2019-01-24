<?php defined('_JEXEC') or die('Restricted access');
$currency = $viewData['currency'];
$related = $viewData['related'];
$customfield = $viewData['customfield'];
$thumb = $viewData['thumb'];
$showRating = $viewData['showRating'];
$product = $viewData['product'];
$rating  = $related->rating;
$ratingModel = VmModel::getModel('ratings'); 

// Get New Products
$db     = JFactory::getDBO();
$query  = "SELECT virtuemart_product_id FROM #__virtuemart_products ORDER BY virtuemart_product_id DESC LIMIT 0, 10";
$db->setQuery($query);
$newIds = $db->loadColumn();
?>
<?php
if($customfield->wPrice){
	$currency = calculationHelper::getInstance()->_currencyDisplay;
	
	$basePriceWithTax = $currency->createPriceDiv('basePriceWithTax', '', $related->prices);
	$salesPrice = $currency->createPriceDiv('salesPrice', '', $related->prices);
	$discountAmount = $currency->createPriceDiv('discountAmount', '', $related->prices);
	
	$isSaleLabel = (!empty($related->prices['discountAmount'])) ? 1 : 0;
	
	$pid = $related->virtuemart_product_id;
	$isNewLabel = in_array($pid, $newIds);
	
}
?>
<div class="vm-product">
	<div class="item">
		<!-- Check Product Label -->
		<?php if($isSaleLabel == 1 && $isNewLabel==NULL) : ?>
		<div class="label-pro label-pro-sale"><?php echo JTEXT::_('VINA_VIRTUEMART_SALE'); ?></div>
		<?php endif; ?>
		<?php if($isNewLabel && $isSaleLabel == 1) : ?>
		<div class="label-pro label-pro-hot"><?php echo JTEXT::_('VINA_VIRTUEMART_HOT'); ?></div>
		<?php endif; ?>
		<?php if($isNewLabel && $isSaleLabel == 0) : ?>
		<div class="label-pro label-pro-new"><?php echo JTEXT::_('VINA_VIRTUEMART_NEW'); ?></div>
		<?php endif; ?>
		<div class="image-block">
			<?php
			//juri::root() For whatever reason, we used this here, maybe it was for the mails
			echo JHtml::link (JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $related->virtuemart_product_id . '&virtuemart_category_id=' . $related->virtuemart_category_id), $thumb, array('title' => $related->product_name,'target'=>'_blank'));
			?>
			<!-- Check Product Label -->
			<?php if($isSaleLabel == 1) : ?>
			<div class="label-pro label-percentage"><?php echo $discountAmount; ?></div>
			<?php endif; ?>
		</div>
		<div class="box-des">
			<div class="text-block">
				<h2 class="product-name"><a href="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $related->virtuemart_product_id . '&virtuemart_category_id=' . $related->virtuemart_category_id); ?>"><?php echo $related->product_name ?></a></h2>
				<div class="product-rating">
					<?php
					$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
					
					$reviews = $ratingModel->getReviewsByProduct($related->virtuemart_product_id);
					if(empty($rating)) { ?>						
						<div class="ratingbox dummy" title="<?php echo vmText::_('COM_VIRTUEMART_UNRATED'); ?>" >
						</div>
					<?php } else {						
						$ratingwidth = $rating * 14; ?>
						<div title=" <?php echo (vmText::_("COM_VIRTUEMART_RATING_TITLE") . round($rating) . '/' . $maxrating) ?>" class="ratingbox" >
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
							<a href="<?php echo $pLink; ?>"><?php echo $count_review;?> <?php echo JText::_('VINA_REVIEW'); ?></a>
						</span>
					<?php } ?>
				</div>
				<div class="price-box">
					<!-- Product Price -->
					<?php echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$related,'currency'=>$currency)); ?>
				</div>
			</div>
		</div>
	</div>
</div>