<?php 
/**
 * @version 1.1.0
 * @package Joomla
 * @subpackage Popup_Anywhere
 * @copyright Copyright (C) 2010 codextension.com. All rights reserved.
 * @license GNU/GPL, see LICENSE.txt
 * @contact contact@codextension.com
 * @website codextension.com
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formfield');

class JFormFieldAddarticle extends JFormField
{
	protected $type = 'addarticle';

	protected function getInput()
	{
		$name	= $this->name;
		$value	= $this->value;
		$id		= $this->id;
		
		$db			=& JFactory::getDBO();
		$doc 		=& JFactory::getDocument();
		//$fieldName	= $control_name.'['.$name.']';
		$article =& JTable::getInstance('content');
		if ($value) {
			$article->load($value);
		} else {
			$article->title = 'Please select an Article';
		}

		$js = "
		function jSelectArticle(id, title, object) {			
			document.getElementById('".$this->id."'+'_id').value = id;
			document.getElementById('".$this->id."'+'_name').value = title;
			document.getElementById('jform_params_link').value = 'index.php?option=com_content&view=article&tmpl=component&id='+id;
			SqueezeBox.close();
		}";
		$doc->addScriptDeclaration($js);

		//$link = 'index.php?option=com_content&amp;task=element&amp;tmpl=component&amp;object='.$name;
		$link	= 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle';

		// class='required' for client side validation
		$class = '';
		if ($this->required) {
			$class = ' class="required modal-value"';
		}
		
		JHTML::_('behavior.modal', 'a.modal');
		$html = "\n".'<div style="float: left;"><input style="background: #ffffff;" type="text" id="'.$this->id.'_name" value="'.htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" /></div>';
		$html .= '<div class="button2-left"><div class="blank"><a class="modal" title="'.JText::_('Select an Article').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}">'.JText::_('Select').'</a></div></div>'."\n";
		$html .= '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';
		return $html;
	}
}