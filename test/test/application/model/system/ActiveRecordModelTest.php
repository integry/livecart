<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';


/**
 * Common ActiveRecordModel tests
 *
 * @author Integry Systems
 * @package test.model.system
 */
class ActiveRecordModelTest extends LiveCartTest
{
	public function __construct()
	{
		parent::__construct('Test active record model');
	}

	public function getUsedSchemas()
	{
		return array(
			'User',
			'UserAddress'
		);
	}

	function testSerialization()
	{
		$user = User::getNewInstance('test@testzer.com');
		$user->firstName->set('Rinalds');
		$user->lastName->set('Uzkalns');
		$user->save();

		$address = UserAddress::getNewInstance();
		$address->city->set('Vilnius');
		$address->save();

		$billing = BillingAddress::getNewInstance($user, $address);
		$billing->save();

		$user->defaultBillingAddress->set($billing);

		$serialized = serialize($user);
		$unser = unserialize($serialized);

		$this->assertEqual($user->firstName, $unser->firstName);
		$this->assertEqual($user->defaultBillingAddress->userAddress->city, $unser->defaultBillingAddress->userAddress->city);
	}

	function testCloning()
	{
		$user = User::getNewInstance('test@tester.com');
		$user->firstName->set('Rinalds');
		$user->lastName->set('Uzkalns');
		$user->save();

		$state = State::getInstanceByID(2, ActiveRecordModel::LOAD_DATA);
		$address = UserAddress::getNewInstance();
		$address->city->set('Vilnius');
		$address->state->set($state);
		$address->save();

		$newAddress = clone $address;

		// simple value
		$this->assertEqual($address->city, $newAddress->city);

		// foreign key
		$this->assertEqual($address->state, $newAddress->state);

		$newAddress->save();

		// primary key (autoincrement)
		$this->assertNotEquals($address->getID(), $newAddress->getID());
	}
}

?>