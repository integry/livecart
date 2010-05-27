<?php
ClassLoader::import('application.model.datasync.ModelApi');
ClassLoader::import('application.model.datasync.api.reader.XmlCustomerOrderApiReader');
ClassLoader::import('application/model.datasync.CsvImportProfile');
ClassLoader::import('application/model.order.CustomerOrder');

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
	}

	// ------ 

	public function create()
	{
		die('create');
	}
	
	public function update()
	{
		die('update');
	}

	public function delete()
	{
		die('delete');
	}

	public function get()
	{
		$request = $this->getApplication()->getRequest();
		$id = $request->get('ID');
		$customerOrders = ActiveRecordModel::getRecordSetArray('CustomerOrder',
			select(eq(f(is_numeric($id)?'CustomerOrder.ID':'CustomerOrder.invoiceNumber'), $id))
		);

		$parser = $this->getParser();
		$apiFieldNames = $parser->getApiFieldNames();
		// --
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		$responseItem = $response->addChild('order');
		while($item = array_shift($customerOrders))
		{
			foreach($item as $k => $v)
			{
				if(in_array($k, $apiFieldNames))
				{
					$responseItem->addChild($k, $v);
				}
			}
		}
		return new SimpleXMLResponse($response);
	}

	public function filter()
	{
		set_time_limit(0);
		$request = $this->application->getRequest();
		$parser = $this->getParser();
		$apiFieldNames = $parser->getApiFieldNames();
		$customerOrders = ActiveRecordModel::getRecordSet('CustomerOrder',
			select()
		);
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		foreach ($customerOrders as $order)
		{
			$order->loadAll();
			$transactions = $order->getTransactions;
			$item = $order->toArray();

			//pp($apiFieldNames, $item);
			$xmlItem = $response->addChild('order');
			foreach($item as $k => $v)
			{
				if(in_array($k, $apiFieldNames))
				{
					// todo: how to escape in simplexml, cdata? create cdata or what?
					$xmlItem->addChild($k, htmlentities($v));
				}
			}
			unset($order);
			ActiveRecord::clearPool();
		}
		return new SimpleXMLResponse($response);
	}

	// ------ 
	
	public function userImportCallback($record)
	{
		$this->importedIDs[] = $record->getID();
	}

	private function getDataImportIterator($updater, $profile)
	{
		// parser can act as DataImport::importFile() iterator
		$parser = $this->getParser();
		$parser->populate($updater, $profile);
		return $parser;
	}
}

ClassLoader::import("application.model.datasync.import.UserImport");

// misc things
// @todo: in seperate file!
class ApiOrderImport extends UserImport
{
	const CREATE = 1;
	const UPDATE = 2;
	
	private $allowOnly = null;

	public function allowOnlyUpdate()
	{
		$this->allowOnly = self::UPDATE;
	}

	public function getClassName()  // because dataImport::getClassName() will return ApiUser, not User.
	{
		return 'Order';
	}

	public function allowOnlyCreate()
	{
		$this->allowOnly = self::CREATE;
	}

	public // one (bad) implementation of delete() action calls this method, therefore public
	function getInstance($record, CsvImportProfile $profile)
	{
		$instance = parent::getInstance($record, $profile);
		$id = $instance->getID();
		if($this->allowOnly == self::CREATE && $id > 0) 
		{
			throw new Exception('Record exists');
		}
		if($this->allowOnly == self::UPDATE && $id == 0) 
		{
			throw new Exception('Record not found');
		}
		return $instance;
	}

}

?>