<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.user.*");
ClassLoader::import("application.model.product.*");
ClassLoader::import("application.model.category.Category");

/**
 *  @author Integry Systems
 *  @package test.model.product
 */
class ProductRatingTest extends UnitTest
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
		$this->assertEqual($this->product->ratingCount->get(), 1);
		$this->assertEqual($this->product->ratingSum->get(), 6);
		$this->assertEqual($this->product->rating->get(), 6);

		$rating = ProductRating::getNewInstance($this->product);
		$rating->rating->set(4);
		$rating->user->set($this->user);
		$rating->save();

		$this->product->reload();
		$this->assertEqual($this->product->ratingCount->get(), 2);
		$this->assertEqual($this->product->ratingSum->get(), 10);
		$this->assertEqual($this->product->rating->get(), 5);
	}

	public function testRatingTypes()
	{

	}
}
?>