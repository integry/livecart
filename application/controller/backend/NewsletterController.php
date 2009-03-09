<?php

ClassLoader::import('application.controller.backend.abstract.ActiveGridController');
ClassLoader::import("application.model.newsletter.*");

/**
 * Manage and send newsletters
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role newsletter
 */
class NewsletterController extends ActiveGridController
{
	const PROGRESS_FLUSH_INTERVAL = 10;

	public function index()
	{
		return $this->setGridResponse(new ActionResponse());
	}

	protected function getClassName()
	{
		return 'NewsletterMessage';
	}

	protected function getDefaultColumns()
	{
		return array('NewsletterMessage.ID', 'NewsletterMessage.subject', 'NewsletterMessage.status'/*, 'NewsletterMessage.time'*/);
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

	private function getForm()
	{
		return new Form($this->createValidator());
	}

	private function createValidator()
	{
		$validator = $this->getValidator('newsletter', $this->request);
		$validator->addCheck('subject', new IsNotEmptyCheck($this->translate('_err_title_empty')));
		$validator->addCheck('text', new IsNotEmptyCheck($this->translate('_err_text_empty')));
		return $validator;
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