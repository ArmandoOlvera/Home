<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen

 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_images.php 8657 2015-01-19 19:16:02Z Milbo $
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
$vm_image_zoom 			= $helix3->getParam('vm_image_zoom', 1);
$vm_zoomType			= $helix3->getParam('vm_zoomType', 'window');
$vm_zoomCursor			= $helix3->getParam('vm_zoomCursor', 'default');
$vm_zoomLensShape		= $helix3->getParam('vm_zoomLensShape', 'square');
$vm_scrollZoom			= $helix3->getParam('vm_scrollZoom', 1) ? "true" : "false";
$vm_zoomWindowFadeIn 	= $helix3->getParam('vm_zoomWindowFadeIn', 500);
$vm_zoomWindowFadeOut	= $helix3->getParam('vm_zoomWindowFadeOut', 500);
$vm_zoomLensFadeIn		= $helix3->getParam('vm_zoomLensFadeIn', 500);
$vm_zoomLensFadeOut		= $helix3->getParam('vm_zoomLensFadeOut', 500);

if(VmConfig::get('usefancy',1)){
	$app = JFactory::getApplication();
	$document = JFactory::getDocument ();
	$template = $app->getTemplate();
	$current_template_path = $this->baseurl."/templates/".$template ;	
	vmJsApi::removeJScript( 'fancybox/jquery.fancybox-1.3.4.pack' );
	vmJsApi::css('jquery.fancybox-1.3.4');
	$document->addScript($current_template_path .'/js/jquery.custom.fancybox-1.3.4.pack.js' );
	
	$zoom_image = '';
	if($vm_image_zoom) {
		$zoom_image = '
			jQuery(".zoomContainer").remove();
			if( !(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) && jQuery("#zoom-image").length ) {
				jQuery("#zoom-image").elevateZoom({
					zoomType: "' . $vm_zoomType . '",
					cursor: "' . $vm_zoomCursor . '",
					lensShape: "' . $vm_zoomLensShape . '",
					scrollZoom : ' . $vm_scrollZoom . ',
					zoomWindowFadeIn: '. $vm_zoomWindowFadeIn .',
					zoomWindowFadeOut: '. $vm_zoomWindowFadeOut .',
					lensFadeIn: '. $vm_zoomLensFadeIn .',
					lensFadeOut: '. $vm_zoomLensFadeOut .'
				});
				jQuery(".main-image a").attr("title",jQuery("#zoom-image").attr("alt") );
			}
		';
	}
	
	$imageJS = '
	jQuery(document).ready(function() {
		Virtuemart.updateImageEventListeners()
	});
	Virtuemart.updateImageEventListeners = function() {
		jQuery("a[data-rel=vm-additional-images]").fancybox({			
			"transitionIn"		: "elastic",
			"transitionOut"		: "elastic",
			"titlePosition" 	: "over",
			"titleFormat"		: function(title, currentArray, currentIndex, currentOpts) {
				return "<span id=\"fancybox-title-over\">' . JText::_('IMAGE') . ' " +  (currentIndex + 1) + " / " + currentArray.length + " - " + title + "</span>";
			}
		});
		jQuery(".additional-images a.product-image.image-0").removeAttr("data-rel");
		jQuery(".additional-images img.product-image").click(function() {
			jQuery(".additional-images a.product-image").attr("data-rel","vm-additional-images" );
			jQuery(this).parent().children("a.product-image").removeAttr("data-rel");
			var src = jQuery(this).parent().children("a.product-image").attr("href");
			jQuery(".main-image img").attr("src",src);
			jQuery(".main-image img").attr("alt",this.alt );
			jQuery(".main-image a").attr("href",src );
			jQuery(".main-image a").attr("title",this.alt );
			jQuery(".main-image .vm-img-desc").html(this.alt);			
			
			/* Zoom Image add code */			
			'.$zoom_image.'
			/* Zoom Image end */
		}); 
	}
	';
} else {
	vmJsApi::addJScript( 'facebox',false );
	vmJsApi::css( 'facebox' );
	$document = JFactory::getDocument ();
	$imageJS = '
	jQuery(document).ready(function() {
		Virtuemart.updateImageEventListeners()
	});
	Virtuemart.updateImageEventListeners = function() {
		jQuery("a[data-rel=vm-additional-images]").facebox();
		var imgtitle = jQuery("span.vm-img-desc").text();
		jQuery("#facebox span").html(imgtitle);
	}
	';
}
vmJsApi::addJScript('imagepopup',$imageJS);

// Zoom Image add code --------------------------------------------------------------------------
if($vm_image_zoom) {
	$jvmzoom = '
	if(!(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent))) {
		jQuery(document).ready(function() {
			if(jQuery("#zoom-image").length) {
				jQuery(".zoomContainer").remove();
				jQuery("#zoom-image").elevateZoom({
					zoomType: "' . $vm_zoomType . '",
					cursor: "' . $vm_zoomCursor . '",
					lensShape: "' . $vm_zoomLensShape . '",
					scrollZoom : ' . $vm_scrollZoom . ',
					zoomWindowFadeIn: '. $vm_zoomWindowFadeIn .',
					zoomWindowFadeOut: '. $vm_zoomWindowFadeOut .',
					lensFadeIn: '. $vm_zoomLensFadeIn .',
					lensFadeOut: '. $vm_zoomLensFadeOut .'
				});
				jQuery(".main-image img").attr("alt",jQuery(".additional-images img.product-image.img-0").attr("alt") );
				jQuery(".main-image a").attr("title",jQuery("#zoom-image").attr("alt") );
			}
		});
	}';

	vmJsApi::addJScript('jvmzoom',$jvmzoom);
}
// Zoom Image end ---------------------------------------------------------------------------------

if (!empty($this->product->images)) {
	$image = $this->product->images[0];
	//$title_file = $image->file_name;
	$title_file = '';
	$title_file = ($image->file_meta == '') ? ' title="'. $image->file_name .'"' : ' title="'. $image->file_meta .'"';
	?>
	<div class="main-image">
		<?php echo $image->displayMediaFull( 'id="zoom-image"',true, 'data-rel="vm-additional-images"'.$title_file ); ?>
		<?php //echo $image->displayMediaFull("",true,"rel='vm-additional-images'"); ?>
		<div class="clear"></div>
	</div>
	<?php
}
?>
<?php if($vm_image_zoom) :?>
<script type="text/javascript" src="<?php echo $current_template_path; ?>/js/jquery.elevatezoom.js"></script>
<?php endif; ?>