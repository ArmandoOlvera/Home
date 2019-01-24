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

class JFormFieldPreview extends JFormField{
	protected $type = 'preview';

	protected function getInput(){
		$html = '<div class="button2-left"><div class="blank"><a href="../index.php?popup_anywhere_preview=true" target="_blank">Click for Preview (apply first)</a></div></div>';
		return $html;
	}
}
