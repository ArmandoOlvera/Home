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
$Revision: 7 $
$LastChangedDate: 2015-01-22 22:56:52 +0530 (Thu, 22 Jan 2015) $
$Id: default_shipment.php 7 2015-01-22 17:26:52Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');
?>
<?php if($this->found_shipment_method) : ?>
	<form id="proopc-shipment-form">
		<div class="inner-wrap">
			<fieldset>
				<?php foreach ($this->shipments_shipment_rates as $shipment_shipment_rates)
				{
					if(is_array($shipment_shipment_rates))
					{
						foreach ($shipment_shipment_rates as $shipment_shipment_rate)
						{
							echo str_replace('name="virtuemart_shipmentmethod_id"', 'name="virtuemart_shipmentmethod_id" onclick="return ProOPC.setshipment(this);"', $shipment_shipment_rate);
							echo '<div class="clear"></div>';
						}
					}
				} ?>
				<input type="hidden" name="proopc-savedShipment" id="proopc-savedShipment" value="<?php echo $this->cart->virtuemart_shipmentmethod_id ?>"/>
			</fieldset>
		</div>
	</form>
<?php else : ?>
	<div class="proopc-alert-error"><?php echo $this->shipment_not_found_text ?></div>
<?php endif; ?>

