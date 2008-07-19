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
		$schema->registerField(new ARField('ip', ARInteger::instance()));
		$schema->registerField(new ARField('dateCreated', ARDateTime::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Product $product, ProductRatingType $type = null, User $user = null)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->product->set($product);

		if ($type && is_null($type->getID()))
		{
			$type = null;
		}
		$instance->ratingType->set($type);

		if ($user && $user->isAnonymous())
		{
			$user = null;
		}
		$instance->user->set($user);

		return $instance;
	}

	public function delete()
	{
		parent::delete();

		$f = new ARUpdateFilter();
		$f->addModifier('ratingSum', new ARExpressionHandle('ratingSum-' . $this->rating->get()));
		$f->addModifier('ratingCount', new ARExpressionHandle('ratingCount-1'));
		$f->addModifier('rating', new ARExpressionHandle('ratingSum/ratingCount'));

		$this->updateRatings($f);
	}

	protected function insert()
	{
		self::beginTransaction();

		parent::insert();
		$this->updateTimeStamp('dateCreated');

		$summary = ProductRatingSummary::getInstance($this->product->get(), $this->ratingType->get());
		$summary->save();

		$f = new ARUpdateFilter();
		$f->addModifier('ratingSum', new ARExpressionHandle('ratingSum+' . $this->rating->get()));
		$f->addModifier('ratingCount', new ARExpressionHandle('ratingCount+1'));
		$f->addModifier('rating', new ARExpressionHandle('ratingSum/ratingCount'));

		$this->updateRatings($f);

		self::commit();
	}

	protected function update()
	{
		self::beginTransaction();

		$ratingDiff = $this->rating->get() - $this->rating->getInitialValue();

		$f = new ARUpdateFilter();
		$f->addModifier('ratingSum', new ARExpressionHandle('ratingSum+(' . $ratingDiff . ')'));
		$f->addModifier('rating', new ARExpressionHandle('ratingSum/ratingCount'));

		$this->updateRatings($f);

		parent::update();

		self::commit();
	}

	private function updateRatings(ARUpdateFilter $f)
	{
		$this->product->get()->updateRecord(clone $f);

		$summary = ProductRatingSummary::getInstance($this->product->get(), $this->ratingType->get());
		if ($summary->getID())
		{
			$summary->updateRecord(clone $f);
		}

		if ($this->review->get() && !$this->review->get()->isDeleted())
		{
			$this->review->get()->updateRecord(clone $f);
		}
	}
}

?>