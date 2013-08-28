<?php


/**
 *  Handles user data import logic
 *
 *  @package application.model.datasync.import
 *  @author Integry Systems
 */
class NewsPostImport extends DataImport
{
	public function getFields()
	{
		$this->loadLanguageFile('backend/SiteNews');

		foreach (ActiveGridController::getSchemaColumns('NewsPost', $this->application) as $key => $data)
		{
			$fields[$key] = $this->translate($data['name']);
		}

		return $this->getGroupedFields($fields);
	}

	public function isRootCategory()
	{
		return false;
	}

	protected function getInstance($record, CsvImportProfile $profile)
	{
		$fields = $profile->getSortedFields();
		if (isset($fields['NewsPost']['ID']))
		{
			try
			{
				$instance = ActiveRecordModel::getInstanceByID('NewsPost', $record[$fields['NewsPost']['ID']], true);
			}
			catch (ARNotFoundException $e)
			{

			}
		}

		if (empty($instance))
		{
			$instance = ActiveRecordModel::getNewInstance('NewsPost');
		}

		//$this->setLastImportedRecordName($instance->getID());
		return $instance;
	}
}

?>