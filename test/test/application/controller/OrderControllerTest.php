<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.controller.OrderController');
ClassLoader::import('application.model.order.CustomerOrder');

/**
 *
 * @package test.application.controller
 * @author Integry Systems
 */
class OrderControllerTest extends LiveCartTest implements ControllerTestCase
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
			'ProductRating',
			'ProductRatingType',
			'ProductReview',
			'User',
		);
	}

	public function setUp()
	{
		parent::setUp();

		$this->controller = new OrderController(self::getApplication());
		$this->initOrder();
		$this->controller->setOrder($this->order);
		$this->controller->setUser($this->user);
	}

	public function testSetMultiAddress()
	{
		$this->order->addProduct($this->products[0], 1);
		$this->order->addProduct($this->products[1], 2);
		$this->order->save();

		$this->controller->setOrder($this->reloadOrder($this->order));

		$response = $this->controller->setMultiAddress();
		$order = $this->reloadOrder($this->order);

		$this->assertIsA($response, 'ActionRedirectResponse');
		$this->assertEqual($order->isMultiAddress, '1');
	}

	public function testSetSingleAddress()
	{
		$this->order->addProduct($this->products[0], 1);
		$this->controller->setOrder($this->order);
		$this->controller->setMultiAddress();

		$shipment1 = Shipment::getNewInstance($this->order);
		$shipment1->save();
		$shipment2 = Shipment::getNewInstance($this->order);
		$shipment2->save();

		$this->order->addProduct($this->products[0], 1, true, $shipment1);
		$this->order->addProduct($this->products[1], 2, true, $shipment2);
		$this->order->save();

		$order = $this->reloadOrder($this->order);
		$this->assertEqual($order->getShipments()->size(), 2);
		$this->assertEqual(count($order->getOrderedItems()), 3);

		$this->controller->setOrder($order = $this->reloadOrder($this->order));
		$response = $this->controller->setSingleAddress();

		$order = $this->reloadOrder($order);

		$this->assertIsA($response, 'ActionRedirectResponse');
		$this->assertEqual($order->isMultiAddress, '0');
		$this->assertEqual($order->getShipments()->size(), 1);
		$this->assertEqual(count($order->getOrderedItems()), 2);
	}

	private function reloadOrder(CustomerOrder $order)
	{
		ActiveRecord::clearPool();
		$order = CustomerOrder::getInstanceById($order->getID(), true);
		$order->loadAll();

		return $order;
	}
}

?>