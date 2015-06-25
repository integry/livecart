<?php

ClassLoader::import('application.model.user.User');
ClassLoader::import('application.model.user.UserAddress');

abstract class LiveCartTest extends PHPUnit_Framework_TestCase
{
	protected $config;
	protected $userEmail = 'test@test.com';
	protected $userPassword = 'testerer';
	protected $usd2eru =2;

	public function setUp()
	{
        # Do not convert warnings to exceptions.
        PHPUnit_Framework_Error_Warning::$enabled = FALSE;

        # Do not convert notice and strict to exceptions
        PHPUnit_Framework_Error_Notice::$enabled = FALSE;

		# Do not convert deprecated notices to exceptions
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		parent::setUp();

		$this->config = ActiveRecordModel::getApplication()->getConfig();

		ActiveRecordModel::beginTransaction();

		ActiveRecordModel::executeUpdate('DELETE FROM Tax');
		ActiveRecordModel::executeUpdate('DELETE FROM TaxRate');
		ActiveRecordModel::executeUpdate('DELETE FROM Currency');
		ActiveRecordModel::executeUpdate('DELETE FROM DiscountCondition');
		ActiveRecordModel::executeUpdate('DELETE FROM DiscountAction');
		ActiveRecordModel::executeUpdate('DELETE FROM DeliveryZone');

		$this->getApplication()->clearCachedVars();

		if ($this instanceof ControllerTestCase)
		{
			$this->request = self::getApplication()->getRequest();
		}
	}

	public function tearDown()
	{
		parent::tearDown();

		@unlink(ClassLoader::getRealPath('cache.') . 'currencies.php');
		$this->setUpCurrency();

		ActiveRecordModel::rollback();
	}

	/**
	 * !Running tests not involving initOrder() method will not recreate Currency,
	 * but setUp() method is wiping all Currecy records,
	 *
	 * Store frontend is not working without Currency object
	 *
	 * As workround this method can be called from test suite to recreate Currency
	 *
	 * @todo: reorganize tests to call DELETE FROM Currency only when setUpCurrency() method is called.
	 *
	 */
	protected function setUpCurrency()
	{
		if (ActiveRecord::objectExists('Currency', 'USD'))
		{
			$this->usd = Currency::getInstanceByID('USD', Currency::LOAD_DATA);
		}
		else
		{
			$this->usd = Currency::getNewInstance('USD');
			$this->usd->setAsDefault();
			$this->usd->save();
		}

		if (ActiveRecord::objectExists('Currency', 'EUR'))
		{
			$this->eur = Currency::getInstanceByID('EUR', Currency::LOAD_DATA);
		}
		else
		{
			$this->eur = Currency::getNewInstance('EUR');
			$this->eur->rate->set($this->usd2eru);
			$this->eur->save();
		}
	}

	protected function initUser()
	{
		ActiveRecordModel::executeUpdate('DELETE FROM User WHERE email="'.$this->userEmail.'"');
		$user = User::getNewInstance($this->userEmail);
		$user->setPassword($this->userPassword);
		$user->isEnabled->set(true);
		$user->save();
		$this->user = $user;

		$address = UserAddress::getNewInstance();
		$address->countryID->set('US');
		$state = State::getInstanceById(1, State::LOAD_DATA);
		$address->state->set(State::getInstanceById(1));
		$address->postalCode->set(90210);
		$address->save();
		$billing = BillingAddress::getNewInstance($user, $address);
		$billing->save();

		$address = clone $address;
		$address->save();
		$shipping = ShippingAddress::getNewInstance($user, $address);
		$shipping->save();
	}

	protected function initOrder()
	{
		// set up currency
		$this->setUpCurrency();
		$this->usd->decimalCount->set(2);
		$this->usd->clearRoundingRules();
		$this->usd->save();

		// initialize user
		$this->initUser();

		// initialize order
		$this->order = CustomerOrder::getNewInstance($this->user);
		$this->order->shippingAddress->set($this->user->defaultShippingAddress->get()->userAddress->get());
		$this->order->billingAddress->set($this->user->defaultBillingAddress->get()->userAddress->get());

		// set up products
		$product = Product::getNewInstance(Category::getInstanceById(Category::ROOT_ID), 'test1');
		$product->save();
		$product->setPrice('USD', 100);
		$product->stockCount->set(20);
		$product->isEnabled->set(1);
		$product->save();
		$this->products[] = $product;

		$product = Product::getNewInstance(Category::getInstanceById(Category::ROOT_ID), 'test2');
		$product->save();
		$product->setPrice('USD', 200);
		$product->stockCount->set(20);
		$product->isEnabled->set(1);
		$product->save();
		$this->products[] = $product;

		$product = Product::getNewInstance(Category::getInstanceById(Category::ROOT_ID), 'test3');
		$product->save();
		$product->setPrice('USD', 400);
		$product->isSeparateShipment->set(true);
		$product->stockCount->set(20);
		$product->isEnabled->set(1);
		$product->save();
		$this->products[] = $product;
	}

	public function assertEqual($a, $b)
	{
		return $this->assertEquals($a, $b);
	}

	public function assertIsA($object, $className)
	{
		return $this->assertTrue(get_class($object) == $className);
	}

	protected function pass()
	{
		$this->assertTrue(true);
	}

	protected function xfail()
	{
		$this->assertTrue(false);
	}

	protected function getApplication()
	{
		return ActiveRecordModel::getApplication();
	}
}

?>