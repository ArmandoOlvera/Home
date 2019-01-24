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
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'modules/' . $module->module . '/assets/js/jquery.cookie.js');
$document->addScript(JURI::base() . 'modules/' . $module->module . '/assets/js/jquery.treeview.js');
$document->addStyleSheet(JURI::base() . 'modules/' . $module->module . '/assets/css/jquery.treeview.css');

//Load Helix
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';

if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $helix3 = helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}

$max_vmcategory_menu = $helix3->getParam('max_VMCategoryMenu', 6);
$count_root_category = count($categories);
$i = 1;
$count_parentCatRoot = 1;

foreach($categories as $item) {
	$cidRoot   = $item->virtuemart_category_id;
	$parentCatRoot = $categoryModel->getCategoryRecurse($cidRoot,0); 
	$count_parentCatRoot = count($parentCatRoot);	
}
?>
<div id="vina-treeview-virtuemart<?php echo $module->id; ?>" class="vina-treeview-virtuemart">
	<?php if($params->get('showControl', 1)) { ?>
	<div id="vina-treeview-treecontrol<?php echo $module->id; ?>" class="treecontrol">
        <a href="#" title="<?php echo JTEXT::_('VINA_TREEVIEW_VMART_COLLAPSE_ALL_DESC'); ?>"><?php echo JTEXT::_('VINA_TREEVIEW_VMART_COLLAPSE_ALL'); ?></a> | 
        <a href="#" title="<?php echo JTEXT::_('VINA_TREEVIEW_VMART_EXPAND_ALL_DESC'); ?>"><?php echo JTEXT::_('VINA_TREEVIEW_VMART_EXPAND_ALL'); ?></a> | 
        <a href="#" title="<?php echo JTEXT::_('VINA_TREEVIEW_VMART_TOGGLE_ALL_DESC'); ?>"><?php echo JTEXT::_('VINA_TREEVIEW_VMART_TOGGLE_ALL'); ?></a>
    </div>
	<?php } ?>
	
	<ul class="level0 <?php echo $params->get('moduleStyle', ''); ?>">
		<?php require JModuleHelper::getLayoutPath($module->module, 'default_items'); ?>
		
		<?php if( $count_root_category > $max_vmcategory_menu ) : ?>
		<li class="vmcategory-more">
			<div class="more-inner">
				<span class="more-view"><em class="more-categories"><?php echo JTEXT::_('VINA_MORE_CATEGORIES'); ?></em></span>
			</div>
		</li>
		<?php endif; ?>
	</ul>
</div>
<?php
if( $count_root_category > $max_vmcategory_menu ) {
	$js="
	//<![CDATA[
	jQuery(document).ready(function() {
		jQuery('#vina-treeview-virtuemart" . $module->id . " li.extra_menu').hide();
		jQuery('#vina-treeview-virtuemart" . $module->id . " .vmcategory-more').click(function() {
			jQuery('#vina-treeview-virtuemart" . $module->id . " li.extra_menu').slideToggle();
			jQuery('.extra_menu').css('overflow','visible');
			
			if(jQuery('#vina-treeview-virtuemart" . $module->id . " .vmcategory-more .more-view').hasClass('open'))
			{
				jQuery('#vina-treeview-virtuemart" . $module->id . " .vmcategory-more .more-view').removeClass('open');
				jQuery('#vina-treeview-virtuemart" . $module->id . " .vmcategory-more .more-view').html('<em class=\"more-categories\">" . JTEXT::_('VINA_MORE_CATEGORIES') . "</em><i class=\"zmdi zmdi-plus-circle-o\"></i>');
			}
			else
			{
				jQuery('#vina-treeview-virtuemart" . $module->id . " .vmcategory-more .more-view').addClass('open');
				jQuery('#vina-treeview-virtuemart" . $module->id . " .vmcategory-more .more-view').html('<em class=\"closed-menu\">" . JTEXT::_('VINA_CLOSE_MENU') . "</em><i class=\"zmdi zmdi-minus-circle-outline\"></i>');
			}
		});
	});
	//]]>
	" ;

	$document = JFactory::getDocument();
	$document->addScriptDeclaration($js);
}
?>

<script type="text/javascript">
jQuery("#vina-treeview-virtuemart<?php echo $module->id; ?> ul").treeview({
	animated: 	"<?php echo $params->get('animated', 1); ?>",
	persist: 	"<?php echo $params->get('persist', 'cookie'); ?>",
	collapsed: 	<?php echo $params->get('collapsed', 1) ? "true" : "false"; ?>,
	unique:		<?php echo $params->get('unique', 1) ? "true" : "false"; ?>,
	<?php if($params->get('showControl', 1)) { ?>
	control: "#vina-treeview-treecontrol<?php echo $module->id; ?>",
	<?php } ?>
});
</script>
