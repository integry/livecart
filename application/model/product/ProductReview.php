<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.user.User');

/**
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class ProductReview extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField('ID', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField('productID', 'Product', 'ID', null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField('userID', 'User', 'ID', null, ARInteger::instance()));
		$schema->registerField(new ARField('isEnabled', ARBool::instance()));
		$schema->registerField(new ARField('dateCreated', ARDateTime::instance()));
		$schema->registerField(new ARField('ip', ARInteger::instance()));
		$schema->registerField(new ArField('ratingSum', ARInteger::instance()));
		$schema->registerField(new ArField('ratingCount', ARInteger::instance()));
		$schema->registerField(new ArField('rating', ARFloat::instance(8)));
		$schema->registerField(new ARField('nickname', ARVarchar::instance(100)));
		$schema->registerField(new ARField('title', ARVarchar::instance(255)));
		$schema->registerField(new ARField('text', ARText::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Product $product, User $user)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->product->set($product);

		if ($user && $user->isAnonymous())
		{
			$user = null;
		}
		$instance->user->set($user);

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

	protected function insert()
	{
		$this->updateProductCounter();
		$res = parent::insert();
		$this->updateTimeStamp('dateCreated');
		return $res;
	}

	private function updateProductCounter($increase = true)
	{
		$update = new ARUpdateFilter();
		$update->addModifier('reviewCount', new ARExpressionHandle('reviewCount ' . ($increase ? '+' : '-' ) . ' 1'));
		$this->product->get()->updateRecord($update);
	}
}

?>