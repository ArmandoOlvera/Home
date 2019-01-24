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
$Id: texttotag.php 19 2015-01-24 13:58:10Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('text');

class JFormFieldTexttotag extends JFormFieldText
{
	protected $type = 'Texttotag';

	function getInput()
	{
		if(version_compare(JVERSION, '3.0.0', 'ge'))
		{
			JHtml::_('jquery.framework');
			JHtml::_('script', 'system/html5fallback.js', false, true);
			$doc = JFactory::getDocument();
			$root = JUri::root(true);
			$doc->addScript($root . '/plugins/system/vponepagecheckout/assets/admin/js/angular.min.js');
			JHtml::_('bootstrap.framework');
			$doc->addScript($root . '/plugins/system/vponepagecheckout/assets/admin/js/bootstrap-tagsinput.js');
			$doc->addScript($root . '/plugins/system/vponepagecheckout/assets/admin/js/bootstrap-tagsinput-angular.js');
			$doc->addStyleSheet($root . '/plugins/system/vponepagecheckout/assets/admin/css/bootstrap-tagsinput.css');
			$doc->addScriptDeclaration("
			jQuery(function($) {
				$('#{$this->id}').tagsinput({
						trimValue: true,
						allowDuplicates: false,
						tagClass: 'label'
				});
			});
			");
		}
		
		$html  = parent::getInput();
		$html .= '<div class="clearfix"></div>';
		$html .= '<div class="muted small">Examples: Email Missing, Missing* etc.</div>';
		return $html;
	}
}