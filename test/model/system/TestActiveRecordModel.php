<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.user.User');

/**
 * Common ActiveRecordModel tests
 *
 * @author Integry Systems
 * @package test.model.system
 */
class TestActiveRecordModel extends UnitTest
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
        $user = User::getNewInstance();        
        $user->firstName->set('Rinalds');
        $user->lastName->set('Uzkalns');
        $user->save();
        
        $address = UserAddress::getNewInstance();
        $address->city->set('Vilnius');
        $address->save();
        $user->defaultBillingAddress->set($address);        
                
        $serialized = serialize($user);        
        $unser = unserialize($serialized);

        $this->assertEqual($user->firstName->get(), $unser->firstName->get());
        $this->assertEqual($user->defaultBillingAddress->get()->city->get(), $unser->defaultBillingAddress->get()->city->get());
    }
    
    function testCloning()
    {
        $user = User::getNewInstance();        
        $user->firstName->set('Rinalds');
        $user->lastName->set('Uzkalns');
        $user->save();
        
        $state = ActiveRecordModel::getInstanceByID('State', 2, ActiveRecordModel::LOAD_DATA);
        $address = UserAddress::getNewInstance();
        $address->city->set('Vilnius');
        $address->state->set($state);
        $address->save();

        $newAddress = clone $address;
        
        // simple value
        $this->assertEqual($address->city->get(), $newAddress->city->get());
        
        // foreign key
        $this->assertEqual($address->state->get(), $newAddress->state->get());
        
        $newAddress->save();
        
        // primary key (autoincrement)
        $this->assertNotEqual($address->getID(), $newAddress->getID());        
    }
}

?>