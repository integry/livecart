<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.Currency');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.user.SessionUser');

/**
 *
 * @author Integry Systems
 * @package test.model.system
 */
class CurrencyTest extends LiveCartTest
{
	public function getUsedSchemas()
	{
		return array(
			'Currency'
		);
	}

	public function setUp()
	{
		parent::setUp();
		ActiveRecord::executeUpdate('DELETE FROM Currency');
		ActiveRecord::executeUpdate('DELETE FROM DiscountCondition');
	}

	function testRounding()
	{
		$currency = Currency::getNewInstance('RON');
		$currency->save();

		$currency->setRoundingRule(0, Currency::ROUND, 0.05);

		// test precision
		$this->assertEquals(99.95, $currency->roundPrice(99.97));
		$this->assertEquals(100, $currency->roundPrice(99.98));
		$this->assertEquals(0.05, $currency->roundPrice(0.02));

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::ROUND_DOWN, 0.33333333);
		$this->assertEquals(99.67, $currency->roundPrice(99.95));
		$this->assertEquals(0.33, $currency->roundPrice(0.5));

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::ROUND_UP, 2);
		$this->assertEquals(100, $currency->roundPrice(98.5));
		$this->assertEquals(4, $currency->roundPrice(2.01));

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::NO_ROUNDING);
		$this->assertEquals(98.5, $currency->roundPrice(98.5));
		$this->assertEquals(2.01, $currency->roundPrice(2.01));

		// test negative values
		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::ROUND_UP, 0.1);
		$this->assertEquals(-0.2, $currency->roundPrice(-0.17));

		// test interval matching
		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::ROUND_UP, 2);
		$currency->setRoundingRule(100, Currency::ROUND_DOWN, 5);
		$currency->setRoundingRule(1000, Currency::ROUND, 10);
		$currency->setRoundingRule(10000, Currency::NO_ROUNDING);

		$this->assertEquals(4, $currency->roundPrice(3));
		$this->assertEquals(110, $currency->roundPrice(113));
		$this->assertEquals(1460, $currency->roundPrice(1456));
		$this->assertEquals(12345.67, $currency->roundPrice(12345.67));
	}

	function testTrimmingPennies()
	{
		$currency = Currency::getNewInstance('RON');
		$currency->save();

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::TRIM, 0.09);
		$this->assertEquals(99.99, $currency->roundPrice(99.97));
		$this->assertEquals(99.89, $currency->roundPrice(99.91));

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::TRIM_UP, 0.25);
		$this->assertEquals(99.25, $currency->roundPrice(99.12));
		$this->assertEquals(99.25, $currency->roundPrice(99.14));
		$this->assertEquals(99.25, $currency->roundPrice(99.24));
		$this->assertEquals(90.05, $currency->roundPrice(89.88));
		$this->assertEquals(99.45, $currency->roundPrice(99.26));
		$this->assertEquals(99.45, $currency->roundPrice(99.34));

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::TRIM, 0.99);
		$this->assertEquals(98.99, $currency->roundPrice(99.12));
		$this->assertEquals(89.99, $currency->roundPrice(89.88));

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::TRIM_UP, 0.99);
		$this->assertEquals(99.99, $currency->roundPrice(99.12));
		$this->assertEquals(89.99, $currency->roundPrice(89.88));

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::TRIM, 0.19);
		$this->assertEquals(99.39, $currency->roundPrice(99.34));
		$this->assertEquals(99.19, $currency->roundPrice(99.12));
		$this->assertEquals(89.79, $currency->roundPrice(89.87));

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::TRIM, 0.29);
		$this->assertEquals(99.29, $currency->roundPrice(99.34));
		$this->assertEquals(99.49, $currency->roundPrice(99.44));
		$this->assertEquals(89.89, $currency->roundPrice(89.87));

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::TRIM_DOWN, 0.19);
		$this->assertEquals(99.19, $currency->roundPrice(99.34));
		$this->assertEquals(98.99, $currency->roundPrice(99.12));
		$this->assertEquals(89.79, $currency->roundPrice(89.87));

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::TRIM, 0.45);
		$this->assertEquals(98.95, $currency->roundPrice(99.12));
		$this->assertEquals(99.45, $currency->roundPrice(99.34));
		$this->assertEquals(99.45, $currency->roundPrice(99.44));
		$this->assertEquals(89.95, $currency->roundPrice(89.87));

		/*
		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::TRIM_UP, 0.39);
		$this->assertEquals(89.39, $currency->roundPrice(89.01));
		$this->assertEquals(99.39, $currency->roundPrice(99.34));
		$this->assertEquals(99.69, $currency->roundPrice(99.44));
		$this->assertEquals(89.99, $currency->roundPrice(89.87));

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::TRIM, 0.39);
		$this->assertEquals(88.99, $currency->roundPrice(89.01));
		$this->assertEquals(99.39, $currency->roundPrice(99.34));
		$this->assertEquals(99.39, $currency->roundPrice(99.44));
		$this->assertEquals(89.99, $currency->roundPrice(89.87));
		*/
	}

	function testTrimmingToWholeNumbers()
	{
		$currency = Currency::getNewInstance('RON');
		$currency->save();

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::TRIM, 4);
		$this->assertEquals(99, $currency->roundPrice(98));
		$this->assertEquals(94, $currency->roundPrice(93));

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::TRIM, 4.99);
		$this->assertEquals(99.99, $currency->roundPrice(98.99));
		$this->assertEquals(94.99, $currency->roundPrice(93));

		$currency->clearRoundingRules();
		$currency->setRoundingRule(0, Currency::TRIM_UP, 77.99);
		$this->assertEquals(4477.99, $currency->roundPrice(4453));
		$this->assertEquals(4577.99, $currency->roundPrice(4480));
	}

	public function testOrderTotalsWithRoundedPrices()
	{
		$currency = Currency::getNewInstance('RON');
		$currency->setRoundingRule(0, Currency::TRIM, 0.09);
		$currency->save();

		$product = Product::getNewInstance(Category::getRootNode());
		$product->isEnabled->set(true);
		$product->setPrice($currency, 1.26);
		$product->save();

		$order = CustomerOrder::getNewInstance(SessionUser::getAnonymousUser());
		$order->addProduct($product);
		$order->save(true);
		$item = array_shift($order->getItemsByProduct($product));

		$this->assertEquals($product->getPrice($currency), 1.29);

		$this->assertEquals($item->getSubTotal($currency), 1.29);
		$item->count->set(2);
		$this->assertEquals($item->getSubTotal($currency), 2.58);

		$this->assertEquals($order->getTotal($currency), 2.58);

		// add another currency to mix - no rounding rules
		$bgn = Currency::getNewInstance('BGN');
		$bgn->rate->set(2);
		$bgn->save();

		$item->count->set(2);
		$order->changeCurrency($bgn);
		$this->assertEquals($product->getPrice($bgn), 0.63);
		$this->assertEquals($item->getSubTotal(), 1.26);
		$this->assertEquals($order->getTotal(), 1.26);

		// add rounding rules
		$bgn->clearRoundingRules();
		$bgn->setRoundingRule(0, Currency::TRIM, 0.07);
		$this->assertEquals($product->getPrice($bgn), 0.67);
		$this->assertEquals($item->getSubTotal(), 1.34);
		$this->assertEquals($order->getTotal(), 1.34);

	}

}

?>