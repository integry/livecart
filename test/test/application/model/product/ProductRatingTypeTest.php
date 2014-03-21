<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';


/**
 *  @author Integry Systems
 *  @package test.model.product
 */
class ProductRatingTypeTest extends LiveCartTest
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
		ActiveRecordModel::executeUpdate('DELETE FROM ProductRatingType');
	}

	public function testProductRatingTypes()
	{
		$subCategory = Category::getNewInstance(Category::getRootNode());
		$subCategory->save();

		$product = Product::getNewInstance($subCategory, 'test');
		$product->save();

		$rootType = ProductRatingType::getNewInstance(Category::getRootNode());
		$rootType->save();

		$subType = ProductRatingType::getNewInstance($subCategory);
		$subType->save();

		$types = ProductRatingType::getProductRatingTypes($product);

		$this->assertEqual($types->count(), 2);

		// parent category types should go first
		$this->assertSame($types->shift(), $rootType);
		$this->assertSame($types->get(1), $subType);
	}

	public function testPositions()
	{
		$type1 = ProductRatingType::getNewInstance(Category::getRootNode());
		$type1->save();

		$type2 = ProductRatingType::getNewInstance(Category::getRootNode());
		$type2->save();

		$this->assertEqual($type1->position, 1);
		$this->assertEqual($type2->position, 2);
	}
}
?>