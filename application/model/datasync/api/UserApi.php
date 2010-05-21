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
		if(XmlUserApiReader::canParse($request))
		{
			return true;
		}
		return false;
	}

	public function __construct(LiveCart $application)
	{
		$this->application = $application;
		$request = $this->application->getRequest();
		// ---
		$this->setParserClassName($request->get('_ApiParserClassName'));
		$cn = $this->getParserClassName();
		$this->setParser(new $cn($request->get('_ApiParserData')));
		// --
		parent::__construct('User');
	}

	public function userImportCallback($record, $updated)
	{
		$this->importedIDs[] = $record->getID();
	}

	public function filter()
	{
		$parser = $this->getParser();
		$customers = User::getRecordSet($parser->getARSelectFilter());
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
		$reader = $this->getDataImportIterator($updater, $profile);
		$updater->setCallback(array($this, 'userImportCallback'));
		$updater->importFile($reader, $profile);

		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		foreach($this->importedIDs as $id)
		{
			$response->addChild('updated', $id);
		}
		return new SimpleXMLResponse($response);
	}

	public function create()
	{
		ClassLoader::import("application/model.datasync.CsvImportProfile");

		$updater = new ApiUserImport($this->application);
		$updater->allowOnlyCreate();
		$profile = new CsvImportProfile('User');
		$reader = $this->getDataImportIterator($updater, $profile);
		$updater->setCallback(array($this, 'userImportCallback'));
		$updater->importFile($reader, $profile);
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		foreach($this->importedIDs as $id)
		{
			$response->addChild('created', $id);
		}
		return new SimpleXMLResponse($response);
	}

	public function delete()
	{
		ClassLoader::import("application/model.datasync.CsvImportProfile");
		$updater = new ApiUserImport($this->application);
		$updater->allowOnlyUpdate(); // throws exception, if record does not exists.
		$profile = new CsvImportProfile('User');
		$reader = $this->getDataImportIterator($updater, $profile);
		$user = $updater->getInstance($reader->current(), $profile);
		$id = $user->getID();
		$user->delete();
		return new SimpleXMLResponse(new SimpleXMLElement('<response datetime="'.date('c').'"><deleted>'.$id.'</deleted></response>'));
	}

	private function getDataImportIterator($updater, $profile)
	{
		// parser can act as DataImport::importFile() iterator
		$parser = $this->getParser();
		$parser->populate($updater, $profile);
		return $parser;
	}
}

// misc things

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

	public function getClassName()  // because dataImport::getClassName() will return ApiUser, not User.
	{
		return 'User';
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


class UserApiReader implements Iterator {
	protected $iteratorKey = 0;
	protected $content;
	private $apiActionName;
	
	public function getApiActionName()
	{
		return $this->apiActionName;
	}
	
	public function setApiActionName($apiActionName)
	{
		$this->apiActionName=$apiActionName;
	}

	public function addItem($item)
	{
		$this->content[] = $item;
	}
	// --
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

class XmlUserApiReader extends UserApiReader
{
	const HANDLE = 0;
	const CONDITION = 1;
	const ALL_KEYS = -1;
	private $xmlKeyToApiActionMapping = array(
		// 'filter' => 'list' filter is better than list, because list is keyword.
	);
	private $apiActionName;
	private $listFilterMapping;

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
					$request->set('_ApiParserData',$xml);
					$request->set('_ApiParserClassName', 'XmlUserApiReader');
					return true; // yes, can parse
				}
			}
		}
	}

	protected static function getSanitizedSimpleXml($xmlString)
	{
		try {
			$xmlRequest = @simplexml_load_string($xmlString);
			if(!is_object($xmlRequest) || $xmlRequest->getName() != 'request') {
				$xmlRequest = @simplexml_load_string('<request>'.$xmlString.'</request>');
			}
		} catch(Exception $e) {
			$xmlRequest = null;
		}
		if(!is_object($xmlRequest) || $xmlRequest->getName() != 'request') { // still nothing?
			throw new Exception('Bad request');
		}
		return $xmlRequest;
	}

	public function __construct($xml)
	{
		// todo: multiple customers
		$this->xml = $xml;
		$this->findApiActionName($xml);
		$apiActionName = $this->getApiActionName();
	}
	
	public function populate($updater, $profile)
	{
		$item = array('ID'=>null, 'email'=>null);
		$apiActionName = $this->getApiActionName();
		foreach ($updater->getFields() as $group => $fields)
		{
			foreach ($fields as $field => $name)
			{
				list($class, $fieldName) = explode('.', $field);
				if ($class != $profile->getClassName())
				{
					$fieldName = $class . '_' . $fieldName;
				}
				$v = $this->xml->xpath('/request/customer/'.$apiActionName.'/'.$fieldName);
				if(count($v) > 0)
				{
					$item[$fieldName] = (string)$v[0];
					$profile->setField($fieldName, $field);
				}
			}
		}
		if($item['ID'] || $item['email'])
		{
			$this->addItem($item);
		}
	}
	
	public function getARSelectFilter()
	{
		$arsf = new ARSelectFilter();
		
		$ormClassName = 'User';
		$filterKeys = $this->getListFilterKeys();

		foreach($filterKeys as $key)
		{
			$data = $this->xml->xpath('//filter/'.$key);
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
	
	public function getListFilterMapping()
	{
		if($this->listFilterMapping == null)
		{
			$cn = 'User';
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
	
	
	public function getListFilterConditionAndARHandle($key)
	{
		$mapping = $this->getListFilterMapping();
		if(array_key_exists($key, $mapping) == false || array_key_exists(self::CONDITION, $mapping[$key]) == false)
		{
			throw new Exception('Condition for key ['.$key.'] not found in mapping');
		}
		if(array_key_exists($key, $mapping) == false || array_key_exists(self::HANDLE, $mapping[$key]) == false)
		{
			throw new Exception('Handle for key ['.$key.'] not found in mapping');
		}

		return $mapping[$key];
	}
	
	public function getListFilterCondition($key)
	{
		$r = $this->getListFilterConditionAndARHandle($key);
		return $r[$key][self::CONDITION];
	}
	
	public function getListFilterARHandle($key)
	{
		$r = $this->getListFilterConditionAndARHandle($key);
		return $r[$key][self::HANDLE];
	}

	public function getListFilterKeys()
	{
		return array_keys($this->getListFilterMapping());
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

	public function getApiActionName()
	{
		return $this->apiActionName;
	}

	private function findApiActionName($xml)
	{
		$customerNodeChilds = $xml->xpath('//customer/*');
		$firstCustomerNodeChild = array_shift($customerNodeChilds);
		if($firstCustomerNodeChild)
		{
			$apiActionName = $firstCustomerNodeChild->getName();
			$this->apiActionName = array_key_exists($apiActionName,$this->xmlKeyToApiActionMapping)?$this->xmlKeyToApiActionMapping[$apiActionName]:$apiActionName;
		}
		return null;
	}
}
?>
