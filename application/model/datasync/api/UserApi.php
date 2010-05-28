<?php

/**
 * Web service access layer for User model
 *
 * @package application.model.datasync.api
 * @author Integry Systems <http://integry.com>
 * 
 */

ClassLoader::import("application.model.datasync.ModelApi");

class UserApi extends ModelApi
{
	private $listFilterMapping = null;

	protected $importedIDs = array();
	protected $application;

	public static function canParse(Request $request)
	{
		$get = $request->getRawGet();
		if(array_key_exists('xml',$get))
		{
			$xml = self::getSanitizedSimpleXml($get['xml']);
			if($xml != null)
			{
				if(count($xml->xpath('/request/customer')) == 1)
				{
					$request->set('userApiXmlData',$xml);
					return true; // yes, can parse
				}
			}
		}
	}

	public function __construct(LiveCart $application)
	{
		$this->application = $application;
		parent::__construct('User');
	}

	public function updateCallback($record, $updated)
	{
		echo '[update callback]';
		echo $updated ? 'update existing' : 'created new';
		print_r($record->getID());
		
		$this->importedIDs[] = $record;
	}

	public function getApiActionName()
	{
		if(parent::getApiActionName() == null)
		{
			$xmlKeyToApiActionMapping = array(
				// 'filter' => 'list' filter is better than list, because list is keyword.
			);
			$xml = $this->application->getRequest()->get('userApiXmlData');
			$customerNodeChilds = $xml->xpath('//customer/*');
			$firstCustomerNodeChild = array_shift($customerNodeChilds);
			if($firstCustomerNodeChild)
			{
				$apiActionName = $firstCustomerNodeChild->getName();
				$this->setApiActionName(canParse
					array_key_exists($apiActionName,$xmlKeyToApiActionMapping)?$xmlKeyToApiActionMapping[$apiActionName]:$apiActionName
				);
			}
		}
		return parent::getApiActionName();
	}

	// -------------------
	public function filter()
	{
		$customers = User::getRecordSet($this->getARSelectFilter());

		// request
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		while($customer = $customers->shift())
		{
			$customerNode = $response->addChild('customer');
			$customerNode->addChild('custno', $customer->getID());
			
			$ormXmlAddressMapping = array(
				'address1'=>'address_1',
				'address2'=>'address_2',
				'city'=>'city',
				'stateName'=>'state_name',
				'postalCode'=>'postal_code',
				'phone'=>'phone'
			);
			$customerNode->addChild('name', $customer->firstName->get(),' '.$customer->lastName->get());
			foreach(array('billing', 'shipping') as $z)
			{
				$mn = 'get'.ucfirst($z).'AddressArray'; // getBillingAddressArray() or getShippingAddressArray()
				foreach($customer->$mn() as $a)
				{
					foreach($ormXmlAddressMapping as $addressOrmKey=>$addressXmlKey)
					{
						$customerNode->addChild($z.'_'.$addressXmlKey,$a['UserAddress'][$addressOrmKey]);
					}
				}
			}
		}
		return new SimpleXMLResponse($response);
	}

	public function update()
	{
		ClassLoader::import("application/model.datasync.CsvImportProfile");

		$updater = new ApiUserImport($this->application);
		$updater->allowOnlyUpdate();
		$profile = new CsvImportProfile('User');

		$reader = $this->getUpdateIterator($this->application->getRequest()->get('userApiXmlData'), $updater, $profile);
		
		

		$updater->setCallback(array($this, 'updateCallback'));
		$updater->importFile($reader, $profile);

		// print_r($updater->getFields());
		exit;
			
		// 
		$user = User::getInstanceByEmail($u['email']);
		if($user == null)
		{
			throw new Exception('User does not exists');
		}
		
		$u = $request->getUserData();
		$user->loadRequestData($request->getUpdateRequest());
		$user->firstName->set($u['firstName']);
		$user->lastName->set($u['lastName']);
		$user->companyName->set($u['companyName']);
		// $user->isEnabled->set(TRUE);
		$user->save();
		$response->addChild('updated', $user->getID());
		return new SimpleXMLResponse($response);
	}

