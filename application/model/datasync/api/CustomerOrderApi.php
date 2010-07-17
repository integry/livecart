<?php

ClassLoader::import('application.model.datasync.ModelApi');
ClassLoader::import('application.model.datasync.api.reader.XmlCustomerOrderApiReader');
ClassLoader::import('application.model.datasync.import.CustomerOrderImport');
ClassLoader::import('application/model.datasync.CsvImportProfile');
ClassLoader::import('application/model.order.CustomerOrder');
ClassLoader::import('application.helper.LiveCartSimpleXMLElement');

/**
 * Web service access layer for CustomerOrder model
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 * 
 */

class CustomerOrderApi extends ModelApi
{
	private $listFilterMapping = null;
	protected $application;

	public static function canParse(Request $request)
	{
		return parent::canParse($request, array('XmlCustomerOrderApiReader'));
	}

	public function __construct(LiveCart $application)
	{
		parent::__construct(
			$application,
			'CustomerOrder',
			array() // fields to ignore in CustomerOrder model
		);

		$this->addSupportedApiActionName('invoice');
		$this->addSupportedApiActionName('capture');
		$this->addSupportedApiActionName('cancel');
		$this->addSupportedApiActionName('import');
	}

	public function invoice()
	{
		return $this->apiActionGetOrdersBySelectFilter(
			select(eq(
				f('CustomerOrder.invoiceNumber'),
				$this->getApplication()->getRequest()->get('ID'))));
	}

	public function get()
	{
		return $this->apiActionGetOrdersBySelectFilter(
			select(eq(
				f('CustomerOrder.ID'),
				$this->getApplication()->getRequest()->get('ID'))));
	}

	public function filter()
	{
		return $this->apiActionGetOrdersBySelectFilter($this->getParser()->getARSelectFilter(), true);
	}
	
	public function delete()
	{
		$order = CustomerOrder::getInstanceByID($this->getRequestID());
		$id = $order->getID();
		$order->delete();
		return $this->statusResponse($id, 'deleted');
	}

	public function capture()
	{
		$order = CustomerOrder::getInstanceByID($this->getRequestID());
		foreach ($order->getTransactions() as $transaction)
		{
			$transaction->capture($transaction->amount->get());
		}

		return $this->statusResponse($order->getID(), 'captured');
	}
	
	public function cancel()
	{
		$order = CustomerOrder::getInstanceByID($this->getRequestID());
		$order->cancel();

		return $this->statusResponse($order->getID(), 'canceled');
	}
	
	public function import()
	{
		$updater = new ApiCustomerOrderImport($this->application);
		$profile = new CsvImportProfile('CustomerOrder');
		$reader = $this->getDataImportIterator($updater, $profile);
		$updater->setCallback(array($this, 'importCallback'));
		$updater->importFile($reader, $profile);
		
		return $this->statusResponse($this->importedIDs, 'imported');
	}

	public function create()
	{
		$updater = $this->getImportHandler();
		$updater->allowOnlyCreate();
		$profile = new CsvImportProfile('CustomerOrder');
		$reader = $this->getDataImportIterator($updater, $profile);
		$updater->setCallback(array($this, 'importCallback'));
		$updater->importFile($reader, $profile);
		
		return $this->statusResponse($this->importedIDs, 'created');
	}

	public function update()
	{
		$updater = new ApiCustomerOrderImport($this->application);
		$updater->allowOnlyUpdate();
		$profile = new CsvImportProfile('CustomerOrder');
		$reader = $this->getDataImportIterator($updater, $profile);
		$updater->setCallback(array($this, 'importCallback'));
		$updater->importFile($reader, $profile);
		
		return $this->statusResponse($this->importedIDs, 'updated');
	}
	
	// --
	
	private function fillResponseItem($xml, $item)
	{
		parent::fillSimpleXmlResponseItem($xml, $item);
	
		$userFieldNames = array('userGroupID','email', 'firstName','lastName','companyName','isEnabled');
		$addressFieldNames = array('stateID','phone', 'firstName','lastName','companyName','phone', 'address1', 'address2', 'city', 'stateName', 'postalCode', 'countryID', 'countryName', 'fullName', 'compact');
		$cartItemFieldNames = array('name', 'customerOrderID', 'shipmentID', 'price', 'count', 'reservedProductCount',  'dateAdded', 'isSavedForLater');

		// User
		if(array_key_exists('User', $item))
		{
			foreach($userFieldNames as $fieldName)
			{
				$xml->addChild('User_'.$fieldName, isset($item['User'][$fieldName]) ? $item['User'][$fieldName] : '');
			}
		}

		// Shipping and billing addresses
		foreach(array('ShippingAddress','BillingAddress') as $addressType)
		{
			if(array_key_exists($addressType, $item))
			{
				foreach($addressFieldNames as $fieldName)
				{
					$xml->addChild($addressType.'_'.$fieldName, isset($item[$addressType], $item[$addressType][$fieldName]) ? $item[$addressType][$fieldName] : '');
				}
			}
		}
		
		// cart itmes
		if(array_key_exists('cartItems', $item))
		{
			$xmlProducts = $xml->addChild('Products');
			foreach($item['cartItems'] as $cartItem)
			{
				$ci = $xmlProducts->addChild('Product');
				$ci->addChild('sku', isset($cartItem['nameData'], $cartItem['nameData']['sku']) ? $cartItem['nameData']['sku'] : '');
				foreach($cartItemFieldNames as $fieldName)
				{
					$ci->addChild($fieldName, isset($cartItem[$fieldName]) ? $cartItem[$fieldName] : '');
				}
			}
		}

		// more?
	}

	// this one handles list(), filter(), get() and invoice() actions
	private function apiActionGetOrdersBySelectFilter($ARSelectFilter, $allowEmptyResponse=false)
	{
		set_time_limit(0);

		$customerOrders = ActiveRecordModel::getRecordSet('CustomerOrder', $ARSelectFilter, array('User'));
		if($allowEmptyResponse == false && count($customerOrders) == 0)
		{
			throw new Exception('Order not found');
		}
		$response = new LiveCartSimpleXMLElement('<response datetime="'.date('c').'"></response>');
		foreach($customerOrders as $order)
		{
			$order->loadAll();
			$transactions = $order->getTransactions();
			$this->fillResponseItem($response->addChild('order'), $order->toArray());
			
			unset($order);
			ActiveRecord::clearPool();
		}
		return new SimpleXMLResponse($response);
	}
}

// misc things
ClassLoader::import("application.model.datasync.import.UserImport");

class ApiCustomerOrderImport extends CustomerOrderImport
{
	const CREATE = 1;
	const UPDATE = 2;

	private $allowOnly = null;
	public function allowOnlyUpdate()
	{
		$this->allowOnly = self::UPDATE;
	}

	public function allowOnlyCreate()
	{
		$this->allowOnly = self::CREATE;
	}
	
	
	public function getClassName()
	{	
		return str_replace('Api', '', parent::getClassName());
	}
}

?>
