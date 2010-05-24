<?php

ClassLoader::import('application.model.datasync.api.reader.UserApiReader');

/**
 * User model API XML format request parsing (reading/routing)
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 */

class XmlUserApiReader extends UserApiReader
{
	const HANDLE = 0;
	const CONDITION = 1;
	const ALL_KEYS = -1;
	//protected $xmlKeyToApiActionMapping = array(
		// 'filter' => 'list' filter is better than list, because list is keyword.
	//);
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

	protected function findApiActionName($xml)
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
