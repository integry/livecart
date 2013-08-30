<?php

/**
 * Manage order notes (communication with customer)
 *
 * @package application/controller/backend
 * @author Integry Systems
 *
 * @role order
 */
class OrderNoteController extends StoreManagementController
{
	public function indexAction()
	{
		$order = CustomerOrder::getInstanceById($this->request->get('id'));

		$notes = $order->getNotes();
		foreach ($notes as $note)
		{
			if (!$note->isRead->get() && !$note->isAdmin->get())
			{
				$note->isRead->set(true);
				$note->save();
			}
		}


		$this->set('form', $this->buildOrderNoteForm());
		$this->set('order', $order->toArray());
		$this->set('notes', $notes->toArray());
	}

	public function viewAction()
	{
		return new ActionResponse('note', ActiveRecordModel::getInstanceById('OrderNote', $this->request->get('id'), OrderNote::LOAD_DATA, OrderNote::LOAD_REFERENCES)->toArray());
	}

	public function addAction()
	{
		if ($this->buildOrderNoteValidator()->isValid())
		{
			$order = CustomerOrder::getInstanceById($this->request->get('id'), CustomerOrder::LOAD_DATA);

			$note = OrderNote::getNewInstance($order, $this->user);
			$note->isAdmin->set(true);
			$note->text->set($this->request->get('comment'));
			$note->save();

			if ($this->config->get('EMAIL_ORDERNOTE'))
			{
				$order->user->get()->load();

				$email = new Email($this->application);
				$email->setUser($order->user->get());
				$email->setTemplate('order.message');
				$email->set('order', $order->toArray(array('payments' => true)));
				$email->set('message', $note->toArray());
				$email->send();
			}

			return new ActionRedirectResponse('backend.orderNote', 'view', array('id' => $note->getID()));
		}
		else
		{
			return new RawResponse('invalid');
		}
	}

	private function buildOrderNoteForm()
	{
		return new Form($this->buildOrderNoteValidator());
	}

	private function buildOrderNoteValidator()
	{
		$validator = $this->getValidator("orderNote", $this->request);
		$validator->addCheck('comment', new IsNotEmptyCheck($this->translate('_err_enter_text')));
		return $validator;
	}
}

?>