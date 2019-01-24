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
$Id: default_checkout.php 5 2015-01-21 20:53:43Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined ('_JEXEC') or die('Restricted access');

$style = $this->params->get('style', 1);
if($style == 1)
{
	echo $this->loadTemplate('style1');
}
else
{
	echo $this->loadTemplate('style2');
} 
?>