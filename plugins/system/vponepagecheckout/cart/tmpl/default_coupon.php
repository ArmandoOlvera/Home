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
$Revision: 19 $
$LastChangedDate: 2015-01-24 19:28:10 +0530 (Sat, 24 Jan 2015) $
$Id: default_coupon.php 19 2015-01-24 13:58:10Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');
$btn_class = ($this->params->get('color', 1) == 2) ? ' proopc-btn-danger' : '';
?>
<div class="inner-wrap">
	<div class="proopc-input-append proopc-row">
		<input type="text" name="coupon_code" size="20" maxlength="50" id="proopc-coupon-code" alt="<?php echo $this->coupon_text ?>" value="<?php echo $this->coupon_text; ?>" onblur="if(this.value=='') this.value='<?php echo $this->coupon_text; ?>';" onfocus="if(this.value=='<?php echo $this->coupon_text; ?>') this.value='';" data-default="<?php echo $this->coupon_text; ?>""/>
		<button type="button" class="proopc-btn<?php echo $btn_class ?>" title="<?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?>" onclick="return ProOPC.savecoupon(this);"><?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?></button>
	</div>
</div>