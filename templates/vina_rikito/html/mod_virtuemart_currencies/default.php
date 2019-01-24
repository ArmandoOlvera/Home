<?php // no direct access
defined('_JEXEC') or die('Restricted access');
vmJsApi::jQuery();
vmJsApi::chosenDropDowns();
?>

<!-- Currency Selector Module -->
<?php echo $text_before; ?>
<?php 
	$this_name_form = 'user_mode'.$module->id; 
	$class = "class='inputbox selectpicker' OnChange='".$this_name_form.".submit();return false;'";
?>
<div id="curVm3_<?php echo $module->id; ?>" class="curVm3">
	<form id="cur_form" class="cur_box" name="user_mode<?php echo $module->id; ?>" action="<?php echo vmURI::getCleanUrl() ?>" method="post"> 
		<?php echo JHTML::_('select.genericlist', $currencies, 'virtuemart_currency_id', $class , 'virtuemart_currency_id', 'currency_txt', $virtuemart_currency_id) ; ?>
	</form>
</div>