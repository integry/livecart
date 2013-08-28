<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.controller.UserController');
ClassLoader::import('application.model.order.SessionOrder');

/**
 *
 * @package test.application.controller
 * @author Integry Systems
 */
class UserControllerTest extends LiveCartTest implements ControllerTestCase
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
			'UserAddress',
			'BillingAddress',
			'ShippingAddress',
		);
	}

	public function setUp()
	{
		parent::setUp();

		ActiveRecordModel::executeUpdate('DELETE FROM EavField');

		$this->controller = new UserController(self::getApplication());
		$this->initOrder();
		$this->controller->setOrder($this->order);
		$this->controller->setUser($this->user);
	}

	public function testUserCheckoutWithSameAddresses()
	{
		$this->order->addProduct($this->products[0], 1);
		$this->order->save();

		$this->assertTrue($this->order->isShippingRequired());

		$this->controller->setOrder($this->reloadOrder($this->order));

		$request = $this->controller->getRequest();
		$request->set('email', 'usercheckout@example.com');
		$request->set('billing_firstName', 'First');

		// last name is empty
		$request->set('billing_lastName', '');
		$request->set('billing_companyName', 'CMP');
		$request->set('billing_address1', 'Address 1');
		$request->set('billing_state_text', 'State');
		$request->set('billing_city', 'Some City');
		$request->set('billing_country', 'LV');
		$request->set('billing_postalCode', 'LV-1234');
		$request->set('billing_phone', '1234');

		$request->set('sameAsBilling', 'on');
		$response = $this->controller->processCheckoutRegistration();

		// last name was not entered, so we get back to user/checkout
		$this->assertIsA($response, 'ActionRedirectResponse');
		$this->assertEqual($response->getControllerName(), 'user');
		$this->assertEqual($response->getActionName(), 'checkout');
		$this->assertEqual(count($this->controller->checkout()->get('form')->getValidator()->getErrorList()), 1);

		// should be correct, except that we didn't select that shipping address is the same
		$request->set('billing_lastName', 'Last');
		$request->set('sameAsBilling', '');
		$this->assertEqual($response->getControllerName(), 'user');
		$this->assertEqual($response->getActionName(), 'checkout');

		// ok, selected now
		$request->set('sameAsBilling', 'on');
		$response = $this->controller->processCheckoutRegistration();
		$this->assertIsA($response, 'ActionRedirectResponse');
		$this->assertEqual($response->getControllerName(), 'checkout');
		$this->assertEqual($response->getActionName(), 'shipping');

		// verify user data
		$user = SessionUser::getUser();
		$user->reload(true);
		$this->assertEquals($user->firstName, 'First');
		$this->assertEquals($user->defaultBillingAddress->userAddress->countryID, 'LV');
		$this->assertEquals($user->defaultShippingAddress->userAddress->countryID, 'LV');
	}

	public function testUserCheckoutWithDifferentAddresses()
	{
		$this->order->addProduct($this->products[0], 1);
		$this->order->save();

		$this->assertTrue($this->order->isShippingRequired());

		$this->controller->setOrder($this->reloadOrder($this->order));

		$request = $this->controller->getRequest();
		$request->set('sameAsBilling', '');
		$request->set('email', 'usercheckout@example.com');

		// shipping address not entered at all
		$request->set('billing_firstName', 'First');
		$request->set('billing_lastName', 'Last');
		$request->set('billing_companyName', 'CMP');
		$request->set('billing_address1', 'Address 1');
		$request->set('billing_state_text', 'State');
		$request->set('billing_city', 'Some City');
		$request->set('billing_country', 'LV');
		$request->set('billing_postalCode', 'LV-1234');
		$request->set('billing_phone', '1234');

		$response = $this->controller->processCheckoutRegistration();

		// last name was not entered, so we get back to user/checkout
		// with a bunch of errors for each shipping address field
		$this->assertIsA($response, 'ActionRedirectResponse');
		$this->assertEqual($response->getControllerName(), 'user');
		$this->assertEqual($response->getActionName(), 'checkout');
		$this->assertTrue(1 < count($this->controller->checkout()->get('form')->getValidator()->getErrorList()));

		// let's forget the last name again
		$request->set('shipping_firstName', 'Recipient');
		$request->set('shipping_companyName', 'CMP');
		$request->set('shipping_address1', 'Rec Street');
		$request->set('shipping_city', 'Rec City');
		$request->set('shipping_state_text', 'State');
		$request->set('shipping_country', 'LT');
		$request->set('shipping_postalCode', 'LT-4321');
		$request->set('shipping_phone', '4321');

		$this->assertEqual($response->getControllerName(), 'user');
		$this->assertEqual($response->getActionName(), 'checkout');

		// enter that last name at last
		$request->set('shipping_lastName', 'Last');

		$response = $this->controller->processCheckoutRegistration();
		$this->assertIsA($response, 'ActionRedirectResponse');
		$this->assertEqual($response->getControllerName(), 'checkout');
		$this->assertEqual($response->getActionName(), 'shipping');

		// verify user data
		$user = SessionUser::getUser();
		$user->reload(true);
		$this->assertEquals($user->firstName, 'First');
		$this->assertEquals($user->defaultShippingAddress->userAddress->firstName, 'Recipient');
		$this->assertEquals($user->defaultBillingAddress->userAddress->countryID, 'LV');
		$this->assertEquals($user->defaultShippingAddress->userAddress->countryID, 'LT');

		// order address
		ActiveRecord::clearPool();
		$order = CustomerOrder::getInstanceByID($this->order->getID(), true);
		$order->loadAll();
		$this->assertEquals($order->shippingAddress->countryID, 'LT');

	}

	private function setUpController(FrontendController $controller)
	{
		$this->initOrder();
		$controller->setOrder($this->order);
		$controller->setUser($this->user);
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