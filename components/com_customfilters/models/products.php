<?php
/**
 *
 * Customfilters products model
 *
 * @package		customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2012 - 2015 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *				customfilters is free software. This version may have been modified
 *				pursuant to the GNU General Public License, and as distributed
 *				it includes or is derivative of works licensed under the GNU
 *				General Public License or other free or open source software
 *				licenses.
 * @version $Id: products.php 2015-06-11 20:04:00Z sakis $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');
jimport( 'joomla.application.module.helper' );

require_once(JPATH_VM_ADMIN . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'product.php');

/**
 * Class containing the main logic of the component
 * @author sakis
 *
 */
class CustomfiltersModelProducts extends VirtueMartModelProduct
{    
	protected $context = 'com_customfilters.products';
	private $published_cf;
	public $total;
	public $vmCurrencyHelper;
	protected $componentparams;
	protected $menuparams;
	protected $moduleparams;
	protected $found_product_ids=array();
	public $vmVersion;

	/**
	 * The class constructor
	 * @since	1.0
	 * @author	Sakis Terz
	 */
	public function __construct($config = array())
	{
		$module=cftools::getModule();
		$this->menuparams=cftools::getMenuparams();
		$this->moduleparams=cftools::getModuleparams();
		$this->componentparams  = cftools::getComponentparams();
		$this->cfinputs=CfInput::getInputs();
		$this->vmVersion=VmConfig::getInstalledVersion();
		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.0
	 */
	protected function populateState($ordering = 'ordering', $direction = 'ASC')
	{
		$app = JFactory::getApplication();
		$jinput=$app->input;
		$view = $jinput->get('view','products','cmd');
		//$config = JFactory::getConfig();

		//check multi-language
		$plugin =JPluginHelper::getPlugin('system', 'languagefilter');
		$this->setState('langPlugin', $plugin);


		// List state information
		$default_limit=!empty($this->menuparams)?$this->menuparams->get('pagination_default_value','24'):VmConfig::get ('list_limit', 20);
		$limit = $app->getUserStateFromRequest('com_customfilters.products.limit', 'limit', $default_limit,'int');
		$limitstart = $jinput->get('limitstart', 0,'uint');

		//get the order by field
		$filter_order = $jinput->get('orderby',$this->filter_order,'string');
        
		//sanitize the order by
		$filter = JFilterInput::getInstance();
		$filter_order_fields=explode(',', $filter_order);

		foreach ($filter_order_fields as &$order_by_field){		    
		    $order_by_field=$filter->clean($order_by_field,'cmd');
		}
		$order_by_string=implode(',', $filter_order_fields);
		
		//check also against the allowed order by fields
		if(method_exists($this, 'checkFilterOrder'))$order_by_string = $this->checkFilterOrder ($order_by_string);

		//get the order by direction
		$this->filter_order_Dir= strtoupper($jinput->get('order', VmConfig::get('prd_brws_orderby_dir', 'ASC'),'cmd'));
		
		//sanitize Direction in case of invalid input
		if(!in_array($this->filter_order_Dir, array('ASC','DESC'))){
			$this->filter_order_Dir ='ASC';
		}

		$this->setState('list.limitstart', $limitstart);
		$this->setState('list.limit', $limit);
		$this->setState('filter_order', $order_by_string);
		$this->setState('filter_order_Dir', $this->filter_order_Dir);
	}

	/**
	 * Method to get a list of products.
	 * Overriddes the the function defined in the com_virtuemart/models/product.php.
	 *
	 * @author	Sakis Terz
	 * @return	mixed	An array of data items on success, false on failure.
	 * @since	1.0
	 */
	public function getProductListing($group = false, $nbrReturnProducts = false, $withCalc = true, $onlyPublished = true, $single = false, $filterCategory = true, $category_id = 0)
	{
		$front = true;
		$user = JFactory::getUser();
		if (!($user->authorise('core.admin','com_virtuemart') or $user->authorise('core.manage','com_virtuemart'))) {
			$onlyPublished = true;
			if ($show_prices=VmConfig::get('show_prices',1) == '0'){
				$withCalc = false;
			}
		}

		//get the published custom filters
		$this->published_cf=cftools::getCustomFilters('');
		$ids = $this->sortSearchListQuery($onlyPublished,$vmcat=false,$group,$nbrReturnProducts);
		//$products = $this->getProducts($ids, $front, $withCalc, $onlyPublished,$single);
		//return $products;
		return $ids;
	}

	/**
	 *
	 * Returns the product ids after running the filtering sql queries
	 * Overriddes the function defined in the com_virtuemart/models/product.php
	 * 
	 * @param 	boolen	$onlyPublished only the published products
	 * @param 	string	$group	indicates some predefined groups
	 * @param 	Int $nbrReturnProducts
	 * 
	 * @since	1.0
	 * 
	 * @return  array  product ids
	 * 
	 * @todo	Avoid joins if only 1 filter is selected. Just get the product id from it's table
	 */
	public function sortSearchListQuery($onlyPublished=true, $virtuemart_category_id=false, $group=false, $nbrReturnProducts=false, $langFields = array())
	{
		if($this->moduleparams->get('cf_profiler',0))$profiler=JProfiler::getInstance('application');
		if($this->moduleparams->get('cf_profiler',0))$profiler->mark('start');
		$vmCompatibility=VmCompatibility::getInstance();
		$app = JFactory::getApplication() ;
		$jinput=$app->input;
		$db=JFactory::getDbo();
		$query=$db->getQuery(true);
		$where=array();
		$where_product_ids=array();
		
		//adds/creates a logger for that extension
		cftools::addLogger();
        
		//joins initialization
		$join_prodcat=false;
		$join_prodlang=version_compare($this->vmVersion, '2.9','<');
		$joinCategory=false;
		$join_prodmnf=false;
		$joinMf=false;
		$joinPrice=false;
		$joinChildren=false;
		$joinShopper=false;
		$product_ids=array();

		//return parent or child products
		$returned_products=$this->componentparams->get('returned_products','parent');
		
		//filters from
		$filtered_products=$this->componentparams->get('filtered_products','parent');
		
		/*
		 * In case we return the parents and the filters are from child products, we should search the child products but return the parents
		 * */
		if($returned_products=='parent' && $filtered_products=='child')$searchable='child';
		else $searchable=$returned_products;
		
		//create the JRegistry with the module's params
		$resetType=$this->componentparams->get('reset_results',0);
		if($resetType==0 && empty($this->cfinputs))return;
		
		if($searchable=='child' && $returned_products=='parent')$query->select('DISTINCT SQL_CALC_FOUND_ROWS p.product_parent_id');
		else $query->select('DISTINCT SQL_CALC_FOUND_ROWS p.virtuemart_product_id');
		$query->from('#__virtuemart_products AS p');


		//stock control
		$stock=$jinput->get('virtuemart_stock',array(2),'array');
		if($stock[0]==1)$in_stock=true;
		else $in_stock=false;
		

		//----generate categories filter query---//
		if(isset($this->cfinputs['virtuemart_category_id'])){
			$vm_categories=$this->cfinputs['virtuemart_category_id'];
			$vm_categories=array_filter($vm_categories);
			if(count($vm_categories)>0 && isset($vm_categories[0])){
				JArrayHelper::toInteger($vm_categories);
				if(count($vm_categories)>0){
					$join_prodcat=true;
					$where[]=' pc.virtuemart_category_id IN ('.implode(',',$vm_categories).')';
				}
			}
		}


		//----generate manufacturers filter query---//
		if(isset($this->cfinputs['virtuemart_manufacturer_id']))$vm_manufacturers=$this->cfinputs['virtuemart_manufacturer_id'];

		if(isset($vm_manufacturers[0])){
			//set the selected manufs
			$join_prodmnf=true;
			$where[]=' p_m.virtuemart_manufacturer_id IN ('.implode(',',$vm_manufacturers).')';
		}

		//display products in specific shoppers
		$virtuemart_shoppergroup_ids =cftools::getUserShopperGroups();

		if(is_array($virtuemart_shoppergroup_ids) && $this->componentparams->get('products_multiple_shoppers',0)){
			$where[] .= '(s.`virtuemart_shoppergroup_id` IN (' . implode(',',$virtuemart_shoppergroup_ids). ') OR' . ' (s.`virtuemart_shoppergroup_id`) IS NULL )';
			$joinShopper = true;
		}

		//--general--//
		if($onlyPublished){
			$where[] = ' `p`.`published`=1';
		}

		if(!VmConfig::get('use_as_catalog',0) || $in_stock) {
			if (VmConfig::get('stockhandle','none')=='disableit_children') {
				$where[] = '(p.`product_in_stock` - p.`product_ordered` >0 OR children.`product_in_stock` - children.`product_ordered` >0)';
				$joinChildren = true;
			} else if (VmConfig::get('stockhandle','none')=='disableit') {
				$where[] = 'p.`product_in_stock` - p.`product_ordered` >0';
			}
		}

		//lookup parent or child products
		if($returned_products=='parent'){
			if($searchable=='child')$where[] = 'p.product_parent_id>0';
			else $where[] = 'p.product_parent_id=0';
		}else{
			$where[] = 'p.product_parent_id>0';
		}

		//ordering
		$groupBy = '';
		$filter_order=$this->getState('filter_order');

		// special  orders case
		switch ($this->getState('filter_order')) {
		    case 'pc.ordering,product_name':
		        $orderBy='pc.ordering,l.product_name';		        
		        $join_prodcat=true;
		        $join_prodlang=true;
		        break;
			case 'product_name':
				$orderBy='l.product_name';
				$join_prodlang=true;
				break;
			case 'product_special':
				$where[] = ' p.`product_special`="1" '; // TODO Change  to  a  individual button
				$orderBy = 'RAND()';
				break;
			case 'category_name':
				$orderBy = 'c.`category_name`';
				$join_prodcat=true;
				$joinCategory = true ;
				break;
			case 'category_description':
				$orderBy = 'c.`category_description`';
				$join_prodcat=true;
				$joinCategory = true ;
				break;
			case 'mf_name':
				$orderBy = 'm.`mf_name`';
				$join_prodmnf=true;
				$joinMf = true ;
				break;
			case 'pc.ordering':
				$orderBy = 'pc.`ordering`';
				$join_prodcat=true;
				$joinCategory = true ;
				break;
			case 'ordering': //VM versions lower to 2.0.14 use that
				$orderBy = 'pc.ordering';
				$join_prodcat=true;
				$joinCategory = true ;
				break;
			case 'product_price':
				$orderBy = 'pp.`product_price`';
				$joinPrice = true ;
				break;
			case  'created_on':
				$orderBy = 'p.`created_on`';
				break;
			case  'product_mpn':
				$orderBy = 'p.`product_mpn`';
				break;
			default ;
			if(!empty($filter_order)){
				$orderBy = $this->getState('filter_order');
			} else {
				$this->setState('filter_order_Dir','');
				$orderBy='';
			}
			break;
		}		

		//set the joins
		if($join_prodlang)$query->innerJoin('#__virtuemart_products_'.VMLANG.' AS l ON p.virtuemart_product_id=l.virtuemart_product_id');
		if($join_prodcat){
			if($returned_products=='child' || $searchable=='child')$query->innerJoin('#__virtuemart_product_categories AS pc ON pc.virtuemart_product_id=p.product_parent_id');
			else $query->innerJoin('#__virtuemart_product_categories AS pc ON pc.virtuemart_product_id=p.virtuemart_product_id');
		}
		if($joinCategory)$query->leftJoin('#__virtuemart_categories_'.VMLANG.' as c ON c.`virtuemart_category_id` = pc.`virtuemart_category_id`');

		if($joinShopper){
			$query->leftJoin('`#__virtuemart_product_shoppergroups` ON p.`virtuemart_product_id` = `#__virtuemart_product_shoppergroups`.`virtuemart_product_id`');
			$query->leftJoin('`#__virtuemart_shoppergroups` as s ON s.`virtuemart_shoppergroup_id` = `#__virtuemart_product_shoppergroups`.`virtuemart_shoppergroup_id`');
		}

		if($join_prodmnf){
			if($returned_products=='child' || $searchable=='child')$query->innerJoin('#__virtuemart_product_manufacturers  AS p_m ON p_m.virtuemart_product_id=p.product_parent_id');
			else $query->innerJoin('#__virtuemart_product_manufacturers  AS p_m ON p_m.virtuemart_product_id=p.virtuemart_product_id');
		}
		if($joinMf)$query->leftJoin('#__virtuemart_manufacturers_'.VMLANG.' as m ON m.`virtuemart_manufacturer_id` = p_m.`virtuemart_manufacturer_id`');

		if($joinPrice)$query->leftJoin('`#__virtuemart_product_prices` as pp ON p.`virtuemart_product_id` = pp.`virtuemart_product_id` ');

		if ($joinChildren) $query->leftJoin('`#__virtuemart_products` children ON p.`virtuemart_product_id` = children.`product_parent_id`');

		// List state information
		$limit =$this->getState('list.limit',5);
		$limitstart=$this->getState('list.limitstart',0);
		
		$query->order($db->escape($orderBy.' '.$this->getState('filter_order_Dir')));
		
		if(count($where)>0)$query->where(implode(' AND ', $where));
		
		//fetch the product ids
		try{
    		$db->setQuery($query,$limitstart,$limit); 
    		$db->query();
    		$product_ids =$db->loadColumn(); 
		}
		catch (RuntimeException $e){
		    JLog::add(
                sprintf('Failed to return products: %s',$e->getMessage()),
                	JLog::ERROR,
		           'customfilters'
                );
		}
		
		//count the results
		try{
    		$db->setQuery('SELECT FOUND_ROWS()');
    		$this->total=$db->loadResult();
		}		
		catch(RuntimeException $e){
		    JLog::add(
		        sprintf('Failed to count products: %s',$e->getMessage()),
		        JLog::ERROR,
		        'customfilters'
		    );
		}		
		
		//reset the query again. Without that could create an issue in plugins, modules
		$db->query(true);
		$app->setUserState("com_customfilters.product_ids",$product_ids);
		if(!empty($profiler))$profiler->mark('Finish Filtering/Search');

		return $product_ids;
	}	

	/**
	 * Get the Order By Select List
	 * Overrides the function originaly written by Kohl Patrick (Virtuemart parent class)
	 *
	 * @author 	Sakis Terz
	 * @access	public
	 * @param 	int	    The category id
	 * 
	 * @return 	array	the orderBy HTML List and the manufacturers list
	 **/
	public function getOrderByList($virtuemart_category_id=false) 
	{
	    if ($this->_pagination == null) {
	        require_once JPATH_COMPONENT.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'cfpagination.php';
	        
	        $limit = $this->getState('list.limit');
	        $limitstart=$this->getState('list.limitstart',0);
	        $this->_pagination = new cfPagination($this->total , $limitstart, $limit );
	    }
	    
	    return $this->_pagination->getOrderByList($virtuemart_category_id=false, $default_order_by=$this->filter_order, $order_by=$this->getState('filter_order'), $order_dir=$this->getState('filter_order_Dir'));
	}	

	/**
	 * Loads the pagination
	 *
	 * @author 	   Sakis Terz
	 * @since	 1.0
	 * @return   cfPagination object  
	 */
	public function getPagination($total=0,$limitStart=0,$limit=0) 
	{
		if ($this->_pagination == null) {
			require_once JPATH_COMPONENT.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'cfpagination.php';

			$limit = $this->getState('list.limit');
			$limitstart=$this->getState('list.limitstart',0);
			$this->_pagination = new cfPagination($this->total , $limitstart, $limit );
		}
		
		return $this->_pagination;
	}
}
