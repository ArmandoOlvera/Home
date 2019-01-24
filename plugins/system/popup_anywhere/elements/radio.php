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

class JFormFieldRadio extends JFormField{
	protected $type = 'radio';

	protected function getInput(){		
		if( $this->value ){
			$class = "toggle-checked";
		}else{
			$class = "toggle-unchecked";
		}
		$html='';
		$html .='
		
		<div class="wrapper">
			<!--<div class="chain-label">Show</div>-->
			<div class="toggle">

				<div class="toggle-container '.$class.'">

					<div class="toggle-sides">

						<div class="toggle-wrapper">

							<div class="toggle-switch"></div>
							<input type="hidden" id="'.$this->id.'_id" name="'.$this->name.'" value="'.$this->value.'" />
							<input type="checkbox" checked="" value="'.$this->value.'" class="toggle-input" />
						</div>

						<div class="toggle-button"></div>

					</div>

				</div>

			</div>
		</div>
		';
		return $html;
	}
}
