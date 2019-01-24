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
$Revision: 5 $
$LastChangedDate: 2015-01-22 02:23:43 +0530 (Thu, 22 Jan 2015) $
$Id: default_btaddress.php 5 2015-01-21 20:53:43Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');
?>
<div class="inner-wrap">
	<div class="edit-address">
		<?php if(!empty($this->btFields['fields'])) : ?>
			<form id="EditBTAddres" autocomplete="off">
				<?php 
				foreach($this->btFields['fields'] as $name => $field)
				{
					echo '<div class="'.$field['name'].'-group">';
					echo '<div class="inner">';
					if(strpos($field['formcode'],'<select') === false)
					{
						echo '<label class="' . $field['name'] . '" for="' . $field['name'] . '_field">';
					} 
					else
					{
						echo '<label class="' . $field['name'] . '" for="' . $field['name'] . '">';
					}
					echo vmText::_($field['title']);
					echo (strpos($field['formcode'],'required') || $field['required'])  ? ' *' : '';
					echo '</label>';
					if($field['name'] == 'zip' || $field['name'] == 'city')
					{
						echo str_replace('<input', '<input onchange="return ProOPC.updateBTaddress(this);"', $field['formcode']);
					} 
					elseif($field['name'] == 'virtuemart_country_id' || $field['name'] == 'virtuemart_state_id')
					{
						echo str_replace(array('<select', 'vm-chzn-select'), array('<select onchange="return ProOPC.updateBTaddress(this);"', ''), $field['formcode']);
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
			</form>
		<?php endif; ?>
	</div>
	<input type="hidden" name="billto" value="<?php echo $this->cart->lists['billTo']; ?>"/>
</div>