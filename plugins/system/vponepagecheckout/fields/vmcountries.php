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
$Id: vmcountries.php 23 2015-01-24 14:58:12Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined('JPATH_PLATFORM') or die;
JFormHelper::loadFieldClass('list');

if (!class_exists('VmConfig'))
{
	$config = JPath::clean(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
	if(file_exists($config)) require($config);
}

class JFormFieldVMCountries extends JFormFieldList
{
	protected $type = 'VMCountries';
	
	protected static $vmcountries = null;

	protected function getOptions()
	{
		if(!class_exists('VmConfig'))
		{
			JFactory::getApplication()->enqueueMessage('VirtueMart 3 Component not found in your site.', 'error');
			return array();
		}
		
		if(self::$vmcountries === null)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true)
			            ->select('`virtuemart_country_id` AS value, `country_name` AS text')
			            ->from('`#__virtuemart_countries`')
			            ->where('published = 1');
			if(version_compare(JVERSION, '3.0.0', 'ge'))
			{
				$query->clear('limit');
			}
			$db->setQuery($query);
			self::$vmcountries = $db->loadObjectList();
		}
		$options = array();
		foreach(self::$vmcountries as $country)
		{
			$options[] = JHtml::_('select.option', (int) $country->value, $country->text);
		}
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}