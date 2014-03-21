<?php


/**
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductRatingSummary extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		public $ID;
		public $productID', 'Product', 'ID', null, ARInteger::instance()));
		public $ratingTypeID', 'ProductRatingType', 'ID', null, ARInteger::instance()));
		public $ratingSum;
		public $ratingCount;
		public $rating', ARFloat::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Product $product, ProductRatingType $type = null)
	{
		$instance = new self();
		$instance->product = $product;
		$instance->ratingType = $type;
		return $instance;
	}

	public static function getInstance(Product $product, ProductRatingType $type = null)
	{
		$field = new ARFieldHandle(__CLASS__, 'ratingTypeID');
		$summary = $product->getRelatedRecordSet(__CLASS__, new ARSelectFilter($type ? new EqualsCond($field, $type->getID()) : new IsNullCond($field)));
		if ($summary->count())
		{
			return $summary->shift();
		}
		else
		{
			return self::getNewInstance($product, $type);
		}
	}

	public static function getProductRatingsArray(Product $product)
	{
		$f = new ARSelectFilter();
		$f->orderBy('ProductRatingType.position');
		return $product->getRelatedRecordSetArray('ProductRatingSummary', $f, array('ProductRatingType'));
	}
}

?>