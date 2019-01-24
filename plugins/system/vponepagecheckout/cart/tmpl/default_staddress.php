<?php
/*--------------------------------------------------------------------------------------------------------
# VP One Page Checkout - Joomla! System Plugin for VirtueMart 3
----------------------------------------------------------------------------------------------------------
# Copyright:     Copyright (C) 2012 - 2015 VirtuePlanet Services LLP. All Rights Reserved.
# License:       GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
# Author:        Abhishek Das
# Email:         info@virtueplanet.com
# Websites:      http://www.virtueplanet.com
----------------------------------------------------------------------------------------------------------
$Revision: 38 $
$LastChangedDate: 2015-03-04 18:17:34 +0530 (Wed, 04 Mar 2015) $
$Id: default_staddress.php 38 2015-03-04 12:47:34Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');
if($this->juser->guest)
{
	unset($this->stFields['fields']['address_type_name']);
}
?>
<div id="proopc-st-address">
	<div class="inner-wrap">
		<label for="BTasST" class="st-same-checkbox">
			<input type="checkbox" name="STsameAsBT" id="STsameAsBT" <?php echo $this->cart->STsameAsBT == 1 ? 'checked="checked"' : '' ; ?> onclick="return ProOPC.setst(this);"/>
			<?php echo JText::_('COM_VIRTUEMART_USER_FORM_ST_SAME_AS_BT') ?>
		</label>
		<div class="edit-address<?php echo ($this->cart->STsameAsBT == 1) ? '' : ' soft-show'; ?>">
			<?php if(!empty($this->stFields['fields'])) : ?>
				<form id="EditSTAddres" autocomplete="off">
					<?php 
					if($this->selectSTName && !$this->juser->guest)
					{
						echo '<div class="proopc-select-st-group">';
						echo '<div class="inner">';
						echo '<label class="">' . JText::_('PLG_VPONEPAGECHECKOUT_SELECT_ADDRESS') . '</label>';
						echo $this->selectSTName;
						echo '</div>';
						echo '</div>';	
					}	
					foreach($this->stFields['fields'] as $field)
					{
						echo '<div class="' . $field['name'] . '-group">';
						echo '<div class="inner">';
						if(strpos($field['formcode'],'<select') === false)
						{
							echo '<label class="' . $field['name'] . '" for="' . $field['name'] . '_field">';
						}
						else
						{
							echo '<label class="' . $field['name'] . '" for="' . $field['name'] . '">';
						}	
						echo JText::_($field['title']);	
						echo (strpos($field['formcode'],'required') || $field['required']) ? ' *' : '';			
						echo '</label>';
						if($field['name'] == 'shipto_zip' || $field['name'] == 'shipto_city')
						{
							echo str_replace('input', 'input onchange="return ProOPC.updateSTaddress(this);"', $field['formcode']);
						}
						elseif($field['name'] == 'shipto_virtuemart_country_id' || $field['name'] == 'shipto_virtuemart_state_id')
						{
							echo str_replace(array('<select', 'vm-chzn-select'), array('<select onchange="return ProOPC.updateSTaddress(this);"', ''), $field['formcode']);
						}
						elseif(strpos($field['formcode'], 'vm-chzn-select') !== false)
						{
							echo str_replace('vm-chzn-select', '', $field['formcode']);
						}
						else
						{
							echo $field['formcode'];
						}
						echo '</div>';
						echo '</div>';
					} ?>
					<input type="hidden" name="shipto_virtuemart_userinfo_id" id="shipto_virtuemart_userinfo_id" value="<?php echo $this->cart->selected_shipto ?>"/>
				</form>
			<?php endif; ?>
		</div>
	</div>
</div>