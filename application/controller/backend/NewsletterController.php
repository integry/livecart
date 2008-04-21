<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.newsletter.*");
ClassLoader::import("framework.request.validator.RequestValidator");
ClassLoader::import("framework.request.validator.Form");

/**
 * Manage and send newsletters
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role newsletter
 */
class NewsletterController extends StoreManagementController
{
	const PROGRESS_FLUSH_INTERVAL = 10;

	public function index()
	{
		$response = new ActionResponse();
		$response->set('massForm', $this->getMassForm());

		$availableColumns = $this->getAvailableColumns();
		$displayedColumns = $this->getDisplayedColumns();

		// sort available columns by display state (displayed columns first)
		$displayedAvailable = array_intersect_key($availableColumns, $displayedColumns);
		$notDisplayedAvailable = array_diff_key($availableColumns, $displayedColumns);
		$availableColumns = array_merge($displayedAvailable, $notDisplayedAvailable);

		$response->set('displayedColumns', $displayedColumns);
		$response->set('availableColumns', $availableColumns);

		return $response;
	}

	public function add()
	{
		return new ActionResponse('form', $this->getForm());
	}

	public function edit()
	{
		$newsletter = ActiveRecordModel::getInstanceById('NewsletterMessage', $this->request->get('id'), ActiveRecordModel::LOAD_DATA);
		$form = $this->getForm();
		$form->setData($newsletter->toArray());
		$form->set('users', 1);
		$form->set('subscribers', 1);

		$response = new ActionResponse('form', $form);
		$response->set('newsletter', $newsletter->toArray());
		$response->set('sentCount', $newsletter->getSentCount());
		$response->set('recipientCount', $this->getRecipientCount($form->getData()));
		return $response;
	}

	public function recipientCount()
	{
		return new ActionResponse('count', $this->getRecipientCount($this->request->toArray()));
	}

	public function save()
	{
		$validator = $this->createValidator();
		if (!$validator->isValid())
		{
			return new JSONResponse(array('errors' => $validator->getErrorList(), 'failure'));
		}

		if ($id = $this->request->get('id'))
		{
			$newsletter = ActiveRecordModel::getInstanceByID('NewsletterMessage', $id);
		}
		else
		{
			$newsletter = ActiveRecordModel::getNewInstance('NewsletterMessage');
		}

		$newsletter->loadRequestData($this->request);
		$newsletter->save();

		if ($this->request->isValueSet('send'))
		{
			return $this->send($newsletter);
		}

		return new JSONResponse($newsletter->toArray());
	}

	private function send(NewsletterMessage $newsletter)
	{
		set_time_limit(0);
		$response = new JSONResponse(null);

		$data = $this->getRecipientData($this->request->toArray());
		$total = count($data);

		$subscribers = $users = array();
		foreach ($data as $row)
		{
			if ($row['userID'])
			{
				$users[] = $row['userID'];
			}
			else
			{
				$subscribers[] = $row['subscriberID'];
			}
		}

		$progress = 0;
		foreach (array('User' => $users, 'NewsletterSubscriber' => $subscribers) as $table => $ids)
		{
			foreach (array_chunk($ids, self::PROGRESS_FLUSH_INTERVAL) as $chunk)
			{
				foreach (ActiveRecordModel::getRecordSet($table, new ARSelectFilter(new InCond(new ARFieldHandle($table, 'ID'), $chunk))) as $recipient)
				{
					$progress++;

					$newsletter->send($recipient, $this->application);

					if ($progress % self::PROGRESS_FLUSH_INTERVAL == 0 || ($total == $progress))
					{
						$response->flush($this->getJsonResponse(array('progress' => $progress, 'total' => $total)));
					}
				}

				ActiveRecord::clearPool();
			}
		}

		$newsletter->markAsSent();
		$response->flush($this->getJsonResponse(array('progress' => 0, 'total' => $total)));

		exit;
	}

	private function getJsonResponse($data)
	{
		return '|' . base64_encode(json_encode($data));
	}

	public function lists($dataOnly = false, $displayedColumns = null)
	{
		$filter = new ARSelectFilter();

		new ActiveGrid($this->application, $filter, 'NewsletterMessage');

		$recordCount = true;
		$newsletterArray = ActiveRecordModel::getRecordSetArray('NewsletterMessage', $filter, false, $recordCount);

		if (!$displayedColumns)
		{
			$displayedColumns = $this->getDisplayedColumns();
		}

		$data = array();

		foreach ($newsletterArray as $newsletter)
		{
			$record = array();
			foreach ($displayedColumns as $column => $type)
			{
				list($class, $field) = explode('.', $column, 2);
				$value = $newsletter/*[$class]*/[$field];

				if ('bool' == $type)
				{
					$value = $value ? $this->translate('_yes') : $this->translate('_no');
				}

				$record[] = $value;
			}

			$data[] = $record;
		}

		if ($dataOnly)
		{
			return $data;
		}

		$return = array();
		$return['columns'] = array_keys($displayedColumns);
		$return['totalCount'] = $recordCount;
		$return['data'] = $data;

		return new JSONResponse($return);
	}

