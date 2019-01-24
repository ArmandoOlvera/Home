<?php
/**
 * @version 2.0.0
 * @package Popup_Anywhere
 * @subpackage plg_popup_anywhere
 * @copyright Copyright (C) 2010 codextension.com. All rights reserved.
 * @license GNU/GPL, see LICENSE.txt
 * @author Codextension
 * @contact contact@codextension.com
 * @website codextension.com
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgSystemPopup_Anywhere extends JPlugin{

	function plgSystemPopup_Anywhere(& $subject, $config){
		parent::__construct($subject, $config);
	}

	
	
	function onAfterDispatch(){		
		$mainframe = JFactory::getApplication();
		
		if($mainframe->isAdmin()) {
			return;
		}

		$popupWidth = intval($this->params->get('popupwidth',0));
		$popupHeight = intval($this->params->get('popupheight',0));
		
		$x = '';
		if (trim($popupWidth)!=''){
			$x = "x: ".intval($popupWidth).' ';
		}
		
		$y = '';
		if (trim($popupHeight)!=''){
			$y = "y: ".intval($popupHeight);
		}
		
		$modaltype = intval($this->params->get('modaltype',0));
		if ($modaltype==0){
			$modaltype='iframe';
		}else if ($modaltype==1){
			$modaltype='url';
		}else{
			$modaltype='image';
		}
		
		$image = $this->params->get('image','');
		if (trim($image!='')){
			$link = $image;
			$modaltype = 'image';
		}else{
			$link = $this->params->get('link','');
			if (trim($link=='')){
				return;
			}
			/*CHECK POPUP IN IFRAME*/
				if( strpos($link,"?") && !strpos($link,"popupiniframe") ){
					$link =$link."&popupiniframe=true";
				}else{
					if( !strpos($link,"popupiniframe") ){
						$link =$link."?popupiniframe=true";
					}
				}
				$popupiniframe = JRequest::getVar("popupiniframe","0");
				if( $popupiniframe ){
					return;
				}
			/*CHECK POPUP IN IFRAME*/
		}
		
		$addUrl = $this->params->get('addurl',1);
		if ($addUrl==1&&strtolower(substr($link,0,7))!='http://'){
			$link=JURI::base().$link;
		}
		
		$type = intval($this->params->get('type',0));
		$cookietime = intval($this->params->get('cookietime',0));
		
		$session =&JFactory::getSession();
		
		//check endless
		
		$par = array();
		
		$preview = JRequest::getVar('popup_anywhere_preview',false);
		$session =&JFactory::getSession();
		
		if ($preview=='true'){
			$preview = true;
			$session->set('modaldone','true','plg_popup_anywhere');
			setcookie('modaldone', 'true',time() + 300*3600,'/');
		}else{			
			$preview = false;
		}
		
		
		$time = $cookietime;		
		if ($cookietime>0){
			$time = time()+($cookietime*3600);
		}		
		if (!$preview){			
			if ($type==0){			
				$value = $session->get('modaldone',null,'plg_popup_anywhere');
				$session->set('modaldone','true','plg_popup_anywhere');
				setcookie('modaldone', 'false',$time,'/'); 
				if ($value=='true'){
					return;
				}
			}else if ($type==1){
				if (isset($_COOKIE['modaldone'])){
					$value = $_COOKIE['modaldone'];
				}else{
					$value = 'false';
				}				
				setcookie('modaldone', 'true',$time,'/');
				if ($value=='true'){
					return;
				}
			}else{
				
			}
	}
		
		//add pop up Javascript to document
		
		//JHTML::_('behavior.mootools');
		JHTML::_('behavior.modal');

		$sizeStr = '';
		if ($popupWidth>0&&$popupHeight==0){
			$sizeStr = ", size: {".$x."}";
		}elseif ($popupWidth==0&&$popupHeight>0){
			$sizeStr = ", size: {".$y."}";
		}elseif ($popupWidth>0&&$popupHeight>0){
			$sizeStr = ", size: {".$x.", ".$y."}";
		}
		
		$linkofimage = $this->params->get('linkofimage','');
		
		$noconflict = intval($this->params->get('enableNoConflict',0));
		
		$nc = '$';
		if ($noconflict==1){
			$nc = 'document.id';
		}
		
		
		$closeafter = intval($this->params->get('closeafterseconds',0));
		$jsCloseString = '';
		if ($closeafter>0){
			$jsCloseString = 'setTimeout(\'popupAnywherecloseWindow();\','.($closeafter*1000).');';
		}
		
		$jsimagelink = '';
		
		$document =& JFactory::getDocument();
		
		if (trim($linkofimage)!=''){
			$linktarget = intval($this->params->get('linktarget',0));
			$locationJs = '';
			switch($linktarget){
				case 0:
					$locationJs = "parent.window.location.href='".$linkofimage."'";
				break;
				case 1:
					$locationJs = "document.forms['popupAnywhereForm'].submit();";
				break;
				case 2:
					$locationJs = "location.href='".$linkofimage."'";
				break;
			}
			
			if ($modaltype=='iframe'){
				$jsimagelink = "$$('.sbox-content-iframe iframe').setProperty('id','popupanywhereIframeId');IframeOnClick.track(".$nc."('popupanywhereIframeId'), function() { ".$locationJs.";});";
				$document->addScript( JURI::base().'plugins/system/popup_anywhere/js/iframetracker.js' );
			}else{
				$jsimagelink = $nc."('sbox-content').addEvent('click', function(){".$locationJs.";});";
			}
		}
		// if mootool < 1.2 , use $$ => noproblem , but use $ problem with jQuery
		// if mootool >=1.2 , use $$ => noproblem , but use $ problem with jQuery , and have to use document.id(only support mootool 1.2)
		
		$noscroll = '';
		if (intval($this->params->get('scrollbar',1))==0&&$modaltype!='image'){		
			if ($modaltype=='iframe'){
				$noscroll = '$$(\'.sbox-content-iframe iframe\').setProperty(\'scrolling\',\'no\');';
			}elseif($modaltype=='url'){
				$noscroll = $nc.'(\'sbox-content\').setStyle(\'overflow\',\'hidden\');';
			}
		}
		$nobutton = '';
		if (intval($this->params->get('closebutton',1))==0){
			$nobutton = $nc.'(\'sbox-btn-close\').setStyle(\'display\',\'none\');';
		}
		$onUpdate = '';
		if ($jsCloseString!=''||$jsimagelink!=''||$noscroll!=''||$nobutton!=''){
			$onloadwrapper = '{placeholder}';
			if ($modaltype=='iframe'){
				$onloadwrapper = "$$('.sbox-content-iframe iframe').addEvent('load', function(){{placeholder};});"; 
			}
			/*if ($modaltype=='url'){
				$onloadwrapper = $nc."('page').addEvent('domready', function(){{placeholder};});"; 
			}
			if ($modaltype=='image'){
				$onloadwrapper = "$$('#sbox-content img').setProperty('onload', 'alert(\\\'yes\\\')');"; 
			}*/
			$addOnLoad = str_replace('{placeholder}',$jsCloseString.$jsimagelink,$onloadwrapper);
			$onUpdate = ", onUpdate:function(){".$noscroll.$nobutton.$addOnLoad."}";
		}
		
		$globals = '';
		
		if ($jsCloseString!=''){
			$globals = 'function popupAnywherecloseWindow(){
				SqueezeBox.close();
			}
			';
		}
		$options = "handler: '".$modaltype."'".$sizeStr.$onUpdate;
		
		
		$js = "
	SqueezeBox.open('".$link."',{".$options."});";
		
		
		$document->addScriptDeclaration($this->_wrapJS($js,true,$globals));

		$cursor = $this->params->get('cursor','');
		if (trim($cursor)!=''){
			if ($modaltype!='iframe'){
				$document->addStyleDeclaration('#sbox-window{cursor:'.$cursor.' !important}', 'text/css');
			}
		}
		
		$template = $this->params->get('template','');
		
		if ($preview){
			$session->set('modaldone','true','plg_popup_anywhere');
			setcookie('modaldone', 'true', time() + (100 * 60 * 60),JURI::base(),'/');
		}
		
		if (trim($template)==''||$template==null||intval($template)==-1){
			return;
		}
		$document->addStyleSheet( JURI::root().'plugins/system/popup_anywhere/css/'.$template, 'text/css', null, array() );
	}
	
	/**
	 * Wraps Javascript
	 *
	 * @param string $js main Javascript
	 * @param bool $onload add mootools window.addEvent('load'...
	 * @param string $globals if $onload true -> $globals will be inserted between script tag and mootools window.addEvent
	 * @return string wrapped javascript
	 */

	function _wrapJS($js, $onload=false, $globals=''){
		if ($onload==false) return $js;
		return $globals.'
	window.addEvent("load", function() {'.$js.'
	});';
	}
	
	function onAfterRender(){
		$mainframe = JFactory::getApplication();
		if($mainframe->isAdmin()) {
			return;
		}
		
		$linkofimage = $this->params->get('linkofimage','');
		$linktarget = intval($this->params->get('linktarget',0));
		if ($linktarget==1&&trim($linkofimage)!=''){
			$html = JResponse::getBody();
			$html = str_replace('</body>','<form action="'.$linkofimage.'" target="_blank" name="popupAnywhereForm"></form></body>',$html);
			$html = JResponse::setBody($html);
		}
	}
}


