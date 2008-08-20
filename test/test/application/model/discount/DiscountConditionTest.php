<?php

require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.Currency");
ClassLoader::import("application.model.order.CustomerOrder");
ClassLoader::import("application.model.user.User");
ClassLoader::import("application.model.discount.DiscountCondition");

/**
 *
 * @package test.model.discount
 * @author Integry Systems
 */
class DiscountConditionTest extends UnitTest
{
	/**
	 * Root category
	 * @var Category
	 */
	private $root;

	public function getUsedSchemas()
	{
		return array(
			'DiscountCondition',
			'CustomerOrder',
			'OrderedItem',
			'Product',
		);
	}

	public function setUp()
	{
		parent::setUp();
		$this->root = DiscountCondition::getRootNode();

		$this->usd = ActiveRecordModel::getInstanceByIDIfExists('Currency', 'USD');
		$this->usd->save();

		$this->user = User::getNewInstance('discount.condition@test');
		$this->user->save();

		$this->order = CustomerOrder::getNewInstance($this->user);
		$this->order->currency->set($this->usd);
		$this->order->save(true);

		$this->product1 = Product::getNewInstance(Category::getRootNode());
		$this->product1->setPrice('USD', 10);
		$this->product1->save();

		$this->product2 = Product::getNewInstance(Category::getRootNode());
		$this->product2->setPrice('USD', 20);
		$this->product2->save();

		ActiveRecordModel::getApplication()->getConfig()->set('INVENTORY_TRACKING', 'DISABLE');
	}

	public function testGetRootConditions()
	{
		$this->assertTrue($this->root->isExistingRecord());
	}

	public function testCreateAndRetrieve()
	{
		$condition = DiscountCondition::getNewInstance();
		$condition->save();

		$this->assertSame($this->root->getDirectChildNodes()->get(0), $condition);
	}

	public function testGlobalDiscount()
	{
		$this->order->addProduct($this->product1, 1, true);
		$this->order->addProduct($this->product2, 1, true);
		$this->order->save();

		$orderTotal = $this->order->getTotal($this->usd);

		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();

		$this->assertSame($this->order->getDiscountConditions()->get(0), $condition);

		$result = DiscountAction::getNewInstance($condition);
		$result->type->set(DiscountAction::TYPE_ORDER_DISCOUNT);
		$result->amountMeasure->set(DiscountAction::MEASURE_PERCENT);
		$result->amount->set(10);
		$result->save();

		$newTotal = $this->order->getTotal($this->usd);
		$this->assertEqual($orderTotal * 0.9, $newTotal);
	}
}

?>