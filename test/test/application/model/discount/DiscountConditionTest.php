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

	public function testBasicRestrictions()
	{
		$condition = DiscountCondition::getNewInstance();
		$condition->save();

		// no conditions, because not enabled
		$this->assertEquals(count($this->order->getDiscountConditions()), 0);

		// enable condition - should be available now
		$condition->isEnabled->set(true);
		$condition->save();
		$this->assertEquals(count($this->order->getDiscountConditions()), 1);

		// set time restriction in future - no longer available
		$condition->validFrom->set(time() + 100);
		$condition->validTo->set(time() + 200);
		$condition->save();
		$this->assertEquals(count($this->order->getDiscountConditions()), 0);

		// set time restriction in present - available again
		$condition->validFrom->set(time() - 100);
		$condition->validTo->set(time() + 100);
		$condition->save();
		$this->assertEquals(count($this->order->getDiscountConditions()), 1);
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

		$conditions = $this->order->getDiscountConditions();
		$this->assertEqual($conditions[0]['ID'], $condition->getID());

		$result = DiscountAction::getNewInstance($condition);
		$result->type->set(DiscountAction::TYPE_ORDER_DISCOUNT);
		$result->amountMeasure->set(DiscountAction::MEASURE_PERCENT);
		$result->amount->set(10);
		$result->save();

		$actions = $this->order->getDiscountActions();
		$this->assertEqual($actions->get(0)->getID(), $result->getID());

		$discounts = $this->order->getCalculatedDiscounts();
		$this->assertEqual($discounts->size(), 1);

		return;

		$newTotal = $this->order->getTotal($this->usd);
		$this->assertEqual($orderTotal * 0.9, $newTotal);
	}
}

?>