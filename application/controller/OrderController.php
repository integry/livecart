<?php

ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.Currency');

class OrderController extends FrontendController
{
    /**
     *  View shopping cart contents
     */
    public function index()
    {
    	$this->addBreadCrumb($this->translate('My Shopping Session'), Router::getInstance()->createUrlFromRoute($this->request->getValue('return')));
		$this->addBreadCrumb($this->translate('My Shopping Basket'), '');
		
		$order = CustomerOrder::getInstance();
		$order->loadItemData();		
		//print_r($order);
		
		$currency = Currency::getInstanceByID($this->request->getValue('currency', $this->store->getDefaultCurrencyCode()), Currency::LOAD_DATA);       
        		
		$response = new ActionResponse();
		$response->setValue('cart', $order->toArray());
		$response->setValue('form', $this->buildCartForm($order));
		$response->setValue('return', $this->request->getValue('return'));				
		$response->setValue('currency', $currency->getID());
		$response->setValue('orderTotal', $currency->getFormattedPrice($order->getSubTotal($currency)));
		return $response;
    }   
    
    /**
     *  Update product quantities
     */
    public function update()
    {
		$order = CustomerOrder::getInstance();
		foreach ($order->getOrderedItems() as $item)
		{
			if ($this->request->isValueSet('item_' . $item->getID()))
			{
				$item->count->set($this->request->getValue('item_' . $item->getID(), 0));
			}
		}		 
		
		$order->saveToSession();
		
        return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->getValue('return')));	      
    }

    /**
     *  Remove a product from shopping cart
     */
    public function delete()
    {
		$order = CustomerOrder::getInstance();
		$order->removeItem(ActiveRecordModel::getInstanceByID('OrderedItem', $this->request->getValue('id')));
		$order->saveToSession();
		
        return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->getValue('return')));	      		
    }
    
    /**
     *  Add a new product to shopping cart
     */
    public function addToCart()
    {
        $product = Product::getInstanceByID($this->request->getValue('id'));
        if (!$product->isAvailable())
        {
            throw new ApplicationException('The product ' . $product->sku->get() . '  is not available for ordering!'); 
        }
        
        $count = $this->request->getValue('count', 1);

        $order = CustomerOrder::getInstance();
        $order->addProduct($product, $count);
        $order->mergeItems();
        $order->saveToSession();

        return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->getValue('return')));
    }

	public function moveToCart()
	{
        $order = CustomerOrder::getInstance();
        $item = $order->getItemByID($this->request->getValue('id'));
        $item->isSavedForLater->set(false);
        $order->mergeItems();        
        $order->saveToSession();
		
        return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->getValue('return')));
	}

	public function moveToWishList()
	{
        $order = CustomerOrder::getInstance();
        $item = $order->getItemByID($this->request->getValue('id'));
        $item->isSavedForLater->set(true);
        $order->mergeItems();
        $order->saveToSession();
		
        return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->getValue('return')));
	}
	
    /**
     *  Add a new product to wish list (save items for buying later)
     */
    public function addToWishList()
    {
        $product = Product::getInstanceByID($this->request->getValue('id'), Product::LOAD_DATA);

        $order = CustomerOrder::getInstance();
        $order->addToWishList($product);
        $order->mergeItems();
        $order->saveToSession();
        
        return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->getValue('return')));
    }
    
	private function buildCartForm(CustomerOrder $order)
	{
		ClassLoader::import("framework.request.validator.Form");

		$form = new Form($this->buildCartValidator($order));
		
		foreach ($order->getOrderedItems() as $item)
		{
			$name = 'item_' . $item->getID();
			$form->setValue($name, $item->count->get());			
		}
		
		return $form;
	}
	
	/**
	 * @return RequestValidator
	 */
	private function buildCartValidator(CustomerOrder $order)
	{
		ClassLoader::import("framework.request.validator.RequestValidator");
		
		$validator = new RequestValidator("cartValidator", $this->request);

		foreach ($order->getOrderedItems() as $item)
		{
			$name = 'item_' . $item->getID();
			$validator->addCheck($name, new IsNumericCheck($this->translate('_err_not_numeric')));	
			$validator->addFilter($name, new NumericFilter());	
		}
		
		return $validator;
	}	
}

?>