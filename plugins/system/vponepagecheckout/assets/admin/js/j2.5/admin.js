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
$Id: admin.js 19 2015-01-24 13:58:10Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
jQuery(document).ready(function($) {
	var $title = $('.vp-extension-description .extension-title [data-text]');
	$title.text($title.data('text'));
	$('#vp-inline-stylesheet').remove();
});