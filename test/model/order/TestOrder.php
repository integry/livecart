<?php

if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");
ClassLoader::import("application.model.order.*");
ClassLoader::import("application.model.user.*");
ClassLoader::import("library.payment.*");

/**
 *	Test Order model for the following scenarios:
 */ 
class TestOrder extends UnitTest
{  
    private $order;
    
    private $products = array();
    
    private $usd;
    
    function __construct()
    {
        parent::__construct();
        ActiveRecordModel::beginTransaction();            
                
        // set up currency
        try
        {
            $this->usd = Currency::getInstanceByID('USD', Currency::LOAD_DATA);
        }
        catch (ARNotFoundException $e)
        {
            $this->usd = Currency::getNewInstance('USD');
            $this->usd->setAsDefault();
            $this->usd->save();
        }        

        // initialize order
        $user = User::getNewInstance();
        $user->save();
        
        $address = UserAddress::getNewInstance();
        $address->countryID->set('US');
        $state = State::getInstanceById(1, State::LOAD_DATA);
        $address->state->set(State::getInstanceById(1));
        $address->postalCode->set(90210);
        $address->save();
        $billing = BillingAddress::getNewInstance($user, $address);
        $billing->save();
        
        $address = clone $address;
        $shipping = ShippingAddress::getNewInstance($user, $address);
        $shipping->save();
        
        $user->defaultBillingAddress->set($billing);
        $user->defaultShippingAddress->set($shipping);
        $user->save();
                                
        $this->order = CustomerOrder::getNewInstance($user);
        $this->order->shippingAddress->set($shipping->userAddress->get());
        $this->order->billingAddress->set($billing->userAddress->get());
        $this->order->save();

        // set up products
        $product = Product::getNewInstance(Category::getInstanceById(Category::ROOT_ID));   
        $product->save();
        $product->setPrice('USD', 100);
        $product->stockCount->set(20);
        $product->save();
        $product->isEnabled->set(true);
        $this->products[] = $product;

        $product = Product::getNewInstance(Category::getInstanceById(Category::ROOT_ID));   
        $product->save();
        $product->setPrice('USD', 200);
        $product->stockCount->set(20);
        $product->isEnabled->set(true);
        $product->save();
        $this->products[] = $product;
        
        $product = Product::getNewInstance(Category::getInstanceById(Category::ROOT_ID));   
        $product->save();
        $product->setPrice('USD', 400);
        $product->isSeparateShipment->set(true);
        $product->stockCount->set(20);
        $product->isEnabled->set(true);
        $product->save();
        $this->products[] = $product;
    }
    
    function tearDown()
    {
        $this->order->save();
    }
    
    function testAddingToCartWithInvalidProductCount()
    {        
        // negative
        try
        {
            $this->order->addProduct($this->products[0], -1);   
            $this->fail(); 
        }
        catch (Exception $e)
        {
            $this->pass();
        }
    }    
    
    function testAddingToAndRemovingFromCart()
    {        
        $this->order->addProduct($this->products[0], 1);   
        $this->pass(); 
        
        $this->order->addProduct($this->products[0], 0);
        $this->pass(); 
        
        $this->assertEqual($this->order->getSubTotal($this->usd), 0);
    }    

    function testSubTotal()
    {        
        $subtotal = 0;
        foreach ($this->products as $product)
        {
            $this->order->addProduct($product, 1); 
            $subtotal += $product->getPrice('USD');
        }
        $this->assertEqual($this->order->getSubTotal($this->usd), $subtotal);
    }    

    function testShipments()
    {
        $this->order->save();
        $this->assertEqual($this->order->getShipments()->size(), 2);

        $this->order->removeProduct($this->products[2]);
        $this->assertEqual($this->order->getShipments()->size(), 1);
    }
    
    function testSerialization()
    {
        $rates = new ShippingRateSet();
        
        $rate = new ShipmentDeliveryRate();
        $rate->setServiceID(12);
        $rate->setCost(33, 'USD');
        $rates->add($rate);
        
        $rate = new ShipmentDeliveryRate();
        $rate->setServiceID(14);
        $rate->setCost(53, 'USD');
        $rates->add($rate);
        
        $shipments = $this->order->getShipments();
        
        foreach ($shipments as $shipment)
        {
            $shipment->setAvailableRates($rates);
        }
        
        $subTotal = $this->order->getSubTotal($this->usd);

        // make sure none of the old objects are used after unserialization
        ActiveRecord::clearPool();

        $this->order = unserialize(serialize($this->order));
        
        $this->assertEqual($subTotal, $this->order->getSubTotal($this->usd));        
        
        $this->assertEqual($shipments->size(), $this->order->getShipments()->size());
        $this->assertEqual(count($shipments->get(0)->getItems()), count($this->order->getShipments()->get(0)->getItems()));
    }

    function testFinalize()
    {
        $total = $this->order->getTotal($this->usd);
        $this->order->finalize($this->usd);
        
        ActiveRecord::clearPool();        
        
        // reload the whole order data - the calculated total should still match
        $order = CustomerOrder::getInstanceById($this->order->getID());        
        $this->assertEqual($total, $order->getTotal($this->usd));
    
        // change price for one product...
        foreach ($order->getShoppingCartItems() as $item)
        {
            $product = $item->product->get();
            $product->setPrice('USD', $product->getPrice('USD') + 10);            
            break;
        }

        // ...so the new total calculated total would be different
        $this->assertNotEqual($total, $order->getTotal($this->usd));

        // however the "closed" price should still be the same as this order is already finalized
        $this->assertEqual($total, $order->totalAmount->get());               
    }

    function testPayment()
    {
        $result = new TransactionResult();
        $result->amount->set($this->order->totalAmount->get());
        $result->currency->set($this->order->currency->get()->getID());
        $result->gatewayTransactionID->set('TESTTRANSACTION');
        $result->setAsCaptured();
        
        $transaction = Transaction::getNewInstance($this->order, $result);
        $transaction->save();
        
        $this->assertEqual($this->order->totalAmount->get(), $this->order->capturedAmount->get());
    }

    function test_SuiteTearDown()
    {
        ActiveRecordModel::rollback();
    }   
}

?>