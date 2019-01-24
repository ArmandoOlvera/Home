<?php
/*
# ------------------------------------------------------------------------
# Vina Product Carousel for VirtueMart for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum: http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/

/* no direct access */
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.modal');
$doc = JFactory::getDocument();
//$doc->addScript('modules/' . $module->module . '/assets/js/owl.carousel.js', 'text/javascript');
//$doc->addStyleSheet('modules/' . $module->module . '/assets/css/owl.carousel.css');
//$doc->addStyleSheet('modules/' . $module->module . '/assets/css/owl.theme.css');
$doc->addStyleSheet('modules/' . $module->module . '/assets/css/custom.css');

/* Timthumb Class Path */
$timthumb = 'modules/'.$module->module.'/libs/timthumb.php?a=c&amp;q=99&amp;z=0&amp;w='.$imageWidth.'&amp;h='.$imageHeight;
$timthumb = JURI::base() . $timthumb;

//Include Helix3 plugin
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';

if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $helix3 = helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}

$vm_product_labels 		=  $helix3->getParam('vm_product_labels', 1);
$newLabel_date 			=  $helix3->getParam('vm_product_label_newdate', 1);
$newLabel_limit 		=  $helix3->getParam('vm_product_label_newlimit', 1);
$vm_product_quickview 	=  $helix3->getParam('vm_product_quickview', 1);
$vm_product_desc_limit 	=  $helix3->getParam('vm_product_desc_limit', 60);

// Get New Products
$db    = JFactory::getDBO();
$query = "SELECT virtuemart_product_id FROM #__virtuemart_products WHERE DATE(product_available_date) >= DATE_SUB(CURDATE(), INTERVAL ". $newLabel_date." DAY) ORDER BY product_available_date DESC LIMIT 0, " . $newLabel_limit;
$db->setQuery($query);
$newIds = $db->loadColumn();

// Add styles
$stylebgImage = ($bgImage != '') ? "background: url({$bgImage}) repeat scroll 0 0;" : '';
$stylebgImage .= ($isBgColor) ? "background-color: {$bgColor};" : '';
$styleisItemBgColor = ($isItemBgColor) ? "background-color: {$itemBgColor};" : "";

$style = '#vina-carousel-virtuemart'.$module->id .'{'
	. 'overflow: hidden;'
	. 'width:'.$moduleWidth.';'
	. 'height:'.$moduleHeight.';'
	. 'margin:'.$moduleMargin.';'
	. 'padding:'.$modulePadding.';'
	. $stylebgImage
	. '}'
	. '#vina-carousel-virtuemart'.$module->id .' .item{'
	. $styleisItemBgColor
	. 'margin:' . $itemMargin . ';'
	. 'padding:' .$itemPadding .';'
	. '}'; 
