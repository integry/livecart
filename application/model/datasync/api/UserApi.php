<?php

// id - 582

ClassLoader::import('application.model.datasync.ModelApi');
ClassLoader::import('application.model.datasync.api.reader.XmlUserApiReader');
ClassLoader::import('application/model.datasync.CsvImportProfile');
		
/**
 * Web service access layer for User model
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 * 
 */

class UserApi extends ModelApi
{
	private $listFilterMapping = null;
	protected $application;


	public static function canParse(Request $request)
	{
		return parent::canParse($request, array('XmlUserApiReader'));
	}

	public function __construct(LiveCart $application)
	{
		parent::__construct(
			$application,
			'User',
			array() // fields to ignore in User model
		);
	}

	// ------ 

	public function create()
	{
		$updater = new ApiUserImport($this->application);
		$updater->allowOnlyCreate();
		$profile = new CsvImportProfile('User');
		$reader = $this->getDataImportIterator($updater, $profile);
		$updater->setCallback(array($this, 'userImportCallback'));
		$updater->importFile($reader, $profile);

		return $this->statusResponse($this->importedIDs, 'created');
	}
	
	public function update()
	{
		$updater = new ApiUserImport($this->application);
		$updater->allowOnlyUpdate();
		$profile = new CsvImportProfile('User');
		$reader = $this->getDataImportIterator($updater, $profile);
		$updater->setCallback(array($this, 'userImportCallback'));
		$updater->importFile($reader, $profile);

		return $this->statusResponse($this->importedIDs, 'updated');
	}

	public function delete()
	{
		$request = $this->getApplication()->getRequest();
		$id = $this->getRequestID();
		$instance = User::getInstanceByID($id, true);
		$instance->delete();
		return $this->statusResponse($id, 'deleted');
	}

	public function get()
	{
		$request = $this->application->getRequest();
		$parser = $this->getParser();
		$users = ActiveRecordModel::getRecordSetArray('User',
			select(eq(f('User.ID'), $this->getRequestID()))
		);
		if(count($users) == 0)
		{
			throw new Exception('User not found');
		}
		$apiFieldNames = $parser->getApiFieldNames();

		// --
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		$responseCustomer = $response->addChild('customer');
		while($user = array_shift($users))
		{
			foreach($user as $k => $v)
			{
				if(in_array($k, $apiFieldNames))
				{
					$responseCustomer->addChild($k, $v);
				}
			}
		}
		return new SimpleXMLResponse($response);
	}

	public function filter() // synonym to list method
	{
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		$parser = $this->getParser();
		$customers = User::getRecordSetArray('User',$parser->getARSelectFilter(), true);

		// $addressFieldNames = array_keys(ActiveRecordModel::getSchemaInstance('UserAddress')->getFieldList());
		$addressFieldNames = array('firstName', 'lastName', 'address1', 'address2', 'city', 'stateName', 'postalCode', 'phone');
		$userFieldNames = $parser->getApiFieldNames();

		foreach($customers as $customer)
		{
			$customerNode = $response->addChild('customer');
			foreach($userFieldNames as $fieldName)
			{
				$customerNode->addChild($fieldName, is_string($customer[$fieldName])? $customer[$fieldName] : '');
			}

			// todo: join? how?? m?!
			$u = User::getInstanceByID($customer['ID']);	
			$u->loadAddresses();
			// default billing and shipping addreses
			foreach(array('defaultShippingAddress', 'defaultBillingAddress') as $addressType)
			{
				if(is_numeric($customer[$addressType.'ID']))
				{
					$address = $u->defaultBillingAddress->get()->userAddressID->get();
					foreach($addressFieldNames as $addressFieldName)
					{
						$customerNode->addChild($addressType.'_'.$addressFieldName, $address->$addressFieldName->get());
					}
				}
			}
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

class ApiUserImport extends UserImport
{
	const CREATE = 1;
	const UPDATE = 2;
	
	private $allowOnly = null;

	public function allowOnlyUpdate()
	{
		$this->allowOnly = self::UPDATE;
	}

	public function getClassName($classNameToCompare=null, $instanceClassName=null)  // because dataImport::getClassName() will return ApiUser, not User.
	{
		echo $instanceClassName;
		if($instanceClassName == 'userAddress')
		{
			
			echo 1111;
			return $classNameToCompare;
		}
		return substr(parent::getClassName(),3); // cut off Api from class name
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
