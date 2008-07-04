<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.category.ProductRatingType');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.product.ProductReview');
ClassLoader::import('application.model.user.User');

/**
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class ProductRating extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField('ID', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField('productID', 'Product', 'ID', null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField('userID', 'User', 'ID', null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField('reviewID', 'ProductReview', 'ID', null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField('ratingTypeID', 'ProductRatingType', 'ID', null, ARInteger::instance()));
		$schema->registerField(new ARField('rating', ARInteger::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Product $product)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->product->set($product);
		return $instance;
	}

	protected function insert()
	{
		self::beginTransaction();

		parent::insert();

		$summary = ProductRatingSummary::getInstance($this->product->get(), $this->ratingType->get());
		$summary->save();

		$f = new ARUpdateFilter();
		$f->addModifier('ratingSum', new ARExpressionHandle('ratingSum+' . $this->rating->get()));
		$f->addModifier('ratingCount', new ARExpressionHandle('ratingCount+1'));
		$f->addModifier('rating', new ARExpressionHandle('ratingSum/ratingCount'));

		$summary->updateRecord(clone $f);
		$this->product->get()->updateRecord(clone $f);

		self::commit();
	}
}

?>
