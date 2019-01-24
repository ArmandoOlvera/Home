<?php
/**
 * sublayout products
 *
 * @package	VirtueMart
 * @author Max Milbers
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL2, see LICENSE.php
 * @version $Id: cart.php 7682 2014-02-26 17:07:20Z Milbo $
 */
defined('_JEXEC') or die('Restricted access');
$products_per_row = $viewData['products_per_row'];
$currency = $viewData['currency'];
$showRating = $viewData['showRating'];
$verticalseparator = " vertical-separator";
echo shopFunctionsF::renderVmSubLayout('askrecomjs');

$ItemidStr = '';
$Itemid = shopFunctionsF::getLastVisitedItemId();
if(!empty($Itemid)){
	$ItemidStr = '&Itemid='.$Itemid;
}

// Get New Products
$db     = JFactory::getDBO();
$query  = "SELECT virtuemart_product_id FROM #__virtuemart_products ORDER BY virtuemart_product_id DESC LIMIT 0, 10";
$db->setQuery($query);
$newIds = $db->loadColumn();

$productModel = VmModel::getModel('product');
foreach ($viewData['products'] as $type => $products ) {
	$productModel->addImages($products,2);
	$rowsHeight = shopFunctionsF::calculateProductRowsHeights($products,$currency,$products_per_row);
	if(!empty($type) and count($products)>0){
		$productTitle = vmText::_('COM_VIRTUEMART_'.strtoupper($type).'_PRODUCT'); ?>
		<div class="<?php echo $type ?>-view">
			<h4><?php echo $productTitle ?></h4>
			<?php // Start the Output
    }
	// Calculating Products Per Row
	$cellwidth = ' width'.floor ( 100 / $products_per_row );
	$BrowseTotalProducts = count($products);
	$col = 1;
	$nb = 1;
	$row = 1;
	foreach ( $products as $product ) { 
		$image  = $product->images[0];
		$image_second = $product->images[1];
		$pImage = (!empty($image)) ? JURI::base() . $image->file_url : '';
		$pImage = (!empty($pImage) && $resizeImage) ? $timthumb . '&amp;src=' . $pImage : $pImage;
		$pImage_second = (!empty($image_second)) ? JURI::base() . $image_second->file_url : $pImage;
		$pImage_second = (!empty($pImage_second) && $resizeImage) ? $timthumb . '&amp;src=' . $pImage_second : $pImage_second;
		$pLink  = JRoute::_('index.php?option=com_virtuemart&amp;view=productdetails&amp;virtuemart_product_id=' . $product->virtuemart_product_id . '&amp;virtuemart_category_id=' . $product->virtuemart_category_id);
		// Show Label Sale Or New
		$basePriceWithTax = $currency->createPriceDiv('basePrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
		$salesPrice = $currency->createPriceDiv('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
		$discountAmount = $currency->createPriceDiv('discountAmount', '', $product->prices);
		
		$isSaleLabel = (!empty($product->prices['discountAmount'])) ? 1 : 0;
		
		$pid = $product->virtuemart_product_id;
		$isNewLabel = in_array($pid, $newIds);
	?>
		<!-- Show the horizontal seperator -->
		<!-- <?php if ($col == 1 && $nb > $products_per_row) { ?>
			<div class="horizontal-separator"></div>
		<?php } ?> -->
		
		<!-- this is an indicator wether a row needs to be opened or not -->
		<?php if ($col == 1) { ?>
			<div class="row vm-product-row"> 
		<?php } ?>
		
		<!-- Show the vertical seperator -->
		<?php if ($nb == $products_per_row or $nb % $products_per_row == 0) {
			$show_vertical_separator = ' ';
		} else {
			$show_vertical_separator = $verticalseparator;
		} ?>
		
		<!-- Show Products -->
		<div class="vm-product vm-col<?php echo ' col-md-' . 12/$products_per_row . $show_vertical_separator ?> <?php echo ' col-sm-' . 12/$products_per_row . $show_vertical_separator ?>">
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
					<!--<a title="<?php //echo $product->product_name ?>" href="<?php //echo $product->link.$ItemidStr; ?>">
						<?php //echo $product->images[0]->displayMediaThumb('class="browseProductImage"', false); ?>						
					</a> -->
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
					<div class="actions">
						<ul class="add-to-links">
							<li class="pro-detail">
								<a class="jutooltip" title="<?php echo JText::_( 'VINA_VIEW_DETAIL' ); ?>" href="<?php echo $product->link; ?>"><i class="fa fa-eye"></i></a>
							</li>
							
							<!-- Add Wishlist Button -->
							<?php if(is_dir(JPATH_BASE . "/components/com_wishlist/")) :?>
							<li class="pro-wishlist">
								<?php $app = JFactory::getApplication();?>
								<?php require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); ?>
							</li>
							<?php endif; ?>
							
						</ul>
						<?php echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product,'rowHeights'=>$rowsHeight[$row])); ?>
					</div>
				</div>
				<div class="box-des">
					<div class="text-block">
						<!-- Product Name -->
						<h2 class="product-name"><?php echo JHtml::link ($product->link.$ItemidStr, $product->product_name); ?></h2>
						<div class="product-rating">
							<?php 
								echo shopFunctionsF::renderVmSubLayout('rating',array('showRating'=>$showRating, 'product'=>$product));
								if ( VmConfig::get ('display_stock', 1)) { ?>
									<span class="vmicon vm2-<?php echo $product->stock->stock_level ?>" title="<?php echo $product->stock->stock_tip ?>"></span>
								<?php }
								echo shopFunctionsF::renderVmSubLayout('stockhandle',array('product'=>$product));
							?>
							<?php 
								$ratingModel = VmModel::getModel('ratings');
								$reviews = $ratingModel->getReviewsByProduct($product->virtuemart_product_id);
								if(!empty($reviews)) {					
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
						
						<!-- Product Price -->
						<div class="price-box">
							<?php echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$product,'currency'=>$currency)); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php
    $nb ++;

      // Do we need to close the current row now?
      if ($col == $products_per_row || $nb>$BrowseTotalProducts) { ?>
    <div class="clear"></div>
  </div>
      <?php
      	$col = 1;
		$row++;
    } else {
      $col ++;
    }
  }

      if(!empty($type)and count($products)>0){
        // Do we need a final closing row tag?
        //if ($col != 1) {
      ?>
    <div class="clear"></div>
  </div>
    <?php
    // }
    }
  }
