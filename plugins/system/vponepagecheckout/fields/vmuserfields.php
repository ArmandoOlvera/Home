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
$Revision: 23 $
$LastChangedDate: 2015-01-24 20:28:12 +0530 (Sat, 24 Jan 2015) $
$Id: vmuserfields.php 23 2015-01-24 14:58:12Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('JPATH_PLATFORM') or die;
JFormHelper::loadFieldClass('list');

if (!class_exists('VmConfig'))
{
	$config = JPath::clean(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
	if(file_exists($config)) require($config);
}

class JFormFieldVMUserfields extends JFormFieldList
{
	protected $type = 'VMUserfields';
	
	protected static $userfields = array();

	protected function getOptions()
	{
		if(!class_exists('VmConfig'))
		{
			JFactory::getApplication()->enqueueMessage('VirtueMart 3 Component not found in your site.', 'error');
			return array();
		}
		
		VmConfig::loadConfig();
		if (!class_exists ('VirtueMartModelUserfields')) 
		{
			require(VMPATH_ADMIN . '/models/userfields.php');
		}
		
		$type = !empty($this->element['field_type']) ? (string) $this->element['field_type'] : 'BT';
		
		if(!isset(self::$userfields[$type]))
		{
			$userFieldsModel = VmModel::getModel('Userfields');
			self::$userfields[$type] = $userFieldsModel->getUserFieldsFor('cart', $type);
		}

		$options = array();
		$skips = array('email', 'username', 'name', 'password', 'password2', 'agreed');
		foreach(self::$userfields[$type] as $field)
		{
			if(!in_array($field->name, $skips) && $field->type != 'delimiter')
			{
				$options[] = JHtml::_('select.option', (string) $field->name, vmText::_($field->title));
			}
		}
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}