<?php
/*
# ------------------------------------------------------------------------
# Vina Product Ticker for VirtueMart for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum: http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();
$doc->addScript('modules/' . $module->module . '/assets/js/jquery.easing.min.js', 'text/javascript');
$doc->addScript('modules/' . $module->module . '/assets/js/jquery.easy-ticker.js', 'text/javascript');
$doc->addStyleSheet('modules/' . $module->module . '/assets/css/style.css');

$timthumb = 'modules/'.$module->module.'/libs/timthumb.php?a=c&amp;q=99&amp;z=0&amp;w='.$imageWidth.'&amp;h='.$imageHeight;
$timthumb = JURI::base() . $timthumb;

$stylebgImage = ($bgImage != '') ? "background: url({$bgImage}) top center no-repeat;" : '';
$stylebgImage .= ($isBgColor) ? "background-color: {$bgColor};" : '';

$style = '#vina-ticker-virtuemart' . $module->id . '{'
		. 'width:' . $moduleWidth . ';'
		. 'padding' . $modulePadding . ';'
		. $stylebgImage
	. '}'
	.'#vina-ticker-virtuemart' . $module->id. ' .vina-item {'
		. 'padding: ' . $itemPadding . ';'
		. 'color: ' . $itemTextColor . ';'
		. 'border-bottom: solid 1px ' . $bgColor . ';'
		. ($isItemBgColor) ? 'background-color: ' . $itemBgColor : ''
	. '}'
	.'#vina-ticker-virtuemart' . $module->id . ' .vina-item a {'
		.'color: ' . $itemLinkColor . ';'
	. '}'
	.'#vina-ticker-virtuemart' . $module->id . ' .header-block {'
		. 'color: ' . $headerColor . ';'
		. 'margin-bottom: ' . $modulePadding . ';'
	. '}';
$doc->addStyleDeclaration($style);
?>
<?php $ratingModel = VmModel::getModel('ratings'); ?>
<div class="vina-ticker-virtuemart-wrapper">

	<!-- HTML Block -->
	<div id="vina-ticker-virtuemart<?php echo $module->id; ?>" class="vina-ticker-virtuemart">
		<!-- Header Buttons Block -->
		<?php if($headerBlock) : ?>
		<div class="header-block">
			<div class="row-fluid">
				<?php if(!empty($headerText)) : ?>
				<div class="span<?php echo ($controlButtons) ? 8 : 12; ?>">
					<h3><?php echo $headerText; ?></h3>
				</div>
				<?php endif; ?>
				
				<?php if($controlButtons) : ?>
				<div class="span<?php echo empty($headerText) ? 12 : 4; ?>">
					<div class="control-block pull-right">
						<span class="up">UP</span>
						<span class="toggle">TOGGLE</span>
						<span class="down">DOWN</span>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<!-- Items Block -->	
		<div class="vina-items-wrapper">
			<div class="vina-items">
				<?php 
					foreach($products as $key => $product) :
						$image  = $product->images[0];
						$pImage = (!empty($image)) ? JURI::base() . $image->file_url : '';
						$pImage = (!empty($pImage) && $resizeImage) ? $timthumb . '&amp;src=' . $pImage : $pImage;
						$pLink  = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id);
						$pName  = $product->product_name;
						$rating = shopFunctionsF::renderVmSubLayout('rating', array('showRating' => $productRating, 'product' => $product));
						$sDesc  = $product->product_s_desc;
						$pDesc  = (!empty($sDesc)) ? shopFunctionsF::limitStringByWord($sDesc, 60, ' ...') : '';
						$detail = JHTML::link($pLink, vmText::_('COM_VIRTUEMART_PRODUCT_DETAILS'), array('title' => $pName, 'class' => 'product-details'));
						$stock  = $productModel->getStockIndicator($product);
						$sLevel = $stock->stock_level;
						$sTip   = $stock->stock_tip;
						$handle = shopFunctionsF::renderVmSubLayout('stockhandle', array('product' => $product));
						
						// Show Label Sale Or New
						$basePriceWithTax = $currency->createPriceDiv('basePrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
						$salesPrice = $currency->createPriceDiv('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
						// Show Label Sale Or New
						$isSaleLabel = (!empty($product->prices['discountAmount'])) ? 1 : 0;
				?>
				<div class="item">
					<!-- Image Block -->
					<?php if($productImage && !empty($pImage)) : ?>
					<div class="image-block">
						<a href="<?php echo $pLink; ?>" title="<?php echo $pName; ?>">
							<img src="<?php echo $pImage; ?>" alt="<?php echo $pName; ?>" title="<?php echo $pName; ?>" />
						</a>
					</div>
					<?php endif; ?>
					
					<!-- Text Block -->
					<div class="box-des">
						<!-- Product Name -->
						<?php if($productName) : ?>
							<h2 class="product-name"><a href="<?php echo $pLink; ?>" title="<?php echo $pName; ?>"><?php echo $pName; ?></a></h2>
						<?php endif; ?>
						<!-- Product Description -->
						<?php if($productDesc && !empty($pDesc)) : ?>
							<div class="product-description"><?php echo $pDesc; ?></div>
						<?php endif; ?>
						<!-- Product Rating -->
						<?php if($productRating) : ?>
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
						<!-- Add to Cart Button & View Details Button -->
						<?php if($addtocart || $viewDetails) : ?>
						<div class="actions actions-bottom">
							<?php if($addtocart) : ?>
								<?php modVinaTickerVirtueMartHelper::addtocart($product); ?>
							<?php endif; ?>
							<?php if($viewDetails) : ?>
							<ul class="add-to-links">
								<li><!-- Add Wishlist Button -->
										<?php if(is_dir(JPATH_BASE . "/components/com_wishlist/")) :
										 $app = JFactory::getApplication();
										?>
										<?php require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); ?>
										<?php endif; ?></li>
								<li class="jutooltip" title="<?php echo JText::_( 'VINA_VIEW_DETAIL' ); ?>"><?php echo $detail; ?></li>
							</ul>
							<?php endif; ?>
						</div>
						<?php endif; ?>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<!-- Javascript Block -->
	<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#vina-ticker-virtuemart<?php echo $module->id; ?> .vina-items-wrapper').easyTicker({
			direction: 		'<?php echo $direction?>',
			easing: 		'<?php echo $easing?>',
			speed: 			'<?php echo $speed?>',
			interval: 		<?php echo $interval?>,
			height: 		'<?php echo $moduleHeight; ?>',
			visible: 		<?php echo $visible?>,
			mousePause: 	<?php echo $mousePause?>,
			
			<?php if($controlButtons) : ?>
			controls: {
				up: '#vina-ticker-virtuemart<?php echo $module->id; ?> .up',
				down: '#vina-ticker-virtuemart<?php echo $module->id; ?> .down',
				toggle: '#vina-ticker-virtuemart<?php echo $module->id; ?> .toggle',
				playText: 'Play',
				stopText: 'Stop'
			},
			<?php endif; ?>
		});
	});
	</script>
</div>