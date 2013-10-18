<?php


/**
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductRating extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		public $ID;
		public $productID', 'Product', 'ID', null, ARInteger::instance()));
		public $userID', 'User', 'ID', null, ARInteger::instance()));
		public $reviewID', 'ProductReview', 'ID', null, ARInteger::instance()));
		public $ratingTypeID', 'ProductRatingType', 'ID', null, ARInteger::instance()));
		public $rating;
		public $ip;
		public $dateCreated', ARDateTime::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Product $product, ProductRatingType $type = null, User $user = null)
	{
		$instance = new self();
		$instance->product = $product;

		if ($type && is_null($type->getID()))
		{
			$type = null;
		}
		$instance->ratingType = $type;

		if ($user && $user->isAnonymous())
		{
			$user = null;
		}
		$instance->user = $user;

		return $instance;
	}

	public function delete()
	{
		parent::delete();

		$f = new ARUpdateFilter();
		$f->addModifier('ratingSum', new ARExpressionHandle('ratingSum-' . $this->rating));
		$f->addModifier('ratingCount', new ARExpressionHandle('ratingCount-1'));
		$f->addModifier('rating', new ARExpressionHandle('ratingSum/ratingCount'));

		$this->updateRatings($f);
	}

	public function beforeCreate()
	{
		self::beginTransaction();

		$this->dateCreated = new ARSerializableDateTime());
		parent::insert();

		$summary = ProductRatingSummary::getInstance($this->product, $this->ratingType);
		$summary->save();

		$f = new ARUpdateFilter();
		$f->addModifier('ratingSum', new ARExpressionHandle('ratingSum+' . $this->rating));
		$f->addModifier('ratingCount', new ARExpressionHandle('ratingCount+1'));
		$f->addModifier('rating', new ARExpressionHandle('ratingSum/ratingCount'));

		$this->updateRatings($f);

		self::commit();
	}

	protected function update()
	{
		self::beginTransaction();

		$ratingDiff = $this->rating - $this->rating->getInitialValue();

		$f = new ARUpdateFilter();
		$f->addModifier('ratingSum', new ARExpressionHandle('ratingSum+(' . $ratingDiff . ')'));
		$f->addModifier('rating', new ARExpressionHandle('ratingSum/ratingCount'));

		$this->updateRatings($f);

		parent::update();

		self::commit();
	}

	private function updateRatings(ARUpdateFilter $f)
	{
		$this->product->updateRecord(clone $f);

		$summary = ProductRatingSummary::getInstance($this->product, $this->ratingType);
		if ($summary->getID())
		{
			$summary->updateRecord(clone $f);
		}

		if ($this->review && !$this->review->isDeleted())
		{
			$this->review->updateRecord(clone $f);
		}
	}
}

?>