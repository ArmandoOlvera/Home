/*--------------------------------------------------------------------------------------------------------
# VP One Page Checkout - Joomla! System Plugin for VirtueMart 3
----------------------------------------------------------------------------------------------------------
# Copyright:     Copyright (C) 2012 - 2015 VirtuePlanet Services LLP. All Rights Reserved.
# License:       GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
# Author:        Abhishek Das
# Email:         info@virtueplanet.com
# Websites:      http://www.virtueplanet.com
----------------------------------------------------------------------------------------------------------
$Revision: 24 $
$LastChangedDate: 2015-01-24 20:54:30 +0530 (Sat, 24 Jan 2015) $
$Id: admin.js 24 2015-01-24 15:24:30Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
jQuery(document).ready(function($) {
	var $title = $('.vp-extension-description .extension-title [data-text]');
	$title.text($title.data('text'));
	$('.hide-on-j3').parents('.control-group').hide();
	$('#vp-inline-stylesheet').remove();
	var title;
	$('label').each(function() {
		title = $(this).attr('title'); 
		if(title && title.indexOf('</strong>') && title.indexOf('<br/>')) {
			title = title.replace('<br/>', '');
			if(title.indexOf('<br />') === -1) {
				title = title.replace('</strong>', '</strong><br/>');
			}
			$(this).attr('title', title);
		}
	});
	$('.hasTooltip').tooltip({'html': true, 'container': 'body'});
});
