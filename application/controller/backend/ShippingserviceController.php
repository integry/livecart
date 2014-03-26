<?php

require_once(dirname(__FILE__) . '/abstract/ActiveGridController.php');

use delivery\DeliveryZone;
use delivery\ShippingService;
use delivery\ShippingRate;
use Phalcon\Validation\Validator;

/**
 * Custom fields controller
 *
 * @package application/controller/backend
 * @author Integry Systems
 */
class ShippingServiceController extends ActiveGridController
{
	public function indexAction()
	{
		
	}

	public function editAction()
	{
		$this->setValidator($this->buildValidator());
	}
	
	protected function getClassName()
	{
		return 'delivery\ShippingService';
	}
	
	protected function getDefaultColumns()
	{
		return array('delivery\ShippingService.ID', 'delivery\ShippingService.name');
	}
	
	/**
	 * Displays form for creating a new or editing existing one product group specification field
	 *
	 * @return JSONResponse
	 */
	public function getAction()
	{
		if ($id = $this->request->getParam('id'))
		{
			$field = ShippingService::getInstanceByID($id);
			$array = $field->toArray();
			$array['rates'] = array();
			foreach ($field->shippingRates as $rate)
			{
				$array['rates'][] = $rate->toArray();
			}
		}
		else
		{
			$array = array('rates' => array());
		}
		
		echo json_encode($array);
	}

	/**
	 * Creates a new or modifies an exisitng specification field (according to a passed parameters)
	 *
	 * @return JSONResponse Returns success status or failure status with array of erros
	 */
	public function saveAction()
	{
		if ($id = $this->request->getJson('ID'))
		{
			$specField = EavField::getInstanceByID($id);
		}
		else
		{
			$specField = new EavField;
			$specField->classID = $this->request->getJson('eavType');
		}

		if (!is_numeric($this->request->getJson('eavType')))
		{
			$specField->stringIdentifier = $this->request->getJson('eavType');
		}
		
		$specField->loadRequestData($this->request);
		
		$type = $this->request->getJson('advancedText') ? EavField::TYPE_TEXT_ADVANCED : (int)$this->request->getJson('type');
		$dataType = EavField::getDataTypeFromType($type);

		$specField->dataType = $dataType;
		$specField->type = $type;

		$specField->save();
		
		$values = $this->request->getJson('values');
		if (is_array($values))
		{
			$existingValues = array();
			foreach ($specField->getValues() as $value)
			{
				$existingValues[$value->getID()] = $value;
			}
			
			foreach ($values as $key => &$value)
			{
				if (empty($value['value']))
				{
					continue;
				}

				if (empty($value['ID']))
				{
					$val = EavValue::getNewInstance($specField);
				}
				else
				{
					$val = isset($existingValues[$value['ID']]) ? $existingValues[$value['ID']] : EavValue::getNewInstance($specField);
					unset($existingValues[$value['ID']]);
				}
				
				$val->assign($value);
				$val->position = $key;
				$val->save();
			}
			
			// existing values not present in the posted data are deleted
			foreach ($existingValues as $deleted)
			{
				$deleted->delete();
			}
		}
		
		$arr = $specField->toArray();
		$arr['values'] = $specField->getValues()->toArray();
		
		echo json_encode($arr);
	}
	
	public function updateAction()
	{
		if ($id = $this->request->getJSON('ID'))
		{
			$field = ShippingService::getInstanceByID($id);
		}
		else
		{
			$field = ShippingService::getNewInstance(DeliveryZone::getInstanceByID($this->request->getJSON('deliveryZoneID')));
		}
		
		$field->loadRequestData($this->request);
		$field->save();
		
		$field->shippingRates->delete();
		foreach ((array)$this->request->getJson('rates') as $rateData)
		{
			$rate = ShippingRate::getNewInstance($field);
			$rate->assign($rateData);
			$rate->save();
		}
		
		return $this->getAction($field->getID());
	}

	protected function getFieldClass()
	{
		return 'ShippingService';
	}

	public function listAction()
	{
	}

	public function addAction()
	{
	}

	protected function getSelectFilter()
	{
		$f = parent::getSelectFilter();
		$f->andWhere('deliveryZoneID = :zone:', array('zone' => $this->dispatcher->getParam(0)));
		return $f;
	}
	
	/**
	 *
	 * @return \Phalcon\Validation
	 */
	protected function buildValidator()
	{
		$validator = $this->getValidator("shippingServiceValidator", $this->request);

		$validator->add('name', new Validator\PresenceOf(array('message' => $this->translate('_err_name_empty'))));

		return $validator;
	}

}

?>