$doc->addStyleDeclaration($style);
$ratingModel = VmModel::getModel('ratings'); 
$ItemidStr = '';
$Itemid = shopFunctionsF::getLastVisitedItemId();
if(!empty($Itemid)){
	$ItemidStr = '&amp;Itemid='.$Itemid;
}
?>
<div id="vina-carousel-virtuemart<?php echo $module->id; ?>" class="vina-carousel-virtuemart owl-carousel">
	<?php
		$totalRow  = $itemInCol;
		$totalLoop = ceil(count($products)/$totalRow);
		$keyLoop   = 0;
		for($i = 0; $i < $totalLoop; $i ++) :
	?>
	
	<div class="item">
		<?php 
		for($m = 0; $m < $totalRow; $m ++) : 
			$product = $products[$keyLoop];
			$keyLoop = $keyLoop + 1;
			if(!empty($product)) :
		?>
		<?php
			$image  = $product->images[0];
			$image_second = $product->images[1];
			$pImage = (!empty($image)) ? JURI::base() . $image->file_url : '';
			$pImage = (!empty($pImage) && $resizeImage) ? $timthumb . '&amp;src=' . $pImage : $pImage;
			$pImage_second = (!empty($image_second)) ? JURI::base() . $image_second->file_url : $pImage;
			$pImage_second = (!empty($pImage_second) && $resizeImage) ? $timthumb . '&amp;src=' . $pImage_second : $pImage_second;
			$pLink  = JRoute::_('index.php?option=com_virtuemart&amp;view=productdetails&amp;virtuemart_product_id=' . $product->virtuemart_product_id . '&amp;virtuemart_category_id=' . $product->virtuemart_category_id);
			$pName  = $product->product_name;
			$rating = shopFunctionsF::renderVmSubLayout('rating', array('showRating' => $productRating, 'product' => $product));
			$sDesc  = $product->product_s_desc;
			$pDesc  = (!empty($sDesc)) ? shopFunctionsF::limitStringByWord($sDesc, $vm_product_desc_limit, ' ...') : '';
			$detail = JHTML::link($pLink, JText::_('VINA_VIEW_DETAIL'), array('title' => $pName, 'class' => 'product-details'));
			$stock  = $productModel->getStockIndicator($product);
			$sLevel = $stock->stock_level;
			$sTip   = $stock->stock_tip;
			$handle = shopFunctionsF::renderVmSubLayout('stockhandle', array('product' => $product));
			
			// Show Label Sale Or New
			$basePriceWithTax = $currency->createPriceDiv('basePrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
			$salesPrice = $currency->createPriceDiv('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
			$discountAmount = $currency->createPriceDiv('discountAmount', '', $product->prices);
			
			$isSaleLabel = (!empty($product->prices['discountAmount'])) ? 1 : 0;
			
			$pid = $product->virtuemart_product_id;
			$isNewLabel = in_array($pid, $newIds);
	?>
		<div class="item-i round-corners">
			<!-- Check Product Label -->
			<?php if($vm_product_labels) {?>
				<?php if($isSaleLabel == 1 && $isNewLabel==NULL) : ?>
				<div class="label-pro label-pro-sale"><?php echo JTEXT::_('VINA_VIRTUEMART_SALE'); ?></div>
				<?php endif; ?>
				<?php if($isNewLabel && $isSaleLabel == 1) : ?>
				<div class="label-pro label-pro-hot"><?php echo JTEXT::_('VINA_VIRTUEMART_HOT'); ?></div>
				<?php endif; ?>
				<?php if($isNewLabel && $isSaleLabel == 0) : ?>
				<div class="label-pro label-pro-new"><?php echo JTEXT::_('VINA_VIRTUEMART_NEW'); ?></div>
				<?php endif; ?>
			<?php } ?>
			<!-- Image Block -->
			<?php if($productImage && !empty($pImage)) : ?>
			<div class="image-block">
				<a class="product-image" href="<?php echo $pLink; ?>" title="<?php echo $pName; ?>">
					<span class="pro-image first-image">
						<img class="browseProductImage" src="<?php echo $pImage; ?>" alt="<?php echo $pName; ?>" title="<?php echo $pName; ?>" />
					</span>
				</a>
				<!-- Check Product Label -->
				<?php if($isSaleLabel == 1) : ?>
				<div class="label-pro label-percentage"><?php echo $discountAmount; ?></div>
				<?php endif; ?>
				<!-- Add to Cart Button & View Details Button -->
				<?php if($addtocart || $viewDetails) : ?>
				<div class="actions">
					<?php if($viewDetails) : ?>
					<ul class="add-to-links">
						<li class="pro-detail">
							<a class="jutooltip" title="<?php echo JText::_( 'VINA_VIEW_DETAIL' ); ?>" href="<?php echo $product->link; ?>"><i class="icon-eye-open"></i></a>
						</li>
						<!-- Add Wishlist Button -->
							<?php if(is_dir(JPATH_BASE . "/components/com_wishlist/")) :?>
							<li class="pro-wishlist">
								<?php $app = JFactory::getApplication();?>
								<?php require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); ?>
							</li>
							<?php endif; ?>
					</ul>
					<?php endif; ?>
					<?php if($addtocart) : ?>
						<?php echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product)); ?>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<div class="box-des">
				<!-- Text Block -->
				<div class="text-block">
					<!-- Product Name -->
					<?php if($productName) : ?>
						<h2 class="product-name"><a href="<?php echo $pLink; ?>" title="<?php echo $pName; ?>"><?php echo $pName; ?></a></h2>
					<?php endif; ?>
					
					<!-- Product Description -->
					<?php if($productDesc && !empty($pDesc)) : ?>
						<p class="des"><?php echo $pDesc; ?></p>
					<?php endif; ?>
					
					<!-- Product Stock -->
					<?php if($productStock) : ?>
					<div class="product-stock">
						<span class="vmicon vm2-<?php echo $sLevel; ?>" title="<?php echo $sTip; ?>"></span>
						<?php echo $handle; ?>
					</div>
					<?php endif; ?>
					
					<!-- Product Price -->
					<?php if($productPrice) : ?>
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
					<?php endif; ?>
					
					<!-- Product Rating -->
					<?php if ($productRating) { ?>		
					<div class="product-rating">
						<?php
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
								<a href="<?php echo $pLink; ?>"><?php echo $count_review;?> <?php echo JText::_('VINA_REVIEW'); ?></a>
							</span>
						<?php } ?>		
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	<?php endif; endfor; ?>
	</div>
	<?php endfor; ?>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$("#vina-carousel-virtuemart<?php echo $module->id; ?>").owlCarousel({
		items : 			<?php echo $itemsVisible; ?>,
        itemsDesktop : 		<?php echo $itemsDesktop; ?>,
        itemsDesktopSmall : <?php echo $itemsDesktopSmall; ?>,
        itemsTablet : 		<?php echo $itemsTablet; ?>,
        itemsTabletSmall : 	<?php echo $itemsTabletSmall; ?>,
        itemsMobile : 		<?php echo $itemsMobile; ?>,
        singleItem : 		<?php echo ($singleItem) ? 'true' : 'false'; ?>,
        itemsScaleUp : 		<?php echo ($itemsScaleUp) ? 'true' : 'false'; ?>,

        slideSpeed : 		<?php echo $slideSpeed; ?>,
        paginationSpeed : 	<?php echo $paginationSpeed; ?>,
        rewindSpeed : 		<?php echo $rewindSpeed; ?>,

        autoPlay : 		<?php echo $autoPlay; ?>,
        stopOnHover : 	<?php echo ($stopOnHover) ? 'true' : 'false'; ?>,

        navigation : 	<?php echo ($navigation) ? 'true' : 'false'; ?>,
        rewindNav : 	<?php echo ($rewindNav) ? 'true' : 'false'; ?>,
        scrollPerPage : <?php echo ($scrollPerPage) ? 'true' : 'false'; ?>,

        pagination : 		<?php echo ($pagination) ? 'true' : 'false'; ?>,
        paginationNumbers : <?php echo ($paginationNumbers) ? 'true' : 'false'; ?>,

        responsive : 	<?php echo ($responsive) ? 'true' : 'false'; ?>,
        autoHeight : 	<?php echo ($autoHeight) ? 'true' : 'false'; ?>,
        mouseDrag : 	<?php echo 'false'; //echo ($mouseDrag) ? 'true' : 'false'; ?>,
        touchDrag : 	<?php echo 'false'; //echo ($touchDrag) ? 'true' : 'false'; ?>,
		leftOffSet: 	<?php echo $leftOffSet; ?>,
	});
}); 
</script>