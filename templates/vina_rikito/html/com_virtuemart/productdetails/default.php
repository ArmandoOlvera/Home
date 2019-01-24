<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Eugen Stranz, Max Galt
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default.php 8610 2014-12-02 18:53:19Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//Include Helix3 plugin
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';

if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $helix3 = helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}

/* Parameter */
$vm_image_zoom = $helix3->getParam('vm_image_zoom', 1);
$vm_social_product = $helix3->getParam('vm_social_product', 1);
$vm_social_product_code = $helix3->getParam('vm_social_product_code');

/* Let's see if we found the product */
if (empty($this->product)) {
	echo vmText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND');
	echo '<br /><br />  ' . $this->continue_link_html;
	return;
}

echo shopFunctionsF::renderVmSubLayout('askrecomjs',array('product'=>$this->product));

vmJsApi::jDynUpdate();
vmJsApi::addJScript('updDynamicListeners',"
jQuery(document).ready(function() { // GALT: Start listening for dynamic content update.
	// If template is aware of dynamic update and provided a variable let's
	// set-up the event listeners.
	if (Virtuemart.container)
		Virtuemart.updateDynamicUpdateListeners();

}); ");

if(vRequest::getInt('print',false)){ ?>
<body onload="javascript:print();">
<?php } ?>

<div class="productdetails-view productdetails">
    <?php
    // Product Navigation
    if (VmConfig::get('product_navigation', 1)) {
	?>
        <div class="product-neighbours">
	    <?php
	    if (!empty($this->product->neighbours ['previous'][0])) {
		$prev_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['previous'][0] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id, FALSE);
		echo JHtml::_('link', $prev_link, $this->product->neighbours ['previous'][0]
			['product_name'], array('rel'=>'prev', 'class' => 'previous-page','data-dynamic-update' => '1'));
	    }
	    if (!empty($this->product->neighbours ['next'][0])) {
		$next_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['next'][0] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id, FALSE);
		echo JHtml::_('link', $next_link, $this->product->neighbours ['next'][0] ['product_name'], array('rel'=>'next','class' => 'next-page','data-dynamic-update' => '1'));
	    }
	    ?>
    	<div class="clear"></div>
        </div>
    <?php } // Product Navigation END
    ?>
	<?php // Back To Category Button ?>				
	<?php
	if ($this->product->virtuemart_category_id) {
		$catURL =  JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$this->product->virtuemart_category_id, FALSE);
		$categoryName = $this->product->category_name ;
	} else {
		$catURL =  JRoute::_('index.php?option=com_virtuemart');
		$categoryName = vmText::_('COM_VIRTUEMART_SHOP_HOME') ;
	}
	?>	
	<div class="back-to-category">
		<?php echo JText::_('VINA_LISTED_CATEGORY') ?>: <a href="<?php echo $catURL ?>" class="product-details" title="<?php echo $categoryName ?>"><?php echo $categoryName; ?></a>
	</div>
    <div class="row vm-product-container">
		<div class="col-md-5 product-img-box">
			<?php echo $this->loadTemplate('images'); ?>
			
			<?php //Additional Images ?>
			<?php
			$count_images = count ($this->product->images);
			if ($count_images > 1) {
				echo $this->loadTemplate('images_additional');
			}
			// event onContentBeforeDisplay
			echo $this->product->event->beforeDisplayContent;
			?>			
		</div>

		<div class="col-md-7 product-shop">
			<div class="product-shop-inner">	

				<?php // Product Title   ?>
				<div class="product-name"><h1><?php echo $this->product->product_name ?></h1></div>
				
				<?php // afterDisplayTitle Event ?>
				<?php echo $this->product->event->afterDisplayTitle ?>
				
				<?php // Show Rating ?>
				<div class="product-rating">
					<?php 
						echo shopFunctionsF::renderVmSubLayout('rating',array('showRating'=>$this->showRating,'product'=>$this->product)); 
					?>	
					<?php 
						$ratingModel = VmModel::getModel('ratings');
						$reviews = $ratingModel->getReviewsByProduct($this->product->virtuemart_product_id);
						if(!empty($reviews)) {					
						$count_review = 0;
						$ItemidStr = '';
						$Itemid = shopFunctionsF::getLastVisitedItemId();
						foreach($reviews as $k=>$review) {
							$count_review ++;
						}										
					?>
						<span class="amount">
							<?php echo $count_review;?> <?php echo JText::_('VINA_REVIEW'); ?> | <a href="#vina-tab-block"><?php echo JText::_('VINA_ADD_YOUR_REVIEW'); ?></a>
						</span>
					<?php } ?>
				</div>
				
				<?php // Product Edit Link ?>
				<?php echo $this->edit_link; ?>				
				<?php //Price ?>
				<div class="price-box">
					<?php 
						echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$this->product,'currency'=>$this->currency));
					?>
					<p class="sku"><?php echo vmText::_('COM_VIRTUEMART_SKU'); ?>: <span class="color"><?php echo $this->product->product_sku; ?></span></p>
				</div>
				<?php // Product Short Description ?>
				<?php
				if (!empty($this->product->product_s_desc)) {
				?>
					<div class="product-short-description">
					<?php
					/** @todo Test if content plugins modify the product description */
					echo nl2br($this->product->product_s_desc);
					?>
					</div>
				<?php
				} // Product Short Description END
				?>
				<p class="in-stock">
				<?php echo vmText::_('COM_VIRTUEMART_AVAILABILITY'); ?>: 
				<?php if($this->product->product_in_stock > 0) {
					echo "<span>".vmText::_('COM_VIRTUEMART_PRODUCT_FORM_IN_STOCK')."</span>";
				}
				else {
					echo "<span>".vmText::_('COM_VIRTUEMART_STOCK_LEVEL_OUT')."</span>";
				}?>	
				</p>
				<?php
				echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'ontop'));
				?>
				
				<?php // Manufacturer of the Product ?>
				<?php if (VmConfig::get('show_manufacturers', 1) && !empty($this->product->virtuemart_manufacturer_id)) { ?>					
					<?php echo $this->loadTemplate('manufacturer'); ?>					
				<?php } ?>					
				
				<?php // Price Block ?>
				<div class="spacer-buy-area">
					<?php 
						// TODO in Multi-Vendor not needed at the moment and just would lead to confusion
						/* $link = JRoute::_('index2.php?option=com_virtuemart&view=virtuemart&task=vendorinfo&virtuemart_vendor_id='.$this->product->virtuemart_vendor_id);
						  $text = vmText::_('COM_VIRTUEMART_VENDOR_FORM_INFO_LBL');
						  echo '<span class="bold">'. vmText::_('COM_VIRTUEMART_PRODUCT_DETAILS_VENDOR_LBL'). '</span>'; ?><a class="modal" href="<?php echo $link ?>"><?php echo $text ?></a><br />
						*/
					?>
					<?php			
					if (is_array($this->productDisplayShipments)) {
						foreach ($this->productDisplayShipments as $productDisplayShipment) {
						echo $productDisplayShipment;
						}
					}
					if (is_array($this->productDisplayPayments)) {
						foreach ($this->productDisplayPayments as $productDisplayPayment) {
						echo $productDisplayPayment;
						}
					}
					//In case you are not happy using everywhere the same price display fromat, just create your own layout
					//in override /html/fields and use as first parameter the name of your file
					echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$this->product));

					echo shopFunctionsF::renderVmSubLayout('stockhandle',array('product'=>$this->product));

					?>
				</div>
				<?php // PDF - Print - Email Icon ?>
				<div class="icons">
					<ul class="list-option">
						<li>
							<?php if(is_dir(JPATH_BASE . "/components/com_wishlist/")) :
							 $app = JFactory::getApplication();
							?>
							<?php require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); ?>
							<?php endif; ?>
						</li>
						<li>
							<div class="add-compare"></div>
						</li>
					
						<?php
						if (VmConfig::get('show_emailfriend') || VmConfig::get('show_printicon') || VmConfig::get('pdf_icon')) {
						?>
							<?php
							$link = 'index.php?tmpl=component&amp;option=com_virtuemart&amp;view=productdetails&amp;virtuemart_product_id=' . $this->product->virtuemart_product_id;

							//echo $this->linkIcon($link . '&amp;format=pdf', 'COM_VIRTUEMART_PDF', 'pdf_button', 'pdf_icon', false);					
							//echo $this->linkIcon($link . '&amp;print=1', 'COM_VIRTUEMART_PRINT', 'printButton', 'show_printicon',false,true,false,'class="printModal"');					
							$MailLink = 'index.php?option=com_virtuemart&amp;view=productdetails&amp;task=recommend&amp;virtuemart_product_id=' . $this->product->virtuemart_product_id . '&amp;virtuemart_category_id=' . $this->product->virtuemart_category_id . '&amp;tmpl=component';
							//echo $this->linkIcon($MailLink, 'COM_VIRTUEMART_EMAIL', 'emailButton', 'show_emailfriend', false,true,false,'class="recommened-to-friend"');
							?>
							<li><a class="vina-icons print" href="<?php echo $link . '&amp;print=1' ?>" data-original-title="<?php echo JText::_('COM_VIRTUEMART_PRINT'); ?>">
								<span><?php echo vmText::_('COM_VIRTUEMART_PRINT'); ?></span>
							</a>
							</li>
							<li><a class="vina-icons email recommened-to-friend" href="<?php echo $MailLink; ?>" data-original-title="<?php echo JText::_('COM_VIRTUEMART_EMAIL'); ?>">
								<span><?php echo vmText::_('COM_VIRTUEMART_EMAIL'); ?></span>
							</a></li>
							<?php
							// Ask a question about this product
							if (VmConfig::get('ask_question', 0) == 1) {
								$askquestion_url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&task=askquestion&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component', FALSE);
							?>						
								<li><a class="vina-icons ask-a-question icon-send" href="<?php echo $askquestion_url ?>" data-original-title="<?php echo JText::_('VINA_ADD_YOUR_QUESTION'); ?>" ><span><?php echo JText::_('VINA_ADD_YOUR_QUESTION'); ?></span></a></li>				
							<?php } ?>
							<div class="clear"></div>
						<?php } ?>
					</ul>
				</div>
				<!-- Social sharing -->
				<?php if($vm_social_product) : ?>
				<div class="itemSocialSharing">
				<?php if ($vm_social_product_code):
							echo $vm_social_product_code;
						else :?>
							<!-- fb-like -->
							<div class="fb-like" style="width: 150px; text-align: left; top: -5px;" data-width="110" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true"></div>
							<script>
							(function(d, s, id) {
								var js, fjs = d.getElementsByTagName(s)[0];
								if (d.getElementById(id)) return;
								js = d.createElement(s); js.id = id;
								js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=276217465798532";
								fjs.parentNode.insertBefore(js, fjs);
							}(document, 'script', 'facebook-jssdk'));
							</script>
							
							<!-- twitter -->
							<div class="g-plusone" data-size="medium"></div>
							<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
							
							<!-- twitter -->
							<a class="twitter-share-button" data-via="twitterapi" data-lang="en">Tweet</a>	
							<script>
								!function(d,s,id){
									var js,fjs=d.getElementsByTagName(s)[0];
									if(!d.getElementById(id)){
										js=d.createElement(s);
										js.id=id;js.src="https://platform.twitter.com/widgets.js";
										fjs.parentNode.insertBefore(js,fjs);
									}
								}
								(document,"script","twitter-wjs");
							</script>	
							
							
							<script src="//platform.linkedin.com/in.js" type="text/javascript"></script>
							<script type="IN/Share" data-counter="right"></script>
				<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>					
		</div>
	<div class="clear"></div>	
    </div>
	
	<!-- Tabs Full Description + Review + comment -->
	<div id="vina-tab-block" class="tab-block">
		<ul class="nav nav-pills" id="vinaTab">
			<?php if (!empty($this->product->product_desc)) {?>
			<li class="active">
				<a data-toggle="tab" href="#vina-description"><?php echo JText::_('VINA_JSHOP_FULL_DESCRIPTION'); ?></a>
			</li>
			<?php }?>			
			<li class=""><a data-toggle="tab" href="#vina-reviews"><?php echo JText::_('VINA_ADD_YOUR_REVIEW'); ?></a></li>	
			<li class=""><a data-toggle="tab" href="#vina-infomation"><?php echo JText::_('VINA_ADD_YOUR_INFOMATION'); ?></a></li>			
		</ul>
		<div id="vinaTabContent" class="tab-content">			
			<?php // Product Description
			if (!empty($this->product->product_desc)) { ?>
				<div id="vina-description" class="tab-pane product-description active">
					<?php /** @todo Test if content plugins modify the product description */ ?>
					<?php echo $this->product->product_desc; ?>
				</div>
			<?php } // Product Description END ?>			
			<div id="vina-reviews" class="tab-pane product-review">
				<?php
					echo $this->loadTemplate('reviews');
				?>
			</div>
			<div id="vina-infomation" class="tab-pane product-infomation">
				<ul class="list-info">
					<?php if ($this->product->product_weight){ ?>
						<li><strong><?php echo JText::_("VINA_PRODUCT_WEIGHT"); ?></strong> <?php echo number_format($this->product->product_weight,2); ?> <?php echo $this->product->product_weight_uom; ?></li>
					<?php } ?>
					<?php if ($this->product->product_length){ ?>
						<li><strong><?php echo JText::_("VINA_PRODUCT_LENGTH"); ?></strong> <?php echo number_format($this->product->product_length,2); ?> <?php echo $this->product->product_lwh_uom; ?></li>
					<?php } ?>
					<?php if ($this->product->product_width){ ?>
						<li><strong><?php echo JText::_("VINA_PRODUCT_WIDTH"); ?></strong> <?php echo number_format($this->product->product_width,2); ?></li>
					<?php } ?>
					<?php if ($this->product->product_height){ ?>
						<li><strong><?php echo JText::_("VINA_PRODUCT_HEIGHT"); ?></strong> <?php echo number_format($this->product->product_height,2); ?></li>
					<?php } ?>
					<?php if ($this->product->product_packaging){ ?>
						<li><strong><?php echo JText::_("VINA_PRODUCT_PACKAGING"); ?></strong> <?php echo number_format($this->product->product_packaging,2); ?> <?php echo $this->product->product_unit; ?></li>
					<?php } ?>
					<?php	
					echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'normal'));

					// Product Packaging
					$product_packaging = '';
					if ($this->product->product_box) {
					?>
					<li>
						<strong><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_UNITS_IN_BOX') ?></strong> <?php echo $this->product->product_box; ?>
					</li>
					<?php } // Product Packaging END ?>
				</ul>
			</div>			
		</div>
	</div>

    <?php 
	echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'onbot'));

    echo shopFunctionsF::renderVmSubLayout('customfields_related',array('product'=>$this->product,'position'=>'related_products','class'=> 'product-related-products','customTitle' => true ));

	echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'related_categories','class'=> 'product-related-categories'));

	?>

<?php // onContentAfterDisplay event
echo $this->product->event->afterDisplayContent; ?>

<?php // Show child categories
    if (VmConfig::get('showCategory', 1)) {
		echo $this->loadTemplate('showcategory');
    }?>
	<?php
	echo vmJsApi::writeJS();
	?>

</div>
<?php if($vm_image_zoom) :?>
<script type="text/javascript" src="<?php echo $current_template_path; ?>/js/jquery.elevatezoom.js"></script>
<?php endif; ?>
<script>
	// GALT
	/*
	 * Notice for Template Developers!
	 * Templates must set a Virtuemart.container variable as it takes part in
	 * dynamic content update.
	 * This variable points to a topmost element that holds other content.
	 */
	// If this <script> block goes right after the element itself there is no
	// need in ready() handler, which is much better.
	//jQuery(document).ready(function() {
	Virtuemart.container = jQuery('.productdetails-view');
	Virtuemart.containerSelector = '.productdetails-view';
	//Virtuemart.container = jQuery('.main');
	//Virtuemart.containerSelector = '.main';
	//});	  	
</script>
<script type="text/javascript">var switchTo5x=true;</script>
<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "301fa37a-230d-442a-9167-8ddfd614fd2d", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>







