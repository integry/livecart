<?php

ClassLoader::import('application.model.datasync.DataImport');
ClassLoader::import('application.model.delivery.ShippingRate');
ClassLoader::import('application.model.delivery.ShippingService');
ClassLoader::import('application.model.delivery.DeliveryZone');

/**
 *  Handles shipping rate import
 *
 *  @package application.model.datasync.import
 *  @author Integry Systems
 */
class ShippingRateImport extends DataImport
{
	public function getFields()
	{
		$this->loadLanguageFile('backend/DeliveryZone');
		$this->loadLanguageFile('backend/ShippingService');

		foreach (ActiveGridController::getSchemaColumns('DeliveryZone', $this->application) as $key => $data)
		{
			$fields[$key] = $this->translate($data['name']);
		}

		$fields = array_intersect_key($fields, array_flip(array('DeliveryZone.ID', 'DeliveryZone.name')));

		foreach (ActiveGridController::getSchemaColumns('ShippingService', $this->application) as $key => $data)
		{
			$fields[$key] = $this->translate($data['name']);
		}

		foreach (ActiveGridController::getSchemaColumns('ShippingRate', $this->application) as $key => $data)
		{
			$fields[$key] = $this->translate($data['name']);
		}

		foreach (array('ShippingService.ID', 'ShippingService.position', 'ShippingRate.ID', 'ShippingRate.perItemChargeClass') as $unnecessary)
		{
			unset($fields[$unnecessary]);
		}

		return $this->getGroupedFields($fields);
	}

	protected function translate($str)
	{
		static $map = array(
			'DeliveryZone.ID' => '_delivery_zone_ID',
			'ShippingService.isFinal' => '_disable_other_services',
		);

		static $rangeMap = array(
			'ShippingService.deliveryTimeMinDays' => array('_expected_delivery_time', 0),
			'ShippingService.deliveryTimeMaxDays' => array('_expected_delivery_time', 1),
			'ShippingRate.weightRangeStart' => array('_weight_range', 0),
			'ShippingRate.weightRangeEnd' => array('_weight_range', 1),
			'ShippingRate.subtotalRangeStart' => array('_subtotal_range', 0),
			'ShippingRate.subtotalRangeEnd' => array('_subtotal_range', 1)
		);

		$translated = parent::translate($str);

		if ($translated == $str)
		{
			if (isset($map[$str]))
			{
				$translated = parent::translate($map[$str]);
			}
			elseif (isset($rangeMap[$str]))
			{
				$t = $rangeMap[$str];
				$translated = parent::translate($t[0]) . ' (' . strtolower(parent::translate($t[1] ? '_to' : '_from')) . ')';
			}
			else
			{
				$orig = $str;
				$str = array_pop(explode('.', $str));
				$str = '_' . strtolower(preg_replace('/[A-Z]/', '_\\0', $str));
				$translated = parent::translate($str);

				if ($translated == $str)
				{
					$translated = $orig;
				}
			}
		}

		return $translated;

	}

	public function isRootCategory()
	{
		return false;
	}

	protected function getInstance($record, CsvImportProfile $profile)
	{
		$fields = $profile->getSortedFields();

		// get delivery zone
		if (isset($fields['DeliveryZone']['ID']))
		{
			try
			{
				$zone = DeliveryZone::getInstanceByID($record[$fields['DeliveryZone']['ID']], true);
			}
			catch (ARNotFoundException $e)
			{
				$zone = DeliveryZone::getDefaultZoneInstance();
			}
		}
		else
		{
			$zone = DeliveryZone::getDefaultZoneInstance();
		}

		// get shipping service
		$f = select(new EqualsCond(
							MultiLingualObject::getLangSearchHandle(
								new ARFieldHandle('ShippingService', 'name'),
								$this->application->getDefaultLanguageCode()
							),
							$record[$fields['ShippingService']['name']]
						));

		if ($zone->isDefault())
		{
			$f->mergeCondition(new IsNullCond(f('ShippingService.deliveryZoneID')));
		}
		else
		{
			$f->mergeCondition(eq(f('ShippingService.deliveryZoneID'), $zone->getID()));
		}

		$services = ActiveRecordModel::getRecordSet('ShippingService', $f);
		if ($services->get(0))
		{
			$service = $services->get(0);

			// temporary
			$service->deleteRelatedRecordSet('ShippingRate');
		}
		else
		{
			$service = ShippingService::getNewInstance($zone, '', 0);
			$service->rangeType->set(ShippingService::SUBTOTAL_BASED);
		}

		$this->importInstance($record, $profile, $service);

		$this->setLastImportedRecordName($service->getValueByLang('name'));

		// get rate instance
		$rate = ShippingRate::getNewInstance($service, 0, 1000000);
		$rate->subtotalRangeStart->set(0);
		$rate->subtotalRangeEnd->set(1000000);
		return $rate;
	}

	public function getClassName($instanceClass = null, $default = null)
	{
		return $instanceClass ? $instanceClass : parent::getClassName($instanceClass, $default);
	}
}

?>