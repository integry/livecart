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
		ActiveRecord::executeUpdate('DELETE FROM DiscountCondition');
		ActiveRecord::executeUpdate('DELETE FROM DiscountAction');
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
		$this->order->addProduct($this->product1, 1, true);

		$condition = DiscountCondition::getNewInstance();
		$condition->save();

		// no conditions, because not enabled
		$this->assertEquals(count($this->order->getDiscountConditions(true)), 0);

		// enable condition - should be available now
		$condition->isEnabled->set(true);
		$condition->save();
		$this->assertEquals(count($this->order->getDiscountConditions(true)), 1);

		// set time restriction in future - no longer available
		$condition->validFrom->set(time() + 100);
		$condition->validTo->set(time() + 200);
		$condition->save();
		$this->assertEquals(count($this->order->getDiscountConditions(true)), 0);

		// set time restriction in present - available again
		$condition->validFrom->set(time() - 100);
		$condition->validTo->set(time() + 100);
		$condition->save();
		$this->assertEquals(count($this->order->getDiscountConditions(true)), 1);
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

		$conditions = $this->order->getDiscountConditions(true);
		$this->assertEqual($conditions[0]['ID'], $condition->getID());

		$result = DiscountAction::getNewInstance($condition);
		$result->isEnabled->set(true);
		$result->type->set(DiscountAction::TYPE_ORDER_DISCOUNT);
		$result->amountMeasure->set(DiscountAction::MEASURE_PERCENT);
		$result->amount->set(10);
		$result->save();

		$actions = $this->order->getDiscountActions(true);
		$this->assertEqual($actions->get(0)->getID(), $result->getID());

		$discounts = $this->order->getCalculatedDiscounts();
		$this->assertEqual($discounts->size(), 1);

		$newTotal = $this->order->getTotal($this->usd);

		// uuhh.. Failed asserting that <double:27> matches expected value <double:27>.
		$this->assertEqual((string)($orderTotal * 0.9), (string)$newTotal);
	}

	public function testRecordCount()
	{
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();

		$this->assertEquals(0, (int)$condition->recordCount->get());

		$record = DiscountConditionRecord::getNewInstance($condition, $this->product1);
		$record->save();
		$this->assertEquals(1, $condition->recordCount->get());

		$record = DiscountConditionRecord::getNewInstance($condition, $this->product2);
		$record->save();
		$this->assertEquals(2, $condition->recordCount->get());

		$record->delete();
		$this->assertEquals(1, $condition->recordCount->get());
	}

	public function testIsProductMatching()
	{
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();

		$record = DiscountConditionRecord::getNewInstance($condition, $this->product1);
		$record->save();

		$condition->loadAll();

		$this->assertFalse($condition->isProductMatching($this->product2));
		$this->assertTrue($condition->isProductMatching($this->product1));

		// check matching by manufacturer
		$manufacturer = Manufacturer::getNewInstance('testing');
		$manufacturer->save();
		$this->product2->manufacturer->set($manufacturer);

		$record = DiscountConditionRecord::getNewInstance($condition, $manufacturer);
		$record->save();

		$condition->loadAll();

		$this->assertTrue($condition->isProductMatching($this->product2));

		// check matching by category
		$newCategory = Category::getNewInstance(Category::getRootNode());
		$newCategory->save();
		$newSubCategory = Category::getNewInstance($newCategory);
		$newSubCategory->save();

		$newProduct = Product::getNewInstance($newSubCategory);
		$newProduct->save();

		$record = DiscountConditionRecord::getNewInstance($condition, $newCategory);
		$record->save();

		$this->assertFalse($condition->isProductMatching($newProduct));
		$condition->loadAll();
		$this->assertTrue($condition->isProductMatching($newProduct));

		// sub-condition
		$sub = DiscountCondition::getNewInstance($condition);
		$sub->isEnabled->set(true);
		$sub->save();

		$condition->isAllSubconditions->set(true);
		$condition->save();
		$condition->loadAll();

		$manufacturer = Manufacturer::getNewInstance('new one');
		$manufacturer->save();

		$record = DiscountConditionRecord::getNewInstance($sub, $manufacturer);
		$record->save();
		$sub->loadAll();

		$this->assertFalse($condition->isProductMatching($newProduct));

		$newProduct->manufacturer->set($manufacturer);
		$newProduct->save();
		$this->assertTrue($condition->isProductMatching($newProduct));

		// sub-sub condition
		$sub->isAllSubconditions->set(false);
		$sub->save();

		for ($k = 1; $k <= 2; $k++)
		{
			$subs[$k] = DiscountCondition::getNewInstance($sub);
			$subs[$k]->isEnabled->set(true);
		}

		// false
		$subs[1]->save();
		$someManufacturer = Manufacturer::getNewInstance('Manufacturer without products');
		$someManufacturer->save();
		$record = DiscountConditionRecord::getNewInstance($subs[1], $someManufacturer);
		$record->save();

		$subs[1]->loadAll();
		$sub->loadAll();
		$condition->loadAll();

		$this->assertFalse($subs[1]->isProductMatching($newProduct));
		$this->assertFalse($condition->isProductMatching($newProduct));

		// true
		$subs[2]->save();
		$record = DiscountConditionRecord::getNewInstance($subs[2], $newProduct);
		$record->save();

		$subs[2]->loadAll();
		$sub->loadAll();
		$condition->loadAll();

		$this->assertTrue($condition->isProductMatching($newProduct));
	}

	public function testOrderMinTotal()
	{
		$this->order->addProduct($this->product1, 1, true);
		$this->order->addProduct($this->product2, 1, true);
		$this->order->save();
		// order total = 30

		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->subTotal->set(10);
		$condition->comparisonType->set(DiscountCondition::COMPARE_GTEQ);
		$condition->save();

		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));

		$condition->subTotal->set(50);
		$condition->save();
		$this->assertEquals(0, count($this->order->getDiscountConditions(true)));

		$condition->subTotal->set(30);
		$condition->save();
		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));
	}

	public function testOrderTotalRange()
	{
		$this->order->addProduct($this->product1, 1, true);
		$this->order->addProduct($this->product2, 1, true);
		$this->order->save();
		// order total = 30

		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		//$condition->isAllSubconditions->set(true);
		$condition->subTotal->set(10);
		$condition->comparisonType->set(DiscountCondition::COMPARE_GTEQ);
		$condition->save();

		$sub = DiscountCondition::getNewInstance($condition);
		$sub->isEnabled->set(true);
		$sub->subTotal->set(60);
		$sub->comparisonType->set(DiscountCondition::COMPARE_LTEQ);
		$sub->save();

		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));

		$condition->subTotal->set(50);
		$condition->save();
		$this->assertEquals(0, count($this->order->getDiscountConditions(true)));

		$condition->subTotal->set(30);
		$condition->save();
		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));
	}

	public function testOrderItemCount()
	{
		$this->order->addProduct($this->product1, 1, true);
		$this->order->addProduct($this->product2, 1, true);
		$this->order->save();

		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->count->set(1);
		$condition->comparisonType->set(DiscountCondition::COMPARE_GTEQ);
		$condition->save();

		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));

		$condition->count->set(3);
		$condition->save();
		$this->assertEquals(0, count($this->order->getDiscountConditions(true)));

		$condition->count->set(2);
		$condition->save();
		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));
	}

}

?>