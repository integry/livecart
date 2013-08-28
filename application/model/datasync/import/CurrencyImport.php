<?php


/**
 *  Handles user data import logic
 *
 *  @package application.model.datasync.import
 *  @author Integry Systems
 */
class CurrencyImport extends DataImport
{
	public function getFields()
	{
		$this->loadLanguageFile('backend/Currency');

		foreach (ActiveGridController::getSchemaColumns('Currency', $this->application) as $key => $data)
		{
			$fields[$key] = $this->translate($data['name']);
		}

		unset($fields['Currency.position']);
		unset($fields['Currency.lastUpdated']);
		unset($fields['Currency.rounding']);

		return $this->getGroupedFields($fields);
	}

	public function isRootCategory()
	{
		return false;
	}

	protected function getInstance($record, CsvImportProfile $profile)
	{
		$fields = $profile->getSortedFields();
		if (isset($fields['Currency']['ID']))
		{
			try
			{
				$instance = Currency::getInstanceByID($record[$fields['Currency']['ID']], true);
			}
			catch (ARNotFoundException $e)
			{

			}
		}
		else
		{
			return;
		}

		if (empty($instance))
		{
			$instance = Currency::getNewInstance($record[$fields['Currency']['ID']]);
		}

		$this->setLastImportedRecordName($instance->getID());
		return $instance;
	}
}

?>