<?php


/**
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductReview extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);


		public $ID;
		public $productID', 'Product', 'ID', null, ARInteger::instance()));
		public $userID', 'User', 'ID', null, ARInteger::instance()));
		public $isEnabled', ARBool::instance()));
		public $dateCreated', ARDateTime::instance()));
		public $ip;
		public $ratingSum;
		public $ratingCount;
		public $rating', ARFloat::instance()));
		public $nickname', ARVarchar::instance()));
		public $title', ARVarchar::instance()));
		public $text', ARText::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Product $product, User $user)
	{
		$instance = new self();
		$instance->product = $product;

		if ($user && $user->isAnonymous())
		{
			$user = null;
		}
		$instance->user = $user;

		return $instance;
	}

	/**
	 * Removes a product review from a database
	 */
	public function delete()
	{
		// reduce product review count
		$this->updateProductCounter(false);

		// update ratings
		foreach ($this->getRelatedRecordSet('ProductRating') as $rating)
		{
			$rating->delete();
		}

		return parent::delete();
	}

	public function beforeCreate()
	{
		$this->updateProductCounter();
		$this->dateCreated = new ARSerializableDateTime());

	}

	private function updateProductCounter($increase = true)
	{
		$update = new ARUpdateFilter();
		$update->addModifier('reviewCount', new ARExpressionHandle('reviewCount ' . ($increase ? '+' : '-' ) . ' 1'));
		$this->product->updateRecord($update);
	}
}

?>