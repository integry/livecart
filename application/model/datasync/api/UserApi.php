<?php

ClassLoader::import('application.model.datasync.ModelApi');
ClassLoader::import('application.model.datasync.api.reader.XmlUserApiReader');
ClassLoader::import('application/model.datasync.CsvImportProfile');
ClassLoader::import('application.helper.LiveCartSimpleXMLElement');

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
			array('preferences') // fields to ignore in User model
		);
		$this->addSupportedApiActionName('import');
	}

	// ------

	public function import()
	{
		$updater = new ApiUserImport($this->application);
		$profile = new CsvImportProfile('User');
		$reader = $this->getDataImportIterator($updater, $profile);
		$updater->setCallback(array($this, 'importCallback'));
		$updater->importFile($reader, $profile);

		return $this->statusResponse($this->importedIDs, 'imported');
	}


	public function create()
	{
		$updater = $this->getImportHandler();
		$updater->allowOnlyCreate();
		$profile = new CsvImportProfile('User');
		$reader = $this->getDataImportIterator($updater, $profile);
		$updater->setCallback(array($this, 'importCallback'));
		$updater->importFile($reader, $profile);

		return $this->statusResponse($this->importedIDs, 'created');
	}

	public function update()
	{
		//
		// DataImport will find user by id, if not found by email, if not found then create new
		// if requesting to change user email (provaiding ID and new email),
		//
		// threrefore check if user exists here.
		//
		$request = $this->application->getRequest();
		$id = $this->getRequestID(true);
		if($id != '' && $request->get('email') != '')
		{
			$users = ActiveRecordModel::getRecordSetArray('User',
				select(eq(f('User.ID'), $id))
			);
			if(count($users) == 0)
			{
				throw new Exception('User not found');
			}
		}
		$updater = new ApiUserImport($this->application);
		$updater->allowOnlyUpdate();
		$profile = new CsvImportProfile('User');
		$reader = $this->getDataImportIterator($updater, $profile);
		$updater->setCallback(array($this, 'importCallback'));
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
		$addressFieldNames = array('firstName', 'lastName', 'address1', 'address2', 'city', 'stateName', 'postalCode', 'phone');

		// --
		$response = new LiveCartSimpleXMLElement('<response datetime="'.date('c').'"></response>');
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

			// todo: join? how?? m?!
			$u = User::getInstanceByID($user['ID']);
			$u->loadAddresses();
			// default billing and shipping addreses
			foreach(array('defaultShippingAddress', 'defaultBillingAddress') as $addressType)
			{
				if(is_numeric($user[$addressType.'ID']))
				{
					$address = $u->defaultBillingAddress->get()->userAddressID->get();
					foreach($addressFieldNames as $addressFieldName)
					{
						$responseCustomer->addChild($addressType.'_'.$addressFieldName, $address->$addressFieldName->get());
					}
				}
			}
			$this->mergeUserEavFields($responseCustomer, $u);
			$this->clear($u);
		}
		return new SimpleXMLResponse($response);
	}

	public function filter() // synonym to list method
	{
		$response = new LiveCartSimpleXMLElement('<response datetime="'.date('c').'"></response>');
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
			$u = User::getInstanceByID($customer['ID'], true);
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
			$this->mergeUserEavFields($customerNode, $u);
			$this->clear($u);
		}
		return new SimpleXMLResponse($response);
	}

	private function mergeUserEavFields($customerNode, $u)
	{
		$eavFieldsNode = $customerNode->addChild('EavFields');
		if($u->eavObjectID->get())
		{
			$u->getSpecification();
			$userArray = $u->toArray();
			$attributes = array();
			foreach($userArray['attributes'] as $attr)
			{
				$attrData = array(
					'ID' => $attr['EavField']['ID'],
					'handle' => $attr['EavField']['handle'],
					'name' => $attr['EavField']['name'],
					'value' => '');
				if ($attr['EavField'] && (isset($attr['values']) || isset($attr['value']) || isset($attr['value_lang'])))
				{
					if (isset($attr['values']))
					{
						foreach ($attr['values'] as  $value)
						{
							$attrData['value'][] = $value['value_lang'];
						}
					} else if (isset($attr['value_lang'])) {
						$attrData['value'] = $attr['value_lang'];
					} else if(isset($attr['value'])) {
						$attrData['value'] = $attr['EavField']['valuePrefix_lang'] . $attr['value'] . $attr['EavField']['valueSuffix_lang'];
					}
				}
				$attributes[] = $attrData;
			}
			foreach($attributes as $attr)
			{
				$eavFieldNode = $eavFieldsNode->addChild('EavField');
				foreach($attr as $key => $value)
				{
					if(is_array($value))
					{
						$node = $eavFieldNode->addChild($key.'s');
						foreach($value as $v)
						{
							$node->addChild($key, $v);
						}
					}
					else
					{
						$eavFieldNode->addChild($key, $value);
					}
				}
			}
		}
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
		// todo: use options add, create
		$this->allowOnly = self::UPDATE;
	}

	public function allowOnlyCreate()
	{
		// todo: use options add, create
		$this->allowOnly = self::CREATE;
	}

	public function getClassName()  // because dataImport::getClassName() will return ApiUser, not User.
	{
		return substr(parent::getClassName(),3); // cut off Api from class name
	}

	public // one (bad) implementation of delete() action calls this method, therefore public
	function getInstance($record, CsvImportProfile $profile)
	{
		$instance = parent::getInstance($record, $profile);

		$e = $instance->isExistingRecord();
		if($this->allowOnly == self::CREATE && $e == true)
		{
			throw new Exception('Record exists');
		}
		if($this->allowOnly == self::UPDATE && $e == false)
		{
			throw new Exception('Record not found');
		}
		return $instance;
	}
}

?>
