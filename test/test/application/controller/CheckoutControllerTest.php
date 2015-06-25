<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.controller.CheckoutController');
ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('test.mock.FakePaymentMethod');

/**
 *
 * @package test.application.controller
 * @author Shumoapp
 */
class CheckoutControllerTest extends LiveCartTest implements ControllerTestCase
{
	/**
	 * Root category
	 * @var Category
	 */
	private $controller;

	private $product;

	public function getUsedSchemas()
	{
		return array(
			'CustomerOrder',
			'OrderedItem',
			'Shipment',
			'Product',
			'User',
		);
	}

	public function setUp()
	{
		parent::setUp();

		$this->controller = new CheckoutController(self::getApplication());
		$this->initOrder();
		$this->controller->setOrder($this->order);
		$this->controller->setUser($this->user);
	}

	/**
	 *	Testing notify() method when the order was assembled in non-default currency.
	 */
	public function testNotifyNonDefaultCurrency()
	{
		$this->order->addProduct($this->products[0], 1);
		$this->order->addProduct($this->products[1], 2);
		$this->order->save();

		//Get the total in the default currency
		$total = $this->order->getTotal(true);

		//However the customer is a foreigner, and prefers to see the amounts in his own currency
		$this->order->changeCurrency($this->eur);

		//Simulate a postback from the Payment Processor. Because it could be a background post, the user selected currency is not passed
		$this->request->set('id', 'FakePaymentMethod');
		$this->request->set('orderID', $this->order->getID());
		$this->request->set('transactionId', time());
		$this->request->set('amount', $total);
		$this->request->set('currency', 'USD');

		//Process the transaction using the mock FakePaymentMethod class
		$this->controller->notify();
		//$transactions = $this->order->getTransactions();

		//Verify that the order prices match the product prices
		foreach ($this->order->getPurchasedItems() as $orderedItem)
		{
			$product = $this->getProductByID($orderedItem->product->get()->getID());
			if (null!=$product) $this->assertEquals($product->getPrice($orderedItem->getCurrency()), $orderedItem->getItemPrice());
		}
	}

	/**
	 * Find the product in the $this->products array, and return it
	 *
	 * @param $productID
	 * @return Product|null
	 */
	private function getProductByID($productID)
	{
		foreach ($this->products as $product)
		{
			if ($product->getID()==$productID) return $product;
		}

		return null;
	}
}

?>
