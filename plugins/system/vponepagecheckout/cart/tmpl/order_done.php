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
$Id: order_done.php 5 2015-01-21 20:53:43Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die;

if($this->display_title)
{
	echo '<h3>' . vmText::_('COM_VIRTUEMART_CART_ORDERDONE_THANK_YOU') . '</h3>';
}
// Everything here is displayed by payment method plugin.
// It is exactly same as standard VirtueMart order done layout. We just need to print it as it is.
echo $this->html;
