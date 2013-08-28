<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';


/**
 *  @author Integry Systems
 *  @package test.model.product
 */
class ProductRatingTest extends LiveCartTest
{
	private $product;
	private $user;

	public function getUsedSchemas()
	{
		return array(
			'Product',
			'ProductRating',
			'ProductRatingType',
			'ProductRatingSummary',
			'ProductReview',
		);
	}

	public function setUp()
	{
		parent::setUp();

		$this->product = Product::getNewInstance(Category::getRootNode(), 'test');
		$this->product->save();

		$this->user = User::getNewInstance('sdfsdfsd@ProductRatingTest.com');
		$this->user->save();
	}

	public function testSimpleRating()
	{
		$rating = ProductRating::getNewInstance($this->product);
		$rating->rating->set(6);
		$rating->save();

		$this->product->reload();
		$this->assertEqual($this->product->ratingCount, 1);
		$this->assertEqual($this->product->ratingSum, 6);
		$this->assertEqual($this->product->rating, 6);

		$rating = ProductRating::getNewInstance($this->product);
		$rating->rating->set(4);
		$rating->user->set($this->user);
		$rating->save();

		$this->product->reload();
		$this->assertEqual($this->product->ratingCount, 2);
		$this->assertEqual($this->product->ratingSum, 10);
		$this->assertEqual($this->product->rating, 5);
	}

	public function testSimpleRatingWithNullRatingType()
	{
		$defaultRatingType = ProductRatingType::getDefaultRatingType();

		$rating = ProductRating::getNewInstance($this->product, $defaultRatingType);
		$rating->rating->set(6);
		$rating->save();

		$this->product->reload();
		$this->assertEqual($this->product->ratingCount, 1);
		$this->assertEqual($this->product->ratingSum, 6);
		$this->assertEqual($this->product->rating, 6);

		$rating = ProductRating::getNewInstance($this->product, $defaultRatingType);
		$rating->rating->set(4);
		$rating->user->set($this->user);
		$rating->save();

		$this->product->reload();
		$this->assertEqual($this->product->ratingCount, 2);
		$this->assertEqual($this->product->ratingSum, 10);
		$this->assertEqual($this->product->rating, 5);
	}

	public function testRatingTypes()
	{
		$type = ProductRatingType::getNewInstance(Category::getRootNode());
		$type->save();

		$rating = ProductRating::getNewInstance($this->product, $type);
		$rating->rating->set(6);
		$rating->save();

		$this->product->reload();
		$this->assertEqual($this->product->ratingCount, 1);
		$this->assertEqual($this->product->ratingSum, 6);
		$this->assertEqual($this->product->rating, 6);

		ActiveRecord::clearPool();
		$summary = ProductRatingSummary::getInstance($this->product, $type);
		$summary->reload();

		$this->assertEqual($summary->ratingCount, 1);
		$this->assertEqual($summary->ratingSum, 6);
		$this->assertEqual($summary->rating, 6);

		$type2 = ProductRatingType::getNewInstance(Category::getRootNode());
		$type2->save();

		$rating = ProductRating::getNewInstance($this->product, $type2);
		$rating->rating->set(4);
		$rating->save();

		$this->product->reload();
		$this->assertEqual($this->product->ratingCount, 2);
		$this->assertEqual($this->product->ratingSum, 10);
		$this->assertEqual($this->product->rating, 5);

		ActiveRecord::clearPool();
		$summary = ProductRatingSummary::getInstance($this->product, $type2);
		$summary->reload();

		$this->assertEqual($summary->ratingCount, 1);
		$this->assertEqual($summary->ratingSum, 4);
		$this->assertEqual($summary->rating, 4);
	}

	public function testRatingDelete()
	{
		// first rating
		$rating = ProductRating::getNewInstance($this->product);
		$rating->rating->set(6);
		$rating->save();

		// second rating
		$rating = ProductRating::getNewInstance($this->product);
		$rating->rating->set(4);
		$rating->save();

		$rating->delete();

		$this->product->reload();
		$this->assertEqual($this->product->ratingCount, 1);
		$this->assertEqual($this->product->ratingSum, 6);
		$this->assertEqual($this->product->rating, 6);
	}

	public function testRatingUpdate()
	{
		// initial rating
		$rating = ProductRating::getNewInstance($this->product);
		$rating->rating->set(6);
		$rating->save();

		// change rating
		$rating->rating->set(8);
		$rating->save();

		$this->product->reload();
		$this->assertEqual($this->product->ratingCount, 1);
		$this->assertEqual($this->product->ratingSum, 8);
		$this->assertEqual($this->product->rating, 8);
	}
}

?>