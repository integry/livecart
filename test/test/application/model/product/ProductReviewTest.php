<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.user.*");
ClassLoader::import("application.model.product.*");
ClassLoader::import("application.model.category.Category");

/**
 *  @author Integry Systems
 *  @package test.model.product
 */
class ProductReviewTest extends UnitTest
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
}
?>