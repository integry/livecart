<?php

ClassLoader::import('application.model.order.CustomerOrder');

class OrderController extends FrontendController
{
    /**
     *  View shopping cart contents
     */
    function index()
    {
        
    }   
    
    /**
     *  Update product quantities
     */
    function update()
    {
        
    }

    /**
     *  Remove a product from shopping cart
     */
    function delete()
    {
        
    }
    
    /**
     *  Add a new product to shopping cart
     */
    function addToCart()
    {
        $product = Product::getInstanceByID($this->request->getValue('id'));
        if (!$product->isAvailable())
        {
            throw new ApplicationException('The product ' . $product->sku->get() . '  is not available for ordering!'); 
        }
        
        $count = $this->request->getValue('count', 1);

        $order = CustomerOrder::getInstance();
        $order->addProduct($product, $count);
        $order->save();
        
        return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->getValue('return')));
    }

    /**
     *  Add a new product to wish list (save items for buying later)
     */
    function addToWishList()
    {
        
    }
}

?>