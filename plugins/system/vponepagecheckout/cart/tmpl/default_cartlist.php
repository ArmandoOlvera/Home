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
$Id: default_cartlist.php 7 2015-01-22 17:26:52Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');

$style = $this->params->get('style', 1);
if($style == 1)
{
	// For style 1 layout we need to have a different type of price list layout
	echo $this->loadTemplate('pricelistnarrow');
}
else
{
	// For style 2 layout we use the same price list sublayout as first stage.
	// default_pricelist.php layout will always display full cart table when we are in final stage.
	echo $this->loadTemplate('pricelist');
}