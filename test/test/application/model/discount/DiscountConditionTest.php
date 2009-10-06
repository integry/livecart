<?php

require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.Currency");
ClassLoader::import("application.model.order.CustomerOrder");
ClassLoader::import("application.model.user.User");
ClassLoader::import("application.model.discount.DiscountCondition");
ClassLoader::import("application.model.businessrule.BusinessRuleController");

/**
 *
 * @package test.model.discount
 * @author Integry Systems
 */
class DiscountConditionTest extends LiveCartTest
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

		BusinessRuleController::clearCache();

		ActiveRecord::executeUpdate('DELETE FROM DeliveryZone');
		ActiveRecord::executeUpdate('DELETE FROM Tax');
		ActiveRecord::executeUpdate('DELETE FROM TaxRate');
		ActiveRecord::executeUpdate('DELETE FROM DiscountCondition');
		ActiveRecord::executeUpdate('DELETE FROM DiscountAction');
		ActiveRecord::executeUpdate('DELETE FROM Currency');
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

		$orderTotal = $this->order->getTotal();

		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();

		$conditions = $this->order->getDiscountConditions(true);
		$this->assertEqual($conditions[0]->getParam('ID'), $condition->getID());

		$result = DiscountAction::getNewInstance($condition);
		$result->isEnabled->set(true);
		$result->actionClass->set('RuleActionPercentageDiscount');
		$result->amount->set(10);
		$result->save();

		$actions = $this->order->getDiscountActions(true);
		$this->assertEqual($actions[0]->getParam('ID'), $result->getID());

		//$this->order->processBusinessRules();

		$discounts = $this->order->getOrderDiscounts();
		//$this->assertEqual(count($discounts), 1);

		$newTotal = $this->order->getTotal(true);

		// uuhh.. Failed asserting that <double:27> matches expected value <double:27>.
		$this->assertEqual((string)($orderTotal * 0.9), (string)$newTotal);
	}

	public function testFixedDiscount()
	{
		$this->order->addProduct($this->product1, 1, true);
		$this->order->addProduct($this->product2, 1, true);
		$this->order->save();

		$orderTotal = $this->order->getTotal();

		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();

		$result = DiscountAction::getNewInstance($condition);
		$result->isEnabled->set(true);
		$result->actionClass->set('RuleActionFixedDiscount');
		$result->amount->set(10);
		$result->save();

		$this->order->processBusinessRules(true);
		$discounts = $this->order->getOrderDiscounts();
		$this->assertEqual(count($discounts), 1);

		$this->assertEqual($orderTotal - 10, $this->order->getTotal(true));
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
		$condition->conditionClass->set('RuleConditionContainsProduct');
		$condition->save();

		$record = DiscountConditionRecord::getNewInstance($condition, $this->product1);
		$record->save();

		$condition->loadAll();
		$ruleCondition = RuleCondition::createFromArray($condition->toArray());

		$this->assertFalse($ruleCondition->isProductMatching($this->product2));
		$this->assertTrue($ruleCondition->isProductMatching($this->product1));

		// check matching by manufacturer
		$manufacturer = Manufacturer::getNewInstance('testing');
		$manufacturer->save();
		$this->product2->manufacturer->set($manufacturer);

		$record = DiscountConditionRecord::getNewInstance($condition, $manufacturer);
		$record->save();

		$condition->loadAll();
		$ruleCondition = RuleCondition::createFromArray($condition->toArray());
		$this->assertTrue($ruleCondition->isProductMatching($this->product2));

		// check matching by category
		$newCategory = Category::getNewInstance(Category::getRootNode());
		$newCategory->save();
		$newSubCategory = Category::getNewInstance($newCategory);
		$newSubCategory->save();

		$newProduct = Product::getNewInstance($newSubCategory);
		$newProduct->save();
		$newProduct->reload();

		$record = DiscountConditionRecord::getNewInstance($condition, $newCategory);
		$record->save();

		$this->assertFalse($ruleCondition->isProductMatching($newProduct));
		$condition->loadAll();
		$ruleCondition = RuleCondition::createFromArray($condition->toArray());

		$this->assertTrue($ruleCondition->isProductMatching($newProduct));

		// sub-condition
		$sub = DiscountCondition::getNewInstance($condition);
		$sub->isEnabled->set(true);
		$sub->conditionClass->set('RuleConditionContainsProduct');
		$sub->save();

		$condition->isAllSubconditions->set(true);
		$condition->save();
		$condition->loadAll();

		$manufacturer = Manufacturer::getNewInstance('new one');
		$manufacturer->save();

		$record = DiscountConditionRecord::getNewInstance($sub, $manufacturer);
		$record->save();
		$sub->loadAll();

		$subCondition = RuleCondition::createFromArray($sub->toArray());
		$this->assertFalse($subCondition->isProductMatching($newProduct));

		$ruleCondition = RuleCondition::createFromArray($condition->toArray());
		$this->assertFalse($ruleCondition->isProductMatching($newProduct));

		$newProduct->manufacturer->set($manufacturer);
		$newProduct->save();
		$this->assertTrue($ruleCondition->isProductMatching($newProduct));

		// sub-sub condition
		$sub->isAllSubconditions->set(false);
		$sub->save();

		for ($k = 1; $k <= 2; $k++)
		{
			$subs[$k] = DiscountCondition::getNewInstance($sub);
			$subs[$k]->isEnabled->set(true);
			$subs[$k]->conditionClass->set('RuleConditionContainsProduct');
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

		$this->assertFalse(RuleCondition::create($subs[1])->isProductMatching($newProduct));
		$this->assertFalse(RuleCondition::create($condition)->isProductMatching($newProduct));

		// true
		$subs[2]->save();
		$record = DiscountConditionRecord::getNewInstance($subs[2], $newProduct);
		$record->save();

		$subs[2]->loadAll();
		$sub->loadAll();
		$condition->loadAll();

		$this->assertTrue(RuleCondition::create($condition)->isProductMatching($newProduct));
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
		$condition->conditionClass->set('RuleConditionOrderTotal');
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
		$condition->conditionClass->set('RuleConditionOrderTotal');
		$condition->subTotal->set(10);
		$condition->comparisonType->set(DiscountCondition::COMPARE_GTEQ);
		$condition->save();

		$sub = DiscountCondition::getNewInstance($condition);
		$sub->isEnabled->set(true);
		$sub->subTotal->set(60);
		$sub->conditionClass->set('RuleConditionOrderTotal');
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
		$condition->conditionClass->set('RuleConditionOrderItemCount');
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

	public function testEmptyCondition()
	{
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();

		$this->order->addProduct($this->product1, 1, true);
		$this->order->save();
		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));
	}

	public function testUserCoupon()
	{
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->couponCode->set('test');
		$condition->save();

		$someUser = User::getNewInstance('discount...condition@test');
		$someUser->save();
		$userCond = DiscountCondition::getNewInstance($condition);
		$userCond->isEnabled->set(true);
		$userCond->save();

		$rec = DiscountConditionRecord::getNewInstance($userCond, $someUser);
		$rec->save();

		$this->order->addProduct($this->product1, 1, true);
		$this->order->addProduct($this->product2, 1, true);
		$this->order->save();
		$this->assertEquals(0, count($this->order->getDiscountConditions(true)));

		$this->order->user->set($someUser);
		$this->order->save();
		$this->assertEquals(0, count($this->order->getDiscountConditions(true)));

		OrderCoupon::getNewInstance($this->order, 'test')->save();
		$this->order->getCoupons(true);
		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));
	}

	public function testLimitedCoupon()
	{
		$code = 'unit test coupon';

		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->couponCode->set($code);
		$condition->couponLimitCount->set(1);
		$condition->save();

		$someUser = User::getNewInstance('discount...condition@test');
		$someUser->save();

		$this->order->addProduct($this->product1, 1, true);
		$this->order->save();
		OrderCoupon::getNewInstance($this->order, $code)->save();
		$this->assertEquals(1, $this->order->getCoupons(true)->size());
		$this->order->save();
		$this->order->finalize($this->usd);

		$newOrder = CustomerOrder::getNewInstance($someUser);
		$newOrder->addProduct($this->product1, 1, true);
		$newOrder->save();
		OrderCoupon::getNewInstance($newOrder, $code)->save();
		$newOrder->getCoupons(true);

		$this->assertEquals(0, $newOrder->getCoupons(true)->size());

		// increase limit - will pass
		$condition->couponLimitCount->set(2);
		OrderCoupon::getNewInstance($newOrder, $code)->save();
		$this->assertEquals(1, $newOrder->getCoupons(true)->size());

		// remove coupon
		$condition->couponLimitCount->set(1);
		$this->assertEquals(0, $newOrder->getCoupons(true)->size());

		// change limit type to per user and change order user
		$otherUser = User::getNewInstance('discount...otheruser@test');
		$otherUser->save();
		$newOrder->user->set($otherUser);

		$condition->couponLimitCount->set(1);
		$condition->couponLimitType->set(DiscountCondition::COUPON_LIMIT_USER);
		OrderCoupon::getNewInstance($newOrder, $code)->save();

		$this->assertEquals(1, $newOrder->getCoupons(true)->size());
	}

	/**
	 *	@todo: doesn't work
	 */
	public function testAdditionalCategories()
	{
		$customCategory = Category::getNewInstance(Category::getRootNode());
		$customCategory->save();

		$product = $this->product1;
		$this->order->addProduct($product, 1, true);
		$this->order->save();

		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->conditionClass->set('RuleConditionContainsProduct');
		$condition->save();
		DiscountConditionRecord::getNewInstance($condition, $customCategory)->save();

		$this->assertEquals(0, count($this->order->getDiscountConditions(true)));

		ProductCategory::getNewInstance($product, $customCategory)->save();

		$condition->loadAll();

		//$this->assertEquals(1, count($this->order->getDiscountConditions(true)));
	}

	public function testStopProcessing()
	{
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();

		$another = DiscountCondition::getNewInstance();
		$another->isEnabled->set(true);
		$another->save();

		$this->order->addProduct($this->product1, 1, true);
		$this->order->save();

		$this->assertEquals(2, count($this->order->getDiscountConditions(true)));

		$condition->isFinal->set(true);
		$condition->save();
		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));
	}

	public function testDisableCheckout()
	{
		$this->order->addProduct($this->product1, 1, true);
		$this->order->save();
		$this->assertTrue($this->order->isOrderable());

		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();

		$action = DiscountAction::getNewInstance($condition);
		$action->isEnabled->set(true);
		$action->actionClass->set('RuleActionDisableCheckout');
		$action->save();

		$this->order->getDiscountActions(true);
		$this->order->processBusinessRules(true);
		$this->assertFalse($this->order->isOrderable());
	}

	public function testDiscountStep()
	{
		$this->order->addProduct($this->product1, 5, true);
		$this->order->save();

		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();

		$action = DiscountAction::getNewInstance($condition);
		$action->isEnabled->set(true);
		$action->amount->set(10);
		$action->actionClass->set('RuleActionPercentageDiscount');
		//$action->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
		$action->save();

		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));
		$this->assertEquals(1, count($this->order->getDiscountActions(true)));

		$this->order->processBusinessRules(true);
		$this->assertEquals(45, $this->order->getTotal(true));

		// discount is applied to every other item
		$action->discountStep->set(2);
		$action->save();
		$this->order->getDiscountActions(true);
		$this->assertEquals(48, $this->order->getTotal(true));
	}

	public function testDiscountLimit()
	{
		$this->order->addProduct($this->product1, 5, true);
		$this->order->save();

		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();

		$action = DiscountAction::getNewInstance($condition);
		$action->isEnabled->set(true);
		$action->amount->set(10);
		$action->actionClass->set('RuleActionPercentageDiscount');
		$action->save();

		$this->order->getDiscountActions(true);
		$this->assertEquals(45, $this->order->getTotal(true));

		// discount is applied to 3 items only
		$action->discountLimit->set(3);
		$action->save();
		$this->order->getDiscountActions(true);
		$this->assertEquals(47, $this->order->getTotal(true));
	}

	public function testDivisable()
	{
		$this->order->addProduct($this->product1, 8, true);
		$this->order->save();

		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->comparisonType->set(DiscountCondition::COMPARE_DIV);

		// test divisibility
		$condition->count->set(3);
		$condition->conditionClass->set('RuleConditionOrderItemCount');
		$condition->save();
		$this->assertEquals(0, count($this->order->getDiscountConditions(true)));

		$condition->count->set(2);
		$condition->save();
		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));

		$condition->count->set(5);
		$condition->save();
		$this->assertEquals(0, count($this->order->getDiscountConditions(true)));

		$condition->count->set(4);
		$condition->save();
		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));

		$condition->count->set(8);
		$condition->save();
		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));

		// test non-divisibility
		$condition->comparisonType->set(DiscountCondition::COMPARE_NDIV);
		$condition->save();
		$this->assertEquals(0, count($this->order->getDiscountConditions(true)));

		$condition->count->set(5);
		$condition->save();
		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));

		$condition->count->set(2);
		$condition->save();
		$this->assertEquals(0, count($this->order->getDiscountConditions(true)));
	}

	public function testPaymentMethod()
	{
		ActiveRecordModel::getApplication()->getConfig()->set('INVENTORY_TRACKING', 'DISABLE');

		$this->product1->isEnabled->set(true);
		$this->product1->save();
		$this->order->addProduct($this->product1, 1);
		$this->order->save();

		ActiveRecord::clearPool();

		$this->order = CustomerOrder::getInstanceById($this->order->getID(), true);
		$this->order->loadAll();
		$this->assertEquals(1, $this->order->getShoppingCartItemCount());

		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->conditionClass->set('RuleConditionPaymentMethodIs');
		$condition->addValue('TESTING');
		$condition->save();

		$this->assertEquals(0, count($this->order->getDiscountConditions(true)));

		$this->order->setPaymentMethod('TESTING');
		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));

		$this->order->setPaymentMethod('AnotherOne');
		$this->assertEquals(0, count($this->order->getDiscountConditions(true)));

		// test finalized order
		ClassLoader::import('library.payment.TransactionResult');
		$transResult = new TransactionResult();
		$transResult->setTransactionType(TransactionResult::TYPE_SALE);
		$transResult->amount->set(10000);
		$transResult->currency->set('USD');

		$transaction = Transaction::getNewInstance($this->order, $transResult);
		$transaction->method->set('TESTING');
		$transaction->save();

		$this->order->finalize();

		ActiveRecord::clearPool();

		$reloaded = CustomerOrder::getInstanceById($this->order->getID(), true);
		$reloaded->loadAll();

		$this->assertTrue($reloaded->isExistingRecord());
		$this->assertEquals(1, $reloaded->getShoppingCartItemCount());
		$this->assertEquals(1, count($reloaded->getDiscountConditions(true)));

		$condition->removeValue('TESTING');
		$condition->addValue('Whatever');
		$condition->save();
		$this->assertEquals(0, count($reloaded->getDiscountConditions(true)));
	}

	public function testSumVariationQuantityDiscounts()
	{
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();

		$action = DiscountAction::getNewInstance($condition);
		$action->actionClass->set('RuleActionSumVariations');
		$action->isEnabled->set(false);
		$action->save();

		// price = 10
		$price = $this->product1->getPricingHandler()->getPrice($this->usd);
		$price->setPriceRule(3, null, 5);
		$price->save();

		$children = array();
		for ($k = 0; $k <= 1; $k++)
		{
			$child = $this->product1->createChildProduct();
			$child->save();
			$children[] = $child;

			$this->order->addProduct($child, 2, true);
		}

		$this->order->save();
		$this->assertEquals(40, $this->order->getTotal(true));

		$action->isEnabled->set(true);
		$action->save();

		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));
		$this->assertEquals(1, count($this->order->getDiscountActions(true)));

		$this->order->reset();
		$this->assertEquals(1, count($this->order->getDiscountConditions(true)));
		$this->assertEquals(1, count($this->order->getDiscountActions(true)));
		$this->assertEquals(20, $this->order->getTotal(true));
	}
}

?>