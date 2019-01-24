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
$Id: default_payment.php 7 2015-01-22 17:26:52Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');
?>
<?php if($this->found_payment_method) : ?>
	<div class="inner-wrap">
		<form id="proopc-payment-form">
			<fieldset>
				<?php foreach($this->paymentplugins_payments as $paymentplugin_payments)
				{
					if(is_array($paymentplugin_payments))
					{
						foreach($paymentplugin_payments as $paymentplugin_payment)
						{
							echo $paymentplugin_payment;
							echo "\n" . '<div class="clear proopc-method-end"></div>' . "\n";
						}
					}
				} ?>
			</fieldset>
		</form>
	</div>
<?php else : ?>
	<div class="proopc-alert-error payment"><?php echo $this->payment_not_found_text ?></div>  
<?php endif; ?>