	public function getAvailableColumns()
	{
		// get available columns
		$schema = ActiveRecordModel::getSchemaInstance('NewsletterMessage');

		$availableColumns = array();
		foreach ($schema->getFieldList() as $field)
		{
			$type = ActiveGrid::getFieldType($field);

			if (!$type)
			{
				continue;
			}

			$availableColumns['NewsletterMessage.' . $field->getName()] = $type;
		}

		foreach ($availableColumns as $column => $type)
		{
			$availableColumns[$column] = array('name' => $this->translate($column), 'type' => $type);
		}

		unset($availableColumns['NewsletterMessage.text']);

		return $availableColumns;
	}


	protected function getDisplayedColumns()
	{
		// get displayed columns
		//$displayedColumns = $this->getSessionData('columns');

		$displayedColumns = null;
		if (!$displayedColumns)
		{
			$displayedColumns = array('NewsletterMessage.ID', 'NewsletterMessage.subject', 'NewsletterMessage.status', 'NewsletterMessage.time');
		}

		$availableColumns = $this->getAvailableColumns();
		$displayedColumns = array_intersect_key(array_flip($displayedColumns), $availableColumns);

		// set field type as value
		foreach ($displayedColumns as $column => $foo)
		{
			if (is_numeric($displayedColumns[$column]))
			{
				$displayedColumns[$column] = $availableColumns[$column]['type'];
			}
		}

		$displayedColumns = array_merge(array('NewsletterMessage.ID' => 'numeric'), $displayedColumns);

		return $displayedColumns;
	}

	public function processMass()
	{
		ClassLoader::import('application.helper.massAction.NewsletterMessageMassActionProcessor');

		$grid = new ActiveGrid($this->application, new ARSelectFilter(), 'NewsletterMessage');

		$mass = new NewsletterMessageMassActionProcessor($grid, array());
		$mass->setCompletionMessage($this->translate('_mass_action_succeed'));
		return $mass->process();
	}

	public function isMassCancelled()
	{
		ClassLoader::import('application.helper.massAction.NewsletterMessageMassActionProcessor');

		return new JSONResponse(array('isCancelled' => NewsletterMessageMassActionProcessor::isCancelled($this->request->get('pid'))));
	}

	public function export()
	{
		@set_time_limit(0);

		// init file download
		header('Content-Disposition: attachment; filename="exported.csv"');
		$out = fopen('php://output', 'w');

		// header row
		$columns = $this->getDisplayedColumns();
		foreach ($columns as $column => $type)
		{
			$header[] = $this->translate($column);
		}
		fputcsv($out, $header);

		// columns
		foreach ($this->lists(true, $columns) as $row)
		{
			fputcsv($out, $row);
		}

		exit;
	}

	private function getForm()
	{
		return new Form($this->createValidator());
	}

	private function createValidator()
	{
		$validator = new RequestValidator('newsletter', $this->request);
		$validator->addCheck('subject', new IsNotEmptyCheck($this->translate('_err_title_empty')));
		$validator->addCheck('text', new IsNotEmptyCheck($this->translate('_err_text_empty')));
		return $validator;
	}

	private function getMassForm()
	{
		$validator = new RequestValidator("newsletterMassValidator", $this->request);
		return new Form($validator);
	}

	private function getRecipientData($data)
	{
		$query = $this->getRecipientQuery($data, true);
		if (!$query)
		{
			return array();
		}

		return ActiveRecord::getDataBySQL('SELECT email, userID, subscriberID FROM (' . $query . ') AS recipients GROUP BY email');
	}

	private function getRecipientCount($data)
	{
		$query = $this->getRecipientQuery($data, false);
		if (!$query)
		{
			return 0;
		}

		$query = 'SELECT COUNT(*) FROM (' . $query . ') AS recipCount';
		return array_shift(array_shift(ActiveRecordModel::getDataBySQL($query)));
	}

	private function getRecipientQuery($data, $allFields = true)
	{
		if (!isset($data['id']))
		{
			$data['id'] = $data['ID'];
		}

		$queries = array();
		if (!empty($data['users']))
		{
			$queries[] = 'SELECT User.email' . ($allFields ? ', User.ID as userID, NULL AS subscriberID' : '') . ' FROM User LEFT JOIN NewsletterSubscriber ON User.email=NewsletterSubscriber.email LEFT JOIN NewsletterSentMessage ON (User.ID=NewsletterSentMessage.userID AND NewsletterSentMessage.messageID=' . $data['id'] . ') WHERE NewsletterSentMessage.userID IS NULL AND ((NewsletterSubscriber.isEnabled IS NULL) OR (NewsletterSubscriber.isEnabled=1))';
		}

		if (!empty($data['subscribers']))
		{
			$queries[] = 'SELECT NewsletterSubscriber.email' . ($allFields ? ', NULL as userID, NewsletterSubscriber.ID as subscriberID' : '') . ' FROM NewsletterSubscriber LEFT JOIN User ON User.email=NewsletterSubscriber.email LEFT JOIN NewsletterSentMessage ON (NewsletterSubscriber.ID=NewsletterSentMessage.subscriberID AND NewsletterSentMessage.messageID=' . $data['id'] . ') WHERE NewsletterSentMessage.subscriberID IS NULL AND ((User.email IS NULL) OR (NewsletterSubscriber.isEnabled=1))';
		}

		return implode(' UNION ', $queries);
	}
}

?>