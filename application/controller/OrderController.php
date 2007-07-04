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
    	$this->addBreadCrumb($this->translate('My Shopping Session'), $this->router->createUrlFromRoute($this->request->getValue('return')));
		$this->addBreadCrumb($this->translate('My Shopping Basket'), '');
		
		$this->order->loadItemData();		
		
        $currency = Currency::getValidInstanceByID($this->request->getValue('currency', $this->store->getDefaultCurrencyCode()), Currency::LOAD_DATA);                   
        		
		$response = new ActionResponse();
		$response->setValue('cart', $this->order->toArray());
		$response->setValue('form', $this->buildCartForm($this->order));
		$response->setValue('return', $this->request->getValue('return'));				
		$response->setValue('currency', $currency->getID());
		$response->setValue('orderTotal', $currency->getFormattedPrice($this->order->getSubTotal($currency)));
		return $response;
    }   
    
    /**
     *  Update product quantities
     */
    public function update()
    {
		foreach ($this->order->getOrderedItems() as $item)
		{
			if ($this->request->isValueSet('item_' . $item->getID()))
			{
				$this->order->updateCount($item, $this->request->getValue('item_' . $item->getID(), 0));
			}
		}		 
		
        SessionOrder::save($this->order);
		
        return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->getValue('return')));	      
    }

    /**
     *  Remove a product from shopping cart
     */
    public function delete()
    {
		$this->order->removeItem(ActiveRecordModel::getInstanceByID('OrderedItem', $this->request->getValue('id')));
		SessionOrder::save($this->order);
		
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

        $this->order->addProduct($product, $count);
        $this->order->mergeItems();
        SessionOrder::save($this->order);
    
        return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->getValue('return')));
    }

	public function moveToCart()
	{
        $item = $this->order->getItemByID($this->request->getValue('id'));
        $item->isSavedForLater->set(false);
        $this->order->mergeItems();        
        $this->order->resetShipments();
        SessionOrder::save($this->order);
		
        return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->getValue('return')));
	}

	public function moveToWishList()
	{
        $item = $this->order->getItemByID($this->request->getValue('id'));
        $item->isSavedForLater->set(true);
        $this->order->mergeItems();
        $this->order->resetShipments();
        SessionOrder::save($this->order);
		
        return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->getValue('return')));
	}
	
    /**
     *  Add a new product to wish list (save items for buying later)
     */
    public function addToWishList()
    {
        $product = Product::getInstanceByID($this->request->getValue('id'), Product::LOAD_DATA);

        $this->order->addToWishList($product);
        $this->order->mergeItems();
        SessionOrder::save($this->order);
              
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