	public function getUpdateIterator($xml, $updater, $profile)
	{

		// todo: multiple customers
		$iterator = new UpdateIterator();
		$item = array('ID'=>null, 'email'=>null);
		foreach ($updater->getFields() as $group => $fields)
		{
			foreach ($fields as $field => $name)
			{
				list($class, $fieldName) = explode('.', $field);
				if ($class != $profile->getClassName())
				{
					$fieldName = $class . '_' . $fieldName;
				}
				$v = $xml->xpath('/request/customer/update/'.$fieldName);
				if(count($v) > 0)
				{
					$item[$fieldName] = (string)$v[0];
					$profile->setField($fieldName, $field);
				}
			}
		}

		if($item['ID'] || $item['email'])
		{
			$iterator->addItem($item);
		}
		return $iterator;
	}

	public function getListFilterMapping()
	{
		if($this->listFilterMapping == null)
		{
			$cn = $this->getClassName();
			$this->listFilterMapping = array(
				'id' => array(
					self::HANDLE => new ARFieldHandle($cn, 'ID'),
					self::CONDITION => 'EqualsCond'),
				'name' => array(
					self::HANDLE => new ARExpressionHandle("CONCAT(".$cn.".firstName,' ',".$cn.".lastName)"),
					self::CONDITION => 'LikeCond'),
				'first_name' => array(
					self::HANDLE => new ARFieldHandle($cn, 'firstName'),
					self::CONDITION => 'LikeCond'),
				'last_name' => array(
					self::HANDLE => new ARFieldHandle($cn, 'lastName'),
					self::CONDITION => 'LikeCond'),
				'company_name' => array(
					self::HANDLE => new ARFieldHandle($cn, 'companyName'),
					self::CONDITION => 'LikeCond'),
				'email' => array(
					self::HANDLE => new ARFieldHandle($cn, 'email'),
					self::CONDITION => 'LikeCond'),
				'created' => array(
					self::HANDLE => new ARFieldHandle($cn, 'dateCreated'),
					self::CONDITION => 'EqualsCond'),
				'enabled' => array(
					self::HANDLE => new ARFieldHandle($cn, 'isEnabled'),
					self::CONDITION => 'EqualsCond')
			);
		}
		return $this->listFilterMapping;
	}

	public function sanitizeFilterField($name, &$value)
	{
		switch($name)
		{
			case 'enabled':
				$value = in_array(strtolower($value), array('y','t','yes','true','1')) ? true : false;
				break;
		}
		return $value;
	}

	public function getARSelectFilter()
	{
		$arsf = new ARSelectFilter();
		$xml = $this->application->getRequest()->get('userApiXmlData');
		$ormClassName = $this->getClassName();
		$filterKeys = $this->getListFilterKeys();

		foreach($filterKeys as $key)
		{
			$data = $xml->xpath('//filter/'.$key);
			while(count($data) > 0)
			{
				$z  = $this->getListFilterConditionAndARHandle($key);
				$value = (string)array_shift($data);
				$arsf->mergeCondition(
					new $z[self::CONDITION](
						$z[self::HANDLE],						
						$this->sanitizeFilterField($key, $value)
					)
				);
			}
		}
		return $arsf;
	}
}

// misc things
// thingies.. 

ClassLoader::import("application.model.datasync.import.UserImport");

class ApiUserImport extends UserImport
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

	protected function getInstance($record, CsvImportProfile $profile)
	{
		$instance = parent::getInstance($record, $profile);
		$id = $instance->getID();
		if($this->allowOnly == self::CREATE && $id > 0) 
		{
			throw new Exception('Cannot create, record exists');
		}
		if($this->allowOnly == self::UPDATE && $id == 0) 
		{
			throw new Exception('Cannot update, record not found');
		}
		return $instance;
	}
}



/**
 * Update iterator for data import
 *
 * @see DataImport
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 */

class UpdateIterator implements Iterator
{
	protected $iteratorKey = 0;
	protected $content;

	public function addItem($item)
	{
		$this->content[] = $item;
	}

	public function rewind()
	{
		$this->iteratorKey = 0;
	}

	public function valid()
	{
		return $this->iteratorKey < count($this->content);
	}

	public function next()
	{
		$this->iteratorKey++;
	}

	public function key()
	{
		return $this->iteratorKey;
	}

	public function current()
	{
		return $this->content[$this->iteratorKey];
	}
}

?>
