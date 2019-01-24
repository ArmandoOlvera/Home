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
$Id: default_advertisement.php 7 2015-01-22 17:26:52Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined ( '_JEXEC' ) or die ( 'Restricted access' );
?>
<?php if(!empty($this->checkoutAdvertise) && $this->params->get('checkout_advertisement', 1)) : ?>
	<div id="proopc-advertise-box">
		<?php foreach($this->checkoutAdvertise as $checkoutAdvertise) : ?>
			<div class="checkout-advertise">
				<?php echo $checkoutAdvertise; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>