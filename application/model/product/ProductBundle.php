<?php


/**
 * Assigns a bundled product to a parent product (bundle container)
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductBundle extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryForeignKeyField("productID", "Product", "ID", "Product;
		$schema->registerField(new ARPrimaryForeignKeyField("relatedProductID", "Product", "ID", "Product;
		public $position",  ARInteger::instance()));
		public $count",  ARFloat::instance()));
	}

	/*####################  Static method implementations ####################*/

	/**
	 * Get related product active record by ID
	 *
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 *
	 * @return ProductRelationshipGroup
	 */
	public static function getInstance(Product $product, Product $relatedProduct, $loadRecordData = false, $loadReferencedRecords = false)
	{
		$recordID = array(
			'productID' => $product->getID(),
			'relatedProductID' => $relatedProduct->getID()
		);

		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	/**
	 * Creates a new bundle relation
	 *
	 * @param Product $product
	 * @param Product $relatedProduct
	 *
	 * @return ProductRelationship
	 */
	public static function getNewInstance(Product $product, Product $related)
	{
		if(null == $product || null == $related || $product === $related || $product->getID() == $related->getID())
		{
			return null;
		}

		$relationship = new self();

		$relationship->product = $product;
		$relationship->relatedProduct = $related;

		return $relationship;
	}

	/**
	 * Get relationships set
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	/**
	 * Gets an existing relationship instance
	 *
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return ProductRelationship
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}

	/*####################  Value retrieval and manipulation ####################*/

	public static function hasRelationship(Product $product, Product $relatedToProduct)
	{
		$recordID = array(
			'productID' => $product->getID(),
			'relatedProductID' => $relatedToProduct->getID()
		);

		if(self::retrieveFromPool(__CLASS__, $recordID)) return true;
		if(self::objectExists(__CLASS__, $recordID)) return true;

		return false;
	}

	public function getCount()
	{
		return $this->count->get() ? $this->count->get() : 1;
	}

	public function getProduct()
	{
		return $this->product->get();
	}

	/*####################  Saving ####################*/

	protected function insert()
	{
	  	// get max position
	  	$f = new ARSelectFilter(self::getFilter($this->product->get())->getCondition());
	  	$f->setOrder(new ARFieldHandle(__CLASS__, "position"), 'DESC');
	  	$f->setLimit(1);
	  	$rec = ActiveRecord::getRecordSetArray(__CLASS__, $f);
		$position = (is_array($rec) && count($rec) > 0) ? $rec[0]['position'] + 1 : 0;
		$this->position = $position;

		return parent::insert();
	}

	/*####################  Get related objects ####################*/

	/**
	 * Get product relationships
	 *
	 * @param Product $product
	 * @return ARSet
	 */
	public static function getBundledProductSet(Product $product, $loadReferencedRecords = array('RelatedProduct' => 'Product'))
	{
		return self::getRecordSet(self::getFilter($product), $loadReferencedRecords);
	}

	/**
	 * Get product relationships
	 *
	 * @param Product $product
	 * @return array
	 */
	public static function getBundledProductArray(Product $product, $loadReferencedRecords = array('RelatedProduct' => 'Product', 'ProductImage'))
	{
		return parent::getRecordSetArray(__CLASS__, self::getFilter($product), $loadReferencedRecords);
	}

	public static function getTotalBundlePrice(Product $product, Currency $currency)
	{
		$products = new ARSet();
		$bundle = self::getBundledProductSet($product);
		foreach ($bundle as $item)
		{
			$products->add($item->relatedProduct->get());
		}
		ProductPrice::loadPricesForRecordSet($products);

		$total = 0;
		foreach ($bundle as $item)
		{
			$itemTotal = $item->relatedProduct->get()->getPrice($currency) * $item->getCount();
			$total += $itemTotal;
		}

		return $total;
	}

	private static function getFilter(Product $product)
	{
		$filter = new ARSelectFilter(new EqualsCond(new ARFieldHandle(__CLASS__, "productID"), $product->getID()));
		$filter->setOrder(new ARFieldHandle(__CLASS__, "position"), 'ASC');

		return $filter;
	}
}

?>