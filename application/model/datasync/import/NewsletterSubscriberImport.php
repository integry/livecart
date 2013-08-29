<?php


/**
 *  Handles user data import logic
 *
 *  @package application/model/datasync/import
 *  @author Integry Systems
 */
class NewsletterSubscriberImport extends DataImport
{
	public function getFields()
	{
		$this->loadLanguageFile('backend/NewsletterSubscriber');

		foreach (ActiveGridController::getSchemaColumns('NewsletterSubscriber', $this->application) as $key => $data)
		{
			$fields[$key] = $this->translate($data['name']);
		}

		unset($fields['NewsletterSubscriber.confirmationCode']);

		return $this->getGroupedFields($fields);
	}

	public function isRootCategory()
	{
		return false;
	}

	protected function getInstance($record, CsvImportProfile $profile)
	{
		$fields = $profile->getSortedFields();
		if (isset($fields['NewsletterSubscriber']['ID']))
		{
			$instance = ActiveRecordModel::getInstanceByID('NewsletterSubscriber', $record[$fields['NewsletterSubscriber']['ID']], true);
		}
		else if (isset($fields['NewsletterSubscriber']['email']))
		{
			$instance = NewsletterSubscriber::getInstanceByEmail($record[$fields['NewsletterSubscriber']['email']]);
		}
		else
		{
			return;
		}

		if (empty($instance))
		{
			$instance = NewsletterSubscriber::getNewInstanceByEmail($record[$fields['NewsletterSubscriber']['email']]);
		}

		$this->setLastImportedRecordName($instance->email->get());
		return $instance;
	}
}

?>