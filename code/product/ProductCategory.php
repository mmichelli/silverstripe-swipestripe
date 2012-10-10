<?php
/**
 * Represents a Product category, Products can be added to many categories and they 
 * can have a ProductCategory as a parent in the site tree. 
 * 
 * @author Frank Mullenger <frankmullenger@gmail.com>
 * @copyright Copyright (c) 2011, Frank Mullenger
 * @package swipestripe
 * @subpackage product
 */
class ProductCategory extends Page {

  public static $singular_name = 'Product Category';
  public static $plural_name = 'Product Categories';

  /**
   * Many many relations for a ProductCategory
   * 
   * @var Array
   */
	public static $many_many = array(
    'Products' => 'Product'
  );
  
  public static $many_many_extraFields = array(
		'Products' => array(
			'ProductOrder' => 'Int'
    )
  );
  
  /**
   * Summary fields for viewing categories in the CMS
   * 
   * @var Array
   */
  public static $summary_fields = array(
	  'Title' => 'Name',
    'MenuTitle' => 'Menu Title'
	);
    
	/**
	 * Can add products to the category straight from the ProductCategory page
	 * TODO remove this, its not useful. And change the direction of the many_many relation so that patched version of CTF not needed
	 * 
	 * @see Page::getCMSFields()
	 * @return FieldList
	 */
	function getCMSFields() {
    $fields = parent::getCMSFields();

    if ($warning = ShopConfig::licence_key_warning()) {
      $fields->addFieldToTab('Root.Main', new LiteralField('SwipeStripeLicenseWarning', 
        '<p class="message warning">'.$warning.'</p>'
      ), 'Title');
    }
    
    return $fields;
	}
}

/**
 * Controller to display a ProductCategory and retrieve its Products. 
 * 
 * @author Frank Mullenger <frankmullenger@gmail.com>
 * @copyright Copyright (c) 2011, Frank Mullenger
 * @package swipestripe
 * @subpackage product
 */
class ProductCategory_Controller extends Page_Controller {
  
  /**
   * Set number of products per page displayed in ProductCategory pages
   * 
   * @var Int
   */
  public static $products_per_page = 12;

  /**
   * Set how the products are ordered on ProductCategory pages
   * 
   * @see ProductCategory_Controller::Products()
   * @var String Suitable for inserting in ORDER BY clause
   */
  public static $products_ordered_by = "\"ProductCategory_Products\".\"ProductOrder\" DESC";
  
	/**
   * Include some CSS.
   * 
   * @see Page_Controller::init()
   */
  function init() {
    parent::init();
    Requirements::css('swipestripe/css/Shop.css');
  }

  /**
   * Get Products that have this ProductCategory set or have this ProductCategory as a parent in site tree.
   * Supports pagination.
   * 
   * @see Page_Controller::Products()
   * @return FieldList
   */  
  public function Products() {

    if(!isset($_GET['start']) || !is_numeric($_GET['start']) || (int)$_GET['start'] < 1) $_GET['start'] = 0;
      
    $start = (int)$_GET['start'];
    $limit = self::$products_per_page;
    $orderBy = self::$products_ordered_by;

    //TODO need to change to PaginatedList

    $products = Product::get()
      ->where("\"ProductCategory_Products\".\"ProductCategoryID\" = '{$this->ID}' OR \"ParentID\" = '{$this->ID}'")
      ->sort($orderBy)
      ->leftJoin('ProductCategory_Products', "\"ProductCategory_Products\".\"ProductID\" = \"SiteTree\".\"ID\"")
      ->limit($limit);

    $this->extend('updateCategoryProducts', $products);

    return $products;
  }

}


class ProductCategory_Products extends DataObject {

  public static $db = array(
    'ProductOrder' => 'Int'
  );

  public static $has_one = array(
    'ProductCategory' => 'ProductCategory',
    'Product' => 'Product'
  );
}

