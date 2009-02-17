<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.user.*");
ClassLoader::import("application.model.product.*");
ClassLoader::import("application.model.category.Category");

/**
 *  @author Integry Systems
 *  @package test.model.product
 */
class ProductReviewTest extends LiveCartTest
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
		$review = ProductReview::getNewInstance($this->product, $this->user);
		$review->save();

		$rating = ProductRating::getNewInstance($this->product);
		$rating->rating->set(6);
		$rating->review->set($review);
		$rating->save();

		$review->reload();

		$this->assertEqual($review->ratingCount->get(), 1);
		$this->assertEqual($review->ratingSum->get(), 6);
		$this->assertEqual($review->rating->get(), 6);
	}

	public function testRatingTypes()
	{
		$review = ProductReview::getNewInstance($this->product, $this->user);
		$review->save();

		$type = ProductRatingType::getNewInstance(Category::getRootNode());
		$type->save();

		$rating = ProductRating::getNewInstance($this->product, $type);
		$rating->rating->set(6);
		$rating->review->set($review);
		$rating->save();

		$type2 = ProductRatingType::getNewInstance(Category::getRootNode());
		$type2->save();

		$rating = ProductRating::getNewInstance($this->product, $type2);
		$rating->rating->set(4);
		$rating->review->set($review);
		$rating->save();

		$review->reload();
		$this->assertEqual($review->ratingCount->get(), 2);
		$this->assertEqual($review->ratingSum->get(), 10);
		$this->assertEqual($review->rating->get(), 5);
	}


	public function testDelete()
	{
		$type = ProductRatingType::getNewInstance(Category::getRootNode());
		$type->save();
		$type2 = ProductRatingType::getNewInstance(Category::getRootNode());
		$type2->save();

		for ($k = 0; $k <= 1; $k++)
		{
			$review = ProductReview::getNewInstance($this->product, $this->user);
			$review->save();

			$rating = ProductRating::getNewInstance($this->product, $type);
			$rating->rating->set(6 + $k);
			$rating->review->set($review);
			$rating->save();

			$rating = ProductRating::getNewInstance($this->product, $type2);
			$rating->rating->set(4 + $k);
			$rating->review->set($review);
			$rating->save();
		}

		$this->product->reload();
		$this->assertEqual($this->product->ratingCount->get(), 4);
		$this->assertEqual($this->product->ratingSum->get(), 22);
		$this->assertEqual($this->product->rating->get(), 5.5);

		// delete last review
		$review->delete();

		$this->product->reload();
		$this->assertEqual($this->product->ratingCount->get(), 2);
		$this->assertEqual($this->product->ratingSum->get(), 10);
		$this->assertEqual($this->product->rating->get(), 5);

		// check rating summaries
		$summary = ProductRatingSummary::getInstance($this->product, $type2);
		$this->assertEqual($summary->rating->get(), 4);
	}
}
?>