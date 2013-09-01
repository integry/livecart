<?php



/**
 * Manage and send newsletters
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role newsletter
 */
class NewsletterController extends ActiveGridController
{
	const PROGRESS_FLUSH_INTERVAL = 10;

	const FORMAT_HTML_AUTO_TEXT = 1;
	const FORMAT_HTML_TEXT = 2;
	const FORMAT_HTML = 3;
	const FORMAT_TEXT = 4;

	public function indexAction()
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

	public function addAction()
	{
		$this->set('form', $this->getForm());
	}

	private function sortGroups($a, $b)
	{
		return strcmp($a['name'], $b['name']);
	}

	public function editAction()
	{
		$newsletter = ActiveRecordModel::getInstanceById('NewsletterMessage', $this->request->get('id'), ActiveRecordModel::LOAD_DATA);

		$form = $this->getForm();
		$form->setData($newsletter->toArray());
		$form->set('users', 1);
		$form->set('subscribers', 1);
		$this->set('form', $form);
		$groupsArray = array_merge(
			ActiveRecord::getRecordSetArray('UserGroup', select()),
			array(array('ID' => null,'name' => $this->translate('Customers')))
		);

        usort($groupsArray, array($this, 'sortGroups'));
		$this->set('groupsArray', $groupsArray);

		$newsletterArray = $newsletter->toArray();
		$text = strlen($newsletterArray['text']);
		$html =strlen($newsletterArray['html']);
		if($text && $html)
		{
			$newsletterArray['format'] = self::FORMAT_HTML_TEXT;
		}
		else if($text)
		{
			$newsletterArray['format'] = self::FORMAT_TEXT;
		}
		else if($html)
		{
			$newsletterArray['format'] = self::FORMAT_HTML;
		}
		$this->set('newsletter', $newsletterArray);
		$this->set('sentCount', $newsletter->getSentCount());
		$this->set('recipientCount', $this->getRecipientCount($form->getData()));
	}

	public function recipientCountAction()
	{
		$this->set('count', $this->getRecipientCount($this->request->toArray()));
	}

	public function saveAction()
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
			$newsletter = new NewsletterMessage;
		}

		$format = $this->request->get('newsletter_'.$id.'_format');
		if($format == self::FORMAT_TEXT)
		{
			$this->request->set('html', '');
		}
		else if($format == self::FORMAT_HTML)
		{
			$this->request->set('text', '');
		}

		$newsletter->loadRequestData($this->request);
		$newsletter->save();

		if ($this->request->get('sendFlag'))
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
		$validator->add('subject', new PresenceOf(array('message' => $this->translate('_err_title_empty'))));
		// $validator->add('text', new PresenceOf(array('message' => $this->translate('_err_text_empty'))));


		$validator->add('text',
			new OrCheck(
				array('text', 'html'),
				array(
					new PresenceOf(array('message' => $this->translate('_err_text_empty')),
					new PresenceOf(array('message' => $this->translate('_err_text_empty'))
				),
				$this->request
			)
		);

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

		$input = explode(',',$this->getRequest()->get('userGroupIDs'));
		$userGroupIDs = array();
		foreach($input as $value)
		{
			if(preg_match('/^\d+$/', $value))
			{
				$userGroupIDs[] = $value;
			}
			else if(strtolower($value) == 'null')
			{
				$userGroupIDs[] = 'NULL';
			}
		}
		if(count($userGroupIDs) > 0)
		{
			$queries[] = 'SELECT User.email' . ($allFields ? ', User.ID as userID, NULL as subscriberID' : '') . ' FROM User WHERE userGroupID IN('.implode(',',$userGroupIDs).')';
		}
		return implode(' UNION ', $queries);
	}

	public function plaintextAction()
	{
		$h2t = new HtmlToText(
			$this->getRequest()->get('html'),
			array('enableInlineLinks'=>true, 'enableLinkList'=>false)
		);
		return new JSONResponse(array('plaintext' =>$h2t->get_text()), 'ok');
	}
}



?>