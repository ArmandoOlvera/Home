<?php
/**
 * @version 1.0
 * @package Joomla
 * @subpackage SWFContent (Module)
 * @author Mr.LongAnh <contact@codextension.com>
 * @copyright Copyright (C) 2009 Codextension. All rights reserved.
 * @license GNU/GPL
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formfield');

class JFormFieldToggleRadio extends JFormField{
	protected $type = 'toggleradio';

	protected function getInput(){
		$doc 		=& JFactory::getDocument();
		$doc->addStyleSheet(JURI::root().'plugins/system/popup_anywhere/toggle/css/toggle.css');
		$doc->addScript(JURI::root().'plugins/system/popup_anywhere/toggle/js/touch-mt1.2.js');
		$doc->addScript(JURI::root().'plugins/system/popup_anywhere/toggle/js/toggle-mt1.2.js');
		$js = "
			window.addEvent('domready', function(){ var x = new Toggle(); });
		";
		$doc->addScriptDeclaration($js);
		$html	= '';
		return $html;
	}
}
