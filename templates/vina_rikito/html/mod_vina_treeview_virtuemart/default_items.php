<?php
/*
# ------------------------------------------------------------------------
# Module: Vina Treeview for VirtueMart
# ------------------------------------------------------------------------
# Copyright (C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://VinaGecko.com
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

?>
<?php

$class_extra = '';

foreach($categories as $item) :
	$cid   = $item->virtuemart_category_id;
	$cname = $item->category_name;	
	$child = $categoryModel->getChildCategoryList($vendorId, $cid, $fieldSort, $ordering);
	$link  = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $cid);
	
	$parentCat = $categoryModel->getCategoryRecurse($cid,0);
	$count_parentCat = count($parentCat);
	if( ( $i > $max_vmcategory_menu ) && ( $count_parentCat == $count_parentCatRoot ) ) {
		$class_extra = 'class="extra_menu"';
	}
	if( ( $count_parentCat == $count_parentCatRoot ) ) {
		$i++;			
	}
?>
<li <?php echo $class_extra;?>>

	<?php if(count($child)) : ?>
		<a class="vmcat-a <?php echo $item->slug; ?>" href="<?php echo $link; ?>" title="<?php echo $cname; ?>">		
			<span class="catTitle <?php echo (($params->get('moduleStyle') == 'filetree') ? ' folder' : ''); ?>">
				<?php echo $cname; ?>
				<?php if($count) : ?>(<?php echo modVinaTreeViewVMartHelper::countProductsinCategory($cid); ?>)<?php endif; ?>
			</span>
		</a>
		<ul class="sub-menu">
			<?php
				$temp 		= $categories;
				$categories = $child;
				require JModuleHelper::getLayoutPath($module->module, 'default_items');
				$categories = $temp;
				
			?>
		</ul>	
	<?php else: ?>
		<a class="vmcat-a <?php echo $item->slug; ?>" href="<?php echo $link; ?>" title="<?php echo $cname; ?>">
			<span class="catTitle <?php echo (($params->get('moduleStyle') == 'filetree') ? ' file' : ''); ?>">
				<?php echo $cname; ?>
				<?php if($count) : ?>(<?php echo modVinaTreeViewVMartHelper::countProductsinCategory($cid); ?>)<?php endif; ?>
			</span>
		</a>
	<?php endif; ?>
	
</li>
<?php
	endforeach;
?>