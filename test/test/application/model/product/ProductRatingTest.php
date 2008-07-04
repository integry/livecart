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
		$type = ProductRatingType::getNewInstance(Category::getRootNode());
		$type->save();

		$rating = ProductRating::getNewInstance($this->product, $type);
		$rating->rating->set(5);
		$rating->save();

		$this->product->reload();
		$this->assertEqual($this->product->ratingCount->get(), 1);
		$this->assertEqual($this->product->ratingSum->get(), 5);
		$this->assertEqual($this->product->rating->get(), 5);

		ActiveRecord::clearPool();
		$summary = ProductRatingSummary::getInstance($this->product, $type);
		$summary->reload();
		ActiveRecord::executeUpdate('UPDATE ProductRatingSummary SET ratingSum = ratingSum+5, ratingCount=1, rating=ratingSum/ratingCount WHERE (ProductRatingSummary.ID=18)');
		var_dump(ActiveRecord::getDataBySQL('SELECT * FROM ProductRatingSummary WHERE ID=' . $summary->getID()));
		var_dump($summary->isExistingRecord());
		$this->assertEqual($summary->ratingCount->get(), 1);
		$this->assertEqual($summary->ratingSum->get(), 5);
		$this->assertEqual($summary->rating->get(), 5);
	}
}
?>